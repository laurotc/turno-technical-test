<?php

namespace App\Contracts;

use App\Data\PurchasedShippingLabel;

interface ShippingLabelPurchaser
{
    /**
     * @param array<string, mixed> $fromAddress
     * @param array<string, mixed> $toAddress
     * @param array<string, mixed> $parcel
     */
    public function purchase(array $fromAddress, array $toAddress, array $parcel): PurchasedShippingLabel;
}
