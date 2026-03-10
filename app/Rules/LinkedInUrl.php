<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LinkedInUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
        if (empty($value)) {
            return;
        }

        $host = parse_url($value, PHP_URL_HOST);
        $match = preg_match('/(^|\.)linkedin\.com$/i', $host);

        if (!$host || !$match) {
            $fail('must be a valid LinkedIn profile URL.');
        }
    }
}
