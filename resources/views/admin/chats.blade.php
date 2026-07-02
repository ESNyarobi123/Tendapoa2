@extends('layouts.admin')
@section('title', 'Admin — Mazungumzo')

@section('content')
<div class="adm-page adm-chats-page">
    @php
        $heroTitle = isset($chatsForUser)
            ? 'Mazungumzo — '.$chatsForUser->name
            : 'Usimamizi wa Mazungumzo';
        $heroSubtitle = isset($chatsForUser)
            ? 'Kazi zenye ujumbe wa faragha kwa mtumiaji huyu'
            : 'Fuatilia mazungumzo ya faragha kati ya wateja na wafanyakazi';
        $heroActions = (isset($chatsForUser)
            ? '<a class="adm-btn adm-btn--ghost" href="'.route('admin.user.details', $chatsForUser).'">👤 Wasifu</a>'
            : '')
            .'<a class="adm-btn adm-btn--ghost" href="'.route('admin.dashboard').'">↩️ Dashibodi</a>';
    @endphp

    @include('admin.partials.page-hero', [
        'title' => $heroTitle,
        'subtitle' => $heroSubtitle,
        'icon' => '💬',
        'actions' => $heroActions,
    ])

    <div class="adm-stat-grid">
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">💬</span>
            <span class="adm-stat-tile__val">{{ number_format($stats['conversations'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Mazungumzo</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">📨</span>
            <span class="adm-stat-tile__val">{{ number_format($stats['messages'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Ujumbe</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">⚡</span>
            <span class="adm-stat-tile__val">{{ number_format($stats['active_today'] ?? 0) }}</span>
            <span class="adm-stat-tile__lbl">Hai leo</span>
        </div>
        <div class="adm-stat-tile">
            <span class="adm-stat-tile__ico" aria-hidden="true">📋</span>
            <span class="adm-stat-tile__val">{{ $conversations->total() }}</span>
            <span class="adm-stat-tile__lbl">Ukurasa huu</span>
        </div>
    </div>

    <form method="GET" action="{{ isset($chatsForUser) ? route('admin.user.chats', $chatsForUser) : route('admin.chats') }}" class="adm-filter-bar adm-card">
        <div class="adm-filter-bar__grid">
            <div class="adm-field">
                <label class="adm-label" for="chat-search">Tafuta kazi</label>
                <input type="search" id="chat-search" name="search" class="adm-input"
                    value="{{ $search ?? '' }}" placeholder="Kichwa cha kazi…">
            </div>
            <div class="adm-field adm-field--actions">
                <label class="adm-label adm-label--hidden">Vitendo</label>
                <div class="adm-filter-actions">
                    <button type="submit" class="adm-btn adm-btn--primary">Chuja</button>
                    <a href="{{ isset($chatsForUser) ? route('admin.user.chats', $chatsForUser) : route('admin.chats') }}" class="adm-btn adm-btn--ghost">Safisha</a>
                </div>
            </div>
        </div>
    </form>

    @if($conversations->count())
        <div class="adm-list">
            @foreach($conversations as $conv)
                @if($conv->job)
                    <article class="adm-list-card adm-card adm-chat-card">
                        <div class="adm-list-card__head">
                            <div class="adm-list-card__main">
                                <h2 class="adm-list-card__title">
                                    <a href="{{ route('admin.chat.view', $conv->job) }}">{{ $conv->job->title }}</a>
                                </h2>
                                <ul class="adm-meta-row">
                                    <li>👤 {{ $conv->job->muhitaji->name ?? '—' }}</li>
                                    <li>👷 {{ $conv->job->acceptedWorker->name ?? 'Hajateuliwa' }}</li>
                                    <li>🏷️ {{ $conv->job->category->name ?? '—' }}</li>
                                    <li>💬 {{ $conv->message_count }} ujumbe</li>
                                    <li>⏱️ {{ \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans() }}</li>
                                </ul>
                            </div>
                            <div class="adm-chat-count" aria-label="Idadi ya ujumbe">
                                {{ $conv->message_count }}
                            </div>
                        </div>

                        <div class="adm-list-card__actions">
                            <a href="{{ route('admin.chat.view', $conv->job) }}" class="adm-btn adm-btn--primary adm-btn--sm">👁️ Soma mazungumzo</a>
                            <a href="{{ route('admin.job.details', $conv->job) }}" class="adm-btn adm-btn--ghost adm-btn--sm">📋 Kazi</a>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>

        <div class="adm-pagination">
            {{ $conversations->links() }}
        </div>
    @else
        <div class="adm-empty adm-card">
            <span class="adm-empty__ico" aria-hidden="true">💬</span>
            <h3>Hakuna mazungumzo</h3>
            <p>Mazungumzo yataonekana hapa watumiaji wanapoanza kuwasiliana.</p>
        </div>
    @endif
</div>
@endsection
