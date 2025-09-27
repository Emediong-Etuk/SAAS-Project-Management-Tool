<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateLoginCredentials implements ValidationRule, DataAwareRule
{
    protected array $data=[];

    public function setData(array $data):static
    {
        $this->data=$data;

        return $this;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
        $credentials=[
            'email'=>$this->data['email'] ?? null,
            'password'=>$this->data['password']??null
        ];

        if(!Auth::attempt($credentials)){
            $fail('The provided credentials are incorrect');
        }
    }
}
