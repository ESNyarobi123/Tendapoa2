<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobStatusLog;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Notifications\JobStatusNotification;
use App\Services\ClickPesaService;
use App\Services\EscrowService;
use App\Services\NotificationService;
use App\Services\TranslationService;
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
        if (! in_array($role, ['muhitaji', 'admin'], true)) {
            abort(403, 'Huna ruhusa (muhitaji/admin tu).');
        }
    }

    /**
     * Handle image upload and resizing
     * Returns the stored image path or null
     */
    private function handleImageUpload($file, $existingImage = null): ?string
    {
        if (! $file || ! $file->isValid()) {
            \Log::info('handleImageUpload: No file or invalid file', [
                'hasFile' => $file !== null,
                'isValid' => $file ? $file->isValid() : false,
                'existingImage' => $existingImage,
            ]);

            return $existingImage; // Return existing if no new file
        }

        \Log::info('handleImageUpload: Starting upload', [
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
        ]);

        // Delete old image if exists
        if ($existingImage && Storage::disk('public')->exists($existingImage)) {
            Storage::disk('public')->delete($existingImage);
        }

        // HEIC/HEIF: GD cannot process these — save as-is
        $ext = strtolower($file->getClientOriginalExtension());
        if (in_array($ext, ['heic', 'heif'])) {
            $dir = storage_path('app/public/jobs');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $uniqueName = Str::random(40).'.'.$ext;
            try {
                $file->move($dir, $uniqueName);
                $filename = 'jobs/'.$uniqueName;
                @chmod($dir.DIRECTORY_SEPARATOR.$uniqueName, 0644);
                \Log::info('Image uploaded (HEIC/HEIF) - saved as-is', ['filename' => $filename]);

                return $filename;
            } catch (\Exception $e) {
                \Log::error('HEIC upload failed', ['error' => $e->getMessage()]);

                return $existingImage;
            }
        }

        // Check if GD extension is available
        $gdAvailable = extension_loaded('gd') && function_exists('imagecreatefromjpeg');

        if (! $gdAvailable) {
            // GD not available - just save the file as-is using direct move
            $extension = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            $uniqueName = Str::random(40).'.'.$extension;
            $filename = 'jobs/'.$uniqueName;

            // Ensure directory exists
            $dir = storage_path('app/public/jobs');
            if (! is_dir($dir)) {
                if (! mkdir($dir, 0755, true)) {
                    \Log::error('Image upload - Failed to create jobs directory', ['dir' => $dir]);

                    return $existingImage;
                }
            }

            // Use move() directly to ensure file is saved
            $fullPath = $dir.DIRECTORY_SEPARATOR.$uniqueName;

            try {
                $file->move($dir, $uniqueName);

                // Verify the file was saved correctly
                if (file_exists($fullPath)) {
                    @chmod($fullPath, 0644);
                    \Log::info('Image uploaded (no GD) - File saved successfully', [
                        'filename' => $filename,
                        'fullPath' => $fullPath,
                        'fileSize' => filesize($fullPath),
                        'isReadable' => is_readable($fullPath),
                    ]);

                    return $filename;
                } else {
                    \Log::error('Image upload (no GD) - File not found after move', [
                        'filename' => $filename,
                        'fullPath' => $fullPath,
                        'dir' => $dir,
                    ]);

                    return $existingImage;
                }
            } catch (\Exception $e) {
                \Log::error('Image upload (no GD) - Exception during move', [
                    'error' => $e->getMessage(),
                    'filename' => $filename,
                    'dir' => $dir,
                ]);

                return $existingImage;
            }
        }

        // GD is available - proceed with resizing
        // Get image info
        $imageInfo = getimagesize($file->getRealPath());
        if (! $imageInfo) {
            return $existingImage; // Invalid image
        }

        [$width, $height, $type] = $imageInfo;

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
        $filename = 'jobs/'.Str::random(40).'.jpg';
        $fullPath = storage_path('app/public/'.$filename);

        // Ensure directory exists with proper permissions
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
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
                $filename = 'jobs/'.Str::random(40).'.'.$extension;

                // Ensure directory exists
                $dir = storage_path('app/public/jobs');
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                    chmod($dir, 0755);
                }

                $stored = $file->storeAs('public', $filename);

                // Set file permissions
                $fullPath = storage_path('app/public/'.$filename);
                if (file_exists($fullPath)) {
                    chmod($fullPath, 0644);
                    \Log::info('Image uploaded (unsupported type) - File saved', [
                        'filename' => $filename,
                        'storedPath' => $stored,
                        'fullPath' => $fullPath,
                        'fileSize' => filesize($fullPath),
                    ]);
                } else {
                    \Log::error('Image upload (unsupported type) - File not found after storeAs', [
                        'filename' => $filename,
                        'storedPath' => $stored,
                        'fullPath' => $fullPath,
                    ]);
                }

                return $filename;
        }

        if (! $source) {
            // If we can't create source, save file as-is
            $extension = $file->getClientOriginalExtension();
            $filename = 'jobs/'.Str::random(40).'.'.$extension;

            // Ensure directory exists
            $dir = storage_path('app/public/jobs');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
                chmod($dir, 0755);
            }

            $stored = $file->storeAs('public', $filename);

            // Set file permissions
            $fullPath = storage_path('app/public/'.$filename);
            if (file_exists($fullPath)) {
                chmod($fullPath, 0644);
                \Log::info('Image uploaded (no source) - File saved', [
                    'filename' => $filename,
                    'storedPath' => $stored,
                    'fullPath' => $fullPath,
                    'fileSize' => filesize($fullPath),
                ]);
            } else {
                \Log::error('Image upload (no source) - File not found after storeAs', [
                    'filename' => $filename,
                    'storedPath' => $stored,
                    'fullPath' => $fullPath,
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

        if (! $saved || ! file_exists($fullPath)) {
            \Log::error('Failed to save resized image', [
                'fullPath' => $fullPath,
                'filename' => $filename,
                'saved' => $saved,
                'fileExists' => file_exists($fullPath),
                'directoryExists' => is_dir(dirname($fullPath)),
                'directoryWritable' => is_writable(dirname($fullPath)),
            ]);
            // Fallback: try to save original file using Laravel's storeAs
            $extension = $file->getClientOriginalExtension();
            $filename = 'jobs/'.Str::random(40).'.'.$extension;
            $dir = storage_path('app/public/jobs');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
                chmod($dir, 0755);
            }
            $stored = $file->storeAs('public', $filename);
            $fullPath = storage_path('app/public/'.$filename);

            \Log::info('Image saved using fallback method', [
                'filename' => $filename,
                'stored' => $stored,
                'fullPath' => $fullPath,
                'fileExists' => file_exists($fullPath),
            ]);
        }

        // Set file permissions (Windows compatible)
        if (file_exists($fullPath)) {
            @chmod($fullPath, 0644);

            // FORCE COPY TO STORAGE/JOBS (Maombi ya Mteja: Nakala Halisi)
            try {
                // $filename ni mfano 'jobs/xxxxx.jpg'
                // storage_path($filename) inapeleka kwenye '.../storage/jobs/xxxxx.jpg'
                $storageDirectPath = storage_path($filename);
                $storageDirectDir = dirname($storageDirectPath);

                if (! is_dir($storageDirectDir)) {
                    @mkdir($storageDirectDir, 0755, true);
                }

                // Copy file if it doesn't exist or update it
                @copy($fullPath, $storageDirectPath);
            } catch (\Exception $e) {
                // Ignore copy errors, main file is safe
            }

            // Verify file is readable and has content
            $fileSize = filesize($fullPath);
            $isReadable = is_readable($fullPath);

            // Log successful upload for debugging
            \Log::info('Image uploaded successfully', [
                'filename' => $filename,
                'fullPath' => $fullPath,
                'storageCopyPath' => storage_path($filename), // Log storage copy path
                'fileExists' => true,
                'fileSize' => $fileSize,
                'isReadable' => $isReadable,
                'permissions' => substr(sprintf('%o', fileperms($fullPath)), -4),
                'expectedUrl' => asset('storage/'.$filename),
            ]);

            // Verify the file has content
            if ($fileSize === 0) {
                \Log::error('Image file is empty after save', [
                    'filename' => $filename,
                    'fullPath' => $fullPath,
                ]);
            }
        } else {
            \Log::error('Image file not found after save', [
                'filename' => $filename,
                'fullPath' => $fullPath,
                'directoryExists' => is_dir(dirname($fullPath)),
                'directoryWritable' => is_writable(dirname($fullPath)),
            ]);
            // Return null so we don't save invalid path to database
            imagedestroy($source);
            if (isset($resized)) {
                imagedestroy($resized);
            }

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

        $wallet = Auth::user()->ensureWallet();

        return view('jobs.create', [
            'categories' => Category::all(),
            'wallet' => $wallet,
        ]);
    }

    public function store(Request $r)
    {
        $this->ensureMuhitajiOrAdmin();

        $r->validate([
            'title' => ['required', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:1000'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['nullable'],
            'address_text' => ['nullable'],
            'urgency' => ['nullable', 'in:normal,urgent,flexible'],
            'image' => ['nullable', 'file', 'max:5120', function ($attribute, $value, $fail) {
                if ($value && ! str_starts_with($value->getMimeType(), 'image/')) {
                    $fail('Faili lazima iwe picha.');
                }
            }],
        ], [
            'lat.required' => 'Eneo la kazi ni lazima. Tafadhali weka pini eneo la kazi kwenye ramani.',
            'lng.required' => 'Eneo la kazi ni lazima. Tafadhali weka pini eneo la kazi kwenye ramani.',
            'lat.between' => 'Latitude si sahihi. Tafadhali weka eneo sahihi kwenye ramani.',
            'lng.between' => 'Longitude si sahihi. Tafadhali weka eneo sahihi kwenye ramani.',
            'image.max' => 'Picha haipaswi kuwa kubwa zaidi ya 5MB.',
        ]);

        // Handle image upload
        $imagePath = $this->handleImageUpload($r->file('image'));

        // Log the image path for debugging
        if ($imagePath) {
            $fullPath = storage_path('app/public/'.$imagePath);
            \Log::info('Job creation (store) - Image path saved', [
                'imagePath' => $imagePath,
                'fullStoragePath' => $fullPath,
                'fileExists' => file_exists($fullPath),
                'fileSize' => file_exists($fullPath) ? filesize($fullPath) : 0,
                'expectedUrl' => asset('storage/'.$imagePath),
                'storageDiskExists' => Storage::disk('public')->exists($imagePath),
            ]);
        } else {
            \Log::info('Job creation (store) - No image uploaded');
        }

        // Verify image file exists before saving to database
        if ($imagePath) {
            $imageFullPath = storage_path('app/public/'.$imagePath);
            if (! file_exists($imageFullPath)) {
                \Log::error('Image path provided but file does not exist - not saving to database', [
                    'imagePath' => $imagePath,
                    'fullPath' => $imageFullPath,
                ]);
                $imagePath = null; // Don't save invalid path
            } else {
                \Log::info('Image verified before saving to database', [
                    'imagePath' => $imagePath,
                    'fullPath' => $imageFullPath,
                    'fileSize' => filesize($imageFullPath),
                ]);
            }
        }

        $localized = TranslationService::ensureBothLanguages(
            $r->input('title'),
            $r->input('description')
        );

        // NEW WORKFLOW: Free posting — job goes live immediately as 'open'
        $job = Job::create([
            'user_id' => Auth::id(),
            'category_id' => (int) $r->input('category_id'),
            'title' => $r->input('title'),
            'title_sw' => $localized['title_sw'],
            'title_en' => $localized['title_en'],
            'description' => $r->input('description'),
            'description_sw' => $localized['description_sw'],
            'description_en' => $localized['description_en'],
            'image' => $imagePath,
            'price' => (int) $r->input('price'),
            'lat' => (float) $r->input('lat'),
            'lng' => (float) $r->input('lng'),
            'address_text' => $r->input('address_text'),
            'urgency' => $r->input('urgency', 'normal'),
            'status' => Job::S_OPEN,
            'published_at' => now(),
        ]);

        // Log status transition
        JobStatusLog::log($job, Job::S_OPEN, Auth::id(), 'Job created and published (free posting)');

        // Log final state after database save
        \Log::info('Job created with image', [
            'jobId' => $job->id,
            'imagePath' => $job->image,
            'imageUrl' => $job->image ? asset('storage/'.$job->image) : null,
            'fileExists' => $job->image ? file_exists(storage_path('app/public/'.$job->image)) : false,
        ]);

        // Notify client
        try {
            Auth::user()->notify(new JobStatusNotification($job, 'posted'));
        } catch (\Exception $e) {
        }

        // Notify nearby workers
        $this->notifyNearbyWorkers($job);

        return redirect()->route('jobs.show', $job)->with('success', 'Kazi imechapishwa bure! Wafanyakazi wataona na kuomba.');
    }

    public function edit(Job $job)
    {
        $this->ensureMuhitajiOrAdmin();

        // Only allow editing if job is open or awaiting_payment (not in progress or completed)
        if (! in_array($job->status, [Job::S_OPEN, Job::S_AWAITING_PAYMENT, 'posted', 'assigned'])) {
            return back()->withErrors(['edit' => 'Huwezi kubadilisha kazi ambayo imeanza au imekamilika.']);
        }

        // Only job owner can edit
        if ($job->user_id !== Auth::id()) {
            abort(403, 'Huna ruhusa ya kubadilisha kazi hii.');
        }

        return view('jobs.edit', [
            'job' => $job,
            'categories' => Category::all(),
        ]);
    }

    public function update(Request $r, Job $job, ClickPesaService $clickpesa)
    {
        $this->ensureMuhitajiOrAdmin();

        // Only allow editing if job is posted or assigned
        if (! in_array($job->status, ['posted', 'assigned'])) {
            return back()->withErrors(['edit' => 'Huwezi kubadilisha kazi ambayo imeanza au imekamilika.']);
        }

        // Only job owner can edit
        if ($job->user_id !== Auth::id()) {
            abort(403, 'Huna ruhusa ya kubadilisha kazi hii.');
        }

        $r->validate([
            'title' => ['required', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:1000'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'image' => ['nullable', 'file', 'max:5120', function ($attribute, $value, $fail) {
                if ($value && ! str_starts_with($value->getMimeType(), 'image/')) {
                    $fail('Faili lazima iwe picha.');
                }
            }],
        ], [
            'lat.required' => 'Eneo la kazi ni lazima. Tafadhali weka pini eneo la kazi kwenye ramani.',
            'lng.required' => 'Eneo la kazi ni lazima. Tafadhali weka pini eneo la kazi kwenye ramani.',
            'lat.between' => 'Latitude si sahihi. Tafadhali weka eneo sahihi kwenye ramani.',
            'lng.between' => 'Longitude si sahihi. Tafadhali weka eneo sahihi kwenye ramani.',
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

        $localized = TranslationService::ensureBothLanguages(
            $r->input('title'),
            $r->input('description')
        );

        $updateData = [
            'title' => $r->input('title'),
            'title_sw' => $localized['title_sw'],
            'title_en' => $localized['title_en'],
            'description' => $r->input('description'),
            'description_sw' => $localized['description_sw'],
            'description_en' => $localized['description_en'],
            'category_id' => (int) $r->input('category_id'),
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
            $orderId = strtoupper(Str::random(16));
            $job->payment()->create([
                'order_id' => $orderId,
                'amount' => $priceDifference,
                'status' => 'PENDING',
            ]);

            $res = $clickpesa->startPayment([
                'orderReference' => $orderId,
                'phoneNumber' => Auth::user()?->phone ?? '000000000',
                'amount' => $priceDifference,
            ]);
            if (! $res['ok']) {
                return back()->withErrors(['pay' => 'Imeshindikana kuanzisha malipo ya ziada. Jaribu tena.']);
            }

            return redirect()->route('jobs.pay.wait', $job)
                ->with('success', 'Kazi imebadilishwa! Malipo ya ziada ya TZS '.number_format($priceDifference).' yanahitajika.');
        }

        return redirect()->route('my.jobs')
            ->with('success', 'Kazi imebadilishwa kwa mafanikio!');
    }

    // API Methods for Job Editing
    public function apiEdit(Job $job)
    {
        $this->ensureMuhitajiOrAdmin();

        // Only allow editing if job is posted or assigned
        if (! in_array($job->status, ['posted', 'assigned'])) {
            return response()->json([
                'error' => 'Huwezi kubadilisha kazi ambayo imeanza au imekamilika.',
                'status' => 'error',
            ], 400);
        }

        // Only job owner can edit
        if ($job->user_id !== Auth::id()) {
            return response()->json([
                'error' => 'Huna ruhusa ya kubadilisha kazi hii.',
                'status' => 'forbidden',
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
            'categories' => Category::all(),
            'can_edit' => true,
            'status' => 'success',
        ]);
    }

    public function apiUpdate(Request $r, Job $job, ClickPesaService $clickpesa)
    {
        $this->ensureMuhitajiOrAdmin();

        // Only allow editing if job is posted or assigned
        if (! in_array($job->status, ['posted', 'assigned'])) {
            return response()->json([
                'error' => 'Huwezi kubadilisha kazi ambayo imeanza au imekamilika.',
                'status' => 'error',
            ], 400);
        }

        // Only job owner can edit
        if ($job->user_id !== Auth::id()) {
            return response()->json([
                'error' => 'Huna ruhusa ya kubadilisha kazi hii.',
                'status' => 'forbidden',
            ], 403);
        }

        $r->validate([
            'title' => ['required', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:1000'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'image' => ['nullable', 'file', 'max:5120', function ($attribute, $value, $fail) {
                if ($value && ! str_starts_with($value->getMimeType(), 'image/')) {
                    $fail('Faili lazima iwe picha.');
                }
            }],
        ], [
            'lat.required' => 'Eneo la kazi ni lazima.',
            'lng.required' => 'Eneo la kazi ni lazima.',
            'lat.between' => 'Latitude si sahihi.',
            'lng.between' => 'Longitude si sahihi.',
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
                'field' => 'price',
            ], 422);
        }

        // Handle image upload (keep existing if no new image)
        $imagePath = $this->handleImageUpload($r->file('image'), $job->image);

        $localized = TranslationService::ensureBothLanguages(
            $r->input('title'),
            $r->input('description')
        );

        $updateData = [
            'title' => $r->input('title'),
            'title_sw' => $localized['title_sw'],
            'title_en' => $localized['title_en'],
            'description' => $r->input('description'),
            'description_sw' => $localized['description_sw'],
            'description_en' => $localized['description_en'],
            'category_id' => (int) $r->input('category_id'),
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
            $imageUrl = asset('storage/'.$job->image);
            // Add cache busting
            if (Storage::disk('public')->exists($job->image)) {
                $timestamp = filemtime(storage_path('app/public/'.$job->image));
                $imageUrl = asset('storage/'.$job->image).'?v='.$timestamp;
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
            'status' => 'success',
        ];

        // If price increased, process additional payment
        if ($priceDifference > 0) {
            $orderId = strtoupper(Str::random(16));
            $job->payment()->create([
                'order_id' => $orderId,
                'amount' => $priceDifference,
                'status' => 'PENDING',
            ]);

            $res = $clickpesa->startPayment([
                'orderReference' => $orderId,
                'phoneNumber' => Auth::user()?->phone ?? '000000000',
                'amount' => $priceDifference,
            ]);
            if (! $res['ok']) {
                return response()->json([
                    'error' => 'Imeshindikana kuanzisha malipo ya ziada. Jaribu tena.',
                    'status' => 'payment_error',
                ], 500);
            }

            $response['payment_required'] = true;
            $response['payment_amount'] = $priceDifference;
            $response['payment_url'] = route('jobs.pay.wait', $job);
            $response['message'] = 'Kazi imebadilishwa! Malipo ya ziada ya TZS '.number_format($priceDifference).' yanahitajika.';
        }

        return response()->json($response);
    }

    public function apiStore(Request $r)
    {
        $this->ensureMuhitajiOrAdmin();

        $r->validate([
            'title' => ['required', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:1000'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['nullable'],
            'address_text' => ['nullable'],
            'urgency' => ['nullable', 'in:normal,urgent,flexible'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'lat.required' => 'Eneo la kazi ni lazima.',
            'lng.required' => 'Eneo la kazi ni lazima.',
            'lat.between' => 'Latitude si sahihi.',
            'lng.between' => 'Longitude si sahihi.',
            'image.image' => 'Faili lazima iwe picha (jpeg, jpg, png, au webp).',
            'image.max' => 'Picha haipaswi kuwa kubwa zaidi ya 5MB.',
        ]);

        // Handle image upload
        $imagePath = $this->handleImageUpload($r->file('image'));

        $localized = TranslationService::ensureBothLanguages(
            $r->input('title'),
            $r->input('description')
        );

        // NEW WORKFLOW: Free posting — job goes live immediately as 'open'
        $job = Job::create([
            'user_id' => Auth::id(),
            'category_id' => (int) $r->input('category_id'),
            'title' => $r->input('title'),
            'title_sw' => $localized['title_sw'],
            'title_en' => $localized['title_en'],
            'description' => $r->input('description'),
            'description_sw' => $localized['description_sw'],
            'description_en' => $localized['description_en'],
            'image' => $imagePath,
            'price' => (int) $r->input('price'),
            'lat' => (float) $r->input('lat'),
            'lng' => (float) $r->input('lng'),
            'address_text' => $r->input('address_text'),
            'urgency' => $r->input('urgency', 'normal'),
            'status' => Job::S_OPEN,
            'published_at' => now(),
        ]);

        // Log status transition
        JobStatusLog::log($job, Job::S_OPEN, Auth::id(), 'Job created via API (free posting)');

        $imageUrl = null;
        if ($job->image) {
            $imageUrl = asset('storage/'.$job->image);
            if (Storage::disk('public')->exists($job->image)) {
                $timestamp = filemtime(storage_path('app/public/'.$job->image));
                $imageUrl = asset('storage/'.$job->image).'?v='.$timestamp;
            }
        }

        // Notify client
        try {
            Auth::user()->notify(new JobStatusNotification($job, 'posted'));
        } catch (\Exception $e) {
        }

        // Notify nearby workers
        $this->notifyNearbyWorkers($job);

        return response()->json([
            'message' => 'Kazi imechapishwa bure! Wafanyakazi wataona na kuomba.',
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'price' => $job->price,
                'status' => $job->status,
                'image' => $imageUrl,
                'published_at' => $job->published_at->toISOString(),
            ],
            'status' => 'success',
        ]);
    }

    public function wait(Job $job)
    {
        $this->ensureMuhitajiOrAdmin();
        $job->load('payment');

        return view('jobs.wait', ['job' => $job]);
    }

    public function retryPayment(Job $job, ClickPesaService $clickpesa)
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
        $orderId = strtoupper(Str::random(16));
        $job->payment()->create([
            'order_id' => $orderId,
            'amount' => $job->price,
            'status' => 'PENDING',
        ]);

        $res = $clickpesa->startPayment([
            'orderReference' => $orderId,
            'phoneNumber' => Auth::user()?->phone ?? '000000000',
            'amount' => $job->price,
        ]);

        if (! $res['ok']) {
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

    public function storeMfanyakazi(Request $request, ClickPesaService $clickpesa)
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
            // Use ClickPesa for payment
            return $this->processClickPesaPayment($request, $postingFee, $clickpesa);
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

            return redirect()->route('dashboard')->with('success', 'Kazi imechapishwa kwa mafanikio! Ada ya TZS '.number_format($postingFee).' imekatwa kutoka kwenye salio lako.');
        });
    }

    private function processClickPesaPayment(Request $request, $postingFee, ClickPesaService $clickpesa)
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

        $orderId = strtoupper(Str::random(16));
        $job->payment()->create([
            'order_id' => $orderId,
            'amount' => $postingFee,
            'status' => 'PENDING',
        ]);

        $res = $clickpesa->startPayment([
            'orderReference' => $orderId,
            'phoneNumber' => $request->input('phone'),
            'amount' => $postingFee,
        ]);
        if (! $res['ok']) {
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

        // NEW WORKFLOW: open jobs are free — just cancel, no refund
        if (in_array($job->status, [Job::S_OPEN, 'posted', 'pending_payment']) && is_null($job->accepted_worker_id)) {
            DB::transaction(function () use ($job) {
                $job->transitionStatus(Job::S_CANCELLED, Auth::id(), 'Client cancelled job');

                // Legacy: refund if old pay-before-post payment exists
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

                try {
                    $job->load('muhitaji');
                    ($job->muhitaji ?? $job->user)?->notify(new JobStatusNotification($job, 'cancelled'));
                } catch (\Exception $e) {
                    \Log::warning('Cancel notification failed for Job #'.$job->id.': '.$e->getMessage());
                }
            });

            return redirect()->route('my.jobs')->with('success', 'Kazi imefutwa.');
        }

        // Awaiting payment — cancel, no escrow to refund
        if ($job->status === Job::S_AWAITING_PAYMENT) {
            $job->transitionStatus(Job::S_CANCELLED, Auth::id(), 'Client cancelled before funding');

            return redirect()->route('my.jobs')->with('success', 'Kazi imefutwa.');
        }

        // Funded but not yet in progress — refund escrow
        if ($job->status === Job::S_FUNDED) {
            DB::transaction(function () use ($job) {
                try {
                    app(EscrowService::class)->refundToClient($job, 'Client cancelled funded job');
                } catch (\Exception $e) {
                    \Log::error('Escrow refund failed on cancel: '.$e->getMessage());
                }
                $job->transitionStatus(Job::S_CANCELLED, Auth::id(), 'Client cancelled funded job — escrow refunded');
            });

            return redirect()->route('my.jobs')->with('success', 'Kazi imefutwa na pesa imerudishwa kwenye wallet yako.');
        }

        return back()->withErrors(['cancel' => 'Huwezi ku-cancel kazi hii kwa sasa (huenda imeshaanza au imekamilika).']);
    }

    // API Method for Cancelling Job
    public function apiCancel(Job $job)
    {
        $user = Auth::user();

        if ($job->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Huna ruhusa ya ku-cancel kazi hii.'], 403);
        }

        // NEW WORKFLOW: open/posted/pending_payment — free cancel
        if (in_array($job->status, [Job::S_OPEN, 'posted', 'pending_payment']) && is_null($job->accepted_worker_id)) {
            try {
                DB::transaction(function () use ($job, $user) {
                    $job->transitionStatus(Job::S_CANCELLED, $user->id, 'Client cancelled job (API)');

                    // Legacy refund if old pay-before-post
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

                    try {
                        $user->notify(new JobStatusNotification($job, 'cancelled'));
                    } catch (\Exception $e) {
                    }
                });

                return response()->json(['success' => true, 'message' => 'Kazi imefutwa.']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Imeshindikana: '.$e->getMessage()], 500);
            }
        }

        // Awaiting payment — cancel, no escrow
        if ($job->status === Job::S_AWAITING_PAYMENT) {
            $job->transitionStatus(Job::S_CANCELLED, $user->id, 'Client cancelled before funding (API)');

            return response()->json(['success' => true, 'message' => 'Kazi imefutwa.']);
        }

        // Funded but not in progress — refund escrow
        if ($job->status === Job::S_FUNDED) {
            try {
                DB::transaction(function () use ($job, $user) {
                    app(EscrowService::class)->refundToClient($job, 'Client cancelled funded job (API)');
                    $job->transitionStatus(Job::S_CANCELLED, $user->id, 'Client cancelled funded job — escrow refunded (API)');
                });

                return response()->json(['success' => true, 'message' => 'Kazi imefutwa na pesa imerudishwa.']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Imeshindikana: '.$e->getMessage()], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Huwezi ku-cancel kazi hii kwa sasa (huenda imeshaanza au imekamilika).',
        ], 400);
    }

    public function apiRetryPayment(Job $job, ClickPesaService $clickpesa)
    {
        $this->ensureMuhitajiOrAdmin();

        if ($job->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Huna ruhusa.'], 403);
        }

        if ($job->status !== 'pending_payment') {
            return response()->json(['success' => false, 'message' => 'Kazi hii haidaiwi malipo.'], 400);
        }

        // Create new payment record
        $orderId = strtoupper(Str::random(16));

        // Futa payment ya zamani iliyo pending kama ipo
        $job->payment()->where('status', 'PENDING')->delete();

        $payment = $job->payment()->create([
            'order_id' => $orderId,
            'amount' => $job->price,
            'status' => 'PENDING',
        ]);

        $res = $clickpesa->startPayment([
            'orderReference' => $orderId,
            'phoneNumber' => Auth::user()?->phone ?? '000000000',
            'amount' => $job->price,
        ]);

        if (! $res['ok']) {
            return response()->json([
                'success' => false,
                'message' => 'Imeshindikana kuanzisha malipo. Jaribu tena.',
                'error' => $res,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Malipo yameanzishwa upya. Tafadhali thibitisha kwenye simu yako.',
            'data' => [
                'job_id' => $job->id,
                'payment' => $payment,
                'clickpesa_response' => $res,
            ],
        ]);
    }

    /**
     * Notify nearby workers (delegates to NotificationService — DB notifications + shared Haversine query).
     */
    public function notifyNearbyWorkers(Job $job): void
    {
        try {
            app(NotificationService::class)->notifyNearbyWorkers($job, 50);
        } catch (\Throwable $e) {
            \Log::error('notifyNearbyWorkers failed: '.$e->getMessage());
        }
    }
}
