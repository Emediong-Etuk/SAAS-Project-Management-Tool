<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;

class Task extends Model
{
    //
    use HasFactory, HasUuids, Searchable;

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'tenant_id',
        'deadline',
        'completed',
    ];

    public static function booted(): void
    {
        static::addGlobalScope('tenant_id', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where('tenant_id', Auth::user()->tenant_id);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(TaskUser::class)->withTimestamps();
    }
}
