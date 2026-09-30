<?php

namespace Tests\Feature;

use App\Services\MyadsApiClient;
use Illuminate\Support\Facades\Http;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MembershipFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function merchant(): self
    {
        return $this->withSession([
            'myads.access_token' => 'access', 'myads.gw_token' => 'gateway',
            'myads.user' => ['id' => 'merchant', 'name' => 'Merchant', 'email' => 'merchant@example.test'],
        ]);
    }

    public function test_guest_payment_request_returns_json_401(): void
    {
        $this->postJson('/membership/join', ['customer_phone' => '081234567890'])->assertUnauthorized()->assertJsonPath('success', false);
        $this->getJson('/membership/status')->assertUnauthorized();
        $this->postJson('/campaign/payment/initiate', [])->assertUnauthorized();
    }

    public static function invalidPhones(): array
    {
        return [[''], ['abc081234567890'], ['12345'], ['021234567890'], ['628123456789012345']];
    }

    #[DataProvider('invalidPhones')]
    public function test_invalid_phone_never_initiates_a_payment(string $phone): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldNotReceive('joinMembership');
        $this->app->instance(MyadsApiClient::class, $client);
        $this->merchant()->postJson('/membership/join', ['customer_phone' => $phone])
            ->assertUnprocessable()->assertJsonValidationErrors('customer_phone');
    }

    public static function validPhones(): array
    {
        return [['081234567890'], ['+62 812-3456-7890'], ['6281234567890'], ['81234567890']];
    }

    #[DataProvider('validPhones')]
    public function test_valid_phone_is_normalized_and_upstream_payment_is_preserved(string $phone): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('joinMembership')->once()->with('access', Mockery::on(fn ($payload) =>
            $payload['customer_phone'] === '6281234567890' && $payload['customer_email'] === 'merchant@example.test'
        ))->andReturn(['success' => true, 'data' => ['payment' => ['transaction_id' => 'txn-1', 'status' => 'PENDING']]]);
        $this->app->instance(MyadsApiClient::class, $client);
        $this->merchant()->postJson('/membership/join', ['customer_phone' => $phone])
            ->assertOk()->assertJsonPath('data.payment.transaction_id', 'txn-1')->assertJsonPath('data.payment.status', 'PENDING');
    }

    public function test_membership_status_updates_sidebar_session(): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('getMembershipStatus')->once()->with('access')->andReturn(['success' => true, 'data' => ['is_member' => true]]);
        $this->app->instance(MyadsApiClient::class, $client);
        $this->merchant()->getJson('/membership/status')->assertOk()->assertSessionHas('myads.membership.is_member', true);
    }

    public function test_price_breakdown_is_recalculated_when_api_only_returns_new_price(): void
    {
        Http::fake([
            '*/api/membership/status' => Http::response(['success' => true, 'data' => ['is_member' => false, 'price' => 40000, 'tax_percentage' => 10]]),
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);
        $this->merchant()->get('/dashboard')->assertOk()->assertSee('44.000')->assertSee('PPN 10%');
    }
}
