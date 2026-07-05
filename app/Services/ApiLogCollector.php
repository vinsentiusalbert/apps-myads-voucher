<?php

namespace App\Services;

class ApiLogCollector
{
    private static array $logs = [];

    public static function add(string $method, string $url, $payload, int $status, $response): void
    {
        self::$logs[] = [
            'method' => $method,
            'url' => $url,
            'payload' => $payload,
            'status' => $status,
            'response' => $response,
            'timestamp' => date('H:i:s'),
        ];
    }

    public static function all(): array
    {
        return self::$logs;
    }
}
