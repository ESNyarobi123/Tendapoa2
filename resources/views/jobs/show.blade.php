@extends('layouts.app')
@section('title', $job->title)

@section('content')
<style>
  /* TENDAPOA — ukurasa wa kazi: font ndogo, mpangilio mwepesi, UX wazi */

  :root {
    --job-primary: #059669;
    --job-primary-dark: #047857;
    --job-primary-glow: rgba(5, 150, 105, 0.18);
    --job-success: #10b981;
    --job-success-glow: rgba(16, 185, 129, 0.15);
    --job-warning: #d97706;
    --job-danger: #ef4444;
    --job-dark: #0f172a;
    --job-glass: #ffffff;
    --job-glass-light: #f8fafc;
    --job-glass-border: #e2e8f0;
    --job-text: #0f172a;
    --job-text-muted: #64748b;
    --job-text-dim: #94a3b8;
    --job-gradient-1: linear-gradient(135deg, #059669, #0d9488);
    --job-gradient-success: linear-gradient(135deg, #10b981, #059669);
    --job-shadow: 0 1px 3px rgba(15, 23, 42, 0.06), 0 8px 24px rgba(15, 23, 42, 0.06);
    --job-radius: 14px;
    --job-radius-sm: 10px;
    --job-font: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
  }

  .job-show-page {
    font-family: var(--job-font);
    font-size: 13px;
    line-height: 1.55;
    -webkit-font-smoothing: antialiased;
    color: var(--job-text);
    background: linear-gradient(180deg, #f1f5f9 0%, #e8eef4 100%);
    min-height: 100vh;
    display: flex;
    position: relative;
    overflow-x: hidden;
  }

  .job-show-page::before {
    display: none;
  }

  .job-show-page .main-content {
    flex: 1;
    margin-left: 0;
    padding: 14px 16px 28px;
    min-height: 100vh;
    position: relative;
    z-index: 1;
  }

  @media (min-width: 1025px) {
    .job-show-page .main-content {
      margin-left: 240px;
      padding: 20px 24px 32px;
    }
    .tp-sidebar.collapsed ~ .main-content {
      margin-left: 64px;
    }
  }

  .page-container {
    max-width: 1040px;
    margin: 0 auto;
    display: grid;
    gap: 18px;
  }

  .job-hero {
    position: relative;
    border-radius: var(--job-radius);
    overflow: hidden;
    background: var(--job-glass);
    border: 1px solid var(--job-glass-border);
    box-shadow: var(--job-shadow);
  }

  .job-hero-image-container {
    position: relative;
    width: 100%;
    height: min(38vh, 320px);
    overflow: hidden;
    background: linear-gradient(145deg, #ecfdf5 0%, #e0e7ff 50%, #f5f3ff 100%);
  }

  @media (min-width: 900px) {
    .job-hero-image-container {
      height: 300px;
    }
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
    font-size: 3rem;
    opacity: 0.35;
    margin-bottom: 8px;
  }

  .job-hero-placeholder-text {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--job-text-muted);
  }

  .job-hero-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 48px 16px 16px;
    background: linear-gradient(to top, rgba(15, 23, 42, 0.92) 0%, rgba(15, 23, 42, 0.55) 55%, transparent 100%);
  }

  @media (min-width: 640px) {
    .job-hero-overlay {
      padding: 56px 20px 18px;
    }
  }

  .job-price-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(255, 255, 255, 0.97);
    color: var(--job-primary-dark);
    padding: 6px 11px;
    border-radius: var(--job-radius-sm);
    font-size: 0.8125rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    gap: 4px;
    z-index: 10;
  }

  .job-price-badge small {
    font-size: 0.65rem;
    font-weight: 600;
    opacity: 0.75;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .job-status-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    max-width: calc(100% - 140px);
    padding: 5px 10px;
    border-radius: var(--job-radius-sm);
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    line-height: 1.3;
    z-index: 10;
    backdrop-filter: blur(8px);
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

  .job-status-badge.open {
    background: rgba(59, 130, 246, 0.9);
    color: white;
  }

  .job-status-badge.awaiting_payment, .job-status-badge.awaiting-payment {
    background: rgba(245, 158, 11, 0.9);
    color: white;
  }

  .job-status-badge.funded {
    background: rgba(16, 185, 129, 0.9);
    color: white;
  }

  .job-status-badge.submitted {
    background: rgba(168, 85, 247, 0.9);
    color: white;
  }

  .job-status-badge.disputed {
    background: rgba(239, 68, 68, 0.9);
    color: white;
  }

  .job-status-badge.cancelled {
    background: rgba(107, 114, 128, 0.9);
    color: white;
  }

  .job-status-badge.refunded {
    background: rgba(107, 114, 128, 0.9);
    color: white;
  }

  .job-status-badge.expired {
    background: rgba(107, 114, 128, 0.7);
    color: white;
  }

  .job-flow-banner {
    border-radius: var(--job-radius-sm);
    border: 1px solid var(--job-glass-border);
    background: #fff;
    padding: 12px 14px;
    margin-bottom: 0;
    box-shadow: var(--job-shadow);
  }
  .job-flow-banner-kicker {
    font-size: 0.5625rem;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--job-primary);
    margin-bottom: 4px;
  }
  .job-flow-banner-title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--job-text);
    margin: 0 0 8px 0;
    line-height: 1.45;
  }
  .job-flow-steps {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .job-flow-steps li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 0.75rem;
    color: var(--job-text-muted);
    line-height: 1.45;
  }
  .job-flow-steps li strong {
    color: var(--job-text);
    font-weight: 700;
  }
  .job-flow-steps .step-idx {
    flex-shrink: 0;
    width: 18px;
    height: 18px;
    border-radius: 6px;
    background: #e2e8f0;
    color: #64748b;
    font-size: 0.5625rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .job-flow-steps li.is-done .step-idx {
    background: rgba(16, 185, 129, 0.2);
    color: #059669;
  }
  .job-flow-steps li.is-current .step-idx {
    background: var(--job-gradient-1);
    color: #fff;
    box-shadow: 0 2px 8px var(--job-primary-glow);
  }
  .job-flow-steps li.is-current strong {
    color: var(--job-primary-dark);
  }
  .job-action-panel {
    border-radius: var(--job-radius-sm);
    padding: 14px 16px;
    margin-bottom: 14px;
    border: 1px solid var(--job-glass-border);
    background: var(--job-glass);
    box-shadow: var(--job-shadow);
  }
  .job-action-panel h4 {
    margin: 0 0 8px 0;
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--job-text);
    letter-spacing: -0.01em;
  }
  .job-action-panel p.lead {
    margin: 0 0 10px 0;
    font-size: 0.75rem;
    color: var(--job-text-muted);
    line-height: 1.55;
  }
  .job-action-panel--apply {
    border-color: rgba(16, 185, 129, 0.35);
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(255,255,255,0.95));
  }
  .job-action-panel--apply h4 { color: #047857; }
  .job-action-panel--status {
    border-color: rgba(99, 102, 241, 0.35);
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.07), rgba(255,255,255,0.98));
  }
  .job-action-panel--pay {
    border-color: rgba(245, 158, 11, 0.45);
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(255,255,255,0.98));
    text-align: center;
  }
  .job-action-panel--pay h4 { color: #b45309; }
  .job-action-panel--progress {
    border-color: rgba(168, 85, 247, 0.35);
    background: linear-gradient(135deg, rgba(168, 85, 247, 0.07), rgba(255,255,255,0.98));
  }
  .job-action-panel--progress h4 { color: #6d28d9; }
  .job-legacy-label {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.5625rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #64748b;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 6px;
    margin-bottom: 8px;
  }
  .job-side-flow {
    border-radius: var(--job-radius-sm);
    border: 1px solid var(--job-glass-border);
    background: var(--job-glass);
    padding: 12px 14px;
    margin-bottom: 14px;
    box-shadow: var(--job-shadow);
  }
  .job-side-flow h4 {
    margin: 0 0 8px 0;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--job-text);
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .job-side-flow .job-flow-steps { gap: 2px; }
  .job-side-flow .job-flow-steps li { font-size: 0.6875rem; }

  .job-worker-apply-bar {
    margin: 0 0 14px;
    padding: 11px 14px;
    border-radius: var(--job-radius-sm);
    background: linear-gradient(135deg, #047857, #059669);
    color: #fff;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    box-shadow: var(--job-shadow);
  }
  .job-worker-apply-bar p { margin: 0; font-size: 0.75rem; font-weight: 600; line-height: 1.5; max-width: 100%; flex: 1; min-width: 200px; }
  .job-worker-apply-bar a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border-radius: var(--job-radius-sm);
    background: #fff;
    color: #047857;
    font-weight: 700;
    font-size: 0.75rem;
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
  }
  .job-worker-apply-bar a:hover { filter: brightness(1.02); }

  /* Job Info in Overlay */
  .job-hero-info {
    position: relative;
    z-index: 5;
  }

  .job-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 10px;
  }

  .badge {
    padding: 4px 9px;
    border-radius: 6px;
    font-size: 0.5625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    display: inline-flex;
    align-items: center;
    gap: 4px;
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
    font-size: clamp(1.05rem, 2.8vw, 1.25rem);
    font-weight: 700;
    color: white;
    margin: 0 0 8px 0;
    line-height: 1.3;
    letter-spacing: -0.02em;
    text-shadow: 0 1px 8px rgba(0,0,0,0.35);
  }

  .job-poster-info {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(248, 250, 252, 0.85);
    font-size: 0.6875rem;
  }

  .job-poster-avatar {
    width: 32px;
    height: 32px;
    border-radius: var(--job-radius-sm);
    background: rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .job-poster-name {
    color: #fff;
    font-weight: 600;
  }

  .job-details-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 280px;
    gap: 18px;
    align-items: start;
  }

  @media (max-width: 900px) {
    .job-details-grid {
      grid-template-columns: 1fr;
    }
  }

  .job-card {
    background: var(--job-glass);
    border-radius: var(--job-radius);
    padding: 16px 18px;
    border: 1px solid var(--job-glass-border);
    position: relative;
    box-shadow: var(--job-shadow);
  }

  .job-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--job-gradient-1);
    border-radius: var(--job-radius) var(--job-radius) 0 0;
  }

  .job-card-title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--job-text);
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 6px;
    letter-spacing: -0.01em;
  }

  .job-card-title span {
    font-size: 1rem;
    opacity: 0.9;
  }

  .job-description-text {
    color: var(--job-text-muted);
    font-size: 0.8125rem;
    line-height: 1.65;
    margin: 0 0 16px 0;
  }

  .price-box {
    background: linear-gradient(180deg, #ecfdf5 0%, #f0fdf4 100%);
    border: 1px solid rgba(5, 150, 105, 0.22);
    border-radius: var(--job-radius-sm);
    padding: 12px 14px;
    margin-bottom: 16px;
  }

  .price-box-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
  }

  .price-box-icon {
    font-size: 1rem;
  }

  .price-box-label {
    font-size: 0.5625rem;
    font-weight: 700;
    color: var(--job-primary-dark);
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }

  .price-box-amount {
    font-size: 1.125rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    color: var(--job-primary-dark);
    margin: 0 0 6px 0;
    letter-spacing: -0.02em;
  }

  .price-box-note {
    color: var(--job-text-muted);
    font-size: 0.6875rem;
    margin: 0;
    line-height: 1.5;
  }

  .price-box-agreed {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(5, 150, 105, 0.18);
  }
  .price-box-agreed-label {
    font-size: 0.5625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--job-primary-dark);
  }
  .price-box-agreed-amt {
    font-size: 0.9375rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    color: var(--job-text);
    margin-top: 3px;
  }

  .price-warning {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: var(--job-radius-sm);
    padding: 8px 10px;
    margin-top: 10px;
    color: #b91c1c;
    font-size: 0.6875rem;
    font-weight: 600;
    display: flex;
    align-items: flex-start;
    gap: 6px;
    line-height: 1.45;
  }

  /* Info List */
  .info-list {
    display: grid;
    gap: 8px;
  }

  .info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: var(--job-glass-light);
    border-radius: var(--job-radius-sm);
    border: 1px solid var(--job-glass-border);
    transition: border-color 0.2s ease, background 0.2s ease;
  }

  .info-item:hover {
    border-color: rgba(5, 150, 105, 0.35);
    background: #fff;
  }

  .info-item-icon {
    width: 36px;
    height: 36px;
    background: var(--job-gradient-1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
  }

  .info-item-content {
    flex: 1;
  }

  .info-item-label {
    font-size: 0.5625rem;
    color: var(--job-text-dim);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 2px;
  }

  .info-item-value {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--job-text);
    line-height: 1.35;
  }

  .chat-box {
    background: #f0fdf4;
    border: 1px solid rgba(5, 150, 105, 0.2);
    border-radius: var(--job-radius-sm);
    padding: 12px 14px;
    margin-bottom: 14px;
  }

  .chat-box-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
  }

  .chat-box-icon {
    font-size: 1rem;
  }

  .chat-box-title {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--job-primary-dark);
  }

  .chat-box-text {
    color: var(--job-text-muted);
    font-size: 0.6875rem;
    margin: 0 0 10px 0;
    line-height: 1.5;
  }

  .chat-users {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }

  .tip-box {
    background: #fffbeb;
    border: 1px solid rgba(245, 158, 11, 0.35);
    border-radius: var(--job-radius-sm);
    padding: 12px 14px;
    margin-bottom: 14px;
  }

  .tip-box-header {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
    color: #b45309;
    font-weight: 700;
    font-size: 0.6875rem;
  }

  .tip-box-text {
    color: #92400e;
    font-size: 0.6875rem;
    margin: 0;
    line-height: 1.55;
  }

  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 14px;
    border-radius: var(--job-radius-sm);
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
    border: none;
    cursor: pointer;
    font-size: 0.75rem;
  }

  .btn-primary {
    background: var(--job-gradient-1);
    color: white;
    box-shadow: 0 2px 8px var(--job-primary-glow);
  }

  .btn-primary:hover {
    transform: translateY(-1px);
    filter: brightness(1.03);
  }

  .btn-success {
    background: var(--job-gradient-success);
    color: white;
    box-shadow: 0 2px 8px var(--job-success-glow);
  }

  .btn-success:hover {
    transform: translateY(-1px);
    filter: brightness(1.03);
  }

  .btn-danger {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
  }

  .btn-danger:hover {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
    transform: translateY(-1px);
  }

  .btn-full {
    width: 100%;
  }

  /* ============================================
   * MAP SECTION
   * ============================================ */
  .map-card {
    background: var(--job-glass);
    border-radius: var(--job-radius);
    overflow: hidden;
    border: 1px solid var(--job-glass-border);
    box-shadow: var(--job-shadow);
  }

  .map-container {
    height: 160px;
    width: 100%;
  }

  @media (min-width: 640px) {
    .map-container { height: 180px; }
  }

  .map-info {
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .map-info-icon {
    width: 36px;
    height: 36px;
    background: var(--job-gradient-1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
  }

  .map-info-text h4 {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--job-text);
    margin: 0 0 2px 0;
  }

  .map-info-text p {
    font-size: 0.6875rem;
    color: var(--job-text-muted);
    margin: 0;
    line-height: 1.4;
  }

  .comments-section {
    background: var(--job-glass);
    border-radius: var(--job-radius);
    padding: 14px 16px;
    border: 1px solid var(--job-glass-border);
    box-shadow: var(--job-shadow);
  }

  .comments-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 8px;
  }

  .comments-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--job-text);
    display: flex;
    align-items: center;
    gap: 6px;
    letter-spacing: -0.01em;
  }

  .comments-count {
    background: var(--job-glass-light);
    color: var(--job-primary-dark);
    padding: 3px 9px;
    border-radius: 999px;
    font-size: 0.625rem;
    font-weight: 700;
    border: 1px solid rgba(5, 150, 105, 0.2);
  }

  .comment-form {
    background: var(--job-glass-light);
    border-radius: var(--job-radius-sm);
    padding: 12px 14px;
    margin-bottom: 14px;
    border: 1px solid var(--job-glass-border);
  }

  .form-group {
    margin-bottom: 12px;
  }

  .form-label {
    display: block;
    font-size: 0.6875rem;
    font-weight: 600;
    color: var(--job-text);
    margin-bottom: 6px;
  }

  .form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--job-glass-border);
    border-radius: var(--job-radius-sm);
    font-size: 0.8125rem;
    min-height: 88px;
    resize: vertical;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    background: var(--job-glass);
    color: var(--job-text);
    line-height: 1.5;
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
    padding: 9px 11px;
    border: 1px solid var(--job-glass-border);
    border-radius: var(--job-radius-sm);
    font-size: 0.8125rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
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
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--job-text);
    cursor: pointer;
  }

  .comments-list {
    display: grid;
    gap: 10px;
  }

  .comment-item {
    background: var(--job-glass-light);
    border-radius: var(--job-radius-sm);
    padding: 12px 14px;
    border: 1px solid var(--job-glass-border);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .comment-item:hover {
    border-color: rgba(5, 150, 105, 0.25);
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
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
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: var(--job-gradient-1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    color: white;
  }

  .comment-author-name {
    font-size: 0.8125rem;
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
    line-height: 1.55;
    margin-bottom: 10px;
    font-size: 0.75rem;
  }

  .comment-bid {
    background: #f5f3ff;
    border: 1px solid rgba(99, 102, 241, 0.22);
    border-radius: var(--job-radius-sm);
    padding: 8px 11px;
    margin-top: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
  }

  .comment-bid-label {
    font-size: 0.5625rem;
    font-weight: 700;
    color: #5b21b6;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .comment-bid-amount {
    font-size: 0.9375rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    color: var(--job-primary-dark);
  }

  .comment-actions {
    margin-top: 18px;
  }

  .empty-comments {
    text-align: center;
    padding: 28px 16px;
    color: var(--job-text-dim);
  }

  .empty-comments-icon {
    font-size: 2rem;
    margin-bottom: 8px;
    opacity: 0.45;
  }

  .empty-comments-text {
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.5;
    max-width: 280px;
    margin: 0 auto;
  }

  @media (max-width: 768px) {
    .job-hero-image-container {
      height: min(34vh, 240px);
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
          @case('open') 📋 Wazi - Inapokea Maombi @break
          @case('awaiting_payment') 💳 Inasubiri Malipo @break
          @case('funded') 💳 Imefadhiliwa @break
          @case('in_progress') ⚡ Inaendelea @break
          @case('submitted') 📋 Imewasilishwa @break
          @case('completed') ✅ Imekamilika @break
          @case('disputed') ⚠️ Mgogoro @break
          @case('cancelled') 🚫 Imefutwa @break
          @case('refunded') 💸 Imerudishwa @break
          @case('expired') ⏰ Imepitwa @break
          @case('posted') 📋 Imechapishwa @break
          @case('assigned') 👷 Imepewa Mfanyakazi @break
          @case('pending_payment') ⏳ Inasubiri Malipo @break
          @default {{ ucfirst(str_replace('_', ' ', $job->status)) }}
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

    @auth
      @if(auth()->user()->role === 'mfanyakazi' && (int) auth()->id() !== (int) $job->user_id && $job->status === \App\Models\Job::S_OPEN)
        @php
          $heroWorkerApp = $job->applications->first(fn ($a) => (int) $a->worker_id === (int) auth()->id() && ! in_array($a->status, ['withdrawn', 'rejected'], true));
        @endphp
        @unless($heroWorkerApp)
          <div class="job-worker-apply-bar">
            <p>Kazi hii inapokea maombi. Bonyeza <strong>Omba kazi</strong> chini — baada ya kuwasilisha utaelekezwa kwenye <strong>Maombi yangu</strong> kufuatilia hali.</p>
            <a href="#worker-apply-form">✋ Omba kazi</a>
          </div>
        @endunless
      @endif
    @endauth

    <!-- DETAILS GRID -->
    <div class="job-details-grid">
      
      <!-- Main Content -->
      <div>
        <!-- Description Card -->
        <div class="job-card" style="margin-bottom: 16px;">
          <h3 class="job-card-title"><span>📋</span> Maelezo ya Kazi</h3>
          
          <p class="job-description-text">
            {{ $job->description ?? 'Hakuna maelezo ya ziada yaliyowekwa kwa kazi hii.' }}
          </p>
          
          <!-- Price Box (bajeti + bei iliyokubaliwa baada ya uchaguzi) -->
          <div class="price-box">
            <div class="price-box-header">
              <span class="price-box-icon">💰</span>
              <span class="price-box-label">
                @auth
                  @if(auth()->id() === $job->user_id)
                    Bajeti / bei ya tangazo
                  @else
                    Bei ya tangazo (bajeti)
                  @endif
                @else
                  Bei ya tangazo
                @endauth
              </span>
            </div>
            <div class="price-box-amount">{{ number_format($job->price) }} <small style="font-size:0.55em;font-weight:600;opacity:0.8">TZS</small></div>
            @auth
              @if(auth()->id() === $job->user_id && (int) ($job->agreed_amount ?? 0) > 0 && in_array($job->status, ['awaiting_payment', 'funded', 'in_progress', 'submitted', 'completed', 'disputed', 'refunded'], true))
                <div class="price-box-agreed">
                  <div class="price-box-agreed-label">Bei iliyokubaliwa (escrow)</div>
                  <div class="price-box-agreed-amt">{{ number_format($job->agreed_amount) }} TZS</div>
                </div>
              @endif
            @endauth
            <p class="price-box-note">
              @auth
                @if(auth()->id() === $job->user_id)
                  Malipo kwenda kwa mfanyakazi hutoka escrow baada ya kuthibitisha kazi imekamilika kikamilifu.
                @elseif(auth()->user()->role === 'mfanyakazi')
                  Omba kwa bei unayopendekeza. Mteja atachagua, kisha malipo ya escrow yatafanyika kabla ya kuanza kazi rasmi.
                @else
                  Escrow inalinda pande zote mbili hadi kazi kukamilika.
                @endif
              @else
                Ingia kama mfanyakazi kuomba kazi hii kupitia mfumo wa maombi mapya.
              @endauth
            </p>
            @if(auth()->check() && auth()->user()->role === 'mfanyakazi')
              <div class="price-warning">
                ⚠️ Kumbuka: Makato ya 10% (Service Fee) yatakatwa wakati wa malipo.
              </div>
            @endif
          </div>

          @auth
            @php
              $userHasCommented = $job->comments->where('user_id', auth()->id())->isNotEmpty();
              $isAcceptedWorker = (int) auth()->id() === (int) $job->accepted_worker_id;
              $isMuhitaji = (int) auth()->id() === (int) $job->user_id;
              $isClient = $isMuhitaji;
              $isWorker = auth()->user()->role === 'mfanyakazi';
              $isAssignedWorker = $isAcceptedWorker;
              $isSelectedWorker = $isWorker && (int) auth()->id() === (int) $job->selected_worker_id;
              $applications = $job->applications->sortByDesc('created_at');
              $myApplicationAny = $isWorker ? $applications->filter(fn ($a) => (int) $a->worker_id === (int) auth()->id() && $a->status !== 'withdrawn')->sortByDesc('updated_at')->first() : null;
              $myApplication = $myApplicationAny && $myApplicationAny->isActive() ? $myApplicationAny : null;
              $workersForChat = $applications->filter(fn ($a) => $a->worker && ! in_array($a->status, ['withdrawn', 'rejected'], true))->map->worker->unique('id')->values();
              if ($workersForChat->isEmpty()) {
                  foreach ($job->comments->unique('user_id') as $c) {
                      if ($c->user && $c->user->role === 'mfanyakazi') {
                          $workersForChat->push($c->user);
                      }
                  }
                  $workersForChat = $workersForChat->unique('id')->values();
              }
              $canSelectApplication = $isClient && $job->status === \App\Models\Job::S_OPEN;
            @endphp

            {{-- Mtiririko — mteja --}}
            @if($isClient)
              <div class="job-flow-banner" id="flow-anchor">
                <div class="job-flow-banner-kicker">Wewe ni mteja</div>
                @if($job->status === \App\Models\Job::S_OPEN)
                  <p class="job-flow-banner-title">Kazi inapokea maombi. Chagua mfanyakazi halafu lipia escrow.</p>
                  <ul class="job-flow-steps">
                    <li class="is-current"><span class="step-idx">1</span><span><strong>Wapokee maombi</strong> — angalia sehemu &ldquo;Maombi ya wafanyakazi&rdquo; hapa chini.</span></li>
                    <li><span class="step-idx">2</span><span><strong>Chagua</strong> mfanyakazi (au counter / shortlist / kataa).</span></li>
                    <li><span class="step-idx">3</span><span><strong>Lipa</strong> kiasi kilichokubaliwa kwenye escrow.</span></li>
                    <li><span class="step-idx">4</span><span>Mfanyakazi <strong>anakubali na kufanya</strong> kazi, kisha wewe <strong>unathibitisha</strong>.</span></li>
                  </ul>
                  <p style="margin:12px 0 0;font-size:0.8rem;color:var(--job-text-muted);">Orodha ya maombi yote: <a href="{{ route('my.applications') }}" style="color:var(--job-primary);font-weight:700;">Maombi yangu</a></p>
                @elseif($job->status === \App\Models\Job::S_AWAITING_PAYMENT)
                  <p class="job-flow-banner-title">Umeweka mfanyakazi — sasa tumia escrow kulipa ili kazi ianze.</p>
                  <ul class="job-flow-steps">
                    <li class="is-done"><span class="step-idx">✓</span><span>Maombi na uchaguzi</span></li>
                    <li class="is-current"><span class="step-idx">!</span><span><strong>Lipa escrow</strong> (wallet au malipo ya nje).</span></li>
                    <li><span class="step-idx">3</span><span>Mfanyakazi akubali kuanza kazi</span></li>
                    <li><span class="step-idx">4</span><span>Malipo baada ya uthibitisho</span></li>
                  </ul>
                @elseif(in_array($job->status, [\App\Models\Job::S_FUNDED, \App\Models\Job::S_IN_PROGRESS, \App\Models\Job::S_SUBMITTED], true))
                  <p class="job-flow-banner-title">Kazi iko kwenye utekelezaji au ukaguzi.</p>
                  <ul class="job-flow-steps">
                    <li class="is-done"><span class="step-idx">✓</span><span>Malipo ya escrow</span></li>
                    <li class="{{ $job->status === \App\Models\Job::S_FUNDED ? 'is-current' : 'is-done' }}"><span class="step-idx">{{ $job->status === \App\Models\Job::S_FUNDED ? '!' : '✓' }}</span><span>Mfanyakazi anakubali / anafanya kazi</span></li>
                    <li class="{{ $job->status === \App\Models\Job::S_IN_PROGRESS ? 'is-current' : ($job->status === \App\Models\Job::S_SUBMITTED ? 'is-done' : '') }}"><span class="step-idx">3</span><span>Wasilisho la kazi</span></li>
                    <li class="{{ $job->status === \App\Models\Job::S_SUBMITTED ? 'is-current' : '' }}"><span class="step-idx">4</span><span><strong>Thibitisha</strong> au omba marekebisho</span></li>
                  </ul>
                  @if($job->status === \App\Models\Job::S_IN_PROGRESS && $job->completion_code)
                    <p style="margin:14px 0 0;font-size:0.82rem;color:var(--job-text-muted);line-height:1.5;">🔑 <strong style="color:var(--job-text);">Hatua yako sasa:</strong> mpe mfanyakazi <strong>nambari ya kukamilisha</strong> (imeonyeshwa kwenye kisanduku cha kijani hapo chini) anapomaliza kazi; ataiingiza anapowasilisha, kisha utathibitisha au kuomba marekebisho.</p>
                  @endif
                @elseif($job->status === \App\Models\Job::S_COMPLETED)
                  <p class="job-flow-banner-title">Kazi imekamilika. Asante kwa kutumia TendaPoa.</p>
                @else
                  <p class="job-flow-banner-title">Hali ya kazi: {{ str_replace('_', ' ', $job->status) }}. Fuata maelezo hapa chini.</p>
                @endif
              </div>
            @elseif($isWorker && ! $isClient)
              <div class="job-flow-banner" id="flow-anchor">
                <div class="job-flow-banner-kicker">Wewe ni mfanyakazi</div>
                @if($job->status === \App\Models\Job::S_OPEN && ! $myApplication)
                  <p class="job-flow-banner-title">Kazi iko wazi — wasilisha ombi lako na bei.</p>
                  <ul class="job-flow-steps">
                    <li class="is-current"><span class="step-idx">1</span><span><strong>Wasilisha ombi</strong> (bei + ujumbe).</span></li>
                    <li><span class="step-idx">2</span><span>Subiri mteja achague / counter.</span></li>
                    <li><span class="step-idx">3</span><span>Baada ya malipo ya escrow, <strong>kubali</strong> kuanza kazi.</span></li>
                    <li><span class="step-idx">4</span><span>Fanya kazi → wasilisha → pokea malipo baada ya uthibitisho.</span></li>
                  </ul>
                @elseif($myApplication && $job->status === \App\Models\Job::S_OPEN)
                  <p class="job-flow-banner-title">Ombi lako limewasilishwa. Fuatilia hali kwenye <a href="{{ route('mfanyakazi.applications') }}" style="color:#a7f3d0;text-decoration:underline;font-weight:800;">Maombi yangu</a> au hapa chini.</p>
                  <ul class="job-flow-steps">
                    <li class="is-done"><span class="step-idx">✓</span><span>Ombi limewasilishwa</span></li>
                    <li class="is-current"><span class="step-idx">!</span><span>Ikiwa kuna counter, <strong>kubali</strong> au ondoe ombi.</span></li>
                    <li><span class="step-idx">3</span><span>Malipo ya escrow kisha kuanza kazi</span></li>
                  </ul>
                @elseif($isSelectedWorker && $job->status === \App\Models\Job::S_AWAITING_PAYMENT)
                  <p class="job-flow-banner-title">Umechaguliwa. Subiri mteja alipe escrow — utaarifiwa.</p>
                  <ul class="job-flow-steps">
                    <li class="is-done"><span class="step-idx">✓</span><span>Umechaguliwa</span></li>
                    <li class="is-current"><span class="step-idx">!</span><span><strong>Subiri malipo</strong> ya escrow kutoka kwa mteja.</span></li>
                    <li><span class="step-idx">3</span><span>Kubali kuanza kazi baada ya malipo</span></li>
                  </ul>
                @elseif($isAssignedWorker && in_array($job->status, [\App\Models\Job::S_FUNDED, \App\Models\Job::S_IN_PROGRESS, \App\Models\Job::S_SUBMITTED], true))
                  <p class="job-flow-banner-title">Fuata hatua kwenye kisanduku cha vitendo hapa chini.</p>
                  <ul class="job-flow-steps">
                    <li class="is-done"><span class="step-idx">✓</span><span>Malipo / utelezaji</span></li>
                    <li class="{{ $job->status === \App\Models\Job::S_FUNDED ? 'is-current' : 'is-done' }}"><span class="step-idx">{{ $job->status === \App\Models\Job::S_FUNDED ? '!' : '✓' }}</span><span>Kubali na anza / fanya kazi</span></li>
                    <li class="{{ $job->status === \App\Models\Job::S_IN_PROGRESS ? 'is-current' : '' }}"><span class="step-idx">3</span><span>Wasilisha kazi ukiisha</span></li>
                    <li class="{{ $job->status === \App\Models\Job::S_SUBMITTED ? 'is-current' : '' }}"><span class="step-idx">4</span><span>Subiri mteja athibitishe</span></li>
                  </ul>
                @elseif($job->status === \App\Models\Job::S_COMPLETED)
                  <p class="job-flow-banner-title">Kazi imekamilika.</p>
                @elseif($job->status === 'posted' && ! $myApplication)
                  <p class="job-flow-banner-title">Kazi hii inatumia mfumo wa zamani wa maoni (chini). Kazi mpya hutumia maombi ya wazi.</p>
                @else
                  <p class="job-flow-banner-title">Fuata taarifa na vitendo hapa chini.</p>
                @endif
              </div>
            @endif

            {{-- Chat: mteja — kutoka maombi mapya au maoni ya zamani --}}
            @if($isMuhitaji && $workersForChat->isNotEmpty())
              <div class="chat-box">
                <div class="chat-box-header">
                  <span class="chat-box-icon">💬</span>
                  <span class="chat-box-title">Mazungumzo na wafanyakazi</span>
                </div>
                <p class="chat-box-text">Piga gumzo na mfanyakazi aliyeomba au aliyechangia kwenye kazi hii.</p>
                <div class="chat-users">
                  @foreach($workersForChat as $chatWorker)
                    <a href="{{ route('chat.show', ['job' => $job, 'worker_id' => $chatWorker->id]) }}" class="btn btn-success">
                      💬 {{ $chatWorker->name }}
                    </a>
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Chat: mfanyakazi --}}
            @if($isWorker && ! $isMuhitaji && ($userHasCommented || $isAcceptedWorker || $isSelectedWorker || ($myApplication && $myApplication->isActive())))
              <div class="chat-box">
                <div class="chat-box-header">
                  <span class="chat-box-icon">💬</span>
                  <span class="chat-box-title">Mazungumzo na mteja</span>
                </div>
                <p class="chat-box-text">Mteja: <strong>{{ $job->muhitaji->name }}</strong></p>
                <a href="{{ route('chat.show', $job) }}" class="btn btn-success">
                  💬 Fungua mazungumzo
                </a>
              </div>
            @endif

            {{-- ============================================================ --}}
            {{-- Vitendo vya mfumo mpya (status + jukumu) --}}
            {{-- ============================================================ --}}

            {{-- === OPEN: Worker apply form === --}}
            @if($isWorker && ! $isClient && $job->status === \App\Models\Job::S_OPEN && ! $myApplication)
              <div id="worker-apply-form" class="job-action-panel job-action-panel--apply">
                <h4>✋ Omba kufanya kazi hii</h4>
                <p class="lead">Baada ya kuwasilisha utapelekwa kwenye <a href="{{ route('mfanyakazi.applications') }}" style="color:var(--job-primary);font-weight:800;">Maombi yangu</a> kufuatilia hali (counter, kuchaguliwa, n.k.). Hapa hutaona maombi ya wafanyakazi wengine.</p>
                <form method="POST" action="{{ route('jobs.apply', $job) }}">
                  @csrf
                  <div class="form-group">
                    <label class="form-label">💵 Bei unayopendekeza (TZS)</label>
                    <input type="number" name="proposed_amount" class="form-input" placeholder="10000" min="1000" step="500" required value="{{ old('proposed_amount', $job->price) }}" style="font-weight: 700; font-size: 1.1rem;">
                    <p style="font-size: 0.8rem; color: var(--job-text-dim); margin-top: 6px;">Bajeti ya mteja: <strong style="color: #10b981;">{{ number_format($job->price) }} TZS</strong></p>
                  </div>
                  <div class="form-group">
                    <label class="form-label">📝 Ujumbe — kwa nini uchaguliwe?</label>
                    <textarea name="message" class="form-textarea" rows="3" required placeholder="Mimi ni mtaalamu wa... na nina uzoefu wa..."></textarea>
                  </div>
                  <div class="form-group">
                    <label class="form-label">⏱️ Muda wa kukamilisha (hiari)</label>
                    <input type="text" name="eta_text" class="form-input" placeholder="Mfano: siku 2" value="{{ old('eta_text') }}">
                  </div>
                  <button type="submit" class="btn btn-success btn-full">✋ Wasilisha ombi</button>
                </form>
              </div>
            @endif

            {{-- === Worker already applied === --}}
            @if($isWorker && $myApplicationAny)
              <div class="job-action-panel job-action-panel--status">
                @if($myApplicationAny->status === \App\Models\JobApplication::STATUS_REJECTED)
                  <h4 style="margin:0 0 8px;">📋 Ombi lako</h4>
                  <p style="margin:0 0 10px;font-size:0.8rem;"><a href="{{ route('mfanyakazi.applications') }}" style="color:var(--job-primary);font-weight:800;">→ Maombi yangu</a></p>
                  <p class="lead" style="color:var(--job-text-muted);">Ombi lako halijakubaliwa kwenye kazi hii. Unaweza kuomba tena hapa chini ikiwa kazi bado wazi.</p>
                @else
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                  <h4 style="margin:0;">📋 Ombi lako</h4>
                  <span style="background: rgba(99, 102, 241, 0.2); color: #4f46e5; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700;">{{ $myApplicationAny->getStatusLabel() }}</span>
                </div>
                <p style="margin:0 0 10px;font-size:0.8rem;"><a href="{{ route('mfanyakazi.applications') }}" style="color:var(--job-primary);font-weight:800;">→ Maombi yangu</a> (orodha yako — huioni maombi ya wengine)</p>
                <p class="lead" style="margin-bottom:8px;">Uliopendekeza: <strong>TZS {{ number_format($myApplicationAny->proposed_amount) }}</strong></p>
                @if($myApplicationAny->status === \App\Models\JobApplication::STATUS_SELECTED)
                  <p class="lead" style="color:#047857;font-weight:700;">🎉 Mteja amekuchagua. Fuata hatua za malipo ya escrow hapa chini — hii ndiyo kazi yako sasa.</p>
                @endif
                @if($myApplication && $myApplication->isCountered())
                  <div style="margin-top: 12px; padding: 14px; background: rgba(168, 85, 247, 0.12); border-radius: 12px; border: 1px solid rgba(168, 85, 247, 0.35);">
                    <div style="color: #6d28d9; font-weight: 700; font-size: 0.88rem;">🔄 Counter offer ya mteja: TZS {{ number_format($myApplication->counter_amount) }}</div>
                    @if($myApplication->client_response_note)
                      <p style="color: var(--job-text-muted); font-size: 0.85rem; margin: 8px 0 0;">{{ $myApplication->client_response_note }}</p>
                    @endif
                    <p style="font-size: 0.78rem; color: var(--job-text-dim); margin: 8px 0 0;">Baada ya kukubali, mteja ataweza kukuchagua kisha kufanya malipo ya escrow.</p>
                    <form method="POST" action="{{ route('applications.accept-counter', [$job, $myApplication]) }}" style="margin-top: 12px;">
                      @csrf
                      <button type="submit" class="btn btn-success btn-full">✅ Kubali counter offer</button>
                    </form>
                  </div>
                @endif
                @if($myApplication && $job->status === \App\Models\Job::S_OPEN)
                  <form method="POST" action="{{ route('applications.withdraw', [$job, $myApplication]) }}" style="margin-top: 12px;">
                    @csrf
                    <button type="button" class="btn btn-danger btn-full" style="opacity: 0.75; font-size: 0.85rem;" onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Ondoa ombi lako?'):Promise.resolve(confirm('Ondoa ombi lako?'))).then(function(ok){ if(ok) f.submit(); });">❌ Ondoa ombi</button>
                  </form>
                @endif
                @endif
              </div>
            @endif

            {{-- === AWAITING PAYMENT: Client funding CTA === --}}
            @if($isClient && $job->status === \App\Models\Job::S_AWAITING_PAYMENT)
              <div class="job-action-panel job-action-panel--pay">
                <div style="font-size: 2rem; margin-bottom: 8px;">💳</div>
                <h4>Lipa escrow ili kuanza kazi</h4>
                <p class="lead">Umemchagua <strong>{{ $job->selectedWorker->name ?? 'mfanyakazi' }}</strong>. Weka TZS <strong>{{ number_format($job->agreed_amount) }}</strong> kwenye escrow. Mfanyakazi ataarifiwa baada ya malipo.</p>
                <a href="{{ route('jobs.fund', $job) }}" class="btn btn-success btn-full" style="font-size: 1.05rem;">💰 Fungua ukurasa wa malipo</a>
              </div>
            @endif

            {{-- === CLIENT: Code ya kukamilisha (mfanyakazi anaiingiza anapowasilisha) === --}}
            @if($isClient && $job->completion_code && in_array($job->status, [\App\Models\Job::S_FUNDED, \App\Models\Job::S_IN_PROGRESS], true))
              @php
                $workerForCode = $job->acceptedWorker ?? $job->selectedWorker;
              @endphp
              <div class="job-action-panel job-action-panel--apply" style="border-color: rgba(16, 185, 129, 0.4); background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(5, 150, 105, 0.06));">
                <div style="font-size: 1.75rem; margin-bottom: 6px;">🔑</div>
                <h4>Nambari yako ya kukamilisha kazi</h4>
                <p class="lead" style="margin-bottom: 12px;">
                  Hii si nenosiri la akaunti — ni <strong>nambari ya uthibitisho</strong> pekee. Mpe mfanyakazi <strong>{{ $workerForCode->name ?? 'aliyechaguliwa' }}</strong> baada ya kufanya kazi vizuri; ataingiza kwenye fomu ya <em>Wasilisha kazi</em> ili kukamilisha mchakato.
                </p>
                <button type="button" onclick="tpCopyJobCode('{{ $job->completion_code }}')" class="btn btn-full" style="font-family: ui-monospace, monospace; font-size: 1.35rem; font-weight: 800; letter-spacing: 0.35em; background: rgba(255,255,255,0.95); color: #047857; border: 2px solid rgba(16, 185, 129, 0.45); padding: 14px;">
                  {{ $job->completion_code }}
                </button>
                <p class="lead" style="margin-top: 10px; font-size: 0.78rem; color: var(--job-text-muted);">Bonyeza nambari kunakili. Unaweza pia kuiona kwenye <a href="{{ route('my.jobs') }}" style="color: var(--job-primary); font-weight: 700;">Kazi zangu</a>.</p>
              </div>
            @endif

            {{-- === AWAITING PAYMENT: Selected worker waiting === --}}
            @if($isSelectedWorker && $job->status === \App\Models\Job::S_AWAITING_PAYMENT)
              <div class="job-action-panel job-action-panel--pay">
                <div style="font-size: 2rem; margin-bottom: 8px;">⏳</div>
                <h4>Subiri malipo ya escrow</h4>
                <p class="lead">Mteja amekuchagua kwa <strong>TZS {{ number_format($job->agreed_amount) }}</strong>. Bado anahitaji kulipa kabla ya kazi kuanza rasmi. Utapata taarifa mteja akimaliza.</p>
              </div>
            @endif

            {{-- === FUNDED: Worker accept/decline === --}}
            @if($isAssignedWorker && $job->status === \App\Models\Job::S_FUNDED)
              <div class="job-action-panel job-action-panel--apply">
                <h4>💰 Escrow imewekwa</h4>
                <p class="lead">TZS {{ number_format($job->escrow_amount ?? $job->agreed_amount) }} zimehifadhiwa. <strong>Kubali</strong> kuanza kazi au <strong>kataa</strong> (malipo yatarudi kwa mteja).</p>
                <div style="display: grid; gap: 10px;">
                  <form method="POST" action="{{ route('jobs.worker.accept', $job) }}">@csrf<button type="submit" class="btn btn-success btn-full">✅ Kubali na anza kazi</button></form>
                  <form method="POST" action="{{ route('jobs.worker.decline', $job) }}">@csrf<button type="button" class="btn btn-danger btn-full" onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Kataa kazi? Malipo yatarudishwa kwa muhitaji.'):Promise.resolve(confirm('Kataa kazi? Malipo yatarudishwa kwa muhitaji.'))).then(function(ok){ if(ok) f.submit(); });">❌ Kataa kazi</button></form>
                </div>
              </div>
            @endif

            {{-- === IN PROGRESS: Worker submit completion === --}}
            @if($isAssignedWorker && $job->status === \App\Models\Job::S_IN_PROGRESS)
              <div class="job-action-panel job-action-panel--progress">
                <h4>📋 Wasilisha kazi ukiisha</h4>
                <form method="POST" action="{{ route('jobs.worker.submit', $job) }}">
                  @csrf
                  <div class="form-group">
                    <label class="form-label">📝 Maelezo ya kukamilika (hiari)</label>
                    <textarea name="notes" class="form-textarea" rows="2" placeholder="Kazi imekamilika kama ilivyoagizwa..."></textarea>
                  </div>
                  @if($job->completion_code)
                    <div class="form-group">
                      <label class="form-label">🔑 Code ya Kukamilisha (muulize muhitaji)</label>
                      <input type="text" name="code" class="form-input" maxlength="6" placeholder="123456">
                    </div>
                  @endif
                  <button type="submit" class="btn btn-primary btn-full">📤 Wasilisha Kazi</button>
                </form>
                <form method="POST" action="{{ route('jobs.worker.dispute', $job) }}" style="margin-top: 10px;">
                  @csrf
                  <input type="hidden" name="reason" id="worker-dispute-reason-ip">
                  <button type="button" class="btn btn-danger btn-full" style="opacity: 0.6; font-size: 0.8rem;" onclick="var b=this; (typeof tpPrompt==='function'?tpPrompt('Sababu ya mgogoro:',''):new Promise(function(res){ var x=prompt('Sababu ya mgogoro:'); res(x&&x.trim()?x.trim():null); })).then(function(r){ if(!r) return; var f=b.closest('form'); var i=f.querySelector('#worker-dispute-reason-ip'); if(i) i.value=r; f.submit(); });">⚠️ Fungua Mgogoro</button>
                </form>
              </div>
            @endif

            {{-- === SUBMITTED: Client confirm/revision/dispute === --}}
            @if($isClient && $job->status === \App\Models\Job::S_SUBMITTED)
              <div class="job-action-panel job-action-panel--progress">
                <h4>📋 Mfanyakazi amewasilisha kazi</h4>
                <p class="lead">Hakiki kazi, thibitisha ili malipo yatoke escrow, omba marekebisho, au fungua mgogoro ikiwa kuna tatizo.</p>
                @if($job->auto_release_at)
                  <p style="color: #fcd34d; font-size: 0.8rem; margin-bottom: 12px;">⏱️ Malipo yatatumwa moja kwa moja {{ $job->auto_release_at->diffForHumans() }} kama hukujibu.</p>
                @endif
                <div style="display: grid; gap: 10px;">
                  <form method="POST" action="{{ route('jobs.client.confirm', $job) }}">@csrf<button type="button" class="btn btn-success btn-full" onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Thibitisha kazi imekamilika? Malipo yatatumwa kwa mfanyakazi.'):Promise.resolve(confirm('Thibitisha kazi imekamilika? Malipo yatatumwa kwa mfanyakazi.'))).then(function(ok){ if(ok) f.submit(); });">✅ Thibitisha - Tuma Malipo</button></form>
                  <div style="background: var(--job-glass-light); padding: 14px; border-radius: 12px; border: 1px solid var(--job-glass-border);">
                    <div style="font-weight: 600; color: var(--job-text); margin-bottom: 8px; font-size: 0.9rem;">🔄 Omba Marekebisho</div>
                    <form method="POST" action="{{ route('jobs.client.revision', $job) }}">
                      @csrf
                      <input type="text" name="reason" class="form-input" placeholder="Sababu ya marekebisho..." required style="margin-bottom: 8px;">
                      <button type="submit" class="btn btn-full" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white;">📤 Omba Marekebisho</button>
                    </form>
                  </div>
                  <form method="POST" action="{{ route('jobs.client.dispute', $job) }}">
                    @csrf
                    <input type="hidden" name="reason" id="client-dispute-reason">
                    <button type="button" class="btn btn-danger btn-full" style="opacity: 0.7;" onclick="var b=this; (typeof tpPrompt==='function'?tpPrompt('Sababu ya mgogoro:',''):new Promise(function(res){ var x=prompt('Sababu ya mgogoro:'); res(x&&x.trim()?x.trim():null); })).then(function(r){ if(!r) return; var f=b.closest('form'); var i=f.querySelector('#client-dispute-reason'); if(i) i.value=r; f.submit(); });">⚠️ Fungua Mgogoro</button>
                  </form>
                </div>
              </div>
            @endif

            {{-- === SUBMITTED: Worker waiting for client === --}}
            @if($isAssignedWorker && $job->status === \App\Models\Job::S_SUBMITTED)
              <div class="job-action-panel job-action-panel--status">
                <h4>⏳ Subiri mteja ahakiki</h4>
                <p class="lead">Umewasilisha kazi. Mteja anaweza kuthibitisha, kuomba marekebisho, au kufungua mgogoro. Utapata taarifa mabadiliko yakitokea.</p>
                @if($job->submitted_at)
                  <p style="margin:0;font-size:0.8rem;color:var(--job-text-dim);">Wasilisho: {{ $job->submitted_at->diffForHumans() }}</p>
                @endif
              </div>
            @endif

            {{-- === COMPLETED: Review form === --}}
            @if($job->status === \App\Models\Job::S_COMPLETED && ($isClient || $isAssignedWorker))
              @php $existingReview = $job->reviews()->where('reviewer_id', auth()->id())->first(); @endphp
              @if(!$existingReview)
                <div class="job-action-panel job-action-panel--apply">
                  <h4>⭐ Toa tathmini</h4>
                  <p class="lead">Msaada wako unatusaidia kuendeleza uaminifu kwenye jukwaa.</p>
                  <form method="POST" action="{{ route('jobs.review', $job) }}">
                    @csrf
                    <div class="form-group">
                      <label class="form-label">Rating (1-5)</label>
                      <div style="display: flex; gap: 8px;">
                        @for($i = 1; $i <= 5; $i++)
                          <label style="cursor: pointer; font-size: 1.8rem; opacity: 0.5; transition: opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="if(!this.querySelector('input').checked)this.style.opacity=0.5">
                            <input type="radio" name="rating" value="{{ $i }}" required style="display: none;" onchange="this.closest('.form-group').querySelectorAll('label').forEach((l,idx)=>{l.style.opacity=idx<{{ $i }}?'1':'0.5'})">⭐
                          </label>
                        @endfor
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Maoni (hiari)</label>
                      <textarea name="comment" class="form-textarea" rows="2" placeholder="Kazi nzuri!..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-full">📤 Tuma Review</button>
                  </form>
                </div>
              @else
                <div style="background: var(--job-glass-light); border-radius: 14px; padding: 16px; margin-bottom: 24px; border: 1px solid var(--job-glass-border);">
                  <div style="color: #6ee7b7; font-weight: 600; font-size: 0.9rem;">✅ Umeshaweka review ({{ $existingReview->rating }}/5)</div>
                </div>
              @endif
            @endif

            {{-- === OPEN / posted: Client tip === --}}
            @if($isClient && $job->status === \App\Models\Job::S_OPEN)
              <div class="tip-box">
                <div class="tip-box-header"><span>💡</span> Kidokezo</div>
                <p class="tip-box-text">Maombi yote yako hapa chini. Baada ya kumchagua mfanyakazi, utaelekezwa kulipa escrow kabla ya kuanza kazi.</p>
              </div>
            @elseif($isClient && $job->status === 'posted')
              <div class="tip-box">
                <div class="tip-box-header"><span>💡</span> Mfumo wa zamani</div>
                <p class="tip-box-text">Kazi hii bado inatumia maoni ya zamani (sehemu &ldquo;Maoni&rdquo; chini). Kazi mpya hutumia maombi ya wazi hapo juu.</p>
              </div>
            @endif

            {{-- Cancel Button --}}
            @if($isClient && in_array($job->status, [\App\Models\Job::S_OPEN, 'posted', 'pending_payment'], true))
              <form action="{{ route('jobs.cancel', $job) }}" method="POST" id="tp-cancel-job-form-{{ $job->id }}">
                @csrf
                <button type="button" class="btn btn-danger btn-full" onclick="var f=document.getElementById('tp-cancel-job-form-{{ $job->id }}'); (typeof tpConfirm==='function'?tpConfirm('Una uhakika unataka kufuta kazi hii?'):Promise.resolve(confirm('Una uhakika unataka kufuta kazi hii?'))).then(function(ok){ if(ok) f.submit(); });">🗑️ Futa kazi</button>
              </form>
            @endif
          @endauth
        </div>

        {{-- ============================================================ --}}
        {{-- Maombi kamili: mteja / admin PEKEE (mfanyakazi haoni maombi ya wengine) --}}
        {{-- ============================================================ --}}
        @auth
        @php
          $employerApplicationsList = $job->applications->sortByDesc('created_at');
          $isJobOwnerApps = (int) auth()->id() === (int) $job->user_id;
          $isAdminApps = auth()->user()->role === 'admin';
          $showEmployerApplicationsList = ($isJobOwnerApps || $isAdminApps) && $employerApplicationsList->count() > 0;
          $canManageAppsHere = $isJobOwnerApps && $job->status === \App\Models\Job::S_OPEN;
        @endphp
        @if($showEmployerApplicationsList)
        <div id="maombi-panel" class="comments-section" style="margin-bottom: 16px;">
          <div class="comments-header">
            <div class="comments-title"><span>✋</span> Maombi ya wafanyakazi</div>
            <div class="comments-count">{{ $employerApplicationsList->count() }} maombi</div>
          </div>
          <p style="margin: 0 0 18px; font-size: 0.88rem; color: var(--job-text-muted); line-height: 1.5;">
            Hapa unaona maombi yote. Unaweza pia kuziangalia kwenye <a href="{{ route('my.applications') }}" style="color:var(--job-primary);font-weight:700;">Maombi yangu</a> (inbox). <strong>Huwezi kumchagua</strong> mfanyakazi mwenye counter hadi akubali bei yako.
          </p>

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

          <div class="comments-list">
            @foreach($employerApplicationsList as $app)
              @php
                $wAvatar = $app->worker->profile_photo_url ?? '';
                if (!$wAvatar) {
                  $wAvatar = 'https://ui-avatars.com/api/?name=' . rawurlencode($app->worker->name) . '&background=e2e8f0&color=475569&size=128';
                }
              @endphp
              <div class="comment-item"
                   style="{{ $app->status === 'selected' ? 'border-color: rgba(16, 185, 129, 0.5); background: rgba(16, 185, 129, 0.05);' : '' }}
                          {{ $app->status === 'rejected' ? 'opacity: 0.5;' : '' }}">
                <div class="comment-header">
                  <div class="comment-author" style="align-items: flex-start;">
                    <img src="{{ $wAvatar }}" alt="" width="48" height="48" class="shrink-0 rounded-xl object-cover" style="width:48px;height:48px;border:1px solid var(--job-glass-border);">
                    <div class="min-w-0">
                      <span class="comment-author-name">{{ $app->worker->name }}</span>
                      <div style="font-size: 0.75rem; color: var(--job-text-dim);">{{ $app->created_at->diffForHumans() }}</div>
                    </div>
                  </div>
                  <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <span style="background: rgba(99, 102, 241, 0.2); color: #a5b4fc; padding: 4px 10px; border-radius: 8px; font-size: 0.7rem; font-weight: 700;">{{ $app->getStatusLabel() }}</span>
                    @if($app->eta_text)
                      <span style="background: rgba(245, 158, 11, 0.2); color: #fcd34d; padding: 4px 10px; border-radius: 8px; font-size: 0.7rem; font-weight: 700;">⏱ {{ $app->eta_text }}</span>
                    @endif
                  </div>
                </div>

                <div class="comment-message">{{ $app->message }}</div>

                {{-- Proposed amount --}}
                <div class="comment-bid">
                  <span class="comment-bid-label">Bei Iliyopendekezwa:</span>
                  <span class="comment-bid-amount" style="{{ $app->proposed_amount < $job->price ? 'color: #fcd34d;' : 'color: #6ee7b7;' }}">
                    {{ number_format($app->proposed_amount) }} TZS
                  </span>
                </div>

                {{-- Counter offer display --}}
                @if($app->isCountered())
                  <div style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.15), rgba(168, 85, 247, 0.05)); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 12px; padding: 14px; margin-top: 14px;">
                    <div style="color: #c4b5fd; font-weight: 700; font-size: 0.85rem;">🔄 Counter Offer: TZS {{ number_format($app->counter_amount) }}</div>
                    @if($app->client_response_note)
                      <p style="color: var(--job-text-muted); font-size: 0.85rem; margin: 4px 0 0;">"{{ $app->client_response_note }}"</p>
                    @endif
                  </div>
                @endif

                {{-- Client actions: kazi lazima iwe wazi; usichague wakati counter bado bila kukubaliwa --}}
                @if($canManageAppsHere && $app->isActive())
                  <div style="display: grid; gap: 10px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--job-glass-border);">
                    @php
                      $canSelectThis = in_array($app->status, [\App\Models\JobApplication::STATUS_APPLIED, \App\Models\JobApplication::STATUS_SHORTLISTED, \App\Models\JobApplication::STATUS_ACCEPTED_COUNTER], true);
                    @endphp
                    @if($app->isCountered())
                      <p style="margin: 0; font-size: 0.82rem; color: var(--job-text-muted); padding: 10px 12px; background: rgba(245, 158, 11, 0.1); border-radius: 10px; border: 1px solid rgba(245, 158, 11, 0.25);">⏳ Subiri <strong>{{ $app->worker->name }}</strong> akubali counter offer ndipo uweze kumchagua.</p>
                    @endif
                    @if($canSelectThis)
                      <form method="POST" action="{{ route('applications.select', [$job, $app]) }}">
                        @csrf
                        <button type="button" class="btn btn-success btn-full" onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm(@json('Chagua '.$app->worker->name.'? Utaelekezwa kulipa escrow.')):Promise.resolve(confirm(@json('Chagua '.$app->worker->name.'? Utaelekezwa kulipa escrow.')))).then(function(ok){ if(ok) f.submit(); });">
                          ✅ Mchague @if($app->status === \App\Models\JobApplication::STATUS_ACCEPTED_COUNTER)(TZS {{ number_format($app->counter_amount) }})@else(TZS {{ number_format($app->proposed_amount) }})@endif
                        </button>
                      </form>
                    @endif

                    @if(! $app->isCountered())
                      <div style="background: var(--job-glass-light); padding: 14px; border-radius: 12px; border: 1px solid var(--job-glass-border);">
                        <div style="font-weight: 600; color: var(--job-text); margin-bottom: 8px; font-size: 0.85rem;">🔄 Counter offer</div>
                        <form method="POST" action="{{ route('applications.counter', [$job, $app]) }}">
                          @csrf
                          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                            <input type="number" name="counter_amount" class="form-input" placeholder="Bei yako" min="1000" required style="font-weight: 600;">
                            <input type="text" name="client_response_note" class="form-input" placeholder="Ujumbe (hiari)">
                          </div>
                          <button type="submit" class="btn btn-full" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; font-size: 0.85rem;">📤 Tuma counter</button>
                        </form>
                      </div>
                    @endif

                    <div style="display: flex; gap: 8px;">
                      @if($app->status !== 'shortlisted' && ! $app->isCountered())
                        <form method="POST" action="{{ route('applications.shortlist', [$job, $app]) }}" style="flex: 1;">
                          @csrf
                          <button type="submit" class="btn btn-full" style="background: rgba(59, 130, 246, 0.2); color: #1d4ed8; border: 1px solid rgba(59, 130, 246, 0.35); font-size: 0.85rem;">⭐ Shortlist</button>
                        </form>
                      @endif
                      <form method="POST" action="{{ route('applications.reject', [$job, $app]) }}" style="flex: 1;">
                        @csrf
                        <button type="button" class="btn btn-danger btn-full" style="opacity: 0.75; font-size: 0.85rem;" onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm(@json('Kataa ombi la '.$app->worker->name.'?')):Promise.resolve(confirm(@json('Kataa ombi la '.$app->worker->name.'?')))).then(function(ok){ if(ok) f.submit(); });">❌ Kataa</button>
                      </form>
                    </div>
                  </div>
                @endif

                {{-- Chat link if selected (mteja na mfanyakazi waliochaguliwa) --}}
                @if($app->status === 'selected' && (auth()->id() === $job->user_id || auth()->id() === $app->worker_id))
                  <a href="{{ route('chat.show', $job) }}" class="btn btn-primary btn-full" style="margin-top: 14px;">💬 Fungua mazungumzo</a>
                @endif
              </div>
            @endforeach
          </div>
        </div>
        @endif
        @endauth

        <!-- Legacy Comments Section (posted / zamani) -->
        <div class="comments-section">
          <div class="job-legacy-label">💬 Historia — mfumo wa maoni wa zamani</div>
          <div class="comments-header">
            <div class="comments-title">
              <span>💬</span> Maoni
            </div>
            <div class="comments-count">
              {{ $job->comments->count() }} Maoni
            </div>
          </div>

          @if (session('success') && $job->applications->count() === 0)
            <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.1)); border-left: 4px solid #10b981; padding: 16px; border-radius: 12px; margin-bottom: 20px; color: #6ee7b7;">
              ✅ {{ session('success') }}
            </div>
          @endif

          @if (session('error') && $job->applications->count() === 0)
            <div style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1)); border-left: 4px solid #ef4444; padding: 16px; border-radius: 12px; margin-bottom: 20px; color: #fca5a5;">
              ❌ {{ session('error') }}
            </div>
          @endif

          <!-- Comment Form for Mfanyakazi (legacy - for posted status backward compat) -->
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
                  <button type="button" class="btn btn-success" onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm('Una uhakika unataka kuongeza pesa kwenye kazi hii?'):Promise.resolve(confirm('Una uhakika unataka kuongeza pesa kwenye kazi hii?'))).then(function(ok){ if(ok) f.submit(); });">
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
                        <button type="button" class="btn btn-success btn-full" onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm(@json('Uma uhakika unataka kumchagua '.$comment->user->name.'?')):Promise.resolve(confirm(@json('Uma uhakika unataka kumchagua '.$comment->user->name.'?')))).then(function(ok){ if(ok) f.submit(); });">
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
                        <button type="button" class="btn btn-danger btn-full" style="opacity: 0.8;" onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm(@json('Una uhakika unataka kumkataa '.$comment->user->name.'?')):Promise.resolve(confirm(@json('Una uhakika unataka kumkataa '.$comment->user->name.'?')))).then(function(ok){ if(ok) f.submit(); });">
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
                <div class="empty-comments-text">
                  @if($job->status === \App\Models\Job::S_OPEN)
                    Hakuna maoni ya zamani hapa. Kazi hii inatumia <strong>maombi</strong> (sehemu ya juu).
                  @else
                    Bado hakuna maoni ya zamani kwenye kazi hii.
                  @endif
                </div>
              </div>
            @endforelse
          </div>
        </div>
      </div>
      
      <!-- Sidebar -->
      <div>
        @auth
          <div class="job-side-flow">
            <h4>🧭 Mtiririko wa kazi</h4>
            <ul class="job-flow-steps">
              <li class="{{ $job->status === \App\Models\Job::S_OPEN ? 'is-current' : (in_array($job->status, [\App\Models\Job::S_AWAITING_PAYMENT, \App\Models\Job::S_FUNDED, \App\Models\Job::S_IN_PROGRESS, \App\Models\Job::S_SUBMITTED, \App\Models\Job::S_COMPLETED], true) ? 'is-done' : '') }}"><span class="step-idx">1</span><span><strong>Maombi</strong> → uchaguzi</span></li>
              <li class="{{ $job->status === \App\Models\Job::S_AWAITING_PAYMENT ? 'is-current' : (in_array($job->status, [\App\Models\Job::S_FUNDED, \App\Models\Job::S_IN_PROGRESS, \App\Models\Job::S_SUBMITTED, \App\Models\Job::S_COMPLETED], true) ? 'is-done' : '') }}"><span class="step-idx">2</span><span><strong>Escrow</strong> (malipo)</span></li>
              <li class="{{ in_array($job->status, [\App\Models\Job::S_FUNDED, \App\Models\Job::S_IN_PROGRESS], true) ? 'is-current' : (in_array($job->status, [\App\Models\Job::S_SUBMITTED, \App\Models\Job::S_COMPLETED], true) ? 'is-done' : '') }}"><span class="step-idx">3</span><span><strong>Utekelezaji</strong></span></li>
              <li class="{{ $job->status === \App\Models\Job::S_SUBMITTED ? 'is-current' : ($job->status === \App\Models\Job::S_COMPLETED ? 'is-done' : '') }}"><span class="step-idx">4</span><span><strong>Uthibitisho</strong></span></li>
              <li class="{{ $job->status === \App\Models\Job::S_COMPLETED ? 'is-current is-done' : '' }}"><span class="step-idx">5</span><span><strong>Malipo</strong> kwa mfanyakazi</span></li>
            </ul>
            <a href="#flow-anchor" style="display:inline-block;margin-top:10px;font-size:0.78rem;font-weight:700;color:var(--job-primary);">↑ Maelezo kamili juu</a>
          </div>
        @endauth
        <!-- Map Card -->
        <div class="map-card" style="margin-bottom: 16px;">
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
                    @case('open') Wazi @break
                    @case('awaiting_payment') Inasubiri Malipo @break
                    @case('funded') 💳 Imefadhiliwa @break
                    @case('in_progress') Inaendelea @break
                    @case('submitted') Imewasilishwa @break
                    @case('completed') Imekamilika @break
                    @case('disputed') Mgogoro @break
                    @case('cancelled') Imefutwa @break
                    @case('refunded') Imerudishwa @break
                    @case('expired') Imepitwa @break
                    @case('posted') Imechapishwa @break
                    @case('assigned') Imekabidhiwa @break
                    @case('pending_payment') Inasubiri Malipo @break
                    @default {{ ucfirst(str_replace('_', ' ', $job->status)) }}
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
              <div class="info-item-icon">✋</div>
              <div class="info-item-content">
                <div class="info-item-label">Maombi</div>
                <div class="info-item-value">{{ $job->applications()->count() ?: $job->comments->where('is_application', true)->count() }} maombi</div>
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
function tpCopyJobCode(text) {
  if (!text) return;
  var s = String(text);
  var done = function () {
    if (typeof tpToast === 'function') tpToast('Nambari imenakiliwa', 'success');
    else {
      var n = document.createElement('div');
      n.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;border-radius:12px;background:#059669;color:#fff;padding:10px 16px;font-size:12px;font-weight:700;box-shadow:0 10px 25px rgba(0,0,0,0.15);';
      n.textContent = 'Code imenakiliwa';
      document.body.appendChild(n);
      setTimeout(function () { n.remove(); }, 2000);
    }
  };
  navigator.clipboard.writeText(s).then(done).catch(function () {
    try {
      var ta = document.createElement('textarea');
      ta.value = s;
      ta.setAttribute('readonly', '');
      ta.style.cssText = 'position:fixed;left:-9999px';
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      ta.remove();
      done();
    } catch (e) {
      if (typeof tpToast === 'function') {
        tpToast('Nakili nambari kwa mkono: ' + s, 'info');
      } else {
        var n2 = document.createElement('div');
        n2.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;max-width:18rem;border-radius:12px;background:#1e293b;color:#fff;padding:10px 14px;font-size:11px;font-weight:600;box-shadow:0 10px 25px rgba(0,0,0,0.15);';
        n2.textContent = 'Nakili: ' + s;
        document.body.appendChild(n2);
        setTimeout(function () { n2.remove(); }, 8000);
      }
    }
  });
}
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