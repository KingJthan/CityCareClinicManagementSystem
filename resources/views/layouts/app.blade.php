<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CityCare Clinic')</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="{{ auth()->check() ? 'workspace-body' : 'public-body' }}">
    @php
        $careCartCount = collect(session('care_cart', []))->sum();
    @endphp
    @auth
        @php
            $user = auth()->user();
            $roleLabels = [
                'admin' => 'Administrator',
                'receptionist' => 'Receptionist',
                'doctor' => 'Doctor',
                'cashier' => 'Cashier',
                'pharmacist' => 'Pharmacist',
                'radiology' => 'Radiology',
                'rn' => 'RN',
                'pct' => 'PCT',
                'housekeeping' => 'House Keeping',
                'nurse' => 'Nurse',
                'dietary' => 'Dietary',
                'patient' => 'Patient',
            ];
            $roleLabel = $roleLabels[$user->role] ?? ucfirst($user->role);
            $adminPortrait = $user->hasRole('admin') ? asset('images/jonathan-admin-portrait.png') : null;
        @endphp

        <div class="workspace-layout">
            <aside class="app-sidebar d-none d-lg-flex">
                @include('partials.sidebar-nav', ['user' => $user])
            </aside>

            <div class="app-main">
                <header class="app-topbar">
                    <div class="d-flex align-items-center gap-2 gap-sm-3">
                        <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                            Menu
                        </button>
                        <div>
                            <div class="eyebrow mb-1">CityCare Workspace</div>
                            <div class="small text-muted">Coordinated clinic operations for {{ $roleLabel }}</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary theme-toggle-btn" type="button" data-theme-toggle aria-label="Toggle light and dark mode">
                            <span class="theme-toggle-icon" data-theme-icon>Dark</span>
                        </button>

                        <div class="workspace-chip">
                            <span class="workspace-chip-avatar {{ $adminPortrait ? 'is-photo' : '' }}">
                                @if($adminPortrait)
                                    <img src="{{ $adminPortrait }}" alt="{{ $user->name }}">
                                @else
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                @endif
                            </span>
                            <div class="d-none d-sm-block">
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <div class="small text-muted">{{ $roleLabel }}</div>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="page-shell page-shell-workspace">
                    <div class="container-fluid px-3 px-lg-4 py-4">
                        <x-alert />
                        @yield('content')
                    </div>
                </main>

                <footer class="workspace-footer">
                    <div class="container-fluid px-3 px-lg-4 d-flex flex-column flex-md-row justify-content-between gap-2 small text-muted">
                        <span>CityCare Medical Centre appointment and patient management system</span>
                        <span>{{ date('Y') }} Clinic operations workspace</span>
                    </div>
                </footer>
            </div>
        </div>

        <div class="offcanvas offcanvas-start workspace-offcanvas d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="mobileSidebarLabel">CityCare Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                @include('partials.sidebar-nav', ['user' => $user])
            </div>
        </div>
    @else
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top public-nav">
            <div class="container-fluid px-3 px-lg-4">
                <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
                    <span class="brand-mark">CC</span>
                    <span>CityCare</span>
                </a>

                <div class="d-none d-lg-flex align-items-center gap-3 ms-auto me-3">
                    <a class="nav-link px-0" href="{{ route('home') }}">Home</a>
                    <a class="nav-link px-0" href="{{ route('services') }}">Services</a>
                    <a class="nav-link px-0" href="{{ route('shop.index') }}">Care shop</a>
                    <a class="nav-link px-0" href="{{ route('about') }}">About</a>
                    <a class="nav-link px-0" href="{{ route('location') }}">Location</a>
                    <a class="nav-link px-0" href="{{ route('contact') }}">Contact us</a>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary theme-toggle-btn" type="button" data-theme-toggle aria-label="Toggle light and dark mode">
                        <span data-theme-icon>Dark</span>
                    </button>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('cart.index') }}">
                        Cart
                        @if($careCartCount > 0)
                            <span class="badge text-bg-dark ms-1">{{ $careCartCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Staff access
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('staff.login') }}">Staff portal login</a></li>
                            <li><a class="dropdown-item" href="{{ route('staff.register') }}">Staff registration</a></li>
                        </ul>
                    </div>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('register') }}">Patient sign up</a>
                    <a class="btn btn-sm btn-dark" href="{{ route('login') }}">Patient login</a>
                </div>
            </div>
        </nav>

        <main class="public-shell">
            <div class="container-fluid px-3 px-lg-4 pt-3">
                <x-alert />
            </div>
            @yield('content')
        </main>

        <footer class="public-footer">
            <div class="container-fluid px-3 px-lg-4 d-flex flex-column flex-md-row justify-content-between gap-2 small text-muted">
                <span>CityCare Medical Centre appointment and patient management system</span>
                <span>{{ date('Y') }} Modern clinic access and operations portal</span>
            </div>
        </footer>
    @endauth

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script>
        (function () {
            const root = document.documentElement;
            const storageKey = 'citycare-theme';
            const applyTheme = function (theme) {
                root.setAttribute('data-theme', theme);
                document.querySelectorAll('[data-theme-icon]').forEach(function (label) {
                    label.textContent = theme === 'dark' ? 'Light' : 'Dark';
                });
            };

            const storedTheme = localStorage.getItem(storageKey);
            applyTheme(storedTheme === 'dark' ? 'dark' : 'light');

            document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                    localStorage.setItem(storageKey, nextTheme);
                    applyTheme(nextTheme);
                });
            });
        })();

        document.addEventListener('submit', function (event) {
            if (event.target.matches('[data-confirm]') && !confirm(event.target.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });

        document.querySelectorAll('.alert[data-auto-dismiss]').forEach(function (element) {
            const timeout = Number(element.getAttribute('data-auto-dismiss')) || 4500;
            const alertInstance = bootstrap.Alert.getOrCreateInstance(element);

            window.setTimeout(function () {
                if (element.isConnected) {
                    alertInstance.close();
                }
            }, timeout);
        });
    </script>
    @stack('scripts')
</body>
</html>
