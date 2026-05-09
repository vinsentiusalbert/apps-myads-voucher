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

                $items = data_get($listResp, 'data.data.campaigns')
                    ?? data_get($listResp, 'data.data.items')
                    ?? data_get($listResp, 'data.data')
                    ?? [];

                if (is_array($items)) {
                    $campaignTotal = count($items);

                    $campaignSmsTotal = count(array_filter($items, function ($row) {
                        $channel = strtoupper((string) data_get($row, 'channel', ''));
                        return $channel === 'SMS';
                    }));

                    $campaignWaTotal = count(array_filter($items, function ($row) {
                        $channel = strtoupper((string) data_get($row, 'channel', ''));
                        return $channel === 'WABA' || $channel === 'WA' || $channel === 'WA-BUSINESS' || $channel === 'WHATSAPP';
                    }));
                }
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
        ]);
    }
}
