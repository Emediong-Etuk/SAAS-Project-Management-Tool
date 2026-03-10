<?php

namespace App;

enum TransactionStatus: string
{
    //
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
