<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GeneralApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'status_code' => $this->resource['status_code'] ?? 200,
            'success' => $this->resource['success'] ?? true,
            'message' => $this->resource['message'] ?? 'Request was successful',
            'data' => $this->resource['data'] ?? null,
        ];
    }
}
