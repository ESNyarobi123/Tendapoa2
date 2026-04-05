@props(['jobs', 'role' => 'muhitaji'])

@if(isset($jobs) && $jobs->isNotEmpty())
  <div class="rounded-2xl border border-rose-200/80 bg-gradient-to-r from-rose-50/90 to-white px-5 py-4 shadow-sm">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
      <div>
        <p class="text-[13px] font-bold text-rose-950">Hatua muhimu kwenye kazi</p>
        <p class="mt-0.5 text-[12px] text-rose-900/80">
          @if($role === 'mfanyakazi')
            Kuna kazi zinazohitaji kukubali, kuendelea, au kuwasilisha.
          @else
            Lipa escrow au thibitisha kazi zilizowasilishwa.
          @endif
        </p>
      </div>
    </div>
    <ul class="mt-4 space-y-2">
      @foreach($jobs as $j)
        <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-rose-100/80 bg-white/90 px-3 py-2.5">
          <div class="min-w-0 flex-1">
            <a href="{{ route('jobs.show', $j) }}" class="truncate text-[13px] font-semibold text-slate-900 hover:text-brand-700 hover:underline">{{ $j->title }}</a>
            <p class="text-[11px] text-slate-500">
              @if($role === 'mfanyakazi')
                @if($j->status === \App\Models\Job::S_FUNDED)
                  Escrow imewekwa — kubali au kataa
                @elseif($j->status === \App\Models\Job::S_IN_PROGRESS)
                  Kazi inaendelea — wasilisha ukimaliza
                @else
                  {{ $j->status }}
                @endif
              @else
                @if($j->status === \App\Models\Job::S_AWAITING_PAYMENT)
                  Subiri malipo ya escrow
                @elseif($j->status === \App\Models\Job::S_SUBMITTED)
                  Mfanyakazi amewasilisha — thibitisha au omba marekebisho
                @else
                  {{ $j->status }}
                @endif
              @endif
            </p>
          </div>
          <a href="{{ route('jobs.show', $j) }}" class="shrink-0 rounded-lg bg-rose-600 px-3 py-1.5 text-[11px] font-bold text-white hover:bg-rose-700">Fungua</a>
        </li>
      @endforeach
    </ul>
  </div>
@endif
