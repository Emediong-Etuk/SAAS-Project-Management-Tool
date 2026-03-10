<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Password;

class VerifyEmailRequest extends FormRequest
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
        return intval(Cache::get("EMAIL_VERIFICATION_TOKEN_$this->email"));
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
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'string', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'token' => ['required', 'integer', function ($attr, $val, $fail) {
                if (intval($val) !== $this->getCachedToken()) {
                    return $fail('Invalid Token');
                }
            }],

        ];
    }
}
