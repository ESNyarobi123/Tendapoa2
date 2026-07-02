@extends('layouts.admin')
@section('title', 'Admin — Job Management')

@php use App\Models\Job; @endphp

@section('content')
<div class="adm-page">
    @include('admin.partials.page-hero', [
        'title' => 'Usimamizi wa Kazi',
        'subtitle' => 'Dhibiti na simamia kazi zote za mfumo',
        'icon' => '📋',
        'actions' => '<a class="adm-btn adm-btn--ghost" href="' . route('dashboard') . '">↩️ Rudi</a>
            <a class="adm-btn adm-btn--primary" href="' . route('jobs.create') . '">➕ Chapisha Kazi</a>',
    ])

    @if(!empty($filterUser))
        <div class="adm-filter-banner">
            <span>Kazi za mtumiaji: <strong>{{ $filterUser->name }}</strong> (ID {{ $filterUser->id }})</span>
            <a href="{{ route('admin.jobs') }}">Ondoa kichujio</a>
        </div>
    @endif

    <div class="adm-stat-grid">
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">📦</span>
            <span class="adm-stat-tile__val">{{ number_format($jobStats['total'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Jumla</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">📝</span>
            <span class="adm-stat-tile__val">{{ number_format($jobStats['posted'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Posted</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">⏳</span>
            <span class="adm-stat-tile__val">{{ number_format($jobStats['in_progress'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">In Progress</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">✅</span>
            <span class="adm-stat-tile__val">{{ number_format($jobStats['completed'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Completed</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">❌</span>
            <span class="adm-stat-tile__val">{{ number_format($jobStats['cancelled'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Cancelled</span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.jobs') }}" class="adm-filter-bar adm-card">
        @if(!empty($filterUser))
            <input type="hidden" name="user" value="{{ $filterUser->id }}">
        @endif
        <div class="adm-filter-bar__grid">
            <div class="adm-field">
                <label class="adm-label" for="search">Tafuta</label>
                <input type="search" id="search" name="search" class="adm-input"
                    value="{{ request('search') }}" placeholder="Kichwa au maelezo…">
            </div>
            <div class="adm-field">
                <label class="adm-label" for="status">Hali</label>
                <select id="status" name="status" class="adm-input adm-select">
                    <option value="">Hali zote</option>
                    @foreach(['open','posted','awaiting_payment','funded','assigned','in_progress','submitted','completed','cancelled','disputed'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ str_replace('_', ' ', ucfirst($st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="adm-field">
                <label class="adm-label" for="category">Kategoria</label>
                <select id="category" name="category" class="adm-input adm-select">
                    <option value="">Kategoria zote</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="adm-field">
                <label class="adm-label" for="visibility">Mwonekano</label>
                <select id="visibility" name="visibility" class="adm-input adm-select">
                    <option value="">Zote</option>
                    <option value="visible" @selected(request('visibility') === 'visible')>Zinazoonekana</option>
                    <option value="hidden" @selected(request('visibility') === 'hidden')>Zilizofichwa</option>
                </select>
            </div>
            <div class="adm-field">
                <label class="adm-label" for="engagement">Aina</label>
                <select id="engagement" name="engagement" class="adm-input adm-select">
                    <option value="">Aina zote</option>
                    <option value="{{ Job::ENGAGEMENT_JOB_REQUEST }}" @selected(request('engagement') === Job::ENGAGEMENT_JOB_REQUEST)>Ombi la Kazi</option>
                    <option value="{{ Job::ENGAGEMENT_SERVICE_LISTING }}" @selected(request('engagement') === Job::ENGAGEMENT_SERVICE_LISTING)>Tangazo la Huduma</option>
                    <option value="{{ Job::ENGAGEMENT_SERVICE_BOOKING }}" @selected(request('engagement') === Job::ENGAGEMENT_SERVICE_BOOKING)>Agizo la Huduma</option>
                </select>
            </div>
            <div class="adm-field adm-field--actions">
                <label class="adm-label adm-label--hidden">Vitendo</label>
                <div class="adm-filter-actions">
                    <button type="submit" class="adm-btn adm-btn--primary">Chuja</button>
                    <a href="{{ route('admin.jobs', $filterUser ? ['user' => $filterUser->id] : []) }}" class="adm-btn adm-btn--ghost">Safisha</a>
                </div>
            </div>
        </div>
    </form>

    @if($jobs->count())
        <div class="adm-list">
            @foreach($jobs as $job)
                @php
                    $statusSlug = str_replace('_', '-', $job->status);
                @endphp
                <article class="adm-list-card adm-card {{ $job->isHidden() ? 'adm-list-card--hidden' : '' }}">
                    <div class="adm-list-card__head">
                        <div class="adm-list-card__main">
                            @if($job->isHidden())
                                <span class="adm-pill adm-pill--hidden">IMEFICHWA</span>
                            @endif
                            @include('components.engagement-badge', ['job' => $job])
                            <span class="adm-pill adm-pill--{{ $statusSlug }}">{{ strtoupper(str_replace('_', ' ', $job->status)) }}</span>
                            <h2 class="adm-list-card__title">
                                <a href="{{ route('admin.job.details', $job) }}">{{ $job->title }}</a>
                            </h2>
                            <ul class="adm-meta-row">
                                <li>👤 {{ $job->muhitaji->name ?? '—' }}</li>
                                <li>🏷️ {{ $job->category->name ?? '—' }}</li>
                                <li>📍 {{ $job->location ?? '—' }}</li>
                                <li>⏱️ {{ $job->created_at?->diffForHumans() }}</li>
                            </ul>
                        </div>
                        <div class="adm-list-card__price">TZS {{ number_format($job->price ?? 0) }}</div>
                    </div>

                    <dl class="adm-dl-compact">
                        <div><dt>Maelezo</dt><dd>{{ Str::limit($job->description ?? '—', 80) }}</dd></div>
                        <div><dt>Mfanyakazi</dt><dd>{{ $job->acceptedWorker->name ?? 'Hajateuliwa' }}</dd></div>
                        @if($job->isServiceBooking() && $job->source_listing_id)
                            <div><dt>Tangazo la chanzo</dt><dd><a href="{{ route('admin.job.details', $job->source_listing_id) }}">#{{ $job->source_listing_id }}</a></dd></div>
                        @endif
                        <div><dt>Imeundwa</dt><dd>{{ $job->created_at?->format('d M Y, H:i') }}</dd></div>
                        @if($job->completed_at)
                            <div><dt>Imekamilika</dt><dd>{{ $job->completed_at->format('d M Y, H:i') }}</dd></div>
                        @endif
                    </dl>

                    <div class="adm-list-card__actions">
                        <a class="adm-btn adm-btn--ghost adm-btn--sm" href="{{ route('admin.job.details', $job) }}">👁️ Maelezo</a>
                        @if(!$job->accepted_worker_id && in_array($job->status, ['open', 'posted', 'awaiting_payment', Job::S_OPEN, Job::S_AWAITING_PAYMENT], true))
                            <button type="button" class="adm-btn adm-btn--warn adm-btn--sm"
                                onclick="admOpenAssignModal({{ $job->id }}, @json($job->title), {{ (int) ($job->price ?? 0) }})">
                                👷 Teua mfanyakazi
                            </button>
                        @endif
                        @if(!in_array($job->status, ['completed', 'cancelled', Job::S_COMPLETED, Job::S_CANCELLED], true))
                            <form method="POST" action="{{ route('admin.job.force-cancel', $job) }}" class="adm-inline-form">
                                @csrf
                                <button type="button" class="adm-btn adm-btn--danger adm-btn--sm"
                                    data-adm-confirm="Thibitisha kughairi kazi hii?">
                                    ❌ Ghairi
                                </button>
                            </form>
                        @endif
                        @if($job->isHidden())
                            <form method="POST" action="{{ route('admin.job.unhide', $job) }}" class="adm-inline-form">
                                @csrf
                                <button type="submit" class="adm-btn adm-btn--primary adm-btn--sm">👁️ Onyesha tena</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.job.hide', $job) }}" class="adm-inline-form">
                                @csrf
                                <button type="button" class="adm-btn adm-btn--warn adm-btn--sm"
                                    data-adm-prompt-hide="Sababu ya kuficha (si lazima):"
                                    data-adm-confirm="Ficha kazi hii kutoka kwa watumiaji wengine?">
                                    🙈 Ficha
                                </button>
                            </form>
                        @endif
                        @if(!in_array($job->status, ['completed', 'cancelled', Job::S_COMPLETED, Job::S_CANCELLED], true))
                            <form method="POST" action="{{ route('admin.job.delete', $job) }}" class="adm-inline-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="adm-btn adm-btn--ghost adm-btn--sm"
                                    data-adm-confirm="Futa kabisa rekodi ya kazi hii? Hatua haiwezi kutenduliwa.">
                                    🗑️ Futa
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="adm-pagination">
            {{ $jobs->links() }}
        </div>
    @else
        <div class="adm-empty adm-card">
            <span class="adm-empty__ico" aria-hidden="true">📋</span>
            <h3>Hakuna kazi</h3>
            <p>Hakuna kazi zinazolingana na vichujio ulivyochagua.</p>
            <a href="{{ route('admin.jobs') }}" class="adm-btn adm-btn--primary">Onyesha zote</a>
        </div>
    @endif
</div>

<div id="assign-worker-modal" class="adm-modal hidden" role="dialog" aria-modal="true" aria-labelledby="assign-modal-title">
    <div class="adm-modal-backdrop" data-adm-modal-close></div>
    <div class="adm-modal-panel">
        <h3 id="assign-modal-title" class="adm-modal-title">Teua mfanyakazi</h3>
        <p id="assign-modal-job" class="adm-modal-sub"></p>
        <form id="assign-worker-form" method="POST" action="">
            @csrf
            <label class="adm-label" for="assign_worker_id">Mfanyakazi</label>
            <select id="assign_worker_id" name="worker_id" class="adm-input adm-select" required>
                <option value="">Chagua mfanyakazi…</option>
                @foreach($workers as $worker)
                    <option value="{{ $worker->id }}">{{ $worker->name }} ({{ $worker->email }})</option>
                @endforeach
            </select>
            <label class="adm-label adm-label--spaced" for="assign_agreed_amount">Kiasi kilichokubaliwa (TZS)</label>
            <input type="number" id="assign_agreed_amount" name="agreed_amount" class="adm-input" min="1000" step="1" required>
            <div class="adm-modal-actions">
                <button type="button" class="adm-btn adm-btn--ghost" data-adm-modal-close>Funga</button>
                <button type="submit" class="adm-btn adm-btn--primary">Hifadhi</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>window.__ADM_JOBS_BASE = @json(rtrim(url('/admin/jobs'), '/'));</script>
@endpush
@endsection
