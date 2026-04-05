@extends('layouts.app')
@section('title', 'Kazi Zangu')

@section('content')
<div class="flex min-h-screen bg-slate-50">
  @include('components.user-sidebar')

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto max-w-6xl space-y-5">

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">Kazi zangu</h1>
          <p class="mt-0.5 text-[12px] text-slate-500">Orodha yako — picha, bei, na hatua kwa kila kazi.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('jobs.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-brand-700">+ Chapisha kazi</a>
          <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Dashibodi</a>
        </div>
      </div>

      @php
        $statusFilter = request('status');
        $filterLink = function (?string $st) {
          return $st === null ? route('my.jobs') : route('my.jobs', ['status' => $st]);
        };
      @endphp
      <div class="flex flex-wrap gap-1.5 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm">
        <a href="{{ $filterLink(null) }}" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold {{ !$statusFilter ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Zote</a>
        <a href="{{ $filterLink('open') }}" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold {{ $statusFilter === 'open' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Wazi</a>
        <a href="{{ $filterLink('posted') }}" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold {{ $statusFilter === 'posted' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Imetangazwa</a>
        <a href="{{ $filterLink('awaiting_payment') }}" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold {{ $statusFilter === 'awaiting_payment' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Lipia escrow</a>
        <a href="{{ $filterLink('in_progress') }}" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold {{ $statusFilter === 'in_progress' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Inaendelea</a>
        <a href="{{ $filterLink('completed') }}" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold {{ $statusFilter === 'completed' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Imekamilika</a>
      </div>

      <div class="flex flex-wrap gap-3 text-[11px] text-slate-600">
        <span class="inline-flex items-center gap-1.5 rounded-md bg-white px-2 py-1 ring-1 ring-slate-200"><strong class="text-slate-900">{{ $jobs->total() }}</strong> jumla</span>
        <span class="inline-flex items-center gap-1.5 rounded-md bg-white px-2 py-1 ring-1 ring-slate-200">Ukurasa <strong class="text-slate-900">{{ $jobs->currentPage() }}</strong> / {{ $jobs->lastPage() }}</span>
      </div>

      @if(session('success'))
        <div class="rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-[12px] font-medium text-brand-900">{{ session('success') }}</div>
      @endif

      @if($jobs->count() > 0)
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
          @foreach($jobs as $job)
            @php
              $stSlug = str_replace('_', '-', $job->status);
              $statusStyle = match($job->status) {
                'open', 'posted' => 'bg-blue-50 text-blue-800 ring-blue-100',
                'awaiting_payment', 'pending_payment' => 'bg-amber-50 text-amber-900 ring-amber-100',
                'funded', 'assigned' => 'bg-emerald-50 text-emerald-900 ring-emerald-100',
                'in_progress' => 'bg-fuchsia-50 text-fuchsia-900 ring-fuchsia-100',
                'submitted' => 'bg-violet-50 text-violet-900 ring-violet-100',
                'completed' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
                'disputed' => 'bg-red-50 text-red-800 ring-red-100',
                'cancelled', 'expired', 'refunded' => 'bg-slate-100 text-slate-600 ring-slate-200',
                default => 'bg-slate-50 text-slate-700 ring-slate-200',
              };
              $img = $job->image_url;
            @endphp
            <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-200 hover:shadow-md">
              <div class="relative h-36 shrink-0 bg-slate-100 sm:h-40">
                @if($img)
                  <img src="{{ $img }}" alt="" class="h-full w-full object-cover"
                    onerror="this.style.display='none';this.nextElementSibling?.classList.remove('hidden');">
                  <div class="hidden h-full w-full flex-col items-center justify-center bg-slate-100 text-slate-400">
                    <span class="text-2xl opacity-50">📷</span>
                    <span class="mt-1 text-[10px] font-medium">Hakuna picha</span>
                  </div>
                @else
                  <div class="flex h-full w-full flex-col items-center justify-center text-slate-400">
                    <span class="text-2xl opacity-50">📷</span>
                    <span class="mt-1 text-[10px] font-medium">Hakuna picha</span>
                  </div>
                @endif
                <span class="absolute right-2 top-2 max-w-[85%] truncate rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide ring-1 {{ $statusStyle }}">
                  @switch($job->status)
                    @case('open') Wazi @break
                    @case('awaiting_payment') Inasubiri malipo @break
                    @case('funded') Imefadhiliwa @break
                    @case('in_progress') Inaendelea @break
                    @case('submitted') Imewasilishwa @break
                    @case('completed') Imekamilika @break
                    @case('disputed') Mgogoro @break
                    @case('cancelled') Imefutwa @break
                    @case('refunded') Imerudishwa @break
                    @case('expired') Imepitwa @break
                    @case('posted') Imetangazwa @break
                    @case('pending_payment') Inasubiri malipo @break
                    @case('assigned') Imepewa @break
                    @default {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                  @endswitch
                </span>
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/65 to-transparent px-2 pb-2 pt-8">
                  <span class="text-[13px] font-bold text-white drop-shadow">{{ number_format($job->price) }} <span class="text-[10px] font-semibold opacity-90">TZS</span></span>
                </div>
              </div>

              @if(in_array($job->status, ['funded', 'in_progress', 'submitted', 'assigned']) && $job->completion_code)
                <div class="border-b border-slate-100 bg-brand-50/50 px-3 py-2">
                  <p class="text-[10px] font-semibold uppercase tracking-wide text-brand-800">Nambari ya uthibitisho</p>
                  <button type="button" onclick="copyToClipboard('{{ $job->completion_code }}')" class="mt-1 w-full rounded-lg bg-white py-1.5 text-center font-mono text-[14px] font-bold tracking-[0.2em] text-brand-700 ring-1 ring-brand-200">{{ $job->completion_code }}</button>
                  <p class="mt-1 text-[9px] text-slate-500">Bofya kunakili. Mpe mfanyakazi baada ya kazi kukamilika.</p>
                </div>
              @endif

              @if(in_array($job->status, ['funded', 'in_progress', 'submitted', 'awaiting_payment', 'assigned']) && ($job->acceptedWorker || $job->selectedWorker))
                <div class="flex items-center gap-2 border-b border-slate-100 px-3 py-2">
                  @php $w = $job->acceptedWorker ?? $job->selectedWorker; @endphp
                  <img src="{{ $w->profile_photo_url ?: 'https://ui-avatars.com/api/?name='.urlencode($w->name).'&background=e2e8f0&color=475569&size=64' }}" alt="" class="h-8 w-8 shrink-0 rounded-lg object-cover ring-1 ring-slate-200">
                  <div class="min-w-0">
                    <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">Mfanyakazi</p>
                    <p class="truncate text-[12px] font-semibold text-slate-800">{{ $w->name }}</p>
                  </div>
                </div>
              @endif

              @if(in_array($job->status, ['open', 'posted']) && ($job->applications_count ?? 0) > 0)
                <div class="border-b border-slate-100 bg-slate-50 px-3 py-1.5 text-[11px] font-medium text-slate-700">✋ {{ $job->applications_count }} maombi</div>
              @endif

              <div class="flex flex-1 flex-col p-3">
                <h2 class="line-clamp-2 text-[14px] font-bold leading-snug text-slate-900">{{ $job->title }}</h2>
                <div class="mt-1.5 flex flex-wrap gap-x-2 gap-y-0.5 text-[10px] text-slate-500">
                  @if($job->category)<span>{{ $job->category->name }}</span>@endif
                  <span>· {{ $job->created_at->format('d M Y') }}</span>
                  <span>· {{ $job->comments_count ?? 0 }} maoni</span>
                </div>
                @if($job->description)
                  <p class="mt-2 line-clamp-2 flex-1 text-[11px] leading-relaxed text-slate-500">{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 140) }}</p>
                @endif

                <div class="mt-3 flex flex-wrap gap-1.5 border-t border-slate-100 pt-2.5">
                  @if($job->status === 'awaiting_payment')
                    <a href="{{ route('jobs.fund', $job) }}" class="inline-flex flex-1 min-w-[6rem] items-center justify-center rounded-lg bg-brand-600 px-2 py-1.5 text-[11px] font-semibold text-white hover:bg-brand-700">Lipia escrow</a>
                  @endif
                  @if($job->status === 'submitted')
                    <a href="{{ route('jobs.show', $job) }}" class="inline-flex flex-1 min-w-[6rem] items-center justify-center rounded-lg bg-brand-600 px-2 py-1.5 text-[11px] font-semibold text-white hover:bg-brand-700">Thibitisha</a>
                  @endif
                  @if($job->status === 'pending_payment')
                    <a href="{{ route('jobs.pay.wait', $job) }}" class="inline-flex flex-1 min-w-[6rem] items-center justify-center rounded-lg bg-brand-600 px-2 py-1.5 text-[11px] font-semibold text-white hover:bg-brand-700">Lipa</a>
                  @endif
                  @if(in_array($job->status, ['open', 'posted', 'pending_payment', 'awaiting_payment', 'funded']) && !in_array($job->status, ['in_progress', 'submitted', 'completed']))
                    <form action="{{ route('jobs.cancel', $job) }}" method="POST" class="inline-flex flex-1" onsubmit="return confirm('Futa kazi hii?');">
                      @csrf
                      <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-2 py-1.5 text-[11px] font-semibold text-red-700 hover:bg-red-100">Futa</button>
                    </form>
                  @endif
                  @if(in_array($job->status, ['open', 'posted']))
                    <a href="{{ route('jobs.edit', $job) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-2 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">Hariri</a>
                  @endif
                  <a href="{{ route('jobs.show', $job) }}" class="inline-flex flex-1 min-w-[5rem] items-center justify-center rounded-lg bg-slate-900 px-2 py-1.5 text-[11px] font-semibold text-white hover:bg-slate-800">Angalia</a>
                </div>
              </div>
            </article>
          @endforeach
        </div>

        <div class="flex justify-center pt-2 text-[12px] text-slate-600 [&_.pagination]:flex [&_.pagination]:flex-wrap [&_.pagination]:justify-center [&_.pagination]:gap-1 [&_a]:rounded-lg [&_a]:border [&_a]:border-slate-200 [&_a]:px-2.5 [&_a]:py-1 [&_a]:font-medium [&_a]:text-slate-700 [&_a:hover]:bg-slate-50 [&_span]:rounded-lg [&_span]:bg-brand-50 [&_span]:px-2.5 [&_span]:py-1 [&_span]:font-semibold [&_span]:text-brand-800">
          {{ $jobs->appends(request()->query())->links() }}
        </div>
      @else
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-14 text-center">
          <p class="text-3xl opacity-40">📋</p>
          <p class="mt-2 text-[15px] font-semibold text-slate-800">Hakuna kazi</p>
          <p class="mt-1 text-[12px] text-slate-500">
            @if(request('status'))
              Hakuna kazi za hali hii. <a href="{{ route('my.jobs') }}" class="font-semibold text-brand-700 hover:underline">Ona zote</a>
            @else
              Chapisha kazi yako ya kwanza.
            @endif
          </p>
          <a href="{{ route('jobs.create') }}" class="mt-4 inline-flex rounded-lg bg-brand-600 px-4 py-2 text-[12px] font-semibold text-white hover:bg-brand-700">Chapisha kazi</a>
        </div>
      @endif

    </div>
  </main>
</div>

<script>
function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(function() {
    const n = document.createElement('div');
    n.className = 'fixed top-4 right-4 z-[2000] rounded-lg bg-brand-600 px-4 py-2 text-[12px] font-semibold text-white shadow-lg';
    n.textContent = 'Imenakiliwa';
    document.body.appendChild(n);
    setTimeout(function() { n.remove(); }, 2000);
  });
}
</script>
@endsection
