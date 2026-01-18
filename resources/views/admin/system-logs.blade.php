@extends('layouts.admin')

@section('title', 'System Logs - Admin')

@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
  
  * {
    font-family: 'Inter', sans-serif;
  }
  
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
    max-width: 1400px;
    margin: 0 auto;
    display: grid;
    gap: 24px;
  }
  
  .glass-morphism {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border);
    border-radius: 20px;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
  }
  
  .glass-morphism:hover {
    background: var(--card-bg-hover);
    box-shadow: var(--shadow-lg);
  }
  
  .gradient-bg {
    background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3e 50%, #2d1b69 100%);
  }
  
  .page-header {
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
  
  .activity-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateY(0);
  }
  
  .activity-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  }
  
  .pulse-animation {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  }
  
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
  }
  
  .fade-in {
    animation: fadeIn 0.5s ease-in-out;
  }
  
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  .slide-in {
    animation: slideIn 0.6s ease-out;
  }
  
  @keyframes slideIn {
    from { transform: translateX(-100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }
  
  .type-badge {
    position: relative;
    overflow: hidden;
  }
  
  .type-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
  }
  
  .type-badge:hover::before {
    left: 100%;
  }
  
  .search-input {
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
  }
  
  .search-input:focus {
    background: rgba(255, 255, 255, 1);
    transform: scale(1.02);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }
  
  .filter-btn {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }
  
  .filter-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
  }
  
  .filter-btn:hover::before {
    width: 300px;
    height: 300px;
  }
  
  .stats-card {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }
  
  .stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899);
  }
  
  .stats-card:hover {
    background: var(--card-bg-hover);
    transform: translateY(-5px) scale(1.02);
    box-shadow: var(--shadow-lg);
  }
  
  .timeline-line {
    position: absolute;
    left: 24px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #3b82f6, #8b5cf6, #f59e0b);
  }
  
  .activity-item {
    position: relative;
    padding-left: 60px;
  }
  
  .activity-item::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 20px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
  }
  
  .floating-action {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
    animation: float 3s ease-in-out infinite;
  }
  
  @keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
  }
</style>

