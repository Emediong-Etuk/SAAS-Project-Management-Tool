<?php

namespace App\Traits;

trait CheckCardType
{
    //
    public function checkCardType(string $cardNumber): string
    {
        $cleanCardNumber = preg_replace('/\D/', '', $cardNumber);

        $cardPatterns = [
            [
                'type' => 'Visa',
                'prefixes' => ['4'],
                'lengths' => [13, 16, 19]
            ],

            [
                'type' => 'MasterCard',
                'prefixes' => ['51', '52', '53', '54', '55'],
                'lengths' => [16]
            ],

            [
                'type' => 'Verve',
                'prefixes' => ['5060', '5061', '6500'],
                'lengths' => [16, 19]
            ]
        ];

        $cardLength = strlen($cleanCardNumber);

        if($cardLength===0){
            return "Invalid Card Number";
        }

        foreach($cardPatterns as $card){
            foreach($card['prefixes'] as $prefix){
                if(str_starts_with($cleanCardNumber,$prefix) && in_array($cardLength, ['length'],true)){
                    return $card['type'];
                }
            }
        }

        return 'This card type is not accepted, we only accept MasterCard ,Verse or Visa';
    }
}
