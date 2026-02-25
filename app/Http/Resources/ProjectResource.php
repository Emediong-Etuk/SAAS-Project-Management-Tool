<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'deadline' => $this->deadline,
            'tenant' => new TenantResource($this->tenant),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
