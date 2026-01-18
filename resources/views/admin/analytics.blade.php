@extends('layouts.admin')
@section('title', 'Admin — Analytics & Reports')

@section('content')
<style>
  /* ====== Modern Admin Analytics Page - Dark Theme ====== */
  .page-container {
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

  .page-container {
    max-width: 1400px;
    margin: 0 auto;
    display: grid;
    gap: 24px;
  }

  /* Header */
  .page-header {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 32px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border);
    position: relative;
    overflow: hidden;
  }
  
  .page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899, #06b6d4);
    background-size: 200% 100%;
    animation: gradientShift 3s ease infinite;
  }
  
  @keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
  }

  .header-content {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 24px;
    align-items: center;
  }

  .header-text h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 8px 0;
    background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
    z-index: 1;
  }

  .header-text p {
    color: var(--text-muted);
    font-size: 1.1rem;
    margin: 0;
  }

  .header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }

  /* Date Range Picker */
  .date-range-section {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }

  .date-range-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    align-items: end;
  }

  .date-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .date-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .date-input {
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: 12px;
    font-size: 0.875rem;
    background: rgba(255,255,255,0.05);
    color: var(--text-primary);
    transition: all 0.3s ease;
  }

  .date-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  /* Key Metrics */
  .metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
  }

  .metric-card {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }
  
  .metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899);
  }

  .metric-card:hover {
    background: var(--card-bg-hover);
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
  }

  .metric-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
  }

  .metric-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: linear-gradient(135deg, var(--primary), var(--success));
    color: white;
  }

  .metric-info h3 {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0;
  }

  .metric-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 8px 0;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .metric-change {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.875rem;
    font-weight: 600;
  }

  .metric-change.positive {
    color: var(--success);
  }

  .metric-change.negative {
    color: var(--danger);
  }

  /* Charts Section */
  .charts-section {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }

  .charts-grid {
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
  }

  /* Top Performers */
  .top-performers {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }

  .performers-list {
    display: grid;
    gap: 16px;
  }

  .performer-item {
    background: rgba(255,255,255,0.03);
    border-radius: 12px;
    padding: 16px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
  }
  
  .performer-item:hover {
    background: rgba(255,255,255,0.08);
  }

  .performer-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
  }

  .performer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
  }

  .performer-info {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .performer-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--success));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
  }

  .performer-details h4 {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
  }

  .performer-details p {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin: 0;
  }

  .performer-stats {
    text-align: right;
  }

  .performer-value {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--success);
  }

  .performer-label {
    font-size: 0.75rem;
    color: var(--text-muted);
  }

  /* Buttons */
  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 12px;
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

  .btn-outline {
    background: transparent;
    color: var(--primary);
    border: 2px solid var(--primary);
  }

  .btn-outline:hover {
    background: var(--primary);
    color: white;
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

  /* Responsive */
  @media (max-width: 768px) {
    .analytics-page {
      padding: 16px;
    }
    
    .page-header {
      padding: 24px;
    }
    
    .header-content {
      grid-template-columns: 1fr;
      text-align: center;
    }
    
    .header-text h1 {
      font-size: 2rem;
    }
    
    .date-range-grid {
      grid-template-columns: 1fr;
    }
    
    .metrics-grid {
      grid-template-columns: 1fr;
    }
    
    .charts-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="page-container">
  <div class="page-container">
    
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-text">
          <h1>📊 Analytics & Reports</h1>
          <p>Chambua data na ufuatilie utendaji wa mfumo</p>
        </div>
        <div class="header-actions">
          <a class="btn btn-outline" href="{{ route('dashboard') }}">
            <span>↩️</span>
            Rudi Dashboard
          </a>
          <a class="btn btn-primary" href="{{ url('/admin/analytics/export') }}">
            <span>📈</span>
            Export Report
          </a>
        </div>
      </div>
    </div>

    <!-- Date Range Picker -->
    <div class="date-range-section">
      <div class="date-range-grid">
        <div class="date-group">
          <label class="date-label" for="startDate">Start Date</label>
          <input type="date" class="date-input" id="startDate" value="{{ date('Y-m-01') }}">
        </div>
        <div class="date-group">
          <label class="date-label" for="endDate">End Date</label>
          <input type="date" class="date-input" id="endDate" value="{{ date('Y-m-d') }}">
        </div>
        <div class="date-group">
          <button class="btn btn-primary" onclick="updateAnalytics()">
            <span>🔄</span>
            Update Analytics
          </button>
        </div>
        <div class="date-group">
          <button class="btn btn-outline" onclick="resetDateRange()">
            <span>📅</span>
            Reset to Current Month
          </button>
        </div>
      </div>
    </div>

    <!-- Key Metrics -->
    <div class="metrics-grid">
      <div class="metric-card">
        <div class="metric-header">
          <div class="metric-icon">📦</div>
          <div class="metric-info">
            <h3>Total Jobs</h3>
            <div class="metric-value">{{ number_format($totalJobs ?? 0) }}</div>
            <div class="metric-change positive">
              <span>📈</span>
              +12% from last month
            </div>
          </div>
        </div>
      </div>

      <div class="metric-card">
        <div class="metric-header">
          <div class="metric-icon">💰</div>
          <div class="metric-info">
            <h3>Total Revenue</h3>
            <div class="metric-value">TZS {{ number_format($totalRevenue ?? 0) }}</div>
            <div class="metric-change positive">
              <span>📈</span>
              +8% from last month
            </div>
          </div>
        </div>
      </div>

      <div class="metric-card">
        <div class="metric-header">
          <div class="metric-icon">👥</div>
          <div class="metric-info">
            <h3>Active Users</h3>
            <div class="metric-value">{{ number_format($activeUsers ?? 0) }}</div>
            <div class="metric-change positive">
              <span>📈</span>
              +15% from last month
            </div>
          </div>
        </div>
      </div>

      <div class="metric-card">
        <div class="metric-header">
          <div class="metric-icon">⭐</div>
          <div class="metric-info">
            <h3>Completion Rate</h3>
            <div class="metric-value">{{ number_format($completionRate ?? 0) }}%</div>
            <div class="metric-change positive">
              <span>📈</span>
              +3% from last month
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
      <div class="charts-grid">
        <!-- Revenue Chart -->
        <div class="chart-container">
          <div class="chart-header">
            <div class="chart-title">
              <span>💰</span>
              Revenue Trend
            </div>
            <select class="btn btn-outline" style="font-size: 0.75rem; padding: 8px 12px;">
              <option>Last 7 days</option>
              <option>Last 30 days</option>
              <option>Last 3 months</option>
            </select>
          </div>
          <div class="chart-canvas-container">
            <canvas id="revenueChart"></canvas>
          </div>
        </div>

        <!-- Job Categories -->
        <div class="chart-container">
          <div class="chart-header">
            <div class="chart-title">
              <span>🏷️</span>
              Job Categories
            </div>
          </div>
          <div class="chart-canvas-container">
            <canvas id="categoryChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Performers -->
    <div class="top-performers">
      <div class="chart-header">
        <div class="chart-title">
          <span>🏆</span>
          Top Performing Workers
        </div>
        <a class="btn btn-outline" href="{{ url('/admin/users') }}" style="font-size: 0.75rem; padding: 8px 12px;">
          View All
        </a>
      </div>
      <div class="performers-list">
        @for($i = 1; $i <= 5; $i++)
          <div class="performer-item">
            <div class="performer-header">
              <div class="performer-info">
                <div class="performer-avatar">
                  {{ chr(64 + $i) }}
                </div>
                <div class="performer-details">
                  <h4>Worker {{ $i }}</h4>
                  <p>Completed {{ rand(10, 50) }} jobs</p>
                </div>
              </div>
              <div class="performer-stats">
                <div class="performer-value">TZS {{ number_format(rand(100000, 500000)) }}</div>
                <div class="performer-label">Total Earnings</div>
              </div>
            </div>
          </div>
        @endfor
      </div>
    </div>

    <!-- Additional Analytics -->
    <div class="charts-section">
      <div class="charts-grid">
        <!-- User Growth -->
        <div class="chart-container">
          <div class="chart-header">
            <div class="chart-title">
              <span>👥</span>
              User Growth
            </div>
          </div>
          <div class="chart-canvas-container">
            <canvas id="userGrowthChart"></canvas>
          </div>
        </div>

        <!-- Job Status Distribution -->
        <div class="chart-container">
          <div class="chart-header">
            <div class="chart-title">
              <span>📋</span>
              Job Status Distribution
            </div>
          </div>
          <div class="chart-canvas-container">
            <canvas id="jobStatusChart"></canvas>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
  // Analytics functions
  function updateAnalytics() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) {
      alert('Please select both start and end dates');
      return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
      alert('Start date cannot be after end date');
      return;
    }
    
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span>⏳</span> Loading...';
    btn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
      btn.innerHTML = originalText;
      btn.disabled = false;
      
      // Update metrics (this would be real data from API)
      updateMetrics();
      
      showNotification('Analytics updated successfully!', 'success');
    }, 2000);
  }

  function resetDateRange() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
    document.getElementById('endDate').value = today.toISOString().split('T')[0];
  }

  function updateMetrics() {
    // This would update the metrics with real data
    console.log('Updating metrics with new data...');
  }

  function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: ${type === 'success' ? '#10b981' : '#ef4444'};
      color: white;
      padding: 16px 24px;
      border-radius: 12px;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      z-index: 1001;
      font-weight: 600;
      transform: translateX(100%);
      transition: transform 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
      notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
      notification.style.transform = 'translateX(100%)';
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 300);
    }, 3000);
  }

  // Chart.js Configuration for Dark Theme
  Chart.defaults.color = '#94a3b8';
  Chart.defaults.borderColor = 'rgba(255,255,255,0.1)';
  Chart.defaults.backgroundColor = 'rgba(99, 102, 241, 0.1)';

  // Initialize Charts
  document.addEventListener('DOMContentLoaded', function() {
    @php
      // Generate revenue data for last 7 days
      $revenueData = [];
      $revenueLabels = [];
      for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);
        $revenueLabels[] = $date->format('M d');
        $dayRevenue = \App\Models\Job::where('status', 'completed')
          ->whereDate('updated_at', $date->format('Y-m-d'))
          ->sum('price');
        $revenueData[] = (int)$dayRevenue;
      }
      
      // Job status distribution
      $jobStatuses = \App\Models\Job::selectRaw('status, count(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();
      
      // User growth data
      $userGrowthData = [];
      $userGrowthLabels = [];
      for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);
        $userGrowthLabels[] = $date->format('M d');
        $dayUsers = \App\Models\User::whereDate('created_at', $date->format('Y-m-d'))->count();
        $userGrowthData[] = $dayUsers;
      }
    @endphp

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
      new Chart(revenueCtx, {
        type: 'line',
        data: {
          labels: @json($revenueLabels),
          datasets: [{
            label: 'Revenue (TZS)',
            data: @json($revenueData),
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
              labels: { color: '#94a3b8' }
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
              ticks: { color: '#94a3b8' },
              grid: { color: 'rgba(255,255,255,0.05)' }
            },
            x: {
              ticks: { color: '#94a3b8' },
              grid: { color: 'rgba(255,255,255,0.05)' }
            }
          }
        }
      });
    }

    // Category Distribution Chart (Doughnut)
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
      new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
          labels: ['Category 1', 'Category 2', 'Category 3', 'Category 4'],
          datasets: [{
            data: [30, 25, 20, 25],
            backgroundColor: [
              'rgba(99, 102, 241, 0.8)',
              'rgba(139, 92, 246, 0.8)',
              'rgba(236, 72, 153, 0.8)',
              'rgba(6, 182, 212, 0.8)'
            ],
            borderColor: [
              '#6366f1',
              '#8b5cf6',
              '#ec4899',
              '#06b6d4'
            ],
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: { color: '#94a3b8', padding: 15 }
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleColor: '#ffffff',
              bodyColor: '#94a3b8'
            }
          }
        }
      });
    }

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart');
    if (userGrowthCtx) {
      new Chart(userGrowthCtx, {
        type: 'bar',
        data: {
          labels: @json($userGrowthLabels),
          datasets: [{
            label: 'New Users',
            data: @json($userGrowthData),
            backgroundColor: 'rgba(139, 92, 246, 0.8)',
            borderColor: '#8b5cf6',
            borderWidth: 2,
            borderRadius: 8
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              labels: { color: '#94a3b8' }
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleColor: '#ffffff',
              bodyColor: '#94a3b8'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { color: '#94a3b8' },
              grid: { color: 'rgba(255,255,255,0.05)' }
            },
            x: {
              ticks: { color: '#94a3b8' },
              grid: { color: 'rgba(255,255,255,0.05)' }
            }
          }
        }
      });
    }

    // Job Status Distribution Chart
    const jobStatusCtx = document.getElementById('jobStatusChart');
    if (jobStatusCtx) {
      new Chart(jobStatusCtx, {
        type: 'pie',
        data: {
          labels: ['Completed', 'In Progress', 'Pending', 'Cancelled'],
          datasets: [{
            data: [
              {{ $jobStatuses['completed'] ?? 0 }},
              {{ $jobStatuses['in_progress'] ?? 0 }},
              {{ $jobStatuses['pending'] ?? 0 }},
              {{ $jobStatuses['cancelled'] ?? 0 }}
            ],
            backgroundColor: [
              'rgba(16, 185, 129, 0.8)',
              'rgba(99, 102, 241, 0.8)',
              'rgba(245, 158, 11, 0.8)',
              'rgba(244, 63, 94, 0.8)'
            ],
            borderColor: [
              '#10b981',
              '#6366f1',
              '#f59e0b',
              '#f43f5e'
            ],
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: { color: '#94a3b8', padding: 15 }
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleColor: '#ffffff',
              bodyColor: '#94a3b8'
            }
          }
        }
      });
    }

    // Add some interactive animations
    // Animate metric cards on scroll
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

    // Observe all metric cards
    document.querySelectorAll('.metric-card').forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'all 0.6s ease';
      observer.observe(card);
    });

    // Observe performer items
    document.querySelectorAll('.performer-item').forEach(item => {
      item.style.opacity = '0';
      item.style.transform = 'translateY(20px)';
      item.style.transition = 'all 0.6s ease';
      observer.observe(item);
    });

    // Add hover effects
    document.querySelectorAll('.metric-card').forEach(card => {
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
