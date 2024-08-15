<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DivisionKPIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'division_id' => $this->division_id,
            'year' => $this->year,
            'month' => $this->month,
            'task_name' => $this->task_name,
            'weight' => $this->weight,
            'target' => $this->target,
        ];
    }
}
