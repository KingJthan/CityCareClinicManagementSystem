<?php

namespace App\Http\Controllers;

use App\Models\PublicInquiry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicInquiryController extends Controller
{
    public function store(Request $request)
    {
        PublicInquiry::create($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'regex:/^[0-9+ ]{7,20}$/'],
            'request_type' => ['required', Rule::in(['appointment', 'inquiry'])],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'message' => ['required', 'string', 'max:1200'],
        ]) + [
            'status' => 'new',
        ]);

        return redirect()
            ->route('contact')
            ->with('success', 'Thank you. CityCare has received your request and will contact you shortly.');
    }
}
