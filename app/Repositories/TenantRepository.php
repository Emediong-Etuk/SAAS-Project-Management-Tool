<?php

namespace App\Repositories;

use App\Models\Tenant;

class TenantRepository
{
    public function create(array $data):Tenant
    {
        return Tenant::create($data);
    }
}
