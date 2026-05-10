<?php

namespace Tests\Feature;

use App\Http\Controllers\CampaignMenuController;
use App\Models\User;
use App\Services\MyadsApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class CampaignSmsReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sms_campaign_report_forwards_campaign_id_to_api_gateway(): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('campaignReport')
            ->once()
            ->with('test-gw-token', ['campaignId' => 2171], Mockery::type('callable'))
            ->andReturn([
                'success' => true,
                'data' => [
                    'code' => 0,
                    'desc' => 'Success',
                    'data' => [
                        'successCount' => 2000,
                        'failedCount' => 3,
                    ],
                ],
            ]);
        $this->app->instance(MyadsApiClient::class, $client);

        $response = $this->actingAs(User::factory()->create())
            ->withSession(['myads.gw_token' => 'test-gw-token'])
            ->get('/campaign/sms/report?campaignId=2171');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.data.successCount', 2000);
    }

    public function test_sms_lba_report_page_uses_selected_campaign_id_from_index_link(): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('campaignList')
            ->once()
            ->with('test-gw-token', ['campaignId' => null], Mockery::type('callable'))
            ->andReturn([
                'success' => true,
                'data' => [
                    'code' => 0,
                    'desc' => 'Success',
                    'data' => [
                        'list' => [
                            [
                                'id' => 2171,
                                'campaignName' => 'SMS LBA Report',
                                'startDate' => '2026-05-10',
                                'inventoryType' => 'SMS',
                                'campaignType' => 'LBA',
                                'operator' => 'Telkomsel',
                                'status' => 'SENT',
                                'reason' => '',
                                'bucketMain' => 150000,
                            ],
                        ],
                    ],
                ],
            ]);
        $client->shouldReceive('campaignReport')
            ->once()
            ->with('test-gw-token', ['campaignId' => 2171], Mockery::type('callable'))
            ->andReturn([
                'success' => true,
                'data' => [
                    'code' => 0,
                    'desc' => 'Success',
                    'data' => [
                        'senderName' => 'MyAds',
                        'message' => 'Pesan dari Report View',
                        'deliveries' => [
                            [
                                'deliveryStartDate' => '20260510',
                                'deliveryStartTime' => '0800',
                                'deliveryEndDate' => '20260510',
                                'deliveryEndTime' => '1200',
                            ],
                        ],
                        'successCount' => 2000,
                        'failedCount' => 3,
                    ],
                ],
            ]);
        $this->app->instance(MyadsApiClient::class, $client);

        $request = Request::create('/campaign/sms/location-based-area', 'GET', [
            'view' => 'show',
            'id' => '2171',
        ]);
        $session = $this->app['session']->driver();
        $session->put('myads.gw_token', 'test-gw-token');
        $request->setLaravelSession($session);

        $view = app(CampaignMenuController::class)->show($request, 'sms', 'location-based-area');

        $this->assertSame('campaign-lba.show', $view->name());
        $this->assertSame('SMS LBA Report', data_get($view->getData(), 'campaignRow.title'));
        $this->assertSame('MyAds', data_get($view->getData(), 'campaignReport.data.data.senderName'));
        $this->assertSame('Pesan dari Report View', data_get($view->getData(), 'campaignReport.data.data.message'));
        $this->assertSame('20260510', data_get($view->getData(), 'campaignReport.data.data.deliveries.0.deliveryStartDate'));
        $this->assertSame(2000, data_get($view->getData(), 'campaignReport.data.data.successCount'));
        $this->assertSame(3, data_get($view->getData(), 'campaignReport.data.data.failedCount'));
    }

    public function test_sms_targeted_show_uses_report_view_and_selected_campaign_id(): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('campaignList')
            ->once()
            ->with('test-gw-token', ['campaignId' => null], Mockery::type('callable'))
            ->andReturn([
                'success' => true,
                'data' => [
                    'code' => 0,
                    'desc' => 'Success',
                    'data' => [
                        'list' => [
                            [
                                'id' => 3001,
                                'campaignName' => 'SMS Targeted Report',
                                'startDate' => '2026-05-10',
                                'inventoryType' => 'SMS',
                                'campaignType' => 'TARGETED',
                                'operator' => 'Telkomsel',
                                'status' => 'SENT',
                                'bucketMain' => 250000,
                            ],
                        ],
                    ],
                ],
            ]);
        $client->shouldReceive('campaignReport')
            ->once()
            ->with('test-gw-token', ['campaignId' => 3001], Mockery::type('callable'))
            ->andReturn([
                'success' => true,
                'data' => [
                    'code' => 0,
                    'desc' => 'Success',
                    'data' => [
                        'senderName' => 'MyAds',
                        'message' => 'Pesan targeted dari Report View',
                        'deliveries' => [
                            [
                                'deliveryDate' => '20260510',
                                'deliveryTime' => '1000',
                            ],
                        ],
                        'successCount' => 1200,
                        'failedCount' => 2,
                    ],
                ],
            ]);
        $this->app->instance(MyadsApiClient::class, $client);

        $request = Request::create('/campaign/sms/targeted', 'GET', [
            'view' => 'show',
            'id' => '3001',
        ]);
        $session = $this->app['session']->driver();
        $session->put('myads.gw_token', 'test-gw-token');
        $request->setLaravelSession($session);

        $view = app(CampaignMenuController::class)->show($request, 'sms', 'targeted');

        $this->assertSame('campaign-targeted.show', $view->name());
        $this->assertSame('SMS Targeted Report', data_get($view->getData(), 'campaignRow.title'));
        $this->assertSame('MyAds', data_get($view->getData(), 'campaignReport.data.data.senderName'));
        $this->assertSame('Pesan targeted dari Report View', data_get($view->getData(), 'campaignReport.data.data.message'));
        $this->assertSame('20260510', data_get($view->getData(), 'campaignReport.data.data.deliveries.0.deliveryDate'));
        $this->assertSame(1200, data_get($view->getData(), 'campaignReport.data.data.successCount'));
        $this->assertSame(2, data_get($view->getData(), 'campaignReport.data.data.failedCount'));
    }

    public function test_sms_show_page_falls_back_when_campaign_list_is_empty(): void
    {
        $client = Mockery::mock(MyadsApiClient::class);
        $client->shouldReceive('campaignList')
            ->once()
            ->with('test-gw-token', ['campaignId' => null], Mockery::type('callable'))
            ->andReturn([
                'success' => true,
                'data' => [
                    'code' => 0,
                    'desc' => 'Success',
                    'data' => [
                        'list' => [],
                    ],
                ],
            ]);
        $client->shouldReceive('campaignReport')
            ->once()
            ->with('test-gw-token', ['campaignId' => 9999], Mockery::type('callable'))
            ->andReturn([
                'success' => true,
                'data' => [
                    'code' => 0,
                    'desc' => 'Success',
                    'data' => [],
                ],
            ]);
        $this->app->instance(MyadsApiClient::class, $client);

        $request = Request::create('/campaign/sms/location-based-area', 'GET', [
            'view' => 'show',
            'id' => '9999',
        ]);
        $session = $this->app['session']->driver();
        $session->put('myads.gw_token', 'test-gw-token');
        $request->setLaravelSession($session);

        $view = app(CampaignMenuController::class)->show($request, 'sms', 'location-based-area');

        $this->assertSame('campaign-lba.show', $view->name());
        $this->assertSame('9999', data_get($view->getData(), 'campaignRow.id'));
    }

    public function test_api_client_refreshes_gateway_token_when_proxy_cache_is_missing(): void
    {
        Http::fakeSequence()
            ->push([
                'success' => false,
                'message' => "Can't get authorization data from in-memory cache data server",
            ], 200)
            ->push([
                'success' => true,
                'data' => [
                    'data' => [
                        'token' => 'fresh-gw-token',
                    ],
                ],
            ], 200)
            ->push([
                'success' => true,
                'data' => [
                    'code' => 0,
                    'desc' => 'Success',
                    'data' => [
                        'successCount' => 7,
                    ],
                ],
            ], 200);

        $client = new MyadsApiClient('http://example.test');
        $freshToken = null;

        $response = $client->campaignReport('stale-gw-token', ['campaignId' => 2171], function () use ($client, &$freshToken): string {
            $freshToken = $client->extractGatewayToken($client->getGatewayToken());

            return $freshToken;
        });

        $this->assertSame('fresh-gw-token', $freshToken);
        $this->assertSame(7, data_get($response, 'data.data.successCount'));

        Http::assertSentCount(3);
        Http::assertSent(function ($request): bool {
            return (string) $request->url() === 'http://example.test/api/gw//scrt/myads/api/v1/report/view'
                && $request['campaignId'] === 2171;
        });
    }

    public function test_api_client_campaign_list_keeps_index_proxy_endpoint(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => [
                    'code' => 0,
                    'desc' => 'Success',
                    'data' => [
                        'list' => [],
                    ],
                ],
            ]),
        ]);

        $client = new MyadsApiClient('http://example.test');
        $client->campaignList('test-gw-token', ['campaignId' => null]);

        Http::assertSent(function ($request): bool {
            return (string) $request->url() === 'http://example.test/api/gw/list'
                && $request['campaignId'] === null;
        });
    }

}
