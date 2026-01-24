<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{
    public function index()
    {
        $u = Auth::user();
        if (!$u || $u->role !== 'admin') abort(403);

        // Get completed jobs to calculate commissions
        $jobs = Job::with(['muhitaji', 'acceptedWorker'])
            ->where('status', 'completed')
            ->latest()
            ->paginate(20);

        $commissionRate = (float) Setting::get('commission_rate', 10) / 100;
        $totalVolume = Job::where('status', 'completed')->sum('price');
        $totalCommission = $totalVolume * $commissionRate;

        return view('admin.commissions', compact('jobs', 'totalVolume', 'totalCommission'));
    }
}
