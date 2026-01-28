@extends('layouts.app')
@section('title', 'Mazungumzo Yangu')

@section('content')
<style>
  /* ============================================
   * TENDAPOA - AMAZING CHAT LIST PAGE
   * Premium Dark Glass Design
   * ============================================ */

  :root {
    --chat-primary: #6366f1;
    --chat-primary-glow: rgba(99, 102, 241, 0.4);
    --chat-success: #10b981;
    --chat-success-glow: rgba(16, 185, 129, 0.3);
    --chat-warning: #f59e0b;
    --chat-danger: #ef4444;
    --chat-dark: #0f172a;
    --chat-glass: rgba(15, 23, 42, 0.85);
    --chat-glass-light: rgba(255, 255, 255, 0.08);
    --chat-glass-border: rgba(255, 255, 255, 0.12);
    --chat-text: #e2e8f0;
    --chat-text-muted: #94a3b8;
    --chat-text-dim: #64748b;
    --chat-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .chat-list-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
    display: flex;
    position: relative;
    overflow-x: hidden;
  }

  .chat-list-page::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
      radial-gradient(ellipse 80% 80% at 50% -20%, rgba(99, 102, 241, 0.12), transparent),
      radial-gradient(ellipse 60% 60% at 80% 100%, rgba(120, 75, 162, 0.08), transparent);
    pointer-events: none;
    z-index: 0;
  }

  .main-content {
    flex: 1;
    margin-left: 280px;
    transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 24px;
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
    }
  }

  .chat-list-container {
    max-width: 900px;
    margin: 0 auto;
  }

  /* Page Header */
  .page-header {
    background: var(--chat-glass);
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    border-radius: 24px;
    padding: 36px;
    margin-bottom: 28px;
    border: 1px solid var(--chat-glass-border);
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
    background: var(--chat-gradient);
  }

  .page-header::after {
    content: '💬';
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 4rem;
    opacity: 0.08;
  }

  .page-header h1 {
    font-size: 2.5rem;
    font-weight: 900;
    background: var(--chat-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .page-header p {
    color: var(--chat-text-muted);
    font-size: 1.1rem;
    margin: 0;
  }

  /* Stats Row */
  .chat-stats {
    display: flex;
    gap: 16px;
    margin-top: 24px;
    flex-wrap: wrap;
  }

  .chat-stat {
    background: var(--chat-glass-light);
    padding: 14px 20px;
    border-radius: 14px;
    border: 1px solid var(--chat-glass-border);
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .chat-stat-icon {
    font-size: 1.5rem;
  }

  .chat-stat-value {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--chat-text);
  }

  .chat-stat-label {
    font-size: 0.75rem;
    color: var(--chat-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  /* Conversations List */
  .conversations-list {
    display: grid;
    gap: 20px;
  }

  .conversation-card {
    background: var(--chat-glass);
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    border-radius: 20px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    text-decoration: none;
    color: inherit;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid var(--chat-glass-border);
    position: relative;
    overflow: hidden;
  }

  .conversation-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;
    background: var(--chat-gradient);
    transform: scaleY(0);
    transition: transform 0.3s ease;
  }

  .conversation-card:hover {
    transform: translateY(-6px) scale(1.01);
    border-color: var(--chat-primary);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 30px var(--chat-primary-glow);
  }

  .conversation-card:hover::before {
    transform: scaleY(1);
  }

  .conversation-card.has-unread {
    border-color: rgba(239, 68, 68, 0.5);
  }

  .conversation-card.has-unread::before {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    transform: scaleY(1);
  }

  /* Avatar */
  .conversation-avatar {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    background: var(--chat-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 900;
    font-size: 1.75rem;
    box-shadow: 0 8px 25px var(--chat-primary-glow);
    flex-shrink: 0;
    position: relative;
  }

  .conversation-avatar.worker {
    background: linear-gradient(135deg, #10b981, #059669);
    box-shadow: 0 8px 25px var(--chat-success-glow);
  }

  .online-indicator {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 20px;
    height: 20px;
    background: #10b981;
    border: 4px solid var(--chat-dark);
    border-radius: 50%;
  }

  /* Info */
  .conversation-info {
    flex: 1;
    min-width: 0;
  }

  .conversation-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
    flex-wrap: wrap;
  }

  .conversation-name {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--chat-text);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .unread-badge {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 800;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
  }

  .new-conversation-badge {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .conversation-job-title {
    color: var(--chat-text-muted);
    font-size: 1rem;
    margin: 0 0 10px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .conversation-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 0.85rem;
    color: var(--chat-text-dim);
    flex-wrap: wrap;
  }

  .conversation-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  /* Side Info */
  .conversation-side {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
    flex-shrink: 0;
  }

  .category-badge {
    padding: 8px 14px;
    border-radius: 12px;
    background: var(--chat-glass-light);
    color: var(--chat-text-muted);
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    border: 1px solid var(--chat-glass-border);
  }

  .status-badge {
    padding: 8px 14px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }

  .status-assigned {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.1));
    color: #fcd34d;
    border: 1px solid rgba(245, 158, 11, 0.3);
  }

  .status-in_progress {
    background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(168, 85, 247, 0.1));
    color: #c4b5fd;
    border: 1px solid rgba(168, 85, 247, 0.3);
  }

  .status-completed {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.1));
    color: #6ee7b7;
    border: 1px solid rgba(16, 185, 129, 0.3);
  }

  .status-pending_payment {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.3);
  }

  .status-posted {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.1));
    color: #93c5fd;
    border: 1px solid rgba(59, 130, 246, 0.3);
  }

  .time-ago {
    font-size: 0.8rem;
    color: var(--chat-text-dim);
  }

  /* Arrow */
  .conversation-arrow {
    font-size: 1.5rem;
    color: var(--chat-text-dim);
    transition: all 0.3s ease;
  }

  .conversation-card:hover .conversation-arrow {
    color: var(--chat-primary);
    transform: translateX(6px);
  }

  /* Empty State */
  .empty-conversations {
    background: var(--chat-glass);
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    border-radius: 24px;
    padding: 80px 40px;
    text-align: center;
    border: 1px solid var(--chat-glass-border);
  }

  .empty-icon {
    font-size: 6rem;
    margin-bottom: 24px;
    opacity: 0.4;
  }

  .empty-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--chat-text);
    margin: 0 0 16px 0;
  }

  .empty-text {
    color: var(--chat-text-muted);
    font-size: 1.1rem;
    margin: 0 0 32px 0;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
  }

  .btn-feed {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 16px 32px;
    background: var(--chat-gradient);
    color: white;
    text-decoration: none;
    border-radius: 14px;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px var(--chat-primary-glow);
  }

  .btn-feed:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px var(--chat-primary-glow);
  }

  /* Responsive */
  @media (max-width: 768px) {
    .main-content {
      padding: 16px;
    }

    .page-header {
      padding: 24px;
    }

    .page-header h1 {
      font-size: 1.75rem;
    }

    .chat-stats {
      flex-direction: column;
    }

    .conversation-card {
      padding: 20px;
      gap: 14px;
    }

    .conversation-avatar {
      width: 56px;
      height: 56px;
      font-size: 1.3rem;
      border-radius: 14px;
    }

    .conversation-name {
      font-size: 1.1rem;
    }

    .conversation-side {
      display: none;
    }

    .conversation-arrow {
      display: none;
    }
  }
