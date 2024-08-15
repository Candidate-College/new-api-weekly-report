<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScoreDivisionKPIResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $score = $this->end_of_month_realization ? ($this->end_of_month_realization / $this->target) * 100 : null;
        $finalScore = $score ? ($score * $this->weight) / 100 : null;

        return [
            'division_id' => $this->division_id,
            'year' => $this->year,
            'month' => $this->month,
            'task_name' => $this->task_name,
            'weight' => $this->weight,
            'target' => $this->target,
            'end_of_month_realization' => $this->end_of_month_realization,
            'score' => $score ? round($score, 2) : null,
            'final_score' => $finalScore ? round($finalScore, 2) : null,
        ];
    }
}
