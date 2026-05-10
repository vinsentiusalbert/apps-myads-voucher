<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Masuk ke MyAds');
        $response->assertSee('Daftar melalui tautan partner');
        $response->assertSee(route('password.request'));
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
        $response->assertSee('Daftar akun MyAds');
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

        $response->assertRedirect('/');
    }

    public function test_authenticated_user_can_open_dashboard(): void
    {
        $user = new User([
            'name' => 'Ads Operator',
            'email' => 'operator@myads.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Selamat Datang,');
        $response->assertSee('Ads Operator');
        $response->assertSee('Ringkasan Dashboard');
        $response->assertSee('SMS');
        $response->assertSee('WA Business');
        $response->assertSee('Location Based Area belum diisi.');
    }

    public function test_authenticated_user_can_open_sms_location_based_area_page(): void
    {
        $user = new User([
            'name' => 'Ads Operator',
            'email' => 'operator@myads.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/campaign/sms/location-based-area');

        $response->assertOk();
        $response->assertSee('SMS Location Based Area');
        $response->assertSee('ID Iklan');
        $response->assertSee('Judul Pesan Iklan');
    }

    public function test_authenticated_user_can_open_sms_targeted_page(): void
    {
        $user = new User([
            'name' => 'Ads Operator',
            'email' => 'operator@myads.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/campaign/sms/targeted');

        $response->assertOk();
        $response->assertSee('SMS Targeted');
        $response->assertSee('ID Iklan');
        $response->assertSee('Judul Pesan Iklan');
    }

    public function test_authenticated_user_can_open_sms_location_based_area_create_page(): void
    {
        $user = new User([
            'name' => 'Ads Operator',
            'email' => 'operator@myads.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/campaign/sms/location-based-area?view=create');

        $response->assertOk();
        $response->assertSee('Buat Judul Iklan');
        $response->assertSee('Konten Pesan Iklan');
        $response->assertSee('Atur Pengiriman');
    }

    public function test_authenticated_user_can_open_sms_location_based_area_show_page(): void
    {
        $user = new User([
            'name' => 'Ads Operator',
            'email' => 'operator@myads.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/campaign/sms/location-based-area?view=show&id=1642248');

        $response->assertOk();
        $response->assertSee('Detail Iklan');
        $response->assertSee('Location Based Advertising - SMS');
        $response->assertSee('Pengiriman');
        $response->assertSee('Biaya Pembayaran');
    }

    public function test_authenticated_user_can_open_sms_targeted_create_page(): void
    {
        $user = new User([
            'name' => 'Ads Operator',
            'email' => 'operator@myads.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/campaign/sms/targeted?view=create');

        $response->assertOk();
        $response->assertSee('Buat Judul Iklan');
        $response->assertSee('Konten Pesan Iklan');
        $response->assertSee('Review & Pembayaran');
    }

    public function test_authenticated_user_can_open_wa_business_location_based_area_page(): void
    {
        $user = new User([
            'name' => 'Ads Operator',
            'email' => 'operator@myads.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/campaign/wa-business/location-based-area');

        $response->assertOk();
        $response->assertSee('WA Business Location Based Area');
        $response->assertSee('ID Iklan');
        $response->assertSee('Judul Pesan Iklan');
    }

    public function test_authenticated_user_can_open_wa_business_location_based_area_show_page(): void
    {
        $user = new User([
            'name' => 'Ads Operator',
            'email' => 'operator@myads.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/campaign/wa-business/location-based-area?view=show&id=1640146');

        $response->assertOk();
        $response->assertSee('Detail Iklan');
        $response->assertSee('WA Business - Lba');
        $response->assertSee('Laporan Iklan -');
        $response->assertSee('Biaya Pembayaran');
    }

    public function test_authenticated_user_can_open_wa_business_targeted_create_page(): void
    {
        $user = new User([
            'name' => 'Ads Operator',
            'email' => 'operator@myads.test',
            'password' => 'password',
        ]);

        $response = $this->actingAs($user)->get('/campaign/wa-business/targeted?view=create');

        $response->assertOk();
        $response->assertSee('Buat Iklan WA Business Targeted');
        $response->assertSee('Template Pesan');
        $response->assertSee('Atur Profil Penerima');
    }
}
