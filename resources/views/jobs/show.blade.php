@extends('layouts.app')
@section('title', $job->title)

@section('content')
<style>
  /* ============================================
   * TENDAPOA - AMAZING JOB DETAILS PAGE
   * Premium Dark Glass Design with Hero Image
   * ============================================ */

  :root {
    --job-primary: #6366f1;
    --job-primary-dark: #4f46e5;
    --job-primary-glow: rgba(99, 102, 241, 0.4);
    --job-success: #10b981;
    --job-success-glow: rgba(16, 185, 129, 0.3);
    --job-warning: #f59e0b;
    --job-danger: #ef4444;
    --job-dark: #0f172a;
    --job-glass: rgba(15, 23, 42, 0.85);
    --job-glass-light: rgba(255, 255, 255, 0.08);
    --job-glass-border: rgba(255, 255, 255, 0.12);
    --job-text: #e2e8f0;
    --job-text-muted: #94a3b8;
    --job-text-dim: #64748b;
    --job-gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --job-gradient-success: linear-gradient(135deg, #10b981, #059669);
    --job-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
  }

  .job-show-page {
    background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
    min-height: 100vh;
    display: flex;
    position: relative;
    overflow-x: hidden;
  }

  .job-show-page::before {
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

  .page-container {
    max-width: 1100px;
    margin: 0 auto;
    display: grid;
    gap: 28px;
  }

  /* ============================================
   * HERO IMAGE SECTION - PICHA YA KAZI
   * ============================================ */
  .job-hero {
    position: relative;
    border-radius: 28px;
    overflow: hidden;
    background: var(--job-glass);
    border: 1px solid var(--job-glass-border);
  }

  .job-hero-image-container {
    position: relative;
    width: 100%;
    height: 400px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(120, 75, 162, 0.15));
  }

  .job-hero-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
  }

  .job-hero:hover .job-hero-image {
    transform: scale(1.03);
  }

  .job-hero-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(120, 75, 162, 0.2));
    color: var(--job-text-dim);
  }

  .job-hero-placeholder-icon {
    font-size: 6rem;
    opacity: 0.3;
    margin-bottom: 16px;
  }

  .job-hero-placeholder-text {
    font-size: 1.1rem;
    font-weight: 600;
    opacity: 0.5;
  }

  /* Gradient Overlay on Image */
  .job-hero-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 100px 32px 32px;
    background: linear-gradient(to top, rgba(15, 23, 42, 0.98) 0%, rgba(15, 23, 42, 0.7) 50%, transparent 100%);
  }

  /* Price Badge on Image */
  .job-price-badge {
    position: absolute;
    top: 24px;
    right: 24px;
    background: var(--job-gradient-success);
    color: white;
    padding: 14px 24px;
    border-radius: 16px;
    font-size: 1.5rem;
    font-weight: 900;
    box-shadow: 0 8px 25px var(--job-success-glow);
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 10;
  }

  .job-price-badge small {
    font-size: 0.9rem;
    font-weight: 500;
    opacity: 0.9;
  }

  /* Status Badge on Image */
  .job-status-badge {
    position: absolute;
    top: 24px;
    left: 24px;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    z-index: 10;
    backdrop-filter: blur(10px);
  }

  .job-status-badge.posted {
    background: rgba(59, 130, 246, 0.9);
    color: white;
  }

  .job-status-badge.assigned {
    background: rgba(245, 158, 11, 0.9);
    color: white;
  }

  .job-status-badge.in_progress, .job-status-badge.in-progress {
    background: rgba(168, 85, 247, 0.9);
    color: white;
  }

  .job-status-badge.completed {
    background: rgba(16, 185, 129, 0.9);
    color: white;
  }

  .job-status-badge.pending_payment {
    background: rgba(239, 68, 68, 0.9);
    color: white;
  }

  /* Job Info in Overlay */
  .job-hero-info {
    position: relative;
    z-index: 5;
  }

  .job-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 16px;
  }

  .badge {
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .badge-category {
    background: var(--job-gradient-1);
    color: white;
  }

  .badge-muhitaji {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(59, 130, 246, 0.7));
    color: white;
  }

  .badge-mfanyakazi {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(16, 185, 129, 0.7));
    color: white;
  }

  .job-title {
    font-size: 2.5rem;
    font-weight: 900;
    color: white;
    margin: 0 0 12px 0;
    line-height: 1.2;
    text-shadow: 0 4px 12px rgba(0,0,0,0.3);
  }

  .job-poster-info {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--job-text-muted);
    font-size: 0.95rem;
  }

  .job-poster-avatar {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--job-gradient-1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  }

  .job-poster-name {
    color: var(--job-text);
    font-weight: 600;
  }

  /* ============================================
   * JOB DETAILS GRID
   * ============================================ */
  .job-details-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 28px;
  }

  @media (max-width: 900px) {
    .job-details-grid {
      grid-template-columns: 1fr;
    }
  }

  /* Main Content Card */
  .job-card {
    background: var(--job-glass);
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    border-radius: 24px;
    padding: 32px;
    border: 1px solid var(--job-glass-border);
    position: relative;
  }

  .job-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--job-gradient-1);
    border-radius: 24px 24px 0 0;
  }

  .job-card-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--job-text);
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .job-card-title span {
    font-size: 1.3rem;
  }

  .job-description-text {
    color: var(--job-text-muted);
    font-size: 1.05rem;
    line-height: 1.8;
    margin: 0 0 24px 0;
  }

  /* Price Box */
  .price-box {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
    border: 2px solid rgba(16, 185, 129, 0.3);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
  }

  .price-box-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
  }

  .price-box-icon {
    font-size: 1.5rem;
  }

  .price-box-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #6ee7b7;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .price-box-amount {
    font-size: 2.5rem;
    font-weight: 900;
    color: #10b981;
    margin: 0 0 8px 0;
    text-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  }

  .price-box-note {
    color: #6ee7b7;
    font-size: 0.875rem;
    margin: 0;
  }

  .price-warning {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 10px;
    padding: 12px 16px;
    margin-top: 16px;
    color: #fca5a5;
    font-size: 0.85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  /* Info List */
  .info-list {
    display: grid;
    gap: 16px;
  }

  .info-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    background: var(--job-glass-light);
    border-radius: 14px;
    border: 1px solid var(--job-glass-border);
    transition: all 0.3s ease;
  }

  .info-item:hover {
    transform: translateX(6px);
    border-color: var(--job-primary);
  }

  .info-item-icon {
    width: 48px;
    height: 48px;
    background: var(--job-gradient-1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
  }

  .info-item-content {
    flex: 1;
  }

  .info-item-label {
    font-size: 0.75rem;
    color: var(--job-text-dim);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
  }

  .info-item-value {
    font-size: 1rem;
    font-weight: 600;
    color: var(--job-text);
  }

  /* Chat Box */
  .chat-box {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
    border: 2px solid rgba(16, 185, 129, 0.3);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
  }

  .chat-box-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
  }

  .chat-box-icon {
    font-size: 1.5rem;
  }

  .chat-box-title {
    font-size: 1rem;
    font-weight: 700;
    color: #6ee7b7;
  }

  .chat-box-text {
    color: #6ee7b7;
    font-size: 0.875rem;
    margin: 0 0 16px 0;
  }

  .chat-users {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

  /* Tip Box */
  .tip-box {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
    border: 2px solid rgba(245, 158, 11, 0.3);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 24px;
  }

  .tip-box-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    color: #fcd34d;
    font-weight: 700;
  }

  .tip-box-text {
    color: #fcd34d;
    font-size: 0.875rem;
    margin: 0;
    line-height: 1.6;
  }

  /* Action Buttons */
  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 24px;
    border-radius: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    cursor: pointer;
    font-size: 0.95rem;
  }

  .btn-primary {
    background: var(--job-gradient-1);
    color: white;
    box-shadow: 0 8px 25px var(--job-primary-glow);
  }

  .btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px var(--job-primary-glow);
  }

  .btn-success {
    background: var(--job-gradient-success);
    color: white;
    box-shadow: 0 8px 25px var(--job-success-glow);
  }

  .btn-success:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px var(--job-success-glow);
  }

  .btn-danger {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));
    color: #fca5a5;
    border: 2px solid rgba(239, 68, 68, 0.3);
  }

  .btn-danger:hover {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    transform: translateY(-3px);
  }

  .btn-full {
    width: 100%;
  }

  /* ============================================
   * MAP SECTION
   * ============================================ */
  .map-card {
    background: var(--job-glass);
    backdrop-filter: blur(30px);
    border-radius: 24px;
    overflow: hidden;
    border: 1px solid var(--job-glass-border);
  }

  .map-container {
    height: 220px;
    width: 100%;
  }

  .map-info {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
  }

  .map-info-icon {
    width: 44px;
    height: 44px;
    background: var(--job-gradient-1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
  }

  .map-info-text h4 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--job-text);
    margin: 0 0 4px 0;
  }

  .map-info-text p {
    font-size: 0.85rem;
    color: var(--job-text-muted);
    margin: 0;
  }

  /* ============================================
   * COMMENTS SECTION
   * ============================================ */
  .comments-section {
    background: var(--job-glass);
    backdrop-filter: blur(30px);
    border-radius: 24px;
    padding: 32px;
    border: 1px solid var(--job-glass-border);
  }

  .comments-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
  }

  .comments-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--job-text);
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .comments-count {
    background: var(--job-gradient-1);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
  }

  /* Comment Form */
  .comment-form {
    background: var(--job-glass-light);
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 28px;
    border: 1px solid var(--job-glass-border);
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--job-text);
    margin-bottom: 10px;
  }

  .form-textarea {
    width: 100%;
    padding: 16px;
    border: 2px solid var(--job-glass-border);
    border-radius: 14px;
    font-size: 1rem;
    min-height: 120px;
    resize: vertical;
    transition: all 0.3s ease;
    background: var(--job-glass);
    color: var(--job-text);
  }

  .form-textarea::placeholder {
    color: var(--job-text-dim);
  }

  .form-textarea:focus {
    outline: none;
    border-color: var(--job-primary);
    box-shadow: 0 0 0 3px var(--job-primary-glow);
  }

  .form-input {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid var(--job-glass-border);
    border-radius: 14px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--job-glass);
    color: var(--job-text);
  }

  .form-input::placeholder {
    color: var(--job-text-dim);
  }

  .form-input:focus {
    outline: none;
    border-color: var(--job-primary);
    box-shadow: 0 0 0 3px var(--job-primary-glow);
  }

  .form-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 16px 0;
    padding: 12px 16px;
    background: var(--job-glass);
    border-radius: 12px;
    border: 1px solid var(--job-glass-border);
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .form-checkbox:hover {
    border-color: var(--job-primary);
  }

  .form-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
    accent-color: var(--job-primary);
  }

  .form-checkbox label {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--job-text);
    cursor: pointer;
  }

  /* Comments List */
  .comments-list {
    display: grid;
    gap: 20px;
  }

  .comment-item {
    background: var(--job-glass-light);
    border-radius: 18px;
    padding: 24px;
    border: 1px solid var(--job-glass-border);
    transition: all 0.3s ease;
  }

  .comment-item:hover {
    transform: translateY(-4px);
    border-color: var(--job-primary);
    box-shadow: 0 12px 30px rgba(0,0,0,0.3);
  }

  .comment-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 10px;
  }

  .comment-author {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .comment-avatar {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--job-gradient-1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
  }

  .comment-author-name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--job-text);
  }

  .comment-badge {
    background: var(--job-gradient-success);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .comment-message {
    color: var(--job-text-muted);
    line-height: 1.7;
    margin-bottom: 14px;
    font-size: 0.95rem;
  }

  .comment-bid {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 12px;
    padding: 14px 18px;
    margin-top: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .comment-bid-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #a5b4fc;
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }

  .comment-bid-amount {
    font-size: 1.25rem;
    font-weight: 800;
    color: #10b981;
  }

  .comment-actions {
    margin-top: 18px;
  }

  /* Empty Comments */
  .empty-comments {
    text-align: center;
    padding: 48px 24px;
    color: var(--job-text-dim);
  }

  .empty-comments-icon {
    font-size: 3.5rem;
    margin-bottom: 16px;
    opacity: 0.5;
  }

  .empty-comments-text {
    font-size: 1.1rem;
    font-weight: 600;
  }

  /* ============================================
   * RESPONSIVE
   * ============================================ */
  @media (max-width: 768px) {
    .main-content {
      padding: 16px;
    }

    .job-hero-image-container {
      height: 280px;
    }

    .job-title {
      font-size: 1.75rem;
    }

    .job-price-badge {
      font-size: 1.2rem;
      padding: 10px 16px;
    }

    .page-container {
      gap: 20px;
    }

    .job-card {
      padding: 24px;
    }

    .price-box-amount {
      font-size: 2rem;
    }

    .comments-header {
      flex-direction: column;
      align-items: flex-start;
    }
  }
</style>

<div class="job-show-page">
  @include('components.user-sidebar')
  
  <main class="main-content">
    <div class="page-container">
    
    <!-- HERO SECTION WITH IMAGE -->
    <div class="job-hero">
      <div class="job-hero-image-container">
        @php
          $imageUrl = null;
          if ($job->image) {
            $filePath = storage_path('app/public/' . $job->image);
            if (file_exists($filePath)) {
              $imageUrl = asset('storage/' . $job->image) . '?v=' . filemtime($filePath);
            }
          }
        @endphp
        
        @if($imageUrl)
          <img src="{{ $imageUrl }}" alt="{{ $job->title }}" class="job-hero-image"
               onerror="this.onerror=null; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
          <div class="job-hero-placeholder" style="display: none;">
            <div class="job-hero-placeholder-icon">📷</div>
            <div class="job-hero-placeholder-text">Picha Haipo</div>
          </div>
        @else
          <div class="job-hero-placeholder">
            <div class="job-hero-placeholder-icon">📷</div>
            <div class="job-hero-placeholder-text">Hakuna Picha ya Kazi</div>
          </div>
        @endif
      </div>
      
      <!-- Price Badge -->
      <div class="job-price-badge">
        {{ number_format($job->price) }} <small>TZS</small>
      </div>
      
      <!-- Status Badge -->
      <div class="job-status-badge {{ str_replace('_', '-', $job->status) }}">
        @switch($job->status)
          @case('posted') 📋 Imechapishwa @break
          @case('assigned') 👷 Imepewa Mfanyakazi @break
          @case('in_progress') ⚡ Inaendelea @break
          @case('completed') ✅ Imekamilika @break
          @case('pending_payment') ⏳ Inasubiri Malipo @break
          @default {{ ucfirst($job->status) }}
        @endswitch
      </div>
      
      <!-- Info Overlay -->
      <div class="job-hero-overlay">
        <div class="job-hero-info">
          <div class="job-badges">
            <div class="badge badge-category">{{ $job->category->name }}</div>
            @if($job->poster_type === 'mfanyakazi')
              <div class="badge badge-mfanyakazi">👷 Huduma ya Mfanyakazi</div>
            @else
              <div class="badge badge-muhitaji">👤 Kazi ya Muhitaji</div>
            @endif
          </div>
          
          <h1 class="job-title">{{ $job->title }}</h1>
          
          <div class="job-poster-info">
            <div class="job-poster-avatar">
              @if($job->poster_type === 'mfanyakazi') 👷 @else 👤 @endif
            </div>
            <div>
              <span>{{ $job->poster_type === 'mfanyakazi' ? 'Mfanyakazi' : 'Muhitaji' }}:</span>
              <span class="job-poster-name">{{ $job->muhitaji->name }}</span>
              • {{ $job->created_at->diffForHumans() }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DETAILS GRID -->
    <div class="job-details-grid">
      
      <!-- Main Content -->
      <div>
        <!-- Description Card -->
        <div class="job-card" style="margin-bottom: 28px;">
          <h3 class="job-card-title"><span>📋</span> Maelezo ya Kazi</h3>
          
          <p class="job-description-text">
            {{ $job->description ?? 'Hakuna maelezo ya ziada yaliyowekwa kwa kazi hii.' }}
          </p>
          
          <!-- Price Box -->
          <div class="price-box">
            <div class="price-box-header">
              <span class="price-box-icon">💰</span>
              <span class="price-box-label">Bei ya Kazi</span>
            </div>
            <div class="price-box-amount">{{ number_format($job->price) }} TZS</div>
            <p class="price-box-note">Bei ya escrow - utalipa tu mfanyakazi akimaliza kazi kwa usahihi.</p>
            
            @if(auth()->check() && auth()->user()->role === 'mfanyakazi')
              <div class="price-warning">
                ⚠️ Kumbuka: Makato ya 10% (Service Fee) yatakatwa wakati wa malipo.
              </div>
            @endif
          </div>

          @auth
            @php
              $userHasCommented = auth()->check() && $job->comments()->where('user_id', auth()->id())->exists();
              $isAcceptedWorker = auth()->id() === $job->accepted_worker_id;
              $isMuhitaji = auth()->id() === $job->user_id;
            @endphp

            {{-- Chat Box for Muhitaji --}}
            @if($isMuhitaji && $job->comments()->whereHas('user', fn($q) => $q->where('role', 'mfanyakazi'))->exists())
              <div class="chat-box">
                <div class="chat-box-header">
                  <span class="chat-box-icon">💬</span>
                  <span class="chat-box-title">Mazungumzo na Wafanyakazi</span>
                </div>
                <p class="chat-box-text">Unaweza kuzungumza na mfanyakazi yeyote aliye apply</p>
                <div class="chat-users">
                  @foreach($job->comments()->with('user')->get()->unique('user_id') as $comment)
                    @if($comment->user && $comment->user->role === 'mfanyakazi')
                      <a href="{{ route('chat.show', ['job' => $job, 'worker_id' => $comment->user_id]) }}" class="btn btn-success">
                        💬 {{ $comment->user->name }}
                      </a>
                    @endif
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Chat Box for Mfanyakazi --}}
            @if(($userHasCommented || $isAcceptedWorker) && !$isMuhitaji)
              <div class="chat-box">
                <div class="chat-box-header">
                  <span class="chat-box-icon">💬</span>
                  <span class="chat-box-title">Mazungumzo na Muhitaji</span>
                </div>
                <p class="chat-box-text">Muhitaji: <strong>{{ $job->muhitaji->name }}</strong></p>
                <a href="{{ route('chat.show', $job) }}" class="btn btn-success">
                  💬 Fungua Mazungumzo
                </a>
              </div>
            @endif

            {{-- Tip for Muhitaji --}}
            @if(auth()->user()->role === 'muhitaji' && auth()->id() === $job->user_id && $job->status === 'posted')
              <div class="tip-box">
                <div class="tip-box-header">
                  <span>💡</span> Kidokezo
                </div>
                <p class="tip-box-text">
                  Chagua mfanyakazi kupitia maombi (applications) hapa chini. Angalia ujuzi na profile ya mfanyakazi kabla ya kumchagua.
                </p>
              </div>
            @endif

            {{-- Cancel Button --}}
            @if(auth()->user()->role === 'muhitaji' && auth()->id() === $job->user_id && $job->status === 'posted')
              <form action="{{ route('jobs.cancel', $job) }}" method="POST" onsubmit="return confirm('Una uhakika unataka kufuta kazi hii? Pesa itarudishwa kwenye wallet yako.');">
                @csrf
                <button type="submit" class="btn btn-danger btn-full">
                  🗑️ Futa Kazi na Rudisha Pesa
                </button>
              </form>
            @endif
          @endauth
        </div>

        <!-- Comments Section -->
        <div class="comments-section">
          <div class="comments-header">
            <div class="comments-title">
              <span>💬</span> Maoni na Maombi
            </div>
            <div class="comments-count">
              {{ $job->comments->count() }} Maoni • 
              {{ $job->comments->where('is_application', true)->count() }} Maombi
            </div>
          </div>

          @if (session('success'))
            <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.1)); border-left: 4px solid #10b981; padding: 16px; border-radius: 12px; margin-bottom: 20px; color: #6ee7b7;">
              ✅ {{ session('success') }}
            </div>
          @endif

          @if (session('error'))
            <div style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1)); border-left: 4px solid #ef4444; padding: 16px; border-radius: 12px; margin-bottom: 20px; color: #fca5a5;">
              ❌ {{ session('error') }}
            </div>
          @endif

          <!-- Comment Form for Mfanyakazi -->
          @auth
            @if(auth()->user()->role === 'mfanyakazi' && $job->status === 'posted' && auth()->id() !== $job->user_id)
              <form method="post" action="{{ route('jobs.comment', $job) }}" class="comment-form" id="comment-form">
                @csrf
                <div class="form-group">
                  <label class="form-label">📝 Andika Maoni au Omba Kazi</label>
                  <textarea 
                    name="message" 
                    id="message"
                    class="form-textarea"
                    placeholder="Karibu! Mimi ni mtaalamu wa... na nina uzoefu wa miaka... Naomba kufanya kazi hii..."
                    required
                    rows="4"
                  ></textarea>
                </div>
                
                <!-- Application Type Selector -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px;">
                  <label class="type-option" style="padding: 16px; background: var(--job-glass); border: 2px solid var(--job-glass-border); border-radius: 14px; cursor: pointer; transition: all 0.3s; text-align: center;">
                    <input type="radio" name="type" value="comment" checked style="display: none;">
                    <span style="display: block; font-size: 1.5rem; margin-bottom: 6px;">💬</span>
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--job-text);">Maoni tu</span>
                  </label>
                  
                  <label class="type-option" style="padding: 16px; background: var(--job-glass); border: 2px solid var(--job-glass-border); border-radius: 14px; cursor: pointer; transition: all 0.3s; text-align: center;">
                    <input type="radio" name="type" value="application" style="display: none;">
                    <span style="display: block; font-size: 1.5rem; margin-bottom: 6px;">✋</span>
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--job-text);">Omba Kazi</span>
                  </label>
                  
                  <label class="type-option" style="padding: 16px; background: var(--job-glass); border: 2px solid var(--job-glass-border); border-radius: 14px; cursor: pointer; transition: all 0.3s; text-align: center;">
                    <input type="radio" name="type" value="offer" style="display: none;">
                    <span style="display: block; font-size: 1.5rem; margin-bottom: 6px;">💰</span>
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--job-text);">Pendelea Bei</span>
                  </label>
                </div>
                
                <!-- Bid Amount (shows when offer or application is selected) -->
                <div class="form-group" id="bid-section" style="display: none;">
                  <label class="form-label" for="bid_amount">💵 Bei Unayopendekeza</label>
                  <div style="position: relative;">
                    <input 
                      type="number" 
                      name="bid_amount" 
                      id="bid_amount"
                      class="form-input"
                      placeholder="10000"
                      min="1000"
                      step="500"
                      style="padding-left: 50px; font-size: 1.2rem; font-weight: 700;"
                    >
                    <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--job-text-muted); font-weight: 600;">TZS</span>
                  </div>
                  <p style="font-size: 0.8rem; color: var(--job-text-dim); margin-top: 8px;">
                    Bei ya sasa: <strong style="color: #10b981;">{{ number_format($job->price) }} TZS</strong>
                  </p>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                  📤 Tuma
                </button>
              </form>
            @elseif(auth()->user()->role === 'mfanyakazi' && $job->status !== 'posted')
              <div class="tip-box" style="margin-bottom: 20px;">
                <div class="tip-box-header">
                  <span>ℹ️</span> Taarifa
                </div>
                <p class="tip-box-text">
                  Kazi hii imekwishachaguliwa mfanyakazi au imekamilika. Huwezi kuomba tena.
                </p>
              </div>
            @endif

            <!-- Increase Budget Form for Muhitaji -->
            @if(auth()->user()->role === 'muhitaji' && auth()->id() === $job->user_id && in_array($job->status, ['posted', 'assigned']))
              <div class="job-card" style="margin-bottom: 24px; border: 1px dashed rgba(16, 185, 129, 0.4);">
                <h4 style="color: #10b981; margin: 0 0 16px 0; font-size: 1rem;">💰 Ongeza Bajeti ya Kazi</h4>
                <form action="{{ route('jobs.increase-budget', $job) }}" method="POST" style="display: flex; gap: 12px; align-items: flex-end;">
                  @csrf
                  <div style="flex: 1;">
                    <input 
                      type="number" 
                      name="additional_amount" 
                      class="form-input"
                      placeholder="5000"
                      min="1000"
                      step="500"
                      required
                      style="font-weight: 600;"
                    >
                  </div>
                  <button type="submit" class="btn btn-success" onclick="return confirm('Una uhakika unataka kuongeza pesa kwenye kazi hii?');">
                    ➕ Ongeza TZS
                  </button>
                </form>
              </div>
            @endif
          @else
            <div class="tip-box">
              <div class="tip-box-header">
                <span>🔐</span> Unahitaji Kuingia
              </div>
              <p class="tip-box-text">
                <a href="{{ route('login') }}" style="color: #fcd34d; text-decoration: underline;">Ingia</a> ili uweze kutuma maoni au kuomba kazi.
              </p>
            </div>
          @endauth

          <!-- Comments List -->
          <div class="comments-list">
            @forelse($job->comments->whereNull('parent_id')->sortByDesc('created_at') as $comment)
              <div class="comment-item {{ $comment->isApplication() ? 'is-application' : '' }} {{ $comment->isOffer() ? 'is-offer' : '' }}" 
                   style="{{ $comment->status === 'accepted' ? 'border-color: rgba(16, 185, 129, 0.5); background: rgba(16, 185, 129, 0.05);' : '' }}
                          {{ $comment->status === 'rejected' ? 'opacity: 0.6;' : '' }}">
                
                <div class="comment-header">
                  <div class="comment-author">
                    <div class="comment-avatar" style="{{ $comment->user->role === 'mfanyakazi' ? 'background: linear-gradient(135deg, #10b981, #059669);' : '' }}">
                      {{ $comment->user->role === 'mfanyakazi' ? '👷' : '👤' }}
                    </div>
                    <div>
                      <span class="comment-author-name">{{ $comment->user->name }}</span>
                      <div style="font-size: 0.75rem; color: var(--job-text-dim);">
                        {{ $comment->created_at->diffForHumans() }}
                      </div>
                    </div>
                  </div>
                  
                  <!-- Badges -->
                  <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    @if($comment->type === 'system')
                      <span style="background: rgba(99, 102, 241, 0.2); color: #a5b4fc; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700;">🔔 System</span>
                    @elseif($comment->isOffer())
                      <span style="background: rgba(245, 158, 11, 0.2); color: #fcd34d; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700;">💰 Pendekezo la Bei</span>
                    @elseif($comment->isApplication())
                      <span class="comment-badge">✋ Ameomba Kazi</span>
                    @endif
                    
                    @if($comment->status === 'accepted')
                      <span style="background: rgba(16, 185, 129, 0.3); color: #6ee7b7; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700;">✅ Amechaguliwa</span>
                    @elseif($comment->status === 'rejected')
                      <span style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700;">❌ Amekataliwa</span>
                    @elseif($comment->status === 'countered')
                      <span style="background: rgba(168, 85, 247, 0.2); color: #c4b5fd; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700;">🔄 Counter Offer</span>
                    @endif
                  </div>
                </div>
                
                <div class="comment-message">{{ $comment->message }}</div>
                
                <!-- Bid/Offer Amount -->
                @if($comment->bid_amount)
                  <div class="comment-bid" style="margin-top: 16px;">
                    <span class="comment-bid-label">Pendekezo la Bei:</span>
                    <span class="comment-bid-amount" style="{{ $comment->bid_amount < $job->price ? 'color: #fcd34d;' : 'color: #6ee7b7;' }}">
                      {{ number_format($comment->bid_amount) }} TZS
                    </span>
                    @if($comment->bid_amount < $job->price)
                      <span style="font-size: 0.75rem; color: #fcd34d; margin-left: 8px;">
                        ({{ number_format($job->price - $comment->bid_amount) }} chini)
                      </span>
                    @elseif($comment->bid_amount > $job->price)
                      <span style="font-size: 0.75rem; color: #6ee7b7; margin-left: 8px;">
                        (+{{ number_format($comment->bid_amount - $job->price) }})
                      </span>
                    @endif
                  </div>
                @endif

                <!-- Counter Offer Display -->
                @if($comment->status === 'countered' && $comment->counter_amount)
                  <div style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.15), rgba(168, 85, 247, 0.05)); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 12px; padding: 16px; margin-top: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                      <span style="font-size: 1.25rem;">🔄</span>
                      <span style="font-weight: 700; color: #c4b5fd;">Counter Offer kutoka Muhitaji</span>
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 900; color: #c4b5fd;">
                      {{ number_format($comment->counter_amount) }} TZS
                    </div>
                    @if($comment->reply_message)
                      <p style="color: var(--job-text-muted); font-size: 0.9rem; margin-top: 8px;">
                        "{{ $comment->reply_message }}"
                      </p>
                    @endif
                    
                    <!-- Accept Counter Button for Mfanyakazi -->
                    @auth
                      @if(auth()->id() === $comment->user_id && $job->status === 'posted')
                        <form action="{{ route('jobs.accept-counter', [$job, $comment]) }}" method="POST" style="margin-top: 12px;">
                          @csrf
                          <button type="submit" class="btn btn-success btn-full">
                            ✅ Nakubali Counter Offer hii
                          </button>
                        </form>
                      @endif
                    @endauth
                  </div>
                @endif

                <!-- Muhitaji's Reply Display -->
                @if($comment->reply_message && $comment->status !== 'countered')
                  <div style="background: var(--job-glass-light); border-left: 3px solid var(--job-primary); padding: 12px 16px; margin-top: 16px; border-radius: 0 12px 12px 0;">
                    <div style="font-size: 0.75rem; color: var(--job-text-dim); margin-bottom: 6px;">
                      💬 Jibu la Muhitaji ({{ $comment->replied_at ? $comment->replied_at->diffForHumans() : '' }}):
                    </div>
                    <p style="margin: 0; color: var(--job-text);">{{ $comment->reply_message }}</p>
                  </div>
                @endif

                <!-- Action Buttons for Muhitaji -->
                @auth
                  @if(auth()->id() === $job->user_id && $job->status === 'posted' && $comment->user->role === 'mfanyakazi' && $comment->status === 'pending')
                    <div style="display: grid; gap: 12px; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--job-glass-border);">
                      
                      <!-- Accept Button -->
                      <form method="post" action="{{ route('jobs.accept', [$job, $comment]) }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-full" onclick="return confirm('Uma uhakika unataka kumchagua {{ $comment->user->name }}?');">
                          ✅ Mchague Huyu Mfanyakazi
                          @if($comment->bid_amount)
                            ({{ number_format($comment->bid_amount) }} TZS)
                          @endif
                        </button>
                      </form>
                      
                      <!-- Counter Offer Section (when there's a bid) -->
                      @if($comment->bid_amount || $comment->isOffer())
                        <div style="background: var(--job-glass-light); padding: 16px; border-radius: 12px; border: 1px solid var(--job-glass-border);">
                          <div style="font-weight: 700; color: var(--job-text); margin-bottom: 12px;">
                            🔄 Tuma Counter Offer
                          </div>
                          <form action="{{ route('jobs.counter', [$job, $comment]) }}" method="POST">
                            @csrf
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                              <input 
                                type="number" 
                                name="counter_amount" 
                                class="form-input"
                                placeholder="Bei yako"
                                min="1000"
                                required
                                style="font-weight: 600;"
                              >
                              <input 
                                type="text" 
                                name="counter_message" 
                                class="form-input"
                                placeholder="Ujumbe (hiari)"
                              >
                            </div>
                            <button type="submit" class="btn btn-full" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white;">
                              📤 Tuma Counter Offer
                            </button>
                          </form>
                        </div>
                      @endif
                      
                      <!-- Reply Section -->
                      <div style="background: var(--job-glass-light); padding: 16px; border-radius: 12px; border: 1px solid var(--job-glass-border);">
                        <div style="font-weight: 700; color: var(--job-text); margin-bottom: 12px;">
                          💬 Jibu tu (bila kumchagua)
                        </div>
                        <form action="{{ route('jobs.reply', [$job, $comment]) }}" method="POST">
                          @csrf
                          <div style="margin-bottom: 12px;">
                            <input 
                              type="text" 
                              name="reply_message" 
                              class="form-input"
                              placeholder="Andika jibu lako..."
                              required
                            >
                          </div>
                          <button type="submit" class="btn btn-full" style="background: var(--job-glass-light); border: 1px solid var(--job-glass-border); color: var(--job-text);">
                            📤 Jibu
                          </button>
                        </form>
                      </div>
                      
                      <!-- Reject Button -->
                      <form action="{{ route('jobs.reject', [$job, $comment]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="reject_reason" value="Sio mfanyakazi tunayemhitaji kwa sasa.">
                        <button type="submit" class="btn btn-danger btn-full" style="opacity: 0.8;" onclick="return confirm('Una uhakika unataka kumkataa {{ $comment->user->name }}?');">
                          ❌ Mkatae
                        </button>
                      </form>
                    </div>
                  @endif
                  
                  <!-- Chat Link for both parties after selection -->
                  @if(($job->accepted_worker_id === $comment->user_id && auth()->id() === $job->user_id) || 
                      ($job->accepted_worker_id === auth()->id() && $comment->user_id === auth()->id()))
                    <a href="{{ route('chat.show', $job) }}" class="btn btn-primary btn-full" style="margin-top: 16px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                      💬 Fungua Chat
                    </a>
                  @endif
                @endauth
              </div>
            @empty
              <div class="empty-comments">
                <div class="empty-comments-icon">💬</div>
                <div class="empty-comments-text">Bado hakuna maoni. Kuwa wa kwanza kutuma maoni!</div>
              </div>
            @endforelse
          </div>
        </div>
      </div>
      
      <!-- Sidebar -->
      <div>
        <!-- Map Card -->
        <div class="map-card" style="margin-bottom: 28px;">
          <div class="map-container">
            <div id="map" style="height: 100%; width: 100%;"></div>
          </div>
          <div class="map-info">
            <div class="map-info-icon">📍</div>
            <div class="map-info-text">
              <h4>Eneo la Kazi</h4>
              <p>{{ $job->address_text ?? 'Eneo limewekwa kwenye ramani' }}</p>
            </div>
          </div>
        </div>

        <!-- Quick Info -->
        <div class="job-card">
          <h3 class="job-card-title"><span>ℹ️</span> Taarifa Fupi</h3>
          
          <div class="info-list">
            <div class="info-item">
              <div class="info-item-icon">📂</div>
              <div class="info-item-content">
                <div class="info-item-label">Kategoria</div>
                <div class="info-item-value">{{ $job->category->name }}</div>
              </div>
            </div>
            
            <div class="info-item">
              <div class="info-item-icon">📊</div>
              <div class="info-item-content">
                <div class="info-item-label">Hali</div>
                <div class="info-item-value">
                  @switch($job->status)
                    @case('posted') Imechapishwa @break
                    @case('assigned') Imekabidhiwa @break
                    @case('in_progress') Inaendelea @break
                    @case('completed') Imekamilika @break
                    @case('pending_payment') Inasubiri Malipo @break
                    @default {{ ucfirst($job->status) }}
                  @endswitch
                </div>
              </div>
            </div>
            
            <div class="info-item">
              <div class="info-item-icon">📅</div>
              <div class="info-item-content">
                <div class="info-item-label">Ilipostiwa</div>
                <div class="info-item-value">{{ $job->created_at->format('d M Y') }}</div>
              </div>
            </div>
            
            <div class="info-item">
              <div class="info-item-icon">💬</div>
              <div class="info-item-content">
                <div class="info-item-label">Maombi</div>
                <div class="info-item-value">{{ $job->comments->where('is_application', true)->count() }} maombi</div>
              </div>
            </div>

            @if($job->accepted_worker_id)
              <div class="info-item" style="background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.3);">
                <div class="info-item-icon" style="background: var(--job-gradient-success);">👷</div>
                <div class="info-item-content">
                  <div class="info-item-label" style="color: #6ee7b7;">Mfanyakazi</div>
                  <div class="info-item-value" style="color: #6ee7b7;">{{ $job->acceptedWorker->name ?? 'N/A' }}</div>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

  </div>
</main>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize map
  @if($job->lat && $job->lng)
    const map = L.map('map').setView([{{ $job->lat }}, {{ $job->lng }}], 14);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap'
    }).addTo(map);

    // Custom marker
    const jobIcon = L.divIcon({
      className: 'custom-marker',
      html: '<div style="background: linear-gradient(135deg, #6366f1, #4f46e5); width: 36px; height: 36px; border-radius: 50%; border: 4px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; font-size: 16px;">📍</div>',
      iconSize: [36, 36],
      iconAnchor: [18, 18]
    });

    L.marker([{{ $job->lat }}, {{ $job->lng }}], { icon: jobIcon })
      .addTo(map)
      .bindPopup('<strong>{{ $job->title }}</strong><br>{{ $job->address_text ?? "Eneo la kazi" }}')
      .openPopup();
  @else
    document.getElementById('map').innerHTML = '<div style="height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(120, 75, 162, 0.1)); color: #94a3b8; font-size: 0.9rem;">📍 Eneo halijawekwa</div>';
  @endif

  // Animate comment items
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.comment-item').forEach((item, index) => {
    item.style.opacity = '0';
    item.style.transform = 'translateY(20px)';
    item.style.transition = `all 0.5s ease ${index * 0.1}s`;
    observer.observe(item);
  });

  // Comment type selector
  const typeOptions = document.querySelectorAll('.type-option');
  const bidSection = document.getElementById('bid-section');
  
  typeOptions.forEach(option => {
    const input = option.querySelector('input[type="radio"]');
    
    // Initialize selected state
    if (input && input.checked) {
      option.style.borderColor = '#6366f1';
      option.style.background = 'rgba(99, 102, 241, 0.15)';
    }
    
    option.addEventListener('click', function() {
      // Reset all options
      typeOptions.forEach(opt => {
        opt.style.borderColor = 'rgba(255,255,255,0.12)';
        opt.style.background = 'rgba(15, 23, 42, 0.85)';
      });
      
      // Highlight selected
      this.style.borderColor = '#6366f1';
      this.style.background = 'rgba(99, 102, 241, 0.15)';
      
      // Check the radio
      const radio = this.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
      
      // Show/hide bid section
      if (bidSection) {
        if (radio.value === 'offer' || radio.value === 'application') {
          bidSection.style.display = 'block';
          bidSection.style.animation = 'slideIn 0.3s ease';
        } else {
          bidSection.style.display = 'none';
        }
      }
    });
  });

  // Animation keyframes
  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  `;
  document.head.appendChild(style);
});
</script>

@endsection