<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JobController;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobStatusLog;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Notifications\JobStatusNotification;
use App\Services\ClickPesaService;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Mfanyakazi (worker) acting AS A JOB POSTER.
 *
 * Endpoints:
 *   POST   /api/worker/jobs                       — create new job (image, fee logic)
 *   GET    /api/worker/my-posted-jobs             — list jobs the worker has posted
 *   GET    /api/worker/jobs/{job}                 — single posted job + summary
 *   GET    /api/worker/jobs/{job}/applications    — applications received on a posted job
 *   PUT    /api/worker/jobs/{job}                 — edit posted job (open jobs only)
 *   DELETE /api/worker/jobs/{job}                 — cancel posted job (open jobs only)
 */
class MfanyakaziJobController extends Controller
{
    /** Ensure the authenticated user is a worker (or admin). */
    private function ensureWorker(Request $request): void
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['mfanyakazi', 'admin'], true)) {
            abort(403, 'Huna ruhusa. Mfanyakazi tu.');
        }
    }

    /** Ensure the user owns the job (or is admin). */
    private function ensureOwner(Request $request, Job $job): void
    {
        $user = $request->user();
        if ($job->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Hii sio kazi yako.');
        }
    }

    /**
     * POST /api/worker/jobs — create job.
     * Supports image upload (multipart/form-data), wallet/clickpesa posting fee.
     */
    public function store(Request $request): JsonResponse
    {
        $this->ensureWorker($request);
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string', 'min:20', 'max:2000'],
            'price' => ['required', 'integer', 'min:1000'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'phone' => ['required', 'regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
            'address_text' => ['nullable', 'string', 'max:255'],
            'urgency' => ['nullable', 'in:normal,urgent,flexible'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'image.image' => 'Faili lazima iwe picha (jpeg, jpg, png, au webp).',
            'image.max' => 'Picha haipaswi kuwa kubwa zaidi ya 5MB.',
            'phone.regex' => 'Namba ya simu si sahihi (mfano: 0712345678).',
        ]);

        // Reuse JobController's image upload helper
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = app(JobController::class)->handleImageUpload($request->file('image'));
        }

        $localized = TranslationService::ensureBothLanguages(
            $validated['title'],
            $validated['description']
        );

        $postingFee = (int) Setting::get('job_posting_fee', 0);
        $wallet = $user->ensureWallet();

        // Case 1: Free posting
        if ($postingFee <= 0) {
            $job = $this->createJob($user->id, $validated, $localized, $imagePath, Job::S_OPEN, 0);
            $this->afterPosted($job, $user);

            return response()->json([
                'success' => true,
                'message' => 'Kazi imechapishwa! Wafanyakazi wengine wataona na kuomba.',
                'data' => ['job' => $this->presentJob($job)],
                'payment_method' => 'free',
            ], 201);
        }

        // Case 2: Paid via wallet
        if ($wallet->balance >= $postingFee) {
            $job = DB::transaction(function () use ($user, $validated, $localized, $imagePath, $postingFee, $wallet) {
                $job = $this->createJob($user->id, $validated, $localized, $imagePath, Job::S_OPEN, $postingFee);
                $wallet->decrement('balance', $postingFee);
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $postingFee,
                    'description' => "Job posting fee: {$job->title}",
                    'reference' => "JOB_POST_{$job->id}",
                ]);
                return $job;
            });
            $this->afterPosted($job, $user);

            return response()->json([
                'success' => true,
                'message' => 'Kazi imechapishwa kwa mafanikio! Ada imelipwa kutoka pochi yako.',
                'data' => ['job' => $this->presentJob($job)],
                'payment_method' => 'wallet',
            ], 201);
        }

        // Case 3: Paid via ClickPesa USSD push
        $job = $this->createJob($user->id, $validated, $localized, $imagePath, 'pending_payment', $postingFee);
        $orderId = strtoupper(Str::random(16));
        $payment = $job->payment()->create([
            'order_id' => $orderId,
            'amount' => $postingFee,
            'status' => 'PENDING',
        ]);

        $clickpesa = app(ClickPesaService::class);
        $res = $clickpesa->startPayment([
            'orderReference' => $orderId,
            'phoneNumber' => $validated['phone'],
            'amount' => $postingFee,
        ]);

        return response()->json([
            'success' => $res['ok'] ?? false,
            'message' => ($res['ok'] ?? false)
                ? 'Fanya malipo ya ada ya kuchapisha. Utapokea ujumbe wa USSD.'
                : 'Imeshindikana kuanzisha malipo. Jaribu tena.',
            'data' => [
                'job' => $this->presentJob($job),
                'payment' => $payment,
                'clickpesa' => $res,
            ],
            'payment_method' => 'clickpesa',
        ], ($res['ok'] ?? false) ? 201 : 400);
    }

    /**
     * GET /api/worker/my-posted-jobs — list jobs the worker has posted.
     * Filters: status (open|in_progress|completed|all), q (search title)
     */
    public function myPosted(Request $request): JsonResponse
    {
        $this->ensureWorker($request);
        $user = $request->user();

        $query = Job::query()
            ->where('user_id', $user->id)
            ->with(['category', 'selectedWorker:id,name,profile_photo_path,phone'])
            ->withCount(['applications as applications_count' => function ($q) {
                $q->whereNotIn('status', [JobApplication::STATUS_WITHDRAWN, JobApplication::STATUS_REJECTED]);
            }])
            ->latest();

        // Status filter
        $status = $request->get('status', 'all');
        if ($status === 'open') {
            $query->whereIn('status', [Job::S_OPEN, 'posted']);
        } elseif ($status === 'in_progress') {
            $query->whereIn('status', [Job::S_AWAITING_PAYMENT, Job::S_FUNDED, Job::S_IN_PROGRESS, Job::S_SUBMITTED]);
        } elseif ($status === 'completed') {
            $query->whereIn('status', [Job::S_COMPLETED, 'completed']);
        } elseif ($status === 'cancelled') {
            $query->whereIn('status', [Job::S_CANCELLED, 'cancelled', Job::S_REFUNDED]);
        }

        if ($q = $request->get('q')) {
            $query->where('title', 'like', "%{$q}%");
        }

        $paginated = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => [
                'jobs' => $paginated->getCollection()->map(fn ($j) => $this->presentJob($j))->values(),
                'pagination' => $this->paginationMeta($paginated),
                'counts' => [
                    'open' => Job::where('user_id', $user->id)->whereIn('status', [Job::S_OPEN, 'posted'])->count(),
                    'in_progress' => Job::where('user_id', $user->id)->whereIn('status', [Job::S_AWAITING_PAYMENT, Job::S_FUNDED, Job::S_IN_PROGRESS, Job::S_SUBMITTED])->count(),
                    'completed' => Job::where('user_id', $user->id)->whereIn('status', [Job::S_COMPLETED, 'completed'])->count(),
                ],
            ],
        ]);
    }

    /**
     * GET /api/worker/jobs/{job} — single posted job with applications summary.
     */
    public function show(Request $request, Job $job): JsonResponse
    {
        $this->ensureWorker($request);
        $this->ensureOwner($request, $job);

        $job->load(['category', 'selectedWorker:id,name,profile_photo_path,phone', 'acceptedWorker:id,name,profile_photo_path,phone']);

        return response()->json([
            'success' => true,
            'data' => [
                'job' => $this->presentJob($job, full: true),
                'applications_count' => $job->applications()
                    ->whereNotIn('status', [JobApplication::STATUS_WITHDRAWN, JobApplication::STATUS_REJECTED])
                    ->count(),
            ],
        ]);
    }

    /**
     * GET /api/worker/jobs/{job}/applications — applications received on this posted job.
     * Filters: status (applied|shortlisted|countered|selected|rejected|all)
     */
    public function applications(Request $request, Job $job): JsonResponse
    {
        $this->ensureWorker($request);
        $this->ensureOwner($request, $job);

        $query = $job->applications()
            ->with(['worker:id,name,phone,profile_photo_path,role'])
            ->latest();

        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $paginated = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'job' => [
                    'id' => $job->id,
                    'title' => $job->title,
                    'price' => $job->price,
                    'status' => $job->status,
                    'completion_code' => $job->user_id === $request->user()->id ? $job->completion_code : null,
                ],
                'applications' => $paginated->getCollection()->map(function ($app) {
                    return [
                        'id' => $app->id,
                        'status' => $app->status,
                        'proposed_amount' => $app->proposed_amount,
                        'counter_amount' => $app->counter_amount,
                        'agreed_amount' => $app->getAgreedAmount(),
                        'message' => $app->message,
                        'eta_text' => $app->eta_text,
                        'client_response_note' => $app->client_response_note,
                        'created_at' => $app->created_at?->toISOString(),
                        'shortlisted_at' => $app->shortlisted_at?->toISOString(),
                        'selected_at' => $app->selected_at?->toISOString(),
                        'rejected_at' => $app->rejected_at?->toISOString(),
                        'worker' => $app->worker ? [
                            'id' => $app->worker->id,
                            'name' => $app->worker->name,
                            'phone' => $app->worker->phone,
                            'profile_photo_url' => $app->worker->profile_photo_path
                                ? asset('storage/'.$app->worker->profile_photo_path)
                                : null,
                        ] : null,
                    ];
                })->values(),
                'pagination' => $this->paginationMeta($paginated),
            ],
        ]);
    }

    /**
     * PUT /api/worker/jobs/{job} — edit job (only when still open / no worker selected).
     */
    public function update(Request $request, Job $job): JsonResponse
    {
        $this->ensureWorker($request);
        $this->ensureOwner($request, $job);

        if (! in_array($job->status, [Job::S_OPEN, 'posted'], true) || $job->selected_worker_id) {
            return response()->json([
                'success' => false,
                'message' => 'Kazi haiwezi kuhaririwa baada ya mfanyakazi kuchaguliwa.',
            ], 422);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:120'],
            'description' => ['sometimes', 'string', 'min:20', 'max:2000'],
            'price' => ['sometimes', 'integer', 'min:1000'],
            'address_text' => ['nullable', 'string', 'max:255'],
            'urgency' => ['nullable', 'in:normal,urgent,flexible'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = app(JobController::class)->handleImageUpload($request->file('image'), $job->image);
        }

        // Re-translate if title/description changed
        if (isset($validated['title']) || isset($validated['description'])) {
            $localized = TranslationService::ensureBothLanguages(
                $validated['title'] ?? $job->title,
                $validated['description'] ?? $job->description
            );
            $validated['title_sw'] = $localized['title_sw'];
            $validated['title_en'] = $localized['title_en'];
            $validated['description_sw'] = $localized['description_sw'];
            $validated['description_en'] = $localized['description_en'];
        }

        $job->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kazi imehaririwa.',
            'data' => ['job' => $this->presentJob($job->fresh(), full: true)],
        ]);
    }

    /**
     * DELETE /api/worker/jobs/{job} — cancel job (only if no worker selected/funded).
     */
    public function destroy(Request $request, Job $job): JsonResponse
    {
        $this->ensureWorker($request);
        $this->ensureOwner($request, $job);

        if (! in_array($job->status, [Job::S_OPEN, 'posted'], true) || $job->selected_worker_id) {
            return response()->json([
                'success' => false,
                'message' => 'Kazi haiwezi kufutwa baada ya mfanyakazi kuchaguliwa au malipo kufanyika.',
            ], 422);
        }

        DB::transaction(function () use ($job, $request) {
            // Mark all active applications as rejected (auto-cleanup)
            $job->applications()
                ->whereNotIn('status', [JobApplication::STATUS_WITHDRAWN, JobApplication::STATUS_REJECTED])
                ->update(['status' => JobApplication::STATUS_REJECTED, 'rejected_at' => now()]);

            $job->transitionStatus(Job::S_CANCELLED, $request->user()->id, 'Cancelled by poster');
        });

        return response()->json([
            'success' => true,
            'message' => 'Kazi imefutwa.',
        ]);
    }

    // ============================================================
    // Helpers
    // ============================================================

    private function createJob(int $userId, array $v, array $localized, ?string $imagePath, string $status, int $postingFee): Job
    {
        $job = Job::create([
            'user_id' => $userId,
            'category_id' => $v['category_id'],
            'title' => $v['title'],
            'title_sw' => $localized['title_sw'],
            'title_en' => $localized['title_en'],
            'description' => $v['description'],
            'description_sw' => $localized['description_sw'],
            'description_en' => $localized['description_en'],
            'image' => $imagePath,
            'price' => (int) $v['price'],
            'lat' => (float) $v['lat'],
            'lng' => (float) $v['lng'],
            'address_text' => $v['address_text'] ?? null,
            'urgency' => $v['urgency'] ?? 'normal',
            'status' => $status,
            'published_at' => $status === Job::S_OPEN ? now() : null,
            'poster_type' => 'mfanyakazi',
            'posting_fee' => $postingFee,
        ]);

        JobStatusLog::log($job, $status, $userId, 'Job created via worker API');

        return $job;
    }

    private function afterPosted(Job $job, $user): void
    {
        try {
            $user->notify(new JobStatusNotification($job, 'posted'));
        } catch (\Throwable $e) {
            Log::warning('JobStatus posted notify failed: '.$e->getMessage());
        }

        try {
            app(JobController::class)->notifyNearbyWorkers($job);
        } catch (\Throwable $e) {
            Log::warning('notifyNearbyWorkers failed: '.$e->getMessage());
        }
    }

    private function presentJob(Job $job, bool $full = false): array
    {
        $imageUrl = null;
        if ($job->image) {
            $imageUrl = asset('storage/'.$job->image);
            $absPath = storage_path('app/public/'.$job->image);
            if (file_exists($absPath)) {
                $imageUrl .= '?v='.filemtime($absPath);
            }
        }

        $base = [
            'id' => $job->id,
            'title' => $job->title,
            'price' => $job->price,
            'status' => $job->status,
            'image_url' => $imageUrl,
            'lat' => $job->lat,
            'lng' => $job->lng,
            'address_text' => $job->address_text,
            'urgency' => $job->urgency,
            'category' => $job->category ? ['id' => $job->category->id, 'name' => $job->category->name] : null,
            'applications_count' => $job->applications_count ?? null,
            'published_at' => $job->published_at?->toISOString(),
            'created_at' => $job->created_at?->toISOString(),
        ];

        if ($full) {
            $base = array_merge($base, [
                'description' => $job->description,
                'completion_code' => $job->completion_code,
                'agreed_amount' => $job->agreed_amount,
                'escrow_amount' => $job->escrow_amount,
                'selected_worker' => $job->selectedWorker ? [
                    'id' => $job->selectedWorker->id,
                    'name' => $job->selectedWorker->name,
                    'phone' => $job->selectedWorker->phone,
                ] : null,
                'accepted_worker' => $job->acceptedWorker ? [
                    'id' => $job->acceptedWorker->id,
                    'name' => $job->acceptedWorker->name,
                    'phone' => $job->acceptedWorker->phone,
                ] : null,
            ]);
        }

        return $base;
    }

    private function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'has_more' => $paginator->hasMorePages(),
        ];
    }
}
