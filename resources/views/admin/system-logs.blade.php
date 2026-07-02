@extends('layouts.admin')
@section('title', 'Admin — Kumbukumbu za Mfumo')

@section('content')
<div class="adm-page adm-logs-page">
    @include('admin.partials.page-hero', [
        'title' => 'Shughuli za Mfumo',
        'subtitle' => 'Fuatilia kazi, ujumbe, na malipo ya hivi karibuni (data halisi kutoka database).',
        'icon' => '📋',
        'actions' => '<a class="adm-btn adm-btn--ghost" href="' . route('admin.dashboard') . '">↩️ Dashibodi</a>
            <a class="adm-btn adm-btn--primary" href="' . route('admin.system-logs') . '">🔄 Onyesha upya</a>',
    ])

    <div class="adm-stat-grid adm-logs-stat-grid">
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">📊</span>
            <span class="adm-stat-tile__val">{{ number_format($stats['total'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Jumla (hivi karibuni)</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">💼</span>
            <span class="adm-stat-tile__val">{{ number_format($stats['jobs'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Kazi</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">💬</span>
            <span class="adm-stat-tile__val">{{ number_format($stats['messages'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Ujumbe</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">💰</span>
            <span class="adm-stat-tile__val">{{ number_format($stats['payments'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Malipo</span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.system-logs') }}" class="adm-filter-bar adm-card">
        <div class="adm-filter-bar__grid">
            <div class="adm-field">
                <label class="adm-label" for="log-search">Tafuta</label>
                <input type="search" id="log-search" name="search" class="adm-input"
                    value="{{ $search ?? '' }}" placeholder="Mtumiaji au maelezo…">
            </div>
            <div class="adm-field">
                <label class="adm-label" for="log-type">Aina</label>
                <select id="log-type" name="type" class="adm-input adm-select">
                    <option value="">Zote</option>
                    <option value="job_created" @selected(($filter ?? 'all') === 'job_created')>Kazi</option>
                    <option value="message_sent" @selected(($filter ?? 'all') === 'message_sent')>Ujumbe</option>
                    <option value="payment_made" @selected(($filter ?? 'all') === 'payment_made')>Malipo</option>
                </select>
            </div>
            <div class="adm-field adm-field--actions">
                <label class="adm-label adm-label--hidden">Vitendo</label>
                <div class="adm-filter-actions">
                    <button type="submit" class="adm-btn adm-btn--primary">Chuja</button>
                    <a href="{{ route('admin.system-logs') }}" class="adm-btn adm-btn--ghost">Safisha</a>
                </div>
            </div>
        </div>
    </form>

    <div class="adm-card adm-logs-panel">
        <div class="adm-logs-panel__head">
            <h2 class="adm-card-title">Shughuli za hivi karibuni</h2>
            <p class="adm-logs-panel__sub">Inaonyesha hadi shughuli 100 za karibuni zaidi</p>
        </div>

        @if($activities->count())
            <ul class="adm-log-list">
                @foreach($activities as $activity)
                    @php
                        $type = $activity['type'];
                        $typeLabel = match($type) {
                            'job_created' => 'Kazi',
                            'message_sent' => 'Ujumbe',
                            'payment_made' => 'Malipo',
                            default => 'Nyingine',
                        };
                        $typeClass = match($type) {
                            'job_created' => 'adm-log-icon--job',
                            'message_sent' => 'adm-log-icon--msg',
                            'payment_made' => 'adm-log-icon--pay',
                            default => '',
                        };
                        $icon = match($type) {
                            'job_created' => '💼',
                            'message_sent' => '💬',
                            'payment_made' => '💰',
                            default => '📌',
                        };
                    @endphp
                    <li class="adm-log-item">
                        <div class="adm-log-icon {{ $typeClass }}" aria-hidden="true">{{ $icon }}</div>
                        <div class="adm-log-body">
                            <div class="adm-log-head">
                                <div class="adm-log-user">
                                    <strong>{{ $activity['user']->name ?? 'Mtumiaji' }}</strong>
                                    <span class="adm-pill adm-pill--posted">{{ ucfirst($activity['user']->role ?? 'user') }}</span>
                                    <span class="adm-pill adm-pill--{{ str_replace('_', '-', $type) }}">{{ $typeLabel }}</span>
                                </div>
                                <time class="adm-log-time" datetime="{{ $activity['timestamp']->toIso8601String() }}">
                                    {{ $activity['timestamp']->diffForHumans() }}
                                </time>
                            </div>
                            <p class="adm-log-desc">{{ $activity['description'] }}</p>
                            @if(isset($activity['data']))
                                <div class="adm-log-meta">
                                    @if($type === 'job_created')
                                        <span>ID #{{ $activity['data']->id }}</span>
                                        <span>TZS {{ number_format($activity['data']->price ?? $activity['data']->budget ?? 0) }}</span>
                                    @elseif($type === 'message_sent')
                                        <span>Msg #{{ $activity['data']->id }}</span>
                                        <span>Kwa: {{ $activity['data']->receiver->name ?? '—' }}</span>
                                    @elseif($type === 'payment_made')
                                        <span>Malipo #{{ $activity['data']->id }}</span>
                                        <span>TZS {{ number_format($activity['data']->amount ?? 0) }}</span>
                                    @endif
                                    <span>{{ $activity['timestamp']->format('d M Y, H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="adm-empty">
                <span class="adm-empty__ico" aria-hidden="true">📋</span>
                <h3>Hakuna shughuli</h3>
                <p>Hakuna shughuli zinazolingana na vichujio ulivyochagua.</p>
                <a href="{{ route('admin.system-logs') }}" class="adm-btn adm-btn--primary">Onyesha zote</a>
            </div>
        @endif
    </div>
</div>
@endsection
