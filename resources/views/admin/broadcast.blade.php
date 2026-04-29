@extends('layouts.admin')
@section('title', 'Admin — Tuma Push Notification')

@section('content')
<div class="adm-subpage adm-stack" style="max-width:72rem;">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">📣 Tuma Push Notification</h1>
            <p class="adm-page-head-sub">Tuma taarifa kwa watumiaji — itaonekana kwenye system + push popup kupitia Firebase</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="adm-btn adm-btn--muted">← Dashibodi</a>
    </div>

    @if(session('success'))
        <div class="adm-card" style="border-left:4px solid #10b981;background:#ecfdf5;">
            <p style="color:#047857;font-weight:700;">✅ {{ session('success') }}</p>
        </div>
    @endif

    {{-- Two-column layout --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">

        {{-- LEFT: Form --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Audience Stats --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div class="adm-card" style="text-align:center;padding:14px 10px;">
                    <div style="font-size:1.5rem;font-weight:800;color:#0f172a;">{{ number_format($stats['all']) }}</div>
                    <div style="font-size:0.72rem;color:#64748b;">Watumiaji wote</div>
                </div>
                <div class="adm-card" style="text-align:center;padding:14px 10px;">
                    <div style="font-size:1.5rem;font-weight:800;color:#10b981;">{{ number_format($stats['with_fcm']) }}</div>
                    <div style="font-size:0.72rem;color:#64748b;">📱 Wenye FCM token</div>
                </div>
                <div class="adm-card" style="text-align:center;padding:14px 10px;">
                    <div style="font-size:1.5rem;font-weight:800;color:#7c3aed;">{{ number_format($stats['muhitaji']) }}</div>
                    <div style="font-size:0.72rem;color:#64748b;">Wahitaji</div>
                </div>
                <div class="adm-card" style="text-align:center;padding:14px 10px;">
                    <div style="font-size:1.5rem;font-weight:800;color:#0891b2;">{{ number_format($stats['mfanyakazi']) }}</div>
                    <div style="font-size:0.72rem;color:#64748b;">Wafanyakazi</div>
                </div>
            </div>

            {{-- Form --}}
            <div class="adm-card">
                <form method="POST" action="{{ route('admin.broadcast.send') }}" id="broadcast-form">
                    @csrf

                    <div class="adm-form-group">
                        <label class="adm-label" for="title">Kichwa</label>
                        <input id="title" class="adm-input" type="text" name="title" value="{{ old('title') }}" placeholder="mfano: Mafundi wapya wamejisajili" required maxlength="255">
                        @error('title')<p class="adm-field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-label" for="message">Ujumbe</label>
                        <textarea id="message" name="message" class="adm-textarea" rows="4" placeholder="Andika ujumbe wa push notification..." required maxlength="2000">{{ old('message') }}</textarea>
                        <p style="font-size:0.7rem;color:#64748b;margin-top:4px;"><span id="msg-counter">0</span>/2000</p>
                        @error('message')<p class="adm-field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-label" for="action_url">Action URL (hiari)</label>
                        <input id="action_url" class="adm-input" type="text" name="action_url" value="{{ old('action_url') }}" placeholder="/jobs/123 au https://...">
                        <p style="font-size:0.7rem;color:#64748b;margin-top:4px;">Mtumiaji akibofya notification, atapelekwa kwenye link hii.</p>
                    </div>

                    <div class="adm-form-group">
                        <label class="adm-label" for="target">Lengo</label>
                        <select id="target" name="target" class="adm-select" onchange="onTargetChange()">
                            <option value="all" @selected(old('target', 'all') === 'all')>Watumiaji wote</option>
                            <option value="muhitaji" @selected(old('target') === 'muhitaji')>Wahitaji tu</option>
                            <option value="mfanyakazi" @selected(old('target') === 'mfanyakazi')>Wafanyakazi tu</option>
                            <option value="specific" @selected(old('target') === 'specific')>Watumiaji maalum (chagua)</option>
                        </select>
                        @error('target')<p class="adm-field-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Specific user picker --}}
                    <div class="adm-form-group" id="user-picker" style="display:none;">
                        <label class="adm-label">Chagua watumiaji</label>
                        <input type="text" id="user-search" class="adm-input" placeholder="Tafuta kwa jina, email, au simu...">
                        <div id="user-search-results" style="border:1px solid #e2e8f0;border-radius:8px;max-height:240px;overflow-y:auto;margin-top:6px;display:none;background:#fff;"></div>
                        <div id="selected-users" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;"></div>
                        <p style="font-size:0.7rem;color:#64748b;margin-top:6px;">Bofya mtumiaji kumchagua. Bofya tena kuondoa.</p>
                        @error('user_ids')<p class="adm-field-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Live audience preview --}}
                    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:14px 16px;margin-top:8px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                            <div>
                                <p style="font-size:0.72rem;color:#0369a1;font-weight:600;margin:0 0 2px;">WATUMIAJI WATAKAOFIKIWA</p>
                                <p style="font-size:1.6rem;font-weight:800;color:#0c4a6e;margin:0;"><span id="live-total">{{ $stats['all'] }}</span></p>
                            </div>
                            <div style="text-align:right;">
                                <p style="font-size:0.78rem;color:#10b981;font-weight:700;margin:0;">📱 <span id="live-fcm">{{ $stats['with_fcm'] }}</span> watapata push</p>
                                <p style="font-size:0.78rem;color:#f59e0b;font-weight:600;margin:2px 0 0;">📋 <span id="live-no-fcm">0</span> kwenye system tu</p>
                            </div>
                        </div>
                    </div>

                    <div class="adm-actions" style="justify-content:flex-end;margin-top:12px;">
                        <button type="submit" class="adm-btn adm-btn--primary" id="submit-btn" style="font-size:0.9rem;padding:10px 24px;">📤 Tuma Notification</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RIGHT: History --}}
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="adm-card" style="padding:0;">
                <div style="padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:1rem;font-weight:700;color:#0f172a;">📜 Historia ya Taarifa</h3>
                    <span style="font-size:0.72rem;color:#94a3b8;">{{ $recent->count() }} za hivi karibuni</span>
                </div>

                @if($recent->count() > 0)
                    <div style="display:flex;flex-direction:column;">
                        @foreach($recent as $sn)
                            <div style="padding:14px 18px;border-bottom:1px solid #f1f5f9;{{ $loop->last ? 'border-bottom:none;' : '' }}">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:6px;">
                                    <div style="flex:1;min-width:0;">
                                        <strong style="color:#0f172a;font-size:0.88rem;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $sn->title }}</strong>
                                        <span style="font-size:0.68rem;color:#94a3b8;">{{ $sn->created_at->diffForHumans() }} · {{ $sn->target }}</span>
                                    </div>
                                    <div style="display:flex;gap:4px;flex-shrink:0;">
                                        <a href="{{ route('admin.broadcast.edit', $sn->id) }}" title="Hariri" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;background:#f1f5f9;border-radius:6px;color:#475569;font-size:0.8rem;text-decoration:none;">✏️</a>
                                        <form method="POST" action="{{ route('admin.broadcast.delete', $sn->id) }}" style="display:inline;" onsubmit="return confirm('Una uhakika unataka kufuta taarifa hii?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Futa" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;background:#fef2f2;border-radius:6px;color:#dc2626;font-size:0.8rem;border:none;cursor:pointer;">🗑️</button>
                                        </form>
                                    </div>
                                </div>
                                <p style="font-size:0.78rem;color:#475569;margin:0 0 8px;line-height:1.4;">{{ \Illuminate\Support\Str::limit($sn->message, 150) }}</p>

                                {{-- Delivery stats --}}
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    @if($sn->total_count > 0)
                                        <span style="font-size:0.68rem;padding:2px 8px;background:#eff6ff;color:#1d4ed8;border-radius:9999px;font-weight:600;">
                                            👥 {{ $sn->total_count }} jumla
                                        </span>
                                        <span style="font-size:0.68rem;padding:2px 8px;background:#ecfdf5;color:#047857;border-radius:9999px;font-weight:600;">
                                            ✅ {{ $sn->sent_count }} imefika
                                        </span>
                                        @if($sn->failed_count > 0)
                                            <span style="font-size:0.68rem;padding:2px 8px;background:#fef2f2;color:#b91c1c;border-radius:9999px;font-weight:600;">
                                                ❌ {{ $sn->failed_count }} imefeli
                                            </span>
                                        @endif
                                        @if($sn->fcm_sent_count > 0)
                                            <span style="font-size:0.68rem;padding:2px 8px;background:#f0fdf4;color:#15803d;border-radius:9999px;font-weight:600;">
                                                📱 {{ $sn->fcm_sent_count }} push
                                            </span>
                                        @endif
                                        <span style="font-size:0.68rem;padding:2px 8px;background:#f8fafc;color:#64748b;border-radius:9999px;">
                                            📋 {{ $sn->total_count - $sn->fcm_sent_count }} system tu
                                        </span>
                                    @else
                                        <span style="font-size:0.68rem;padding:2px 8px;background:#f8fafc;color:#94a3b8;border-radius:9999px;">
                                            Hakuna takwimu
                                        </span>
                                    @endif
                                </div>

                                <p style="font-size:0.68rem;color:#cbd5e1;margin:6px 0 0;">Imetumwa na {{ $sn->sender->name ?? 'Admin' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding:32px 18px;text-align:center;">
                        <p style="font-size:2rem;margin:0 0 4px;">📭</p>
                        <p style="font-size:0.82rem;color:#94a3b8;margin:0;">Hakuna taarifa iliyotumwa bado.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
const csrf = '{{ csrf_token() }}';
const audienceUrl = '{{ route('admin.broadcast.audience') }}';
const searchUrl = '{{ route('admin.broadcast.users.search') }}';
const selectedUsers = new Map();

document.getElementById('message').addEventListener('input', function () {
    document.getElementById('msg-counter').textContent = this.value.length;
});

function onTargetChange() {
    const target = document.getElementById('target').value;
    document.getElementById('user-picker').style.display = target === 'specific' ? 'block' : 'none';
    refreshAudience();
}

async function refreshAudience() {
    const target = document.getElementById('target').value;
    const params = new URLSearchParams();
    params.set('target', target);
    if (target === 'specific') {
        for (const id of selectedUsers.keys()) {
            params.append('user_ids[]', id);
        }
    }
    try {
        const r = await fetch(`${audienceUrl}?${params.toString()}`, { headers: { 'Accept': 'application/json' }});
        const d = await r.json();
        document.getElementById('live-total').textContent = d.total;
        document.getElementById('live-fcm').textContent = d.with_fcm;
        document.getElementById('live-no-fcm').textContent = d.without_fcm;
    } catch (e) { console.error(e); }
}

let searchTimer = null;
document.getElementById('user-search').addEventListener('input', function () {
    clearTimeout(searchTimer);
    const q = this.value;
    searchTimer = setTimeout(async () => {
        if (q.trim() === '') {
            document.getElementById('user-search-results').style.display = 'none';
            return;
        }
        const r = await fetch(`${searchUrl}?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' }});
        const d = await r.json();
        const box = document.getElementById('user-search-results');
        if (d.users.length === 0) {
            box.innerHTML = '<div style="padding:10px;color:#94a3b8;font-size:0.85rem;">Hakuna watumiaji.</div>';
        } else {
            box.innerHTML = d.users.map(u => `
                <div onclick="toggleUser(${u.id}, ${JSON.stringify(u.name).replace(/"/g, '&quot;')}, ${JSON.stringify(u.role).replace(/"/g, '&quot;')})"
                    style="padding:8px 12px;cursor:pointer;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;${selectedUsers.has(u.id) ? 'background:#eff6ff;' : ''}">
                    <div>
                        <strong style="font-size:0.85rem;">${u.name}</strong>
                        <div style="font-size:0.72rem;color:#64748b;">${u.email || ''} ${u.phone ? '· ' + u.phone : ''}</div>
                    </div>
                    <span style="font-size:0.7rem;padding:2px 8px;background:${u.role === 'muhitaji' ? '#ddd6fe' : u.role === 'mfanyakazi' ? '#cffafe' : '#fef3c7'};border-radius:9999px;">${u.role}</span>
                </div>`).join('');
        }
        box.style.display = 'block';
    }, 200);
});

function toggleUser(id, name, role) {
    if (selectedUsers.has(id)) {
        selectedUsers.delete(id);
    } else {
        selectedUsers.set(id, { name, role });
    }
    renderSelectedUsers();
    refreshAudience();
}

function renderSelectedUsers() {
    const box = document.getElementById('selected-users');
    box.innerHTML = '';
    for (const [id, info] of selectedUsers.entries()) {
        const chip = document.createElement('span');
        chip.style.cssText = 'background:#3b82f6;color:#fff;padding:4px 10px;border-radius:9999px;font-size:0.78rem;display:inline-flex;align-items:center;gap:6px;';
        chip.innerHTML = `${info.name} <span style="opacity:.7;">×</span>`;
        chip.style.cursor = 'pointer';
        chip.onclick = () => { selectedUsers.delete(id); renderSelectedUsers(); refreshAudience(); };
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'user_ids[]';
        hidden.value = id;
        chip.appendChild(hidden);
        box.appendChild(chip);
    }
}

document.getElementById('broadcast-form').addEventListener('submit', function (e) {
    const target = document.getElementById('target').value;
    if (target === 'specific' && selectedUsers.size === 0) {
        e.preventDefault();
        alert('Tafadhali chagua angalau mtumiaji mmoja.');
        return;
    }
    document.getElementById('submit-btn').disabled = true;
    document.getElementById('submit-btn').textContent = '⏳ Inatuma...';
});

// init
document.getElementById('msg-counter').textContent = document.getElementById('message').value.length;
onTargetChange();
</script>
@endsection
