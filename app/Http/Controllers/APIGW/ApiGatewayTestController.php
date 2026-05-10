<?php

namespace App\Http\Controllers\APIGW;

use App\Http\Controllers\Controller;
use App\Services\MyadsApiClient;
use Illuminate\Http\Request;
use Throwable;

class ApiGatewayTestController extends Controller
{
    public function index()
    {
        return view('apigw.test');
    }

    public function action(Request $request, MyadsApiClient $client)
    {
        $action = $request->action;

        try {

            $gwToken = session('myads.gw_token');

            if (!$gwToken) {
                throw new \RuntimeException('GW Token tidak ditemukan');
            }

            $result = [];
            $refreshGatewayToken = function () use ($request, $client): string {
                $gwResp = $client->getGatewayToken();
                $freshToken = $client->extractGatewayToken($gwResp);

                if ($freshToken === '') {
                    throw new \RuntimeException('Gagal ambil token gateway (format response tidak sesuai).');
                }

                $request->session()->put('myads.gw_token', $freshToken);

                return $freshToken;
            };

            switch ($action) {

                case 'balance':
                    $result = $client->getBalance($gwToken, $refreshGatewayToken);
                    break;

                case 'campaign_list':
                    $result = $client->campaignList($gwToken, [
                        'campaignId' => null
                    ], $refreshGatewayToken);
                    break;

                case 'price_view':
                    $result = $client->campaignPriceView($gwToken, [
                        'campaignType' => 'ALL'
                    ], $refreshGatewayToken);
                    break;

                case 'profile_dropdown':
                    $result = $client->profileDropdownList($gwToken, [
                        'profile' => 'ALL'
                    ], $refreshGatewayToken);
                    break;

                case 'targeted_add':

                    $payload = [
                        'channel' => 'SMS',
                        'campaignName' => 'UI Test Targeted',
                        'message' => 'Testing dari Laravel UI',
                        'senderName' => 'MyAds',
                        'recepientTotal' => 10,
                        'deliveries' => [
                            [
                                'deliveryDate' => now()->format('Ymd'),
                                'deliveryTime' => '1000',
                            ]
                        ],
                        'controlNumbers' => [
                            '6282381600327'
                        ]
                    ];

                    $result = $client->campaignTargetedAdd($gwToken, $payload, $refreshGatewayToken);

                    break;

                case 'lba_add':

                    $payload = [
                        'channel' => 'SMS',
                        'campaignName' => 'UI Test LBA',
                        'message' => 'Testing LBA Laravel',
                        'senderName' => 'MyAds',
                        'recepientTotal' => 10,
                        'deliveries' => [
                            [
                                'deliveryStartDate' => now()->format('Ymd'),
                                'deliveryStartTime' => '0800',
                                'deliveryEndDate' => now()->addDay()->format('Ymd'),
                                'deliveryEndTime' => '1300',
                            ]
                        ],
                        'controlNumbers' => [
                            '6282381600326'
                        ],
                        'contents' => [
                            'contentName' => 'test',
                            'message' => 'test lba',
                            'locations' => [
                                'address' => 'Jakarta',
                                'latitude' => -6.2,
                                'longitude' => 106.8,
                                'locationNameMap' => 'Jakarta',
                                'locationNameUser' => 'Jakarta',
                                'radius' => 500,
                                'locationType' => 'ALL',
                            ]
                        ]
                    ];

                    $result = $client->campaignLbaAdd($gwToken, $payload, $refreshGatewayToken);

                    break;

                case 'upload':

                    $request->validate([
                        'file' => ['required', 'file']
                    ]);

                    $result = $client->uploadFile(
                        $gwToken,
                        $request->file('file'),
                        'FILE',
                        $refreshGatewayToken
                    );

                    break;

                default:
                    throw new \RuntimeException('Action tidak dikenali');
            }

            return back()->with([
                'success' => true,
                'result' => json_encode($result, JSON_PRETTY_PRINT),
            ]);

        } catch (Throwable $e) {

            return back()->with([
                'success' => false,
                'result' => $e->getMessage(),
            ]);
        }
    }
}
