<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MyadsApiClient
{
    public function __construct(
        private string $baseUrl = 'http://api.goto-myads.com'
    ) {
        $this->baseUrl = $this->baseUrl !== '' ? $this->baseUrl : (string) config('myads.api_base_url');
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    private function bearerClient(string $accessToken): PendingRequest
    {
        $accessToken = trim($accessToken);
        if ($accessToken == '') {
            throw new RuntimeException('Token akses tidak ditemukan. Silakan login ulang.');
        }

        return $this->client()->withToken($accessToken);
    }

    /**
     * API gateway wrapper shape:
     * - Outer (proxy) : { success: bool, data?: mixed, error?: mixed, message?: string }
     * - Inner (upstream) usually: { code: int, desc: string, data: mixed }
     *
     * We treat `success !== true` OR `inner.code != 0` as failure.
     */
    private function assertGatewayOk(array $json, string $fallback): array
    {
        $success = $json['success'] ?? null;
        if ($success !== null && $success !== true) {
            throw new RuntimeException($this->extractGatewayMessage($json, $fallback));
        }

        $inner = $json['data'] ?? null;
        if (is_array($inner)) {
            $innerSuccess = $inner['success'] ?? null;
            if ($innerSuccess !== null && $innerSuccess !== true) {
                throw new RuntimeException($this->extractGatewayMessage($inner, $fallback));
            }

            $code = $inner['code'] ?? null;
            if (is_numeric($code) && (int) $code !== 0) {
                $desc = $inner['desc'] ?? null;
                $message = is_string($desc) && trim($desc) !== '' ? $desc : $fallback;
                throw new RuntimeException($message);
            }
        }

        return $json;
    }

    private function extractGatewayMessage(array $json, string $fallback): string
    {
        $msg = $json['message'] ?? $json['error'] ?? null;
        if (is_string($msg) && trim($msg) !== '') {
            return $msg;
        }

        $desc = data_get($json, 'desc')
            ?? data_get($json, 'error.desc')
            ?? data_get($json, 'data.desc')
            ?? null;
        if (is_string($desc) && trim($desc) !== '') {
            return $desc;
        }

        return $fallback;
    }

    /**
     * @return array{success:bool, message?:string, data?:array}
     */
    public function login(string $email, string $password): array
    {
        try {
            $response = $this->client()->post('/api/auth/login', [
                'email' => $email,
                'password' => $password,
            ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak bisa konek ke API login: ' . $e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RuntimeException($this->extractErrorMessage($response, 'Login gagal'));
        }

        return (array) $response->json();
    }

    /**
     * @return array{success:bool, message?:string, data?:array}
     */
    public function register(string $email, string $password, string $name): array
    {
        try {
            $response = $this->client()->post('/api/auth/register', [
                'email' => $email,
                'password' => $password,
                'name' => $name,
            ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak bisa konek ke API register: ' . $e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RuntimeException($this->extractErrorMessage($response, 'Register gagal'));
        }

        return (array) $response->json();
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function getGatewayToken(): array
    {
        try {
            $response = $this->client()->post('/api/gw/get-token', []);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak bisa konek ke API gateway: ' . $e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RuntimeException($this->extractErrorMessage($response, 'Gagal ambil token gateway'));
        }

        return (array) $response->json();
    }

    /**
     * @return array{success:bool, message?:string, data?:array}
     */
    public function paymentInitiate(string $accessToken, array $payload): array
    {
        try {
            $response = $this->bearerClient($accessToken)->post('/api/payment/initiate', $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak bisa konek ke API payment initiate: ' . $e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RuntimeException($this->extractErrorMessage($response, 'Gagal initiate pembayaran'));
        }

        return (array) $response->json();
    }

    /**
     * @return array{success:bool, message?:string, data?:array}
     */
    public function paymentStatus(string $accessToken, string $transactionId): array
    {
        $transactionId = trim($transactionId);
        if ($transactionId === '') {
            throw new RuntimeException('Transaction ID kosong.');
        }

        try {
            $response = $this->bearerClient($accessToken)->get('/api/payment/' . rawurlencode($transactionId));
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak bisa konek ke API payment status: ' . $e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RuntimeException($this->extractErrorMessage($response, 'Gagal cek status pembayaran'));
        }

        return (array) $response->json();
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function getBalance(string $gatewayToken): array
    {
        return $this->postGw('/api/gw/get-balance', $gatewayToken, []);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function campaignPriceView(string $gatewayToken, array $payload = ['campaignType' => 'ALL']): array
    {
        return $this->postGw('/api/gw/price-view', $gatewayToken, $payload);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function profileDropdownList(string $gatewayToken, array $payload = ['profile' => 'ALL']): array
    {
        return $this->postGw('/api/gw/profile-dropdown-list', $gatewayToken, $payload);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function campaignList(string $gatewayToken, array $payload = ['campaignId' => null]): array
    {
        return $this->postGw('/api/gw/list', $gatewayToken, $payload);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function campaignTargetedAdd(string $gatewayToken, array $payload): array
    {
        return $this->postGw('/api/gw/targeted-add', $gatewayToken, $payload);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function campaignLbaAdd(string $gatewayToken, array $payload): array
    {
        return $this->postGw('/api/gw/lba-add', $gatewayToken, $payload);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function postGw(string $path, string $gatewayToken, array $payload): array
    {
        try {
            $response = $this->client()
                ->withHeaders([
                    'x-api-gw-token' => $gatewayToken,
                ])
                ->post($path, $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak bisa konek ke API GW proxy: ' . $e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RuntimeException($this->extractErrorMessage($response, 'Request ke API GW gagal'));
        }

        $json = (array) $response->json();
        return $this->assertGatewayOk($json, 'Request ke API GW gagal');
    }

    private function extractErrorMessage(Response $response, string $fallback): string
    {
        $json = $response->json();

        if (is_array($json)) {
            $msg = $json['message'] ?? $json['error'] ?? null;

            if (is_string($msg) && trim($msg) !== '') {
                return $msg;
            }

            $desc = data_get($json, 'error.desc') ?? data_get($json, 'data.desc') ?? null;
            if (is_string($desc) && trim($desc) !== '') {
                return $desc;
            }
        }

        return $fallback;
    }

    /**
     * Upload file via gateway if needed by MMS flows.
     * Kept here because ApiGatewayTestController references it.
     */
    public function uploadFile(string $gatewayToken, UploadedFile $file, string $type = 'FILE'): array
    {
        try {
            $response = Http::baseUrl($this->baseUrl)
                ->acceptJson()
                ->timeout(60)
                ->withHeaders([
                    'x-api-gw-token' => $gatewayToken,
                ])
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post('/api/gw/upload', [
                    'type' => $type,
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak bisa konek ke API upload: ' . $e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RuntimeException($this->extractErrorMessage($response, 'Upload gagal'));
        }

        $json = (array) $response->json();
        return $this->assertGatewayOk($json, 'Upload gagal');
    }
}
