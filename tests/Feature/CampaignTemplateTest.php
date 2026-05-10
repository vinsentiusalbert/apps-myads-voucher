<?php

namespace Tests\Feature;

use App\Models\CampaignTemplate;
use App\Models\User;
use App\Services\MyadsApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CampaignTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_template_list_reads_from_database(): void
    {
        CampaignTemplate::create([
            'name' => 'welcome_offer_template',
            'template_type' => 'simple_message',
            'category' => 'Single Banner',
            'language' => 'Indonesia',
            'header_type' => 'none',
            'body' => 'Promo khusus pelanggan baru.',
            'status' => 'APPROVED',
        ]);

        $response = $this->actingAs($this->user())->get('/campaign/wa-business/campaign-template');

        $response->assertOk();
        $response->assertSee('Template Message');
        $response->assertSee('welcome_offer_template');
        $response->assertSee('Promo khusus pelanggan baru.');
    }

    public function test_authenticated_user_can_create_campaign_template(): void
    {
        config([
            'myads.waba_account_id' => '3735503976689341',
            'myads.waba_phone_number_id' => '337795902761255',
        ]);

        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('wabaTemplateAdd')
            ->once()
            ->with('test-gw-token', [
                'templateName' => 'campaign_whatsapp_test',
                'language' => 'INDONESIA',
                'headerType' => 'TEXT',
                'headerLabel' => '',
                'headerPathFile' => '',
                'bodyMessage' => 'Halo pelanggan, nikmati promo hari ini.',
                'bodyType' => 'STATIC',
                'bodyPathFile' => '',
                'useButton' => 0,
                'buttons' => [],
                'wabaAccountId' => 3735503976689341,
                'wabaButtonId' => '',
                'phoneNumberId' => 337795902761255,
            ], Mockery::type('callable'))
            ->andReturn([
                'success' => true,
                'data' => [
                    'code' => 0,
                    'desc' => 'Success',
                    'data' => [
                        'templateId' => 'TEMP001',
                        'status' => 'PENDING',
                    ],
                ],
            ]);
        $this->app->instance(MyadsApiClient::class, $client);

        $response = $this->actingAs($this->user())
            ->withSession(['myads.gw_token' => 'test-gw-token'])
            ->post('/campaign/wa-business/campaign-template', [
                'name' => 'campaign_whatsapp_test',
                'template_type' => 'simple_message',
                'category' => 'Single Banner',
                'language' => 'Indonesia',
                'header_type' => 'none',
                'body' => 'Halo pelanggan, nikmati promo hari ini.',
                'footer' => 'MyAds',
            ]);

        $response->assertRedirect('/campaign/wa-business/campaign-template');

        $this->assertDatabaseHas('campaign_templates', [
            'gw_template_id' => 'TEMP001',
            'name' => 'campaign_whatsapp_test',
            'category' => 'Single Banner',
            'language' => 'Indonesia',
            'status' => 'PENDING',
        ]);
    }

    public function test_authenticated_user_can_read_campaign_template_item(): void
    {
        $template = CampaignTemplate::create([
            'name' => 'read_only_template',
            'template_type' => 'simple_message',
            'category' => 'Single Banner',
            'language' => 'Indonesia',
            'header_type' => 'none',
            'body' => 'Konten template hanya untuk dibaca.',
            'footer' => 'MyAds',
            'status' => 'APPROVED',
        ]);

        $response = $this->actingAs($this->user())->get("/campaign/wa-business/campaign-template/{$template->id}");

        $response->assertOk();
        $response->assertSee('Read Campaign Template');
        $response->assertSee('read_only_template');
        $response->assertSee('Konten template hanya untuk dibaca.');
        $response->assertSee('readonly', false);
        $response->assertSee('disabled', false);
        $response->assertDontSee('Save Template');
    }

    private function user(): User
    {
        return User::factory()->create();
    }
}
