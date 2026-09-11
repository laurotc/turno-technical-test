<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ShippingLabel>
 */
class ShippingLabelFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'easypost_shipment_id' => 'shp_'.$this->faker->unique()->regexify('[A-Za-z0-9]{24}'),
            'easypost_postage_label_id' => 'pl_'.$this->faker->unique()->regexify('[A-Za-z0-9]{24}'),
            'easypost_rate_id' => 'rate_'.$this->faker->unique()->regexify('[A-Za-z0-9]{24}'),
            'carrier' => 'USPS',
            'service' => 'GroundAdvantage',
            'tracking_code' => $this->faker->numerify('94####################'),
            'label_url' => 'https://example.com/label.png',
            'label_pdf_url' => 'https://example.com/label.pdf',
            'from_address' => [
                'name' => 'Sender',
                'street1' => '118 2nd Street',
                'city' => 'San Francisco',
                'state' => 'CA',
                'zip' => '94105',
                'country' => 'US',
                'phone' => '4155550100',
            ],
            'to_address' => [
                'name' => 'Recipient',
                'street1' => '179 N Harbor Dr',
                'city' => 'Redondo Beach',
                'state' => 'CA',
                'zip' => '90277',
                'country' => 'US',
                'phone' => '3105550100',
            ],
            'parcel' => [
                'length' => 10,
                'width' => 8,
                'height' => 4,
                'weight' => 16,
            ],
            'rate' => [
                'id' => 'rate_test',
                'carrier' => 'USPS',
                'service' => 'GroundAdvantage',
                'rate' => '5.25',
            ],
            'raw_response' => ['mode' => 'test'],
            'status' => 'purchased',
        ];
    }
}
