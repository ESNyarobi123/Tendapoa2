<?php $__env->startSection('title', 'Admin — Tuma Push Notification'); ?>

<?php $__env->startSection('content'); ?>
<div class="adm-subpage adm-stack" style="max-width:64rem;">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">📣 Tuma Push Notification</h1>
            <p class="adm-page-head-sub">Tuma taarifa kwa watumiaji — itaonekana kwenye system + push popup kupitia Firebase</p>
        </div>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="adm-btn adm-btn--muted">← Dashibodi</a>
    </div>

    <?php if(session('success')): ?>
        <div class="adm-card" style="border-left:4px solid #10b981;background:#ecfdf5;">
            <p style="color:#047857;font-weight:700;">✅ <?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>

    
    <div class="adm-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;">
        <div class="adm-card" style="text-align:center;">
            <div style="font-size:1.7rem;font-weight:800;color:#0f172a;"><?php echo e(number_format($stats['all'])); ?></div>
            <div style="font-size:0.78rem;color:#64748b;">Watumiaji wote</div>
        </div>
        <div class="adm-card" style="text-align:center;">
            <div style="font-size:1.7rem;font-weight:800;color:#7c3aed;"><?php echo e(number_format($stats['muhitaji'])); ?></div>
            <div style="font-size:0.78rem;color:#64748b;">Wahitaji</div>
        </div>
        <div class="adm-card" style="text-align:center;">
            <div style="font-size:1.7rem;font-weight:800;color:#0891b2;"><?php echo e(number_format($stats['mfanyakazi'])); ?></div>
            <div style="font-size:0.78rem;color:#64748b;">Wafanyakazi</div>
        </div>
        <div class="adm-card" style="text-align:center;">
            <div style="font-size:1.7rem;font-weight:800;color:#10b981;"><?php echo e(number_format($stats['with_fcm'])); ?></div>
            <div style="font-size:0.78rem;color:#64748b;">Wenye FCM token (push-able)</div>
        </div>
    </div>

    
    <div class="adm-card">
        <form method="POST" action="<?php echo e(route('admin.broadcast.send')); ?>" id="broadcast-form">
            <?php echo csrf_field(); ?>

            <div class="adm-form-group">
                <label class="adm-label" for="title">Kichwa</label>
                <input id="title" class="adm-input" type="text" name="title" value="<?php echo e(old('title')); ?>" placeholder="mfano: Mafundi wapya wamejisajili" required maxlength="255">
                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="adm-field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="adm-form-group">
                <label class="adm-label" for="message">Ujumbe</label>
                <textarea id="message" name="message" class="adm-textarea" rows="4" placeholder="Andika ujumbe wa push notification..." required maxlength="2000"><?php echo e(old('message')); ?></textarea>
                <p style="font-size:0.7rem;color:#64748b;margin-top:4px;"><span id="msg-counter">0</span>/2000</p>
                <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="adm-field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="adm-form-group">
                <label class="adm-label" for="action_url">Action URL (hiari)</label>
                <input id="action_url" class="adm-input" type="text" name="action_url" value="<?php echo e(old('action_url')); ?>" placeholder="/jobs/123 au https://...">
                <p style="font-size:0.7rem;color:#64748b;margin-top:4px;">Mtumiaji akibofya notification, atapelekwa kwenye link hii.</p>
            </div>

            <div class="adm-form-group">
                <label class="adm-label" for="target">Lengo</label>
                <select id="target" name="target" class="adm-select" onchange="onTargetChange()">
                    <option value="all" <?php if(old('target', 'all') === 'all'): echo 'selected'; endif; ?>>Watumiaji wote</option>
                    <option value="muhitaji" <?php if(old('target') === 'muhitaji'): echo 'selected'; endif; ?>>Wahitaji tu</option>
                    <option value="mfanyakazi" <?php if(old('target') === 'mfanyakazi'): echo 'selected'; endif; ?>>Wafanyakazi tu</option>
                    <option value="specific" <?php if(old('target') === 'specific'): echo 'selected'; endif; ?>>Watumiaji maalum (chagua)</option>
                </select>
                <?php $__errorArgs = ['target'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="adm-field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="adm-form-group" id="user-picker" style="display:none;">
                <label class="adm-label">Chagua watumiaji</label>
                <input type="text" id="user-search" class="adm-input" placeholder="Tafuta kwa jina, email, au simu...">
                <div id="user-search-results" style="border:1px solid #e2e8f0;border-radius:8px;max-height:240px;overflow-y:auto;margin-top:6px;display:none;background:#fff;"></div>
                <div id="selected-users" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;"></div>
                <p style="font-size:0.7rem;color:#64748b;margin-top:6px;">Bofya mtumiaji kumchagua. Bofya tena kuondoa.</p>
                <?php $__errorArgs = ['user_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="adm-field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="adm-card" style="background:#f1f5f9;border:1px solid #cbd5e1;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div>
                        <p style="font-size:0.78rem;color:#64748b;margin:0;">Itatumwa kwa</p>
                        <p style="font-size:1.4rem;font-weight:800;color:#0f172a;margin:2px 0;"><span id="live-total"><?php echo e($stats['all']); ?></span> watumiaji</p>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-size:0.78rem;color:#10b981;font-weight:700;margin:0;">📱 <span id="live-fcm"><?php echo e($stats['with_fcm']); ?></span> watapata push pop-up</p>
                        <p style="font-size:0.78rem;color:#94a3b8;margin:0;">📋 <span id="live-no-fcm">0</span> wataona kwenye system tu</p>
                    </div>
                </div>
            </div>

            <div class="adm-actions" style="justify-content:flex-end;margin-top:12px;">
                <button type="submit" class="adm-btn adm-btn--primary" id="submit-btn">📤 Tuma Notification</button>
            </div>
        </form>
    </div>

    
    <?php if($recent->count() > 0): ?>
        <div class="adm-card">
            <h3 style="margin:0 0 12px;">Notifications za hivi karibuni</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="border-left:3px solid #6366f1;padding:8px 12px;background:#f8fafc;border-radius:6px;">
                        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                            <strong style="color:#0f172a;"><?php echo e($sn->title); ?></strong>
                            <span style="font-size:0.72rem;color:#64748b;"><?php echo e($sn->created_at->diffForHumans()); ?> · <?php echo e($sn->target); ?></span>
                        </div>
                        <p style="font-size:0.82rem;color:#475569;margin:4px 0 0;"><?php echo e(\Illuminate\Support\Str::limit($sn->message, 200)); ?></p>
                        <p style="font-size:0.72rem;color:#94a3b8;margin:4px 0 0;">Imetumwa na <?php echo e($sn->sender->name ?? 'Admin'); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
const csrf = '<?php echo e(csrf_token()); ?>';
const audienceUrl = '<?php echo e(route('admin.broadcast.audience')); ?>';
const searchUrl = '<?php echo e(route('admin.broadcast.users.search')); ?>';
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/admin/broadcast.blade.php ENDPATH**/ ?>