<div class="page-container">
    <!-- Header Section -->
    <div class="glass-morphism page-header" style="padding: 32px; margin-bottom: 24px; position: relative; overflow: hidden;">
      <div style="display: flex; flex-direction: column; gap: 24px;">
        <div style="display: flex; flex-direction: column; gap: 16px; flex: 1;">
          <h1 class="slide-in" style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text-primary); background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; position: relative; z-index: 1;">
            📊 System Activity Monitor
          </h1>
          <p style="color: var(--text-muted); font-size: 1.125rem; position: relative; z-index: 1;">Real-time monitoring of all platform activities and user interactions</p>
          <div style="display: flex; align-items: center; margin-top: 16px; position: relative; z-index: 1;">
            <div class="pulse-animation" style="width: 12px; height: 12px; border-radius: 50%; margin-right: 8px; background: var(--success);"></div>
            <span style="color: var(--text-primary); font-weight: 600;">Live monitoring active</span>
          </div>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 16px; position: relative; z-index: 1;">
          <a href="{{ route('admin.dashboard') }}" 
             style="background: rgba(255,255,255,0.1); color: var(--text-primary); padding: 12px 24px; border-radius: 12px; font-weight: 600; text-decoration: none; transition: all 0.3s; border: 1px solid var(--border); display: inline-flex; align-items: center; gap: 8px;"
             onmouseover="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='translateY(-2px)'"
             onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
            ← Dashboard
          </a>
          <button onclick="refreshData()" 
                  style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 12px 24px; border-radius: 12px; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);"
                  onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(99, 102, 241, 0.6)'"
                  onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(99, 102, 241, 0.4)'">
            🔄 Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-bottom: 24px;">
      <div class="stats-card" style="border-radius: 20px; padding: 24px; background: var(--card-bg); backdrop-filter: blur(20px); border: 1px solid var(--border); box-shadow: var(--shadow); transition: all 0.3s; position: relative; overflow: hidden;" onmouseover="this.style.background='var(--card-bg-hover)'; this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.background='var(--card-bg)'; this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow)'">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #6366f1, #8b5cf6);"></div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
          <div style="flex: 1;">
            <p style="color: var(--text-muted); font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Total Activities</p>
            <p style="color: var(--text-primary); font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #6366f1, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0.5rem 0;">{{ $activities->count() }}</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.25rem 0 0 0;">All time</p>
          </div>
          <div style="width: 48px; height: 48px; background: rgba(99, 102, 241, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
            ✅
          </div>
        </div>
      </div>

      <div class="stats-card" style="border-radius: 20px; padding: 24px; background: var(--card-bg); backdrop-filter: blur(20px); border: 1px solid var(--border); box-shadow: var(--shadow); transition: all 0.3s; position: relative; overflow: hidden;" onmouseover="this.style.background='var(--card-bg-hover)'; this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.background='var(--card-bg)'; this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow)'">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #10b981, #06b6d4);"></div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
          <div style="flex: 1;">
            <p style="color: var(--text-muted); font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Jobs Created</p>
            <p style="color: var(--text-primary); font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #10b981, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0.5rem 0;">{{ $activities->where('type', 'job_created')->count() }}</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.25rem 0 0 0;">This session</p>
          </div>
          <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
            💼
          </div>
        </div>
      </div>

      <div class="stats-card" style="border-radius: 20px; padding: 24px; background: var(--card-bg); backdrop-filter: blur(20px); border: 1px solid var(--border); box-shadow: var(--shadow); transition: all 0.3s; position: relative; overflow: hidden;" onmouseover="this.style.background='var(--card-bg-hover)'; this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.background='var(--card-bg)'; this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow)'">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #6366f1, #8b5cf6);"></div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
          <div style="flex: 1;">
            <p style="color: var(--text-muted); font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Messages Sent</p>
            <p style="color: var(--text-primary); font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #6366f1, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0.5rem 0;">{{ $activities->where('type', 'message_sent')->count() }}</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.25rem 0 0 0;">Private chats</p>
          </div>
          <div style="width: 48px; height: 48px; background: rgba(99, 102, 241, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
            💬
          </div>
        </div>
      </div>

      <div class="stats-card" style="border-radius: 20px; padding: 24px; background: var(--card-bg); backdrop-filter: blur(20px); border: 1px solid var(--border); box-shadow: var(--shadow); transition: all 0.3s; position: relative; overflow: hidden;" onmouseover="this.style.background='var(--card-bg-hover)'; this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.background='var(--card-bg)'; this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow)'">
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #f59e0b, #f97316);"></div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
          <div style="flex: 1;">
            <p style="color: var(--text-muted); font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Payments Made</p>
            <p style="color: var(--text-primary); font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #f59e0b, #f97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin: 0.5rem 0;">{{ $activities->where('type', 'payment_made')->count() }}</p>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0.25rem 0 0 0;">Transactions</p>
          </div>
          <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
            💰
          </div>
        </div>
      </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="glass-morphism fade-in" style="padding: 24px; margin-bottom: 24px; border-radius: 20px;">
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div style="flex: 1; min-width: 0;">
          <div style="position: relative;">
            <input type="text" 
                   id="searchInput"
                   placeholder="Search activities, users, or descriptions..." 
                   style="width: 100%; padding: 12px 16px 12px 48px; border-radius: 12px; border: 2px solid var(--border); background: rgba(255,255,255,0.05); color: var(--text-primary); font-size: 0.875rem; transition: all 0.3s; font-family: inherit;"
                   onfocus="this.style.borderColor='#6366f1'; this.style.background='rgba(255,255,255,0.08)'; this.style.boxShadow='0 0 0 3px rgba(99, 102, 241, 0.2)'"
                   onblur="this.style.borderColor='var(--border)'; this.style.background='rgba(255,255,255,0.05)'; this.style.boxShadow='none'">
            <div style="position: absolute; top: 50%; left: 16px; transform: translateY(-50%); display: flex; align-items: center; pointer-events: none;">
              <svg style="width: 20px; height: 20px; color: var(--text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
          </div>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
          <button onclick="filterByType('all')" 
                  style="background: rgba(255,255,255,0.1); color: var(--text-primary); padding: 8px 16px; border-radius: 8px; font-weight: 600; border: 1px solid var(--border); cursor: pointer; transition: all 0.3s;"
                  onmouseover="this.style.background='rgba(255,255,255,0.15)'"
                  onmouseout="this.style.background='rgba(255,255,255,0.1)'">
            All
          </button>
          <button onclick="filterByType('job_created')" 
                  style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: 1px solid rgba(16, 185, 129, 0.4); cursor: pointer; transition: all 0.3s;"
                  onmouseover="this.style.background='rgba(16, 185, 129, 0.3)'"
                  onmouseout="this.style.background='rgba(16, 185, 129, 0.2)'">
            Jobs
          </button>
          <button onclick="filterByType('message_sent')" 
                  style="background: rgba(99, 102, 241, 0.2); color: #818cf8; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: 1px solid rgba(99, 102, 241, 0.4); cursor: pointer; transition: all 0.3s;"
                  onmouseover="this.style.background='rgba(99, 102, 241, 0.3)'"
                  onmouseout="this.style.background='rgba(99, 102, 241, 0.2)'">
            Messages
          </button>
          <button onclick="filterByType('payment_made')" 
                  style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; padding: 8px 16px; border-radius: 8px; font-weight: 600; border: 1px solid rgba(245, 158, 11, 0.4); cursor: pointer; transition: all 0.3s;"
                  onmouseover="this.style.background='rgba(245, 158, 11, 0.3)'"
                  onmouseout="this.style.background='rgba(245, 158, 11, 0.2)'">
            Payments
          </button>
        </div>
      </div>
    </div>

    <!-- Activity Timeline -->
    <div class="glass-morphism fade-in" style="border-radius: 20px; overflow: hidden;">
      <div style="padding: 24px; border-bottom: 1px solid var(--border);">
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">Activity Timeline</h2>
        <p style="color: var(--text-muted);">Real-time feed of all platform activities</p>
      </div>

      <div style="position: relative;">
        <div class="timeline-line"></div>
        <div style="padding: 24px; display: flex; flex-direction: column; gap: 24px;" id="activityContainer">
          @forelse($activities as $index => $activity)
          <div class="activity-item activity-card" 
               data-type="{{ $activity['type'] }}" 
               data-user="{{ strtolower($activity['user']->name ?? '') }}"
               data-description="{{ strtolower($activity['description'] ?? '') }}"
               style="animation-delay: {{ $index * 0.1 }}s;">
            <div style="background: rgba(255,255,255,0.03); backdrop-filter: blur(20px); border-radius: 16px; padding: 24px; border: 1px solid var(--border); transition: all 0.3s;"
                 onmouseover="this.style.background='rgba(255,255,255,0.08)'; this.style.transform='translateX(4px)'"
                 onmouseout="this.style.background='rgba(255,255,255,0.03)'; this.style.transform='translateX(0)'">
              <div style="display: flex; align-items: flex-start; gap: 16px;">
                <!-- Activity Icon -->
                <div class="flex-shrink-0">
                  @if($activity['type'] == 'job_created')
                    <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center border border-green-500/30">
                      <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                  @elseif($activity['type'] == 'message_sent')
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center border border-blue-500/30">
                      <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                      </svg>
                    </div>
                  @elseif($activity['type'] == 'payment_made')
                    <div class="w-12 h-12 bg-orange-500/20 rounded-xl flex items-center justify-center border border-orange-500/30">
                      <svg class="w-6 h-6 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                        <path d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"></path>
                      </svg>
                    </div>
                  @else
                    <div class="w-12 h-12 bg-gray-500/20 rounded-xl flex items-center justify-center border border-gray-500/30">
                      <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                      </svg>
                    </div>
                  @endif
                </div>

                <!-- Activity Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-3">
                      <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary);">
                        {{ $activity['user']->name ?? 'Unknown User' }}
                      </h3>
                      <span style="display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; background: rgba(99, 102, 241, 0.2); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.4);">
                        {{ ucfirst($activity['user']->role ?? 'user') }}
                      </span>
                    </div>
                    <div class="text-right">
                      <p style="font-size: 0.875rem; color: var(--text-muted); font-weight: 600;">
                        {{ $activity['timestamp']->diffForHumans() }}
                      </p>
                      <p style="font-size: 0.75rem; color: var(--text-muted);">
                        {{ $activity['timestamp']->format('M j, Y g:i A') }}
                      </p>
                    </div>
                  </div>
                  
                  <p style="color: var(--text-primary); margin-bottom: 1rem;">{{ $activity['description'] }}</p>

                  <!-- Additional Details -->
                  @if(isset($activity['data']))
                  <div class="flex flex-wrap gap-2">
                    @if($activity['type'] == 'job_created')
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                        Job ID: {{ $activity['data']->id }}
                      </span>
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                        Budget: Tsh {{ number_format($activity['data']->budget ?? 0) }}
                      </span>
                    @elseif($activity['type'] == 'message_sent')
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-300 border border-blue-500/30">
                        Message ID: {{ $activity['data']->id }}
                      </span>
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-300 border border-blue-500/30">
                        To: {{ $activity['data']->receiver->name ?? 'Unknown' }}
                      </span>
                    @elseif($activity['type'] == 'payment_made')
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-500/20 text-orange-300 border border-orange-500/30">
                        Payment ID: {{ $activity['data']->id }}
                      </span>
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-500/20 text-orange-300 border border-orange-500/30">
                        Amount: Tsh {{ number_format($activity['data']->amount ?? 0) }}
                      </span>
                    @endif
                  </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
          @empty
          <div class="text-center py-16">
            <div class="w-24 h-24 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-6">
              <svg class="w-12 h-12 text-white/60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">No activities found</h3>
            <p class="text-white/80">Activities will appear here as users interact with the platform.</p>
          </div>
          @endforelse
        </div>
      </div>
    </div>

    <!-- Floating Action Button -->
    <div class="floating-action">
      <button onclick="scrollToTop()" 
              class="w-14 h-14 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-full flex items-center justify-center text-white transition-all duration-300 transform hover:scale-110 border border-white/30">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
      </button>
    </div>
  </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
  const searchTerm = e.target.value.toLowerCase();
  const activities = document.querySelectorAll('.activity-item');
  
  activities.forEach(activity => {
    const user = activity.dataset.user;
    const description = activity.dataset.description;
    
    if (user.includes(searchTerm) || description.includes(searchTerm)) {
      activity.style.display = 'block';
      activity.classList.add('fade-in');
    } else {
      activity.style.display = 'none';
    }
  });
});

