@extends('layouts.app')

@php
    $editing = $appointment->exists;
    $selectedStart = old('start_time', $appointment->start_time ? substr($appointment->start_time, 0, 5) : '');
    $isPatientPortal = auth()->user()->hasRole('patient');
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
@endphp
@section('title', ($editing ? 'Edit' : 'Book') . ' Appointment | CityCare')

@section('content')
    <x-page-header :title="$editing ? 'Edit Appointment' : ($isPatientPortal ? 'Request Appointment' : 'Book Appointment')" subtitle="Available slots load dynamically from the selected doctor and date." />

    <div class="panel panel-pad mb-3">
        <h2 class="h6">Available doctors and schedules</h2>
        <div class="doctor-schedule-grid">
            @foreach($doctors as $doctor)
                @php
                    $workingDays = collect($doctor->working_days ?: [1, 2, 3, 4, 5])->map(fn ($day) => $days[$day])->implode(', ');
                @endphp
                <div>
                    <strong>{{ $doctor->display_name }}</strong>
                    <div class="small text-muted">{{ $doctor->department->name }} - {{ $workingDays }}</div>
                    <div class="small">{{ substr($doctor->shift_starts_at, 0, 5) }} to {{ substr($doctor->shift_ends_at, 0, 5) }} every {{ $doctor->slot_minutes }} minutes</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="panel panel-pad">
        <form method="POST" action="{{ $editing ? workspace_route('appointments.update', $appointment) : workspace_route('appointments.store') }}" class="row g-3">
            @csrf
            @if($editing)
                @method('PUT')
            @endif

            @if($isPatientPortal)
                <input type="hidden" name="patient_id" value="{{ $patients->first()?->id }}">
            @else
                <div class="col-md-4">
                    <label class="form-label" for="patient_id">Patient</label>
                    <select class="form-select" id="patient_id" name="patient_id" required>
                        <option value="">Select patient</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" @selected((string) old('patient_id', request('patient_id', $appointment->patient_id)) === (string) $patient->id)>{{ $patient->patient_number }} - {{ $patient->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-4">
                <label class="form-label" for="doctor_id">Doctor</label>
                <select class="form-select" id="doctor_id" name="doctor_id" required>
                    <option value="">Select doctor</option>
                    @foreach($doctors as $doctor)
                        @php($workingDays = collect($doctor->working_days ?: [1, 2, 3, 4, 5])->map(fn ($day) => $days[$day])->implode(', '))
                        <option value="{{ $doctor->id }}" data-schedule="{{ $workingDays }} | {{ substr($doctor->shift_starts_at, 0, 5) }} to {{ substr($doctor->shift_ends_at, 0, 5) }}" @selected((string) old('doctor_id', $appointment->doctor_id) === (string) $doctor->id)>{{ $doctor->display_name }} - {{ $doctor->department->name }}</option>
                    @endforeach
                </select>
                <div class="form-text" id="doctorSchedule">Select a doctor to view working days and time.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="appointment_date">Date</label>
                <input class="form-control" id="appointment_date" name="appointment_date" type="date" min="{{ today()->toDateString() }}" value="{{ old('appointment_date', $appointment->appointment_date?->format('Y-m-d')) }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="start_time">Available slot</label>
                <select class="form-select" id="start_time" name="start_time" required data-selected="{{ $selectedStart }}">
                    @if($selectedStart)
                        <option value="{{ $selectedStart }}">{{ $selectedStart }}</option>
                    @else
                        <option value="">Select doctor and date first</option>
                    @endif
                    @foreach($slots as $slot)
                        <option value="{{ $slot['start'] }}" @selected($selectedStart === $slot['start'])>{{ $slot['label'] }}</option>
                    @endforeach
                </select>
                <div class="form-text" id="slotHelp">Slots update automatically.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="visit_type">Visit type</label>
                <input class="form-control" id="visit_type" name="visit_type" value="{{ old('visit_type', $appointment->visit_type ?? 'Consultation') }}" required>
            </div>
            @if($isPatientPortal)
                <input type="hidden" name="status" value="pending">
            @else
                <div class="col-md-4">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        @foreach(['pending', 'scheduled', 'available', 'checked_in', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $appointment->status ?? 'scheduled') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="{{ $isPatientPortal ? 'col-12' : 'col-md-6' }}">
                <label class="form-label" for="reason">Reason</label>
                <textarea class="form-control" id="reason" name="reason" rows="4">{{ old('reason', $appointment->reason) }}</textarea>
            </div>
            @unless($isPatientPortal)
                <div class="col-md-6">
                    <label class="form-label" for="internal_notes">Internal notes</label>
                    <textarea class="form-control" id="internal_notes" name="internal_notes" rows="4">{{ old('internal_notes', $appointment->internal_notes) }}</textarea>
                </div>
            @endunless

            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">{{ $editing ? 'Save changes' : ($isPatientPortal ? 'Request approval' : 'Book appointment') }}</button>
                <a class="btn btn-outline-secondary" href="{{ workspace_route('appointments.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const doctor = document.getElementById('doctor_id');
            const date = document.getElementById('appointment_date');
            const slot = document.getElementById('start_time');
            const help = document.getElementById('slotHelp');
            const doctorSchedule = document.getElementById('doctorSchedule');
            const baseUrl = @json(url('/api/doctors'));
            const exclude = @json($appointment->id);

            function updateDoctorSchedule() {
                const selected = doctor.options[doctor.selectedIndex];
                doctorSchedule.textContent = selected?.dataset?.schedule || 'Select a doctor to view working days and time.';
            }

            async function loadSlots() {
                if (!doctor.value || !date.value) {
                    updateDoctorSchedule();
                    return;
                }

                updateDoctorSchedule();
                slot.innerHTML = '<option value="">Loading slots...</option>';
                help.textContent = 'Checking doctor availability.';

                const params = new URLSearchParams({ date: date.value });
                if (exclude) {
                    params.append('exclude', exclude);
                }

                const response = await fetch(`${baseUrl}/${doctor.value}/available-slots?${params.toString()}`);
                const payload = await response.json();
                const selected = slot.dataset.selected;
                slot.innerHTML = '<option value="">Select a slot</option>';

                payload.slots.forEach(function (item) {
                    const option = document.createElement('option');
                    option.value = item.start;
                    option.textContent = item.label;
                    option.selected = selected === item.start;
                    slot.appendChild(option);
                });

                help.textContent = payload.slots.length ? `${payload.slots.length} slots available.` : 'No slots available for this doctor on the selected date.';
            }

            doctor.addEventListener('change', loadSlots);
            date.addEventListener('change', loadSlots);
            updateDoctorSchedule();

            if (doctor.value && date.value) {
                loadSlots();
            }
        });
    </script>
@endpush
