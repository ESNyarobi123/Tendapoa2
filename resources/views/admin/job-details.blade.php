@extends('layouts.admin')
@section('title', 'Admin — Maelezo ya kazi')

@section('content')
@php
    $statusClass = match ($job->status) {
        'completed' => 'adm-badge--ok',
        'in_progress', 'assigned', 'submitted', 'funded', 'awaiting_payment', 'open', 'offered', 'ready_for_confirmation', 'disputed' => 'adm-badge--warn',
        'cancelled', 'expired', 'refunded' => 'adm-badge--danger',
        default => 'adm-badge--info',
    };
@endphp
<div class="adm-subpage adm-stack">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">Maelezo ya kazi</h1>
            <p class="adm-page-head-sub">{{ $job->title }}</p>
        </div>
        <div class="adm-actions">
            @if($job->status !== 'completed')
                <form action="{{ route('admin.job.force-complete', $job) }}" method="POST" class="adm-actions" style="margin:0;">
                    @csrf
                    <button type="button" class="adm-btn adm-btn--success"
                        onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Thibitisha force-complete kwa kazi hii?'):Promise.resolve(confirm('Force complete?'))).then(function(ok){ if(ok) f.submit(); });">
                        Force complete
                    </button>
                </form>
            @endif
            @if($job->status !== 'cancelled')
                <form action="{{ route('admin.job.force-cancel', $job) }}" method="POST" class="adm-actions" style="margin:0;">
                    @csrf
                    <button type="button" class="adm-btn adm-btn--danger"
                        onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Thibitisha kughairi kazi hii kwa nguvu?'):Promise.resolve(confirm('Force cancel?'))).then(function(ok){ if(ok) f.submit(); });">
                        Force cancel
                    </button>
                </form>
            @endif
            @if($job->accepted_worker_id)
                <a href="{{ route('admin.chat.view', $job) }}" class="adm-btn adm-btn--accent">💬 Mazungumzo</a>
            @endif
            <a href="{{ route('admin.jobs') }}" class="adm-btn adm-btn--muted">← Kazi zote</a>
        </div>
    </div>

    <div class="adm-card">
        <h2 class="adm-card-title">Taarifa za kazi</h2>
        <div class="adm-grid2">
            <div>
                <span class="adm-k">Kichwa</span>
                <span class="adm-v">{{ $job->title }}</span>
            </div>
            <div>
                <span class="adm-k">Hali</span>
                <span class="adm-badge {{ $statusClass }}">{{ $job->status }}</span>
            </div>
            <div>
                <span class="adm-k">Bei</span>
                <span class="adm-v">TSh {{ number_format($job->amount) }}</span>
            </div>
            <div>
                <span class="adm-k">Kundi</span>
                <span class="adm-v">{{ $job->category->name }}</span>
            </div>
            <div>
                <span class="adm-k">Aliyechapisha</span>
                <span class="adm-v"><a href="{{ route('admin.user.details', $job->muhitaji) }}">{{ $job->muhitaji->name }}</a></span>
            </div>
            <div>
                <span class="adm-k">Mfanyakazi</span>
                <span class="adm-v">
                    @if($job->acceptedWorker)
                        <a href="{{ route('admin.user.details', $job->acceptedWorker) }}">{{ $job->acceptedWorker->name }}</a>
                    @else
                        <span style="color:var(--adm-muted)">Hajateuliwa</span>
                    @endif
                </span>
            </div>
            <div class="adm-grid2-span">
                <span class="adm-k">Maelezo</span>
                <p class="adm-v" style="font-weight:500;margin:0;">{{ $job->description ?? 'Hakuna maelezo' }}</p>
            </div>
            <div>
                <span class="adm-k">Tarehe</span>
                <span class="adm-v">{{ $job->created_at->format('d M Y H:i') }}</span>
            </div>
            @if($job->completed_at)
                <div>
                    <span class="adm-k">Imekamilika</span>
                    <span class="adm-v">{{ $job->completed_at->format('d M Y H:i') }}</span>
                </div>
            @endif
        </div>
    </div>

    @if($job->accepted_worker_id && $job->privateMessages->count() > 0)
        <div class="adm-card">
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                <h2 class="adm-card-title" style="margin:0;">Ujumbe wa faragha ({{ $job->privateMessages->count() }})</h2>
                <a href="{{ route('admin.chat.view', $job) }}" style="font-size:var(--adm-text-sm);color:var(--adm-primary);font-weight:600;">Fungua mazungumzo →</a>
            </div>
            <div style="max-height:24rem;overflow-y:auto;display:flex;flex-direction:column;gap:0.5rem;">
                @foreach($job->privateMessages->take(10) as $message)
                    <div class="adm-msg-preview {{ $message->sender_id === $job->user_id ? 'adm-msg-preview--a' : 'adm-msg-preview--b' }}">
                        <span class="adm-v" style="font-size:var(--adm-text-xs);">{{ $message->sender->name }}</span>
                        <span style="color:var(--adm-muted);font-size:0.7rem;margin-left:0.35rem;">{{ $message->created_at->diffForHumans() }}</span>
                        <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);color:var(--adm-text);">{{ Str::limit($message->message, 150) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="adm-card">
        <h2 class="adm-card-title">Maoni na maombi ({{ $job->comments->count() }})</h2>
        @if($job->comments->isEmpty())
            <p style="color:var(--adm-muted);margin:0;">Hakuna maoni bado.</p>
        @else
            @foreach($job->comments as $comment)
                <div class="adm-comment">
                    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:0.5rem;margin-bottom:0.35rem;">
                        <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                            <span class="adm-v">{{ $comment->user->name }}</span>
                            @if($comment->is_application)
                                <span class="adm-badge adm-badge--ok">Maombi</span>
                            @endif
                        </div>
                        <span style="font-size:var(--adm-text-xs);color:var(--adm-muted);">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p style="margin:0;color:var(--adm-text);">{{ $comment->message }}</p>
                    @if($comment->bid_amount)
                        <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);color:var(--adm-accent);">Bei aliyopendekeza: TSh {{ number_format($comment->bid_amount) }}</p>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    @if($job->payment)
        <div class="adm-card">
            <h2 class="adm-card-title">Malipo</h2>
            <div class="adm-grid2">
                <div>
                    <span class="adm-k">Kiasi</span>
                    <span class="adm-v">TSh {{ number_format($job->payment->amount) }}</span>
                </div>
                <div>
                    <span class="adm-k">Hali</span>
                    <span class="adm-v">{{ $job->payment->status }}</span>
                </div>
                <div>
                    <span class="adm-k">Kumbukumbu</span>
                    <span class="adm-v">{{ $job->payment->reference ?? '—' }}</span>
                </div>
                <div>
                    <span class="adm-k">Tarehe</span>
                    <span class="adm-v">{{ $job->payment->created_at->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
