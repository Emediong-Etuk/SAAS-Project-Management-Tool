<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TaskUser extends Pivot
{
    //

    use HasUuids;

    protected $table='task_user';

}
