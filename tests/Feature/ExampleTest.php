<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_page_returns_a_successful_response(): void
    {
        $this->get(route('home', [], false))
            ->assertOk()
            ->assertSee('CityCare Medical Centre')
            ->assertSee('Staff access')
            ->assertSee('Patient access')
            ->assertSee('24/7 services')
            ->assertDontSee('Patient visit history')
            ->assertDontSee('Payment tracking');
    }

    public function test_login_page_returns_a_successful_response(): void
    {
        $this->get(route('login', [], false))
            ->assertOk()
            ->assertSee('Patient portal login')
            ->assertSee('citycare456');
    }

    public function test_public_information_pages_return_successful_responses(): void
    {
        $this->get(route('about', [], false))->assertOk()->assertSee('About CityCare');
        $this->get(route('services', [], false))->assertOk()->assertSee('24/7 support and ambulance service');
        $this->get(route('location', [], false))->assertOk()->assertSee('Plot 24 Yusuf Lule Road, Kampala')->assertSee('Get directions');
        $this->get(route('contact', [], false))->assertOk()->assertSee('Book an appointment or send an inquiry');
    }

    public function test_homepage_feature_tags_open_dedicated_public_pages(): void
    {
        $this->get(route('features.role-access', [], false))
            ->assertOk()
            ->assertSee('Role-based access that follows clinic duties');

        $this->get(route('features.doctor-slots', [], false))
            ->assertOk()
            ->assertSee('Live doctor slot checks before a booking is confirmed');

        $this->get(route('features.services-24-7', [], false))
            ->assertOk()
            ->assertSee('24/7 services that stay reachable beyond clinic counters');

        $this->get(route('features.ambulance-support', [], false))
            ->assertOk()
            ->assertSee('Ambulance support linked to faster coordination');
    }

    public function test_staff_login_page_is_separate_from_patient_login(): void
    {
        $this->get(route('staff.login', [], false))
            ->assertOk()
            ->assertSee('Staff portal login')
            ->assertDontSee('Patient portal login')
            ->assertDontSee('New patient?');
    }

    public function test_public_contact_form_stores_booking_or_inquiry_request(): void
    {
        $this->post(route('contact.store', [], false), [
            'name' => 'Amina Visitor',
            'email' => 'amina@example.test',
            'phone' => '+256700555123',
            'request_type' => 'appointment',
            'preferred_date' => now()->addDay()->toDateString(),
            'message' => 'I would like to book a family medicine appointment.',
        ])
            ->assertRedirect(route('contact', [], false));

        $this->assertDatabaseHas('public_inquiries', [
            'name' => 'Amina Visitor',
            'email' => 'amina@example.test',
            'phone' => '+256700555123',
            'request_type' => 'appointment',
            'status' => 'new',
        ]);
    }
}
