@extends('layouts.app')
@section('title', 'Kazi Zangu')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<style>
  /* ====== Ultra Modern My Jobs Page ====== */
  .my-jobs-page {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --success: #10b981;
    --success-dark: #059669;
    --warning: #f59e0b;
    --danger: #ef4444;
    --dark: #0f172a;
    --light: #f8fafc;
    --border: #e2e8f0;
    --text: #334155;
    --text-muted: #64748b;
    --glass: rgba(255, 255, 255, 0.85);
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  }

  .my-jobs-page {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #6366f1 100%);
    min-height: 100vh;
    display: flex;
    position: relative;
  }

  .my-jobs-page::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
  }

  .main-content {
    flex: 1;
    margin-left: 280px;
    transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 32px;
    min-height: 100vh;
    position: relative;
    z-index: 1;
  }

  .sidebar.collapsed ~ .main-content {
    margin-left: 80px;
  }

  @media (max-width: 1024px) {
    .main-content {
      margin-left: 0;
      padding: 20px;
    }
  }

  .page-container {
    max-width: 1400px;
    margin: 0 auto;
  }

  /* Header - Glassmorphism */
  .page-header {
    background: var(--glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 28px;
    padding: 40px;
    margin-bottom: 32px;
    box-shadow: var(--shadow-xl);
    border: 1px solid rgba(255,255,255,0.3);
    position: relative;
    overflow: hidden;
  }

  .page-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, transparent 60%);
    pointer-events: none;
  }

  .header-content {
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 24px;
  }

  .header-text {
    flex: 1;
    min-width: 280px;
  }

  .page-title {
    font-size: 2.75rem;
    font-weight: 900;
    color: transparent;
    margin: 0 0 12px 0;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
    -webkit-background-clip: text;
    background-clip: text;
    letter-spacing: -0.02em;
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .page-title-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
  }

  .page-subtitle {
    color: var(--text-muted);
    font-size: 1.15rem;
    margin: 0;
    line-height: 1.6;
  }

  .header-stats {
    display: flex;
    gap: 16px;
  }

  .stat-card {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
    border-radius: 16px;
    padding: 16px 24px;
    text-align: center;
    border: 1px solid rgba(99, 102, 241, 0.2);
  }

  .stat-value {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--primary);
    display: block;
  }

  .stat-label {
    font-size: 0.8rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
  }

  .header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }

  /* Job Cards Grid */
  .jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 28px;
  }

  @media (max-width: 480px) {
    .jobs-grid {
      grid-template-columns: 1fr;
    }
  }

  /* Individual Job Card */
  .job-card {
    background: var(--glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(255,255,255,0.3);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
  }

  .job-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: var(--shadow-xl);
  }

  .job-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
    pointer-events: none;
    z-index: 1;
  }

  /* Job Image */
  .job-image-wrapper {
    position: relative;
    width: 100%;
    height: 220px;
    overflow: hidden;
  }

  .job-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .job-card:hover .job-image {
    transform: scale(1.1);
  }

  .job-image-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.2) 40%, transparent 100%);
    pointer-events: none;
  }

  .job-image-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #94a3b8;
  }

  .job-image-placeholder-icon {
    font-size: 3.5rem;
    opacity: 0.6;
  }

  .job-image-placeholder-text {
    font-size: 0.85rem;
    font-weight: 600;
    opacity: 0.7;
  }

  /* Status Badge - Floating */
  .job-status-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    z-index: 2;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }

  .job-status-badge.posted {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.9), rgba(22, 163, 74, 0.9));
    color: white;
  }

  .job-status-badge.pending-payment {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.9), rgba(220, 38, 38, 0.9));
    color: white;
    animation: pulse 2s infinite;
  }

  .job-status-badge.assigned {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.9), rgba(217, 119, 6, 0.9));
    color: white;
  }

  .job-status-badge.in-progress {
    background: linear-gradient(135deg, rgba(236, 72, 153, 0.9), rgba(219, 39, 119, 0.9));
    color: white;
    animation: pulse 2s infinite;
  }

  .job-status-badge.completed {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9));
    color: white;
  }

  .job-status-badge.cancelled {
    background: linear-gradient(135deg, rgba(100, 116, 139, 0.9), rgba(71, 85, 105, 0.9));
    color: white;
  }

  @keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
    50% { box-shadow: 0 0 0 8px rgba(255,255,255,0); }
  }

  /* Price Badge - Floating */
  .job-price-badge {
    position: absolute;
    bottom: 16px;
    left: 16px;
    background: linear-gradient(135deg, rgba(0,0,0,0.85), rgba(0,0,0,0.7));
    color: white;
    padding: 10px 18px;
    border-radius: 14px;
    z-index: 2;
    backdrop-filter: blur(10px);
  }

  .job-price-badge .price-amount {
    font-size: 1.35rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #10b981;
  }

  .job-price-badge .price-currency {
    font-size: 0.75rem;
    font-weight: 600;
    color: rgba(255,255,255,0.7);
    margin-left: 4px;
  }

  /* Job Content */
  .job-content {
    padding: 24px;
    position: relative;
    z-index: 2;
  }

  .job-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--dark);
    margin: 0 0 16px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 20px;
  }

  .job-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: var(--text-muted);
  }

  .job-meta-item-icon {
    font-size: 1rem;
  }

  /* Job Description Preview */
  .job-description {
    font-size: 0.9rem;
    color: var(--text-muted);
    line-height: 1.6;
    margin-bottom: 20px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Action Buttons */
  .job-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    padding-top: 16px;
    border-top: 1px solid var(--border);
  }

  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
    flex: 1;
    min-width: 120px;
  }

  .btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.5);
  }

  .btn-success {
    background: linear-gradient(135deg, var(--success), var(--success-dark));
    color: white;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
  }

  .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.5);
  }

  .btn-outline {
    background: transparent;
    color: var(--primary);
    border: 2px solid var(--primary);
  }

  .btn-outline:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
  }

  .btn-danger-outline {
    background: transparent;
    color: var(--danger);
    border: 2px solid var(--danger);
  }

  .btn-danger-outline:hover {
    background: var(--danger);
    color: white;
    transform: translateY(-2px);
  }

  .btn-icon {
    font-size: 1rem;
  }

  /* Special Sections */
  .completion-section {
    margin: 0 24px 24px 24px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
    border: 2px solid rgba(99, 102, 241, 0.3);
    border-radius: 16px;
    padding: 20px;
    position: relative;
    overflow: hidden;
  }

  .completion-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6, #a855f7);
  }

  .completion-code-display {
    background: white;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    margin: 12px 0;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
  }

  .completion-code-display:hover {
    transform: scale(1.02);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
  }

  .completion-code-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--primary);
    letter-spacing: 6px;
    font-family: 'Monaco', 'Consolas', monospace;
  }

  /* Worker Info */
  .worker-info-section {
    margin: 0 24px 16px 24px;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
    border-left: 4px solid var(--success);
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .worker-avatar {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, var(--success), var(--success-dark));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
  }

  .worker-details h5 {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    margin: 0 0 4px 0;
  }

  .worker-details p {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--dark);
    margin: 0;
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 80px 40px;
    background: var(--glass);
    backdrop-filter: blur(20px);
    border-radius: 28px;
    box-shadow: var(--shadow-xl);
    border: 1px solid rgba(255,255,255,0.3);
  }

  .empty-state-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    margin: 0 auto 24px;
  }

  .empty-state h3 {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--dark);
    margin: 0 0 12px 0;
  }

  .empty-state p {
    color: var(--text-muted);
    font-size: 1.1rem;
    margin: 0 0 32px 0;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
  }

  /* Pagination */
  .pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 40px;
  }

  .pagination {
    display: flex;
    gap: 8px;
    background: var(--glass);
    backdrop-filter: blur(20px);
    padding: 12px;
    border-radius: 16px;
    box-shadow: var(--shadow);
  }

  .pagination .page-link {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    color: var(--text);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .pagination .page-link:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
  }

  .pagination .page-link.active {
    background: var(--primary);
    color: white;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .page-header {
      padding: 28px;
    }
    
    .page-title {
      font-size: 1.75rem;
    }

    .page-title-icon {
      width: 44px;
      height: 44px;
      font-size: 1.25rem;
    }
    
    .header-stats {
      display: none;
    }
    
    .jobs-grid {
      grid-template-columns: 1fr;
      gap: 20px;
    }

    .job-image-wrapper {
      height: 180px;
    }

    .job-content {
      padding: 20px;
    }

    .btn {
      padding: 10px 16px;
      font-size: 0.8rem;
    }
  }

  /* Animations */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .job-card {
    animation: fadeInUp 0.6s ease forwards;
  }

  .job-card:nth-child(1) { animation-delay: 0.1s; }
  .job-card:nth-child(2) { animation-delay: 0.2s; }
  .job-card:nth-child(3) { animation-delay: 0.3s; }
  .job-card:nth-child(4) { animation-delay: 0.4s; }
  .job-card:nth-child(5) { animation-delay: 0.5s; }
  .job-card:nth-child(6) { animation-delay: 0.6s; }
