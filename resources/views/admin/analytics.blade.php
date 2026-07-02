@extends('layouts.admin')
@section('title', 'Admin — Takwimu')

@section('content')
<div class="adm-page adm-analytics-page">
    @include('admin.partials.page-hero', [
        'title' => 'Takwimu na Ripoti',
        'subtitle' => 'Chambua mapato, kazi, watumiaji, na utendaji wa mfumo',
        'icon' => '📊',
        'actions' => '<a class="adm-btn adm-btn--ghost" href="' . route('admin.dashboard') . '">↩️ Dashibodi</a>',
    ])

    <form method="GET" action="{{ route('admin.analytics') }}" class="adm-filter-bar adm-card">
        <div class="adm-filter-bar__grid">
            <div class="adm-field">
                <label class="adm-label" for="startDate">Tarehe ya kuanza</label>
                <input type="date" id="startDate" name="start_date" class="adm-input"
                    value="{{ $startDate->format('Y-m-d') }}" required>
            </div>
            <div class="adm-field">
                <label class="adm-label" for="endDate">Tarehe ya mwisho</label>
                <input type="date" id="endDate" name="end_date" class="adm-input"
                    value="{{ $endDate->format('Y-m-d') }}" required>
            </div>
            <div class="adm-field adm-field--actions">
                <label class="adm-label adm-label--hidden">Vitendo</label>
                <div class="adm-filter-actions">
                    <button type="submit" class="adm-btn adm-btn--primary">Sasisha</button>
                    <a href="{{ route('admin.analytics', ['period' => 30]) }}" class="adm-btn adm-btn--ghost">Siku 30</a>
                </div>
            </div>
        </div>
        <p class="adm-analytics-period">Kipindi: siku {{ (int) $period }} ({{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }})</p>
    </form>

    <div class="adm-stat-grid adm-analytics-stats">
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">📦</span>
            <span class="adm-stat-tile__val">{{ number_format($totalJobs) }}</span>
            <span class="adm-stat-tile__lbl">Kazi</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">💰</span>
            <span class="adm-stat-tile__val">{{ number_format($totalRevenue) }}</span>
            <span class="adm-stat-tile__lbl">Mapato TZS</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">🏦</span>
            <span class="adm-stat-tile__val">{{ number_format($totalCommissions) }}</span>
            <span class="adm-stat-tile__lbl">Kamisheni TZS</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">👥</span>
            <span class="adm-stat-tile__val">{{ number_format($activeUsers) }}</span>
            <span class="adm-stat-tile__lbl">Watumiaji hai</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">⭐</span>
            <span class="adm-stat-tile__val">{{ number_format($completionRate, 1) }}%</span>
            <span class="adm-stat-tile__lbl">Ukamilishaji</span>
        </div>
    </div>

    <div class="adm-analytics-grid adm-analytics-grid--wide">
        <div class="adm-card adm-chart-card">
            <h2 class="adm-card-title">💰 Mwenendo wa mapato</h2>
            <div class="adm-chart-canvas">
                <canvas id="revenueChart" aria-label="Mwenendo wa mapato"></canvas>
            </div>
        </div>
        <div class="adm-card adm-chart-card">
            <h2 class="adm-card-title">🏷️ Makundi ya kazi</h2>
            <div class="adm-chart-canvas adm-chart-canvas--compact">
                <canvas id="categoryChart" aria-label="Mgawanyo wa makundi"></canvas>
            </div>
        </div>
    </div>

    <div class="adm-card">
        <div class="adm-analytics-section-head">
            <h2 class="adm-card-title">🏆 Wafanyakazi bora</h2>
            <a href="{{ route('admin.completed-jobs') }}" class="adm-btn adm-btn--ghost adm-btn--sm">Ona wote</a>
        </div>
        @if($topWorkers->count())
            <ul class="adm-log-list">
                @foreach($topWorkers as $worker)
                    <li class="adm-log-item adm-analytics-worker">
                        <div class="adm-worker-avatar" aria-hidden="true">{{ strtoupper(substr($worker->name, 0, 2)) }}</div>
                        <div class="adm-log-body">
                            <div class="adm-log-head">
                                <div class="adm-log-user">
                                    <strong>{{ $worker->name }}</strong>
                                    <span class="adm-pill adm-pill--completed">{{ $worker->completed_jobs ?? 0 }} kazi</span>
                                </div>
                                <span class="adm-log-time">TZS {{ number_format($worker->total_earned ?? 0) }}</span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="adm-empty adm-empty--compact">
                <p>Hakuna data ya wafanyakazi kwa kipindi hiki.</p>
            </div>
        @endif
    </div>

    <div class="adm-analytics-grid">
        <div class="adm-card adm-chart-card">
            <h2 class="adm-card-title">👥 Ukuaji wa watumiaji</h2>
            <div class="adm-chart-canvas">
                <canvas id="userGrowthChart" aria-label="Ukuaji wa watumiaji"></canvas>
            </div>
        </div>
        <div class="adm-card adm-chart-card">
            <h2 class="adm-card-title">📋 Hali za kazi</h2>
            <div class="adm-chart-canvas adm-chart-canvas--compact">
                <canvas id="jobStatusChart" aria-label="Mgawanyo wa hali za kazi"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@php
    $revenueLabels = $revenueTrend->pluck('date')->map(fn ($d) => date('d M', strtotime($d)))->values()->all();
    $revenueData = $revenueTrend->pluck('total')->values()->all();
    $userGrowthLabels = $userGrowth->pluck('date')->map(fn ($d) => date('d M', strtotime($d)))->values()->all();
    $userGrowthData = $userGrowth->pluck('count')->values()->all();
    $categoryLabels = $categoryDistribution->pluck('name')->values()->all();
    $categoryData = $categoryDistribution->pluck('jobs_count')->values()->all();
    $statusLabels = array_map(fn ($s) => str_replace('_', ' ', ucfirst($s)), array_keys($jobStatuses));
    $statusData = array_values($jobStatuses);
    $chartColors = ['#818cf8', '#34d399', '#fbbf24', '#f472b6', '#22d3ee', '#fb7185', '#a78bfa'];
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(255,255,255,0.08)';

    const tooltip = {
        backgroundColor: 'rgba(15, 23, 42, 0.92)',
        titleColor: '#f8fafc',
        bodyColor: '#cbd5e1',
        borderColor: 'rgba(129, 140, 248, 0.25)',
        borderWidth: 1,
        padding: 10,
    };

    const scaleOpts = {
        y: { beginAtZero: true, ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
        x: { ticks: { color: '#94a3b8', maxRotation: 0 }, grid: { color: 'rgba(255,255,255,0.03)' } },
    };

    const revenueEl = document.getElementById('revenueChart');
    if (revenueEl) {
        new Chart(revenueEl, {
            type: 'line',
            data: {
                labels: @json($revenueLabels),
                datasets: [{
                    label: 'Mapato (TZS)',
                    data: @json($revenueData),
                    borderColor: '#818cf8',
                    backgroundColor: 'rgba(129, 140, 248, 0.12)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip },
                scales: scaleOpts,
            },
        });
    }

    const categoryEl = document.getElementById('categoryChart');
    if (categoryEl) {
        new Chart(categoryEl, {
            type: 'doughnut',
            data: {
                labels: @json($categoryLabels),
                datasets: [{
                    data: @json($categoryData),
                    backgroundColor: @json($chartColors).map(c => c + 'cc'),
                    borderColor: @json($chartColors),
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#94a3b8', boxWidth: 10, padding: 8 } },
                    tooltip,
                },
            },
        });
    }

    const userEl = document.getElementById('userGrowthChart');
    if (userEl) {
        new Chart(userEl, {
            type: 'bar',
            data: {
                labels: @json($userGrowthLabels),
                datasets: [{
                    label: 'Watumiaji wapya',
                    data: @json($userGrowthData),
                    backgroundColor: 'rgba(52, 211, 153, 0.75)',
                    borderColor: '#34d399',
                    borderWidth: 1,
                    borderRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip },
                scales: scaleOpts,
            },
        });
    }

    const statusEl = document.getElementById('jobStatusChart');
    if (statusEl) {
        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: @json($statusLabels),
                datasets: [{
                    data: @json($statusData),
                    backgroundColor: @json($chartColors).map(c => c + 'cc'),
                    borderColor: @json($chartColors),
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#94a3b8', boxWidth: 10, padding: 8 } },
                    tooltip,
                },
            },
        });
    }
});
</script>
@endpush
@endsection
