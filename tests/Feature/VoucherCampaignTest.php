<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VoucherCampaignTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->travelTo(now()->setTimezone('Asia/Jakarta')->setDate(2026, 10, 20)->setTime(10, 0));
    }

    private function campaignData(array $overrides = []): array
    {
        return array_merge([
            'campaign_name' => 'Promo toko',
            'sender_name' => 'TOKOKITA',
            'message' => 'Promo akhir pekan untuk Anda!',
            'recipient_total' => 400,
            'location_name' => 'Jakarta Selatan',
            'location_lat' => -6.2,
            'location_lng' => 106.8,
            'location_radius' => 1000,
            'delivery_start_date' => '2026-10-21',
            'delivery_end_date' => '2026-10-22',
            'delivery_start_time' => '06:00',
            'delivery_end_time' => '10:00',
            'control_numbers' => ['81234567890'],
        ], $overrides);
    }

    public static function packages(): array
    {
        return [['MYADS20K', 400, 7], ['MYADS25K', 800, 7], ['MYADS50K', 10000, 30]];
    }

    #[DataProvider('packages')]
    public function test_redeem_opens_sms_lba_setup_and_saves_a_dummy_campaign(string $code, ?int $recipients, int $days): void
    {
        $this->post('/login', ['code' => $code])->assertRedirect(route('dashboard'));
        $this->get('/dashboard')->assertOk()->assertSee('Setup campaign SMS LBA')
            ->assertSee('voucher-mobile-preview')->assertSee($days.' hari')
            ->assertDontSee('WA Business')->assertDontSee('SMS Targeted');

        $this->post('/voucher/campaign', $this->campaignData([
            'recipient_total' => $recipients,
            'campaign_type' => 'wa_targeted',
            'report_days' => 999,
        ]))->assertRedirect(route('dashboard'))
            ->assertSessionHas('voucher_campaign.campaign_type', 'sms_lba')
            ->assertSessionHas('voucher_campaign.voucher_code', $code)
            ->assertSessionHas('voucher_campaign.report_days', $days)
            ->assertSessionHas('voucher_campaign', fn (array $draft) => array_key_exists('recipient_total', $draft) && $draft['recipient_total'] === $recipients)
            ->assertSessionHas('voucher_campaign.status', 'draft');

        $this->get('/dashboard')->assertOk()->assertSee('Belum ada SMS yang dikirim.')
            ->assertSee('Promo akhir pekan untuk Anda!')->assertSee('Simpan perubahan campaign');
        Http::assertNothingSent();
    }

    public static function lockedRecipients(): array
    {
        return [
            ['MYADS20K', 401, 400], ['MYADS20K', 1, 400],
            ['MYADS25K', 801, 800], ['MYADS25K', 1, 800],
            ['MYADS50K', 10001, 10000], ['MYADS50K', 1, 10000],
        ];
    }

    #[DataProvider('lockedRecipients')]
    public function test_recipient_count_is_always_taken_from_the_voucher(string $code, int $submitted, ?int $expected): void
    {
        $this->post('/login', ['code' => $code]);
        $this->get('/dashboard')->assertSee('readonly', false)->assertDontSee('name="recipient_total"', false);
        $this->post('/voucher/campaign', $this->campaignData(['recipient_total' => $submitted, 'sms_quota' => null]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('voucher_campaign', fn (array $draft) => array_key_exists('recipient_total', $draft) && $draft['recipient_total'] === $expected);
        Http::assertNothingSent();
    }

    public function test_invalid_input_does_not_replace_an_existing_draft(): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData());
        $this->post('/voucher/campaign', $this->campaignData([
            'recipient_total' => 0, 'location_lat' => 91, 'location_lng' => 181,
            'location_radius' => 50001, 'message' => str_repeat('a', 161),
        ]))->assertSessionHasErrors(['location_lat', 'location_lng', 'location_radius', 'message'])
            ->assertSessionHas('voucher_campaign.message', 'Promo akhir pekan untuk Anda!');
    }

    public function test_schedule_is_validated_in_wib(): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData([
            'delivery_start_date' => '2026-10-20', 'delivery_start_time' => '13:59', 'delivery_end_time' => '18:00',
        ]))->assertSessionHasErrors('delivery_start_time')->assertSessionMissing('voucher_campaign');
        $this->post('/voucher/campaign', $this->campaignData([
            'delivery_start_date' => '2026-10-20', 'delivery_start_time' => '14:00', 'delivery_end_time' => '18:00',
        ]))->assertSessionHasNoErrors()->assertSessionHas('voucher_campaign.delivery_start_time', '14:00');
    }

    public static function invalidSchedules(): array
    {
        return [
            [['delivery_start_time' => '05:59'], 'delivery_start_time'],
            [['delivery_end_time' => '20:01'], 'delivery_end_time'],
            [['delivery_end_time' => '09:59'], 'delivery_end_time'],
            [['delivery_start_time' => '18:00', 'delivery_end_time' => '06:00'], 'delivery_end_time'],
            [['delivery_end_date' => '2026-10-20'], 'delivery_end_date'],
            [['delivery_start_date' => '2026-02-30'], 'delivery_start_date'],
            [['delivery_start_time' => '25:00'], 'delivery_start_time'],
            [['delivery_end_time' => ''], 'delivery_end_time'],
        ];
    }

    #[DataProvider('invalidSchedules')]
    public function test_invalid_schedule_ranges_are_rejected(array $overrides, string $field): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData($overrides))
            ->assertSessionHasErrors($field)->assertSessionMissing('voucher_campaign');
        Http::assertNothingSent();
    }

    public static function scheduleDefaultTimes(): array
    {
        return [
            ['2026-10-20 01:00:00', '2026-10-20', '06:00', '10:00'],
            ['2026-10-20 10:00:00', '2026-10-20', '14:00', '18:00'],
            ['2026-10-20 10:17:30', '2026-10-20', '15:00', '19:00'],
            ['2026-10-20 12:00:00', '2026-10-20', '16:00', '20:00'],
            ['2026-10-20 12:00:01', '2026-10-21', '06:00', '10:00'],
            ['2026-10-20 22:00:00', '2026-10-21', '06:00', '10:00'],
        ];
    }

    #[DataProvider('scheduleDefaultTimes')]
    public function test_default_window_respects_wib_daytime_and_minimum_lead_time(string $now, string $date, string $start, string $end): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse($now, 'Asia/Jakarta'));
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->get('/dashboard')->assertOk()->assertViewHas('scheduleDefaults', [
            'delivery_start_date' => $date, 'delivery_end_date' => $date,
            'delivery_start_time' => $start, 'delivery_end_time' => $end,
        ])->assertSee('min="06:00" max="20:00"', false)->assertSee('Nomor testing');
        $this->post('/voucher/campaign', $this->campaignData([
            'delivery_start_date' => $date, 'delivery_end_date' => $date,
            'delivery_start_time' => $start, 'delivery_end_time' => $end,
        ]))->assertSessionHasNoErrors();
    }

    public static function validPhoneNumbers(): array
    {
        return [['81234567890'], ['081234567890'], ['6281234567890'], ['+6281234567890']];
    }

    #[DataProvider('validPhoneNumbers')]
    public function test_testing_numbers_are_saved_in_normalized_format(string $number): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData(['control_numbers' => [$number]]))
            ->assertSessionHasNoErrors()->assertSessionHas('voucher_campaign.control_numbers', ['6281234567890']);
        $this->get('/dashboard')->assertSee('Nomor testing: 6281234567890')
            ->assertSee('id="testing-prefix" class="voucher-phone-prefix">62</span>', false)
            ->assertSee('value="81234567890"', false);
        Http::assertNothingSent();
    }

    public static function invalidTestingNumbers(): array
    {
        return [[''], ['12345'], ['08123abc7890'], ['+1281234567890']];
    }

    #[DataProvider('invalidTestingNumbers')]
    public function test_invalid_testing_numbers_are_rejected(string $number): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData(['control_numbers' => [$number]]))
            ->assertSessionHasErrors('control_numbers.0')->assertSessionMissing('voucher_campaign');
        Http::assertNothingSent();
    }

    public function test_multiple_testing_numbers_are_normalized_deduplicated_and_restored(): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData([
            'control_numbers' => ['81234567890', '081234567891', '+6281234567890'],
        ]))->assertSessionHasNoErrors()->assertSessionHas('voucher_campaign.control_numbers', [
            '6281234567890', '6281234567891',
        ]);
        $response = $this->get('/dashboard')->assertOk()->assertSee('Nomor testing: 6281234567890, 6281234567891')
            ->assertSee('value="81234567890"', false)->assertSee('value="81234567891"', false)
            ->assertSee('data-testing-number-label>6281234567891</span>', false)
            ->assertSee('Tambah nomor testing')->assertSee('voucher-testing-numbers.js');
        $this->assertSame(1, substr_count($response->getContent(), 'type="tel"'));
        Http::assertNothingSent();
    }

    public function test_any_invalid_testing_number_rejects_the_entire_draft(): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->from('/dashboard')->post('/voucher/campaign', $this->campaignData([
            'control_numbers' => ['81234567890', 'invalid'],
        ]))->assertSessionHasErrors('control_numbers.1')->assertSessionMissing('voucher_campaign');
        $this->get('/dashboard')->assertOk()->assertSee('value="81234567890"', false)
            ->assertSee('value="invalid"', false);
        Http::assertNothingSent();
    }

    public function test_at_least_one_testing_number_is_required(): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData(['control_numbers' => []]))
            ->assertSessionHasErrors('control_numbers')->assertSessionMissing('voucher_campaign');
    }

    public function test_successful_campaign_save_prompts_for_an_optional_email(): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->get('/dashboard')->assertDontSee('id="voucherReportEmailModal"', false);
        $this->post('/voucher/campaign', $this->campaignData())
            ->assertSessionHas('prompt_report_email', true)->assertSessionHas('voucher_campaign.status', 'draft');
        $this->get('/dashboard')->assertOk()->assertSee('Kirim report ke email?')->assertSee('Lewati')
            ->assertSee('data-auto-open="true"', false)->assertSee('Alamat email (opsional)');
        $this->get('/dashboard')->assertSee('data-auto-open="false"', false);
        Http::assertNothingSent();
    }

    public function test_email_can_be_skipped_without_losing_the_campaign(): void
    {
        Mail::fake();
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData());
        $this->post('/voucher/campaign/report-email', ['report_email' => ''])
            ->assertRedirect(route('dashboard'))->assertSessionHasNoErrors()
            ->assertSessionHas('voucher_campaign.campaign_name', 'Promo toko');
        $this->assertNull(session('voucher_campaign.report_email'));
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_report_email_is_saved_and_preserved_when_the_campaign_is_edited(): void
    {
        Mail::fake();
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData());
        $this->post('/voucher/campaign/report-email', ['report_email' => 'owner@example.test'])
            ->assertRedirect(route('dashboard'))->assertSessionHasNoErrors()
            ->assertSessionHas('voucher_campaign.report_email', 'owner@example.test');
        $this->post('/voucher/campaign', $this->campaignData(['campaign_name' => 'Promo baru']))
            ->assertSessionHas('voucher_campaign.report_email', 'owner@example.test')
            ->assertSessionHas('prompt_report_email', false);
        $this->get('/dashboard')->assertOk()->assertSee('Email report: owner@example.test')
            ->assertSee('Ubah email report')->assertSee('data-auto-open="false"', false);
        Mail::assertNothingSent();
        Http::assertNothingSent();
    }

    public function test_invalid_email_reopens_the_popup_without_changing_the_saved_campaign(): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData());
        $this->from('/dashboard')->post('/voucher/campaign/report-email', ['report_email' => 'invalid-email'])
            ->assertRedirect(route('dashboard'));
        $this->get('/dashboard')->assertOk()->assertSee('data-auto-open="true"', false)
            ->assertSee('Masukkan alamat email yang valid.')->assertSee('value="invalid-email"', false)
            ->assertSessionHas('voucher_campaign.campaign_name', 'Promo toko');
    }

    public function test_email_cannot_be_saved_without_a_campaign(): void
    {
        $this->post('/voucher/campaign/report-email', ['report_email' => 'owner@example.test'])
            ->assertRedirect(route('login'));
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign/report-email', ['report_email' => 'owner@example.test'])
            ->assertForbidden()->assertSessionMissing('voucher_campaign');
        Http::assertNothingSent();
    }

    public function test_existing_session_and_draft_refresh_to_the_latest_package_quota(): void
    {
        $this->post('/login', ['code' => 'MYADS50K']);
        $this->post('/voucher/campaign', $this->campaignData());
        $this->withSession(['voucher.sms_quota' => null, 'voucher_campaign.recipient_total' => null])
            ->get('/dashboard')->assertOk()->assertSee('10.000 pesan')
            ->assertSessionHas('voucher.sms_quota', 10000)
            ->assertSessionHas('voucher_campaign.recipient_total', 10000);
        Http::assertNothingSent();
    }

    public function test_map_popup_restores_the_saved_location_for_editing(): void
    {
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData());
        $response = $this->get('/dashboard')->assertOk()
            ->assertSee('Atur lokasi di peta')
            ->assertSee('id="campaignMapModal" hidden', false)
            ->assertSee('Gunakan Lokasi')
            ->assertSee('id="location_lat" name="location_lat" type="hidden" value="-6.2"', false)
            ->assertSee('id="location_lng" name="location_lng" type="hidden" value="106.8"', false)
            ->assertSee('voucher-location-map.js');
        $this->assertSame(1, substr_count($response->getContent(), 'name="location_radius"'));
        Http::assertNothingSent();
    }

    public function test_guest_cannot_save_a_campaign_and_logout_clears_the_draft(): void
    {
        $this->post('/voucher/campaign', $this->campaignData())->assertRedirect(route('login'));
        $this->post('/login', ['code' => 'MYADS20K']);
        $this->post('/voucher/campaign', $this->campaignData());
        $this->post('/logout')->assertSessionMissing('voucher_campaign');
        $this->post('/login', ['code' => 'MYADS25K'])->assertSessionMissing('voucher_campaign');
    }
}
