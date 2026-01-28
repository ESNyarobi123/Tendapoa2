@extends('layouts.app')

@section('title', 'Wasifu Wangu')

@section('content')
<style>
    /* ====== Modern Profile Page Styles ====== */
    :root {
        --primary: #3b82f6;
        --primary-dark: #2563eb;
        --secondary: #64748b;
        --success: #10b981;
        --danger: #ef4444;
        --dark: #1e293b;
        --light: #f8fafc;
        --surface: #ffffff;
        --border: #e2e8f0;
    }

    body {
        background: #f1f5f9;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .profile-page {
        display: flex;
        min-height: 100vh;
        background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
    }

    .main-content {
        flex: 1;
        margin-left: 280px; /* Match sidebar width */
        padding: 32px;
        transition: margin-left 0.3s ease;
    }

    @media (max-width: 1024px) {
        .main-content {
            margin-left: 0;
            padding: 20px;
        }
    }

    .page-header {
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 800;
        color: var(--dark);
        margin: 0 0 8px 0;
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .page-subtitle {
        color: var(--secondary);
        font-size: 1.1rem;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 32px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Cards */
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 32px;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.5);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-description {
        color: var(--secondary);
        font-size: 0.9rem;
        margin-top: 4px;
    }

    /* Forms */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-input {
        width: 100%;
        padding: 12px 16px;
        border-radius: 12px;
        border: 2px solid var(--border);
        background: var(--light);
        color: var(--dark);
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        font-size: 1rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger), #dc2626);
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
    }

    /* Profile Photo Upload */
    .profile-photo-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .profile-photo-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin-bottom: 20px;
    }

    .profile-photo {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }

    .photo-upload-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: var(--primary);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }

    .photo-upload-btn:hover {
        transform: scale(1.1);
        background: var(--primary-dark);
    }

    .hidden-input {
        display: none;
    }

    .user-meta {
        margin-top: 10px;
    }

    .user-name {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--dark);
        margin: 0;
    }

    .user-role {
        display: inline-block;
        padding: 4px 12px;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Success Message */
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid #a7f3d0;
    }
</style>

