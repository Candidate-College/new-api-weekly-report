<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'batch_no' => $this->batch_no,
            'division_id' => $this->division_id,
            'supervisor_id' => $this->supervisor_id,
            'vice_supervisor_id' => $this->vice_supervisor_id,
            'CFlag' => $this->CFlag,
            'Sflag' => $this->Sflag,
            'StFlag' => $this->StFlag,
            'profile_picture' => $this->profile_picture,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
