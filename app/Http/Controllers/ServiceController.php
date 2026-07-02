<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\Review;
use App\Models\User;
use App\Rules\NoPhoneNumberInText;
use App\Services\LocationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_unless($this->canBrowseServices($user), 403, 'Mteja tu anaweza kuona huduma.');

        $listings = $this->buildListingsPaginator($request, $user);
        $categories = Category::orderBy('name')->get(['id', 'name', 'slug']);

        return view('services.index', compact('listings', 'categories'));
    }

    public function show(Request $request, Job $listing): View
    {
        $user = Auth::user();
        abort_unless($this->canBrowseServices($user), 403, 'Mteja tu anaweza kuona huduma.');
        abort_unless($this->isVisibleServiceListing($listing), 404);

        $listing->load(['category', 'user:id,name,phone,profile_photo_path,lat,lng,role']);
        $this->attachListingMeta(collect([$listing]), $user);

        return view('services.show', compact('listing'));
    }

    public function book(Request $request, Job $listing): RedirectResponse
    {
        $client = Auth::user();
        abort_unless(in_array($client->role, ['muhitaji', 'admin'], true), 403);

        if ($message = $this->bookingValidationError($listing, $client)) {
            return back()->withErrors(['error' => $message]);
        }

        $request->validate([
            'message' => ['nullable', 'string', 'max:1000', new NoPhoneNumberInText()],
        ]);

        $booking = $this->createServiceBooking($listing, $client);

        return redirect()
            ->route('jobs.fund', $booking)
            ->with('success', 'Umechagua mtoa huduma. Fanya malipo ya escrow kuendelea.');
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $this->canBrowseServices($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Mteja tu anaweza kuona huduma.',
            ], 403);
        }

        $listings = $this->buildListingsPaginator($request, $user);

        return response()->json([
            'success' => true,
            'data' => array_values($listings->items()),
            'pagination' => [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'per_page' => $listings->perPage(),
                'total' => $listings->total(),
                'has_more' => $listings->hasMorePages(),
            ],
            'filters' => [
                'category' => $request->query('category'),
                'search' => $request->query('search'),
                'distance' => $request->query('distance'),
            ],
            'user_location' => $user->hasLocation() ? [
                'lat' => $user->lat,
                'lng' => $user->lng,
            ] : null,
        ]);
    }

    public function apiShow(Request $request, Job $listing): JsonResponse
    {
        $user = $request->user();
        if (! $this->canBrowseServices($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Mteja tu anaweza kuona huduma.',
            ], 403);
        }

        if (! $this->isVisibleServiceListing($listing)) {
            return response()->json([
                'success' => false,
                'message' => 'Huduma haipatikani.',
            ], 404);
        }

        $listing->load([
            'category',
            'user:id,name,phone,profile_photo_path,lat,lng,role',
        ]);
        $this->attachListingMeta(collect([$listing]), $user);

        return response()->json([
            'success' => true,
            'data' => $this->formatListing($listing),
        ]);
    }

    public function apiBook(Request $request, Job $listing): JsonResponse
    {
        $client = $request->user();
        if (! in_array($client->role, ['muhitaji', 'admin'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Mteja tu anaweza kuagiza huduma.',
            ], 403);
        }

        if (! $this->isServiceListing($listing)) {
            return response()->json([
                'success' => false,
                'message' => 'Hii si huduma inayopatikana kwa kuagiza.',
            ], 404);
        }

        if ($message = $this->bookingValidationError($listing, $client)) {
            $status = str_contains($message, 'Tayari') ? 422 : (str_contains($message, 'mwenyewe') ? 403 : 422);

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        $request->validate([
            'message' => ['nullable', 'string', 'max:1000', new NoPhoneNumberInText()],
        ]);

        $booking = $this->createServiceBooking($listing, $client);
        $booking->load(['category', 'sourceListing', 'selectedWorker:id,name,phone,profile_photo_path']);

        return response()->json([
            'success' => true,
            'message' => 'Umechagua mtoa huduma. Fanya malipo ya escrow kuendelea.',
            'data' => [
                'booking' => $booking,
                'listing' => $listing->only(['id', 'title', 'price']),
                'provider' => $listing->user,
            ],
            'payment' => $this->paymentInstructions($booking),
        ], 201);
    }

    protected function bookingValidationError(Job $listing, User $client): ?string
    {
        if ($listing->isHidden()) {
            return 'Huduma hii haipatikani.';
        }

        if (! $this->isBookableStatus($listing->status)) {
            return 'Huduma hii haipokei maagizo kwa sasa.';
        }

        if ($listing->user_id === $client->id) {
            return 'Huwezi kuagiza huduma yako mwenyewe.';
        }

        if ($this->hasActiveBooking($listing, $client)) {
            return 'Tayari una oda inayosubiri malipo au inaendelea kwa huduma hii.';
        }

        return null;
    }

    protected function createServiceBooking(Job $listing, User $client): Job
    {
        return DB::transaction(function () use ($listing, $client) {
            return Job::create([
                'user_id' => $client->id,
                'category_id' => $listing->category_id,
                'title' => $listing->getAttributes()['title'] ?? $listing->title,
                'title_sw' => $listing->getAttributes()['title_sw'] ?? null,
                'title_en' => $listing->getAttributes()['title_en'] ?? null,
                'description' => $listing->getAttributes()['description'] ?? $listing->description,
                'description_sw' => $listing->getAttributes()['description_sw'] ?? null,
                'description_en' => $listing->getAttributes()['description_en'] ?? null,
                'image' => $listing->image,
                'price' => $listing->price,
                'agreed_amount' => $listing->price,
                'lat' => $listing->lat,
                'lng' => $listing->lng,
                'address_text' => $listing->address_text,
                'poster_type' => 'muhitaji',
                'engagement_type' => Job::ENGAGEMENT_SERVICE_BOOKING,
                'source_listing_id' => $listing->id,
                'selected_worker_id' => $listing->user_id,
                'status' => Job::S_AWAITING_PAYMENT,
            ]);
        });
    }

    protected function canBrowseServices(?User $user): bool
    {
        return $user && in_array($user->role, ['muhitaji', 'admin'], true);
    }

    protected function isServiceListing(Job $job): bool
    {
        return $job->isCatalogListing();
    }

    protected function isVisibleServiceListing(Job $listing): bool
    {
        return $this->isServiceListing($listing)
            && ! $listing->isHidden()
            && in_array($listing->status, [Job::S_OPEN, 'posted'], true);
    }

    protected function isBookableStatus(string $status): bool
    {
        return in_array($status, [Job::S_OPEN, 'posted'], true);
    }

    protected function hasActiveBooking(Job $listing, User $client): bool
    {
        return Job::query()
            ->serviceBookings()
            ->where('source_listing_id', $listing->id)
            ->where('user_id', $client->id)
            ->whereIn('status', Job::activeBookingStatuses())
            ->exists();
    }

    protected function buildListingsPaginator(Request $request, User $user): LengthAwarePaginator
    {
        $search = trim((string) $request->query('search', ''));
        $category = $request->query('category');
        $distance = $request->query('distance');

        $query = Job::query()
            ->with(['category', 'user:id,name,phone,profile_photo_path,lat,lng,role'])
            ->publiclyVisible()
            ->where(function ($q) {
                $q->where('engagement_type', Job::ENGAGEMENT_SERVICE_LISTING)
                    ->orWhere(function ($qq) {
                        $qq->where('engagement_type', Job::ENGAGEMENT_JOB_REQUEST)
                            ->where('poster_type', 'mfanyakazi')
                            ->whereNull('source_listing_id');
                    });
            })
            ->whereIn('status', [Job::S_OPEN, 'posted'])
            ->when($category, fn ($q) => $q->whereHas(
                'category',
                fn ($qq) => $qq->where('slug', $category)->orWhere('id', $category)
            ))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('address_text', 'like', "%{$search}%");
                });
            })
            ->latest();

        $listings = $query->paginate(12);
        $this->attachListingMeta($listings->getCollection(), $user);

        if ($distance) {
            $maxDistance = (float) $distance;
            $filtered = $listings->getCollection()->filter(function ($listing) use ($maxDistance) {
                return $listing->distance_info['distance'] !== null
                    && $listing->distance_info['distance'] <= $maxDistance;
            });
            $listings->setCollection($filtered->values());
        }

        $sorted = $listings->getCollection()->sortBy(fn ($listing) => $this->listingSortKey($listing));
        $listings->setCollection($sorted->values());

        return $listings;
    }

    protected function attachListingMeta($listings, User $user): void
    {
        $listings->transform(function (Job $listing) use ($user) {
            if ($user->hasLocation()) {
                $listing->distance_info = LocationService::getDistanceInfo(
                    $user->lat,
                    $user->lng,
                    $listing->lat,
                    $listing->lng
                );
            } else {
                $listing->distance_info = [
                    'distance' => null,
                    'category' => 'unknown',
                    'color' => '#6b7280',
                    'bg_color' => '#f3f4f6',
                    'text_color' => '#6b7280',
                    'label' => __('messages.distance.unknown'),
                ];
            }

            $listing->image_url = $this->listingImageUrl($listing);
            $listing->provider = $this->formatProvider($listing->user);

            return $listing;
        });
    }

    protected function formatListing(Job $listing): array
    {
        $data = $listing->toArray();
        $data['provider'] = $this->formatProvider($listing->user);
        $data['image_url'] = $listing->image_url ?? $this->listingImageUrl($listing);

        return $data;
    }

    protected function formatProvider(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        $data = $user->toArray();
        $avg = Review::where('reviewee_id', $user->id)->avg('rating');
        $count = Review::where('reviewee_id', $user->id)->count();
        $data['average_rating'] = $avg ? round((float) $avg, 1) : null;
        $data['reviews_count'] = $count;

        return $data;
    }

    protected function listingImageUrl(Job $listing): ?string
    {
        if (! $listing->image) {
            return null;
        }

        $url = asset('storage/'.$listing->image);
        if (Storage::disk('public')->exists($listing->image)) {
            $timestamp = filemtime(storage_path('app/public/'.$listing->image));

            return $url.'?v='.$timestamp;
        }

        return $url;
    }

    protected function listingSortKey(Job $listing): int|float
    {
        $distance = $listing->distance_info['distance'] ?? null;
        $category = $listing->distance_info['category'] ?? 'unknown';

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

    protected function paymentInstructions(Job $booking): array
    {
        $amount = (int) ($booking->agreed_amount ?? $booking->price);

        return [
            'booking_id' => $booking->id,
            'amount' => $amount,
            'currency' => 'TZS',
            'status' => $booking->status,
            'fund_wallet_url' => url("/api/jobs/{$booking->id}/fund/wallet"),
            'fund_external_url' => url("/api/jobs/{$booking->id}/fund/external"),
            'fund_poll_url' => url("/api/jobs/{$booking->id}/fund/poll"),
            'web_fund_url' => route('jobs.fund', $booking),
        ];
    }
}
