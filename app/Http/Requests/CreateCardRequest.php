<?php

namespace App\Http\Requests;

use App\Enum\CardsEnum;
use App\Traits\CheckCardType;
use Illuminate\Foundation\Http\FormRequest;

class CreateCardRequest extends FormRequest
{
    use CheckCardType;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function validateCard(string $cardNumber):string
    {
        $cardType=$this->checkCardType($cardNumber);
        return $cardType;
    }

    /*
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'card_number'=>['required','string',],
            'expiry_month'=>['required','string'],
            'expiry_year'=>['required','string'],
            'cvv'=>['required','string']

        ];
        //  function ($attr,$val,$fail){
        //         if(!in_array($this->validateCard($val), CardsEnum::values(),true)){
        //             return $fail('Invalid Card Number, we only accept MasterCard, Verve or Visa');
        //         }
        //     }
    }
}
