<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaction extends Model
{
    //
    use HasUuids;


    protected $fillable=[
        'user_id',
        'reference',
        'status',
        'category',
        'amount',
        'transaction_id',
    ];

    
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
