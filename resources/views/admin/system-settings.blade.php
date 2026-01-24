@extends('layouts.admin')

@section('title', 'System Settings - Admin')

@section('content')
<style>
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

  .settings-container {
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

  /* Settings Section */
  .settings-section {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 32px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }
  
  .settings-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899);
  }

  .settings-section:hover {
    background: var(--card-bg-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
  }

  .section-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 24px 0;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .form-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .form-input, .form-select, .form-textarea {
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: 12px;
    font-size: 0.875rem;
    background: rgba(255,255,255,0.05);
    color: var(--text-primary);
    transition: all 0.3s ease;
    font-family: inherit;
  }

  .form-input::placeholder, .form-textarea::placeholder {
    color: rgba(255,255,255,0.4);
  }

  .form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #6366f1;
    background: rgba(255,255,255,0.08);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
  }

  .form-select option {
    background: #1a1a3e;
    color: white;
  }

  /* Toggle Switch */
  .toggle-group {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    background: rgba(255,255,255,0.03);
    border-radius: 12px;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
  }

  .toggle-group:hover {
    background: rgba(255,255,255,0.06);
  }

  .toggle-info h3 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 4px 0;
  }

  .toggle-info p {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
  }

  .toggle-switch {
    position: relative;
    width: 56px;
    height: 32px;
  }

  .toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.1);
    transition: 0.3s;
    border-radius: 32px;
    border: 2px solid var(--border);
  }

  .toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background: white;
    transition: 0.3s;
    border-radius: 50%;
  }

  .toggle-switch input:checked + .toggle-slider {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-color: #6366f1;
  }

  .toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(24px);
  }

  /* Buttons */
  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
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

  .btn-outline {
    background: transparent;
    color: var(--primary);
    border: 2px solid var(--primary);
  }

  .btn-outline:hover {
    background: var(--primary);
    color: white;
  }

  .btn-save {
    background: linear-gradient(135deg, #10b981, #06b6d4);
    color: white;
    padding: 16px 32px;
    font-size: 1rem;
    box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.4);
  }

  .btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px 0 rgba(16, 185, 129, 0.6);
  }

  /* Section Icons */
  .section-icon {
    font-size: 1.5rem;
  }

  /* Shimmer Animation for Progress Bar */
  @keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
  }

  /* Upload Button Disabled State */
  #uploadBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .form-grid {
      grid-template-columns: 1fr;
    }
    
    .header-content {
      grid-template-columns: 1fr;
      text-align: center;
    }
  }
</style>

