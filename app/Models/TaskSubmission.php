<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSubmission extends Model
{
    //
    protected $fillable=[
        'submission_files',
        'comment',
        'task_id',
        'user_id'
    ];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task():BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
