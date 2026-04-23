@extends('layouts.app')

@section('title', 'Live Doctor Slot Checks | CityCare')

@section('content')
    @include('public.features._detail', [
        'eyebrow' => 'Appointment flow',
        'title' => 'Live doctor slot checks before a booking is confirmed',
        'lead' => 'CityCare checks available consultation days and time windows before a receptionist or patient confirms an appointment, helping the clinic avoid overlap, delays, and double-booking.',
        'primaryLabel' => 'Request an appointment',
        'primaryRoute' => route('login'),
        'image' => 'images/reception.jpg',
        'imageAlt' => 'Reception team coordinating doctor appointments',
        'imageTitle' => 'Booking that respects doctor availability',
        'imageCopy' => 'Appointments are matched against doctor schedules so patients can request care from real open slots instead of guessed time blocks.',
        'supportCopy' => 'The booking flow is designed to protect doctor time, reduce waiting-room congestion, and give reception staff a clearer way to place patients into realistic schedules.',
        'points' => [
            [
                'title' => 'Overlap prevention',
                'copy' => 'The system blocks appointments that would collide with a doctor’s already assigned consultation times.',
            ],
            [
                'title' => 'Live available slots',
                'copy' => 'Patients and reception staff can see available doctor days and time ranges before submitting a request.',
            ],
            [
                'title' => 'Approval-based patient booking',
                'copy' => 'Patients can request appointments, and the clinic approves them on dates and times that are actually available.',
            ],
            [
                'title' => 'Check-in readiness',
                'copy' => 'Patients can access appointment check-in 30 minutes before the scheduled start time for smoother arrivals.',
            ],
        ],
        'details' => [
            [
                'eyebrow' => 'Reception',
                'title' => 'Cleaner front-desk scheduling',
                'copy' => 'Reception teams can confirm bookings with clearer visibility into doctor workload and open hours.',
            ],
            [
                'eyebrow' => 'Doctors',
                'title' => 'Protected consultation time',
                'copy' => 'Doctors work from schedules that are less likely to be overcrowded by overlapping bookings.',
            ],
            [
                'eyebrow' => 'Patients',
                'title' => 'More reliable appointment times',
                'copy' => 'Patients request care through a flow that reflects actual availability rather than uncertain manual booking.',
            ],
        ],
        'ctaTitle' => 'Book from schedules that reflect real doctor availability.',
        'ctaCopy' => 'CityCare helps the clinic confirm appointments with fewer clashes and better timing for both staff and patients.',
    ])
@endsection
