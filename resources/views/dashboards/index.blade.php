@extends('layouts.app')

@section('title', 'Dashboard | CityCare')

@section('content')
    <div class="dashboard-hero-grid mb-4">
        <section class="dashboard-hero-panel">
            <div class="dashboard-hero-copy">
                <span class="dashboard-hero-tag">{{ $currentDateLabel }}</span>
                <h1>{{ $roleLabel }} Dashboard</h1>
                <p>{{ $dashboardSubtitle }}</p>
            </div>
            <div class="dashboard-hero-actions">
                @if(auth()->user()->hasRole(['admin', 'receptionist']))
                    <a class="btn btn-dark" href="{{ workspace_route('appointments.create') }}">Book appointment</a>
                @endif
                @if(auth()->user()->hasRole('cashier'))
                    <a class="btn btn-dark" href="{{ workspace_route('payments.create') }}">Record payment</a>
                @endif
                @if(auth()->user()->hasRole('patient'))
                    <a class="btn btn-dark" href="{{ workspace_route('patients.profile') }}">My profile</a>
                @endif
                <a class="btn btn-outline-secondary" href="{{ workspace_route('reports.index') }}">Reports</a>
                <a class="btn btn-outline-secondary" href="{{ workspace_route('documents.index') }}">Documents</a>
            </div>
        </section>

        <aside class="dashboard-focus-panel">
            <div class="dashboard-focus-header">
                <div>
                    <span class="dashboard-focus-label">Today board</span>
                    <h2>At-a-glance activity</h2>
                </div>
                <span class="dashboard-focus-badge">{{ $upcomingAppointments->count() }} queued</span>
            </div>
            <div class="dashboard-focus-list">
                @forelse($upcomingAppointments->take(3) as $appointment)
                    <div class="dashboard-focus-item">
                        <div class="dashboard-focus-time">{{ substr($appointment->start_time, 0, 5) }}</div>
                        <div>
                            <strong>{{ $appointment->patient->full_name }}</strong>
                            <div class="small text-muted">{{ $appointment->doctor->display_name }} - {{ ucfirst($appointment->status) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">No appointment items are queued right now.</div>
                @endforelse
            </div>
        </aside>
    </div>

    <div class="dashboard-metrics mb-4">
        @foreach($metricCards as $metric)
            <x-metric-card :label="$metric['label']" :value="$metric['value']" :tone="$metric['tone']" :icon="$metric['icon']" :note="$metric['note']" />
        @endforeach
    </div>

    @if(!empty($chartData))
        <div class="dashboard-chart-grid mb-4">
            @foreach($chartData as $chart)
                @php
                    $colors = ['#156d7a', '#249b61', '#3577a8', '#b88a2d', '#8256d0', '#c14f43'];
                    $total = collect($chart['items'])->sum('value');
                    $start = 0;
                    $segments = [];
                    foreach ($chart['items'] as $index => $item) {
                        $degrees = $total > 0 ? ($item['value'] / $total) * 360 : 0;
                        $segments[] = $colors[$index % count($colors)] . ' ' . $start . 'deg ' . ($start + $degrees) . 'deg';
                        $start += $degrees;
                    }
                    $pieStyle = $segments ? implode(', ', $segments) : '#d8dee8 0deg 360deg';
                @endphp
                <div class="panel panel-pad dashboard-chart">
                    <div class="dashboard-section-head">
                        <div>
                            <span class="dashboard-section-tag">Insights</span>
                            <h2 class="h6 mb-0">{{ $chart['title'] }}</h2>
                        </div>
                    </div>
                    @if($chart['type'] === 'pie')
                        <div class="chart-pie-wrap">
                            <div class="chart-pie" style="background: conic-gradient({{ $pieStyle }});"></div>
                            <div class="chart-legend">
                                @forelse($chart['items'] as $index => $item)
                                    <div><span style="background: {{ $colors[$index % count($colors)] }}"></span>{{ $item['label'] }}: <strong>{{ $item['value'] }}</strong></div>
                                @empty
                                    <div class="text-muted">No figures yet.</div>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <div class="chart-bars">
                            @forelse($chart['items'] as $item)
                                @php($width = $total > 0 ? max(8, ($item['value'] / $total) * 100) : 0)
                                <div class="chart-bar-row">
                                    <div class="chart-bar-label">{{ $item['label'] }}</div>
                                    <div class="chart-bar-track"><span style="width: {{ $width }}%"></span></div>
                                    <strong>{{ $item['value'] }}</strong>
                                </div>
                            @empty
                                <div class="text-muted">No figures yet.</div>
                            @endforelse
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="panel">
                <div class="panel-pad border-bottom dashboard-section-head">
                    <div>
                        <span class="dashboard-section-tag">Schedule</span>
                        <h2 class="h5 mb-0">Upcoming appointments</h2>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingAppointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_date->format('M d, Y') }} {{ substr($appointment->start_time, 0, 5) }}</td>
                                    <td>{{ $appointment->patient->full_name }}</td>
                                    <td>{{ $appointment->doctor->display_name }}</td>
                                    <td><x-status-pill :status="$appointment->status" /></td>
                                    <td><a class="btn btn-sm btn-outline-secondary" href="{{ workspace_route('appointments.show', $appointment) }}">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center py-4">No upcoming appointments found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel">
                <div class="panel-pad border-bottom dashboard-section-head">
                    <div>
                        <span class="dashboard-section-tag">{{ $canSeePaymentSummary ? 'Finance' : 'Role focus' }}</span>
                        <h2 class="h5 mb-0">{{ $canSeePaymentSummary ? 'Recent payments' : 'Assigned duties' }}</h2>
                    </div>
                </div>

                @if($canSeePaymentSummary)
                    <div class="list-group list-group-flush">
                        @forelse($recentPayments as $payment)
                            <a class="list-group-item list-group-item-action" href="{{ workspace_route('payments.show', $payment) }}">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ $payment->invoice_number }}</strong>
                                    <span>{{ number_format($payment->amount) }}</span>
                                </div>
                                <div class="small text-muted">{{ $payment->patient->full_name }} - {{ ucfirst($payment->status) }}</div>
                            </a>
                        @empty
                            <div class="list-group-item text-muted">No payment activity yet.</div>
                        @endforelse
                    </div>
                @else
                    <div class="panel-pad">
                        <ul class="mb-0 ps-3">
                            @foreach($roleDuties as $duty)
                                <li class="mb-2">{{ $duty }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(auth()->user()->hasRole('admin'))
        <div class="row g-4 mt-1">
            <div class="col-lg-6">
                <div class="panel">
                    <div class="panel-pad border-bottom dashboard-section-head">
                        <div>
                            <span class="dashboard-section-tag">Capacity</span>
                            <h2 class="h5 mb-0">Doctor workload today</h2>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>Doctor</th><th>Today</th><th>Upcoming</th></tr></thead>
                            <tbody>
                                @forelse($doctorWorkloads as $workloadDoctor)
                                    <tr>
                                        <td>{{ $workloadDoctor->display_name }}</td>
                                        <td>{{ $workloadDoctor->appointments_today_count }}</td>
                                        <td>{{ $workloadDoctor->upcoming_appointments_count }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-muted text-center py-4">No doctor workload data yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="panel">
                    <div class="panel-pad border-bottom dashboard-section-head">
                        <div>
                            <span class="dashboard-section-tag">Trend</span>
                            <h2 class="h5 mb-0">Patient attendance trend</h2>
                        </div>
                    </div>
                    <div class="panel-pad">
                        @foreach($attendanceTrend as $trend)
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="small text-muted" style="width: 58px;">{{ $trend['label'] }}</div>
                                <div class="progress flex-grow-1" style="height: 10px;">
                                    <div class="progress-bar" style="width: {{ min(100, $trend['count'] * 18) }}%"></div>
                                </div>
                                <strong style="width: 32px;">{{ $trend['count'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($canSeePaymentSummary && !auth()->user()->hasRole('patient'))
        <div class="panel panel-pad mt-4">
            <h2 class="h5">Assigned duties</h2>
            <ul class="mb-0 ps-3">
                @foreach($roleDuties as $duty)
                    <li class="mb-2">{{ $duty }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
