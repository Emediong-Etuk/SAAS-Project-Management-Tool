<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingPlanResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'role' => $this->role,
            'can_create_tenant' => $this->can_create_tenant,
            'can_edit_tenant' => $this->can_edit_tenant,
            'can_delete_tenant' => $this->can_delete_tenant,
            'can_create_tasks' => $this->can_create_tasks,
            'can_create_projects' => $this->can_create_projects,
            'can_edit_tasks' => $this->can_edit_tasks,
            'can_edit_projects' => $this->can_edit_projects,
            'can_delete_tasks' => $this->can_delete_tasks,
            'can_delete_projects' => $this->can_delete_projects,
        ];
    }
}
