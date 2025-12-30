<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Notification extends Model
{
    //
    use HasUuids, HasApiTokens, HasFactory;

    protected $fillable=[
        'message',
        'mark_read',
        'user_id',
        'alerts'
    ];


    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
