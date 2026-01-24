@extends('layouts.admin')
@section('title', 'Admin — Commission & Fees')

@section('content')
<style>
  .page-container {
    --primary: #6366f1;
    --success: #10b981;
    --card-bg: rgba(255,255,255,0.05);
    --text-primary: #ffffff;
    --text-muted: #94a3b8;
    --border: rgba(255,255,255,0.1);
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    gap: 24px;
  }

  .page-header {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 32px;
    border: 1px solid var(--border);
  }

  .header-text h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 8px 0;
    background: linear-gradient(135deg, var(--primary), var(--success));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
  }

  .stat-card {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 24px;
    border: 1px solid var(--border);
    text-align: center;
  }

  .stat-value {
    font-size: 2.5rem;
    font-weight: 900;
    color: var(--success);
    margin: 8px 0;
  }

  .commission-list {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 24px;
    border: 1px solid var(--border);
  }

  .commission-table {
    width: 100%;
    border-collapse: collapse;
    color: var(--text-primary);
  }

  .commission-table th {
    text-align: left;
    padding: 16px;
    border-bottom: 1px solid var(--border);
    color: var(--text-muted);
    font-size: 0.875rem;
    text-transform: uppercase;
  }

  .commission-table td {
    padding: 16px;
    border-bottom: 1px solid var(--border);
  }

  .amount-gross { color: var(--text-muted); font-size: 0.875rem; }
  .amount-fee { color: #f43f5e; font-weight: 700; }
  .amount-net { color: var(--success); font-weight: 700; }

  .btn-outline {
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid var(--border);
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.875rem;
  }
</style>

<div class="page-container">
  <div class="page-header">
    <div class="header-text">
      <h1>💰 Commission & Fees</h1>
      <p>Fuatilia faida ya mfumo (10% Service Fee) kutoka kwa kila kazi iliyokamilika</p>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Total Transaction Volume</div>
      <div class="stat-value" style="color: var(--text-primary);">TZS {{ number_format($totalVolume) }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Total Net Profit (10%)</div>
      <div class="stat-value">TZS {{ number_format($totalCommission) }}</div>
    </div>
  </div>

  <div class="commission-list">
    <table class="commission-table">
      <thead>
        <tr>
          <th>Job ID</th>
          <th>Job Title</th>
          <th>Worker</th>
          <th>Gross Amount</th>
          <th>Fee (10%)</th>
          <th>Worker Net</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        @foreach($jobs as $job)
          @php
            $fee = $job->price * 0.10;
            $net = $job->price - $fee;
          @endphp
          <tr>
            <td>#{{ $job->id }}</td>
            <td>
              <div style="font-weight: 600;">{{ $job->title }}</div>
              <div style="font-size: 0.75rem; color: var(--text-muted);">Client: {{ $job->muhitaji->name ?? 'N/A' }}</div>
            </td>
            <td>{{ $job->acceptedWorker->name ?? 'N/A' }}</td>
            <td class="amount-gross">TZS {{ number_format($job->price) }}</td>
            <td class="amount-fee">- TZS {{ number_format($fee) }}</td>
            <td class="amount-net">TZS {{ number_format($net) }}</td>
            <td>{{ $job->completed_at?->format('M d, Y') ?? 'N/A' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div style="margin-top: 24px;">
      {{ $jobs->links() }}
    </div>
  </div>
</div>
@endsection
