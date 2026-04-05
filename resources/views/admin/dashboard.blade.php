@extends('layouts.admin')
@section('title', 'Admin — Super Dashboard')

@section('content')
<style>
  /* ====== Super Admin Dashboard - Dark Theme ====== */
  .dashboard-container {
    --primary: #6366f1;
    --secondary: #06b6d4;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #f43f5e;
    --card-bg: rgba(255,255,255,0.05);
    --card-bg-hover: rgba(255,255,255,0.08);
    --text-primary: #ffffff;
    --text-muted: #94a3b8;
    --border: rgba(255,255,255,0.1);
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
  }

  .dashboard-container {
    max-width: 1600px;
    margin: 0 auto;
    display: grid;
    gap: 24px;
  }

  /* Header */
  .admin-header {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 20px 24px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border);
  }

  .header-content {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 24px;
    align-items: center;
  }

  .header-text h1 {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 8px 0;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .header-text p {
    color: var(--text-muted);
    font-size: 0.95rem;
    margin: 0;
  }

  .header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }

  /* Super Stats Grid */
  .super-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
  }

  .super-stat-card {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 16px 20px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }
  
  .super-stat-card:hover {
    background: var(--card-bg-hover);
  }

  .super-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
  }

  .super-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, var(--primary), var(--success));
  }

  .stat-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
  }

  .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    background: linear-gradient(135deg, var(--primary), var(--success));
    color: white;
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
  }

  .stat-info h3 {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0;
  }

  .stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 8px 0;
    line-height: 1;
  }

  .stat-change {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-top: 8px;
  }

  .stat-change.positive {
    color: var(--success);
  }

  .stat-change.negative {
    color: var(--danger);
  }

  .stat-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 4px;
  }

  /* Real-time Monitoring */
  .monitoring-section {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }

  .monitoring-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
  }

  .monitoring-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary), var(--success));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
  }

  .monitoring-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
  }

  .monitoring-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
  }

  .monitoring-card {
    background: rgba(255,255,255,0.03);
    border-radius: 16px;
    padding: 20px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
  }
  
  .monitoring-card:hover {
    background: rgba(255,255,255,0.06);
  }

  .monitoring-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
  }

  .monitoring-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
  }

  .monitoring-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .live-indicator {
    width: 8px;
    height: 8px;
    background: var(--success);
    border-radius: 50%;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
  }

  .monitoring-list {
    display: grid;
    gap: 12px;
  }

  .monitoring-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255,255,255,0.03);
    border-radius: 12px;
    transition: all 0.3s ease;
  }

  .monitoring-item:hover {
    background: rgba(255,255,255,0.08);
    transform: translateX(4px);
  }

  .monitoring-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--success));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
  }

  .monitoring-details h4 {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 2px 0;
  }

  .monitoring-details p {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin: 0;
  }

  .monitoring-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .monitoring-status.active {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
  }

  .monitoring-status.completed {
    background: rgba(99, 102, 241, 0.2);
    color: #818cf8;
  }

  .monitoring-status.pending {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
  }

  /* Analytics Charts */
  .analytics-section {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }

  .analytics-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
  }

  .chart-container {
    background: rgba(255,255,255,0.03);
    border-radius: 16px;
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }
  
  .chart-container:hover {
    background: rgba(255,255,255,0.06);
  }

  .chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
  }

  .chart-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .chart-canvas-container {
    position: relative;
    height: 300px;
    width: 100%;
  }

  .chart-placeholder {
    height: 300px;
    background: rgba(255,255,255,0.02);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 1.1rem;
    font-weight: 600;
    border: 2px dashed var(--border);
    position: relative;
    overflow: hidden;
  }

  /* Quick Actions */
  .quick-actions-section {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }

  .actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
  }

  .action-card {
    background: rgba(255,255,255,0.03);
    border-radius: 16px;
    padding: 20px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    text-align: center;
    text-decoration: none;
    color: inherit;
  }
  
  .action-card:hover {
    background: rgba(255,255,255,0.08);
  }

  .action-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    color: inherit;
  }

  .action-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--primary), var(--success));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin: 0 auto 12px;
  }

  .action-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 4px 0;
  }

  .action-description {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
  }

  /* Buttons */
  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
  }

  .btn-primary {
    background: linear-gradient(135deg, var(--primary), #1d4ed8);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.4);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px 0 rgba(59, 130, 246, 0.6);
  }

  .btn-success {
    background: linear-gradient(135deg, var(--success), #059669);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.4);
  }

  .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px 0 rgba(16, 185, 129, 0.6);
  }

  .btn-warning {
    background: linear-gradient(135deg, var(--warning), #d97706);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(245, 158, 11, 0.4);
  }

  .btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px 0 rgba(245, 158, 11, 0.6);
  }

  .btn-danger {
    background: linear-gradient(135deg, var(--danger), #dc2626);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(239, 68, 68, 0.4);
  }

  .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px 0 rgba(239, 68, 68, 0.6);
  }

  .btn-outline {
    background: transparent;
    color: var(--primary);
    border: 2px solid var(--primary);
  }

  .btn-outline:hover {
    background: var(--primary);
    color: white;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .admin-header {
      padding: 24px;
    }
    
    .header-content {
      grid-template-columns: 1fr;
      text-align: center;
    }
    
    .header-text h1 {
      font-size: 2.5rem;
    }
    
    .super-stats-grid {
      grid-template-columns: 1fr;
    }
    
    .monitoring-grid {
      grid-template-columns: 1fr;
    }
    
    .analytics-grid {
      grid-template-columns: 1fr;
    }
    
    .actions-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

@php
  $s = $stats ?? [];
@endphp

<div class="dashboard-container">
    
    <!-- Super Admin Header -->
    <div class="admin-header">
      <div class="header-content">
        <div class="header-text">
          <h1>🛠️ Dashibodi ya Admin</h1>
          <p>Muhtasari wa mfumo — kazi, malipo, escrow, na watumiaji (data halisi kutoka database; hakuna takwimu bandia).</p>
        </div>
        <div class="header-actions">
          <a class="btn btn-primary" href="{{ route('jobs.create') }}">
            <span>📝</span>
            Chapisha kazi
          </a>
          <a class="btn btn-success" href="{{ route('admin.analytics') }}">
            <span>📊</span>
            Analytics
          </a>
          <a class="btn btn-warning" href="{{ route('admin.users') }}">
            <span>👥</span>
            Watumiaji
          </a>
          <a class="btn" href="{{ route('admin.jobs') }}" style="background:#0ea5e9;color:#fff;border:none;">
            <span>💼</span>
            Kazi
          </a>
          <a class="btn" href="{{ route('admin.broadcast') }}" style="background:#8b5cf6;color:#fff;border:none;">
            <span>📢</span>
            Tuma taarifa
          </a>
        </div>
      </div>
    </div>

    <!-- Super Stats Grid -->
    <div class="super-stats-grid">
      <div class="super-stat-card">
        <div class="stat-header">
          <div class="stat-icon">📦</div>
          <div class="stat-info">
            <h3>Jumla ya kazi</h3>
            <div class="stat-value">{{ number_format($s['total_jobs'] ?? 0) }}</div>
            <div class="stat-trend">
              <span>⚡</span>
              {{ number_format($s['active_jobs'] ?? 0) }} zinaendelea • {{ number_format($s['completed_jobs'] ?? 0) }} zimekamilika
            </div>
            <div class="stat-trend">
              <span>📂</span>
              Wazi: {{ number_format($s['open_jobs'] ?? 0) }} • Subiri malipo: {{ number_format($s['awaiting_payment_jobs'] ?? 0) }} • Escrow/mchakato: {{ number_format($s['escrow_pipeline_jobs'] ?? 0) }}
            </div>
            <div class="stat-trend">
              <span style="color:#f87171;">⚠️</span>
              Mgogoro: {{ number_format($s['disputed_jobs'] ?? 0) }} • Zilizofutwa: {{ number_format($s['cancelled_jobs'] ?? 0) }}
            </div>
            @if(isset($s['jobs_created_trend_pct']) && $s['jobs_created_trend_pct'] !== null)
              <div class="stat-change {{ ($s['jobs_created_trend_pct'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                <span>📈</span>
                Kazi zilizoongezwa (siku 30): {{ ($s['jobs_created_trend_pct'] ?? 0) >= 0 ? '+' : ''}}{{ $s['jobs_created_trend_pct'] }}% vs siku 30 zilizotangulia
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="super-stat-card">
        <div class="stat-header">
          <div class="stat-icon">💰</div>
          <div class="stat-info">
            <h3>Malipo & ada ya jukwaa</h3>
            <div class="stat-value" style="color: #10b981;">TZS {{ number_format($s['payments_completed_sum'] ?? 0) }}</div>
            <div class="stat-trend">
              <span>🏦</span>
              Jumla ya malipo yaliyokamilika (payments COMPLETED)
            </div>
            <div class="stat-trend">
              <span>📌</span>
              Ada iliyorekodiwa (platform_fee): TZS {{ number_format($s['platform_fees_sum'] ?? 0) }}
            </div>
            <div class="stat-trend">
              <span>💳</span>
              Kutoa zinazosubiri: {{ number_format($s['pending_withdrawals'] ?? 0) }}
            </div>
          </div>
        </div>
      </div>

      <div class="super-stat-card">
        <div class="stat-header">
          <div class="stat-icon">👥</div>
          <div class="stat-info">
            <h3>Watumiaji</h3>
            <div class="stat-value">{{ number_format($s['total_users'] ?? 0) }}</div>
            <div class="stat-trend">
              <span>👷</span>
              Wafanyakazi {{ number_format($s['mfanyakazi_count'] ?? 0) }} • Wateja {{ number_format($s['muhitaji_count'] ?? 0) }} • Admin {{ number_format($s['admin_count'] ?? 0) }}
            </div>
            @if(isset($s['users_trend_pct']) && $s['users_trend_pct'] !== null)
              <div class="stat-change {{ ($s['users_trend_pct'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                <span>📈</span>
                Usajili (siku 30): {{ ($s['users_trend_pct'] ?? 0) >= 0 ? '+' : ''}}{{ $s['users_trend_pct'] }}% vs siku 30 zilizotangulia
              </div>
            @endif
            <div class="stat-trend">
              <span>💬</span>
              Jumla ya ujumbe: {{ number_format($s['total_messages'] ?? 0) }}
            </div>
          </div>
        </div>
      </div>

      <div class="super-stat-card">
        <div class="stat-header">
          <div class="stat-icon">⭐</div>
          <div class="stat-info">
            <h3>Kiwango cha ukamilishaji</h3>
            <div class="stat-value">{{ number_format($s['completion_rate_pct'] ?? 0) }}%</div>
            <div class="stat-trend">
              <span>🎯</span>
              {{ number_format($s['completed_jobs'] ?? 0) }} / {{ number_format($s['total_jobs'] ?? 0) }} kazi zimekamilika
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Real-time Monitoring -->
    <div class="monitoring-section">
      <div class="monitoring-header">
        <div class="monitoring-icon">📡</div>
        <div class="monitoring-title">Ufuatiliaji wa kazi (mfumo mpya + zamani)</div>
        <div class="live-indicator"></div>
      </div>
      
      <div class="monitoring-grid">
        <!-- Active Jobs -->
        <div class="monitoring-card">
          <div class="monitoring-card-header">
            <div class="monitoring-card-title">
              <span>⚡</span>
              Kazi kwenye mtiririko
            </div>
            <div class="live-indicator"></div>
          </div>
          <div class="monitoring-list">
            @foreach($pipelineMonitorJobs ?? [] as $job)
              <a href="{{ route('admin.job.details', $job) }}" class="monitoring-item" style="text-decoration:none;color:inherit;">
                <div class="monitoring-avatar">
                  {{ strtoupper(substr($job->muhitaji->name ?? 'U', 0, 2)) }}
                </div>
                <div class="monitoring-details">
                  <h4>{{ \Illuminate\Support\Str::limit($job->title, 42) }}</h4>
                  <p>{{ $job->status }} • {{ $job->acceptedWorker->name ?? 'Bila mfanyakazi' }}</p>
                </div>
                <div class="monitoring-status active">{{ $job->status }}</div>
              </a>
            @endforeach
            @if(($pipelineMonitorJobs ?? collect())->isEmpty())
              <div class="monitoring-item">
                <div class="monitoring-details">
                  <h4>Hakuna kazi kwenye mtiririko</h4>
                  <p>Escrow / in_progress / submitted zote tupu kwa sasa</p>
                </div>
              </div>
            @endif
          </div>
        </div>

        <!-- Recent Users -->
        <div class="monitoring-card">
          <div class="monitoring-card-header">
            <div class="monitoring-card-title">
              <span>👥</span>
              Watumiaji wa hivi karibuni
            </div>
            <div class="live-indicator"></div>
          </div>
          <div class="monitoring-list">
            @foreach($recentUsers as $user)
              <a href="{{ route('admin.user.details', $user) }}" class="monitoring-item" style="text-decoration:none;color:inherit;">
                <div class="monitoring-avatar">
                  {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="monitoring-details">
                  <h4>{{ $user->name }}</h4>
                  <p>{{ ucfirst($user->role) }} • {{ $user->created_at->diffForHumans() }}</p>
                </div>
                <div class="monitoring-status active">Mpya</div>
              </a>
            @endforeach
          </div>
        </div>

        <!-- Pending Withdrawals -->
        <div class="monitoring-card">
          <div class="monitoring-card-header">
            <div class="monitoring-card-title">
              <span>💰</span>
              Kutoa zinazosubiri (PROCESSING)
            </div>
            <div class="live-indicator"></div>
          </div>
          <div class="monitoring-list">
            @foreach(($recentWithdrawals ?? collect())->where('status', 'PROCESSING') as $withdrawal)
              <a href="{{ route('admin.withdrawals') }}" class="monitoring-item" style="text-decoration:none;color:inherit;">
                <div class="monitoring-avatar">
                  {{ strtoupper(substr($withdrawal->user->name ?? 'U', 0, 2)) }}
                </div>
                <div class="monitoring-details">
                  <h4>{{ $withdrawal->user->name ?? 'User' }}</h4>
                  <p>TZS {{ number_format($withdrawal->amount) }} • {{ $withdrawal->created_at->diffForHumans() }}</p>
                </div>
                <div class="monitoring-status pending">Subiri</div>
              </a>
            @endforeach
            @if(($recentWithdrawals ?? collect())->where('status', 'PROCESSING')->count() === 0)
              <div class="monitoring-item">
                <div class="monitoring-details">
                  <h4>Hakuna kutoa zinazosubiri</h4>
                  <p>Hakuna ombi la PROCESSING kwenye sampuli hii</p>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Analytics Charts -->
    <div class="analytics-section">
      <div class="analytics-grid">
        <!-- Revenue Chart -->
        <div class="chart-container">
          <div class="chart-header">
            <div class="chart-title">
              <span>📈</span>
              Mapato ya kazi zilizokamilika (siku 7)
            </div>
            <span class="btn btn-outline" style="font-size: 0.75rem; padding: 8px 12px; cursor: default;">TZS / siku</span>
          </div>
          <div class="chart-canvas-container">
            <canvas id="revenueChart"></canvas>
          </div>
        </div>

        <!-- Top Performers -->
        <div class="chart-container">
          <div class="chart-header">
            <div class="chart-title">
              <span>🏆</span>
              Wafanyakazi bora (ukamilishaji)
            </div>
            <a class="btn btn-outline" href="{{ route('admin.users') }}" style="font-size: 0.75rem; padding: 8px 12px;">
              View All
            </a>
          </div>
          <div style="display: grid; gap: 12px;">
            @foreach($topWorkers ?? [] as $index => $worker)
              <a href="{{ route('admin.user.details', $worker) }}" class="monitoring-item" style="text-decoration:none;color:inherit;">
                <div class="monitoring-avatar">
                  {{ $index + 1 }}
                </div>
                <div class="monitoring-details">
                  <h4>{{ $worker->name }}</h4>
                  <p>{{ $worker->completed_jobs_count ?? 0 }} kazi zilizokamilika</p>
                </div>
                <div class="monitoring-status completed">#{{ $index + 1 }}</div>
              </a>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-section">
      <div class="monitoring-header">
        <div class="monitoring-icon">⚡</div>
        <div class="monitoring-title">Viungo vya haraka</div>
      </div>
      <div class="actions-grid">
        <a class="action-card" href="{{ route('admin.users') }}">
          <div class="action-icon">👥</div>
          <div class="action-title">User Management</div>
          <div class="action-description">Manage all users, roles, and permissions</div>
        </a>
        
        <a class="action-card" href="{{ route('admin.jobs') }}">
          <div class="action-icon">📋</div>
          <div class="action-title">Job Management</div>
          <div class="action-description">Oversee all jobs and assignments</div>
        </a>
        
        <a class="action-card" href="{{ route('admin.withdrawals') }}">
          <div class="action-icon">💰</div>
          <div class="action-title">Withdrawal Processing</div>
          <div class="action-description">Approve and manage withdrawals</div>
        </a>
        
        <a class="action-card" href="{{ route('admin.analytics') }}">
          <div class="action-icon">📊</div>
          <div class="action-title">Analytics & Reports</div>
          <div class="action-description">View detailed analytics and reports</div>
        </a>
        
        <a class="action-card" href="{{ route('admin.chats') }}">
          <div class="action-icon">💬</div>
          <div class="action-title">Conversation Monitor</div>
          <div class="action-description">Monitor on-demand employee chats</div>
        </a>
        
        <a class="action-card" href="{{ route('admin.completed-jobs') }}">
          <div class="action-icon">✅</div>
          <div class="action-title">Completed Jobs</div>
          <div class="action-description">View all completed work by workers</div>
        </a>
        
        <a class="action-card" href="{{ route('admin.categories') }}">
          <div class="action-icon">📁</div>
          <div class="action-title">Makundi</div>
          <div class="action-description">Simamia aina za kazi</div>
        </a>
        
        <a class="action-card" href="{{ route('admin.system-logs') }}">
          <div class="action-icon">📝</div>
          <div class="action-title">System Logs</div>
          <div class="action-description">Monitor system activities and logs</div>
        </a>
      </div>
    </div>

</div>

<script>
  // Chart.js Configuration for Dark Theme
  Chart.defaults.color = '#94a3b8';
  Chart.defaults.borderColor = 'rgba(255,255,255,0.1)';
  Chart.defaults.backgroundColor = 'rgba(99, 102, 241, 0.1)';

  const revenueCtx = document.getElementById('revenueChart');
  if (revenueCtx) {
    new Chart(revenueCtx, {
      type: 'line',
      data: {
        labels: @json($revenueLabels ?? []),
        datasets: [{
          label: 'TZS (kazi zilizokamilika)',
          data: @json($revenueData ?? []),
          borderColor: '#6366f1',
          backgroundColor: 'rgba(99, 102, 241, 0.1)',
          tension: 0.4,
          fill: true,
          pointBackgroundColor: '#6366f1',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            labels: {
              color: '#94a3b8'
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#ffffff',
            bodyColor: '#94a3b8',
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 1
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              color: '#94a3b8',
              callback: function(value) {
                return 'TZS ' + value.toLocaleString();
              }
            },
            grid: {
              color: 'rgba(255,255,255,0.05)'
            }
          },
          x: {
            ticks: {
              color: '#94a3b8'
            },
            grid: {
              color: 'rgba(255,255,255,0.05)'
            }
          }
        }
      }
    });
  }

  // Add some interactive animations
  document.addEventListener('DOMContentLoaded', function() {
    // Animate stat cards on scroll
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);

    // Observe all stat cards
    document.querySelectorAll('.super-stat-card').forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'all 0.6s ease';
      observer.observe(card);
    });

    // Observe monitoring cards
    document.querySelectorAll('.monitoring-card').forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'all 0.6s ease';
      observer.observe(card);
    });

    // Observe action cards
    document.querySelectorAll('.action-card').forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'all 0.6s ease';
      observer.observe(card);
    });

    // Add hover effects
    document.querySelectorAll('.super-stat-card').forEach(card => {
      card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px) scale(1.02)';
      });
      
      card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
      });
    });
  });
</script>
@endsection