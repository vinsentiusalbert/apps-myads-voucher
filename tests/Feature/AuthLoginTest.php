<?php

namespace Tests\Feature;

use App\Services\MyadsApiClient;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake(fn () => Http::response(['success' => true, 'data' => []]));
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Masuk akun ShopAds');
    }

    public function test_register_page_allows_merchant_registration(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('Daftar Akun Merchant');
    }

    public function test_register_page_is_accessible_with_merchant_and_partner_context(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => [
                    'merchant_id' => 'merchant-001',
                    'partner_id' => 'partner-001',
                ],
            ]),
        ]);

        $response = $this->get('/register?merchant_id=merchant-001&partner_id=partner-001');

        $response->assertOk();
        $response->assertSee('merchant-001');
        $response->assertSee('partner-001');
    }

    public function test_forgot_password_page_is_accessible(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
        $response->assertSee('Lupa password');
    }

    public function test_reset_password_page_is_accessible(): void
    {
        $response = $this->get('/reset-password/sample-token?email=operator@myads.test');

        $response->assertOk();
        $response->assertSee('Reset password');
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_removed_sso_route_is_not_exposed(): void
    {
        $this->get('/sso/complete?access_token=token-001')->assertNotFound();
    }

    public function test_success_response_without_access_token_does_not_create_a_session(): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('login')->once()->andReturn(['success' => true, 'data' => []]);
        $this->app->instance(MyadsApiClient::class, $client);
        $this->post('/login', ['merchant_id' => 'merchant-001', 'password' => 'secret'])
            ->assertSessionHasErrors('merchant_id')->assertSessionMissing('myads.access_token');
    }

    public function test_valid_credentials_create_a_session_and_redirect(): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('login')->with('merchant-001', 'secret')->once()->andReturn([
            'success' => true, 'data' => ['access_token' => 'token-001', 'user' => ['name' => 'Merchant']],
        ]);
        $client->shouldReceive('getGatewayToken')->with('token-001')->once()->andReturn(['data' => ['token' => 'gateway']]);
        $client->shouldReceive('extractGatewayToken')->once()->andReturn('gateway');
        $this->app->instance(MyadsApiClient::class, $client);
        $this->post('/login', ['merchant_id' => 'merchant-001', 'password' => 'secret'])
            ->assertRedirect(route('dashboard'))->assertSessionHas('myads.access_token', 'token-001');
    }

    public function test_invalid_credentials_show_validation_error(): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('login')->once()->andReturn(['success' => false, 'message' => 'Password tidak sesuai.']);
        $this->app->instance(MyadsApiClient::class, $client);
        $this->post('/login', ['merchant_id' => 'merchant-001', 'password' => 'wrong'])
            ->assertSessionHasErrors('merchant_id')->assertSessionMissing('myads.access_token');
    }

    public function test_logout_clears_membership_and_login_session(): void
    {
        $this->withApiSession()->withSession(['myads.membership' => ['is_member' => true]])
            ->post('/logout')->assertRedirect(route('login'))
            ->assertSessionMissing('myads.access_token')->assertSessionMissing('myads.membership');
    }

    public function test_authenticated_user_can_open_dashboard(): void
    {
        $response = $this->withApiSession()->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Ads Operator');
        $response->assertSee('SMS');
        $response->assertSee('WA Business');
    }

    public function test_authenticated_user_can_open_sms_location_based_area_page(): void
    {
        $response = $this->withApiSession()->get('/campaign/sms/location-based-area');

        $response->assertOk();
        $response->assertSee('SMS Location Based Area');
    }

    public function test_authenticated_user_can_open_sms_targeted_page(): void
    {
        $response = $this->withApiSession()->get('/campaign/sms/targeted');

        $response->assertOk();
        $response->assertSee('SMS Targeted');
    }

    public function test_authenticated_user_can_open_wa_business_targeted_create_page(): void
    {
        $response = $this->withApiSession()->get('/campaign/wa-business/targeted?view=create');

        $response->assertOk();
        $response->assertSee('Buat Iklan WA Business Targeted');
        $response->assertSee('Template Pesan');
    }

    private function withApiSession(): self
    {
        return $this->withSession([
            'myads.access_token' => 'test-access-token',
            'myads.gw_token' => 'test-gw-token',
            'myads.user' => [
                'id' => 'operator@myads.test',
                'name' => 'Ads Operator',
                'email' => 'operator@myads.test',
            ],
        ]);
    }
}
