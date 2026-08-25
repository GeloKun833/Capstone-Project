<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Enrollment Management') — PMSI</title>
    <link rel="stylesheet" href="{{ URL::to('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/enrollment-portal.css') }}">
    @yield('styles')
</head>
<body class="ep-body">
<script>try{if(localStorage.getItem('ep-dark-mode')==='true')document.body.classList.add('ep-dark');}catch(e){}</script>
    <div class="ep-overlay" id="epOverlay"></div>
    <div class="ep-app">
        <aside class="ep-sidebar" id="epSidebar">
            <div class="ep-brand">
                <img src="{{ URL::to('assets/img/Logo.jpg') }}" alt="Logo">
                <div class="ep-brand-text">
                    <h1>Enrollment Admin</h1>
                    <span>Registrar Portal</span>
                </div>
            </div>
            <nav class="ep-nav">
                <div class="ep-nav-group-title">Overview</div>
                <a href="{{ route('dashboard') }}" class="ep-nav-link"><i class="fas fa-gauge-high"></i><span class="ep-nav-label">Main Dashboard</span></a>
                <a href="{{ route('enrollment.registrar.index') }}" class="ep-nav-link {{ request()->routeIs('enrollment.registrar.index') ? 'active' : '' }}"><i class="fas fa-inbox"></i><span class="ep-nav-label">Applications</span></a>
                <a href="{{ route('enrollment.registrar.statistics') }}" class="ep-nav-link {{ request()->routeIs('enrollment.registrar.statistics') ? 'active' : '' }}"><i class="fas fa-chart-pie"></i><span class="ep-nav-label">Analytics</span></a>
                <a href="{{ route('enrollment.registrar.archive') }}" class="ep-nav-link {{ request()->routeIs('enrollment.registrar.archive') ? 'active' : '' }}"><i class="fas fa-box-archive"></i><span class="ep-nav-label">Archive</span></a>
                <div class="ep-nav-group-title">Portal</div>
                <a href="{{ route('enrollment.portal.index') }}" target="_blank" class="ep-nav-link"><i class="fas fa-external-link"></i><span class="ep-nav-label">Public Portal</span></a>
            </nav>
            <div class="ep-sidebar-footer">
                <div class="ep-user-card">
                    <div class="ep-user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'R', 0, 1)) }}</div>
                    <div class="ep-user-info">
                        <div class="name">{{ auth()->user()->name ?? 'Registrar' }}</div>
                        <div class="role">{{ auth()->user()->role_name ?? 'Admin' }}</div>
                    </div>
                </div>
                <a href="{{ route('logout') }}" class="ep-nav-link mt-2" style="color:#FCA5A5;"><i class="fas fa-right-from-bracket"></i><span class="ep-nav-label">Logout</span></a>
            </div>
        </aside>
        <div class="ep-main">
            <header class="ep-topbar">
                <div class="ep-topbar-left">
                    <button class="ep-menu-toggle" data-ep-toggle-sidebar><i class="fas fa-bars"></i></button>
                    <div class="ep-page-title" style="font-size:1.1rem;margin:0;">@yield('title')</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="ep-dark-toggle" data-ep-toggle-dark aria-label="Toggle dark mode">
                        <i class="fas fa-moon" data-ep-dark-icon></i>
                    </button>
                    @yield('topbar-actions')
                </div>
            </header>
            <main class="ep-page-content">
                <div class="container-fluid">
                    @if(session('success'))<div class="ep-alert ep-alert-success">{{ session('success') }}</div>@endif
                    @if(session('error'))<div class="ep-alert ep-alert-danger">{{ session('error') }}</div>@endif
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <script src="{{ URL::to('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>
    <script src="{{ asset('assets/js/enrollment-portal.js') }}"></script>
    @yield('scripts')
    @include('partials.toastr-scripts')
</body>
</html>
