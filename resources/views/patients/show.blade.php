@extends('layouts.app')

@section('title', $patient->full_name . ' | CityCare')

@section('content')
    @php
        $canViewClinical = auth()->user()->hasRole(['admin', 'doctor', 'patient', 'rn', 'pct', 'nurse']);
        $canViewPayments = auth()->user()->hasRole(['admin', 'cashier', 'patient']);
        $canViewInsurance = auth()->user()->hasRole(['admin', 'patient']);
    @endphp

    <x-page-header :title="$patient->full_name" :subtitle="$patient->patient_number . ' patient profile'">
        <x-slot:actions>
            @if(auth()->user()->hasRole('admin'))
                <a class="btn btn-outline-secondary" href="{{ route('patients.edit', $patient) }}">Edit</a>
                <form method="POST" action="{{ route('patients.destroy', $patient) }}" data-confirm="Archive this patient record?">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger" type="submit">Archive</button>
                </form>
            @endif
            @if(auth()->user()->hasRole(['admin', 'receptionist']))
                <a class="btn btn-dark" href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}">Book appointment</a>
            @endif
            @if(auth()->user()->hasRole('patient'))
                <a class="btn btn-dark" href="{{ route('appointments.create') }}">Request appointment</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="panel panel-pad h-100">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Phone</dt><dd class="col-sm-7">{{ $patient->phone ?? 'Not set' }}</dd>
                    <dt class="col-sm-5">Email</dt><dd class="col-sm-7">{{ $patient->email ?? 'Not set' }}</dd>
                    <dt class="col-sm-5">Gender</dt><dd class="col-sm-7">{{ $patient->gender ?? 'Not set' }}</dd>
                    <dt class="col-sm-5">Birth date</dt><dd class="col-sm-7">{{ $patient->date_of_birth?->format('M d, Y') ?? 'Not set' }}</dd>
                    <dt class="col-sm-5">Status</dt><dd class="col-sm-7"><x-status-pill :status="$patient->status" /></dd>
                    <dt class="col-sm-5">Alerts</dt><dd class="col-sm-7">{{ $patient->allergies ?? 'None recorded' }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="panel panel-pad h-100">
                <h2 class="h5">Emergency and address</h2>
                <p class="text-muted mb-2">{{ $patient->address ?? 'No address recorded.' }}</p>
                <p class="mb-0"><strong>{{ $patient->emergency_contact_name ?? 'No emergency contact' }}</strong></p>
                <p class="text-muted mb-0">{{ $patient->emergency_contact_phone ?? 'No phone recorded' }}</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="panel h-100">
                <div class="panel-pad border-bottom"><h2 class="h5 mb-0">Appointments</h2></div>
                <div class="list-group list-group-flush">
                @forelse($appointments as $appointment)
                        <a class="list-group-item list-group-item-action" href="{{ route('appointments.show', $appointment) }}">
                            <strong>{{ $appointment->appointment_date->format('M d, Y') }} {{ substr($appointment->start_time, 0, 5) }}</strong>
                            <div class="small text-muted">Provider: {{ $appointment->doctor->display_name }} - {{ ucfirst($appointment->status) }}</div>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">No appointments recorded.</div>
                    @endforelse
                </div>
            </div>
        </div>
        @if($canViewClinical)
            <div class="col-xl-4">
                <div class="panel h-100">
                    <div class="panel-pad border-bottom"><h2 class="h5 mb-0">Visit history</h2></div>
                    <div class="list-group list-group-flush">
                        @forelse($consultations as $consultation)
                            <div class="list-group-item">
                                <strong>{{ $consultation->created_at->format('M d, Y') }}</strong>
                                <div>{{ $consultation->diagnosis }}</div>
                                <div class="small text-muted">{{ $consultation->doctor->display_name }} - {{ $consultation->treatment_plan }}</div>
                            </div>
                        @empty
                            <div class="list-group-item text-muted">No consultation notes yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        @if($canViewPayments)
            <div class="col-xl-4">
                <div class="panel h-100">
                    <div class="panel-pad border-bottom"><h2 class="h5 mb-0">Bills and payments</h2></div>
                    <div class="list-group list-group-flush">
                        @forelse($payments as $payment)
                            <a class="list-group-item list-group-item-action" href="{{ route('payments.show', $payment) }}">
                                <strong>{{ $payment->invoice_number }}</strong>
                                <div class="small text-muted">{{ number_format($payment->amount) }} - {{ ucfirst($payment->status) }}</div>
                            </a>
                        @empty
                            <div class="list-group-item text-muted">No payment records yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        @if($canViewInsurance)
            <div class="col-xl-4">
                <div class="panel h-100">
                    <div class="panel-pad border-bottom"><h2 class="h5 mb-0">Insurance</h2></div>
                    <div class="list-group list-group-flush">
                        @forelse($insurances as $insurance)
                            <div class="list-group-item">
                                <strong>{{ $insurance->provider_name }}</strong>
                                <div class="small text-muted">{{ $insurance->policy_number }} - {{ ucfirst($insurance->status) }}</div>
                                <div class="small">Valid until {{ $insurance->valid_until?->format('M d, Y') ?? 'Not set' }}</div>
                            </div>
                        @empty
                            <div class="list-group-item text-muted">No insurance record saved.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        @if($canViewClinical)
            <div class="col-xl-4">
                <div class="panel h-100">
                    <div class="panel-pad border-bottom"><h2 class="h5 mb-0">Blood work and lab results</h2></div>
                    <div class="list-group list-group-flush">
                        @forelse($labResults as $result)
                            <div class="list-group-item">
                                <strong>{{ $result->test_name }}</strong>
                                <div>{{ $result->result_value ?? 'Pending' }} {{ $result->unit }}</div>
                                <div class="small text-muted">{{ $result->reference_range ?? 'No reference range' }} - {{ $result->resulted_at?->format('M d, Y') ?? ucfirst($result->status) }}</div>
                            </div>
                        @empty
                            <div class="list-group-item text-muted">No blood work or lab reports recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="panel h-100">
                    <div class="panel-pad border-bottom"><h2 class="h5 mb-0">Vital signs</h2></div>
                    <div class="list-group list-group-flush">
                        @forelse($vitalSigns as $vital)
                            <div class="list-group-item">
                                <strong>{{ $vital->recorded_at?->format('M d, Y H:i') ?? 'Recent vitals' }}</strong>
                                <div class="small">BP {{ $vital->blood_pressure ?? 'N/A' }} - HR {{ $vital->heart_rate ?? 'N/A' }} - SpO2 {{ $vital->oxygen_saturation ?? 'N/A' }}%</div>
                                <div class="small text-muted">Temp {{ $vital->temperature_c ?? 'N/A' }} C - Weight {{ $vital->weight_kg ?? 'N/A' }} kg</div>
                            </div>
                        @empty
                            <div class="list-group-item text-muted">No vital signs recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="panel h-100">
                    <div class="panel-pad border-bottom"><h2 class="h5 mb-0">Family history</h2></div>
                    <div class="list-group list-group-flush">
                        @forelse($familyHistories as $history)
                            <div class="list-group-item">
                                <strong>{{ $history->condition }}</strong>
                                <div class="small text-muted">{{ $history->relationship ?? 'Family' }} - {{ $history->notes ?? 'No notes' }}</div>
                            </div>
                        @empty
                            <div class="list-group-item text-muted">No family history recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="panel h-100">
                    <div class="panel-pad border-bottom"><h2 class="h5 mb-0">Prescriptions and treatment</h2></div>
                    <div class="list-group list-group-flush">
                        @forelse($prescriptions as $prescription)
                            <div class="list-group-item">
                                <strong>{{ $prescription->drug->name }} {{ $prescription->drug->strength }}</strong>
                                <div>{{ $prescription->dosage }} {{ $prescription->frequency ? '- ' . $prescription->frequency : '' }}</div>
                                <div class="small text-muted">{{ $prescription->instructions ?? 'No instructions' }}</div>
                            </div>
                        @empty
                            <div class="list-group-item text-muted">No prescriptions recorded.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        @if(!$canViewClinical && !$canViewPayments)
            <div class="col-xl-4">
                <div class="panel h-100">
                    <div class="panel-pad">
                        <h2 class="h5">Role-limited patient view</h2>
                        <p class="text-muted mb-0">This account can use patient identity and appointment details only for assigned clinic duties.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
