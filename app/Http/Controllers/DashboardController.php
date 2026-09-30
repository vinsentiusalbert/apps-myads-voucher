<?php

namespace App\Http\Controllers;

use App\Services\MyadsApiClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, MyadsApiClient $client): View
    {
        $gatewayToken = $this->resolveGatewayToken($request, $client);

        $balanceFormatted = 'Rp 0';
        $balanceExpiry = null;

        // Fetch up-to-date user details from database (including real name & local refund balance)
        $accessToken = (string) $request->session()->get('myads.access_token', '');
        if ($accessToken !== '') {
            try {
                $me = $client->getMe($accessToken);
                $apiUser = data_get($me, 'data.user', []);
                if (!empty($apiUser)) {
                    $request->session()->put('myads.user.name', data_get($apiUser, 'name'));
                    $refundBalance = data_get($apiUser, 'refund_balance', 0);
                    $balanceFormatted = 'Rp ' . number_format((float) $refundBalance, 0, ',', '.');
                }
            } catch (\Throwable) {
                // keep default/cached name & Rp 0 balance
            }
        }

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
            $refreshGatewayToken = $this->gatewayRefreshCallback($request, $client);

            try {
                $balanceResp = $client->getBalance($gatewayToken, $refreshGatewayToken);

                $rawExpiry = data_get($balanceResp, 'data.data.expiredBalance');
                if ($rawExpiry && is_numeric($rawExpiry)) {
                    // Convert milliseconds to seconds and format date
                    $balanceExpiry = date('d M Y', (int)($rawExpiry / 1000));
                }
            } catch (\Throwable) {
                // keep default values
            }

            try {
                $listResp = $client->campaignList($gatewayToken, ['campaignId' => null], $refreshGatewayToken);

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
                $campaignPrices = $this->campaignPrices($client, $gatewayToken, $refreshGatewayToken);
            } catch (\Throwable) {
                // optional
            }
        }

        $membershipStatus = $this->fetchMembershipStatus($request, $client);

        // Simpan ke session agar bisa diakses dari sidebar di semua halaman
        $request->session()->put('myads.membership', $membershipStatus);

        return view('dashboard', [
            'balanceFormatted' => $balanceFormatted,
            'balanceExpiry' => $balanceExpiry,
            'campaignTotal' => $campaignTotal,
            'campaignSmsTotal' => $campaignSmsTotal,
            'campaignWaTotal' => $campaignWaTotal,
            'campaignPrices' => $campaignPrices,
            'membership' => $membershipStatus,
        ]);
    }

    private function resolveGatewayToken(Request $request, MyadsApiClient $client): string
    {
        $gatewayToken = (string) $request->session()->get('myads.gw_token', '');
        if (trim($gatewayToken) !== '') {
            return $gatewayToken;
        }

        try {
            return ($this->gatewayRefreshCallback($request, $client))();
        } catch (\Throwable) {
            return '';
        }
    }

    private function fetchMembershipStatus(Request $request, MyadsApiClient $client): array
    {
        $defaults = [
            'is_member' => false,
            'membership' => null,
            'price' => 25000,
            'tax_percentage' => 11.00,
            'tax_amount' => 2750,
            'total_price' => 27750,
            'quota' => 5000,
            'max_per_campaign' => 5000,
        ];

        $accessToken = (string) $request->session()->get('myads.access_token', '');
        if ($accessToken === '') {
            return $defaults;
        }

        try {
            $resp = $client->getMembershipStatus($accessToken);
            if (data_get($resp, 'success') !== true) {
                return $defaults;
            }
            $data = (array) data_get($resp, 'data', []);
            $price = max(0, (float) ($data['price'] ?? $defaults['price']));
            $tax = max(0, (float) ($data['tax_percentage'] ?? $defaults['tax_percentage']));
            $taxAmount = max(0, (float) ($data['tax_amount'] ?? round($price * $tax / 100)));

            return array_merge($defaults, $data, [
                'is_member' => filter_var($data['is_member'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'price' => $price,
                'tax_percentage' => $tax,
                'tax_amount' => $taxAmount,
                'total_price' => max(0, (float) ($data['total_price'] ?? ($price + $taxAmount))),
            ]);
        } catch (\Throwable) {
            return $defaults;
        }
    }

    private function campaignPrices(MyadsApiClient $client, string $gatewayToken, ?callable $refreshGatewayToken = null): array
    {
        $responses = [];

        foreach (['ALL', 'SMS', 'WABA'] as $campaignType) {
            try {
                $responses[] = $client->campaignPriceView($gatewayToken, ['campaignType' => $campaignType], $refreshGatewayToken);
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

    private function gatewayRefreshCallback(Request $request, MyadsApiClient $client): callable
    {
        return function () use ($request, $client): string {
            $gwResp = $client->getGatewayToken((string) $request->session()->get('myads.access_token', ''));
            $gwToken = $client->extractGatewayToken($gwResp);

            if ($gwToken === '') {
                throw new \RuntimeException('Gagal ambil token gateway (format response tidak sesuai).');
            }

            $request->session()->put('myads.gw_token', $gwToken);

            return $gwToken;
        };
    }
}
