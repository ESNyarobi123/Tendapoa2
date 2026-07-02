@extends('layouts.admin')
@section('title', 'Admin — Makundi ya Huduma')

@section('content')
<div class="adm-page">
    @include('admin.partials.page-hero', [
        'title' => 'Makundi ya Huduma',
        'subtitle' => 'Ongeza, hariri, na simamia kategoria za huduma',
        'icon' => '📁',
        'actions' => '<a class="adm-btn adm-btn--ghost" href="' . route('admin.dashboard') . '">↩️ Dashibodi</a>',
    ])

    <div class="adm-card">
        <h2 class="adm-card-title">➕ Ongeza kategoria mpya</h2>
        <form action="{{ route('admin.categories.store') }}" method="POST" class="adm-form-inline">
            @csrf
            <input type="text" name="name" class="adm-input" value="{{ old('name') }}"
                placeholder="Mfano: Usafi, Umeme, Bustani…" required maxlength="120">
            <button type="submit" class="adm-btn adm-btn--primary">Hifadhi</button>
        </form>
    </div>

    <div class="adm-card">
        <h2 class="adm-card-title">📋 Makundi yote ({{ $categories->count() }})</h2>

        @if($categories->count())
            <div class="adm-table-wrap">
                <table class="adm-table adm-table--responsive">
                    <thead>
                        <tr>
                            <th>Jina</th>
                            <th>Slug</th>
                            <th>Kazi</th>
                            <th>Imeundwa</th>
                            <th>Vitendo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td data-label="Jina">
                                    <strong>{{ $category->name }}</strong>
                                </td>
                                <td data-label="Slug">
                                    <span class="adm-table__slug">{{ $category->slug }}</span>
                                </td>
                                <td data-label="Kazi">
                                    <span class="adm-pill adm-pill--posted">{{ $category->jobs_count }} kazi</span>
                                </td>
                                <td data-label="Imeundwa">
                                    {{ $category->created_at?->format('d M Y') ?? '—' }}
                                </td>
                                <td data-label="Vitendo">
                                    <div class="adm-table__actions">
                                        <button type="button" class="adm-btn adm-btn--ghost adm-btn--sm"
                                            onclick="admOpenCategoryEdit({{ $category->id }}, @json($category->name))">
                                            ✏️ Hariri
                                        </button>
                                        <form action="{{ route('admin.categories.delete', $category) }}" method="POST" class="adm-inline-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="adm-btn adm-btn--danger adm-btn--sm"
                                                onclick="var f=this.closest('form'); (typeof tpConfirm==='function'?tpConfirm(@json('Futa kategoria: '.$category->name.'?')):Promise.resolve(confirm(@json('Futa?')))).then(function(ok){ if(ok) f.submit(); });">
                                                🗑️ Futa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="adm-empty">
                <span class="adm-empty__ico" aria-hidden="true">📁</span>
                <h3>Hakuna makundi</h3>
                <p>Ongeza kategoria ya kwanza hapo juu.</p>
            </div>
        @endif
    </div>
</div>

<div id="category-edit-modal" class="adm-modal hidden" role="dialog" aria-modal="true" aria-labelledby="category-edit-title">
    <div class="adm-modal-backdrop" data-adm-modal-close></div>
    <div class="adm-modal-panel">
        <h3 id="category-edit-title" class="adm-modal-title">Hariri kategoria</h3>
        <form id="category-edit-form" method="POST" action="">
            @csrf
            @method('PUT')
            <label class="adm-label" for="category-edit-name">Jina</label>
            <input type="text" id="category-edit-name" name="name" class="adm-input" required maxlength="120">
            <div class="adm-modal-actions">
                <button type="button" class="adm-btn adm-btn--ghost" data-adm-modal-close>Funga</button>
                <button type="submit" class="adm-btn adm-btn--primary">Hifadhi</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
window.admOpenCategoryEdit = function (id, name) {
    const modal = document.getElementById('category-edit-modal');
    const form = document.getElementById('category-edit-form');
    const input = document.getElementById('category-edit-name');
    if (!modal || !form) return;
    form.action = @json(rtrim(url('/admin/categories'), '/')) + '/' + id;
    input.value = name;
    modal.classList.remove('hidden');
};
</script>
@endpush
@endsection
