<?php

namespace App\Services\EasyPost;

use App\Contracts\ShippingLabelPurchaser;
use App\Data\PurchasedShippingLabel;
use EasyPost\EasyPostClient;
use Illuminate\Support\Arr;

class EasyPostShippingLabelPurchaser implements ShippingLabelPurchaser
{
    public function __construct(
        private readonly ?string $apiKey = null,
    ) {
    }

    public function purchase(array $fromAddress, array $toAddress, array $parcel): PurchasedShippingLabel
    {
        $apiKey = $this->apiKey ?? config('services.easypost.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new EasyPostShippingLabelException('EasyPost API key is not configured.');
        }

        $client = new EasyPostClient($apiKey);

        try {
            $shipment = $client->shipment->create([
                'from_address' => $fromAddress,
                'to_address' => $toAddress,
                'parcel' => $parcel,
                'options' => [
                    'label_format' => 'PDF',
                    'label_size' => '4x6',
                ],
            ]);

            $rate = $this->lowestUspsRate($this->objectToArray($shipment->rates ?? []));

            if (! $rate) {
                throw new EasyPostShippingLabelException('No USPS rates were returned for this shipment.');
            }

            $boughtShipment = $client->shipment->buy($shipment->id, ['id' => $rate['id']]);
            $response = $this->objectToArray($boughtShipment);
            $postageLabel = Arr::get($response, 'postage_label', []);
            $selectedRate = Arr::get($response, 'selected_rate', $rate);

            return new PurchasedShippingLabel(
                easypostShipmentId: (string) Arr::get($response, 'id'),
                easypostPostageLabelId: Arr::get($postageLabel, 'id'),
                easypostRateId: Arr::get($selectedRate, 'id'),
                carrier: Arr::get($selectedRate, 'carrier'),
                service: Arr::get($selectedRate, 'service'),
                trackingCode: Arr::get($response, 'tracking_code'),
                labelUrl: Arr::get($postageLabel, 'label_url'),
                labelPdfUrl: Arr::get($postageLabel, 'label_pdf_url'),
                fromAddress: $fromAddress,
                toAddress: $toAddress,
                parcel: $parcel,
                rate: is_array($selectedRate) ? $selectedRate : null,
                rawResponse: $response,
                status: (string) Arr::get($response, 'status', 'purchased'),
            );
        } catch (EasyPostShippingLabelException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new EasyPostShippingLabelException($exception->getMessage(), previous: $exception);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rates
     * @return array<string, mixed>|null
     */
    private function lowestUspsRate(array $rates): ?array
    {
        $uspsRates = array_values(array_filter(
            $rates,
            fn (array $rate): bool => strtoupper((string) ($rate['carrier'] ?? '')) === 'USPS'
        ));

        usort(
            $uspsRates,
            fn (array $left, array $right): int => (float) ($left['rate'] ?? PHP_FLOAT_MAX) <=> (float) ($right['rate'] ?? PHP_FLOAT_MAX)
        );

        return $uspsRates[0] ?? null;
    }

    /**
     * @return array<mixed>
     */
    private function objectToArray(mixed $value): array
    {
        return json_decode(json_encode($value, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
    }
}
