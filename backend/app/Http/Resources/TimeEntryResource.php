<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
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
            'company' => [
                'id' => $this->company_id,
                'name' => $this->company?->name,
            ],
            'employee' => [
                'id' => $this->employee_id,
                'name' => $this->employee?->name,
            ],
            'project' => [
                'id' => $this->project_id,
                'name' => $this->project?->name,
            ],
            'task' => [
                'id' => $this->task_id,
                'name' => $this->task?->name,
            ],
            'date' => $this->date->format('Y-m-d'),
            'hours' => $this->hours,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
