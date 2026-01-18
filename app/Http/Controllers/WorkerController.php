<?php

namespace App\Http\Controllers;

use App\Models\{Job, Quote, User};
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkerController extends Controller
{
    /**
     * Show nearby jobs (notifications page)
     */
    public function nearbyJobs(Request $request, NotificationService $notificationService)
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }

        if (!$user->lat || !$user->lng) {
            return view('mfanyakazi.nearby-jobs', [
                'jobs' => collect([]),
                'needsLocation' => true,
            ]);
        }

        $radiusKm = $request->query('radius', 10);

        // Get nearby jobs accepting quotes
        $jobs = Job::with(['category', 'muhitaji'])
            ->select([
                'work_orders.*',
                DB::raw("
                    (6371 * acos(
                        cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) 
                        + sin(radians(?)) * sin(radians(lat))
                    )) AS distance_km
                ")
            ])
            ->setBindings([$user->lat, $user->lng, $user->lat])
            ->whereIn('status', ['pending_quotes', 'receiving_quotes'])
            ->where('quote_window_closes_at', '>', now())
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km', 'asc')
            ->limit(20)
            ->get()
            ->map(function($job) use ($user) {
                $job->already_quoted = Quote::where('job_id', $job->id)
                    ->where('worker_id', $user->id)
                    ->exists();
                $job->time_remaining_minutes = $job->quote_window_closes_at 
                    ? max(0, now()->diffInMinutes($job->quote_window_closes_at, false)) 
                    : null;
                return $job;
            });

        return view('mfanyakazi.nearby-jobs', [
            'jobs' => $jobs,
            'radius' => $radiusKm,
            'needsLocation' => false,
        ]);
    }

    /**
     * Show quote submission form
     */
    public function showQuoteForm(Job $job)
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }

        // Check if quote window still open
        if ($job->quote_window_closes_at && now()->isAfter($job->quote_window_closes_at)) {
            return redirect()->route('mfanyakazi.nearby-jobs')
                ->with('error', 'Muda wa kutuma ofa umekwisha.');
        }

        // Check if already submitted quote (pending only)
        $existingQuote = Quote::where('job_id', $job->id)
            ->where('worker_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingQuote) {
            return redirect()->route('mfanyakazi.my-quotes')
                ->with('error', 'Umeshatuma ofa kwa kazi hii. Badilisha ofa yako badala ya kutuma mpya.');
        }

        $job->load('category', 'muhitaji');
        
        // Calculate distance
        $notificationService = app(NotificationService::class);
        $distance = $notificationService->calculateDistance(
            $user->lat, $user->lng,
            $job->lat, $job->lng
        );
        
        $job->distance_km = round($distance, 2);

        return view('mfanyakazi.submit-quote', compact('job'));
    }

    /**
     * Submit quote
     */
    public function submitQuote(Request $request, Job $job)
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }

        // Validate
        $request->validate([
            'quoted_price' => ['required', 'integer', 'min:500'],
            'eta_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'quoted_price.required' => 'Weka bei unayotaka.',
            'eta_minutes.required' => 'Weka muda wa kufika.',
        ]);

        // Check if quote window still open
        if ($job->quote_window_closes_at && now()->isAfter($job->quote_window_closes_at)) {
            return back()->with('error', 'Muda wa kutuma ofa umekwisha.');
        }

        // Check if already submitted (only pending quotes)
        $existingQuote = Quote::where('job_id', $job->id)
            ->where('worker_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingQuote) {
            return back()->with('error', 'Umeshatuma ofa kwa kazi hii. Tafadhali ibadilishe badala ya kutuma mpya.');
        }

        // Create quote
        Quote::create([
            'job_id' => $job->id,
            'worker_id' => $user->id,
            'quoted_price' => $request->quoted_price,
            'eta_minutes' => $request->eta_minutes,
            'notes' => $request->notes,
            'status' => 'pending',
            'expires_at' => $job->quote_window_closes_at,
        ]);

        // Increment quote count
        $job->increment('quote_count');

        return redirect()
            ->route('mfanyakazi.nearby-jobs')
            ->with('success', 'Ofa yako imetumwa! Muhitaji ataiona na ataweza kukuchagua.');
    }

    /**
     * View worker's submitted quotes
     */
    public function myQuotes()
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }

        $quotes = Quote::with(['job.category', 'job.muhitaji'])
            ->where('worker_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('mfanyakazi.my-quotes', compact('quotes'));
    }

    /**
     * Edit quote form
     */
    public function editQuote(Quote $quote)
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }

        if ($quote->worker_id !== $user->id) {
            abort(403, 'Hii sio ofa yako.');
        }

        if ($quote->status !== 'pending') {
            return back()->with('error', 'Huwezi kubadilisha ofa iliyokubaliwa au kukataliwa.');
        }

        $job = $quote->job;
        $job->load('category', 'muhitaji');

        return view('mfanyakazi.edit-quote', compact('quote', 'job'));
    }

    /**
     * Update quote
     */
    public function updateQuote(Request $request, Quote $quote)
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }

        if ($quote->worker_id !== $user->id) {
            abort(403, 'Hii sio ofa yako.');
        }

        if ($quote->status !== 'pending') {
            return back()->with('error', 'Huwezi kubadilisha ofa iliyokubaliwa au kukataliwa.');
        }

        $request->validate([
            'quoted_price' => ['required', 'integer', 'min:500'],
            'eta_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $quote->update([
            'quoted_price' => $request->quoted_price,
            'eta_minutes' => $request->eta_minutes,
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('mfanyakazi.my-quotes')
            ->with('success', 'Ofa yako imebadilishwa!');
    }

    /**
     * Delete quote
     */
    public function deleteQuote(Quote $quote)
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }

        if ($quote->worker_id !== $user->id) {
            abort(403, 'Hii sio ofa yako.');
        }

        if ($quote->status !== 'pending') {
            return back()->with('error', 'Huwezi kufuta ofa iliyokubaliwa au kukataliwa.');
        }

        $job = $quote->job;
        $quote->delete();
        
        // Decrement quote count
        $job->decrement('quote_count');

        return redirect()
            ->route('mfanyakazi.my-quotes')
            ->with('success', 'Ofa imefutwa.');
    }
    
    /**
     * Show completion code form for worker (to enter code from muhitaji)
     */
    public function showCompletionCode(Job $job)
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }
        
        if ($job->accepted_worker_id !== $user->id) {
            abort(403, 'Hii sio kazi yako.');
        }
        
        if (!in_array($job->status, ['assigned', 'in_progress'])) {
            return redirect()->route('dashboard')
                ->with('error', 'Kazi hii tayari imekamilika au haijapata malipo bado.');
        }
        
        $job->load('muhitaji');
        
        return view('mfanyakazi.completion-code', compact('job'));
    }
    
    /**
     * Verify completion code and complete job (worker side)
     */
    public function verifyAndComplete(Request $request, Job $job)
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }
        
        if ($job->accepted_worker_id !== $user->id) {
            abort(403, 'Hii sio kazi yako.');
        }
        
        $request->validate([
            'completion_code' => 'required|string|size:6',
        ]);
        
        $code = $request->input('completion_code');
        
        if (!$job->verifyAndComplete($code)) {
            return back()->withErrors(['error' => 'Nambari ya ukamilishaji si sahihi. Jaribu tena.']);
        }
        
        return redirect()->route('dashboard')
            ->with('success', 'Hongera! Kazi imekamilika. Pesa zimetolewa kwako!');
    }
    
    /**
     * Accept job assignment (mfanyakazi accepts after being selected)
     */
    public function acceptAssignment(Job $job)
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }
        
        if ($job->accepted_worker_id !== $user->id) {
            abort(403, 'Hii sio kazi yako.');
        }
        
        if ($job->status !== 'assigned') {
            return back()->with('error', 'Kazi hii tayari imekubaliwa au status si sahihi.');
        }
        
        $job->update(['status' => 'in_progress']);
        
        return redirect()->route('dashboard')
            ->with('success', 'Umekubali kazi! Anza kufanya kazi sasa.');
    }
    
    /**
     * Reject job assignment (mfanyakazi rejects after being selected)
     */
    public function rejectAssignment(Request $request, Job $job)
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }
        
        if ($job->accepted_worker_id !== $user->id) {
            abort(403, 'Hii sio kazi yako.');
        }
        
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        // Revert job status
        $job->update([
            'status' => 'receiving_quotes',
            'accepted_worker_id' => null,
            'selected_quote_id' => null,
        ]);
        
        // Mark quote as rejected
        if ($job->selectedQuote) {
            $job->selectedQuote->update(['status' => 'withdrawn']);
        }
        
        // TODO: Refund muhitaji escrow payment
        // This should trigger a refund process
        
        return redirect()->route('dashboard')
            ->with('success', 'Umekataa kazi. Muhitaji atapata taarifa.');
    }
    
    /**
     * Show map view with all nearby jobs
     */
    public function mapView()
    {
        $user = Auth::user();
        
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Mfanyakazi tu.');
        }
        
        if (!$user->lat || !$user->lng) {
            return redirect()->route('mfanyakazi.nearby-jobs')
                ->with('error', 'Weka location yako kwanza ili kuona ramani.');
        }
        
        $radiusKm = 50; // 50km radius
        
        // Get nearby jobs with distance calculation
        $jobs = Job::with(['category', 'muhitaji'])
            ->select([
                'work_orders.*',
                DB::raw("
                    (6371 * acos(
                        cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) 
                        + sin(radians(?)) * sin(radians(lat))
                    )) AS distance_km
                ")
            ])
            ->setBindings([$user->lat, $user->lng, $user->lat])
            ->whereIn('status', ['pending_quotes', 'receiving_quotes'])
            ->where('quote_window_closes_at', '>', now())
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km', 'asc')
            ->get()
            ->map(function($job) use ($user) {
                $job->already_quoted = Quote::where('job_id', $job->id)
                    ->where('worker_id', $user->id)
                    ->exists();
                $job->time_remaining_minutes = $job->quote_window_closes_at 
                    ? max(0, now()->diffInMinutes($job->quote_window_closes_at, false)) 
                    : null;
                return $job;
            });
        
        // Count by distance categories
        $nearJobsCount = $jobs->filter(fn($j) => $j->distance_km < 5)->count();
        $mediumJobsCount = $jobs->filter(fn($j) => $j->distance_km >= 5 && $j->distance_km < 15)->count();
        $farJobsCount = $jobs->filter(fn($j) => $j->distance_km >= 15)->count();
        
        return view('mfanyakazi.map-view', compact('jobs', 'nearJobsCount', 'mediumJobsCount', 'farJobsCount'));
    }
}
