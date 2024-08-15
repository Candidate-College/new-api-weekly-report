<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ScoreDivisionKPICollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'total_final_score' => round($this->collection->sum(function ($kpi) {
                $score = $kpi->end_of_month_realization ? ($kpi->end_of_month_realization / $kpi->target) * 100 : 0;
                return ($score * $kpi->weight) / 100;
            }), 2),
        ];
    }
}
