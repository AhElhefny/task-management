<?php

namespace App\Http\Resources;

use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    use HelperTrait;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->getEnumInfo($this->status),
            'due_date' => $this->due_date->format('Y-m-d H:i'),
            'priority' => $this->getEnumInfo($this->priority),
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
