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
    ) {}

    public function purchase(array $fromAddress, array $toAddress, array $parcel): PurchasedShippingLabel
    {
        $apiKey = $this->apiKey ?? config('services.easypost.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new EasyPostShippingLabelException('EasyPost API key is not configured.');
        }

        $client = new EasyPostClient($apiKey);

        try {
            $shipment = $client->shipment->create($this->baseShipmentPayload($fromAddress, $toAddress, $parcel));
            $shipmentPayload = $this->objectToArray($shipment);
            $rate = $this->preferredUspsRate(Arr::get($shipmentPayload, 'rates', []));

            if (! $rate) {
                throw new EasyPostShippingLabelException($this->noRatesMessage($shipmentPayload));
            }

            $response = $this->objectToArray($client->shipment->buy($shipment->id, ['rate' => $rate]));

            return $this->purchasedLabelFromResponse($response, $fromAddress, $toAddress, $parcel, $rate);
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
    private function preferredUspsRate(array $rates): ?array
    {
        $uspsRates = array_values(array_filter(
            $rates,
            fn(array $rate): bool => isset($rate['id'])
                && strtoupper((string) ($rate['carrier'] ?? '')) === 'USPS'
        ));

        return $uspsRates[0] ?? null;
    }

    /**
     * @param array<string, mixed> $shipmentPayload
     */
    private function noRatesMessage(array $shipmentPayload): string
    {
        $messages = Arr::get($shipmentPayload, 'messages', []);

        if (! is_array($messages) || $messages === []) {
            return 'No USPS rates were returned for this shipment.';
        }

        $formattedMessages = array_filter(array_map(
            fn(mixed $message): ?string => is_array($message)
                ? trim(sprintf(
                    '%s: %s',
                    $message['carrier'] ?? 'Carrier',
                    $message['message'] ?? $message['type'] ?? 'No rate message provided.'
                ))
                : null,
            $messages,
        ));

        if ($formattedMessages === []) {
            return 'No USPS rates were returned for this shipment.';
        }

        return 'No USPS rates were returned for this shipment. ' . implode(' ', $formattedMessages);
    }

    /**
     * @param array<string, mixed> $fromAddress
     * @param array<string, mixed> $toAddress
     * @param array<string, mixed> $parcel
     * @return array<string, mixed>
     */
    private function baseShipmentPayload(array $fromAddress, array $toAddress, array $parcel): array
    {
        return [
            'from_address' => $fromAddress,
            'to_address' => $toAddress,
            'parcel' => $parcel,
            'options' => [
                'label_format' => 'PDF',
                'label_size' => '4x6',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $response
     * @param array<string, mixed> $fromAddress
     * @param array<string, mixed> $toAddress
     * @param array<string, mixed> $parcel
     * @param array<string, mixed>|null $fallbackRate
     */
    private function purchasedLabelFromResponse(
        array $response,
        array $fromAddress,
        array $toAddress,
        array $parcel,
        ?array $fallbackRate = null,
    ): PurchasedShippingLabel {
        $postageLabel = Arr::get($response, 'postage_label', []);
        $selectedRate = Arr::get($response, 'selected_rate');
        $rate = is_array($selectedRate) ? $selectedRate : $fallbackRate;

        return new PurchasedShippingLabel(
            easypostShipmentId: (string) Arr::get($response, 'id'),
            easypostPostageLabelId: Arr::get($postageLabel, 'id'),
            easypostRateId: Arr::get($rate ?? [], 'id'),
            carrier: Arr::get($rate ?? [], 'carrier'),
            service: Arr::get($rate ?? [], 'service'),
            trackingCode: Arr::get($response, 'tracking_code'),
            labelUrl: Arr::get($postageLabel, 'label_url'),
            labelPdfUrl: Arr::get($postageLabel, 'label_pdf_url'),
            fromAddress: $fromAddress,
            toAddress: $toAddress,
            parcel: $parcel,
            rate: $rate,
            rawResponse: $response,
            status: (string) Arr::get($response, 'status', 'purchased'),
        );
    }

    /**
     * @return array<mixed>
     */
    private function objectToArray(mixed $value): array
    {
        if (is_object($value) && method_exists($value, '__toArray')) {
            return $value->__toArray(true);
        }

        return json_decode(json_encode($value, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
    }
}
