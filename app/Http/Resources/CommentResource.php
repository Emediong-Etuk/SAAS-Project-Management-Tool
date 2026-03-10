<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array{

        return [
            'id'=>$this->id,
            'task'=>new TaskResource($this->task),
            'user'=>new UserResource($this->user),
            'comment'=>$this->comment,
            'project'=>new ProjectResource($this->project),
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
        ];
    }
}
