@extends('layouts.admin')
@section('title', 'Admin - Monitor All Chats')

@section('content')
<style>
  /* ====== Modern Admin Chats Page - Colorful Dark Theme ====== */
  .page-container {
    --primary: #6366f1;
    --secondary: #06b6d4;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #f43f5e;
    --purple: #8b5cf6;
    --pink: #ec4899;
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
    position: relative;
    z-index: 1;
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

  /* Stats Grid */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }

  .stat-card {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 20px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }
  
  .stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899);
  }
  
  .stat-card:hover {
    background: var(--card-bg-hover);
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
  }

  .stat-icon {
    font-size: 2rem;
    margin-bottom: 8px;
  }

  .stat-value {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 4px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .stat-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  /* Conversations List */
  .conversations-section {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }

  .conversations-list {
    display: grid;
    gap: 16px;
  }

  .conversation-card {
    background: rgba(255,255,255,0.03);
    border-radius: 16px;
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    cursor: pointer;
  }
  
  .conversation-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #6366f1, #8b5cf6);
    transition: width 0.3s ease;
  }

  .conversation-card:hover {
    background: rgba(255,255,255,0.08);
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: rgba(99, 102, 241, 0.4);
  }
  
  .conversation-card:hover::before {
    width: 6px;
  }

  .conversation-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 16px;
    gap: 16px;
  }

  .conversation-info {
    flex: 1;
  }

  .conversation-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .conversation-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: center;
    color: var(--text-muted);
    font-size: 0.875rem;
    margin-bottom: 12px;
  }

  .meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .user-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
  }

  .user-badge.muhitaji {
    background: rgba(99, 102, 241, 0.2);
    color: #818cf8;
    border: 1px solid rgba(99, 102, 241, 0.4);
  }

  .user-badge.mfanyakazi {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.4);
  }

  .message-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(139, 92, 246, 0.2);
    color: #a78bfa;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid rgba(139, 92, 246, 0.4);
  }

  .conversation-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
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
    background: linear-gradient(135deg, var(--primary), var(--purple));
    color: white;
    box-shadow: 0 4px 14px 0 rgba(99, 102, 241, 0.4);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px 0 rgba(99, 102, 241, 0.6);
  }

  .btn-secondary {
    background: rgba(255,255,255,0.1);
    color: var(--text-primary);
    border: 1px solid var(--border);
  }

  .btn-secondary:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-2px);
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

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 80px 20px;
    background: rgba(255,255,255,0.02);
    border-radius: 16px;
    border: 2px dashed var(--border);
  }

  .empty-state-icon {
    font-size: 4rem;
    margin-bottom: 16px;
    opacity: 0.6;
  }

  .empty-state h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 8px 0;
  }

  .empty-state p {
    color: var(--text-muted);
    font-size: 1rem;
    margin: 0;
  }

  /* Live Indicator */
  .live-indicator {
    width: 8px;
    height: 8px;
    background: var(--success);
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
  }

  /* Responsive */
  @media (max-width: 768px) {
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
    
    .conversation-header {
      flex-direction: column;
    }
    
    .conversation-actions {
      width: 100%;
    }
    
    .btn {
      flex: 1;
      justify-content: center;
    }
  }
</style>

<div class="page-container">
  <!-- Page Header -->
  <div class="page-header">
    <div class="header-content">
      <div class="header-text">
        <h1>💬 Conversation Monitor</h1>
        <p>Monitor and manage all private conversations between users</p>
      </div>
      <div class="header-actions">
        <a class="btn btn-outline" href="{{ route('admin.dashboard') }}">
          <span>↩️</span>
          Back to Dashboard
        </a>
      </div>
    </div>
  </div>

  <!-- Stats Overview -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">💬</div>
      <div class="stat-value">{{ $conversations->total() }}</div>
      <div class="stat-label">Total Conversations</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📨</div>
      <div class="stat-value">{{ $conversations->sum('message_count') }}</div>
      <div class="stat-label">Total Messages</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">⚡</div>
      <div class="stat-value">{{ $conversations->where('last_message_at', '>=', now()->subHours(24))->count() }}</div>
      <div class="stat-label">Active Today</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">👥</div>
      <div class="stat-value">{{ $conversations->unique('job_id')->count() }}</div>
      <div class="stat-label">Active Jobs</div>
    </div>
  </div>

  <!-- Conversations List -->
  <div class="conversations-section">
    @if($conversations->isEmpty())
      <div class="empty-state">
        <div class="empty-state-icon">💬</div>
        <h3>No conversations yet</h3>
        <p>Conversations will appear here once users start chatting</p>
      </div>
    @else
      <div class="conversations-list">
        @foreach($conversations as $conv)
          @if($conv->job)
            <div class="conversation-card" onclick="window.location='{{ route('admin.chat.view', $conv->job) }}'">
              <div class="conversation-header">
                <div class="conversation-info">
                  <h3 class="conversation-title">
                    <span>📋</span>
                    {{ $conv->job->title }}
                  </h3>
                  
                  <div class="conversation-meta">
                    <div class="meta-item">
                      <span class="user-badge muhitaji">👤 {{ $conv->job->muhitaji->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="meta-item">
                      <span class="user-badge mfanyakazi">👷 {{ $conv->job->acceptedWorker->name ?? 'Not assigned' }}</span>
                    </div>
                    <div class="meta-item">
                      <span class="message-count">
                        <span>💬</span>
                        {{ $conv->message_count }} messages
                      </span>
                    </div>
                    <div class="meta-item">
                      <span class="live-indicator"></span>
                      Last: {{ \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans() }}
                    </div>
                  </div>
                </div>
                
                <div class="conversation-actions" onclick="event.stopPropagation()">
                  <a href="{{ route('admin.chat.view', $conv->job) }}" class="btn btn-primary">
                    <span>👁️</span>
                    View Chat
                  </a>
                  <a href="{{ route('admin.job.details', $conv->job) }}" class="btn btn-secondary">
                    <span>📋</span>
                    View Job
                  </a>
                </div>
              </div>
            </div>
          @endif
        @endforeach
      </div>

      <!-- Pagination -->
      <div style="display: flex; justify-content: center; margin-top: 32px;">
        {{ $conversations->links() }}
      </div>
    @endif
  </div>
</div>

<script>
  // Add interactive animations
  document.addEventListener('DOMContentLoaded', function() {
    // Animate conversation cards on scroll
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

    // Observe all conversation cards
    document.querySelectorAll('.conversation-card').forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'all 0.6s ease';
      observer.observe(card);
    });

    // Add hover effects
    document.querySelectorAll('.conversation-card').forEach(card => {
      card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-4px) scale(1.01)';
      });
      
      card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
      });
    });
  });
</script>
@endsection
