<?php

namespace App\Http\Controllers;

use App\Models\{Job, Category, Wallet, WalletTransaction, Setting, User};
use App\Services\ZenoPayService;
use App\Notifications\JobAvailableNotification;
use App\Notifications\JobStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobController extends Controller
{
    private function ensureMuhitajiOrAdmin(): void
    {
        $role = Auth::user()->role ?? null;
        if (!in_array($role, ['muhitaji', 'admin'], true)) {
            abort(403, 'Huna ruhusa (muhitaji/admin tu).');
        }
    }

    /**
     * Handle image upload and resizing
     * Returns the stored image path or null
     */
    private function handleImageUpload($file, $existingImage = null): ?string
    {
        if (!$file || !$file->isValid()) {
            \Log::info('handleImageUpload: No file or invalid file', [
                'hasFile' => $file !== null,
                'isValid' => $file ? $file->isValid() : false,
                'existingImage' => $existingImage
            ]);
            return $existingImage; // Return existing if no new file
        }

        \Log::info('handleImageUpload: Starting upload', [
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension()
        ]);

        // Validate image
        $validated = validator(['image' => $file], [
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'] // 5MB max
        ])->validate();

        // Delete old image if exists
        if ($existingImage && Storage::disk('public')->exists($existingImage)) {
            Storage::disk('public')->delete($existingImage);
        }

        // Check if GD extension is available
        $gdAvailable = extension_loaded('gd') && function_exists('imagecreatefromjpeg');

        if (!$gdAvailable) {
            // GD not available - just save the file as-is using direct move
            $extension = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            $uniqueName = Str::random(40) . '.' . $extension;
            $filename = 'jobs/' . $uniqueName;

            // Ensure directory exists
            $dir = storage_path('app/public/jobs');
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    \Log::error('Image upload - Failed to create jobs directory', ['dir' => $dir]);
                    return $existingImage;
                }
            }

            // Use move() directly to ensure file is saved
            $fullPath = $dir . DIRECTORY_SEPARATOR . $uniqueName;

            try {
                $file->move($dir, $uniqueName);

                // Verify the file was saved correctly
                if (file_exists($fullPath)) {
                    @chmod($fullPath, 0644);
                    \Log::info('Image uploaded (no GD) - File saved successfully', [
                        'filename' => $filename,
                        'fullPath' => $fullPath,
                        'fileSize' => filesize($fullPath),
                        'isReadable' => is_readable($fullPath)
                    ]);
                    return $filename;
                } else {
                    \Log::error('Image upload (no GD) - File not found after move', [
                        'filename' => $filename,
                        'fullPath' => $fullPath,
                        'dir' => $dir
                    ]);
                    return $existingImage;
                }
            } catch (\Exception $e) {
                \Log::error('Image upload (no GD) - Exception during move', [
                    'error' => $e->getMessage(),
                    'filename' => $filename,
                    'dir' => $dir
                ]);
                return $existingImage;
            }
        }

        // GD is available - proceed with resizing
        // Get image info
        $imageInfo = getimagesize($file->getRealPath());
        if (!$imageInfo) {
            return $existingImage; // Invalid image
        }

        list($width, $height, $type) = $imageInfo;

        // Max dimensions (1200px width, maintain aspect ratio)
        $maxWidth = 1200;
        $maxHeight = 1200;

        // Calculate new dimensions
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int) ($width * $ratio);
            $newHeight = (int) ($height * $ratio);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        // Generate unique filename (always save as jpg for consistency when resizing)
        $filename = 'jobs/' . Str::random(40) . '.jpg';
        $fullPath = storage_path('app/public/' . $filename);

        // Ensure directory exists with proper permissions
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            // Set permissions explicitly (Windows compatible)
            if (is_dir($dir)) {
                chmod($dir, 0755);
            }
        }

        // Create image resource based on type
        $source = null;
        switch ($type) {
            case IMAGETYPE_JPEG:
                if (function_exists('imagecreatefromjpeg')) {
                    $source = imagecreatefromjpeg($file->getRealPath());
                }
                break;
            case IMAGETYPE_PNG:
                if (function_exists('imagecreatefrompng')) {
                    $source = imagecreatefrompng($file->getRealPath());
                }
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $source = imagecreatefromwebp($file->getRealPath());
                }
                break;
            default:
                // Unsupported type - save as-is
                $extension = $file->getClientOriginalExtension();
                $filename = 'jobs/' . Str::random(40) . '.' . $extension;

                // Ensure directory exists
                $dir = storage_path('app/public/jobs');
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                    chmod($dir, 0755);
                }

                $stored = $file->storeAs('public', $filename);

                // Set file permissions
                $fullPath = storage_path('app/public/' . $filename);
                if (file_exists($fullPath)) {
                    chmod($fullPath, 0644);
                    \Log::info('Image uploaded (unsupported type) - File saved', [
                        'filename' => $filename,
                        'storedPath' => $stored,
                        'fullPath' => $fullPath,
                        'fileSize' => filesize($fullPath)
                    ]);
                } else {
                    \Log::error('Image upload (unsupported type) - File not found after storeAs', [
                        'filename' => $filename,
                        'storedPath' => $stored,
                        'fullPath' => $fullPath
                    ]);
                }

                return $filename;
        }

        if (!$source) {
            // If we can't create source, save file as-is
            $extension = $file->getClientOriginalExtension();
            $filename = 'jobs/' . Str::random(40) . '.' . $extension;

            // Ensure directory exists
            $dir = storage_path('app/public/jobs');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                chmod($dir, 0755);
            }

            $stored = $file->storeAs('public', $filename);

            // Set file permissions
            $fullPath = storage_path('app/public/' . $filename);
            if (file_exists($fullPath)) {
                chmod($fullPath, 0644);
                \Log::info('Image uploaded (no source) - File saved', [
                    'filename' => $filename,
                    'storedPath' => $stored,
                    'fullPath' => $fullPath,
                    'fileSize' => filesize($fullPath)
                ]);
            } else {
                \Log::error('Image upload (no source) - File not found after storeAs', [
                    'filename' => $filename,
                    'storedPath' => $stored,
                    'fullPath' => $fullPath
                ]);
            }

            return $filename;
        }

        // Create resized image
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefill($resized, 0, 0, $transparent);
        }

        // Resize
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save as JPEG with 85% quality
        $saved = @imagejpeg($resized, $fullPath, 85);

        if (!$saved || !file_exists($fullPath)) {
            \Log::error('Failed to save resized image', [
                'fullPath' => $fullPath,
                'filename' => $filename,
                'saved' => $saved,
                'fileExists' => file_exists($fullPath),
                'directoryExists' => is_dir(dirname($fullPath)),
                'directoryWritable' => is_writable(dirname($fullPath))
            ]);
            // Fallback: try to save original file using Laravel's storeAs
            $extension = $file->getClientOriginalExtension();
            $filename = 'jobs/' . Str::random(40) . '.' . $extension;
            $dir = storage_path('app/public/jobs');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                chmod($dir, 0755);
            }
            $stored = $file->storeAs('public', $filename);
            $fullPath = storage_path('app/public/' . $filename);

            \Log::info('Image saved using fallback method', [
                'filename' => $filename,
                'stored' => $stored,
                'fullPath' => $fullPath,
                'fileExists' => file_exists($fullPath)
            ]);
        }

        // Set file permissions (Windows compatible)
        if (file_exists($fullPath)) {
            @chmod($fullPath, 0644);

            // Verify file is readable and has content
            $fileSize = filesize($fullPath);
            $isReadable = is_readable($fullPath);

            // Log successful upload for debugging
            \Log::info('Image uploaded successfully', [
                'filename' => $filename,
                'fullPath' => $fullPath,
                'fileExists' => true,
                'fileSize' => $fileSize,
                'isReadable' => $isReadable,
                'permissions' => substr(sprintf('%o', fileperms($fullPath)), -4),
                'expectedUrl' => asset('storage/' . $filename)
            ]);

            // Verify the file has content
            if ($fileSize === 0) {
                \Log::error('Image file is empty after save', [
                    'filename' => $filename,
                    'fullPath' => $fullPath
                ]);
            }
        } else {
            \Log::error('Image file not found after save', [
                'filename' => $filename,
                'fullPath' => $fullPath,
                'directoryExists' => is_dir(dirname($fullPath)),
                'directoryWritable' => is_writable(dirname($fullPath))
            ]);
            // Return null so we don't save invalid path to database
            imagedestroy($source);
            if (isset($resized))
                imagedestroy($resized);
            return null;
        }

        // Clean up
        imagedestroy($source);
        imagedestroy($resized);

        return $filename;
    }

    public function create()
    {
        $this->ensureMuhitajiOrAdmin();
        return view('jobs.create', ['categories' => Category::all()]);
    }

    public function store(Request $r, ZenoPayService $zeno)
    {
        $this->ensureMuhitajiOrAdmin();

        $r->validate([
            'title' => ['required', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:500'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'phone' => ['required', 'regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // 5MB max
        ], [
            'phone.regex' => 'Weka 06/07xxxxxxxx au 2556/2557xxxxxxxx.',
            'lat.required' => 'Eneo la kazi ni lazima. Tafadhali weka pini eneo la kazi kwenye ramani.',
            'lng.required' => 'Eneo la kazi ni lazima. Tafadhali weka pini eneo la kazi kwenye ramani.',
            'lat.between' => 'Latitude si sahihi. Tafadhali weka eneo sahihi kwenye ramani.',
            'lng.between' => 'Longitude si sahihi. Tafadhali weka eneo sahihi kwenye ramani.',
            'image.image' => 'Faili lazima iwe picha (jpeg, jpg, png, au webp).',
            'image.max' => 'Picha haipaswi kuwa kubwa zaidi ya 5MB.',
        ]);

        // Handle image upload
        $imagePath = $this->handleImageUpload($r->file('image'));

        // Log the image path for debugging
        if ($imagePath) {
            $fullPath = storage_path('app/public/' . $imagePath);
            \Log::info('Job creation (store) - Image path saved', [
                'imagePath' => $imagePath,
                'fullStoragePath' => $fullPath,
                'fileExists' => file_exists($fullPath),
                'fileSize' => file_exists($fullPath) ? filesize($fullPath) : 0,
                'expectedUrl' => asset('storage/' . $imagePath),
                'storageDiskExists' => Storage::disk('public')->exists($imagePath)
            ]);
        } else {
            \Log::info('Job creation (store) - No image uploaded');
        }

        // Verify image file exists before saving to database
        if ($imagePath) {
            $imageFullPath = storage_path('app/public/' . $imagePath);
            if (!file_exists($imageFullPath)) {
                \Log::error('Image path provided but file does not exist - not saving to database', [
                    'imagePath' => $imagePath,
                    'fullPath' => $imageFullPath
                ]);
                $imagePath = null; // Don't save invalid path
            } else {
                \Log::info('Image verified before saving to database', [
                    'imagePath' => $imagePath,
                    'fullPath' => $imageFullPath,
                    'fileSize' => filesize($imageFullPath)
                ]);
            }
        }

        $job = Job::create([
            'user_id' => Auth::id(),
            'category_id' => (int) $r->input('category_id'),
            'title' => $r->input('title'),
            'description' => $r->input('description'),
            'image' => $imagePath, // Will be null if file doesn't exist
            'price' => (int) $r->input('price'),
            'lat' => (float) $r->input('lat'),
            'lng' => (float) $r->input('lng'),
            'address_text' => $r->input('address_text'),

            'status' => Setting::get('payments_enabled', '1') == '1' ? 'pending_payment' : 'posted',
            'published_at' => Setting::get('payments_enabled', '1') == '1' ? null : now(),
        ]);

        // Log final state after database save
        \Log::info('Job created with image', [
            'jobId' => $job->id,
            'imagePath' => $job->image,
            'imageUrl' => $job->image ? asset('storage/' . $job->image) : null,
            'fileExists' => $job->image ? file_exists(storage_path('app/public/' . $job->image)) : false
        ]);

        if (Setting::get('payments_enabled', '1') == '0') {
            // Notify client triggers
            try {
                Auth::user()->notify(new JobStatusNotification($job, 'posted'));
            } catch (\Exception $e) {
            }

            // Notify workers
            $this->notifyNearbyWorkers($job);

            return redirect()->route('my.jobs')->with('success', 'Kazi imechapishwa kwa mafanikio!');
        }

        $orderId = (string) Str::ulid();
        $job->payment()->create([
            'order_id' => $orderId,
            'amount' => $job->price,
            'status' => 'PENDING',
        ]);

        $buyer = Auth::user();
        $payload = [
            'order_id' => $orderId,
            'buyer_email' => $buyer?->email ?? 'client@tendapoa.local',
            'buyer_name' => $buyer?->name ?? 'Client',
            'buyer_phone' => $r->input('phone'),
            'amount' => $job->price,
            // 'webhook_url' => route('zeno.webhook'), // User requested polling only
        ];

        $res = $zeno->startPayment($payload);
        if (!$res['ok']) {
            return back()->withErrors(['pay' => 'Imeshindikana kuanzisha malipo. Jaribu tena.']);
        }

        // Notify client pending payment
        try {
            Auth::user()->notify(new JobStatusNotification($job, 'pending'));
        } catch (\Exception $e) {
        }

        return redirect()->route('jobs.pay.wait', $job);
    }

    public function edit(Job $job)
    {
        $this->ensureMuhitajiOrAdmin();

        // Only allow editing if job is posted or assigned (not in progress or completed)
        if (!in_array($job->status, ['posted', 'assigned'])) {
            return back()->withErrors(['edit' => 'Huwezi kubadilisha kazi ambayo imeanza au imekamilika.']);
        }

        // Only job owner can edit
        if ($job->user_id !== Auth::id()) {
            abort(403, 'Huna ruhusa ya kubadilisha kazi hii.');
        }

        return view('jobs.edit', [
            'job' => $job,
            'categories' => Category::all()
        ]);
    }

    public function update(Request $r, Job $job, ZenoPayService $zeno)
    {
        $this->ensureMuhitajiOrAdmin();

        // Only allow editing if job is posted or assigned
        if (!in_array($job->status, ['posted', 'assigned'])) {
            return back()->withErrors(['edit' => 'Huwezi kubadilisha kazi ambayo imeanza au imekamilika.']);
        }

        // Only job owner can edit
        if ($job->user_id !== Auth::id()) {
            abort(403, 'Huna ruhusa ya kubadilisha kazi hii.');
        }

        $r->validate([
            'title' => ['required', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:500'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // 5MB max
        ], [
            'lat.required' => 'Eneo la kazi ni lazima. Tafadhali weka pini eneo la kazi kwenye ramani.',
            'lng.required' => 'Eneo la kazi ni lazima. Tafadhali weka pini eneo la kazi kwenye ramani.',
            'lat.between' => 'Latitude si sahihi. Tafadhali weka eneo sahihi kwenye ramani.',
            'lng.between' => 'Longitude si sahihi. Tafadhali weka eneo sahihi kwenye ramani.',
            'image.image' => 'Faili lazima iwe picha (jpeg, jpg, png, au webp).',
            'image.max' => 'Picha haipaswi kuwa kubwa zaidi ya 5MB.',
        ]);

        $newPrice = (int) $r->input('price');
        $oldPrice = $job->price;
        $priceDifference = $newPrice - $oldPrice;

        // Validate price increase only
        if ($priceDifference < 0) {
            return back()->withErrors(['price' => 'Huwezi kupunguza bei ya kazi. Unaweza kuongeza tu.']);
        }

        // Handle image upload (keep existing if no new image)
        $imagePath = $this->handleImageUpload($r->file('image'), $job->image);

        // Update job details
        $updateData = [
            'title' => $r->input('title'),
            'category_id' => (int) $r->input('category_id'),
            'description' => $r->input('description'),
            'price' => $newPrice,
            'lat' => (float) $r->input('lat'),
            'lng' => (float) $r->input('lng'),
            'address_text' => $r->input('address_text'),
        ];

        if ($imagePath !== null) {
            $updateData['image'] = $imagePath;
        }

        $job->update($updateData);

        // If price increased, process additional payment
        if ($priceDifference > 0 && Setting::get('payments_enabled', '1') == '1') {
            $orderId = (string) Str::ulid();
            $job->payment()->create([
                'order_id' => $orderId,
                'amount' => $priceDifference,
                'status' => 'PENDING',
            ]);

            $buyer = Auth::user();
            $payload = [
                'order_id' => $orderId,
                'buyer_email' => $buyer?->email ?? 'client@tendapoa.local',
                'buyer_name' => $buyer?->name ?? 'Client',
                'buyer_phone' => $buyer?->phone ?? '000000000',
                'amount' => $priceDifference,
                // 'webhook_url' => route('zeno.webhook'), // User requested polling only
            ];

            $res = $zeno->startPayment($payload);
            if (!$res['ok']) {
                return back()->withErrors(['pay' => 'Imeshindikana kuanzisha malipo ya ziada. Jaribu tena.']);
            }

            return redirect()->route('jobs.pay.wait', $job)
                ->with('success', 'Kazi imebadilishwa! Malipo ya ziada ya TZS ' . number_format($priceDifference) . ' yanahitajika.');
        }

        return redirect()->route('my.jobs')
            ->with('success', 'Kazi imebadilishwa kwa mafanikio!');
    }

    // API Methods for Job Editing
    public function apiEdit(Job $job)
    {
        $this->ensureMuhitajiOrAdmin();

        // Only allow editing if job is posted or assigned
        if (!in_array($job->status, ['posted', 'assigned'])) {
            return response()->json([
                'error' => 'Huwezi kubadilisha kazi ambayo imeanza au imekamilika.',
                'status' => 'error'
            ], 400);
        }

        // Only job owner can edit
        if ($job->user_id !== Auth::id()) {
            return response()->json([
                'error' => 'Huna ruhusa ya kubadilisha kazi hii.',
                'status' => 'forbidden'
            ], 403);
        }

        return response()->json([
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'description' => $job->description,
                'price' => $job->price,
                'category_id' => $job->category_id,
                'lat' => $job->lat,
                'lng' => $job->lng,
                'address_text' => $job->address_text,
                'status' => $job->status,
                'created_at' => $job->created_at,
                'updated_at' => $job->updated_at,
            ],
            'categories' => \App\Models\Category::all(),
            'can_edit' => true,
            'status' => 'success'
        ]);
    }

    public function apiUpdate(Request $r, Job $job, ZenoPayService $zeno)
    {
        $this->ensureMuhitajiOrAdmin();

        // Only allow editing if job is posted or assigned
        if (!in_array($job->status, ['posted', 'assigned'])) {
            return response()->json([
                'error' => 'Huwezi kubadilisha kazi ambayo imeanza au imekamilika.',
                'status' => 'error'
            ], 400);
        }

        // Only job owner can edit
        if ($job->user_id !== Auth::id()) {
            return response()->json([
                'error' => 'Huna ruhusa ya kubadilisha kazi hii.',
                'status' => 'forbidden'
            ], 403);
        }

        $r->validate([
            'title' => ['required', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:500'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // 5MB max
        ], [
            'lat.required' => 'Eneo la kazi ni lazima.',
            'lng.required' => 'Eneo la kazi ni lazima.',
            'lat.between' => 'Latitude si sahihi.',
            'lng.between' => 'Longitude si sahihi.',
            'image.image' => 'Faili lazima iwe picha (jpeg, jpg, png, au webp).',
            'image.max' => 'Picha haipaswi kuwa kubwa zaidi ya 5MB.',
        ]);

        $newPrice = (int) $r->input('price');
        $oldPrice = $job->price;
        $priceDifference = $newPrice - $oldPrice;

        // Validate price increase only
        if ($priceDifference < 0) {
            return response()->json([
                'error' => 'Huwezi kupunguza bei ya kazi. Unaweza kuongeza tu.',
                'status' => 'validation_error',
                'field' => 'price'
            ], 422);
        }

        // Handle image upload (keep existing if no new image)
        $imagePath = $this->handleImageUpload($r->file('image'), $job->image);

        // Update job details
        $updateData = [
            'title' => $r->input('title'),
            'category_id' => (int) $r->input('category_id'),
            'description' => $r->input('description'),
            'price' => $newPrice,
            'lat' => (float) $r->input('lat'),
            'lng' => (float) $r->input('lng'),
            'address_text' => $r->input('address_text'),
        ];

        if ($imagePath !== null) {
            $updateData['image'] = $imagePath;
        }

        $job->update($updateData);

        $imageUrl = null;
        if ($job->image) {
            $imageUrl = asset('storage/' . $job->image);
            // Add cache busting
            if (Storage::disk('public')->exists($job->image)) {
                $timestamp = filemtime(storage_path('app/public/' . $job->image));
                $imageUrl = asset('storage/' . $job->image) . '?v=' . $timestamp;
            }
        }

        $response = [
            'message' => 'Kazi imebadilishwa kwa mafanikio!',
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'price' => $job->price,
                'status' => $job->status,
                'image' => $imageUrl,
                'updated_at' => $job->updated_at,
            ],
            'status' => 'success'
        ];

        // If price increased, process additional payment
        if ($priceDifference > 0) {
            $orderId = (string) Str::ulid();
            $job->payment()->create([
                'order_id' => $orderId,
                'amount' => $priceDifference,
                'status' => 'PENDING',
            ]);

            $buyer = Auth::user();
            $payload = [
                'order_id' => $orderId,
                'buyer_email' => $buyer?->email ?? 'client@tendapoa.local',
                'buyer_name' => $buyer?->name ?? 'Client',
                'buyer_phone' => $buyer?->phone ?? '000000000',
                'amount' => $priceDifference,
                // 'webhook_url' => route('zeno.webhook'), // User requested polling only
            ];

            $res = $zeno->startPayment($payload);
            if (!$res['ok']) {
                return response()->json([
                    'error' => 'Imeshindikana kuanzisha malipo ya ziada. Jaribu tena.',
                    'status' => 'payment_error'
                ], 500);
            }

            $response['payment_required'] = true;
            $response['payment_amount'] = $priceDifference;
            $response['payment_url'] = route('jobs.pay.wait', $job);
            $response['message'] = 'Kazi imebadilishwa! Malipo ya ziada ya TZS ' . number_format($priceDifference) . ' yanahitajika.';
        }

        return response()->json($response);
    }

    public function apiStore(Request $r, ZenoPayService $zeno)
    {
        $this->ensureMuhitajiOrAdmin();

        $r->validate([
            'title' => ['required', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:500'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'phone' => ['required', 'regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // 5MB max
        ], [
            'phone.regex' => 'Weka 06/07xxxxxxxx au 2556/2557xxxxxxxx.',
            'lat.required' => 'Eneo la kazi ni lazima.',
            'lng.required' => 'Eneo la kazi ni lazima.',
            'lat.between' => 'Latitude si sahihi.',
            'lng.between' => 'Longitude si sahihi.',
            'image.image' => 'Faili lazima iwe picha (jpeg, jpg, png, au webp).',
            'image.max' => 'Picha haipaswi kuwa kubwa zaidi ya 5MB.',
        ]);

        // Handle image upload
        $imagePath = $this->handleImageUpload($r->file('image'));

        $paymentsEnabled = Setting::get('payments_enabled', '1') == '1';
        $status = $paymentsEnabled ? 'pending_payment' : 'posted';
        $publishedAt = $paymentsEnabled ? null : now();

        $job = Job::create([
            'user_id' => Auth::id(),
            'category_id' => (int) $r->input('category_id'),
            'title' => $r->input('title'),
            'description' => $r->input('description'),
            'image' => $imagePath,
            'price' => (int) $r->input('price'),
            'lat' => (float) $r->input('lat'),
            'lng' => (float) $r->input('lng'),
            'address_text' => $r->input('address_text'),
            'status' => $status,
            'published_at' => $publishedAt,
        ]);

        $imageUrl = null;
        if ($job->image) {
            $imageUrl = asset('storage/' . $job->image);
            if (Storage::disk('public')->exists($job->image)) {
                $timestamp = filemtime(storage_path('app/public/' . $job->image));
                $imageUrl = asset('storage/' . $job->image) . '?v=' . $timestamp;
            }
        }

        if (!$paymentsEnabled) {
            // Notify client triggers
            try {
                Auth::user()->notify(new JobStatusNotification($job, 'posted'));
            } catch (\Exception $e) {
            }

            // Notify workers
            $this->notifyNearbyWorkers($job);

            return response()->json([
                'message' => 'Kazi imechapishwa kwa mafanikio!',
                'job' => [
                    'id' => $job->id,
                    'title' => $job->title,
                    'price' => $job->price,
                    'status' => $job->status,
                    'image' => $imageUrl,
                ],
                'status' => 'success'
            ]);
        }

        // Handle Payment
        $orderId = (string) Str::ulid();
        $job->payment()->create([
            'order_id' => $orderId,
            'amount' => $job->price,
            'status' => 'PENDING',
        ]);

        $buyer = Auth::user();
        $payload = [
            'order_id' => $orderId,
            'buyer_email' => $buyer?->email ?? 'client@tendapoa.local',
            'buyer_name' => $buyer?->name ?? 'Client',
            'buyer_phone' => $r->input('phone'),
            'amount' => $job->price,
        ];

        $res = $zeno->startPayment($payload);
        if (!$res['ok']) {
            return response()->json([
                'error' => 'Imeshindikana kuanzisha malipo. Jaribu tena.',
                'status' => 'payment_error'
            ], 500);
        }

        // Notify client pending payment
        try {
            Auth::user()->notify(new JobStatusNotification($job, 'pending'));
        } catch (\Exception $e) {
        }

        return response()->json([
            'message' => 'Kazi imechapishwa! Malipo yanahitajika.',
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'price' => $job->price,
                'status' => $job->status,
                'image' => $imageUrl,
            ],
            'payment_url' => route('jobs.pay.wait', $job),
            'status' => 'success'
        ]);
    }

    public function wait(Job $job)
    {
        $this->ensureMuhitajiOrAdmin();
        $job->load('payment');
        return view('jobs.wait', ['job' => $job]);
    }

    public function retryPayment(Job $job, ZenoPayService $zeno)
    {
        $this->ensureMuhitajiOrAdmin();

        if ($job->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'Huna ruhusa.');
        }

        if ($job->status !== 'pending_payment') {
            return back()->with('error', 'Kazi hii haidaiwi malipo.');
        }

        // Futa payment ya zamani iliyo pending kama ipo
        $job->payment()->where('status', 'PENDING')->delete();

        // Create new
        $orderId = (string) Str::ulid();
        $job->payment()->create([
            'order_id' => $orderId,
            'amount' => $job->price,
            'status' => 'PENDING',
        ]);

        $buyer = Auth::user();
        $payload = [
            'order_id' => $orderId,
            'buyer_email' => $buyer?->email ?? 'client@tendapoa.local',
            'buyer_name' => $buyer?->name ?? 'Client',
            'buyer_phone' => $buyer?->phone ?? '000000000',
            'amount' => $job->price,
        ];

        $res = $zeno->startPayment($payload);

        if (!$res['ok']) {
            return back()->withErrors(['pay' => 'Imeshindikana kuanzisha malipo. Jaribu tena.']);
        }

        return redirect()->route('jobs.pay.wait', $job)->with('success', 'Ombi la malipo limetumwa tena! Angalia simu yako.');
    }

    // Mfanyakazi job posting methods
    public function createMfanyakazi()
    {
        $user = Auth::user();
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Huna ruhusa. Mfanyakazi tu.');
        }

        return view('jobs.create-mfanyakazi', ['categories' => Category::all()]);
    }

    public function storeMfanyakazi(Request $request, ZenoPayService $zeno)
    {
        $user = Auth::user();
        if ($user->role !== 'mfanyakazi') {
            abort(403, 'Huna ruhusa. Mfanyakazi tu.');
        }

        $request->validate([
            'title' => ['required', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'min:20'],
            'price' => ['required', 'integer', 'min:1000'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'phone' => ['required', 'regex:/^(0[6-7]\d{8}|255[6-7]\d{8})$/'],
        ], [
            'phone.regex' => 'Weka 06/07xxxxxxxx au 2556/2557xxxxxxxx.',
            'description.min' => 'Maelezo lazima yawe angalau herufi 20.',
        ]);

        $postingFee = 2000; // TZS 2,000 posting fee
        $userWallet = $user->ensureWallet();

        // Check if user has enough balance
        if ($userWallet->balance >= $postingFee) {
            // Deduct from wallet
            return $this->processWalletPayment($request, $postingFee, $userWallet);
        } else {
            // Use ZenoPay for payment
            return $this->processZenoPayment($request, $postingFee, $zeno);
        }
    }

    private function processWalletPayment(Request $request, $postingFee, $userWallet)
    {
        return DB::transaction(function () use ($request, $postingFee, $userWallet) {
            // Create the job
            $job = Job::create([
                'user_id' => Auth::id(),
                'category_id' => (int) $request->input('category_id'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'price' => (int) $request->input('price'),
                'lat' => (float) $request->input('lat'),
                'lng' => (float) $request->input('lng'),
                'address_text' => $request->input('address_text'),
                'status' => 'posted',
                'published_at' => now(),
                'poster_type' => 'mfanyakazi',
                'posting_fee' => $postingFee,
            ]);

            // Deduct posting fee from wallet
            $userWallet->decrement('balance', $postingFee);

            // Record transaction
            WalletTransaction::create([
                'user_id' => Auth::id(),
                'type' => 'debit',
                'amount' => $postingFee,
                'description' => "Job posting fee for: {$job->title}",
                'reference' => "JOB_POST_{$job->id}",
            ]);

            return redirect()->route('dashboard')->with('success', 'Kazi imechapishwa kwa mafanikio! Ada ya TZS ' . number_format($postingFee) . ' imekatwa kutoka kwenye salio lako.');
        });
    }

    private function processZenoPayment(Request $request, $postingFee, ZenoPayService $zeno)
    {
        // Create job with pending payment
        $job = Job::create([
            'user_id' => Auth::id(),
            'category_id' => (int) $request->input('category_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'price' => (int) $request->input('price'),
            'lat' => (float) $request->input('lat'),
            'lng' => (float) $request->input('lng'),
            'address_text' => $request->input('address_text'),
            'status' => 'pending_payment',
            'poster_type' => 'mfanyakazi',
            'posting_fee' => $postingFee,
        ]);

        $orderId = (string) Str::ulid();
        $job->payment()->create([
            'order_id' => $orderId,
            'amount' => $postingFee,
            'status' => 'PENDING',
        ]);

        $user = Auth::user();
        $payload = [
            'order_id' => $orderId,
            'buyer_email' => $user->email ?? 'worker@tendapoa.local',
            'buyer_name' => $user->name ?? 'Worker',
            'buyer_phone' => $request->input('phone'),
            'amount' => $postingFee,
            // 'webhook_url' => route('zeno.webhook'), // User requested polling only
        ];

        $res = $zeno->startPayment($payload);
        if (!$res['ok']) {
            return back()->withErrors(['pay' => 'Imeshindikana kuanzisha malipo. Jaribu tena.']);
        }

        return redirect()->route('jobs.pay.wait', $job);
    }
    public function cancel(Job $job)
    {
        $this->ensureMuhitajiOrAdmin();

        if ($job->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'Huna ruhusa ya ku-cancel kazi hii.');
        }

        // 1. If job is pending payment, just delete it or mark cancelled (no refund needed)
        if ($job->status === 'pending_payment') {
            $job->delete(); // Or $job->update(['status' => 'cancelled']);
            return redirect()->route('my.jobs')->with('success', 'Kazi imefutwa kwa sababu haikulipiwa.');
        }

        // 2. If job is posted but not assigned, cancel and refund
        if ($job->status === 'posted' && is_null($job->accepted_worker_id)) {
            DB::transaction(function () use ($job) {
                // Refund to wallet if it was paid
                // Check if payment exists and was completed
                $payment = $job->payment;
                if ($payment && $payment->status === 'COMPLETED') {
                    $wallet = Auth::user()->ensureWallet();
                    $wallet->increment('balance', $job->price);

                    WalletTransaction::create([
                        'user_id' => Auth::id(),
                        'type' => 'refund',
                        'amount' => $job->price,
                        'description' => "Refund for cancelled job: {$job->title}",
                        'reference' => "JOB_REFUND_{$job->id}",
                    ]);
                }

                // Update job status
                $job->update([
                    'status' => 'cancelled',
                ]);

                try {
                    // Notify user about cancellation safely
                    // Reload user to be sure
                    $job->load('muhitaji');
                    $userToNotify = $job->muhitaji ?? $job->user;

                    if ($userToNotify) {
                        $userToNotify->notify(new JobStatusNotification($job, 'cancelled'));
                    }
                } catch (\Exception $e) {
                    \Log::warning('Cancel notification failed for Job #' . $job->id . ': ' . $e->getMessage());
                }
            });

            return redirect()->route('my.jobs')->with('success', 'Kazi imefutwa na pesa imerudishwa kwenye wallet yako (kama ililipiwa).');
        }

        return back()->withErrors(['cancel' => 'Huwezi ku-cancel kazi hii kwa sasa (huenda imeshaanza au imekamilika).']);
    }

    // API Method for Cancelling Job
    public function apiCancel(Job $job)
    {
        $user = Auth::user();

        // Check permission
        if ($job->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Huna ruhusa ya ku-cancel kazi hii.'
            ], 403);
        }

        // 1. If job is pending payment, delete it
        if ($job->status === 'pending_payment') {
            $job->delete();
            return response()->json([
                'success' => true,
                'message' => 'Kazi imefutwa kwa sababu haikulipiwa.'
            ]);
        }

        // 2. If job is posted but not assigned, cancel and refund
        if ($job->status === 'posted' && is_null($job->accepted_worker_id)) {
            try {
                DB::transaction(function () use ($job, $user) {
                    // Refund logic (only if payment was completed)
                    $payment = $job->payment;
                    if ($payment && $payment->status === 'COMPLETED') {
                        $wallet = $user->ensureWallet();
                        $wallet->increment('balance', $job->price);

                        WalletTransaction::create([
                            'user_id' => $user->id,
                            'type' => 'refund',
                            'amount' => $job->price,
                            'description' => "Refund for cancelled job: {$job->title}",
                            'reference' => "JOB_REFUND_{$job->id}",
                        ]);
                    }

                    // Update job status
                    $job->update([
                        'status' => 'cancelled',
                    ]);

                    try {
                        // Notify user about cancellation
                        $user->notify(new JobStatusNotification($job, 'cancelled'));
                    } catch (\Exception $e) {
                    }
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Kazi imefutwa na pesa imerudishwa kwenye wallet yako (kama ililipiwa).'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Imeshindikana kufuta kazi: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Huwezi ku-cancel kazi hii kwa sasa (huenda imeshaanza au imekamilika).'
        ], 400);
    }
    public function apiRetryPayment(Job $job, ZenoPayService $zeno)
    {
        $this->ensureMuhitajiOrAdmin();

        if ($job->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Huna ruhusa.'], 403);
        }

        if ($job->status !== 'pending_payment') {
            return response()->json(['success' => false, 'message' => 'Kazi hii haidaiwi malipo.'], 400);
        }

        // Create new payment record
        $orderId = (string) Str::ulid();

        // Futa payment ya zamani iliyo pending kama ipo
        $job->payment()->where('status', 'PENDING')->delete();

        $payment = $job->payment()->create([
            'order_id' => $orderId,
            'amount' => $job->price,
            'status' => 'PENDING',
        ]);

        $buyer = Auth::user();
        $payload = [
            'order_id' => $orderId,
            'buyer_email' => $buyer?->email ?? 'client@tendapoa.local',
            'buyer_name' => $buyer?->name ?? 'Client',
            'buyer_phone' => $buyer?->phone ?? '000000000',
            'amount' => $job->price,
        ];

        $res = $zeno->startPayment($payload);

        if (!$res['ok']) {
            return response()->json([
                'success' => false,
                'message' => 'Imeshindikana kuanzisha malipo. Jaribu tena.',
                'error' => $res
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Malipo yameanzishwa upya. Tafadhali thibitisha kwenye simu yako.',
            'data' => [
                'job_id' => $job->id,
                'payment' => $payment,
                'zenopay_response' => $res
            ]
        ]);
    }

    /**
     * Notify nearby workers
     */
    private function notifyNearbyWorkers(Job $job)
    {
        // Pata wafanyakazi wote walio na location
        $workers = User::where('role', 'mfanyakazi')
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('id', '!=', $job->user_id)
            ->get();

        foreach ($workers as $worker) {
            $distance = $this->calculateDistance($job->lat, $job->lng, $worker->lat, $worker->lng);

            // Tuma notification ikiwa ndani ya 50km
            if ($distance <= 50) {
                $label = 'Mbali';
                if ($distance <= 5)
                    $label = 'Karibu Sana';
                elseif ($distance <= 15)
                    $label = 'Karibu';
                elseif ($distance <= 30)
                    $label = 'Wastani';

                try {
                    /** @var \App\Models\User $worker */
                    $worker->notify(new JobAvailableNotification($job, $distance, $label));
                } catch (\Exception $e) {
                    \Log::error("Failed to notify worker {$worker->id}: " . $e->getMessage());
                }
            }
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        }

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));

        // Clamp value to -1.0 to 1.0 to check for precision errors
        if ($dist > 1)
            $dist = 1;
        if ($dist < -1)
            $dist = -1;

        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return ($miles * 1.609344); // Convert to KM
    }
}
