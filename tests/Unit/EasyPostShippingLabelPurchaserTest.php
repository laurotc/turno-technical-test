<?php

namespace Tests\Unit;

use App\Services\EasyPost\EasyPostShippingLabelPurchaser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EasyPostShippingLabelPurchaserTest extends TestCase
{
    public function test_it_selects_the_first_returned_usps_rate(): void
    {
        $rate = $this->preferredUspsRate([
            ['id' => 'fedex-cheap', 'carrier' => 'FedEx', 'rate' => '4.00'],
            ['id' => 'usps-expensive', 'carrier' => 'USPS', 'rate' => '9.00'],
            ['id' => 'usps-cheap', 'carrier' => 'USPS', 'rate' => '6.00'],
        ]);

        $this->assertSame('usps-expensive', $rate['id']);
    }

    public function test_it_does_not_fall_back_to_non_usps_rates(): void
    {
        $rate = $this->preferredUspsRate([
            ['id' => 'ups-expensive', 'carrier' => 'UPS', 'rate' => '8.00'],
            ['id' => 'fedex-cheap', 'carrier' => 'FedEx', 'rate' => '4.00'],
        ]);

        $this->assertNull($rate);
    }

    public function test_it_returns_null_when_no_usable_rates_exist(): void
    {
        $this->assertNull($this->preferredUspsRate([
            ['id' => 'missing-carrier', 'rate' => '4.00'],
            ['carrier' => 'FedEx', 'rate' => '4.00'],
        ]));
    }

    public function test_it_includes_easypost_messages_when_no_rates_exist(): void
    {
        $message = $this->noRatesMessage([
            'messages' => [
                [
                    'carrier' => 'USPS',
                    'message' => 'No shipment methods found for these addresses.',
                ],
                [
                    'carrier' => 'UPS',
                    'message' => 'Invalid carrier account.',
                ],
            ],
        ]);

        $this->assertSame(
            'No USPS rates were returned for this shipment. USPS: No shipment methods found for these addresses. UPS: Invalid carrier account.',
            $message,
        );
    }

    public function test_it_converts_easypost_objects_with_the_sdk_array_method(): void
    {
        $payload = $this->objectToArray(new class {
            /**
             * @return array<string, mixed>
             */
            public function __toArray(bool $recursive = false): array
            {
                return [
                    'id' => 'shp_test123',
                    'recursive' => $recursive,
                    'rates' => [
                        ['id' => 'rate_test123', 'carrier' => 'USPS'],
                    ],
                ];
            }
        });

        $this->assertSame('shp_test123', $payload['id']);
        $this->assertTrue($payload['recursive']);
        $this->assertSame('rate_test123', $payload['rates'][0]['id']);
    }

    /**
     * @param array<int, array<string, mixed>> $rates
     * @return array<string, mixed>|null
     */
    private function preferredUspsRate(array $rates): ?array
    {
        $reflection = new ReflectionClass(EasyPostShippingLabelPurchaser::class);
        $method = $reflection->getMethod('preferredUspsRate');

        return $method->invoke(new EasyPostShippingLabelPurchaser('EZTK_TEST'), $rates);
    }

    /**
     * @param array<string, mixed> $shipmentPayload
     */
    private function noRatesMessage(array $shipmentPayload): string
    {
        $reflection = new ReflectionClass(EasyPostShippingLabelPurchaser::class);
        $method = $reflection->getMethod('noRatesMessage');

        return $method->invoke(new EasyPostShippingLabelPurchaser('EZTK_TEST'), $shipmentPayload);
    }

    /**
     * @param array<string, mixed> $fromAddress
     * @param array<string, mixed> $toAddress
     * @param array<string, mixed> $parcel
     * @return array<string, mixed>
     */
    private function baseShipmentPayload(array $fromAddress, array $toAddress, array $parcel): array
    {
        $reflection = new ReflectionClass(EasyPostShippingLabelPurchaser::class);
        $method = $reflection->getMethod('baseShipmentPayload');

        return $method->invoke(new EasyPostShippingLabelPurchaser('EZTK_TEST'), $fromAddress, $toAddress, $parcel);
    }

    /**
     * @return array<mixed>
     */
    private function objectToArray(mixed $value): array
    {
        $reflection = new ReflectionClass(EasyPostShippingLabelPurchaser::class);
        $method = $reflection->getMethod('objectToArray');

        return $method->invoke(new EasyPostShippingLabelPurchaser('EZTK_TEST'), $value);
    }
}
