<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\AppVersion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HomeController extends Controller
{
    public function index()
    {
        $cats = Category::orderBy('name')->get();
        return view('home', compact('cats'));
    }

    /**
     * Download the active APK file
     */
    public function downloadApp()
    {
        $activeVersion = AppVersion::getActive();

        if (!$activeVersion) {
            abort(404, 'No APK version available for download.');
        }

        $filePath = storage_path('app/public/' . $activeVersion->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'APK file not found.');
        }

        return response()->download($filePath, $activeVersion->file_name, [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
