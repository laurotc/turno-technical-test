<?php

namespace Tests\Feature;

use App\Contracts\ShippingLabelPurchaser;
use App\Models\ShippingLabel;
use App\Models\User;
use App\Services\EasyPost\EasyPostShippingLabelException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeShippingLabelPurchaser;
use Tests\TestCase;

class ShippingLabelApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_only_their_paginated_labels(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        ShippingLabel::factory()->count(2)->for($user)->create();
        ShippingLabel::factory()->for($otherUser)->create([
            'tracking_code' => 'OTHER_USER_TRACKING',
        ]);

        $response = $this
            ->withToken($user->createToken('api')->plainTextToken)
            ->getJson('/api/labels?per_page=1');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1);

        $this->assertStringNotContainsString(
            'OTHER_USER_TRACKING',
            $response->getContent()
        );
    }

    public function test_user_can_create_shipping_label(): void
    {
        $user = User::factory()->create();
        $this->app->instance(ShippingLabelPurchaser::class, new FakeShippingLabelPurchaser());

        $response = $this
            ->withToken($user->createToken('api')->plainTextToken)
            ->postJson('/api/labels', $this->validPayload());

        $response
            ->assertCreated()
            ->assertJsonPath('data.carrier', 'USPS')
            ->assertJsonPath('data.tracking_code', '9400100000000000000000')
            ->assertJsonPath('data.print_url', '/api/labels/1/print');

        $this->assertDatabaseHas('shipping_labels', [
            'user_id' => $user->id,
            'easypost_shipment_id' => 'shp_test123',
            'carrier' => 'USPS',
        ]);
    }

    public function test_create_shipping_label_validates_us_only_addresses(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload();
        $payload['to_address']['country'] = 'CA';

        $response = $this
            ->withToken($user->createToken('api')->plainTextToken)
            ->postJson('/api/labels', $payload);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['to_address.country']);
    }

    public function test_create_shipping_label_validates_positive_parcel_values(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload();
        $payload['parcel']['weight'] = 0;

        $response = $this
            ->withToken($user->createToken('api')->plainTextToken)
            ->postJson('/api/labels', $payload);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['parcel.weight']);
    }

    public function test_create_shipping_label_returns_clean_error_when_no_usps_rate_exists(): void
    {
        $user = User::factory()->create();
        $fakePurchaser = new FakeShippingLabelPurchaser();
        $fakePurchaser->exception = new EasyPostShippingLabelException('No USPS rates were returned for this shipment.');
        $this->app->instance(ShippingLabelPurchaser::class, $fakePurchaser);

        $response = $this
            ->withToken($user->createToken('api')->plainTextToken)
            ->postJson('/api/labels', $this->validPayload());

        $response
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Unable to create shipping label.')
            ->assertJsonPath('error', 'No USPS rates were returned for this shipment.');
    }

    public function test_user_can_print_their_label_pdf(): void
    {
        $user = User::factory()->create();
        $label = ShippingLabel::factory()->for($user)->create([
            'label_pdf_url' => 'https://example.com/print.pdf',
        ]);

        $response = $this
            ->withToken($user->createToken('api')->plainTextToken)
            ->getJson("/api/labels/{$label->id}/print");

        $response
            ->assertRedirect('https://example.com/print.pdf');
    }

    public function test_print_falls_back_to_label_url(): void
    {
        $user = User::factory()->create();
        $label = ShippingLabel::factory()->for($user)->create([
            'label_pdf_url' => null,
            'label_url' => 'https://example.com/print.png',
        ]);

        $response = $this
            ->withToken($user->createToken('api')->plainTextToken)
            ->getJson("/api/labels/{$label->id}/print");

        $response
            ->assertRedirect('https://example.com/print.png');
    }

    public function test_user_cannot_print_another_users_label(): void
    {
        $user = User::factory()->create();
        $label = ShippingLabel::factory()->for(User::factory())->create();

        $response = $this
            ->withToken($user->createToken('api')->plainTextToken)
            ->getJson("/api/labels/{$label->id}/print");

        $response->assertNotFound();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
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
        ];
    }
}
