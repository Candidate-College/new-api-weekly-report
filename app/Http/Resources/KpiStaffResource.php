<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KpiStaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $kpiData = [];
        $aspects = [];
        $grandTotal = 0;

        foreach ($this->scores as $score) {
            $aspect = $score['aspect'];
            
            if (!isset($aspects[$aspect])) {
                $aspects[$aspect] = [
                    'aspect' => $aspect,
                    'total_aspect' => 0,
                    'value_conversion' => '',
                    'type' => []
                ];
            }
            $aspects[$aspect]['type'][] = [
                'kpi' => $score['kpi'],
                'end_of_month_realization' => $score['realization'],
                'score' => $score['score'],
                'final_score' => $score['final_score']
            ];
            $aspects[$aspect]['total_aspect'] += $score['final_score'];
        }

        foreach ($aspects as &$aspect) {
            $aspect['value_conversion'] = $this->getAspectValueConversion($aspect['aspect'], $aspect['total_aspect']);
            $grandTotal += $aspect['total_aspect'];
            $kpiData[] = $aspect;
        }

        return [
            'user_id' => $this->user_id,
            'year' => $this->year,
            'month' => $this->month,
            'kpi_data' => $kpiData,
            'total_aspects' => $grandTotal,
            'value_conversion' => $this->getValueConversion($grandTotal)
        ];
    }
}
