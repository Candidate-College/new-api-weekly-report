<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'content_text' => $this->content_text,
            'content_photo' => $this->content_photo,
            'last_updated_at' => $this->last_updated_at,
        ];
    }
}
