<?php

namespace App;

enum TransactionStatus:string
{
    //
    case PENDING='pending';
    case SUCCESSFUL='successful';
    case FAILED='failed';
}
