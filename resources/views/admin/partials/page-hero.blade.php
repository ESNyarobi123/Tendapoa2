{{-- Reusable admin page header — @include('admin.partials.page-hero', ['title' => '...', 'subtitle' => '...']) --}}
@php
    $heroTitle = $title ?? 'Admin';
    $heroSubtitle = $subtitle ?? null;
    $heroIcon = $icon ?? null;
@endphp
<header class="adm-hero">
    <div class="adm-hero-text">
        @if($heroIcon)
            <span class="adm-hero-icon" aria-hidden="true">{{ $heroIcon }}</span>
        @endif
        <div>
            <h1 class="adm-hero-title">{{ $heroTitle }}</h1>
            @if($heroSubtitle)
                <p class="adm-hero-sub">{{ $heroSubtitle }}</p>
            @endif
        </div>
    </div>
    @if(!empty($actions))
        <div class="adm-hero-actions">
            {!! $actions !!}
        </div>
    @endif
</header>
