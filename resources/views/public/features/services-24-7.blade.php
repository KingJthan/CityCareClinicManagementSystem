@extends('layouts.app')

@section('title', '24/7 Services | CityCare')

@section('content')
    @include('public.features._detail', [
        'eyebrow' => 'Always available',
        'title' => '24/7 services that stay reachable beyond clinic counters',
        'lead' => 'CityCare keeps public access open for care inquiries, appointment requests, and urgent support pathways at any time, giving patients a dependable digital front door even outside normal desk activity.',
        'primaryLabel' => 'Book or inquire',
        'primaryRoute' => route('contact'),
        'image' => 'images/patient-care.jpg',
        'imageAlt' => 'Patient support and ongoing CityCare services',
        'imageTitle' => 'A digital front desk that stays open',
        'imageCopy' => 'Patients can reach the clinic online for booking requests, support needs, and service guidance without waiting for a physical counter to open.',
        'supportCopy' => 'This 24/7 layer helps patients find the right next step sooner and gives CityCare a more reliable public-facing experience for services and support.',
        'points' => [
            [
                'title' => 'Any-time inquiry flow',
                'copy' => 'Patients can send questions and booking requests through the public portal whenever they need help.',
            ],
            [
                'title' => 'Service visibility',
                'copy' => 'Public pages clearly present services, directions, and booking pathways without forcing patients into staff-only screens.',
            ],
            [
                'title' => 'Better support continuity',
                'copy' => 'CityCare can guide patients toward appointments, diagnostics, pharmacy, or urgent support through one connected system.',
            ],
            [
                'title' => 'Professional public access',
                'copy' => 'The system presents the clinic as reachable, organized, and active throughout the day and night.',
            ],
        ],
        'details' => [
            [
                'eyebrow' => 'Homepage',
                'title' => 'Public pages that guide next steps',
                'copy' => 'Patients can move from the home page into services, contact, location, and booking routes without confusion.',
            ],
            [
                'eyebrow' => 'Contact',
                'title' => 'One form for booking or inquiry',
                'copy' => 'The contact page supports both appointment requests and general inquiries from the same public entry point.',
            ],
            [
                'eyebrow' => 'Care shop',
                'title' => 'Service browsing with checkout support',
                'copy' => 'Patients can browse selected clinic services and prepare payment-linked care requests through the public experience.',
            ],
        ],
        'ctaTitle' => 'Keep CityCare reachable even when patients are not standing at the desk.',
        'ctaCopy' => 'The public portal helps patients start the right care journey at any hour.',
    ])
@endsection
