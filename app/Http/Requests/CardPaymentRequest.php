<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardPaymentRequest extends FormRequest
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
            'amount' => 'required|numeric|min:100',


            'card_number' => 'required|string',
            'cvv' => 'required|string',
            'expiry_month' => 'required|string',
            'expiry_year' => 'required|string',

            'full_name' => 'required|string',
            'email' => 'required|email',
            'phone_number' => 'required|string',
        ];
    }
}
