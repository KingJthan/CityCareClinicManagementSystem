<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value) && !is_numeric($value)) {
            $fail('The :attribute must be a valid phone number.');

            return;
        }

        $value = (string) $value;

        if (!preg_match('/^\+?[0-9\-\s()]+$/', $value)) {
            $fail('The :attribute may only contain numbers, spaces, brackets, hyphens, or a leading plus sign.');

            return;
        }

        $digits = preg_replace('/\D+/', '', $value);

        if (strlen($digits) < 10) {
            $fail('The :attribute must contain at least 10 digits.');
        }
    }
}
