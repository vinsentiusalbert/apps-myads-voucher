<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use stdClass;

class MyadsApiClient
{
    public function __construct(
        private string $baseUrl = '',
        private string $directBaseUrl = ''
    ) {
        $this->baseUrl = $this->baseUrl !== '' ? $this->baseUrl : (string) config('myads.api_base_url', 'http://api.goto-myads.com');
        $this->directBaseUrl = $this->directBaseUrl !== '' ? $this->directBaseUrl : (string) config('myads.direct_api_base_url', 'https://api.digitalcore.telkomsel.com');
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

    private function directClient(string $accessToken): PendingRequest
    {
        $accessToken = trim($accessToken);
        if ($accessToken === '') {
            throw new RuntimeException('Token akses tidak ditemukan. Silakan login ulang.');
        }

        if (! $this->hasDirectApiConfig()) {
            throw new RuntimeException('Konfigurasi direct API WABA belum lengkap.');
        }

        $apiKey = trim((string) config('myads.direct_api_key', ''));
        $apiSecret = trim((string) config('myads.direct_api_secret', ''));
        $perUserKey = trim((string) config('myads.direct_per_user_key', ''));

        $today = Carbon::now(config('app.timezone'))->format('Ymd');
        $timestamp = (string) time();

        return Http::baseUrl($this->directBaseUrl)
            ->acceptJson()
            ->timeout(30)
            ->withToken($accessToken)
            ->withHeaders([
                'X-API-SECRET' => base64_encode($perUserKey . ':' . $today),
                'api_key' => $apiKey,
                'X-signature' => md5($apiKey . $apiSecret . $timestamp),
            ]);
    }

    private function hasDirectApiConfig(): bool
    {
        return trim((string) config('myads.direct_api_key', '')) !== ''
            && trim((string) config('myads.direct_api_secret', '')) !== ''
            && trim((string) config('myads.direct_per_user_key', '')) !== '';
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
    public function registrationContext(string $merchantId, string $partnerId): array
    {
        try {
            $response = $this->client()->get('/api/sso/register', [
                'merchant_id' => $merchantId,
                'partner_id' => $partnerId,
            ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak bisa konek ke API SSO register: ' . $e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RuntimeException($this->extractErrorMessage($response, 'Konteks register tidak valid'));
        }

        return (array) $response->json();
    }

    /**
     * @return array{success:bool, message?:string, data?:array}
     */
    public function register(string $email, string $password, string $name, string $merchantId, string $partnerId): array
    {
        try {
            $response = $this->client()->post('/api/auth/register', [
                'email' => $email,
                'password' => $password,
                'name' => $name,
                'merchant_id' => $merchantId,
                'partner_id' => $partnerId,
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
    public function getGatewayToken(?string $accessToken = null): array
    {
        try {
            $request = $this->client();
            $accessToken = is_string($accessToken) ? trim($accessToken) : '';
            if ($accessToken !== '') {
                $request = $request->withToken($accessToken);
            }

            $response = $request->post('/api/gw/get-token', []);
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
    public function getBalance(string $gatewayToken, ?callable $refreshGatewayToken = null): array
    {
        return $this->postGw('/api/gw/get-balance', $gatewayToken, [], $refreshGatewayToken);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function campaignPriceView(string $gatewayToken, array $payload = ['campaignType' => 'ALL'], ?callable $refreshGatewayToken = null): array
    {
        return $this->postGw('/api/gw/price-view', $gatewayToken, $payload, $refreshGatewayToken);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function profileDropdownList(string $gatewayToken, array $payload = ['profile' => 'ALL'], ?callable $refreshGatewayToken = null): array
    {
        return $this->postGw('/api/gw/profile-dropdown-list', $gatewayToken, $payload, $refreshGatewayToken);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function campaignList(string $gatewayToken, array $payload = ['campaignId' => null], ?callable $refreshGatewayToken = null): array
    {
        return $this->postGw('/api/gw/list', $gatewayToken, $payload, $refreshGatewayToken);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function campaignReport(string $gatewayToken, array $payload, ?callable $refreshGatewayToken = null): array
    {
        return $this->postGw('/api/gw/report/view', $gatewayToken, $payload, $refreshGatewayToken);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function campaignTargetedAdd(string $gatewayToken, array $payload, ?callable $refreshGatewayToken = null): array
    {
        return $this->postGw('/api/gw/targeted-add', $gatewayToken, $payload, $refreshGatewayToken);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function campaignLbaAdd(string $gatewayToken, array $payload, ?callable $refreshGatewayToken = null): array
    {
        return $this->postGw('/api/gw/lba-add', $gatewayToken, $payload, $refreshGatewayToken);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function wabaLbaAdd(string $gatewayToken, array $payload, ?callable $refreshGatewayToken = null): array
    {
        return $this->postGw('/api/gw/waba-lba-add', $gatewayToken, $payload, $refreshGatewayToken);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function wabaTargetedAdd(string $gatewayToken, array $payload, ?callable $refreshGatewayToken = null): array
    {
        return $this->postGw('/api/gw/waba-targeted-add', $gatewayToken, $payload, $refreshGatewayToken);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function wabaTemplateAdd(string $accessToken, array $payload, ?string $gatewayToken = null, ?callable $refreshGatewayToken = null): array
    {
        if ($gatewayToken === null || trim($gatewayToken) === '') {
            throw new RuntimeException('Token API GW tidak tersedia.');
        }

        return $this->postGw('/api/gw/waba-template-add', $gatewayToken, $payload, $refreshGatewayToken);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function wabaTemplateList(string $accessToken, array $payload = [], ?string $gatewayToken = null, ?callable $refreshGatewayToken = null): array
    {
        if ($gatewayToken === null || trim($gatewayToken) === '') {
            throw new RuntimeException('Token API GW tidak tersedia.');
        }

        return $this->postGw('/api/gw/waba-template-list', $gatewayToken, $payload, $refreshGatewayToken);
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function wabaDisplayNameList(string $accessToken, array $payload = [], ?string $gatewayToken = null, ?callable $refreshGatewayToken = null): array
    {
        $requestPayload = $payload === [] ? new stdClass() : $payload;

        if (! $this->hasDirectApiConfig()) {
            if ($gatewayToken === null || trim($gatewayToken) === '') {
                throw new RuntimeException('Konfigurasi direct API WABA belum lengkap.');
            }

            return $this->postGw('/api/gw/waba-display-name-list', $gatewayToken, is_array($requestPayload) ? $requestPayload : [], $refreshGatewayToken);
        }

        try {
            $response = $this->directClient($accessToken)->asJson()->post('/scrt/myads/api/v1/waba/display-name/list', $requestPayload);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak bisa konek ke API WABA display name list: ' . $e->getMessage(), previous: $e);
        } catch (RuntimeException $e) {
            throw $e;
        }

        if ($response->failed()) {
            throw new RuntimeException($this->extractErrorMessage($response, 'Gagal ambil WABA display name list'));
        }

        $json = (array) $response->json();
        $code = $json['code'] ?? null;
        if (is_numeric($code) && (int) $code !== 0) {
            $desc = $json['desc'] ?? null;
            throw new RuntimeException(is_string($desc) && trim($desc) !== '' ? $desc : 'Gagal ambil WABA display name list');
        }

        return [
            'success' => true,
            'data' => $json,
        ];
    }

    /**
     * @return array{success:bool, data?:array}
     */
    public function postGw(string $path, string $gatewayToken, array $payload, ?callable $refreshGatewayToken = null): array
    {
        try {
            return $this->sendPostGw($path, $gatewayToken, $payload);
        } catch (RuntimeException $e) {
            if ($refreshGatewayToken === null || ! $this->isGatewayAuthorizationCacheMiss($e->getMessage())) {
                throw $e;
            }

            $freshToken = (string) $refreshGatewayToken();
            if (trim($freshToken) === '') {
                throw $e;
            }

            return $this->sendPostGw($path, $freshToken, $payload);
        }
    }

    public function extractGatewayToken(array $response): string
    {
        $token = data_get($response, 'data.data.token')
            ?? data_get($response, 'data.token')
            ?? data_get($response, 'token')
            ?? '';

        return is_string($token) ? trim($token) : '';
    }

    private function isGatewayAuthorizationCacheMiss(string $message): bool
    {
        $message = strtolower($message);

        return str_contains($message, 'authorization data')
            && str_contains($message, 'in-memory cache');
    }

    /**
     * @return array{success:bool, data?:array}
     */
    private function sendPostGw(string $path, string $gatewayToken, array $payload): array
    {
        $requestPayload = $payload === [] ? new stdClass() : $payload;

        try {
            $response = $this->client()
                ->withHeaders([
                    'x-api-gw-token' => $gatewayToken,
                ])
                ->post($path, $requestPayload);
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

        $body = trim(strip_tags($response->body()));

        return $body !== '' ? $body : $fallback;
    }

    /**
     * Upload file via gateway if needed by MMS flows.
     * Kept here because ApiGatewayTestController references it.
     */
    public function uploadFile(string $accessToken, UploadedFile $file, string $type = 'MULTIMEDIA', ?string $gatewayToken = null, ?callable $refreshGatewayToken = null): array
    {
        return $this->sendUploadFile($accessToken, $file, $type);
    }

    private function sendUploadFile(string $accessToken, UploadedFile $file, string $type): array
    {
        try {
            $response = $this->directClient($accessToken)
                ->timeout(60)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post('/scrt/myads/api/v1/content/upload', [
                    'type' => $type,
                ]);
        } catch (ConnectionException | RuntimeException $e) {
            throw new RuntimeException('Tidak bisa konek ke API upload: ' . $e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RuntimeException($this->extractErrorMessage($response, 'Upload gagal'));
        }

        $json = (array) $response->json();
        $code = $json['code'] ?? null;
        if (is_numeric($code) && (int) $code !== 0) {
            $desc = $json['desc'] ?? null;
            throw new RuntimeException(is_string($desc) && trim($desc) !== '' ? $desc : 'Upload gagal');
        }

        return [
            'success' => true,
            'data' => $json,
        ];
    }
}
