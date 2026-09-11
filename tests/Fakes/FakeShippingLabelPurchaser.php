<?php

namespace Tests\Fakes;

use App\Contracts\ShippingLabelPurchaser;
use App\Data\PurchasedShippingLabel;
use App\Services\EasyPost\EasyPostShippingLabelException;

class FakeShippingLabelPurchaser implements ShippingLabelPurchaser
{
    public ?EasyPostShippingLabelException $exception = null;

    public function purchase(array $fromAddress, array $toAddress, array $parcel): PurchasedShippingLabel
    {
        if ($this->exception) {
            throw $this->exception;
        }

        return new PurchasedShippingLabel(
            easypostShipmentId: 'shp_test123',
            easypostPostageLabelId: 'pl_test123',
            easypostRateId: 'rate_test123',
            carrier: 'USPS',
            service: 'GroundAdvantage',
            trackingCode: '9400100000000000000000',
            labelUrl: 'https://example.com/label.png',
            labelPdfUrl: 'https://example.com/label.pdf',
            fromAddress: $fromAddress,
            toAddress: $toAddress,
            parcel: $parcel,
            rate: [
                'id' => 'rate_test123',
                'carrier' => 'USPS',
                'service' => 'GroundAdvantage',
                'rate' => '5.25',
            ],
            rawResponse: [
                'id' => 'shp_test123',
                'mode' => 'test',
            ],
            status: 'purchased',
        );
    }
}
