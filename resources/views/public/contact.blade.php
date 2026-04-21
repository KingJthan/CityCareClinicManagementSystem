@extends('layouts.app')

@section('title', 'Contact CityCare')

@section('content')
    <section class="public-band">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row g-4">
                <div class="col-lg-5">
                    <p class="eyebrow">Contact us</p>
                    <h1 class="section-title">Talk to CityCare</h1>
                    <p class="section-copy">Reach the clinic desk for appointment booking, 24/7 care inquiries, ambulance support, or patient account help.</p>
                    <div class="panel panel-pad mt-4">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">+256 700 555 100</dd>
                            <dt class="col-sm-4">Email</dt><dd class="col-sm-8">support@citycare.test</dd>
                            <dt class="col-sm-4">Hours</dt><dd class="col-sm-8">24/7 patient support and emergency coordination</dd>
                            <dt class="col-sm-4">Location</dt><dd class="col-sm-8">Plot 24 Yusuf Lule Road, Kampala</dd>
                        </dl>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="panel panel-pad">
                        <h2 class="h4 mb-3">Book an appointment or send an inquiry</h2>
                        <form method="POST" action="{{ route('contact.store') }}" class="row g-3">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label" for="name">Full name</label>
                                <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Phone number</label>
                                <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="request_type">Request type</label>
                                <select class="form-select @error('request_type') is-invalid @enderror" id="request_type" name="request_type" required>
                                    <option value="">Choose request</option>
                                    <option value="appointment" @selected(old('request_type') === 'appointment')>Book appointment</option>
                                    <option value="inquiry" @selected(old('request_type') === 'inquiry')>General inquiry</option>
                                </select>
                                @error('request_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="preferred_date">Preferred appointment date</label>
                                <input class="form-control @error('preferred_date') is-invalid @enderror" id="preferred_date" name="preferred_date" type="date" value="{{ old('preferred_date') }}">
                                @error('preferred_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="message">Message</label>
                                <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
                                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <button class="btn btn-dark btn-lg" type="submit">Send request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
