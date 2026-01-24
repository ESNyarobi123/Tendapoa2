@extends('layouts.admin')

@section('title', 'Categories - Admin')

@section('content')
<style>
  .page-container {
    --primary: #6366f1;
    --secondary: #06b6d4;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #f43f5e;
    --purple: #8b5cf6;
    --card-bg: rgba(255,255,255,0.05);
    --card-bg-hover: rgba(255,255,255,0.08);
    --text-primary: #ffffff;
    --text-muted: #94a3b8;
    --border: rgba(255,255,255,0.1);
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.3);
  }

  .categories-container {
    max-width: 1000px;
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

  /* Alert Messages */
  .alert {
    padding: 16px 20px;
    border-radius: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .alert-success {
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.3);
    color: #10b981;
  }
  
  .alert-error {
    background: rgba(244, 63, 94, 0.15);
    border: 1px solid rgba(244, 63, 94, 0.3);
    color: #f43f5e;
  }

  /* Cards */
  .card {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 32px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
  }

  .card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  /* Form */
  .form-row {
    display: flex;
    gap: 12px;
  }

  .form-input {
    flex: 1;
    padding: 14px 18px;
    border: 2px solid var(--border);
    border-radius: 12px;
    font-size: 1rem;
    background: rgba(255,255,255,0.05);
    color: var(--text-primary);
    transition: all 0.3s ease;
  }

  .form-input::placeholder {
    color: rgba(255,255,255,0.4);
  }

  .form-input:focus {
    outline: none;
    border-color: #6366f1;
    background: rgba(255,255,255,0.08);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
  }

  /* Buttons */
  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 24px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 1rem;
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
    padding: 12px 20px;
  }

  .btn-outline:hover {
    background: var(--primary);
    color: white;
  }

  .btn-danger {
    background: linear-gradient(135deg, #f43f5e, #dc2626);
    color: white;
    box-shadow: 0 4px 14px 0 rgba(244, 63, 94, 0.4);
  }

  .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px 0 rgba(244, 63, 94, 0.6);
  }

  .btn-sm {
    padding: 8px 16px;
    font-size: 0.875rem;
  }

  /* Table */
  .categories-table {
    width: 100%;
    border-collapse: collapse;
  }

  .categories-table th,
  .categories-table td {
    padding: 16px;
    text-align: left;
    border-bottom: 1px solid var(--border);
  }

  .categories-table th {
    font-weight: 700;
    color: var(--text-muted);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .categories-table td {
    color: var(--text-primary);
  }

  .categories-table tbody tr {
    transition: background 0.3s;
  }

  .categories-table tbody tr:hover {
    background: rgba(255,255,255,0.03);
  }

  .category-name {
    font-weight: 600;
    font-size: 1rem;
  }

  .category-slug {
    color: var(--text-muted);
    font-size: 0.875rem;
    font-family: monospace;
  }

  .job-count {
    background: linear-gradient(135deg, var(--primary), var(--purple));
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    display: inline-block;
  }

  .actions {
    display: flex;
    gap: 8px;
  }

  /* Edit Modal */
  .modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 2000;
    align-items: center;
    justify-content: center;
  }

  .modal-overlay.active {
    display: flex;
  }

  .modal-content {
    background: linear-gradient(135deg, #1a1a3e 0%, #0f0f23 100%);
    border-radius: 20px;
    padding: 32px;
    width: 100%;
    max-width: 450px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow-lg);
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
  }

  .modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
  }

  .modal-close {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 24px;
    cursor: pointer;
    transition: color 0.3s;
  }

  .modal-close:hover {
    color: var(--text-primary);
  }

  .modal-footer {
    display: flex;
    gap: 12px;
    margin-top: 24px;
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 48px 24px;
    color: var(--text-muted);
  }

  .empty-state-icon {
    font-size: 48px;
    margin-bottom: 16px;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .header-content {
      grid-template-columns: 1fr;
      text-align: center;
    }
    
    .form-row {
      flex-direction: column;
    }

    .actions {
      flex-direction: column;
    }
  }
</style>

<div class="categories-container">
  <!-- Page Header -->
  <div class="page-header">
    <div class="header-content">
      <div class="header-text">
        <h1>📁 Category Management</h1>
        <p>Add, edit, and manage service categories</p>
      </div>
      <div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">
          <span>↩️</span>
          Back to Dashboard
        </a>
      </div>
    </div>
  </div>

  <!-- Alerts -->
  @if(session('success'))
    <div class="alert alert-success">
      <span>✅</span>
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-error">
      <span>❌</span>
      {{ session('error') }}
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-error">
      <span>❌</span>
      {{ $errors->first() }}
    </div>
  @endif

  <!-- Add New Category -->
  <div class="card">
    <h2 class="card-title">
      <span>➕</span>
      Add New Category
    </h2>
    <form action="{{ route('admin.categories.store') }}" method="POST">
      @csrf
      <div class="form-row">
        <input type="text" name="name" class="form-input" placeholder="Enter category name (e.g., Plumbing, Electrician, Gardening)" required>
        <button type="submit" class="btn btn-primary">
          <span>💾</span>
          Add Category
        </button>
      </div>
    </form>
  </div>

  <!-- Categories List -->
  <div class="card">
    <h2 class="card-title">
      <span>📋</span>
      All Categories ({{ $categories->count() }})
    </h2>

    @if($categories->count() > 0)
      <table class="categories-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Slug</th>
            <th>Jobs</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($categories as $category)
          <tr>
            <td>
              <div class="category-name">{{ $category->name }}</div>
            </td>
            <td>
              <span class="category-slug">{{ $category->slug }}</span>
            </td>
            <td>
              <span class="job-count">{{ $category->jobs_count }} jobs</span>
            </td>
            <td>
              {{ $category->created_at ? $category->created_at->format('M d, Y') : 'N/A' }}
            </td>
            <td>
              <div class="actions">
                <button type="button" class="btn btn-outline btn-sm" onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}')">
                  ✏️ Edit
                </button>
                <form action="{{ route('admin.categories.delete', $category) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete \'{{ addslashes($category->name) }}\'?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">
                    🗑️ Delete
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="empty-state">
        <div class="empty-state-icon">📁</div>
        <p>No categories found. Add your first category above!</p>
      </div>
    @endif
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 class="modal-title">✏️ Edit Category</h3>
      <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
    </div>
    <form id="editForm" method="POST">
      @csrf
      @method('PUT')
      <input type="text" name="name" id="editCategoryName" class="form-input" placeholder="Category name" required>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <span>💾</span>
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openEditModal(id, name) {
    document.getElementById('editForm').action = '/admin/categories/' + id;
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editModal').classList.add('active');
  }

  function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
  }

  // Close modal on outside click
  document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeEditModal();
    }
  });

  // Close modal on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeEditModal();
    }
  });
</script>
@endsection