</style>

<div class="my-jobs-page">
  @include('components.user-sidebar')
  
  <main class="main-content">
    <div class="page-container">
    
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-text">
          <h1 class="page-title">
            <span class="page-title-icon">📋</span>
            Kazi Zangu
          </h1>
          <p class="page-subtitle">Fuatilia kazi zako, malipo, na mfanyakazi wako kwa urahisi.</p>
          
          <div class="header-actions" style="margin-top: 20px;">
            <a class="btn btn-primary" href="{{ route('jobs.create') }}">
              <span class="btn-icon">✨</span>
              Chapisha Kazi Mpya
            </a>
            <a class="btn btn-outline" href="{{ route('dashboard') }}">
              <span class="btn-icon">🏠</span>
              Dashboard
            </a>
          </div>
        </div>
        
        <div class="header-stats">
          <div class="stat-card">
            <span class="stat-value">{{ $jobs->count() }}</span>
            <span class="stat-label">Jumla</span>
          </div>
          <div class="stat-card">
            <span class="stat-value">{{ $jobs->where('status', 'posted')->count() }}</span>
            <span class="stat-label">Hai</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Jobs List -->
    @if($jobs->count() > 0)
      <div class="jobs-grid">
        @foreach($jobs as $job)
          <div class="job-card">
            <!-- Job Image -->
            <div class="job-image-wrapper">
              @if($job->image_url)
                <img src="{{ $job->image_url }}" alt="{{ $job->title }}" class="job-image" 
                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'job-image-placeholder\'><span class=\'job-image-placeholder-icon\'>📷</span><span class=\'job-image-placeholder-text\'>Hakuna Picha</span></div>';">
                <div class="job-image-overlay"></div>
              @else
                <div class="job-image-placeholder">
                  <span class="job-image-placeholder-icon">📷</span>
                  <span class="job-image-placeholder-text">Hakuna Picha</span>
                </div>
              @endif
              
              <!-- Status Badge -->
              <div class="job-status-badge {{ str_replace('_', '-', $job->status) }}">
                @switch($job->status)
                  @case('posted') 🟢 Imetangazwa @break
                  @case('pending_payment') ⏳ Inasubiri Malipo @break
                  @case('assigned') 👤 Imepewa @break
                  @case('in_progress') 🔄 Inaendelea @break
                  @case('completed') ✅ Imekamilika @break
                  @case('cancelled') ❌ Imefutwa @break
                  @default {{ $job->status }}
                @endswitch
              </div>
              
              <!-- Price Badge -->
              <div class="job-price-badge">
                <span class="price-amount">{{ number_format($job->price) }}</span>
                <span class="price-currency">TZS</span>
              </div>
            </div>

            <!-- Completion Code Section -->
            @if(in_array($job->status, ['assigned', 'in_progress']) && $job->completion_code)
              <div class="completion-section">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                  <span style="font-size: 1.5rem;">🔐</span>
                  <strong style="color: var(--primary);">Code ya Ukamilishaji</strong>
                </div>
                <div class="completion-code-display" onclick="copyToClipboard('{{ $job->completion_code }}')">
                  <span class="completion-code-value">{{ $job->completion_code }}</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin: 8px 0 0 0;">
                  📋 Bofya kunakili. Mpe mfanyakazi TU baada ya kazi kukamilika!
                </p>
              </div>
            @endif

            <!-- Worker Info -->
            @if($job->status === 'in_progress' && $job->acceptedWorker)
              <div class="worker-info-section">
                <div class="worker-avatar">👷</div>
                <div class="worker-details">
                  <h5>Mfanyakazi</h5>
                  <p>{{ $job->acceptedWorker->name ?? 'Haijulikani' }}</p>
                </div>
              </div>
            @endif

            <!-- Job Content -->
            <div class="job-content">
              <h3 class="job-title">{{ $job->title }}</h3>
              
              <div class="job-meta">
                <div class="job-meta-item">
                  <span class="job-meta-item-icon">💬</span>
                  <span>{{ $job->comments_count ?? 0 }} maoni</span>
                </div>
                <div class="job-meta-item">
                  <span class="job-meta-item-icon">📅</span>
                  <span>{{ $job->created_at->format('M d, Y') }}</span>
                </div>
                @if($job->category)
                <div class="job-meta-item">
                  <span class="job-meta-item-icon">🏷️</span>
                  <span>{{ $job->category->name ?? 'General' }}</span>
                </div>
                @endif
              </div>

              @if($job->description)
              <p class="job-description">{{ $job->description }}</p>
              @endif

              <!-- Action Buttons -->
              <div class="job-actions">
                @if($job->status === 'pending_payment')
                  <a class="btn btn-success" href="{{ route('jobs.pay.wait', $job) }}">
                    <span class="btn-icon">💳</span>
                    Lipa
                  </a>
                @endif
                
                @if($job->status === 'pending_payment' || ($job->status === 'posted' && !$job->accepted_worker_id))
                  <form action="{{ route('jobs.cancel', $job) }}" method="POST" onsubmit="return confirm('Je, una uhakika unataka kufuta kazi hii?');" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn btn-danger-outline" style="width: 100%;">
                      <span class="btn-icon">🗑️</span>
                      Futa
                    </button>
                  </form>
                @endif
                
                @if(in_array($job->status, ['posted', 'assigned']))
                  <a class="btn btn-outline" href="{{ route('jobs.edit', $job) }}">
                    <span class="btn-icon">✏️</span>
                    Hariri
                  </a>
                @endif
                
                <a class="btn btn-primary" href="{{ route('jobs.show', $job) }}">
                  <span class="btn-icon">👁️</span>
                  Angalia
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <!-- Pagination -->
      <div class="pagination-wrapper">
        {{ $jobs->links() }}
      </div>
    @else
      <!-- Empty State -->
      <div class="empty-state">
        <div class="empty-state-icon">📝</div>
        <h3>Hakuna Kazi Bado</h3>
        <p>Hujachapisha kazi yoyote. Chapisha kazi yako ya kwanza na uanze kupata mfanyakazi wa kuaminika!</p>
        <a class="btn btn-primary" href="{{ route('jobs.create') }}" style="display: inline-flex;">
          <span class="btn-icon">🚀</span>
          Chapisha Kazi Sasa
        </a>
      </div>
    @endif

  </div>
</div>

<script>
  // Copy to clipboard function
  function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
      showNotification('Code imenakiliwa!', 'success');
    }, function(err) {
      console.error('Could not copy text: ', err);
      showNotification('Imeshindwa kunakili code', 'error');
    });
  }

  // Show notification
  function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
      position: fixed;
      top: 24px;
      right: 24px;
      background: ${type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)'};
      color: white;
      padding: 16px 28px;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      z-index: 9999;
      font-weight: 600;
      transform: translateX(120%);
      transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      align-items: center;
      gap: 10px;
    `;
    notification.innerHTML = `<span>${type === 'success' ? '✅' : '❌'}</span> ${message}`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.style.transform = 'translateX(0)', 100);
    
    setTimeout(() => {
      notification.style.transform = 'translateX(120%)';
      setTimeout(() => document.body.removeChild(notification), 400);
    }, 3000);
  }

  // Add intersection observer for scroll animations
  document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.job-card');
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, { threshold: 0.1 });

    cards.forEach((card, index) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(30px)';
      card.style.transition = `all 0.6s cubic-bezier(0.4, 0, 0.2, 1) ${index * 0.1}s`;
      observer.observe(card);
    });
  });
</script>
@endsection