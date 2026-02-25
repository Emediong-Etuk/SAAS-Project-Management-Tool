<?php

namespace App\Traits;

use App\Enum\TransactionCategory;
use Ramsey\Uuid\Uuid;

trait GenerateReference
{
    //
    public function generateReference(TransactionCategory $transactionCategory): string
    {
        return $transactionCategory->value.'_'.Uuid::uuid4()->toString();

    }
}
