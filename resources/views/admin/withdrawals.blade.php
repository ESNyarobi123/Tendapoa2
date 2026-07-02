@extends('layouts.admin')
@section('title', 'Admin — Utoaji wa Malipo')

@section('content')
<div class="adm-page">
    @include('admin.partials.page-hero', [
        'title' => 'Utoaji wa Malipo',
        'subtitle' => 'Simamia maombi ya kutoa pesa ya wafanyakazi',
        'icon' => '💰',
        'actions' => '<a class="adm-btn adm-btn--ghost" href="' . route('admin.dashboard') . '">↩️ Dashibodi</a>',
    ])

    <div class="adm-stat-grid">
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">📊</span>
            <span class="adm-stat-tile__val">{{ number_format($stats['total'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Jumla</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">⏳</span>
            <span class="adm-stat-tile__val">{{ number_format($stats['processing'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Inasubiri</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">✅</span>
            <span class="adm-stat-tile__val">{{ number_format($stats['paid'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Imelipwa</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">❌</span>
            <span class="adm-stat-tile__val">{{ number_format($stats['rejected'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Imekataliwa</span>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.withdrawals') }}" class="adm-filter-bar adm-card">
        <div class="adm-filter-bar__grid">
            <div class="adm-field">
                <label class="adm-label" for="wd-status">Hali</label>
                <select id="wd-status" name="status" class="adm-input adm-select">
                    <option value="">Hali zote</option>
                    <option value="PROCESSING" @selected(request('status') === 'PROCESSING')>Inasubiri</option>
                    <option value="PAID" @selected(request('status') === 'PAID')>Imelipwa</option>
                    <option value="REJECTED" @selected(request('status') === 'REJECTED')>Imekataliwa</option>
                </select>
            </div>
            <div class="adm-field adm-field--actions">
                <label class="adm-label adm-label--hidden">Vitendo</label>
                <div class="adm-filter-actions">
                    <button type="submit" class="adm-btn adm-btn--primary">Chuja</button>
                    <a href="{{ route('admin.withdrawals') }}" class="adm-btn adm-btn--ghost">Safisha</a>
                </div>
            </div>
        </div>
    </form>

    @if($items->count())
        <div class="adm-list">
            @foreach($items as $w)
                @php
                    $statusSlug = strtolower($w->status);
                @endphp
                <article class="adm-list-card adm-card">
                    <div class="adm-list-card__head">
                        <div class="adm-list-card__main">
                            <span class="adm-pill adm-pill--{{ $statusSlug }}">{{ strtoupper($w->status) }}</span>
                            <h2 class="adm-list-card__title">{{ $w->user->name ?? 'Mtumiaji' }}</h2>
                            <ul class="adm-meta-row">
                                <li>📱 {{ $w->account ?? '—' }}</li>
                                <li>📧 {{ $w->user->email ?? '—' }}</li>
                                <li>🆔 #{{ $w->id }}</li>
                                <li>⏱️ {{ $w->created_at?->diffForHumans() }}</li>
                            </ul>
                        </div>
                        <div class="adm-list-card__price">TZS {{ number_format($w->amount) }}</div>
                    </div>

                    <dl class="adm-dl-compact">
                        <div><dt>Nambari</dt><dd>{{ $w->account ?? '—' }}</dd></div>
                        <div><dt>Jina la usajili</dt><dd>{{ $w->registered_name ?? '—' }}</dd></div>
                        <div><dt>Mtandao</dt><dd>{{ ucfirst($w->network_type ?? '—') }}</dd></div>
                        <div><dt>Njia</dt><dd>{{ ucfirst(str_replace('_', ' ', $w->method ?? 'mobile_money')) }}</dd></div>
                        <div><dt>Imeombwa</dt><dd>{{ $w->created_at?->format('d M Y, H:i') }}</dd></div>
                        @if($w->updated_at && $w->updated_at != $w->created_at)
                            <div><dt>Imesasishwa</dt><dd>{{ $w->updated_at->format('d M Y, H:i') }}</dd></div>
                        @endif
                    </dl>

                    <div class="adm-list-card__actions">
                        @if($w->status !== 'PAID')
                            <form method="POST" action="{{ route('admin.withdrawals.paid', $w) }}" class="adm-inline-form">
                                @csrf
                                <button type="button" class="adm-btn adm-btn--success adm-btn--sm"
                                    onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Thibitisha malipo yamekamilika?'):Promise.resolve(confirm('Thibitisha PAID?'))).then(function(ok){ if(ok) f.submit(); });">
                                    ✅ Lipa
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.withdrawals.reject', $w) }}" class="adm-inline-form">
                                @csrf
                                <button type="button" class="adm-btn adm-btn--danger adm-btn--sm"
                                    onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Kataa ombi hili? Pesa zitarudi kwa mtumiaji.'):Promise.resolve(confirm('Kataa?'))).then(function(ok){ if(ok) f.submit(); });">
                                    ❌ Kataa
                                </button>
                            </form>
                        @else
                            <span class="adm-pill adm-pill--paid">Imelipwa tayari</span>
                        @endif
                        <a href="{{ route('admin.user.details', $w->user_id) }}" class="adm-btn adm-btn--ghost adm-btn--sm">👤 Wasifu</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="adm-pagination">
            {{ $items->links() }}
        </div>
    @else
        <div class="adm-empty adm-card">
            <span class="adm-empty__ico" aria-hidden="true">💰</span>
            <h3>Hakuna maombi</h3>
            <p>Hakuna maombi ya kutoa pesa yanayolingana na vichujio ulivyochagua.</p>
            <a href="{{ route('admin.withdrawals') }}" class="adm-btn adm-btn--primary">Onyesha yote</a>
        </div>
    @endif
</div>
@endsection
