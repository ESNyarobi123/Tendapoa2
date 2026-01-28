@extends('layouts.app')
@section('title', 'Taarifa Zako')

@section('content')
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #eff6ff;
            --primary-dark: #1e40af;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --bg-surface: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 44px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 16px;
        }

        .notif-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            min-height: 80vh;
        }

        .notif-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            background: var(--bg-surface);
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-100);
        }

        .notif-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notif-title h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gray-800);
            margin: 0;
        }

        .notif-badge {
            background: #ef4444;
            color: white;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.875rem;
            font-weight: 700;
            box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);
        }

        .mark-read-btn {
            background: transparent;
            color: var(--primary);
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .mark-read-btn:hover {
            background: var(--primary-light);
        }

        .notif-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .notif-card {
            background: var(--bg-surface);
            border-radius: var(--radius);
            padding: 20px;
            display: grid;
            grid-template-columns: 48px 1fr auto;
            gap: 16px;
            align-items: start;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .notif-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            border-color: var(--primary);
        }

        .notif-card.unread {
            background: #f0f7ff;
            /* Very light blue for unread */
            border-left: 4px solid var(--primary);
        }

        .notif-card.unread::after {
            content: '';
            position: absolute;
            top: 16px;
            right: 16px;
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            box-shadow: 0 0 0 2px white;
        }

        .notif-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        /* Icon Colors based on types */
        .type-job {
            background: #dbeafe;
            color: #2563eb;
        }

        .type-money {
            background: #d1fae5;
            color: #059669;
        }

        .type-alert {
            background: #fee2e2;
            color: #dc2626;
        }

        .type-info {
            background: #f3f4f6;
            color: #4b5563;
        }

        .notif-content {
            min-width: 0;
            /* Prevent text overflow */
        }

        .notif-card-title {
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 4px;
            font-size: 1rem;
        }

        .notif-card-body {
            color: var(--gray-600);
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        .notif-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.8rem;
            color: var(--gray-400);
        }

        .notif-action {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            background: var(--bg-surface);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            color: var(--primary);
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
            margin-top: 4px;
        }

        .notif-action:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-surface);
            border-radius: var(--radius);
            border: 2px dashed var(--gray-200);
            color: var(--gray-500);
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            color: var(--gray-400);
            background: var(--gray-50);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-icon svg {
            width: 40px;
            height: 40px;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .notif-card {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .notif-icon-box {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }

            .notif-card.unread::after {
                top: 20px;
                right: 20px;
            }
        }
    </style>

    <div class="notif-wrapper">
        <div class="notif-header">
            <div class="notif-title">
                <div style="background: var(--primary-light); padding: 8px; border-radius: 10px;">
                    🔔
                </div>
                <h1>
                    Taarifa Zako
                    @if($notifications->count() > 0)
                        <span style="font-size: 0.5em; vertical-align: middle; margin-left: 8px;"
                            class="notif-badge">{{ $notifications->count() }}</span>
                    @endif
                </h1>
            </div>

            @if($notifications->count() > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit" class="mark-read-btn">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Weka zote zimesomwa
                    </button>
                </form>
            @endif
        </div>

        @if($notifications->count() > 0)
            <div class="notif-list">
                @foreach ($notifications as $notification)
                    @php
                        // Determine icon and style based on notification type
                        $type = $notification->data['type'] ?? 'info';
                        $icon = 'ℹ️';
                        $styleClass = 'type-info';

                        if (str_contains($type, 'job')) {
                            $icon = '💼';
                            $styleClass = 'type-job';
                        } elseif (str_contains($type, 'money') || str_contains($type, 'payment') || isset($notification->data['earnings'])) {
                            $icon = '💰';
                            $styleClass = 'type-money';
                        } elseif (str_contains($type, 'alert') || str_contains($type, 'cancel')) {
                            $icon = '⚠️';
                            $styleClass = 'type-alert';
                        }
                    @endphp

                    <div class="notif-card {{ $notification->read_at ? '' : 'unread' }}">
                        <!-- Icon -->
                        <div class="notif-icon-box {{ $styleClass }}">
                            {{ $icon }}
                        </div>

                        <!-- Content -->
                        <div class="notif-content">
                            <div class="notif-card-title">
                                {{ $notification->data['title'] ?? 'Taarifa Mpya' }}
                            </div>
                            <div class="notif-card-body">
                                {{ $notification->data['message'] ?? 'Hakuna maelezo ya ziada.' }}
                            </div>

                            <div class="notif-meta">
                                <span>🕒 {{ $notification->created_at->diffForHumans() }}</span>
                                @if(isset($notification->data['action_url']))
                                    <a href="{{ $notification->data['action_url'] }}" class="notif-action">
                                        Angalia ↗
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top: 24px;">
                {{ $notifications->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--gray-800); margin-bottom: 8px;">Hakuna Taarifa
                    Mpya</h3>
                <p style="font-size: 0.95rem; color: var(--gray-500); max-width: 300px; margin: 0 auto;">Pindi utakapo pokea
                    taarifa kuhusu kazi, malipo au updates, zitaonekana hapa.</p>

                <a href="/"
                    style="display: inline-block; margin-top: 24px; color: var(--primary); font-weight: 600; text-decoration: none;">
                    Rudi Nyumbani
                </a>
            </div>
        @endif
    </div>
@endsection