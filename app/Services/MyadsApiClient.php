<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
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
     * @return array{success:bool, data?:array}
     */
    public function getBalance(string $gatewayToken): array
    {
        return $this->postGw('/api/gw/get-balance', $gatewayToken, []);
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

        return (array) $response->json();
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
}
