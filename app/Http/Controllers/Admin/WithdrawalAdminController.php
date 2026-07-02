<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WithdrawalAdminController extends Controller
{
    public function index(Request $request)
    {
        $u = Auth::user();
        if (! $u || $u->role !== 'admin') {
            abort(403);
        }

        $query = Withdrawal::with('user')->latest();

        if ($status = $request->get('status')) {
            $query->where('status', strtoupper($status));
        }

        $items = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Withdrawal::count(),
            'processing' => Withdrawal::where('status', 'PROCESSING')->count(),
            'paid' => Withdrawal::where('status', 'PAID')->count(),
            'rejected' => Withdrawal::where('status', 'REJECTED')->count(),
        ];

        return view('admin.withdrawals', compact('items', 'stats'));
    }

    public function markPaid(Withdrawal $withdrawal)
    {
        $u = Auth::user();
        if (!$u || $u->role !== 'admin') abort(403);

        $withdrawal->update(['status'=>'PAID']);
        return back()->with('status','Withdrawal marked as PAID.');
    }

    public function reject(Withdrawal $withdrawal, WalletService $wallets)
    {
        $u = Auth::user();
        if (!$u || $u->role !== 'admin') abort(403);

        if ($withdrawal->status === 'PAID') {
            throw ValidationException::withMessages(['status'=>'Already paid; cannot reject.']);
        }

        // refund to worker wallet if we had debited
        $wallets->credit($withdrawal->user, (int)$withdrawal->amount, 'ADJUST', 'Withdrawal rejected refund');
        $withdrawal->update(['status'=>'REJECTED']);

        return back()->with('status','Withdrawal rejected & refunded.');
    }
}