// Filter by type
function filterByType(type) {
  const activities = document.querySelectorAll('.activity-item');
  const buttons = document.querySelectorAll('.filter-btn');
  
  // Update button states
  buttons.forEach(btn => {
    btn.classList.remove('bg-white/30');
    btn.classList.add('bg-white/20');
  });
  event.target.classList.remove('bg-white/20');
  event.target.classList.add('bg-white/30');
  
  // Filter activities
  activities.forEach(activity => {
    if (type === 'all' || activity.dataset.type === type) {
      activity.style.display = 'block';
      activity.classList.add('fade-in');
    } else {
      activity.style.display = 'none';
    }
  });
}

// Refresh data
function refreshData() {
  const btn = event.target;
  const originalText = btn.innerHTML;
  
  btn.innerHTML = '🔄 Refreshing...';
  btn.disabled = true;
  
  setTimeout(() => {
    location.reload();
  }, 1000);
}

// Scroll to top
function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
}

// Auto-refresh every 30 seconds
setTimeout(function() {
  location.reload();
}, 30000);

// Add entrance animations
document.addEventListener('DOMContentLoaded', function() {
  const elements = document.querySelectorAll('.fade-in, .slide-in');
  elements.forEach((el, index) => {
    el.style.animationDelay = `${index * 0.1}s`;
  });
});
</script>
@endsection