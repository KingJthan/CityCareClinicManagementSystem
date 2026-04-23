@extends('layouts.app')

@section('title', 'Role-Based Access | CityCare')

@section('content')
    @include('public.features._detail', [
        'eyebrow' => 'System control',
        'title' => 'Role-based access that follows clinic duties',
        'lead' => 'CityCare gives each role the modules, dashboards, and records that match daily clinic responsibility, helping the team work faster without exposing the wrong information to the wrong users.',
        'primaryLabel' => 'Open staff portal',
        'primaryRoute' => route('staff.login'),
        'image' => 'images/doctor-team.jpg',
        'imageAlt' => 'CityCare care team using coordinated clinic systems',
        'imageTitle' => 'Access aligned to responsibility',
        'imageCopy' => 'Administrators, reception staff, doctors, cashiers, pharmacy, radiology, nurses, and patients each see the parts of CityCare that support their work.',
        'supportCopy' => 'Instead of one crowded interface for everyone, the system narrows menus, dashboards, and actions by role so clinic teams can stay focused and patient records stay protected.',
        'points' => [
            [
                'title' => 'Controlled visibility',
                'copy' => 'Users only see the menu items, dashboards, and pages that match their assigned role.',
            ],
            [
                'title' => 'Safer billing boundaries',
                'copy' => 'Revenue and payment recording stay limited to administrator and cashier responsibilities.',
            ],
            [
                'title' => 'Clinical focus',
                'copy' => 'Doctors, nurses, RN, and PCT staff can work from patient context needed for treatment and care support.',
            ],
            [
                'title' => 'Patient privacy',
                'copy' => 'Patients sign in to a separate experience where they only view their own approved information.',
            ],
        ],
        'details' => [
            [
                'eyebrow' => 'Administrator',
                'title' => 'Oversight without clutter',
                'copy' => 'Administrators manage departments, staff records, schedules, and reports from one central workspace.',
            ],
            [
                'eyebrow' => 'Operations',
                'title' => 'Staff tools by assignment',
                'copy' => 'Reception, pharmacy, radiology, and cashier roles each get a focused toolset for the work they handle every day.',
            ],
            [
                'eyebrow' => 'Patient portal',
                'title' => 'Separate from staff access',
                'copy' => 'Patients use their own login flow and do not see internal staff tools or operations screens.',
            ],
        ],
        'ctaTitle' => 'Open the right workspace for the right person.',
        'ctaCopy' => 'CityCare keeps clinical operations organized by matching access to responsibility across the whole clinic.',
    ])
@endsection
