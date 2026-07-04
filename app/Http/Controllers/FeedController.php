<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Services\LocationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FeedController extends Controller
{
    public function index(Request $r)
    {
        $role = Auth::user()->role ?? null;
        if (! in_array($role, ['mfanyakazi', 'admin'], true)) {
            abort(403, 'Huna ruhusa (mfanyakazi/admin tu).');
        }

        $cat = $r->query('category');
        $user = Auth::user();
        $jobs = $this->buildFeedPaginator($r, $user);

        return view('feed.index', compact('jobs', 'cat'));
    }

    public function apiIndex(Request $r)
    {
        $role = Auth::user()->role ?? null;
        if (! in_array($role, ['mfanyakazi', 'muhitaji', 'admin'], true)) {
            return response()->json([
                'error' => 'Huna ruhusa.',
                'status' => 'forbidden',
            ], 403);
        }

        $cat = $r->query('category');
        $distance = $r->query('distance');
        $user = Auth::user();
        $jobs = $this->buildFeedPaginator($r, $user);

        return response()->json([
            'jobs' => array_values($jobs->items()),
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
                'has_more' => $jobs->hasMorePages(),
            ],
            'filters' => [
                'category' => $cat,
                'distance' => $distance,
            ],
            'user_location' => $user->hasLocation() ? [
                'lat' => $user->lat,
                'lng' => $user->lng,
            ] : null,
            'status' => 'success',
        ]);
    }

    public function apiMap(Request $r)
    {
        $role = Auth::user()->role ?? null;
        if (! in_array($role, ['mfanyakazi', 'muhitaji', 'admin'], true)) {
            return response()->json([
                'error' => 'Huna ruhusa.',
                'status' => 'forbidden',
            ], 403);
        }

        $user = Auth::user();
        $jobs = Job::with('category', 'muhitaji')
            ->publiclyVisible()
            ->jobRequests()
            ->where('poster_type', 'muhitaji')
            ->whereIn('status', [Job::S_OPEN, 'posted'])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('lat', '!=', 0)
            ->where('lng', '!=', 0)
            ->get();

        $this->attachDistanceAndImages($jobs, $user);

        return response()->json([
            'jobs' => array_values($jobs->values()->all()),
            'user_location' => $user->hasLocation() ? [
                'lat' => $user->lat,
                'lng' => $user->lng,
            ] : null,
            'total_jobs' => $jobs->count(),
            'status' => 'success',
        ]);
    }

    private function buildFeedPaginator(Request $r, $user): LengthAwarePaginator
    {
        $cat = $r->query('category');
        $distance = $r->query('distance');

        $jobs = Job::with('category', 'muhitaji')
            ->publiclyVisible()
            ->jobRequests()
            ->where('poster_type', 'muhitaji')
            ->whereIn('status', [Job::S_OPEN, 'posted'])
            ->when($cat, fn ($q) => $q->whereHas('category', fn ($qq) => $qq->where('slug', $cat)))
            ->latest()
            ->paginate(12);

        $this->attachDistanceAndImages($jobs->getCollection(), $user);

        if ($distance) {
            $maxDistance = (float) $distance;
            $filteredJobs = $jobs->getCollection()->filter(function ($job) use ($maxDistance) {
                return $job->distance_info['distance'] !== null
                    && $job->distance_info['distance'] <= $maxDistance;
            });
            $jobs->setCollection($filteredJobs->values());
        }

        $sortedJobs = $jobs->getCollection()->sortBy(fn ($job) => $this->feedSortKey($job));
        $jobs->setCollection($sortedJobs->values());

        return $jobs;
    }

    private function attachDistanceAndImages($jobs, $user): void
    {
        $jobs->transform(function ($job) use ($user) {
            if ($user->hasLocation()) {
                $job->distance_info = LocationService::getDistanceInfo(
                    $user->lat,
                    $user->lng,
                    $job->lat,
                    $job->lng
                );
            } else {
                $job->distance_info = [
                    'distance' => null,
                    'category' => 'unknown',
                    'color' => '#6b7280',
                    'bg_color' => '#f3f4f6',
                    'text_color' => '#6b7280',
                    'label' => __('messages.distance.unknown'),
                ];
            }

            $job->image_url = $this->jobImageUrl($job);

            return $job;
        });
    }

    private function jobImageUrl(Job $job): ?string
    {
        if (! $job->image) {
            return null;
        }

        $url = asset('storage/'.$job->image);
        if (Storage::disk('public')->exists($job->image)) {
            $timestamp = filemtime(storage_path('app/public/'.$job->image));

            return $url.'?v='.$timestamp;
        }

        return $url;
    }

    private function feedSortKey(Job $job): int|float
    {
        $distance = $job->distance_info['distance'] ?? null;
        $category = $job->distance_info['category'] ?? 'unknown';

        return match ($category) {
            'near' => 0,
            'moderate' => 1,
            'far' => 2,
            'no_user_location' => 3,
            'no_job_location' => 4,
            'unknown' => 5,
            default => $distance ?? 999,
        };
    }
}
