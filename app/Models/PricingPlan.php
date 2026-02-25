<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class PricingPlan extends Model
{
    //

    use HasApiTokens, HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'price',
        'role',
        'can_create_tenant',
        'can_edit_tenant',
        'can_delete_tenant',
        'can_create_tasks',
        'can_create_projects',
        'can_edit_tasks',
        'can_edit_projects',
        'can_delete_tasks',
        'can_delete_projects',
        'can_invite_members',
    ];

    protected $casts = [
        'role' => 'array',
        'can_create_tenant' => 'boolean',
        'can_edit_tenant' => 'boolean',
        'can_delete_tenant' => 'boolean',
        'can_create_tasks' => 'boolean',
        'can_create_projects' => 'boolean',
        'can_edit_tasks' => 'boolean',
        'can_edit_projects' => 'boolean',
        'can_delete_tasks' => 'boolean',
        'can_delete_projects' => 'boolean',
        'can_invite_members' => 'boolean',
    ];
}
