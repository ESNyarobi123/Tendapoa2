@extends('layouts.app')

@section('content')
<div style="max-width:700px;margin:0 auto">
    <h2 style="font-size:22px;font-weight:700;margin-bottom:4px">⚠️ Mgogoro #{{ $dispute->id }}</h2>
    <span class="badge" style="background:{{ $dispute->isOpen() ? '#fef3c7' : '#f0fdf4' }};color:{{ $dispute->isOpen() ? '#92400e' : '#166534' }}">
        {{ $dispute->getStatusLabel() }}
    </span>

    {{-- Job Summary --}}
    <div class="card" style="margin:16px 0">
        <div style="display:flex;justify-content:space-between">
            <div>
                <div style="font-size:12px;color:#999">Kazi</div>
                <div style="font-weight:600">{{ $dispute->job->title }}</div>
            </div>
            <div style="text-align:right">
                <div style="font-size:12px;color:#999">Kiasi</div>
                <div style="font-weight:700;color:#10b981">TZS {{ number_format($dispute->job->agreed_amount ?? $dispute->job->price) }}</div>
            </div>
        </div>
    </div>

    {{-- Parties --}}
    <div class="card" style="margin-bottom:16px">
        <div style="display:flex;gap:20px">
            <div style="flex:1">
                <div style="font-size:12px;color:#999;margin-bottom:4px">Alifungua mgogoro</div>
                <div style="font-weight:600">{{ $dispute->raisedByUser->name }}</div>
                <div style="font-size:12px;color:#666">{{ $dispute->raisedByUser->role }}</div>
            </div>
            <div style="flex:1">
                <div style="font-size:12px;color:#999;margin-bottom:4px">Dhidi ya</div>
                <div style="font-weight:600">{{ $dispute->againstUser->name }}</div>
                <div style="font-size:12px;color:#666">{{ $dispute->againstUser->role }}</div>
            </div>
        </div>
    </div>

    {{-- Reason --}}
    <div class="card" style="margin-bottom:16px">
        <div style="font-size:13px;font-weight:600;margin-bottom:6px">Sababu ya Mgogoro</div>
        <p style="color:#374151;font-size:14px;line-height:1.5;margin:0">{{ $dispute->reason }}</p>
    </div>

    {{-- Resolution (if resolved) --}}
    @if($dispute->isResolved())
    <div class="card" style="margin-bottom:16px;background:#f0fdf4;border-color:#bbf7d0">
        <div style="font-size:13px;font-weight:600;margin-bottom:6px;color:#166534">Uamuzi</div>
        @if($dispute->resolution_note)
            <p style="color:#374151;font-size:14px;margin:0 0 8px">{{ $dispute->resolution_note }}</p>
        @endif
        <div style="display:flex;gap:16px;font-size:13px">
            @if($dispute->worker_amount)
                <div><span style="color:#999">Mfanyakazi:</span> <strong>TZS {{ number_format($dispute->worker_amount) }}</strong></div>
            @endif
            @if($dispute->client_refund_amount)
                <div><span style="color:#999">Muhitaji (refund):</span> <strong>TZS {{ number_format($dispute->client_refund_amount) }}</strong></div>
            @endif
        </div>
        @if($dispute->resolvedByUser)
            <div style="margin-top:8px;font-size:11px;color:#999">
                Imeamuliwa na {{ $dispute->resolvedByUser->name }} — {{ $dispute->resolved_at->format('d M Y H:i') }}
            </div>
        @endif
    </div>
    @endif

    {{-- Messages --}}
    <div style="margin-bottom:16px">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:10px">Mazungumzo</h3>

        @forelse($dispute->messages as $msg)
        <div class="card" style="margin-bottom:8px;{{ $msg->is_admin ? 'border-left:3px solid #ef4444' : '' }}">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                <span style="font-weight:600;font-size:13px">
                    {{ $msg->user->name }}
                    @if($msg->is_admin) <span style="color:#ef4444;font-size:11px">(Admin)</span> @endif
                </span>
                <span style="color:#999;font-size:11px">{{ $msg->created_at->diffForHumans() }}</span>
            </div>
            <p style="margin:0;font-size:14px;color:#374151">{{ $msg->message }}</p>
            @if($msg->attachment)
                <a href="{{ url('image/' . $msg->attachment) }}" target="_blank" style="font-size:12px;color:#2563eb;margin-top:4px;display:inline-block">📎 Angalia Kiambatisho</a>
            @endif
        </div>
        @empty
        <div style="text-align:center;padding:20px;color:#999;font-size:13px">Hakuna ujumbe bado.</div>
        @endforelse
    </div>

    {{-- Add message (if dispute is open) --}}
    @if($dispute->isOpen())
    <div class="card">
        <h4 style="font-size:14px;font-weight:600;margin:0 0 8px">Ongeza Ujumbe</h4>
        <form method="POST" action="{{ route('disputes.show', $dispute) }}">
            @csrf
            <textarea name="message" rows="3" required placeholder="Andika ujumbe wako hapa..." style="margin-bottom:10px"></textarea>
            <button type="submit" class="btn btn-primary">Tuma Ujumbe</button>
        </form>
    </div>
    @endif

    <div style="text-align:center;margin-top:16px">
        <a href="{{ route('jobs.show', $dispute->job) }}" style="color:#666;font-size:13px">← Rudi kwenye kazi</a>
    </div>
</div>
@endsection
