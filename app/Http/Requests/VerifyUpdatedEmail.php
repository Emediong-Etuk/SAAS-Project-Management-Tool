<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;

class VerifyUpdatedEmail extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function getCachedToken(): int
    {
        $cached = Cache::get('EMAIL_VERIFICATION_TOKEN_'.$this->user()->email);

        return $cached[0];

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'token' => ['required', 'integer', function ($attr, $val, $fail) {
                if (intval($val) !== $this->getCachedToken()) {
                    return $fail('Invalid Token');
                }
            }],
        ];
    }
}
