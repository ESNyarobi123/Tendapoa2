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
@php use App\Models\Job; @endphp
<div class="adm-subpage adm-stack">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">Maelezo ya kazi</h1>
            <p class="adm-page-head-sub">{{ $job->title }}</p>
        </div>
        <div class="adm-actions">
            @if(!$job->accepted_worker_id)
                <form action="{{ route('admin.job.assign-worker', $job) }}" method="POST" class="adm-actions adm-assign-form" style="margin:0;flex-wrap:wrap;gap:0.5rem;">
                    @csrf
                    <select name="worker_id" class="adm-input" required style="min-width:12rem;">
                        <option value="">Chagua mfanyakazi…</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}" @selected($job->selected_worker_id == $worker->id)>{{ $worker->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="agreed_amount" class="adm-input" min="1000" value="{{ $job->agreed_amount ?: $job->price }}" style="width:8rem;" required>
                    <button type="submit" class="adm-btn adm-btn--accent">👷 Teua mfanyakazi</button>
                </form>
            @endif
            <form action="{{ route('admin.job.change-status', $job) }}" method="POST" class="adm-actions" style="margin:0;gap:0.5rem;">
                @csrf
                <select name="status" class="adm-input" required style="min-width:10rem;">
                    @foreach($statusOptions as $statusOption)
                        <option value="{{ $statusOption }}" @selected($job->status === $statusOption)>{{ $statusOption }}</option>
                    @endforeach
                </select>
                <button type="button" class="adm-btn adm-btn--muted"
                    onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Badilisha hali ya kazi?'):Promise.resolve(confirm('Badilisha hali?'))).then(function(ok){ if(ok) f.submit(); });">
                    Badilisha hali
                </button>
            </form>
            @if($job->status !== Job::S_COMPLETED && $job->status !== 'completed')
                <form action="{{ route('admin.job.force-complete', $job) }}" method="POST" class="adm-actions" style="margin:0;">
                    @csrf
                    <button type="button" class="adm-btn adm-btn--success"
                        onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Thibitisha force-complete kwa kazi hii?'):Promise.resolve(confirm('Force complete?'))).then(function(ok){ if(ok) f.submit(); });">
                        Force complete
                    </button>
                </form>
            @endif
            @if($job->status !== Job::S_CANCELLED && $job->status !== 'cancelled')
                <form action="{{ route('admin.job.force-cancel', $job) }}" method="POST" class="adm-actions" style="margin:0;">
                    @csrf
                    <button type="button" class="adm-btn adm-btn--danger"
                        onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Thibitisha kughairi kazi hii kwa nguvu?'):Promise.resolve(confirm('Force cancel?'))).then(function(ok){ if(ok) f.submit(); });">
                        Force cancel
                    </button>
                </form>
            @endif
            @if($job->isHidden())
                <form action="{{ route('admin.job.unhide', $job) }}" method="POST" class="adm-actions" style="margin:0;">
                    @csrf
                    <button type="submit" class="adm-btn adm-btn--accent">👁️ Onyesha kwa umma</button>
                </form>
            @else
                <form action="{{ route('admin.job.hide', $job) }}" method="POST" class="adm-actions" style="margin:0;gap:0.5rem;flex-wrap:wrap;">
                    @csrf
                    <input type="text" name="reason" class="adm-input" placeholder="Sababu ya kuficha (si lazima)" maxlength="500" style="min-width:14rem;">
                    <button type="button" class="adm-btn adm-btn--warn"
                        onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Ficha kazi hii? Mchapishaji ataiona tu kwenye orodha yake.'):Promise.resolve(confirm('Ficha kazi?'))).then(function(ok){ if(ok) f.submit(); });">
                        🙈 Ficha kazi
                    </button>
                </form>
            @endif
            <form action="{{ route('admin.job.delete', $job) }}" method="POST" class="adm-actions" style="margin:0;">
                @csrf
                @method('DELETE')
                <button type="button" class="adm-btn adm-btn--danger"
                    onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Futa kabisa rekodi ya kazi hii?'):Promise.resolve(confirm('Futa kazi?'))).then(function(ok){ if(ok) f.submit(); });">
                    🗑️ Futa kazi
                </button>
            </form>
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
                <span class="adm-k">Aina ya kazi</span>
                <span class="adm-v">@include('components.engagement-badge', ['job' => $job])</span>
            </div>
            @if($job->isServiceBooking() && $job->sourceListing)
                <div>
                    <span class="adm-k">Tangazo la chanzo</span>
                    <span class="adm-v">
                        <a href="{{ route('admin.job.details', $job->sourceListing) }}">{{ $job->sourceListing->title }}</a>
                        <span style="color:var(--adm-muted);font-size:0.75rem;"> (#{{ $job->source_listing_id }})</span>
                    </span>
                </div>
            @endif
            <div>
                <span class="adm-k">Mwonekano</span>
                <span class="adm-v">
                    @if($job->isHidden())
                        <span class="adm-pill adm-pill--hidden">Imefichwa</span>
                        @if($job->hidden_at)
                            <span style="display:block;font-size:0.75rem;color:var(--adm-muted);margin-top:0.25rem;">{{ $job->hidden_at->format('d M Y H:i') }}
                                @if($job->hiddenByAdmin) — {{ $job->hiddenByAdmin->name }} @endif
                            </span>
                        @endif
                        @if($job->hidden_reason)
                            <span style="display:block;font-size:0.75rem;color:var(--adm-muted);margin-top:0.15rem;">{{ $job->hidden_reason }}</span>
                        @endif
                    @else
                        Inaonekana kwa umma
                    @endif
                </span>
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
