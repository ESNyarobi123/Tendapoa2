@extends('layouts.admin')
@section('title', 'Admin — Hariri Taarifa')

@section('content')
<div class="adm-subpage adm-stack" style="max-width:48rem;">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">✏️ Hariri Taarifa</h1>
            <p class="adm-page-head-sub">Badilisha kichwa, ujumbe, au action URL ya taarifa iliyotumwa</p>
        </div>
        <a href="{{ route('admin.broadcast') }}" class="adm-btn adm-btn--muted">← Rudi Tangazo</a>
    </div>

    {{-- Delivery info (read-only) --}}
    <div class="adm-card" style="background:#f8fafc;border:1px solid #e2e8f0;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <p style="font-size:0.72rem;color:#64748b;font-weight:600;margin:0 0 2px;">TAARIFA ZA UTOAJI</p>
                <p style="font-size:0.82rem;color:#0f172a;margin:0;">
                    Imetumwa {{ $notification->created_at->diffForHumans() }} · Lengo: <strong>{{ $notification->target }}</strong> · Na: {{ $notification->sender->name ?? 'Admin' }}
                </p>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                @if($notification->total_count > 0)
                    <span style="font-size:0.72rem;padding:3px 10px;background:#eff6ff;color:#1d4ed8;border-radius:9999px;font-weight:600;">
                        👥 {{ $notification->total_count }} jumla
                    </span>
                    <span style="font-size:0.72rem;padding:3px 10px;background:#ecfdf5;color:#047857;border-radius:9999px;font-weight:600;">
                        ✅ {{ $notification->sent_count }} imefika
                    </span>
                    @if($notification->failed_count > 0)
                        <span style="font-size:0.72rem;padding:3px 10px;background:#fef2f2;color:#b91c1c;border-radius:9999px;font-weight:600;">
                            ❌ {{ $notification->failed_count }} imefeli
                        </span>
                    @endif
                    @if($notification->fcm_sent_count > 0)
                        <span style="font-size:0.72rem;padding:3px 10px;background:#f0fdf4;color:#15803d;border-radius:9999px;font-weight:600;">
                            📱 {{ $notification->fcm_sent_count }} push
                        </span>
                    @endif
                @else
                    <span style="font-size:0.72rem;padding:3px 10px;background:#f1f5f9;color:#64748b;border-radius:9999px;">
                        Hakuna takwimu
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Edit Form --}}
    <div class="adm-card">
        <form method="POST" action="{{ route('admin.broadcast.update', $notification->id) }}">
            @csrf @method('PUT')

            <div class="adm-form-group">
                <label class="adm-label" for="title">Kichwa</label>
                <input id="title" class="adm-input" type="text" name="title" value="{{ old('title', $notification->title) }}" required maxlength="255">
                @error('title')<p class="adm-field-error">{{ $message }}</p>@enderror
            </div>

            <div class="adm-form-group">
                <label class="adm-label" for="message">Ujumbe</label>
                <textarea id="message" name="message" class="adm-textarea" rows="6" required maxlength="2000">{{ old('message', $notification->message) }}</textarea>
                <p style="font-size:0.7rem;color:#64748b;margin-top:4px;"><span id="msg-counter">{{ Str::length($notification->message) }}</span>/2000</p>
                @error('message')<p class="adm-field-error">{{ $message }}</p>@enderror
            </div>

            <div class="adm-form-group">
                <label class="adm-label" for="action_url">Action URL (hiari)</label>
                <input id="action_url" class="adm-input" type="text" name="action_url" value="{{ old('action_url', $notification->action_url) }}" placeholder="/jobs/123 au https://...">
                <p style="font-size:0.7rem;color:#64748b;margin-top:4px;">Mtumiaji akibofya notification, atapelekwa kwenye link hii.</p>
            </div>

            <div class="adm-actions" style="justify-content:space-between;margin-top:16px;">
                <a href="{{ route('admin.broadcast') }}" class="adm-btn adm-btn--muted">Ghairi</a>
                <button type="submit" class="adm-btn adm-btn--primary" style="font-size:0.9rem;padding:10px 24px;">💾 Hifadhi Mabadiliko</button>
            </div>
        </form>
    </div>

    {{-- Danger zone --}}
    <div class="adm-card" style="border:1px solid #fecaca;background:#fff5f5;">
        <h3 style="margin:0 0 8px;font-size:0.88rem;color:#991b1b;">⚠️ Eneo la Hatari</h3>
        <p style="font-size:0.78rem;color:#7f1d1d;margin:0 0 12px;">Ukifuta taarifa hii, itaondolewa kwenye historia kabisa. Huwezi kurudisha.</p>
        <form method="POST" action="{{ route('admin.broadcast.delete', $notification->id) }}" onsubmit="return confirm('Una uhakika unataka kufuta taarifa hii? Huwezi kurudisha!');">
            @csrf @method('DELETE')
            <button type="submit" class="adm-btn" style="background:#dc2626;color:#fff;border:none;font-size:0.82rem;padding:8px 18px;">🗑️ Futa Taarifa</button>
        </form>
    </div>
</div>

<script>
document.getElementById('message').addEventListener('input', function () {
    document.getElementById('msg-counter').textContent = this.value.length;
});
</script>
@endsection
