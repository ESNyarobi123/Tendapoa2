@extends('layouts.app')

@section('content')
<h2>Thibitisha Malipo</h2>
<div class="card">
  <p>Ombi la malipo limetumwa. Tafadhali thibitisha kwenye M-Pesa/TigoPesa/Airtel Money.</p>
  <p>Order: <b>{{ $job->payment->order_id }}</b> • Kiasi: <b>{{ number_format($job->payment->amount) }} TZS</b></p>
  <div id="st" class="badge">{{ $job->payment->status }}</div>
  
  <div style="margin-top: 20px;">
    <form action="{{ route('jobs.cancel', $job) }}" method="POST" onsubmit="return confirm('Je, una uhakika unataka kufuta kazi hii na kuacha kulipa?');">
      @csrf
      <button type="submit" class="btn btn-danger" style="background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">
        Futa Kazi (Sitaki Kulipa)
      </button>
    </form>
  </div>
</div>

<script>
setInterval(async ()=>{
  const r = await fetch('{{ route('jobs.pay.poll',$job) }}');
  const j = await r.json();
  if (j.done) {
    document.getElementById('st').textContent='COMPLETED';
    location.href='{{ route('jobs.show',$job) }}';
  }
}, 3000);
</script>
@endsection
