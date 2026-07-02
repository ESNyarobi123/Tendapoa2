@php
    $type = (string) ($notification->data['type'] ?? 'info');
    $icon = '🔔';
    $iconMod = 'slate';
    $typeLabel = 'Taarifa';

    if ($type === 'admin_message' || str_contains($type, 'admin')) {
        $icon = '📢';
        $iconMod = 'violet';
        $typeLabel = 'Mfumo';
    } elseif ($type === 'service_booked') {
        $icon = '🤝';
        $iconMod = 'sky';
        $typeLabel = 'Huduma';
    } elseif (str_contains($type, 'job') || str_contains($type, 'application') || str_contains($type, 'Worker')) {
        $icon = '💼';
        $iconMod = 'sky';
        $typeLabel = 'Kazi';
    } elseif (str_contains($type, 'money') || str_contains($type, 'payment') || str_contains($type, 'Payment') || isset($notification->data['earnings'])) {
        $icon = '💰';
        $iconMod = 'emerald';
        $typeLabel = 'Malipo';
    } elseif (str_contains($type, 'alert') || str_contains($type, 'cancel') || str_contains($type, 'Declined')) {
        $icon = '⚠️';
        $iconMod = 'rose';
        $typeLabel = 'Tahadhari';
    } elseif (str_contains($type, 'chat') || str_contains($type, 'message')) {
        $icon = '💬';
        $iconMod = 'amber';
        $typeLabel = 'Ujumbe';
    }

    $isUnread = ! $notification->read_at;
    $title = $notification->data['title'] ?? 'Taarifa';
    $message = $notification->data['message'] ?? 'Hakuna maelezo ya ziada.';
    $actionUrl = $notification->data['action_url'] ?? null;

    $typePillClass = match ($iconMod) {
        'violet' => 'bg-violet-100 text-violet-700',
        'sky' => 'bg-sky-100 text-sky-700',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'rose' => 'bg-rose-100 text-rose-700',
        'amber' => 'bg-amber-100 text-amber-800',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

@if(($theme ?? 'admin') === 'admin')
    <li>
        <article class="adm-notif-card {{ $isUnread ? 'adm-notif-card--unread' : '' }}">
            @if($isUnread)
                <span class="adm-notif-dot" title="Haijasomwa" aria-hidden="true"></span>
            @endif
            <div class="adm-notif-card-inner">
                <div class="adm-notif-icon adm-notif-icon--{{ $iconMod === 'slate' ? 'sky' : $iconMod }}" aria-hidden="true">{{ $icon }}</div>
                <div class="adm-notif-card-body">
                    <div class="adm-notif-card-head">
                        <div>
                            <span class="adm-notif-type-pill">{{ $typeLabel }}</span>
                            <h2 class="adm-notif-card-title">{{ $title }}</h2>
                        </div>
                        <time class="adm-notif-card-time" datetime="{{ $notification->created_at->toIso8601String() }}">
                            {{ $notification->created_at->diffForHumans() }}
                        </time>
                    </div>
                    <p class="adm-notif-card-msg">{{ $message }}</p>
                    <div class="adm-notif-card-actions">
                        @if($actionUrl)
                            <a href="{{ $actionUrl }}" class="adm-btn adm-btn--primary adm-btn--sm">Fungua →</a>
                        @endif
                        @if($isUnread)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="adm-inline-form">
                                @csrf
                                <button type="submit" class="adm-btn adm-btn--ghost adm-btn--sm">Weka imesomwa</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </article>
    </li>
@else
    <li>
        <article class="tp-notif-card {{ $isUnread ? 'tp-notif-card--unread' : '' }}">
            <span class="tp-notif-card-accent" aria-hidden="true"></span>
            <div class="tp-notif-card-inner">
                <div class="tp-notif-icon tp-notif-icon--{{ $iconMod }}">
                    {{ $icon }}
                    @if($isUnread)
                        <span class="tp-notif-pulse" title="Haijasomwa"></span>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="tp-notif-card-head">
                        <div class="min-w-0 pr-2">
                            <span class="tp-notif-type-pill {{ $typePillClass }}">{{ $typeLabel }}</span>
                            <h2 class="tp-notif-card-title">{{ $title }}</h2>
                        </div>
                        <time class="tp-notif-card-time" datetime="{{ $notification->created_at->toIso8601String() }}">
                            {{ $notification->created_at->diffForHumans() }}
                        </time>
                    </div>
                    <p class="tp-notif-card-msg">{{ $message }}</p>
                    <div class="tp-notif-card-actions">
                        @if($actionUrl)
                            <a href="{{ $actionUrl }}" class="tp-notif-btn-primary w-full sm:w-auto">
                                Fungua
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </a>
                        @endif
                        @if($isUnread)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="w-full sm:w-auto">
                                @csrf
                                <button type="submit" class="tp-notif-btn-ghost w-full sm:w-auto">
                                    Weka imesomwa
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-1 text-[11px] font-semibold text-slate-400">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                Imesomwa
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </article>
    </li>
@endif
