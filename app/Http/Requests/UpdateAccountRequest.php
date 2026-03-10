<?php

namespace App\Http\Requests;

use App\Rules\LinkedInUrl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'name' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255', 'exists:occupations,name'],
            'skills' => ['nullable', 'string', 'max:255', 'exists:skills,name'],
            'profile_picture' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],
            'cover_picture' => ['nullable', 'image', 'mimes:png,jpeg,jpg'],
            'projects_worked_on' => ['nullable', 'string', 'max:255'],
            'linkedin_profile' => ['nullable', 'url', 'max:255', new LinkedInUrl()],
        ];
    }
}
