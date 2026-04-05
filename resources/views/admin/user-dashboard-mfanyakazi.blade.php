@extends('layouts.admin')
@section('title', 'Admin — Dashibodi ya mfanyakazi')

@section('content')
<div class="adm-subpage adm-stack">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">Muonekano wa mfanyakazi</h1>
            <p class="adm-page-head-sub">{{ $user->name }} · {{ $user->email }}</p>
        </div>
        <div class="adm-actions">
            <a href="{{ route('admin.user.details', $user) }}" class="adm-btn adm-btn--primary">Wasifu kamili</a>
            <a href="{{ route('admin.users') }}" class="adm-btn adm-btn--muted">← Watumiaji</a>
        </div>
    </div>

    <div class="adm-stat-row">
        <div class="adm-stat-card">
            <span class="adm-k">Kazi zilizopewa</span>
            <div class="adm-v">{{ $stats['total_jobs'] }}</div>
        </div>
        <div class="adm-stat-card">
            <span class="adm-k">Zilizokamilika</span>
            <div class="adm-v" style="color:var(--adm-success);">{{ $stats['completed_jobs'] }}</div>
        </div>
        <div class="adm-stat-card">
            <span class="adm-k">Salio la pochi</span>
            <div class="adm-v" style="color:var(--adm-accent);">TSh {{ number_format($stats['wallet_balance']) }}</div>
        </div>
    </div>

    <div class="adm-card">
        <h2 class="adm-card-title">Kazi alizopewa</h2>
        @if($assignedJobs->isEmpty())
            <p style="color:var(--adm-muted);margin:0;">Hajapewa kazi bado.</p>
        @else
            <div class="adm-stack">
                @foreach($assignedJobs as $job)
                    <div class="adm-job-row">
                        <div style="min-width:0;flex:1;">
                            <div class="adm-v">{{ $job->title }}</div>
                            <div style="margin-top:0.35rem;display:flex;flex-wrap:wrap;gap:0.5rem 1rem;font-size:var(--adm-text-xs);color:var(--adm-muted);">
                                <span>{{ $job->category->name }}</span>
                                <span>TSh {{ number_format($job->amount) }}</span>
                                @php
                                    $jc = match ($job->status) {
                                        'completed' => 'adm-badge--ok',
                                        'in_progress', 'assigned', 'submitted', 'funded', 'awaiting_payment', 'open', 'offered', 'disputed' => 'adm-badge--warn',
                                        'cancelled', 'expired', 'refunded' => 'adm-badge--danger',
                                        default => 'adm-badge--info',
                                    };
                                @endphp
                                <span class="adm-badge {{ $jc }}">{{ $job->status }}</span>
                            </div>
                            <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);color:var(--adm-muted);">
                                Mteja: <a href="{{ route('admin.user.details', $job->muhitaji) }}" style="color:var(--adm-primary);font-weight:600;">{{ $job->muhitaji->name }}</a>
                            </p>
                        </div>
                        <div class="adm-job-row-actions">
                            <a href="{{ route('admin.job.details', $job) }}" class="adm-btn adm-btn--primary" style="font-size:var(--adm-text-xs);padding:0.35rem 0.65rem;">Maelezo</a>
                            <a href="{{ route('admin.chat.view', $job) }}" class="adm-btn adm-btn--success" style="font-size:var(--adm-text-xs);padding:0.35rem 0.65rem;">Mazungumzo</a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="adm-pagination">{{ $assignedJobs->links() }}</div>
        @endif
    </div>
</div>
@endsection
