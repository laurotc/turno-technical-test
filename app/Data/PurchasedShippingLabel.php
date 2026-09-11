<?php

namespace App\Data;

final readonly class PurchasedShippingLabel
{
    /**
     * @param array<string, mixed> $fromAddress
     * @param array<string, mixed> $toAddress
     * @param array<string, mixed> $parcel
     * @param array<string, mixed>|null $rate
     * @param array<string, mixed>|null $rawResponse
     */
    public function __construct(
        public string $easypostShipmentId,
        public ?string $easypostPostageLabelId,
        public ?string $easypostRateId,
        public ?string $carrier,
        public ?string $service,
        public ?string $trackingCode,
        public ?string $labelUrl,
        public ?string $labelPdfUrl,
        public array $fromAddress,
        public array $toAddress,
        public array $parcel,
        public ?array $rate,
        public ?array $rawResponse,
        public string $status,
        public ?string $errorMessage = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabaseAttributes(): array
    {
        return [
            'easypost_shipment_id' => $this->easypostShipmentId,
            'easypost_postage_label_id' => $this->easypostPostageLabelId,
            'easypost_rate_id' => $this->easypostRateId,
            'carrier' => $this->carrier,
            'service' => $this->service,
            'tracking_code' => $this->trackingCode,
            'label_url' => $this->labelUrl,
            'label_pdf_url' => $this->labelPdfUrl,
            'from_address' => $this->fromAddress,
            'to_address' => $this->toAddress,
            'parcel' => $this->parcel,
            'rate' => $this->rate,
            'raw_response' => $this->rawResponse,
            'status' => $this->status,
            'error_message' => $this->errorMessage,
        ];
    }
}
