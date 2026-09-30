<?php

namespace Tests\Feature;

use App\Services\MyadsApiClient;
use Illuminate\Support\Facades\Http;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CampaignPaymentProtectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->withSession([
            'myads.access_token' => 'access', 'myads.gw_token' => 'gateway',
            'myads.user' => ['id' => 'merchant', 'name' => 'Merchant', 'email' => 'merchant@example.test'],
        ]);
    }

    public static function channels(): array { return [['targeted'], ['location-based-area']]; }

    private function payload(): array
    {
        return [
            'payment_transaction_id' => 'MEMBER-FAKE', 'sender_name' => 'SHOPADS', 'message' => 'Promo terbaru',
            'recipient_total' => 100, 'control_number' => '081234567890',
            'delivery_date' => now()->addDay()->format('Y-m-d'), 'delivery_time' => '12:00',
            'delivery_start_date' => now()->addDay()->format('Y-m-d'), 'delivery_end_date' => now()->addDay()->format('Y-m-d'),
            'delivery_start_time' => '12:00', 'delivery_end_time' => '14:00',
            'location_name' => 'Jakarta', 'location_lat' => -6.2, 'location_lng' => 106.8, 'location_radius' => 1000,
        ];
    }

    #[DataProvider('channels')]
    public function test_member_prefix_never_bypasses_server_payment_validation(string $channel): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('getMembershipStatus')->once()->andReturn(['success' => true, 'data' => ['is_member' => 'false']]);
        $client->shouldReceive('paymentStatus')->once()->with('access', 'MEMBER-FAKE')->andReturn(['success' => true, 'data' => ['status' => 'PENDING']]);
        $client->shouldNotReceive('campaignTargetedAdd');
        $client->shouldNotReceive('campaignLbaAdd');
        $this->app->instance(MyadsApiClient::class, $client);
        $this->post("/campaign/sms/$channel/send", $this->payload())->assertSessionHasErrors();
    }

    #[DataProvider('channels')]
    public function test_verified_active_member_can_send_without_payment(string $channel): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('getMembershipStatus')->once()->andReturn(['success' => true, 'data' => ['is_member' => true]]);
        $client->shouldNotReceive('paymentStatus');
        $method = $channel === 'targeted' ? 'campaignTargetedAdd' : 'campaignLbaAdd';
        $client->shouldReceive($method)->once()->andReturn(['success' => true, 'data' => ['campaignId' => 123]]);
        $this->app->instance(MyadsApiClient::class, $client);
        $payload = $this->payload();
        unset($payload['payment_transaction_id']);
        $this->post("/campaign/sms/$channel/send", $payload)->assertSessionHasNoErrors()->assertSessionHas('status');
    }

    #[DataProvider('channels')]
    public function test_membership_lookup_failure_does_not_grant_free_sending(string $channel): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('getMembershipStatus')->once()->andThrow(new \RuntimeException('API unavailable'));
        $client->shouldReceive('paymentStatus')->once()->andReturn(['success' => true, 'data' => ['status' => 'FAILED']]);
        $client->shouldNotReceive('campaignTargetedAdd');
        $client->shouldNotReceive('campaignLbaAdd');
        $this->app->instance(MyadsApiClient::class, $client);
        $this->post("/campaign/sms/$channel/send", $this->payload())->assertSessionHasErrors();
    }
}
