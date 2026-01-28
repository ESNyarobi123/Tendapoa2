@extends('layouts.app')

@php
  use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
  <style>
    /* ============================================
     * TENDAPOA - AMAZING FEED PAGE STYLES
     * Modern, Premium, Dark Glass Design
     * ============================================ */

    :root {
      --feed-primary: #6366f1;
      --feed-primary-dark: #4f46e5;
      --feed-primary-glow: rgba(99, 102, 241, 0.4);
      --feed-success: #10b981;
      --feed-success-glow: rgba(16, 185, 129, 0.3);
      --feed-warning: #f59e0b;
      --feed-danger: #ef4444;
      --feed-dark: #0f172a;
      --feed-glass: rgba(15, 23, 42, 0.8);
      --feed-glass-light: rgba(255, 255, 255, 0.08);
      --feed-glass-border: rgba(255, 255, 255, 0.12);
      --feed-text: #e2e8f0;
      --feed-text-muted: #94a3b8;
      --feed-text-dim: #64748b;
      --feed-gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --feed-gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      --feed-gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      --feed-gradient-success: linear-gradient(135deg, #10b981, #059669);
      --feed-gradient-warning: linear-gradient(135deg, #f59e0b, #d97706);
      --feed-gradient-danger: linear-gradient(135deg, #ef4444, #dc2626);
      --feed-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
      --feed-shadow-glow: 0 0 40px rgba(99, 102, 241, 0.2);
    }

    .feed-page {
      background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
      min-height: 100vh;
      display: flex;
      position: relative;
      overflow-x: hidden;
    }

    /* Animated Background Effects */
    .feed-page::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background:
        radial-gradient(ellipse 80% 80% at 50% -20%, rgba(99, 102, 241, 0.15), transparent),
        radial-gradient(ellipse 60% 60% at 80% 100%, rgba(120, 75, 162, 0.1), transparent),
        radial-gradient(ellipse 50% 50% at 20% 80%, rgba(99, 241, 196, 0.08), transparent);
      pointer-events: none;
      z-index: 0;
    }

    /* Floating Particles Animation */
    .feed-page::after {
      content: '';
      position: fixed;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background-image:
        radial-gradient(2px 2px at 20px 30px, rgba(255, 255, 255, 0.15), transparent),
        radial-gradient(2px 2px at 40px 70px, rgba(255, 255, 255, 0.1), transparent),
        radial-gradient(2px 2px at 50px 160px, rgba(255, 255, 255, 0.12), transparent),
        radial-gradient(2px 2px at 90px 40px, rgba(255, 255, 255, 0.08), transparent),
        radial-gradient(2px 2px at 130px 80px, rgba(255, 255, 255, 0.1), transparent);
      background-size: 200px 200px;
      animation: floatingParticles 60s linear infinite;
      pointer-events: none;
      z-index: 0;
    }

    @keyframes floatingParticles {
      0% {
        transform: translate(0, 0);
      }

      100% {
        transform: translate(-200px, -200px);
      }
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

    .sidebar.collapsed~.main-content {
      margin-left: 80px;
    }

    @media (max-width: 1024px) {
      .main-content {
        margin-left: 0;
      }
    }

    .page-container {
      max-width: 1400px;
      margin: 0 auto;
    }

    /* ============================================
     * AMAZING PAGE HEADER
     * ============================================ */
    .page-header {
      background: var(--feed-glass);
      backdrop-filter: blur(30px);
      -webkit-backdrop-filter: blur(30px);
      border-radius: 28px;
      padding: 40px;
      margin-bottom: 28px;
      border: 1px solid var(--feed-glass-border);
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
      background: var(--feed-gradient-1);
    }

    .page-header::after {
      content: '🔍';
      position: absolute;
      top: 20px;
      right: 30px;
      font-size: 4rem;
      opacity: 0.08;
      filter: grayscale(100%);
    }

    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 24px;
      flex-wrap: wrap;
    }

    .header-text {
      flex: 1;
    }

    .page-title {
      font-size: 3rem;
      font-weight: 900;
      margin: 0 0 12px 0;
      background: var(--feed-gradient-1);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: -0.02em;
      line-height: 1.1;
      position: relative;
      display: inline-block;
    }

    .page-title::after {
      content: '';
      position: absolute;
      bottom: -4px;
      left: 0;
      width: 60px;
      height: 4px;
      background: var(--feed-gradient-1);
      border-radius: 2px;
    }

    .page-subtitle {
      color: var(--feed-text-muted);
      font-size: 1.15rem;
      margin: 20px 0 0 0;
      max-width: 500px;
      line-height: 1.6;
    }

    /* Stats Row */
    .feed-stats {
      display: flex;
      gap: 16px;
      margin-top: 24px;
    }

    .feed-stat {
      background: var(--feed-glass-light);
      padding: 14px 20px;
      border-radius: 14px;
      border: 1px solid var(--feed-glass-border);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .feed-stat-icon {
      font-size: 1.5rem;
    }

    .feed-stat-value {
      font-size: 1.25rem;
      font-weight: 800;
      color: var(--feed-text);
    }

    .feed-stat-label {
      font-size: 0.75rem;
      color: var(--feed-text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    /* View Toggle Buttons - Premium */
    .view-toggle {
      display: flex;
      gap: 8px;
      background: var(--feed-glass-light);
      padding: 6px;
      border-radius: 16px;
      border: 1px solid var(--feed-glass-border);
    }

    .view-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 24px;
      border: none;
      background: transparent;
      border-radius: 12px;
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--feed-text-muted);
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .view-btn:hover {
      background: var(--feed-glass-light);
      color: var(--feed-text);
      transform: translateY(-1px);
    }

    .view-btn.active {
      background: var(--feed-gradient-1);
      color: white;
      box-shadow: 0 8px 20px var(--feed-primary-glow);
    }

    .view-btn span {
      font-size: 1.1rem;
    }

    /* Location Warning */
    .location-warning {
      background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
      border: 2px solid rgba(245, 158, 11, 0.3);
      border-radius: 16px;
      padding: 20px;
      margin-top: 20px;
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .location-warning-icon {
      font-size: 2rem;
      flex-shrink: 0;
    }

    .location-warning h4 {
      color: #fbbf24;
      margin: 0 0 4px 0;
      font-weight: 700;
    }

    .location-warning p {
      color: #fcd34d;
      margin: 0;
      font-size: 0.875rem;
    }

    .location-warning a {
      color: #fcd34d;
      font-weight: 600;
      text-decoration: underline;
    }

    .location-warning a:hover {
      color: #fef3c7;
    }

    /* ============================================
     * FILTER SECTION - Premium Design
     * ============================================ */
    .filter-section {
      background: var(--feed-glass);
      backdrop-filter: blur(30px);
      -webkit-backdrop-filter: blur(30px);
      border-radius: 24px;
      padding: 28px;
      margin-bottom: 28px;
      border: 1px solid var(--feed-glass-border);
    }

    .filter-form {
      display: flex;
      gap: 20px;
      align-items: flex-end;
      flex-wrap: wrap;
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 10px;
      flex: 1;
      min-width: 200px;
    }

    .filter-label {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--feed-text-muted);
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }

    .filter-select {
      padding: 14px 18px;
      border: 2px solid var(--feed-glass-border);
      border-radius: 14px;
      font-size: 1rem;
      background: var(--feed-glass-light);
      color: var(--feed-text);
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .filter-select:focus {
      outline: none;
      border-color: var(--feed-primary);
      box-shadow: 0 0 0 3px var(--feed-primary-glow);
    }

    .filter-select option {
      background: #1e293b;
      color: var(--feed-text);
    }

    /* Distance Legend - Premium */
    .distance-legend {
      margin-top: 24px;
      padding: 20px;
      background: var(--feed-glass-light);
      border-radius: 16px;
      border: 1px solid var(--feed-glass-border);
    }

    .distance-legend h4 {
      color: var(--feed-text);
      margin: 0 0 16px 0;
      font-size: 0.875rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .legend-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 12px;
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      background: var(--feed-glass-light);
      border-radius: 10px;
      border: 1px solid var(--feed-glass-border);
      transition: all 0.2s ease;
    }

    .legend-item:hover {
      transform: translateX(4px);
      border-color: var(--feed-primary);
    }

    .legend-color {
      width: 24px;
      height: 6px;
      border-radius: 3px;
      flex-shrink: 0;
    }

    .legend-color.near {
      background: var(--feed-gradient-success);
    }

    .legend-color.moderate {
      background: var(--feed-gradient-warning);
    }

    .legend-color.far {
      background: var(--feed-gradient-danger);
    }

    .legend-text {
      font-size: 0.8rem;
      color: var(--feed-text-muted);
    }

    /* ============================================
     * AMAZING JOB CARDS - Premium Glass Design
     * ============================================ */
    .jobs-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
      gap: 28px;
    }

    @media (max-width: 768px) {
      .jobs-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
    }

    .job-card {
      background: var(--feed-glass);
      backdrop-filter: blur(30px);
      -webkit-backdrop-filter: blur(30px);
      border-radius: 24px;
      overflow: hidden;
      border: 1px solid var(--feed-glass-border);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      display: flex;
      flex-direction: column;
    }

    .job-card:hover {
      transform: translateY(-8px) scale(1.01);
      border-color: var(--feed-primary);
      box-shadow: var(--feed-shadow), var(--feed-shadow-glow);
    }

    /* Distance Indicator Strip */
    .job-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      z-index: 10;
      background: var(--feed-glass-border);
    }

    .job-card.near::before {
      background: var(--feed-gradient-success);
    }

    .job-card.moderate::before {
      background: var(--feed-gradient-warning);
    }

    .job-card.far::before {
      background: var(--feed-gradient-danger);
    }

    .job-card.no_user_location::before {
      background: var(--feed-gradient-warning);
    }

    .job-card.no_job_location::before {
      background: var(--feed-gradient-danger);
    }

    .job-card.unknown::before {
      background: linear-gradient(90deg, #64748b, #475569);
    }

    /* Amazing Job Image */
    .job-image-wrapper {
      position: relative;
      width: 100%;
      height: 220px;
      overflow: hidden;
      background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(120, 75, 162, 0.1));
    }

    .job-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1), filter 0.3s ease;
    }

    .job-card:hover .job-image {
      transform: scale(1.1);
    }

    .job-image-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(120, 75, 162, 0.15));
      color: var(--feed-text-dim);
    }

    .job-image-placeholder-icon {
      font-size: 4rem;
      opacity: 0.4;
      margin-bottom: 8px;
    }

    .job-image-placeholder-text {
      font-size: 0.8rem;
      opacity: 0.5;
      font-weight: 600;
    }

    /* Image Overlay with Price */
    .job-image-overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 60px 20px 16px;
      background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, transparent 100%);
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }

    .job-price-tag {
      background: var(--feed-gradient-success);
      color: white;
      padding: 10px 18px;
      border-radius: 12px;
      font-size: 1.25rem;
      font-weight: 800;
      box-shadow: 0 4px 15px var(--feed-success-glow);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .job-price-tag span {
      font-size: 0.75rem;
      opacity: 0.9;
      font-weight: 500;
    }

    .job-distance-tag {
      padding: 8px 14px;
      border-radius: 10px;
      font-size: 0.85rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 6px;
      backdrop-filter: blur(10px);
    }

    /* Job Content Area */
    .job-content {
      padding: 24px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    /* Badges */
    .job-badges {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .badge {
      padding: 8px 14px;
      border-radius: 10px;
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .badge-category {
      background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(99, 102, 241, 0.1));
      color: #a5b4fc;
      border: 1px solid rgba(99, 102, 241, 0.3);
    }

    .badge-muhitaji {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.1));
      color: #6ee7b7;
      border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .badge-mfanyakazi {
      background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.1));
      color: #fcd34d;
      border: 1px solid rgba(245, 158, 11, 0.3);
    }

    /* Job Title */
    .job-title {
      font-size: 1.35rem;
      font-weight: 800;
      color: var(--feed-text);
      margin: 0 0 12px 0;
      line-height: 1.35;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    /* Job Meta Info */
    .job-meta {
      display: flex;
      gap: 16px;
      align-items: center;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }

    .job-meta-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.85rem;
      color: var(--feed-text-muted);
    }

    .job-meta-item-icon {
      font-size: 1rem;
    }

    /* Job Description */
    .job-description {
      color: var(--feed-text-dim);
      font-size: 0.9rem;
      line-height: 1.6;
      margin-bottom: 16px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      flex: 1;
    }

    /* Job Note for Mfanyakazi Posts */
    .job-note {
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05));
      border: 1px solid rgba(16, 185, 129, 0.3);
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 0.8rem;
      color: #6ee7b7;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 16px;
    }

    /* Job Actions */
    .job-actions {
      display: flex;
      gap: 12px;
      padding-top: 16px;
      border-top: 1px solid var(--feed-glass-border);
    }

    .btn-view-job {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 16px 24px;
      background: var(--feed-gradient-1);
      color: white;
      font-size: 0.95rem;
      font-weight: 700;
      border-radius: 14px;
      text-decoration: none;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 8px 25px var(--feed-primary-glow);
      border: none;
      cursor: pointer;
    }

    .btn-view-job:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 35px var(--feed-primary-glow);
    }

    .btn-view-job span {
      font-size: 1.1rem;
      transition: transform 0.3s ease;
    }

    .btn-view-job:hover span {
      transform: translateX(4px);
    }

    /* ============================================
     * MAP CONTAINER - Premium
     * ============================================ */
    .map-container {
      display: none;
      height: 75vh;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: var(--feed-shadow);
      border: 1px solid var(--feed-glass-border);
    }

    .map-container.active {
      display: block;
    }

    .list-container {
      display: block;
      opacity: 1;
      transition: opacity 0.3s ease;
    }

    .list-container.hidden {
      display: none;
      opacity: 0;
    }

    #map {
      width: 100%;
      height: 100%;
    }

    /* Map Popup Styles */
    .map-popup {
      max-width: 320px;
      padding: 0;
    }

    .map-popup-content {
      padding: 16px;
    }

    .map-popup .popup-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #1f2937;
      margin: 0 0 10px 0;
    }

    .map-popup .popup-price {
      font-size: 1.3rem;
      font-weight: 800;
      color: #10b981;
      margin: 0 0 10px 0;
    }

    .map-popup .popup-distance {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.875rem;
      font-weight: 600;
      margin: 0 0 10px 0;
    }

    .map-popup .popup-description {
      font-size: 0.875rem;
      color: #6b7280;
      line-height: 1.5;
      margin: 0 0 14px 0;
    }

    .map-popup .popup-btn {
      display: block;
      width: 100%;
      padding: 12px;
      background: linear-gradient(135deg, #6366f1, #4f46e5);
      color: white;
      text-align: center;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.2s ease;
    }

    .map-popup .popup-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
    }

    /* ============================================
     * EMPTY STATE - Premium
     * ============================================ */
    .empty-state {
      text-align: center;
      padding: 80px 40px;
      background: var(--feed-glass);
      backdrop-filter: blur(30px);
      -webkit-backdrop-filter: blur(30px);
      border-radius: 28px;
      border: 1px solid var(--feed-glass-border);
    }

    .empty-icon {
      font-size: 5rem;
      margin-bottom: 24px;
      opacity: 0.5;
    }

    .empty-title {
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--feed-text);
      margin: 0 0 12px 0;
    }

    .empty-text {
      color: var(--feed-text-muted);
      font-size: 1.1rem;
      margin: 0;
      max-width: 400px;
      margin-left: auto;
      margin-right: auto;
    }

    /* ============================================
     * PAGINATION
     * ============================================ */
    .pagination-wrapper {
      display: flex;
      justify-content: center;
      margin-top: 40px;
    }

    .pagination {
      display: flex;
      gap: 8px;
      background: var(--feed-glass);
      backdrop-filter: blur(20px);
      padding: 8px;
      border-radius: 16px;
      border: 1px solid var(--feed-glass-border);
    }

    /* ============================================
     * RESPONSIVE ADJUSTMENTS
     * ============================================ */
    @media (max-width: 768px) {
      .main-content {
        padding: 16px;
      }

      .page-header {
        padding: 24px;
        border-radius: 20px;
      }

      .page-title {
        font-size: 2rem;
      }

      .page-subtitle {
        font-size: 1rem;
      }

      .header-content {
        flex-direction: column;
      }

      .view-toggle {
        width: 100%;
        justify-content: center;
      }

      .feed-stats {
        flex-wrap: wrap;
      }

      .filter-form {
        flex-direction: column;
      }

      .filter-group {
        min-width: 100%;
      }

      .job-image-wrapper {
        height: 180px;
      }

      .job-content {
        padding: 20px;
      }

      .job-title {
        font-size: 1.2rem;
      }

      .map-container {
        height: 60vh;
        border-radius: 16px;
      }

      .view-toggle {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 1000;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
      }
    }

    /* Marker Styles */
    .job-marker-green,
    .job-marker-orange,
    .job-marker-red {
      z-index: 1000 !important;
    }

    .leaflet-marker-icon {
      z-index: 1000 !important;
    }

    .user-location-marker {
      z-index: 1002 !important;
    }
  </style>

  <div class="feed-page">
    @include('components.user-sidebar')

    <main class="main-content">
      <div class="page-container">

        <!-- Amazing Page Header -->
        <div class="page-header">
          <div class="header-content">
            <div class="header-text">
              <h1 class="page-title">Kazi Zilizopo</h1>
              <p class="page-subtitle">Chunguza kazi zilizo karibu nawe na uwasiliane na wateja haraka. Pata kazi inayofaa
                ujuzi wako.</p>

              <div class="feed-stats">
                <div class="feed-stat">
                  <span class="feed-stat-icon">📋</span>
                  <div>
                    <div class="feed-stat-value">{{ $jobs->total() }}</div>
                    <div class="feed-stat-label">Kazi Zote</div>
                  </div>
                </div>
                <div class="feed-stat">
                  <span class="feed-stat-icon">🟢</span>
                  <div>
                    <div class="feed-stat-value">{{ $jobs->where('distance_info.category', 'near')->count() }}</div>
                    <div class="feed-stat-label">Karibu Nawe</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- View Toggle Buttons -->
            <div class="view-toggle">
              <button id="list-view-btn" class="view-btn active" onclick="switchView('list')">
                <span>📋</span>
                List
              </button>
              <button id="map-view-btn" class="view-btn" onclick="switchView('map')">
                <span>🗺️</span>
                Ramani
              </button>
            </div>
          </div>

          @if(!auth()->user()->hasLocation())
            <div class="location-warning">
              <div class="location-warning-icon">⚠️</div>
              <div>
                <h4>Eneo Lako Halijajulikana</h4>
                <p>
                  Weka eneo lako kwenye profile ili kuona umbali wa kazi.
                  <a href="{{ route('profile.edit') }}">Bonyeza hapa kuongeza eneo →</a>
                </p>
              </div>
            </div>
          @endif
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
          <form method="get" class="filter-form">
            <div class="filter-group">
              <label class="filter-label" for="category">Aina ya Kazi</label>
              <select name="category" id="category" class="filter-select" onchange="this.form.submit()">
                <option value="">Zote</option>
                @foreach(\App\Models\Category::all() as $c)
                  <option value="{{ $c->slug }}" {{ $cat == $c->slug ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label" for="distance">Chuja kwa Umbali</label>
              <select name="distance" id="distance" class="filter-select" onchange="this.form.submit()">
                <option value="">Umbali Wote</option>
                <option value="5" {{ request('distance') == '5' ? 'selected' : '' }}>🟢 Karibu (≤5km)</option>
                <option value="10" {{ request('distance') == '10' ? 'selected' : '' }}>🟠 Wastani (≤10km)</option>
                <option value="20" {{ request('distance') == '20' ? 'selected' : '' }}>🔴 Mbali (≤20km)</option>
                <option value="50" {{ request('distance') == '50' ? 'selected' : '' }}>🟣 Mbali Sana (≤50km)</option>
              </select>
            </div>
          </form>

          <!-- Distance Legend -->
          <div class="distance-legend">
            <h4>🎨 Maana ya Rangi</h4>
            <div class="legend-grid">
              <div class="legend-item">
                <div class="legend-color near"></div>
                <span class="legend-text">🟢 Karibu (≤5km)</span>
              </div>
              <div class="legend-item">
                <div class="legend-color moderate"></div>
                <span class="legend-text">🟠 Wastani (≤10km)</span>
              </div>
              <div class="legend-item">
                <div class="legend-color far"></div>
                <span class="legend-text">🔴 Mbali (>10km)</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Map Container -->
        <div id="map-container" class="map-container">
          <div id="map"></div>
        </div>

        <!-- Jobs List Container -->
        <div id="list-container" class="list-container">
          @if($jobs->count() > 0)
            <div class="jobs-grid">
              @foreach($jobs as $job)
                <div class="job-card {{ $job->distance_info['category'] ?? 'unknown' }}">
                  <!-- Job Image with Overlay -->
                  <div class="job-image-wrapper">
                    @php
                      $imageUrl = null;
                      if (isset($job->image_url) && $job->image_url) {
                        $imageUrl = $job->image_url;
                      } elseif ($job->image) {
                        $filePath = storage_path('app/public/' . $job->image);
                        if (file_exists($filePath)) {
                          $imageUrl = asset('storage/' . $job->image) . '?v=' . filemtime($filePath);
                        }
                      }
                    @endphp

                    @if($imageUrl)
                      <img src="{{ $imageUrl }}" alt="{{ $job->title }}" class="job-image"
                        onerror="this.onerror=null; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                      <div class="job-image-placeholder" style="display: none;">
                        <div class="job-image-placeholder-icon">📷</div>
                        <div class="job-image-placeholder-text">Hakuna Picha</div>
                      </div>
                    @else
                      <div class="job-image-placeholder">
                        <div class="job-image-placeholder-icon">📷</div>
                        <div class="job-image-placeholder-text">Hakuna Picha</div>
                      </div>
                    @endif

                    <!-- Price & Distance Overlay -->
                    <div class="job-image-overlay">
                      <div class="job-price-tag">
                        {{ number_format($job->price) }}<span>TZS</span>
                      </div>
                      <div class="job-distance-tag"
                        style="background: {{ $job->distance_info['bg_color'] ?? 'rgba(100,116,139,0.8)' }}; color: {{ $job->distance_info['text_color'] ?? '#fff' }};">
                        📍
                        @if($job->distance_info['distance'])
                          {{ $job->distance_info['distance'] }}km
                        @else
                          {{ $job->distance_info['label'] ?? 'N/A' }}
                        @endif
                      </div>
                    </div>
                  </div>

                  <!-- Job Content -->
                  <div class="job-content">
                    <!-- Badges -->
                    <div class="job-badges">
                      <div class="badge badge-category">{{ $job->category->name }}</div>
                      @if($job->poster_type === 'mfanyakazi')
                        <div class="badge badge-mfanyakazi">👷 Mfanyakazi</div>
                      @else
                        <div class="badge badge-muhitaji">👤 Muhitaji</div>
                      @endif
                    </div>

                    <!-- Title -->
                    <h3 class="job-title">{{ $job->title }}</h3>

                    <!-- Meta -->
                    <div class="job-meta">
                      <div class="job-meta-item">
                        <span class="job-meta-item-icon">🚗</span>
                        {{ $job->distance_info['label'] ?? 'Umbali haujulikani' }}
                      </div>
                      <div class="job-meta-item">
                        <span class="job-meta-item-icon">🕐</span>
                        {{ $job->created_at->diffForHumans() }}
                      </div>
                    </div>

                    <!-- Description -->
                    @if($job->description)
                      <div class="job-description">{{ Str::limit($job->description, 100) }}</div>
                    @endif

                    <!-- Mfanyakazi Note -->
                    @if($job->poster_type === 'mfanyakazi')
                      <div class="job-note">
                        ⭐ Huduma inayotolewa na mfanyakazi mtaalamu
                      </div>
                    @endif

                    <!-- Actions -->
                    <div class="job-actions">
                      <a class="btn-view-job" href="{{ route('jobs.show', $job) }}">
                        <span>👁️</span>
                        Fungua Kazi
                        <span>→</span>
                      </a>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>

            <div class="pagination-wrapper">
              {{ $jobs->links() }}
            </div>
          @else
            <div class="empty-state">
              <div class="empty-icon">🔍</div>
              <h3 class="empty-title">Hakuna Kazi Zilizopo</h3>
              <p class="empty-text">
                @if($cat)
                  Hakuna kazi za aina hii kwa sasa. Jaribu kuchagua aina nyingine.
                @else
                  Hakuna kazi zilizopo kwa sasa. Rudi baadaye kuangalia kazi mpya.
                @endif
              </p>
            </div>
          @endif
        </div>

      </div>
  </div>

  <!-- Leaflet.js CSS and JS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  @php
    $user = auth()->user();
    $userLat = $user ? ($user->lat ?? null) : null;
    $userLng = $user ? ($user->lng ?? null) : null;
    $jobsArray = $jobs->items();
    $allJobsFromDb = \App\Models\Job::where('status', 'posted')->with('category')->get();
  @endphp

  <div id="feed-data" data-user-lat="{{ $userLat ?? '' }}" data-user-lng="{{ $userLng ?? '' }}"
    data-jobs="{{ json_encode($jobsArray) }}" data-all-jobs="{{ json_encode($allJobsFromDb) }}" style="display: none;">
  </div>

  <script>
    // Global variables
    let map;
    let userLocation = null;
    let jobMarkers = [];

    // Get PHP data
    const feedDataEl = document.getElementById('feed-data');
    let userLat = feedDataEl?.dataset.userLat ? parseFloat(feedDataEl.dataset.userLat) : null;
    let userLng = feedDataEl?.dataset.userLng ? parseFloat(feedDataEl.dataset.userLng) : null;
    let jobsData = [];
    let allJobsData = [];

    if (feedDataEl) {
      try {
        jobsData = JSON.parse(feedDataEl.dataset.jobs || '[]');
        allJobsData = JSON.parse(feedDataEl.dataset.allJobs || '[]');
      } catch (e) {
        console.error('Error parsing jobs data:', e);
      }
    }

    // View switching
    function switchView(view) {
      const listBtn = document.getElementById('list-view-btn');
      const mapBtn = document.getElementById('map-view-btn');
      const listContainer = document.getElementById('list-container');
      const mapContainer = document.getElementById('map-container');

      if (view === 'list') {
        listBtn.classList.add('active');
        mapBtn.classList.remove('active');
        listContainer.classList.remove('hidden');
        mapContainer.classList.remove('active');
      } else {
        mapBtn.classList.add('active');
        listBtn.classList.remove('active');
        listContainer.classList.add('hidden');
        mapContainer.classList.add('active');

        if (!map) {
          initializeMap();
        }
      }
    }

    // Initialize map
    function initializeMap() {
      if (typeof L === 'undefined') {
        console.error('Leaflet not loaded');
        return;
      }

      // Default center (Dar es Salaam)
      let centerLat = -6.7924;
      let centerLng = 39.2083;
      let zoom = 12;

      if (userLat && userLng) {
        centerLat = userLat;
        centerLng = userLng;
        zoom = 13;
      }

      map = L.map('map').setView([centerLat, centerLng], zoom);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(map);

      // User location marker
      if (userLat && userLng) {
        userLocation = L.marker([userLat, userLng], {
          icon: L.divIcon({
            className: 'user-location-marker',
            html: '<div style="background: #3b82f6; width: 24px; height: 24px; border-radius: 50%; border: 4px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.4);"></div>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
          })
        }).addTo(map);

        userLocation.bindPopup('<div style="text-align: center; padding: 8px;"><strong>📍 Eneo Lako</strong><br><small>Hapa ndipo ulipo</small></div>');
      }

      // Add job markers
      addJobMarkers();
    }

    // Add job markers to map
    function addJobMarkers() {
      let jobs = Array.isArray(allJobsData) ? allJobsData : Object.values(allJobsData);

      jobs.forEach(job => {
        if (!job.lat || !job.lng || job.lat == 0 || job.lng == 0) return;

        let distance = null;
        let distanceLabel = 'Umbali haujulikani';
        let markerColor = '#6b7280';

        if (userLat && userLng) {
          distance = calculateDistance(userLat, userLng, job.lat, job.lng);
          if (distance <= 5) {
            markerColor = '#10b981';
            distanceLabel = distance.toFixed(1) + 'km - Karibu';
          } else if (distance <= 10) {
            markerColor = '#f59e0b';
            distanceLabel = distance.toFixed(1) + 'km - Wastani';
          } else {
            markerColor = '#ef4444';
            distanceLabel = distance.toFixed(1) + 'km - Mbali';
          }
        }

        const marker = L.marker([job.lat, job.lng], {
          icon: L.divIcon({
            className: 'job-marker',
            html: `<div style="background: ${markerColor}; width: 32px; height: 32px; border-radius: 50%; border: 4px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; font-size: 14px;">💼</div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
          })
        }).addTo(map);

        const categoryName = job.category?.name || 'Kazi';
        const price = new Intl.NumberFormat('sw-TZ').format(job.price);

        marker.bindPopup(`
          <div class="map-popup-content">
            <h3 class="popup-title">${job.title}</h3>
            <p class="popup-price">${price} TZS</p>
            <p class="popup-distance" style="color: ${markerColor};">📍 ${distanceLabel}</p>
            <p class="popup-description">${job.description ? job.description.substring(0, 80) + '...' : 'Hakuna maelezo'}</p>
            <a href="/jobs/${job.id}" class="popup-btn">👁️ Fungua Kazi</a>
          </div>
        `);

        jobMarkers.push(marker);
      });
    }

    // Calculate distance using Haversine formula
    function calculateDistance(lat1, lon1, lat2, lon2) {
      const R = 6371;
      const dLat = (lat2 - lat1) * Math.PI / 180;
      const dLon = (lon2 - lon1) * Math.PI / 180;
      const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
      const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      return R * c;
    }
  </script>

@endsection