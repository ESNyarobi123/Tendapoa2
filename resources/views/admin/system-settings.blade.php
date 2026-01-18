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

  <form action="{{ route('admin.system-settings.update') }}" method="POST">
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
          <input type="text" id="platform_name" name="platform_name" value="Tendapoa" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="platform_version">Platform Version</label>
          <input type="text" id="platform_version" name="platform_version" value="1.0.0" class="form-input">
        </div>
      </div>

      <div class="form-group" style="margin-top: 24px;">
        <label class="form-label" for="platform_description">Platform Description</label>
        <textarea id="platform_description" name="platform_description" rows="3" class="form-textarea">Tendapoa - Your trusted platform for connecting job seekers with employers</textarea>
      </div>
    </div>

    <!-- User Management Settings -->
    <div class="settings-section">
      <h2 class="section-title">
        <span class="section-icon">👥</span>
        User Management
      </h2>
      
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="user_registration">New User Registration</label>
          <select id="user_registration" name="user_registration" class="form-select">
            <option value="enabled">Enabled</option>
            <option value="disabled">Disabled</option>
            <option value="approval_required">Approval Required</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="email_verification">Email Verification Required</label>
          <select id="email_verification" name="email_verification" class="form-select">
            <option value="required">Required</option>
            <option value="optional">Optional</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="default_role">Default User Role</label>
          <select id="default_role" name="default_role" class="form-select">
            <option value="muhitaji">Muhitaji (Job Poster)</option>
            <option value="mfanyakazi">Mfanyakazi (Worker)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="suspension_policy">User Suspension Policy</label>
          <select id="suspension_policy" name="suspension_policy" class="form-select">
            <option value="manual">Manual Only</option>
            <option value="automatic">Automatic (3 strikes)</option>
            <option value="hybrid">Hybrid (Manual + Auto)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Job Management Settings -->
    <div class="settings-section">
      <h2 class="section-title">
        <span class="section-icon">💼</span>
        Job Management
      </h2>
      
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="job_posting_fee">Job Posting Fee (Tsh)</label>
          <input type="number" id="job_posting_fee" name="job_posting_fee" value="0" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="commission_rate">Commission Rate (%)</label>
          <input type="number" id="commission_rate" name="commission_rate" value="5" step="0.1" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="job_expiry_days">Job Auto-Expiry (Days)</label>
          <input type="number" id="job_expiry_days" name="job_expiry_days" value="30" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="max_jobs_per_user">Max Jobs Per User</label>
          <input type="number" id="max_jobs_per_user" name="max_jobs_per_user" value="10" class="form-input">
        </div>
      </div>
    </div>

    <!-- Payment Settings -->
    <div class="settings-section">
      <h2 class="section-title">
        <span class="section-icon">💳</span>
        Payment Settings
      </h2>
      
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="payment_gateway">Payment Gateway</label>
          <select id="payment_gateway" name="payment_gateway" class="form-select">
            <option value="zenopay">ZenoPay</option>
            <option value="mpesa">M-Pesa</option>
            <option value="tigopesa">Tigo Pesa</option>
            <option value="airtelmoney">Airtel Money</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="min_withdrawal">Minimum Withdrawal (Tsh)</label>
          <input type="number" id="min_withdrawal" name="min_withdrawal" value="10000" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="withdrawal_processing_hours">Withdrawal Processing Time (Hours)</label>
          <input type="number" id="withdrawal_processing_hours" name="withdrawal_processing_hours" value="24" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="transaction_fee">Transaction Fee (%)</label>
          <input type="number" id="transaction_fee" name="transaction_fee" value="2.5" step="0.1" class="form-input">
        </div>
      </div>
    </div>

    <!-- Security Settings -->
    <div class="settings-section">
      <h2 class="section-title">
        <span class="section-icon">🔒</span>
        Security Settings
      </h2>
      
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="session_timeout">Session Timeout (Minutes)</label>
          <input type="number" id="session_timeout" name="session_timeout" value="120" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="max_login_attempts">Max Login Attempts</label>
          <input type="number" id="max_login_attempts" name="max_login_attempts" value="5" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="password_reset_timeout">Password Reset Timeout (Minutes)</label>
          <input type="number" id="password_reset_timeout" name="password_reset_timeout" value="60" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label" for="two_factor_auth">Two-Factor Authentication</label>
          <select id="two_factor_auth" name="two_factor_auth" class="form-select">
            <option value="disabled">Disabled</option>
            <option value="optional">Optional</option>
            <option value="required">Required</option>
          </select>
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
            <input type="checkbox" name="email_notifications" checked>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="toggle-group">
          <div class="toggle-info">
            <h3>SMS Notifications</h3>
            <p>Send SMS notifications for urgent events</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="sms_notifications">
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="toggle-group">
          <div class="toggle-info">
            <h3>Push Notifications</h3>
            <p>Send push notifications to mobile devices</p>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="push_notifications" checked>
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
</div>

<script>
  // Add save confirmation
  document.querySelector('form').addEventListener('submit', function(e) {
    const confirmed = confirm('Are you sure you want to save these settings? This will update the platform configuration.');
    if (!confirmed) {
      e.preventDefault();
    }
  });

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
