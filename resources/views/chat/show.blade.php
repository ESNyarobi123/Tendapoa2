@extends('layouts.app')
@section('title', 'Chat - ' . $job->title)

@section('content')
<style>
  /* ============================================
   * TENDAPOA - AMAZING CHAT PAGE
   * Premium Dark Glass Design
   * ============================================ */

  :root {
    --chat-primary: #6366f1;
    --chat-primary-glow: rgba(99, 102, 241, 0.4);
    --chat-success: #10b981;
    --chat-success-glow: rgba(16, 185, 129, 0.3);
    --chat-dark: #0f172a;
    --chat-glass: rgba(15, 23, 42, 0.9);
    --chat-glass-light: rgba(255, 255, 255, 0.08);
    --chat-glass-border: rgba(255, 255, 255, 0.12);
    --chat-text: #e2e8f0;
    --chat-text-muted: #94a3b8;
    --chat-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  .amazing-chat {
    min-height: 100vh;
    background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
  }

  .amazing-chat::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
      radial-gradient(ellipse 80% 80% at 50% -20%, rgba(99, 102, 241, 0.1), transparent),
      radial-gradient(ellipse 60% 60% at 80% 100%, rgba(120, 75, 162, 0.08), transparent);
    pointer-events: none;
    z-index: 0;
  }

  .chat-container {
    max-width: 1000px;
    margin: 0 auto;
    width: 100%;
    height: 100vh;
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 1;
    padding: 20px 20px 0;
  }

  /* Chat Header */
  .chat-header {
    background: var(--chat-glass);
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    border-radius: 24px 24px 0 0;
    padding: 20px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid var(--chat-glass-border);
    border-bottom: none;
    flex-shrink: 0;
  }

  .chat-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .back-btn {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: var(--chat-glass-light);
    border: 1px solid var(--chat-glass-border);
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: var(--chat-text);
    transition: all 0.3s ease;
  }

  .back-btn:hover {
    background: var(--chat-primary);
    border-color: var(--chat-primary);
    transform: translateX(-3px);
    box-shadow: 0 4px 15px var(--chat-primary-glow);
  }

  .user-avatar-large {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: var(--chat-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 800;
    font-size: 1.5rem;
    box-shadow: 0 8px 20px var(--chat-primary-glow);
    position: relative;
  }

  .online-dot {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 16px;
    height: 16px;
    background: #10b981;
    border: 3px solid var(--chat-dark);
    border-radius: 50%;
  }

  .chat-header-info h2 {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--chat-text);
    margin: 0 0 4px 0;
  }

  .chat-header-info p {
    font-size: 0.875rem;
    color: var(--chat-text-muted);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .role-badge {
    padding: 3px 10px;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
  }

  .role-badge.worker {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.1));
    color: #6ee7b7;
    border: 1px solid rgba(16, 185, 129, 0.3);
  }

  .role-badge.client {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(99, 102, 241, 0.1));
    color: #a5b4fc;
    border: 1px solid rgba(99, 102, 241, 0.3);
  }

  .view-job-btn {
    padding: 12px 24px;
    background: var(--chat-gradient);
    color: white;
    border-radius: 14px;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px var(--chat-primary-glow);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .view-job-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px var(--chat-primary-glow);
  }

  /* Job Info Banner */
  .job-banner {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
    border-left: 1px solid var(--chat-glass-border);
    border-right: 1px solid var(--chat-glass-border);
    padding: 16px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
  }

  .job-banner-info {
    flex: 1;
  }

  .job-banner-info h3 {
    font-weight: 700;
    color: var(--chat-text);
    margin: 0 0 4px 0;
    font-size: 1rem;
  }

  .job-banner-info p {
    color: #10b981;
    font-size: 1.1rem;
    font-weight: 800;
    margin: 0;
  }

  .status-badge {
    padding: 8px 16px;
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

  .status-posted {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.1));
    color: #93c5fd;
    border: 1px solid rgba(59, 130, 246, 0.3);
  }

  /* Messages Container */
  .messages-box {
    background: var(--chat-glass);
    flex: 1;
    overflow-y: auto;
    padding: 28px;
    border-left: 1px solid var(--chat-glass-border);
    border-right: 1px solid var(--chat-glass-border);
    display: flex;
    flex-direction: column;
  }

  .messages-box::-webkit-scrollbar {
    width: 6px;
  }

  .messages-box::-webkit-scrollbar-track {
    background: transparent;
  }

  .messages-box::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
  }

  .messages-box::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.3);
  }

  .messages-inner {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    min-height: 100%;
  }

  /* Empty State */
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1;
    text-align: center;
    padding: 40px;
  }

  .empty-state-icon {
    font-size: 5rem;
    margin-bottom: 20px;
    opacity: 0.4;
  }

  .empty-state h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--chat-text);
    margin: 0 0 10px 0;
  }

  .empty-state p {
    color: var(--chat-text-muted);
    margin: 0;
    font-size: 0.95rem;
  }

  /* Date Divider */
  .date-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 24px 0;
  }

  .date-divider span {
    background: var(--chat-glass-light);
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.75rem;
    color: var(--chat-text-muted);
    font-weight: 600;
  }

  /* Message Bubble */
  .message-wrapper {
    display: flex;
    margin-bottom: 16px;
    animation: slideIn 0.3s ease;
  }

  @keyframes slideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .message-wrapper.sent {
    justify-content: flex-end;
  }

  .message-wrapper.received {
    justify-content: flex-start;
  }

  .message-content {
    max-width: 70%;
  }

  .message-bubble-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 12px;
  }

  .sent .message-bubble-wrapper {
    flex-direction: row-reverse;
  }

  .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 800;
    font-size: 0.95rem;
    flex-shrink: 0;
  }

  .sent .user-avatar {
    background: var(--chat-gradient);
    box-shadow: 0 4px 15px var(--chat-primary-glow);
  }

  .received .user-avatar {
    background: linear-gradient(135deg, #475569, #334155);
    box-shadow: 0 4px 12px rgba(71, 85, 105, 0.4);
  }

  .message-bubble {
    border-radius: 20px;
    padding: 14px 18px;
    word-wrap: break-word;
    position: relative;
  }

  .sent .message-bubble {
    background: var(--chat-gradient);
    color: white;
    border-bottom-right-radius: 6px;
    box-shadow: 0 4px 15px var(--chat-primary-glow);
  }

  .received .message-bubble {
    background: var(--chat-glass-light);
    color: var(--chat-text);
    border: 1px solid var(--chat-glass-border);
    border-bottom-left-radius: 6px;
  }

  .message-text {
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
  }

  .message-meta {
    font-size: 0.7rem;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .sent .message-meta {
    color: rgba(255,255,255,0.7);
    justify-content: flex-end;
  }

  .received .message-meta {
    color: var(--chat-text-dim);
  }

  .read-status {
    color: #10b981;
    font-weight: 700;
  }

  /* System Message */
  .system-message {
    display: flex;
    justify-content: center;
    margin: 20px 0;
  }

  .system-message-content {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 16px;
    padding: 14px 24px;
    max-width: 80%;
    text-align: center;
  }

  .system-message-content p {
    color: #6ee7b7;
    font-size: 0.9rem;
    margin: 0;
    line-height: 1.5;
  }

  /* Message Input */
  .message-input-box {
    background: var(--chat-glass);
    backdrop-filter: blur(30px);
    border-radius: 0 0 24px 24px;
    padding: 20px 28px;
    border: 1px solid var(--chat-glass-border);
    border-top: none;
    flex-shrink: 0;
    margin-bottom: 20px;
  }

  .input-form {
    display: flex;
    gap: 14px;
    align-items: flex-end;
  }

  .input-wrapper {
    flex: 1;
    position: relative;
  }

  .input-textarea {
    width: 100%;
    padding: 16px 20px;
    border: 2px solid var(--chat-glass-border);
    border-radius: 16px;
    font-size: 0.95rem;
    resize: none;
    transition: all 0.3s ease;
    font-family: inherit;
    background: var(--chat-glass-light);
    color: var(--chat-text);
  }

  .input-textarea::placeholder {
    color: var(--chat-text-muted);
  }

  .input-textarea:focus {
    outline: none;
    border-color: var(--chat-primary);
    box-shadow: 0 0 0 3px var(--chat-primary-glow);
    background: var(--chat-glass);
  }

  .send-btn {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px var(--chat-success-glow);
    flex-shrink: 0;
  }

  .send-btn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 30px var(--chat-success-glow);
  }

  .send-btn:active {
    transform: scale(0.95);
  }

  .send-btn svg {
    width: 26px;
    height: 26px;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .chat-container {
      padding: 0;
      height: 100vh;
    }

    .chat-header {
      border-radius: 0;
      padding: 16px 20px;
    }

    .user-avatar-large {
      width: 46px;
      height: 46px;
      font-size: 1.25rem;
      border-radius: 14px;
    }

    .chat-header-info h2 {
      font-size: 1.15rem;
    }

    .view-job-btn {
      padding: 10px 16px;
      font-size: 0.8rem;
    }

    .view-job-btn span {
      display: none;
    }

    .job-banner {
      padding: 12px 20px;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
    }

    .status-badge {
      align-self: flex-end;
    }

    .messages-box {
      padding: 16px;
    }

    .message-content {
      max-width: 85%;
    }

    .message-input-box {
      border-radius: 0;
      padding: 16px 20px;
      margin-bottom: 0;
    }

    .send-btn {
      width: 50px;
      height: 50px;
    }
  }
</style>

<div class="amazing-chat">
  <div class="chat-container">
    
    <!-- Chat Header -->
    <div class="chat-header">
      <div class="chat-header-left">
        <a href="{{ route('chat.index') }}" class="back-btn">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </a>
        
        <div class="user-avatar-large">
          {{ substr($otherUser->name, 0, 1) }}
          <div class="online-dot"></div>
        </div>
        
        <div class="chat-header-info">
          <h2>{{ $otherUser->name }}</h2>
          <p>
            <span class="role-badge {{ $otherUser->role === 'mfanyakazi' ? 'worker' : 'client' }}">
              {{ $otherUser->role === 'mfanyakazi' ? '👷 Mfanyakazi' : '👤 Muhitaji' }}
            </span>
            {{ $job->category->name ?? '' }}
          </p>
        </div>
      </div>
      
      <a href="{{ route('jobs.show', $job) }}" class="view-job-btn">
        <span>📋</span>
        Angalia Kazi
      </a>
    </div>

    <!-- Job Info Banner -->
    <div class="job-banner">
      <div class="job-banner-info">
        <h3>📋 {{ $job->title }}</h3>
        <p>💰 {{ number_format($job->price) }} TZS</p>
      </div>
      <span class="status-badge status-{{ $job->status }}">
        @switch($job->status)
          @case('assigned') 📋 Imekabidhiwa @break
          @case('in_progress') ⚡ Inaendelea @break
          @case('completed') ✅ Imekamilika @break
          @case('pending_payment') ⏳ Malipo @break
          @default {{ ucfirst($job->status) }}
        @endswitch
      </span>
    </div>

    <!-- Messages Container -->
    <div class="messages-box" id="messages-container">
      <div class="messages-inner">
        @if($messages->isEmpty())
          <div class="empty-state">
            <div class="empty-state-icon">🎉</div>
            <h3>Mazungumzo Mapya!</h3>
            <p>Anza mazungumzo yako na {{ $otherUser->name }}. Tuma ujumbe wa kwanza!</p>
          </div>
        @else
          @foreach($messages as $message)
            @php
              $isSystemMessage = str_contains($message->message, '🎉 Hongera!') || str_contains($message->message, 'Umechaguliwa kufanya kazi');
            @endphp
            
            @if($isSystemMessage)
              <div class="system-message">
                <div class="system-message-content">
                  <p>{{ $message->message }}</p>
                </div>
              </div>
            @else
              <div class="message-wrapper {{ $message->sender_id === auth()->id() ? 'sent' : 'received' }}">
                <div class="message-content">
                  <div class="message-bubble-wrapper">
                    <div class="user-avatar">
                      {{ substr($message->sender->name, 0, 1) }}
                    </div>
                    <div>
                      <div class="message-bubble">
                        <p class="message-text">{{ $message->message }}</p>
                      </div>
                      <div class="message-meta">
                        <span>{{ $message->created_at->format('H:i') }}</span>
                        @if($message->sender_id === auth()->id() && $message->is_read)
                          <span class="read-status">✓✓</span>
                        @elseif($message->sender_id === auth()->id())
                          <span>✓</span>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endif
          @endforeach
        @endif
      </div>
    </div>

    <!-- Message Input -->
    <div class="message-input-box">
      <form action="{{ route('chat.send', $job) }}" method="POST" id="message-form" class="input-form">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
        <div class="input-wrapper">
          <textarea 
            name="message" 
            id="message-input"
            rows="2" 
            class="input-textarea"
            placeholder="Andika ujumbe wako hapa..."
            required
          ></textarea>
        </div>
        <button type="submit" class="send-btn">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
          </svg>
        </button>
      </form>
    </div>

  </div>
</div>

<script>
  // Auto-scroll to bottom
  const container = document.getElementById('messages-container');
  if (container) {
    container.scrollTop = container.scrollHeight;
  }

  // Handle form submission with AJAX
  const form = document.getElementById('message-form');
  const input = document.getElementById('message-input');

  if (form && input) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const messageText = input.value.trim();
      if (!messageText) return;
      
      const formData = new FormData(form);
      
      fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.message) {
          // Add message to UI
          const messagesInner = container.querySelector('.messages-inner');
          const emptyState = messagesInner.querySelector('.empty-state');
          if (emptyState) {
            emptyState.remove();
          }
          
          const messageHtml = `
            <div class="message-wrapper sent">
              <div class="message-content">
                <div class="message-bubble-wrapper">
                  <div class="user-avatar">${data.message.sender.name.charAt(0)}</div>
                  <div>
                    <div class="message-bubble">
                      <p class="message-text">${data.message.message}</p>
                    </div>
                    <div class="message-meta">
                      <span>Sasa</span>
                      <span>✓</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `;
          
          messagesInner.insertAdjacentHTML('beforeend', messageHtml);
          container.scrollTop = container.scrollHeight;
          input.value = '';
          
          lastMessageId = data.message.id;
        }
      })
      .catch(error => console.error('Error:', error));
    });
    
    // Enter key to send (Shift+Enter for new line)
    input.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        form.dispatchEvent(new Event('submit'));
      }
    });
  }

  // Poll for new messages every 3 seconds
  let lastMessageId = {{ $messages->last()?->id ?? 0 }};
  const otherUserId = {{ $otherUser->id }};
  const currentUserId = {{ auth()->id() }};
  
  setInterval(function() {
    fetch('{{ route("chat.poll", $job) }}?last_id=' + lastMessageId + '&other_user_id=' + otherUserId)
      .then(response => response.json())
      .then(data => {
        if (data.count > 0) {
          const messagesInner = container.querySelector('.messages-inner');
          const emptyState = messagesInner.querySelector('.empty-state');
          if (emptyState) {
            emptyState.remove();
          }
          
          data.messages.forEach(message => {
            const isFromCurrentUser = message.sender_id == currentUserId;
            
            // Only add if it's from other user (to avoid duplicates)
            if (!isFromCurrentUser) {
              const isSystemMessage = message.message.includes('🎉 Hongera!') || message.message.includes('Umechaguliwa kufanya kazi');
              
              let messageHtml;
              if (isSystemMessage) {
                messageHtml = `
                  <div class="system-message">
                    <div class="system-message-content">
                      <p>${message.message}</p>
                    </div>
                  </div>
                `;
              } else {
                messageHtml = `
                  <div class="message-wrapper received">
                    <div class="message-content">
                      <div class="message-bubble-wrapper">
                        <div class="user-avatar">${message.sender.name.charAt(0)}</div>
                        <div>
                          <div class="message-bubble">
                            <p class="message-text">${message.message}</p>
                          </div>
                          <div class="message-meta">
                            <span>Sasa</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                `;
              }
              
              messagesInner.insertAdjacentHTML('beforeend', messageHtml);
            }
            lastMessageId = message.id;
          });
          
          container.scrollTop = container.scrollHeight;
        }
      })
      .catch(error => console.error('Polling error:', error));
  }, 3000);
</script>
@endsection
