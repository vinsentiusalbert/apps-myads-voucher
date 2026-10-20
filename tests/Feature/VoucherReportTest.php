<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VoucherReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-20 10:00:00', 'Asia/Jakarta'));
    }

    public function test_report_coupon_opens_a_completed_campaign_report_without_setup(): void
    {
        $this->post('/login', ['code' => ' rpt20k01 '])->assertRedirect(route('dashboard'))
            ->assertSessionHas('voucher_campaign.status', 'completed')
            ->assertSessionHas('voucher_report.sent', 400)
            ->assertSessionMissing('myads.access_token');

        $this->get('/dashboard')->assertOk()->assertViewIs('voucher.report')
            ->assertSee('RPT20K01')->assertSee('Report SMS LBA')->assertSee('Campaign selesai')
            ->assertSee('Data dummy')->assertSee('368')->assertSee('32')->assertSee('92%')
            ->assertDontSee('Pengiriman per hari')->assertSee('25/10/2026')
            ->assertSee('voucher-mobile-preview')->assertDontSee('Simpan setup campaign')
            ->assertDontSee('voucherCampaignForm');
        Http::assertNothingSent();
    }

    public function test_daily_report_totals_match_the_summary_and_voucher(): void
    {
        $this->post('/login', ['code' => 'RPT20K01']);
        $report = session('voucher_report');
        foreach (['sent', 'delivered', 'failed'] as $metric) {
            $this->assertSame($report[$metric], array_sum(array_column($report['daily'], $metric)));
        }
        $this->assertSame(session('voucher.sms_quota'), $report['sent']);
        $this->assertSame($report['sent'], $report['delivered'] + $report['failed']);
    }

    public function test_existing_demo_report_refreshes_when_the_package_quota_changes(): void
    {
        $this->post('/login', ['code' => 'RPT20K01']);
        $this->withSession(['voucher.sms_quota' => 200, 'voucher_report.sent' => 200])
            ->get('/dashboard')->assertOk()->assertSessionHas('voucher_report.sent', 400)
            ->assertSessionHas('voucher_report.delivered', 368)
            ->assertSessionHas('voucher_campaign.recipient_total', 400);
        Http::assertNothingSent();
    }

    public function test_completed_campaign_cannot_be_overwritten_through_setup_endpoint(): void
    {
        $this->post('/login', ['code' => 'RPT20K01']);
        $this->post('/voucher/campaign', ['campaign_name' => 'Changed'])
            ->assertForbidden()->assertSessionHas('voucher_campaign.campaign_name', 'Promo Akhir Pekan Toko Kita')
            ->assertSessionHas('voucher_campaign.status', 'completed');
        $this->getJson('/campaign/sms/report')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_logout_removes_the_report_and_setup_coupon_starts_without_one(): void
    {
        $this->post('/login', ['code' => 'RPT20K01']);
        $this->post('/logout')->assertSessionMissing('voucher_report')->assertSessionMissing('voucher_campaign');
        $this->post('/login', ['code' => 'MYADS20K'])->assertSessionMissing('voucher_report');
        $this->get('/dashboard')->assertViewIs('voucher.dashboard')->assertSee('Setup campaign SMS LBA');
        Http::assertNothingSent();
    }
}
