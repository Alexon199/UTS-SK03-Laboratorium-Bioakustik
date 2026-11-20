<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Lab Bioakustik') · Lab Bioakustik</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- Pasang tema tersimpan sebelum halaman digambar, supaya tidak berkedip --}}
    <script>try { var t = localStorage.getItem('theme'); if (t) { document.documentElement.setAttribute('data-theme', t); } } catch (e) {}</script>
</head>
<body>
@include('partials.icons')

<div class="shell">
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="brand">
            <span class="brand-mark">
                <svg viewBox="0 0 32 32" aria-hidden="true"><rect x="3" y="13" width="3" height="6" rx="1.5"/><rect x="9" y="8" width="3" height="16" rx="1.5"/><rect x="15" y="3" width="3" height="26" rx="1.5"/><rect x="21" y="9" width="3" height="14" rx="1.5"/><rect x="27" y="12" width="3" height="8" rx="1.5"/></svg>
            </span>
            <span class="brand-text"><strong>Lab Bioakustik</strong><small>Metadata rekaman lapangan</small></span>
        </a>

        <nav class="nav" aria-label="Menu utama">
            <span class="nav-label">Menu</span>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}"><svg class="ico"><use href="#i-grid"/></svg>Dashboard</a>
            <a href="{{ route('recordings.index') }}" class="{{ request()->routeIs('recordings.*') ? 'is-active' : '' }}"><svg class="ico"><use href="#i-wave"/></svg>Rekaman</a>
            <a href="{{ route('species.index') }}" class="{{ request()->routeIs('species.*') ? 'is-active' : '' }}"><svg class="ico"><use href="#i-feather"/></svg>Spesies</a>
        </nav>

        <div class="side-foot">
            <a class="btn btn-primary" href="{{ route('recordings.create') }}"><svg class="ico"><use href="#i-plus"/></svg>Rekaman baru</a>
            <div class="side-card"><strong>Satu rekaman = satu titik</strong>dan boleh punya banyak anotasi suara.</div>
            <button type="button" class="theme-btn" data-theme-toggle>
                <svg class="ico ico-moon"><use href="#i-moon"/></svg><svg class="ico ico-sun"><use href="#i-sun"/></svg>
                Ganti tema
            </button>
        </div>
    </aside>
    <div class="scrim"></div>

    <div class="content">
        <header class="topbar">
            <a href="{{ route('dashboard') }}" class="brand">
                <span class="brand-mark"><svg viewBox="0 0 32 32" aria-hidden="true"><rect x="3" y="13" width="3" height="6" rx="1.5"/><rect x="9" y="8" width="3" height="16" rx="1.5"/><rect x="15" y="3" width="3" height="26" rx="1.5"/><rect x="21" y="9" width="3" height="14" rx="1.5"/><rect x="27" y="12" width="3" height="8" rx="1.5"/></svg></span>
                <span class="brand-text"><strong>Lab Bioakustik</strong></span>
            </a>
            <button type="button" class="icon-btn" data-nav-toggle aria-label="Buka menu"><svg class="ico"><use href="#i-menu"/></svg></button>
        </header>

        <main class="page">
            @yield('content')
        </main>

        <footer class="page-foot">Administrasi metadata rekaman suara lapangan · Laravel {{ app()->version() }}</footer>
    </div>
</div>

{{-- Notifikasi flash (session) --}}
<div class="toast-zone" aria-live="polite">
    @if (session('success'))
        <div class="toast" role="status">
            <svg class="ico" style="color: var(--good)"><use href="#i-check"/></svg>
            <span>{{ session('success') }}</span>
            <button type="button" data-toast-close aria-label="Tutup"><svg class="ico"><use href="#i-x"/></svg></button>
        </div>
    @endif
</div>

{{-- Dialog konfirmasi hapus (dipakai semua form yang punya data-confirm) --}}
<dialog class="confirm" id="confirm-dialog" aria-labelledby="confirm-title">
    <div class="confirm-icon"><svg class="ico"><use href="#i-alert"/></svg></div>
    <h2 id="confirm-title">Hapus data ini?</h2>
    <p class="muted" data-confirm-message style="margin-top:.4rem"></p>
    <div class="confirm-actions">
        <button type="button" class="btn btn-ghost" data-confirm-cancel>Batal</button>
        <button type="button" class="btn btn-solid-danger" data-confirm-ok>Ya, hapus</button>
    </div>
</dialog>

<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
