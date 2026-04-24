@php
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

    $navItems = array_values(array_filter([
        [
            'label' => 'Dashboard',
            'route' => workspace_route('dashboard'),
            'match' => ['dashboard', 'workspace.dashboard'],
            'icon' => 'DB',
        ],
        $user->hasRole('patient') ? [
            'label' => 'My Profile',
            'route' => workspace_route('patients.profile'),
            'match' => ['patients.profile', 'workspace.patients.profile'],
            'icon' => 'PR',
        ] : null,
        $user->hasRole('patient') ? [
            'label' => 'Care Shop',
            'route' => workspace_route('shop.index'),
            'match' => ['shop.*', 'workspace.shop.*'],
            'icon' => 'CS',
        ] : null,
        $user->hasRole('patient') ? [
            'label' => 'Cart',
            'route' => workspace_route('cart.index'),
            'match' => ['cart.*', 'workspace.cart.*'],
            'icon' => 'CT',
        ] : null,
        $user->hasRole(['admin', 'receptionist', 'doctor', 'rn', 'pct', 'nurse']) ? [
            'label' => 'Patients',
            'route' => workspace_route('patients.index'),
            'match' => ['patients.*', 'workspace.patients.*'],
            'icon' => 'PT',
        ] : null,
        $user->hasRole(['admin', 'receptionist', 'doctor', 'patient']) ? [
            'label' => 'Appointments',
            'route' => workspace_route('appointments.index'),
            'match' => ['appointments.*', 'consultations.*', 'workspace.appointments.*', 'workspace.consultations.*'],
            'icon' => 'AP',
        ] : null,
        $user->hasRole(['admin', 'cashier', 'patient']) ? [
            'label' => 'Billing',
            'route' => workspace_route('payments.index'),
            'match' => ['payments.*', 'workspace.payments.*'],
            'icon' => 'PY',
        ] : null,
        [
            'label' => 'Documents',
            'route' => workspace_route('documents.index'),
            'match' => ['documents.*', 'workspace.documents.*'],
            'icon' => 'DO',
        ],
        $user->hasRole('pharmacist') ? [
            'label' => 'Pharmacy',
            'route' => workspace_route('prescriptions.index'),
            'match' => ['prescriptions.*', 'workspace.prescriptions.*'],
            'icon' => 'RX',
        ] : null,
        $user->hasRole('pharmacist') ? [
            'label' => 'Drugs',
            'route' => workspace_route('drugs.index'),
            'match' => ['drugs.*', 'workspace.drugs.*'],
            'icon' => 'DG',
        ] : null,
        $user->hasRole('pharmacist') ? [
            'label' => 'Drug Categories',
            'route' => workspace_route('drug-categories.index'),
            'match' => ['drug-categories.*', 'workspace.drug-categories.*'],
            'icon' => 'DC',
        ] : null,
        $user->hasRole(['admin', 'radiology']) ? [
            'label' => 'Radiology',
            'route' => workspace_route('radiology-orders.index'),
            'match' => ['radiology-orders.*', 'workspace.radiology-orders.*'],
            'icon' => 'RD',
        ] : null,
        $user->hasRole(['admin', 'receptionist']) ? [
            'label' => 'Doctors',
            'route' => workspace_route('doctors.index'),
            'match' => ['doctors.*', 'workspace.doctors.*'],
            'icon' => 'DR',
        ] : null,
        $user->hasRole('admin') ? [
            'label' => 'Departments',
            'route' => workspace_route('departments.index'),
            'match' => ['departments.*', 'workspace.departments.*'],
            'icon' => 'DP',
        ] : null,
        [
            'label' => 'Reports',
            'route' => workspace_route('reports.index'),
            'match' => ['reports.*', 'workspace.reports.*'],
            'icon' => 'RP',
        ],
    ]));
@endphp

<div class="sidebar-shell">
    <div class="sidebar-brand">
        <span class="brand-mark">CC</span>
        <div>
            <div class="fw-bold">CityCare</div>
            <div class="small text-white-50">Clinic workspace</div>
        </div>
    </div>

    <div class="sidebar-user-card">
        <div class="sidebar-user-avatar {{ $adminPortrait ? 'is-photo' : '' }}">
            @if($adminPortrait)
                <img src="{{ $adminPortrait }}" alt="{{ $user->name }}">
            @else
                {{ strtoupper(substr($user->name, 0, 1)) }}
            @endif
        </div>
        <div>
            <div class="fw-semibold">{{ $user->name }}</div>
            <div class="small text-white-50">{{ $roleLabel }}</div>
        </div>
    </div>

    <div class="sidebar-section-label">Workspace</div>
    <nav class="sidebar-nav">
        @foreach($navItems as $item)
            @php
                $isActive = collect($item['match'])->contains(fn ($pattern) => request()->routeIs($pattern));
            @endphp
            <a class="sidebar-link {{ $isActive ? 'active' : '' }}" href="{{ $item['route'] }}">
                <span class="sidebar-icon">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-note">
        <div class="sidebar-section-label mb-2">Role Focus</div>
        <p class="mb-0">{{ $roleLabel }} access is tailored to daily clinic duties, records, and reporting needs.</p>
    </div>

    <div class="sidebar-section-label">Account</div>
    <div class="sidebar-nav">
        <a class="sidebar-link {{ request()->routeIs('password.*', 'workspace.password.*') ? 'active' : '' }}" href="{{ workspace_route('password.edit') }}">
            <span class="sidebar-icon">PW</span>
            <span>Password</span>
        </a>
        <form method="POST" action="{{ workspace_route('logout') }}">
            @csrf
            <button class="sidebar-action" type="submit">
                <span class="sidebar-icon">LO</span>
                <span>Logout</span>
            </button>
        </form>
    </div>
</div>