</style>

<div class="chat-list-page">
  @include('components.user-sidebar')
  
  <main class="main-content">
    <div class="chat-list-container">
    
    <!-- Page Header -->
    <div class="page-header">
      <h1>💬 Mazungumzo Yangu</h1>
      <p>Angalia mazungumzo yako yote na {{ auth()->user()->role === 'mfanyakazi' ? 'wahitaji' : 'wafanyakazi' }}</p>
      
      <div class="chat-stats">
        <div class="chat-stat">
          <span class="chat-stat-icon">💬</span>
          <div>
            <div class="chat-stat-value">{{ $conversations->count() }}</div>
            <div class="chat-stat-label">Mazungumzo</div>
          </div>
        </div>
        <div class="chat-stat">
          <span class="chat-stat-icon">🔴</span>
          <div>
            <div class="chat-stat-value">{{ $conversations->sum('unread_count') }}</div>
            <div class="chat-stat-label">Haujasoma</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Conversations List -->
    @if($conversations->isEmpty())
      <div class="empty-conversations">
        <div class="empty-icon">💬</div>
        <h3 class="empty-title">Hakuna Mazungumzo</h3>
        <p class="empty-text">
          @if(auth()->user()->role === 'mfanyakazi')
            Bado hujachaguliwa kwa kazi yoyote. Nenda kwenye Feed kupata kazi na kuomba!
          @else
            Bado hujachagua mfanyakazi yeyote. Chapisha kazi na uchague mfanyakazi!
          @endif
        </p>
        <a href="{{ route('feed') }}" class="btn-feed">
          🔍 Tazama Kazi
          <span>→</span>
        </a>
      </div>
    @else
      <div class="conversations-list">
        @foreach($conversations as $conv)
          @if($conv && $conv->job && $conv->other_user)
            @php
              $isWorker = $conv->other_user->role === 'mfanyakazi';
              $workerIdParam = auth()->user()->role === 'muhitaji' ? '?worker_id=' . $conv->other_user->id : '';
            @endphp
            <a href="{{ route('chat.show', $conv->job) }}{{ $workerIdParam }}" 
               class="conversation-card {{ $conv->unread_count > 0 ? 'has-unread' : '' }}">
              
              <!-- Avatar -->
              <div class="conversation-avatar {{ $isWorker ? 'worker' : '' }}">
                {{ substr($conv->other_user->name, 0, 1) }}
                @if($conv->unread_count > 0)
                  <div class="online-indicator" style="background: #ef4444;"></div>
                @endif
              </div>

              <!-- Info -->
              <div class="conversation-info">
                <div class="conversation-header">
                  <h3 class="conversation-name">{{ $conv->other_user->name }}</h3>
                  @if($conv->unread_count > 0)
                    <span class="unread-badge">{{ $conv->unread_count }} mpya</span>
                  @endif
                  @if($conv->job->status === 'assigned')
                    <span class="new-conversation-badge">🎉 Mpya</span>
                  @endif
                </div>
                <p class="conversation-job-title">📋 {{ $conv->job->title }}</p>
                <div class="conversation-meta">
                  <span class="conversation-meta-item">
                    📦 {{ $conv->job->category->name ?? 'N/A' }}
                  </span>
                  <span class="conversation-meta-item">
                    💰 {{ number_format($conv->job->price) }} TZS
                  </span>
                  <span class="conversation-meta-item">
                    ⏰ {{ \Carbon\Carbon::parse($conv->last_message_at)->diffForHumans() }}
                  </span>
                </div>
              </div>

              <!-- Side Info -->
              <div class="conversation-side">
                <span class="category-badge">{{ $isWorker ? '👷 Mfanyakazi' : '👤 Muhitaji' }}</span>
                <span class="status-badge status-{{ $conv->job->status }}">
                  @switch($conv->job->status)
                    @case('assigned') 📋 Imekabidhiwa @break
                    @case('in_progress') ⚡ Inaendelea @break
                    @case('completed') ✅ Imekamilika @break
                    @case('pending_payment') ⏳ Malipo @break
                    @default {{ ucfirst($conv->job->status) }}
                  @endswitch
                </span>
                <span class="time-ago">{{ \Carbon\Carbon::parse($conv->last_message_at)->format('d M') }}</span>
              </div>

              <!-- Arrow -->
              <div class="conversation-arrow">→</div>

            </a>
          @endif
        @endforeach
      </div>
    @endif

  </div>
</main>
</div>

<script>
  // Add smooth animations on scroll
  document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.conversation-card');
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
          setTimeout(() => {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }, index * 80);
        }
      });
    }, { threshold: 0.1 });

    cards.forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(30px)';
      card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
      observer.observe(card);
    });
  });
</script>
@endsection
