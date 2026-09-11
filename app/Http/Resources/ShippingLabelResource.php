<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingLabelResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recipient' => [
                'name' => $this->to_address['name'] ?? null,
                'city' => $this->to_address['city'] ?? null,
                'state' => $this->to_address['state'] ?? null,
                'zip' => $this->to_address['zip'] ?? null,
            ],
            'carrier' => $this->carrier,
            'service' => $this->service,
            'tracking_code' => $this->tracking_code,
            'status' => $this->status,
            'label_url' => $this->label_url,
            'label_pdf_url' => $this->label_pdf_url,
            'from_address' => $this->from_address,
            'to_address' => $this->to_address,
            'parcel' => $this->parcel,
            'rate' => $this->rate,
            'error_message' => $this->error_message,
            'print_url' => route('api.labels.print', $this->resource, absolute: false),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