<div class="settings-container">
  <!-- Page Header -->
  <div class="page-header">
    <div class="header-content">
      <div class="header-text">
        <h1>⚙️ System Settings</h1>
        <p>Configure platform-wide settings and preferences</p>
      </div>
      <div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">
          <span>↩️</span>
          Back to Dashboard
        </a>
      </div>
    </div>
  </div>

  <form action="{{ route('admin.system-settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Platform Settings -->
    <div class="settings-section">
      <h2 class="section-title">
        <span class="section-icon">🏢</span>
        Platform Settings
      </h2>
      
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="platform_name">Platform Name</label>
          <input type="text" id="platform_name" name="platform_name" value="{{ $settings['platform_name'] ?? 'Tendapoa' }}" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="platform_version">Platform Version</label>
          <input type="text" id="platform_version" name="platform_version" value="{{ $settings['platform_version'] ?? '1.0.0' }}" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="system_currency">System Currency</label>
          <input type="text" id="system_currency" name="system_currency" value="{{ $settings['system_currency'] ?? 'TZS' }}" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="contact_email">Support Email</label>
          <input type="email" id="contact_email" name="contact_email" value="{{ $settings['contact_email'] ?? 'support@tendapoa.com' }}" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="platform_logo">Platform Logo</label>
          <input type="file" id="platform_logo" name="platform_logo" class="form-input" style="padding: 8px;">
          @if(isset($settings['platform_logo']))
            <div style="margin-top: 8px; display: flex; align-items: center; gap: 12px;">
              <img src="{{ asset('storage/' . $settings['platform_logo']) }}" alt="Logo" style="height: 40px; border-radius: 8px; background: rgba(255,255,255,0.1); padding: 4px;">
              <span style="font-size: 0.75rem; color: var(--text-muted);">Current Logo</span>
            </div>
          @endif
        </div>
      </div>

      <div class="form-group" style="margin-top: 24px;">
        <label class="form-label" for="platform_description">Platform Description</label>
        <textarea id="platform_description" name="platform_description" rows="3" class="form-textarea">{{ $settings['platform_description'] ?? 'Tendapoa - Your trusted platform for connecting job seekers with employers' }}</textarea>
      </div>
    </div>

    <!-- Financial & Commission Settings -->
    <div class="settings-section">
      <h2 class="section-title">
        <span class="section-icon">💰</span>
        Financial & Commission Settings
      </h2>
      
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="commission_rate">Job Commission Rate (%)</label>
          <input type="number" id="commission_rate" name="commission_rate" value="{{ $settings['commission_rate'] ?? '10' }}" step="0.1" class="form-input">
          <small style="color: var(--text-muted);">This percentage is deducted from worker earnings upon job completion.</small>
        </div>

        <div class="form-group">
          <label class="form-label" for="min_withdrawal">Minimum Withdrawal ({{ $settings['system_currency'] ?? 'TZS' }})</label>
          <input type="number" id="min_withdrawal" name="min_withdrawal" value="{{ $settings['min_withdrawal'] ?? '10000' }}" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="withdrawal_fee">Withdrawal Fee ({{ $settings['system_currency'] ?? 'TZS' }})</label>
          <input type="number" id="withdrawal_fee" name="withdrawal_fee" value="{{ $settings['withdrawal_fee'] ?? '500' }}" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="job_posting_fee">Job Posting Fee ({{ $settings['system_currency'] ?? 'TZS' }})</label>
          <input type="number" id="job_posting_fee" name="job_posting_fee" value="{{ $settings['job_posting_fee'] ?? '0' }}" class="form-input">
        </div>
      </div>
    </div>

    <!-- System Toggles -->
    <div class="settings-section">
      <h2 class="section-title">
        <span class="section-icon">🔌</span>
        System Toggles
      </h2>
      
      <div style="display: grid; gap: 16px;">
        <div class="toggle-group">
          <div class="toggle-info">
            <h3>Enable Payments (USSD Push)</h3>
            <p>If disabled, users can post jobs for free without USSD payment prompt.</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="payments_enabled" {{ ($settings['payments_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="toggle-group">
          <div class="toggle-info">
            <h3>Maintenance Mode</h3>
            <p>Put the entire system into maintenance mode. Only admins can access.</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="maintenance_mode" {{ ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="toggle-group">
          <div class="toggle-info">
            <h3>New User Registration</h3>
            <p>Allow new users to create accounts on the platform.</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="registration_enabled" {{ ($settings['registration_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>
      </div>
    </div>

    <!-- Notification Settings -->
    <div class="settings-section">
      <h2 class="section-title">
        <span class="section-icon">🔔</span>
        Notification Settings
      </h2>
      
      <div style="display: grid; gap: 16px;">
        <div class="toggle-group">
          <div class="toggle-info">
            <h3>Email Notifications</h3>
            <p>Send email notifications for important events</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="email_notifications" {{ ($settings['email_notifications'] ?? '1') == '1' ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="toggle-group">
          <div class="toggle-info">
            <h3>SMS Notifications</h3>
            <p>Send SMS notifications for urgent events</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="sms_notifications" {{ ($settings['sms_notifications'] ?? '0') == '1' ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="toggle-group">
          <div class="toggle-info">
            <h3>Push Notifications</h3>
            <p>Send push notifications to mobile devices</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="push_notifications" {{ ($settings['push_notifications'] ?? '1') == '1' ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>
      </div>
    </div>

    <!-- Save Button -->
    <div style="display: flex; justify-content: flex-end; margin-top: 32px;">
      <button type="submit" class="btn btn-save">
        <span>💾</span>
        Save All Settings
      </button>
    </div>
  </form>

  <!-- Mobile App Management (Separate from main form) -->
  <div class="settings-section">
      <h2 class="section-title">
        <span class="section-icon">📱</span>
        Mobile App Management
      </h2>
      
      @php
        $activeVersion = \App\Models\AppVersion::getActive();
        $allVersions = \App\Models\AppVersion::orderBy('created_at', 'desc')->get();
      @endphp

      @if($activeVersion)
      <div style="background: rgba(16, 185, 129, 0.1); border: 2px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <h3 style="color: #10b981; margin: 0 0 12px 0; font-size: 1.125rem; font-weight: 700;">Current Active Version</h3>
        <div style="display: grid; gap: 8px;">
          <div><strong>Version:</strong> {{ $activeVersion->version }}</div>
          <div><strong>File:</strong> {{ $activeVersion->file_name }}</div>
          <div><strong>Size:</strong> {{ number_format($activeVersion->file_size / 1024 / 1024, 2) }} MB</div>
          @if($activeVersion->description)
          <div><strong>Description:</strong> {{ $activeVersion->description }}</div>
          @endif
          <div><strong>Uploaded:</strong> {{ $activeVersion->created_at->format('Y-m-d H:i') }}</div>
        </div>
      </div>
      @else
      <div style="background: rgba(245, 158, 11, 0.1); border: 2px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <p style="color: #f59e0b; margin: 0;">No active APK version. Upload one below to enable downloads.</p>
      </div>
      @endif

      <!-- APK Upload Form (Separate from main form) -->
      <form id="apkUploadForm" enctype="multipart/form-data" style="margin-bottom: 24px;">
        @csrf
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="apk_file">Upload APK File</label>
            <input type="file" id="apk_file" name="apk_file" accept=".apk" class="form-input" style="padding: 8px;" required>
            <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 4px; display: block;">Only .apk files are allowed. Max size: 100MB</small>
            <div id="fileInfo" style="margin-top: 8px; font-size: 0.875rem; color: var(--text-muted); display: none;"></div>
            <div id="phpConfigInfo" style="margin-top: 8px; font-size: 0.75rem; color: var(--text-muted); display: none;"></div>
          </div>

          <div class="form-group">
            <label class="form-label" for="apk_version">Version Number</label>
            <input type="text" id="apk_version" name="apk_version" placeholder="e.g., 1.0.0, 1.1.0" class="form-input" required>
            <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 4px; display: block;">Use semantic versioning (e.g., 1.0.0)</small>
          </div>
        </div>

        <div class="form-group" style="margin-top: 24px;">
          <label class="form-label" for="apk_description">Version Description / Release Notes</label>
          <textarea id="apk_description" name="apk_description" rows="3" class="form-textarea" placeholder="Describe what's new in this version..."></textarea>
        </div>

        <!-- Progress Bar Container -->
        <div id="uploadProgressContainer" style="display: none; margin-top: 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="color: var(--text-primary); font-weight: 600;">Uploading APK...</span>
            <span id="uploadPercentage" style="color: var(--text-muted); font-size: 0.875rem;">0%</span>
          </div>
          <div style="background: rgba(255,255,255,0.1); border-radius: 12px; height: 12px; overflow: hidden; position: relative;">
            <div id="uploadProgressBar" style="background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899); height: 100%; width: 0%; transition: width 0.3s ease; position: relative; overflow: hidden;">
              <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent); animation: shimmer 1.5s infinite;"></div>
            </div>
          </div>
          <div id="uploadStatus" style="margin-top: 8px; font-size: 0.875rem; color: var(--text-muted);"></div>
        </div>

        <!-- Error Message Container -->
        <div id="uploadError" style="display: none; background: rgba(244, 63, 94, 0.1); border: 2px solid rgba(244, 63, 94, 0.3); border-radius: 12px; padding: 16px; margin-top: 16px;">
          <div style="color: #f43f5e; font-weight: 600; margin-bottom: 8px;">⚠️ Upload Failed</div>
          <div id="uploadErrorText" style="color: #f43f5e; font-size: 0.875rem; margin-bottom: 12px;"></div>
          <button type="button" id="retryUploadBtn" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
            <span>🔄</span>
            Retry Upload
          </button>
        </div>

        <!-- Success Message Container -->
        <div id="uploadSuccess" style="display: none; background: rgba(16, 185, 129, 0.1); border: 2px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 16px; margin-top: 16px;">
          <div style="color: #10b981; font-weight: 600; margin-bottom: 8px;">✅ Upload Successful!</div>
          <div id="uploadSuccessText" style="color: #10b981; font-size: 0.875rem;"></div>
        </div>

        <div style="margin-top: 24px;">
          <button type="submit" id="uploadBtn" class="btn btn-primary" style="width: 100%;">
            <span>📤</span>
            Upload APK
          </button>
        </div>
      </form>

      <!-- Manual Scan Option -->
      <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border);">
        <h3 style="color: var(--text-primary); font-size: 1rem; font-weight: 700; margin-bottom: 12px;">Manual Upload & Scan</h3>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 16px;">
          If the web upload fails, you can upload the APK file manually via FTP or File Manager to:
          <br>
          <code style="background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px; color: #10b981;">storage/app/public/apk/</code>
          <br>
          Then click the button below to detect and activate it.
        </p>
        <form action="{{ route('admin.apk.scan') }}" method="POST">
          @csrf
          <button type="submit" class="btn btn-outline" style="width: 100%;">
            <span>🔍</span> Scan & Activate Latest APK
          </button>
        </form>
      </div>

      @if($allVersions->count() > 0)
      <div style="margin-top: 32px;">
        <h3 style="color: var(--text-primary); font-size: 1rem; font-weight: 700; margin-bottom: 16px;">Previous Versions</h3>
        <div style="display: grid; gap: 12px;">
          @foreach($allVersions as $version)
          <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center;">
            <div>
              <div style="font-weight: 600; color: var(--text-primary);">{{ $version->version }}</div>
              <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 4px;">
                {{ $version->file_name }} ({{ number_format($version->file_size / 1024 / 1024, 2) }} MB)
              </div>
              @if($version->is_active)
              <span style="background: #10b981; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; margin-top: 4px; display: inline-block;">Active</span>
              @endif
            </div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">
              {{ $version->created_at->format('Y-m-d') }}
            </div>
          </div>
          @endforeach
        </div>
      </div>
      @endif
  </div>
</div>

<script>
  // Add save confirmation for main form
  document.querySelector('form[action="{{ route('admin.system-settings.update') }}"]').addEventListener('submit', function(e) {
    const confirmed = confirm('Are you sure you want to save these settings? This will update the platform configuration.');
    if (!confirmed) {
      e.preventDefault();
    }
  });

  // APK Upload with Progress Bar
  // Wait for DOM to be ready
  document.addEventListener('DOMContentLoaded', function() {
    const apkForm = document.getElementById('apkUploadForm');
    if (!apkForm) {
      return;
    }
    
    apkForm.addEventListener('submit', async function(e) {
      e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const uploadBtn = document.getElementById('uploadBtn');
    const progressContainer = document.getElementById('uploadProgressContainer');
    const progressBar = document.getElementById('uploadProgressBar');
    const uploadPercentage = document.getElementById('uploadPercentage');
    const uploadStatus = document.getElementById('uploadStatus');
    const uploadError = document.getElementById('uploadError');
    const uploadErrorText = document.getElementById('uploadErrorText');
    const uploadSuccess = document.getElementById('uploadSuccess');
    const uploadSuccessText = document.getElementById('uploadSuccessText');
    
    // Hide previous messages
    uploadError.style.display = 'none';
    uploadSuccess.style.display = 'none';
    
    // Show progress bar
    progressContainer.style.display = 'block';
    progressBar.style.width = '0%';
    uploadPercentage.textContent = '0%';
    uploadStatus.textContent = 'Preparing upload...';
    
    // Disable button
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span>⏳</span> Uploading...';
    
    try {
      const xhr = new XMLHttpRequest();
      
      // Track upload progress
      xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
          const percentComplete = Math.round((e.loaded / e.total) * 100);
          progressBar.style.width = percentComplete + '%';
          uploadPercentage.textContent = percentComplete + '%';
          
          if (percentComplete < 30) {
            uploadStatus.textContent = 'Uploading file...';
          } else if (percentComplete < 70) {
            uploadStatus.textContent = 'Processing file...';
          } else if (percentComplete < 100) {
            uploadStatus.textContent = 'Saving to database...';
          } else {
            uploadStatus.textContent = 'Finalizing...';
          }
        }
      });
      
      // Handle completion
      xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
          let response;
          try {
            response = JSON.parse(xhr.responseText);
          } catch(parseError) {
            throw new Error('Invalid server response');
          }
          
          if (response.success) {
            progressBar.style.width = '100%';
            uploadPercentage.textContent = '100%';
            uploadStatus.textContent = 'Upload completed successfully!';
            
            uploadSuccessText.textContent = response.message || 'APK uploaded and activated successfully!';
            uploadSuccess.style.display = 'block';
            
            // Reset form
            form.reset();
            document.getElementById('fileInfo').style.display = 'none';
            
            // Reload page after 2 seconds to show updated version
            setTimeout(() => {
              window.location.reload();
            }, 2000);
          } else {
            // Display error instead of throwing
            progressContainer.style.display = 'none';
            uploadErrorText.textContent = response.message || 'Upload failed';
            uploadError.style.display = 'block';
            
            // Reset button
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<span>📤</span> Upload APK';
          }
        } else {
          let response;
          try {
            response = JSON.parse(xhr.responseText);
          } catch(e) {
            response = {message: 'Server error: ' + xhr.status};
          }
          
          // Display error instead of throwing
          progressContainer.style.display = 'none';
          uploadErrorText.textContent = response.message || 'Upload failed with status: ' + xhr.status;
          uploadError.style.display = 'block';
          
          // Reset button
          uploadBtn.disabled = false;
          uploadBtn.innerHTML = '<span>📤</span> Upload APK';
        }
      });
      
      // Handle errors
      xhr.addEventListener('error', function() {
        throw new Error('Network error. Please check your connection and try again.');
      });
      
      // Handle abort
      xhr.addEventListener('abort', function() {
        throw new Error('Upload was cancelled.');
      });
      
      // Disable timeout (0 = no timeout)
      xhr.timeout = 0;
      
      // Handle network errors
      xhr.addEventListener('error', function() {
        progressContainer.style.display = 'none';
        uploadErrorText.textContent = 'Network error occurred. Please check your connection and try again.';
        uploadError.style.display = 'block';
        
        // Reset button
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<span>📤</span> Upload APK';
      });
      
      // Send request
      const uploadUrl = '{{ route('admin.apk.upload') }}';
      xhr.open('POST', uploadUrl);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.send(formData);
      
    } catch (error) {
      progressContainer.style.display = 'none';
      uploadErrorText.textContent = error.message || 'An unexpected error occurred. Please try again.';
      uploadError.style.display = 'block';
      
      // Reset button
      uploadBtn.disabled = false;
      uploadBtn.innerHTML = '<span>📤</span> Upload APK';
    }
    }); // End of apkForm.addEventListener

    // Retry button handler
    const retryBtn = document.getElementById('retryUploadBtn');
    if (retryBtn) {
      retryBtn.addEventListener('click', function() {
        document.getElementById('uploadError').style.display = 'none';
        apkForm.dispatchEvent(new Event('submit'));
      });
    }

    // Show file info when selected
    const apkFileInput = document.getElementById('apk_file');
    if (apkFileInput) {
      apkFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const fileInfo = document.getElementById('fileInfo');
        const phpConfigInfo = document.getElementById('phpConfigInfo');
        
        if (file) {
          const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
          fileInfo.innerHTML = `<strong>Selected:</strong> ${file.name} (${fileSizeMB} MB)`;
          fileInfo.style.display = 'block';
          fileInfo.style.color = file.size > 100 * 1024 * 1024 ? '#f43f5e' : 'var(--text-muted)';
          
          if (file.size > 100 * 1024 * 1024) {
            fileInfo.innerHTML += ' <span style="color: #f43f5e;">⚠️ File is too large! Max size is 100MB</span>';
          }
          
          // Check PHP configuration (client-side estimate)
          if (file.size > 50 * 1024 * 1024) {
            phpConfigInfo.innerHTML = '💡 <strong>Tip:</strong> Large files may take longer to upload. Ensure PHP max_execution_time is at least 180 seconds.';
            phpConfigInfo.style.display = 'block';
            phpConfigInfo.style.color = '#f59e0b';
          } else {
            phpConfigInfo.style.display = 'none';
          }
        } else {
          fileInfo.style.display = 'none';
          phpConfigInfo.style.display = 'none';
        }
      }); // End of apk_file change listener
    } // End of if apkFileInput
  }); // End of DOMContentLoaded

  // Add interactive animations
  document.addEventListener('DOMContentLoaded', function() {
    // Animate settings sections on scroll
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

    // Observe all settings sections
    document.querySelectorAll('.settings-section').forEach(section => {
      section.style.opacity = '0';
      section.style.transform = 'translateY(20px)';
      section.style.transition = 'all 0.6s ease';
      observer.observe(section);
    });

    // Add hover effects to toggle groups
    document.querySelectorAll('.toggle-group').forEach(group => {
      group.addEventListener('mouseenter', function() {
        this.style.transform = 'translateX(4px)';
      });
      
      group.addEventListener('mouseleave', function() {
        this.style.transform = 'translateX(0)';
      });
    });
  });
</script>
@endsection
