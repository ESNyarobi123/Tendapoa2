<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Models\Setting;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    public function requestForm()
    {
        $u = Auth::user();
        if (!$u || !in_array($u->role,['mfanyakazi','admin','muhitaji'])) abort(403);

        $wallet = $u->ensureWallet();
        return view('mfanyakazi.withdraw', compact('wallet'));
    }

    public function submit(Request $r, WalletService $wallets)
    {
        $u = Auth::user();
        if (!$u || !in_array($u->role,['mfanyakazi','admin','muhitaji'])) abort(403);

        $minWithdrawal = (int) Setting::get('min_withdrawal', 5000);
        
        $data = $r->validate([
            'amount'=>['required','integer','min:' . $minWithdrawal],
            'phone_number'=>['required','string','min:10'],
            'registered_name'=>['required','string','min:2'],
            'network_type'=>['required','string','in:vodacom,tigo,airtel,halotel,ttcl'],
            'method'=>['required','string'],
        ], [
            'amount.min' => 'Kiwango cha chini cha kutoa ni TZS ' . number_format($minWithdrawal),
        ]);

        $wallet = $u->ensureWallet();
        $withdrawalFee = (int) Setting::get('withdrawal_fee', 0);
        $totalToDebit = (int)$data['amount'] + $withdrawalFee;

        if ($wallet->balance < $totalToDebit) {
            throw ValidationException::withMessages(['amount'=>'Salio lako halitoshi kulipia kiasi unachotoa pamoja na makato ya TZS ' . number_format($withdrawalFee)]);
        }

        // debit immediately
        $wallets->debit($u, $totalToDebit, 'WITHDRAW', 'Withdrawal request (inc. fee: TZS ' . $withdrawalFee . ')');

        Withdrawal::create([
            'user_id'=>$u->id,
            'amount'=>(int)$data['amount'],
            'status'=>'PROCESSING',
            'method'=>$data['method'],
            'account'=>$data['phone_number'],
            'registered_name'=>$data['registered_name'],
            'network_type'=>$data['network_type'],
        ]);

        return redirect()->route('dashboard')->with('status','Withdrawal imewasilishwa. Subiri uthibitisho wa Admin.');
    }
}
