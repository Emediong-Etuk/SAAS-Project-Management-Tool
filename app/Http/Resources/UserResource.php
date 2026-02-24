<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'username' => $this->username,
            'email' => $this->email,
            'tenant' => new TenantResource($this->tenant),
            'profile_picture' => $this->profile_picture,
            'cover_picture' => $this->cover_picture,
            'occupation' => $this->occupation,
            'skills' => $this->skills,
            'projects_worked_on' => $this->projects_worked_on,
            'linkedin_profile' => $this->linkedin_profile,
            'customer_card_email'=>$this->customer_card_email,
            'password' => $this->password,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
