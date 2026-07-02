@props([
    'size' => 'md',
    'showText' => true,
    'href' => null,
    'class' => '',
    'textClass' => '',
    'imgClass' => '',
])

@php
    $logoUrl = asset('images/brand/tendapoa-logo.jpg');

    $imgSizes = [
        'xs' => 'h-7 w-7',
        'sm' => 'h-8 w-8',
        'md' => 'h-9 w-9',
        'lg' => 'h-11 w-11',
        'xl' => 'h-14 w-14',
        'hero' => 'h-16 w-16 sm:h-20 sm:w-20',
    ];
    $imgSize = $imgSizes[$size] ?? $imgSizes['md'];
    $link = $href ?? route('home');
@endphp

<a href="{{ $link }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 no-underline '.$class]) }}>
    <img
        src="{{ $logoUrl }}"
        alt="TendaPoa"
        class="{{ $imgSize }} shrink-0 rounded-xl object-cover shadow-sm ring-1 ring-black/10 {{ $imgClass }}"
        width="64"
        height="64"
        loading="eager"
    >
    @if($showText)
        <span class="font-extrabold tracking-tight {{ $textClass }}">TendaPoa</span>
    @endif
</a>
