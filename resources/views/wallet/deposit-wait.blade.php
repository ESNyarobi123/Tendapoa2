@extends('layouts.app')
@section('title', 'Inahakiki Deposit')

@section('content')
<style>
  .wait-page {
    --primary: #3b82f6;
    --success: #10b981;
    --danger: #ef4444;
    --dark: #1f2937;
    --text-muted: #6b7280;
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    min-height: 100vh;
    display: flex;
    position: relative;
  }
  .main-content { flex:1; margin-left:280px; padding:24px; min-height:100vh; }
  .sidebar.collapsed ~ .main-content { margin-left:80px; }
  @media(max-width:1024px){ .main-content { margin-left:0; } }
  .page-container { max-width:600px; margin:0 auto; }
  .card {
    background:rgba(255,255,255,0.95); backdrop-filter:blur(20px);
    border-radius:24px; padding:40px; box-shadow:var(--shadow-lg); text-align:center;
  }
  .card h2 { font-size:1.8rem; font-weight:800; color:var(--dark); margin-bottom:12px; }
  .card p { color:var(--text-muted); margin-bottom:8px; }
  .badge {
    display:inline-block; padding:6px 16px; border-radius:20px;
    font-weight:700; font-size:0.9rem; background:#fef3c7; color:#92400e;
  }
  .spinner { margin:24px auto; width:48px; height:48px; border:4px solid #e5e7eb;
    border-top-color:var(--primary); border-radius:50%; animation:spin 1s linear infinite; }
  @keyframes spin { to { transform:rotate(360deg); } }
  #status-text { color:#666; font-style:italic; margin-top:16px; }
  .btn { display:inline-flex; align-items:center; gap:8px; padding:10px 20px;
    border-radius:10px; font-weight:600; text-decoration:none; border:none; cursor:pointer; font-size:0.875rem; }
  .btn-outline { background:transparent; color:var(--primary); border:2px solid var(--primary); }
  .btn-outline:hover { background:var(--primary); color:white; }
</style>

<div class="wait-page">
  @include('components.user-sidebar')

  <main class="main-content">
    <div class="page-container">
      <div class="card">
        <h2>Inahakiki Deposit</h2>
        <p>Ombi la malipo limetumwa. Tafadhali thibitisha kwenye simu yako.</p>
        <p>Kiasi: <b>{{ number_format($transaction->meta['requested'] ?? 0) }} TZS</b></p>
        <div id="st" class="badge">INASUBIRI</div>
        <div class="spinner" id="spinner"></div>
        <p id="status-text">Inahakiki malipo...</p>

        <div style="margin-top:24px;">
          <a href="{{ route('wallet.deposit') }}" class="btn btn-outline">↩️ Rudi kwenye Deposit</a>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  let startTime = Date.now();
  const timeoutMs = 10 * 60 * 1000;

  setInterval(() => {
    const el = document.getElementById('status-text');
    if (el && !el.dataset.done) {
      el.textContent = el.textContent.includes('...') ? 'Inahakiki malipo' : el.textContent + '.';
    }
  }, 500);

  setInterval(async () => {
    if (Date.now() - startTime > timeoutMs) {
      window.location.href = '{{ route("dashboard") }}';
      return;
    }
    try {
      const r = await fetch('{{ route("wallet.deposit.poll", $transaction) }}?t=' + Date.now());
      const j = await r.json();

      if (j.status === 'COMPLETED') {
        document.getElementById('st').textContent = 'IMEKAMILIKA';
        document.getElementById('st').style.background = '#dcfce7';
        document.getElementById('st').style.color = '#16a34a';
        document.getElementById('spinner').style.display = 'none';
        const txt = document.getElementById('status-text');
        txt.textContent = 'Deposit imekamilika! Unapelekwa...';
        txt.style.color = '#16a34a';
        txt.style.fontWeight = 'bold';
        txt.dataset.done = '1';
        setTimeout(() => location.href = '{{ route("dashboard") }}', 1500);
      } else if (j.status === 'FAILED') {
        document.getElementById('st').textContent = 'IMESHINDIKANA';
        document.getElementById('st').style.background = '#fef2f2';
        document.getElementById('st').style.color = '#dc2626';
        document.getElementById('spinner').style.display = 'none';
        const txt = document.getElementById('status-text');
        txt.textContent = 'Malipo yameshindikana. Jaribu tena.';
        txt.style.color = '#dc2626';
        txt.dataset.done = '1';
      }
    } catch (e) { console.error('Poll error', e); }
  }, 3000);
</script>
@endsection
