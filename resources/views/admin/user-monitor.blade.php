@extends('layouts.admin')
@section('title', 'Admin — Ufuatiliaji wa shughuli')

@section('content')
<div class="adm-subpage adm-stack">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">Shughuli za mtumiaji</h1>
            <p class="adm-page-head-sub">{{ $user->name }} · {{ $user->email }}</p>
        </div>
        <div class="adm-actions">
            <a href="{{ route('admin.user.details', $user) }}" class="adm-btn adm-btn--primary">Wasifu</a>
            <a href="{{ route('admin.users') }}" class="adm-btn adm-btn--muted">← Watumiaji</a>
        </div>
    </div>

    <div class="adm-card">
        <h2 class="adm-card-title">Mfululizo wa matukio</h2>
        @if($activities->isEmpty())
            <p style="color:var(--adm-muted);margin:0;">Hakuna shughuli za hivi karibuni.</p>
        @else
            @foreach($activities as $activity)
                @if($activity['type'] === 'job')
                    <div class="adm-tl-item adm-tl-item--job">
                        <div class="adm-tl-ico">📋</div>
                        <div style="min-width:0;flex:1;">
                            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:0.5rem;">
                                <span class="adm-v">{{ $activity['data']->title }}</span>
                                <span style="font-size:var(--adm-text-xs);color:var(--adm-muted);">{{ $activity['timestamp']->diffForHumans() }}</span>
                            </div>
                            <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);color:var(--adm-muted);">
                                Kazi {{ $activity['data']->user_id === $user->id ? 'aliyochapisha' : 'aliyopewa' }} · {{ $activity['data']->status }}
                            </p>
                            <a href="{{ route('admin.job.details', $activity['data']) }}" style="font-size:var(--adm-text-xs);color:var(--adm-primary);font-weight:600;margin-top:0.35rem;display:inline-block;">Angalia kazi →</a>
                        </div>
                    </div>
                @elseif($activity['type'] === 'message')
                    <div class="adm-tl-item adm-tl-item--msg">
                        <div class="adm-tl-ico">💬</div>
                        <div style="min-width:0;flex:1;">
                            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:0.5rem;">
                                <span class="adm-v">Ujumbe wa faragha</span>
                                <span style="font-size:var(--adm-text-xs);color:var(--adm-muted);">{{ $activity['timestamp']->diffForHumans() }}</span>
                            </div>
                            <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);color:var(--adm-muted);">
                                {{ $activity['data']->sender_id === $user->id ? 'Imetumwa kwa' : 'Imepokea kutoka' }}
                                <span class="adm-v" style="font-weight:600;">{{ $activity['data']->sender_id === $user->id ? $activity['data']->receiver->name : $activity['data']->sender->name }}</span>
                            </p>
                            <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);padding:0.5rem;border-radius:6px;background:rgba(0,0,0,.2);">{{ Str::limit($activity['data']->message, 120) }}</p>
                            @if($activity['data']->job)
                                <a href="{{ route('admin.chat.view', $activity['data']->job) }}" style="font-size:var(--adm-text-xs);color:var(--adm-primary);font-weight:600;margin-top:0.35rem;display:inline-block;">Mazungumzo kamili →</a>
                            @endif
                        </div>
                    </div>
                @elseif($activity['type'] === 'transaction')
                    <div class="adm-tl-item adm-tl-item--txn">
                        <div class="adm-tl-ico">💰</div>
                        <div style="min-width:0;flex:1;">
                            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:0.5rem;">
                                <span class="adm-v">{{ ucfirst($activity['data']->type) }}</span>
                                <span style="font-size:var(--adm-text-xs);color:var(--adm-muted);">{{ $activity['timestamp']->diffForHumans() }}</span>
                            </div>
                            @php
                                $tt = strtoupper((string) $activity['data']->type);
                                $isOut = str_contains($tt, 'WITHDRAW') || str_contains($tt, 'DEBIT') || str_contains($tt, 'FEE') || str_contains($tt, 'HOLD');
                            @endphp
                            <p style="margin:0.35rem 0 0;font-size:var(--adm-text-sm);">
                                Kiasi ({{ $activity['data']->type }}):
                                <span class="{{ $isOut ? 'adm-amount-debit' : 'adm-amount-credit' }}">
                                    {{ $isOut ? '-' : '+' }}TSh {{ number_format($activity['data']->amount) }}
                                </span>
                            </p>
                            @if($activity['data']->description)
                                <p style="margin:0.25rem 0 0;font-size:var(--adm-text-xs);color:var(--adm-muted);">{{ $activity['data']->description }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</div>
@endsection