<div class="profile-page">
    @include('components.user-sidebar')

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Wasifu Wangu</h1>
            <p class="page-subtitle">Dhibiti taarifa zako, usalama, na mapendeleo.</p>
        </div>

        @if (session('status') === 'profile-updated' || session('status') === 'password-updated')
            <div class="alert-success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span>Mabadiliko yamehifadhiwa kikamilifu!</span>
            </div>
        @endif

        <div class="profile-grid">
            <!-- Left Column: Photo & Basic Info -->
            <div class="glass-card profile-photo-wrapper">
                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="photo-form">
                    @csrf
                    @method('patch')
                    
                    <!-- Hidden fields to satisfy validation requirements -->
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    @if($user->phone)
                        <input type="hidden" name="phone" value="{{ $user->phone }}">
                    @endif
                    
                    <div class="profile-photo-container">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="profile-photo" id="preview-image" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF'">
                        <label for="photo" class="photo-upload-btn" title="Badilisha Picha">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                        </label>
                        <input type="file" id="photo" name="photo" class="hidden-input" accept="image/*">
                    </div>
                    @error('photo')
                        <div style="color: var(--danger); font-size: 0.85rem; text-align: center; margin-top: 10px;">{{ $message }}</div>
                    @enderror
                    <div id="photo-actions" style="display: none; margin-top: 10px; text-align: center;">
                        <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.85rem;">
                            Hifadhi Picha
                        </button>
                        <button type="button" class="btn" style="padding: 8px 16px; font-size: 0.85rem; background: #e2e8f0; margin-left: 8px;" onclick="cancelPhotoUpload()">
                            Ghairi
                        </button>
                    </div>
                </form>

                <div class="user-meta">
                    <h2 class="user-name">{{ $user->name }}</h2>
                    <span class="user-role">{{ $user->role }}</span>
                    <p style="color: #64748b; margin-top: 8px;">{{ $user->email }}</p>
                    <p style="color: #64748b; font-size: 0.9rem;">{{ $user->phone ?? 'Hakuna namba ya simu' }}</p>
                </div>
            </div>

            <!-- Right Column: Forms -->
            <div style="display: flex; flex-direction: column; gap: 32px;">
                
                <!-- Update Details Form -->
                <div class="glass-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            Taarifa za Msingi
                        </h3>
                        <p class="card-description">Badilisha jina lako, barua pepe, na namba ya simu.</p>
                    </div>

                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="form-group">
                            <label for="name" class="form-label">Jina Kamili</label>
                            <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                            @error('name') <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Barua Pepe</label>
                            <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                            @error('email') <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Phone Field (Assuming 'phone' column exists in users table) -->
                        <div class="form-group">
                            <label for="phone" class="form-label">Namba ya Simu</label>
                            <input type="text" id="phone" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}" placeholder="07xxxxxxxx">
                            @error('phone') <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span> @enderror
                        </div>

                        <div style="text-align: right;">
                            <button type="submit" class="btn btn-primary">Hifadhi Mabadiliko</button>
                        </div>
                    </form>
                </div>

                <!-- Update Password Form -->
                <div class="glass-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--success);"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Usalama
                        </h3>
                        <p class="card-description">Badilisha nenosiri lako ili kulinda akaunti yako.</p>
                    </div>

                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="form-group">
                            <label for="current_password" class="form-label">Nenosiri la Sasa</label>
                            <input type="password" id="current_password" name="current_password" class="form-input" autocomplete="current-password">
                            @error('current_password', 'updatePassword') <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Nenosiri Jipya</label>
                            <input type="password" id="password" name="password" class="form-input" autocomplete="new-password">
                            @error('password', 'updatePassword') <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Thibitisha Nenosiri Jipya</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" autocomplete="new-password">
                            @error('password_confirmation', 'updatePassword') <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span> @enderror
                        </div>

                        <div style="text-align: right;">
                            <button type="submit" class="btn btn-primary">Badilisha Nenosiri</button>
                        </div>
                    </form>
                </div>

                <!-- Delete Account -->
                <div class="glass-card" style="border-color: rgba(239, 68, 68, 0.3);">
                    <div class="card-header" style="border-bottom-color: rgba(239, 68, 68, 0.1);">
                        <h3 class="card-title" style="color: var(--danger);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            Futa Akaunti
                        </h3>
                        <p class="card-description">Ukifuta akaunti yako, data zote zitapotea milele.</p>
                    </div>

                    <p style="color: var(--secondary); margin-bottom: 20px;">
                        Tafadhali kuwa makini. Kitendo hiki hakiwezi kubatilishwa.
                    </p>

                    <div style="text-align: right;">
                        <button type="button" onclick="document.getElementById('delete-modal').style.display='flex'" class="btn btn-danger">
                            Futa Akaunti Yangu
                        </button>
                    </div>

                    <!-- Delete Account Modal -->
                    <div id="delete-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 9999;">
                        <div style="background: white; border-radius: 16px; padding: 32px; max-width: 400px; width: 90%; box-shadow: 0 20px 50px rgba(0,0,0,0.3);">
                            <h3 style="margin: 0 0 12px 0; color: var(--danger); font-size: 1.25rem;">Thibitisha Kufuta Akaunti</h3>
                            <p style="color: #64748b; margin-bottom: 20px;">Weka nenosiri lako kuthibitisha. Kitendo hiki hakiwezi kubatilishwa.</p>
                            
                            <form method="post" action="{{ route('profile.destroy') }}">
                                @csrf
                                @method('delete')
                                
                                <div class="form-group">
                                    <label for="delete_password" class="form-label">Nenosiri</label>
                                    <input type="password" id="delete_password" name="password" class="form-input" required placeholder="Weka nenosiri lako">
                                    @error('password', 'userDeletion') <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span> @enderror
                                </div>
                                
                                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                                    <button type="button" onclick="document.getElementById('delete-modal').style.display='none'" class="btn" style="background: #e2e8f0;">
                                        Ghairi
                                    </button>
                                    <button type="submit" class="btn btn-danger">
                                        Futa Akaunti
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<script>
    // Preview image on selection
    const photoInput = document.getElementById('photo');
    const previewImage = document.getElementById('preview-image');
    const photoForm = document.getElementById('photo-form');
    const photoActions = document.getElementById('photo-actions');

    photoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('Picha ni kubwa sana! Tafadhali chagua picha isiyozidi 5MB.');
                this.value = '';
                return;
            }
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Tafadhali chagua faili ya picha tu!');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                photoActions.style.display = 'block';
            }
            reader.onerror = function() {
                alert('Kuna tatizo la kusoma picha. Tafadhali jaribu tena.');
            }
            reader.readAsDataURL(file);
        }
    });

    // Handle form submission
    photoForm.addEventListener('submit', function(e) {
        if (!photoInput.files || !photoInput.files[0]) {
            e.preventDefault();
            alert('Tafadhali chagua picha ya kwanza!');
            return false;
        }
        
        // Show loading state
        const submitBtn = photoForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Inapakia...';
        }
    });

    // Cancel photo upload
    function cancelPhotoUpload() {
        photoInput.value = '';
        photoActions.style.display = 'none';
        // Reload the original image
        previewImage.src = '{{ $user->profile_photo_url }}';
    }

    // Refresh image after successful upload (if page doesn't reload)
    @if (session('status') === 'profile-updated')
        // Force refresh the image
        setTimeout(function() {
            const timestamp = new Date().getTime();
            previewImage.src = '{{ $user->profile_photo_url }}&t=' + timestamp;
        }, 500);
    @endif
</script>
@endsection
