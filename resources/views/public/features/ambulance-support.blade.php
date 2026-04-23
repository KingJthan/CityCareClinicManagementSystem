@extends('layouts.app')

@section('title', 'Ambulance Support | CityCare')

@section('content')
    @include('public.features._detail', [
        'eyebrow' => 'Emergency coordination',
        'title' => 'Ambulance support linked to faster coordination',
        'lead' => 'CityCare highlights ambulance support as part of the clinic’s public-facing service experience, helping patients and families identify emergency transport and urgent transfer coordination faster.',
        'primaryLabel' => 'Get directions',
        'primaryRoute' => route('location'),
        'image' => 'images/ambulence-team.jpg',
        'imageAlt' => 'CityCare ambulance and emergency response support',
        'imageTitle' => 'Emergency movement with clearer support paths',
        'imageCopy' => 'Ambulance coordination is presented as a visible CityCare service so urgent care requests can move toward action more quickly.',
        'supportCopy' => 'The ambulance pathway connects emergency support, clinic contact, and location guidance so patients can reach care with less uncertainty in urgent moments.',
        'points' => [
            [
                'title' => 'Visible emergency support',
                'copy' => 'Ambulance response is surfaced clearly on public pages so patients know urgent transport help exists.',
            ],
            [
                'title' => 'Contact and inquiry handoff',
                'copy' => 'Families can use the public contact route to request urgent guidance or referrals tied to emergency movement.',
            ],
            [
                'title' => 'Directions to CityCare',
                'copy' => 'The location page provides the clinic address and a directions link for quicker arrival planning.',
            ],
            [
                'title' => 'Better operational readiness',
                'copy' => 'The wider system helps staff coordinate patient arrival, service routing, and follow-up inside the clinic workspace.',
            ],
        ],
        'details' => [
            [
                'eyebrow' => 'Location',
                'title' => 'Find CityCare faster',
                'copy' => 'Patients can move from ambulance support information to exact clinic directions on Plot 24 Yusuf Lule Road, Kampala.',
            ],
            [
                'eyebrow' => 'Services',
                'title' => 'Urgent support alongside routine care',
                'copy' => 'Emergency movement is presented alongside consultations, nursing, pharmacy, and diagnostics as part of one coordinated service offering.',
            ],
            [
                'eyebrow' => 'Operations',
                'title' => 'Prepared clinic response',
                'copy' => 'Once a patient reaches the clinic, internal staff roles can continue from a shared, organized workflow.',
            ],
        ],
        'ctaTitle' => 'Keep emergency support easy to find when time matters.',
        'ctaCopy' => 'CityCare presents ambulance coordination as a visible part of a reliable healthcare access experience.',
    ])
@endsection
