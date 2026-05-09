<?php

namespace App\Http\Controllers;

use App\Services\MyadsApiClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, MyadsApiClient $client): View
    {
        $gatewayToken = (string) $request->session()->get('myads.gw_token', '');

        $balanceFormatted = '-';
        $balanceExpiry = null;
        $campaignTotal = null;
        $campaignSmsTotal = null;
        $campaignWaTotal = null;
        $campaignPrices = [
            'sms_lba' => null,
            'sms_targeted' => null,
            'wa_lba' => null,
            'wa_targeted' => null,
        ];

        if ($gatewayToken !== '') {
            try {
                $balanceResp = $client->getBalance($gatewayToken);

                $amount = data_get($balanceResp, 'data.data.balanceMain')
                    ?? data_get($balanceResp, 'data.data.mainBalance')
                    ?? data_get($balanceResp, 'data.data.amount');

                $rawExpiry = data_get($balanceResp, 'data.data.expiredBalance');
                if ($rawExpiry && is_numeric($rawExpiry)) {
                    // Convert milliseconds to seconds and format date
                    $balanceExpiry = date('d M Y', (int)($rawExpiry / 1000));
                }

                if (is_numeric($amount)) {
                    $balanceFormatted = 'Rp ' . number_format((float) $amount, 0, ',', '.');
                } elseif (is_string($amount) && trim($amount) !== '') {
                    $balanceFormatted = $amount;
                }
            } catch (\Throwable) {
                // keep default values
            }

            try {
                $listResp = $client->campaignList($gatewayToken, ['campaignId' => null]);

                $items = $this->extractCampaignItems($listResp);

                if (is_array($items)) {
                    $campaignTotal = count($items);

                    $campaignSmsTotal = count(array_filter($items, function ($row) {
                        return $this->isSmsCampaign($row);
                    }));

                    $campaignWaTotal = count(array_filter($items, function ($row) {
                        return $this->isWaCampaign($row);
                    }));
                }
            } catch (\Throwable) {
                // optional
            }

            try {
                $campaignPrices = $this->campaignPrices($client, $gatewayToken);
            } catch (\Throwable) {
                // optional
            }
        }

        return view('dashboard', [
            'balanceFormatted' => $balanceFormatted,
            'balanceExpiry' => $balanceExpiry,
            'campaignTotal' => $campaignTotal,
            'campaignSmsTotal' => $campaignSmsTotal,
            'campaignWaTotal' => $campaignWaTotal,
            'campaignPrices' => $campaignPrices,
        ]);
    }

    private function campaignPrices(MyadsApiClient $client, string $gatewayToken): array
    {
        $responses = [];

        foreach (['ALL', 'SMS', 'WABA'] as $campaignType) {
            try {
                $responses[] = $client->campaignPriceView($gatewayToken, ['campaignType' => $campaignType]);
            } catch (\Throwable) {
                // Keep trying the other campaign types.
            }
        }

        $priceData = [];
        foreach ($responses as $response) {
            $data = data_get($response, 'data.data') ?? data_get($response, 'data') ?? [];
            if (is_array($data)) {
                $priceData = array_merge($priceData, $this->flattenPriceData($data));
            }
        }

        return [
            'sms_lba' => $this->findPriceValue($priceData, [
                'LBA_SMS',
                'SMS_LBA',
                'SMS_LOCATION_BASED_AREA',
                'LOCATION_BASED_AREA_SMS',
            ]),
            'sms_targeted' => $this->findPriceValue($priceData, [
                'TARGETED_SMS',
                'SMS_TARGETED',
            ]),
            'wa_lba' => $this->findPriceValue($priceData, [
                'WABA_LBA',
                'WA_LBA',
                'WHATSAPP_LBA',
                'LBA_WABA',
            ]),
            'wa_targeted' => $this->findPriceValue($priceData, [
                'WABA_TARGETED',
                'WA_TARGETED',
                'WHATSAPP_TARGETED',
                'TARGETED_WABA',
            ]),
        ];
    }

    private function flattenPriceData(array $data, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $flatKey = $prefix === '' ? (string) $key : $prefix . '_' . (string) $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenPriceData($value, $flatKey));
                continue;
            }

            $result[strtoupper($flatKey)] = $value;
        }

        return $result;
    }

    private function findPriceValue(array $priceData, array $keys): ?float
    {
        foreach ($keys as $key) {
            $value = $priceData[strtoupper($key)] ?? null;
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        foreach ($priceData as $key => $value) {
            if (! is_numeric($value)) {
                continue;
            }

            foreach ($keys as $candidate) {
                $candidate = strtoupper($candidate);
                if (str_contains((string) $key, $candidate)) {
                    return (float) $value;
                }
            }
        }

        return null;
    }

    private function extractCampaignItems(array $response): array
    {
        $items = data_get($response, 'data.data.list')
            ?? data_get($response, 'data.data.campaigns')
            ?? data_get($response, 'data.data.items')
            ?? data_get($response, 'data.data')
            ?? data_get($response, 'data.list')
            ?? data_get($response, 'data.campaigns')
            ?? data_get($response, 'data.items')
            ?? [];

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, static fn ($row): bool => is_array($row)));
    }

    private function campaignChannel(array $row): string
    {
        return strtoupper(trim((string) (
            data_get($row, 'inventoryType')
            ?? data_get($row, 'inventory_type')
            ?? data_get($row, 'channel')
            ?? data_get($row, 'channelType')
            ?? data_get($row, 'channel_type')
            ?? data_get($row, 'campaignChannel')
            ?? ''
        )));
    }

    private function isSmsCampaign(array $row): bool
    {
        return in_array($this->campaignChannel($row), ['SMS', 'MMS'], true);
    }

    private function isWaCampaign(array $row): bool
    {
        return in_array($this->campaignChannel($row), ['WABA', 'WA', 'WA-BUSINESS', 'WA BUSINESS', 'WHATSAPP'], true);
    }
}
