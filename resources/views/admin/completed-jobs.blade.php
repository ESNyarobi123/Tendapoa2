@extends('layouts.admin')
@section('title', 'Admin — Kazi Zilizokamilika')

@section('content')
<div class="adm-page" id="completed-jobs-page">
    @include('admin.partials.page-hero', [
        'title' => 'Kazi Zilizokamilika',
        'subtitle' => 'Fuatilia utendaji wa wafanyakazi na kazi walizokamilisha',
        'icon' => '✅',
        'actions' => '<a class="adm-btn adm-btn--ghost" href="' . route('admin.dashboard') . '">↩️ Dashibodi</a>
            <a class="adm-btn adm-btn--primary" href="' . route('admin.analytics') . '">📊 Analytics</a>',
    ])

    <div class="adm-stat-grid">
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">✅</span>
            <span class="adm-stat-tile__val">{{ number_format($totalCompletedJobs) }}</span>
            <span class="adm-stat-tile__lbl">Kazi zilizokamilika</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">💰</span>
            <span class="adm-stat-tile__val">{{ number_format($totalEarnings) }}</span>
            <span class="adm-stat-tile__lbl">Mapato (TZS)</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">👷</span>
            <span class="adm-stat-tile__val">{{ number_format($activeWorkers ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Wafanyakazi hai</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">📊</span>
            <span class="adm-stat-tile__val">{{ $averageJobsPerWorker }}</span>
            <span class="adm-stat-tile__lbl">Wastani kazi/mfanyakazi</span>
        </div>
        @if(!empty($topWorker) && ($topWorker->completed_jobs ?? 0) > 0)
            <div class="adm-stat-tile">
                <span class="adm-stat-tile__ico" aria-hidden="true">🏆</span>
                <span class="adm-stat-tile__val" style="font-size:0.95rem;">{{ Str::limit($topWorker->name, 12) }}</span>
                <span class="adm-stat-tile__lbl">Bora ({{ $topWorker->completed_jobs }} kazi)</span>
            </div>
        @endif
    </div>

    <div class="adm-filter-bar adm-card">
        <div class="adm-filter-bar__grid">
            <div class="adm-field">
                <label class="adm-label" for="cj-search">Tafuta mfanyakazi</label>
                <input type="search" id="cj-search" class="adm-input" placeholder="Jina la mfanyakazi…" autocomplete="off">
            </div>
            <div class="adm-field">
                <label class="adm-label" for="cj-sort">Panga kwa</label>
                <select id="cj-sort" class="adm-input adm-select">
                    <option value="jobs">Kazi nyingi</option>
                    <option value="earnings">Mapato makubwa</option>
                    <option value="name">Jina A–Z</option>
                </select>
            </div>
            <div class="adm-field adm-field--actions">
                <label class="adm-label adm-label--hidden">Vitendo</label>
                <div class="adm-filter-actions">
                    <button type="button" id="cj-clear" class="adm-btn adm-btn--ghost">Safisha</button>
                </div>
            </div>
        </div>
    </div>

    @if($workers->count())
        <div class="adm-worker-grid" id="cj-worker-grid">
            @php
                $maxJobs = max(1, $workers->max('completed_jobs') ?? 1);
            @endphp
            @foreach($workers as $worker)
                @php
                    $earnings = (int) $worker->assignedJobs->sum('price');
                    $avgPerJob = $worker->completed_jobs > 0 ? round($earnings / $worker->completed_jobs) : 0;
                    $last30 = $worker->assignedJobs->where('completed_at', '>=', now()->subDays(30))->count();
                    $progressPct = min(100, round(($worker->completed_jobs / $maxJobs) * 100));
                @endphp
                <article class="adm-card adm-worker-card"
                    data-name="{{ strtolower($worker->name) }}"
                    data-jobs="{{ $worker->completed_jobs }}"
                    data-earnings="{{ $earnings }}">
                    <div class="adm-worker-card__head">
                        <div class="adm-worker-avatar" aria-hidden="true">{{ strtoupper(substr($worker->name, 0, 2)) }}</div>
                        <div class="min-w-0">
                            <h2 class="adm-worker-card__name">{{ $worker->name }}</h2>
                            <p class="adm-worker-card__meta">{{ $worker->email }} · Alijiunga {{ $worker->created_at?->format('M Y') }}</p>
                        </div>
                    </div>

                    <div class="adm-worker-metrics">
                        <div class="adm-worker-metric">
                            <span class="adm-worker-metric__val">{{ $worker->completed_jobs }}</span>
                            <span class="adm-worker-metric__lbl">Kazi</span>
                        </div>
                        <div class="adm-worker-metric">
                            <span class="adm-worker-metric__val">{{ number_format($earnings) }}</span>
                            <span class="adm-worker-metric__lbl">Mapato TZS</span>
                        </div>
                        <div class="adm-worker-metric">
                            <span class="adm-worker-metric__val">{{ number_format($avgPerJob) }}</span>
                            <span class="adm-worker-metric__lbl">Wastani/kazi</span>
                        </div>
                        <div class="adm-worker-metric">
                            <span class="adm-worker-metric__val">{{ $last30 }}</span>
                            <span class="adm-worker-metric__lbl">Siku 30</span>
                        </div>
                    </div>

                    <div class="adm-worker-progress">
                        <div class="adm-worker-progress__label">
                            <span>Utendaji</span>
                            <span>{{ $progressPct }}%</span>
                        </div>
                        <div class="adm-worker-progress__bar" role="presentation">
                            <div class="adm-worker-progress__fill" style="width: {{ $progressPct }}%"></div>
                        </div>
                    </div>

                    <div class="adm-worker-jobs">
                        <h3 class="adm-worker-jobs__title">Kazi za hivi karibuni</h3>
                        @forelse($worker->assignedJobs->take(4) as $job)
                            <div class="adm-worker-job-row">
                                <div class="min-w-0">
                                    <p class="adm-worker-job-row__title">{{ Str::limit($job->title, 40) }}</p>
                                    <p class="adm-worker-job-row__sub">
                                        {{ $job->muhitaji->name ?? '—' }} · {{ $job->category->name ?? '—' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="adm-worker-job-row__price">{{ number_format($job->price) }} TZS</span>
                                    <span class="adm-worker-job-row__date">{{ $job->completed_at?->format('d M Y') ?? '—' }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="adm-worker-card__meta" style="margin:0;">Hakuna kazi zilizokamilika bado.</p>
                        @endforelse
                        @if($worker->assignedJobs->count() > 4)
                            <a href="{{ route('admin.jobs', ['user' => $worker->id, 'status' => 'completed']) }}" class="adm-btn adm-btn--ghost adm-btn--sm" style="align-self:center;">
                                Ona zote {{ $worker->assignedJobs->count() }}
                            </a>
                        @endif
                    </div>

                    <div class="adm-worker-card__actions">
                        <a href="{{ route('admin.user.details', $worker) }}" class="adm-btn adm-btn--ghost adm-btn--sm">👤 Wasifu</a>
                        <a href="{{ route('admin.user.dashboard', $worker) }}" class="adm-btn adm-btn--primary adm-btn--sm">📊 Dashibodi</a>
                        <a href="{{ route('admin.jobs', ['user' => $worker->id]) }}" class="adm-btn adm-btn--ghost adm-btn--sm">📋 Kazi zake</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="adm-pagination">
            {{ $workers->links() }}
        </div>
    @else
        <div class="adm-empty adm-card">
            <span class="adm-empty__ico" aria-hidden="true">👷</span>
            <h3>Hakuna wafanyakazi</h3>
            <p>Hakuna wafanyakazi wenye kazi zilizokamilika kwa sasa.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
(function () {
  const grid = document.getElementById('cj-worker-grid');
  const search = document.getElementById('cj-search');
  const sort = document.getElementById('cj-sort');
  const clearBtn = document.getElementById('cj-clear');
  if (!grid) return;

  function cards() {
    return Array.from(grid.querySelectorAll('.adm-worker-card'));
  }

  function applySearch() {
    const q = (search?.value || '').trim().toLowerCase();
    cards().forEach((card) => {
      const name = card.dataset.name || '';
      card.style.display = !q || name.includes(q) ? '' : 'none';
    });
  }

  function applySort() {
    const mode = sort?.value || 'jobs';
    const sorted = cards().filter((c) => c.style.display !== 'none').sort((a, b) => {
      if (mode === 'name') return (a.dataset.name || '').localeCompare(b.dataset.name || '');
      if (mode === 'earnings') return parseInt(b.dataset.earnings || 0, 10) - parseInt(a.dataset.earnings || 0, 10);
      return parseInt(b.dataset.jobs || 0, 10) - parseInt(a.dataset.jobs || 0, 10);
    });
    sorted.forEach((card) => grid.appendChild(card));
  }

  search?.addEventListener('input', applySearch);
  sort?.addEventListener('change', () => { applySort(); });
  clearBtn?.addEventListener('click', () => {
    if (search) search.value = '';
    if (sort) sort.value = 'jobs';
    cards().forEach((c) => { c.style.display = ''; });
    applySort();
  });
})();
</script>
@endpush
@endsection
