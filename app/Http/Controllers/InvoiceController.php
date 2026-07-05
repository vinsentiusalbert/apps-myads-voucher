<?php

namespace App\Http\Controllers;

use App\Services\MyadsApiClient;
use Illuminate\Http\Request;
use Throwable;

class InvoiceController extends Controller
{
    private function ensureValidAccessToken(Request $request, MyadsApiClient $client): string
    {
        $accessToken = (string) $request->session()->get('myads.access_token', '');
        if ($accessToken === '') {
            abort(401, 'Unauthorized - No Access Token found');
        }

        // Refresh token if needed
        $expiresAt = $request->session()->get('myads.token_expires_at');
        if ($expiresAt && time() >= $expiresAt) {
            $refreshToken = (string) $request->session()->get('myads.refresh_token', '');
            if ($refreshToken !== '') {
                try {
                    $refreshResult = $client->refreshAccessToken($refreshToken);
                    $newAccessToken = (string) ($refreshResult['data']['access_token'] ?? '');
                    if ($newAccessToken !== '') {
                        $request->session()->put('myads.access_token', $newAccessToken);
                        $expiresIn = (int) ($refreshResult['data']['expires_in'] ?? 3600);
                        $request->session()->put('myads.token_expires_at', time() + $expiresIn - 60);
                        $accessToken = $newAccessToken;
                    }
                } catch (Throwable) {
                    // Ignore, standard fallback
                }
            }
        }

        return $accessToken;
    }

    public function show(Request $request, MyadsApiClient $client, string $invoiceId)
    {
        try {
            $accessToken = $this->ensureValidAccessToken($request, $client);
            $invoiceDetails = $client->getInvoiceDetails($accessToken, $invoiceId);

            $data = $invoiceDetails['data'] ?? [];
            if (empty($data)) {
                abort(404, 'Data Invoice tidak ditemukan.');
            }

            return view('invoice.show', [
                'invoice' => $data['invoice'] ?? [],
                'transaction' => $data['transaction'] ?? [],
                'campaign' => $data['campaign'] ?? null,
                'invoiceId' => $invoiceId,
            ]);
        } catch (Throwable $e) {
            return response()->view('errors.404', ['message' => $e->getMessage()], 404);
        }
    }

    public function downloadPdf(Request $request, MyadsApiClient $client, string $invoiceId)
    {
        try {
            $accessToken = $this->ensureValidAccessToken($request, $client);
            $pdfResponse = $client->getInvoicePdf($accessToken, $invoiceId);

            if ($pdfResponse->failed()) {
                abort($pdfResponse->status(), 'Gagal mengunduh PDF Invoice dari server API.');
            }

            return response($pdfResponse->body())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="invoice-' . $invoiceId . '.pdf"');
        } catch (Throwable $e) {
            abort(500, 'Terjadi kesalahan saat mengunduh PDF: ' . $e->getMessage());
        }
    }
}
