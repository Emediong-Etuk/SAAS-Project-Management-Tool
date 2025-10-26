<?php

namespace App\Traits;

use Ramsey\Uuid\Uuid;
use App\Enum\TransactionCategory;

trait GenerateReference
{
    //
    public function generateReference(TransactionCategory $transactionCategory): string
    { {
            return $transactionCategory->value . '_' . Uuid::uuid4()->toString();
        }
    }
}
