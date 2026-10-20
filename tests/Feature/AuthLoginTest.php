<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake(fn () => Http::response(['success' => true, 'data' => []]));
    }

    public function test_login_page_only_requires_a_voucher_code(): void
    {
        $this->get('/')->assertOk()->assertSee('Masukkan kode voucher')
            ->assertSee('name="code"', false)->assertSee('Masuk')
            ->assertDontSee('name="password"', false)
            ->assertDontSee('name="merchant_id"', false);
        Http::assertNothingSent();
    }

    public static function voucherPackages(): array
    {
        return [
            ['MYADS20K', 20000, 7, 400, '400 pesan'],
            ['MYADS25K', 25000, 7, 800, '800 pesan'],
            ['MYADS50K', 50000, 30, 10000, '10.000 pesan'],
        ];
    }

    #[DataProvider('voucherPackages')]
    public function test_dummy_code_opens_the_matching_package(string $code, int $price, int $days, ?int $quota, string $label): void
    {
        $this->post('/login', ['code' => $code])->assertRedirect(route('dashboard'))
            ->assertSessionHas('voucher', ['price' => $price, 'report_days' => $days, 'sms_quota' => $quota, 'campaign_type' => 'sms_lba', 'code' => $code])
            ->assertSessionMissing('myads.access_token');
        $this->get('/dashboard')->assertOk()->assertSee($code)->assertSee($days.' hari')->assertSee($label);
        Http::assertNothingSent();
    }

    public function test_codes_accept_lowercase_and_surrounding_spaces(): void
    {
        $this->post('/login', ['code' => ' myads20k '])->assertRedirect(route('dashboard'))
            ->assertSessionHas('voucher.code', 'MYADS20K');
    }

    public static function invalidCodes(): array
    {
        return [[''], ['AB12'], ['ABCDE'], ['ABCDEFGHI'], ['AB-123'], ['ZZ9999'], [['MYADS20K']]];
    }

    public static function validCodeLengths(): array
    {
        return [['ABC123'], ['ABC1234'], ['ABC12345']];
    }

    #[DataProvider('validCodeLengths')]
    public function test_registered_codes_accept_six_to_eight_alphanumeric_characters(string $code): void
    {
        config(['vouchers.'.$code => config('vouchers.MYADS20K')]);
        $this->post('/login', ['code' => $code])->assertRedirect(route('dashboard'))
            ->assertSessionHas('voucher.code', $code);
        Http::assertNothingSent();
    }

    #[DataProvider('invalidCodes')]
    public function test_invalid_codes_do_not_grant_access(mixed $code): void
    {
        $this->post('/login', ['code' => $code])->assertSessionHasErrors('code')
            ->assertSessionMissing('voucher')->assertSessionMissing('myads.access_token');
        Http::assertNothingSent();
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_voucher_session_redirects_from_login_and_cannot_call_live_api(): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->get('/')->assertRedirect(route('dashboard'));
        $this->get('/campaign/sms/location-based-area')->assertRedirect(route('dashboard'));
        $this->getJson('/campaign/sms/report')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_password_and_registration_routes_are_removed(): void
    {
        $this->get('/register')->assertNotFound();
        $this->get('/forgot-password')->assertNotFound();
        $this->get('/reset-password/sample-token')->assertNotFound();
        $this->post('/login', ['merchant_id' => 'merchant', 'password' => 'secret'])
            ->assertSessionHasErrors('code')->assertSessionMissing('voucher');
    }

    public function test_logout_clears_voucher_and_login_session(): void
    {
        $this->post('/login', ['code' => 'MYADS50K']);
        $this->post('/logout')->assertRedirect(route('login'))
            ->assertSessionMissing('voucher')->assertSessionMissing('myads');
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->post('/login', ['code' => 'MYADS25K'])->assertSessionHas('voucher.code', 'MYADS25K');
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

        $this->get('/campaign/sms/location-based-area?view=create')->assertOk()
            ->assertSee('id="campaignMapModal"', false)
            ->assertSee('name="location_radius" form="campaignGwSubmitForm"', false);
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
