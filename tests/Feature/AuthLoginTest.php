<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertStatus(401);
        $response->assertSee('Akses Ditolak');
    }

    public function test_register_page_requires_merchant_and_partner_context(): void
    {
        $response = $this->get('/register');

        $response->assertForbidden();
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
        $response = $this->get('/dashboard');

        $response->assertStatus(401);
        $response->assertSee('Akses Ditolak');
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
