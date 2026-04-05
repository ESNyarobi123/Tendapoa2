@extends('layouts.app')
@section('title', 'Maombi ya wafanyakazi')

@section('content')
<div class="flex min-h-screen bg-slate-50">
  @include('components.user-sidebar')

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto max-w-6xl space-y-5">

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">Maombi ya wafanyakazi</h1>
          <p class="mt-0.5 text-[12px] text-slate-500">
            Maombi yote kwenye kazi zako. Unaweza <strong class="text-slate-700">kuchagua</strong> au <strong class="text-slate-700">kukataa</strong> moja kwa moja hapa; kwa counter offer na shortlist tumia kiungo cha kazi.
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('my.jobs') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Kazi zangu</a>
          <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-medium text-slate-700 hover:bg-slate-50">Dashibodi</a>
        </div>
      </div>

      @php
        $filter = $filter ?? null;
        $filterAll = route('my.applications');
        $filterHatua = route('my.applications', ['filter' => 'hatua']);
      @endphp
      <div class="flex flex-wrap gap-1.5 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm">
        <a href="{{ $filterAll }}" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold {{ !$filter ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Zote (zinazoendelea)</a>
        <a href="{{ $filterHatua }}" class="rounded-lg px-3 py-1.5 text-[11px] font-semibold {{ $filter === 'hatua' ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">Zinazohitaji hatua yako</a>
      </div>

      <div class="flex flex-wrap gap-3 text-[11px] text-slate-600">
        <span class="inline-flex items-center gap-1.5 rounded-md bg-white px-2 py-1 ring-1 ring-slate-200"><strong class="text-slate-900">{{ $applications->total() }}</strong> jumla</span>
        <span class="inline-flex items-center gap-1.5 rounded-md bg-white px-2 py-1 ring-1 ring-slate-200">Ukurasa <strong class="text-slate-900">{{ $applications->currentPage() }}</strong> / {{ $applications->lastPage() }}</span>
      </div>

      @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[12px] font-medium text-emerald-900">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[12px] font-medium text-red-900">{{ $errors->first() }}</div>
      @endif

      @if($applications->count() > 0)
        <div class="space-y-3">
          @foreach($applications as $app)
            @php
              $job = $app->job;
              $worker = $app->worker;
              $stStyle = match($app->status) {
                'applied' => 'bg-amber-50 text-amber-900 ring-amber-100',
                'shortlisted' => 'bg-sky-50 text-sky-900 ring-sky-100',
                'countered' => 'bg-violet-50 text-violet-900 ring-violet-100',
                'accepted_counter' => 'bg-emerald-50 text-emerald-900 ring-emerald-100',
                'selected' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
                default => 'bg-slate-50 text-slate-700 ring-slate-200',
              };
              $jobSt = $job?->status ?? '';
              $jobStStyle = match($jobSt) {
                'open', 'posted' => 'bg-blue-50 text-blue-800 ring-blue-100',
                'awaiting_payment', 'pending_payment' => 'bg-amber-50 text-amber-900 ring-amber-100',
                default => 'bg-slate-100 text-slate-600 ring-slate-200',
              };
            @endphp
            <article class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4">
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide ring-1 {{ $stStyle }}">{{ $app->getStatusLabel() }}</span>
                  @if($job)
                    <span class="rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide ring-1 {{ $jobStStyle }}">{{ str_replace('_', ' ', $jobSt) }}</span>
                  @endif
                </div>
                <p class="mt-2 truncate text-[14px] font-bold text-slate-900">{{ $job?->title ?? 'Kazi' }}</p>
                <p class="mt-0.5 text-[12px] text-slate-600">
                  <span class="font-semibold text-slate-800">{{ $worker?->name ?? 'Mfanyakazi' }}</span>
                  @if($job?->category)
                    · {{ $job->category->name }}
                  @endif
                </p>
                <p class="mt-1 text-[12px] font-semibold tabular-nums text-slate-800">
                  {{ number_format($app->proposed_amount) }} TZS
                  @if($app->counter_amount)
                    <span class="font-normal text-slate-500">· Counter: {{ number_format($app->counter_amount) }} TZS</span>
                  @endif
                </p>
                @if($app->message)
                  <p class="mt-1 line-clamp-2 text-[11px] text-slate-500">{{ $app->message }}</p>
                @endif
                @if($app->isCountered())
                  <p class="mt-2 rounded-lg border border-violet-200 bg-violet-50 px-2.5 py-2 text-[11px] leading-relaxed text-violet-900">
                    <span class="font-semibold">Counter TZS {{ number_format($app->counter_amount ?? 0) }}</span> — subiri mfanyakazi akubali, kisha utaweza kumchagua hapa.
                  </p>
                @endif
              </div>
              <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:items-end">
                @if($job)
                  @php
                    $jobOpen = $job->status === \App\Models\Job::S_OPEN;
                    $canSelectThis = $jobOpen && in_array($app->status, [
                      \App\Models\JobApplication::STATUS_APPLIED,
                      \App\Models\JobApplication::STATUS_SHORTLISTED,
                      \App\Models\JobApplication::STATUS_ACCEPTED_COUNTER,
                    ], true);
                    $canRejectThis = $app->isActive() && $app->status !== \App\Models\JobApplication::STATUS_SELECTED;
                  @endphp

                  <div class="flex flex-wrap gap-2 sm:justify-end">
                    @if($app->status === \App\Models\JobApplication::STATUS_SELECTED)
                      <a href="{{ route('jobs.show', $job) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-emerald-700">Malipo / kazi</a>
                    @elseif($canSelectThis)
                      <form method="POST" action="{{ route('applications.select', [$job, $app]) }}" class="inline" onsubmit="return confirm({{ json_encode('Chagua '.($worker?->name ?? 'mfanyakazi huyu').'? Utaendelea kulipa escrow kwa kazi hii.') }});">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-[12px] font-semibold text-white shadow-sm hover:bg-emerald-700">
                          Chagua mfanyakazi
                        </button>
                      </form>
                    @endif

                    @if($canRejectThis)
                      <form method="POST" action="{{ route('applications.reject', [$job, $app]) }}" class="inline" onsubmit="return confirm({{ json_encode('Kataa ombi la '.($worker?->name ?? 'mfanyakazi huyu').'?') }});">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-2 text-[12px] font-semibold text-red-700 shadow-sm hover:bg-red-50">
                          Kataa ombi
                        </button>
                      </form>
                    @endif

                    <a href="{{ route('jobs.show', $job) }}#maombi-panel" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-[12px] font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                      Chaguo zaidi
                    </a>
                  </div>
                @endif
                <span class="text-[10px] text-slate-400 sm:text-right">{{ $app->updated_at->diffForHumans() }}</span>
              </div>
            </article>
          @endforeach
        </div>

        <div class="pt-2">
          {{ $applications->links() }}
        </div>
      @else
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-14 text-center">
          <p class="text-[14px] font-medium text-slate-700">Hakuna maombi katika kichujio hiki.</p>
          <p class="mt-1 text-[12px] text-slate-500">Wakati mfanyakazi atakapo omba kazi yako, itaonekana hapa.</p>
          <a href="{{ route('my.jobs') }}" class="mt-5 inline-flex rounded-xl bg-brand-600 px-4 py-2.5 text-[12px] font-bold text-white shadow-sm hover:bg-brand-700">Rudi kwenye kazi zangu</a>
        </div>
      @endif

    </div>
  </main>
</div>
@endsection
