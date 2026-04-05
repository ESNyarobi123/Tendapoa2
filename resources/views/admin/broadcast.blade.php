@extends('layouts.admin')
@section('title', 'Admin — Tangazo la taarifa')

@section('content')
<div class="adm-subpage adm-stack" style="max-width:42rem;">
    <div class="adm-page-head">
        <div>
            <h1 class="adm-page-head-title">Tuma taarifa (broadcast)</h1>
            <p class="adm-page-head-sub">Ujumbe utatumwa kama arifa kwa watumiaji walio na programu / wavuti</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="adm-btn adm-btn--muted">← Dashibodi</a>
    </div>

    <div class="adm-card">
        <form method="POST" action="{{ route('admin.broadcast.send') }}">
            @csrf

            <div class="adm-form-group">
                <label class="adm-label" for="title">Kichwa</label>
                <input id="title" class="adm-input" type="text" name="title" value="{{ old('title') }}" required autofocus>
                @error('title')
                    <p class="adm-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="adm-form-group">
                <label class="adm-label" for="target">Lengo</label>
                <select id="target" name="target" class="adm-select">
                    <option value="all" @selected(old('target', 'all') === 'all')>Watumiaji wote</option>
                    <option value="muhitaji" @selected(old('target') === 'muhitaji')>Wahitaji tu</option>
                    <option value="mfanyakazi" @selected(old('target') === 'mfanyakazi')>Wafanyakazi tu</option>
                </select>
                @error('target')
                    <p class="adm-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="adm-form-group">
                <label class="adm-label" for="message">Ujumbe</label>
                <textarea id="message" name="message" class="adm-textarea" required>{{ old('message') }}</textarea>
                @error('message')
                    <p class="adm-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="adm-actions" style="justify-content:flex-end;">
                <button type="submit" class="adm-btn adm-btn--primary">Tuma taarifa</button>
            </div>
        </form>
    </div>
</div>
@endsection
