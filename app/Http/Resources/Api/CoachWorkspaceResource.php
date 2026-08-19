<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachWorkspaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'summary' => $this['summary'],
            'at_risk_clients' => $this['at_risk_clients'],
            'clients' => $this['clients'],
            'availabilities' => $this['availabilities'],
        ];
    }
}
