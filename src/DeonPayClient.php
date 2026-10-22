<?php

declare(strict_types=1);

namespace DeonPay;

use DeonPay\Exception\ApiConnectionException;
use DeonPay\Exception\ApiException;
use DeonPay\Exception\AuthenticationException;
use DeonPay\Exception\DeonPayException;
use DeonPay\Exception\InvalidRequestException;
use DeonPay\Exception\NotFoundException;
use DeonPay\Exception\PermissionDeniedException;
use DeonPay\Exception\RateLimitException;
use DeonPay\HttpClient\CurlClient;
use DeonPay\HttpClient\HttpClientInterface;

/**
 * Cliente de la API de DeonPay.
 *
 * Config soportada: base_url, timeout (s), max_retries, api_version (YYYY-MM-DD), http_client (HttpClientInterface),
 * sleep (callable que recibe segundos como float; útil para pruebas).
 */
final class DeonPayClient
{
    use Resources\HasResources;

    public const VERSION = '0.4.0';
    /** Versión de la API (fecha) contra la que se construyó el SDK; va en el header DeonPay-Version. */
    public const API_VERSION = '2026-10-22';
    public const DEFAULT_BASE_URL = 'https://www.deonpay.mx/api';

    private const RETRY_STATUSES = [429, 500, 502, 503, 504];

    private string $apiKey;
    private string $baseUrl;
    private float $timeout;
    private int $maxRetries;
    private string $apiVersion;
    private HttpClientInterface $http;
    /** @var callable(float):void */
    private $sleeper;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(string $apiKey, array $config = [])
    {
        $apiKey = trim($apiKey);
        if ($apiKey === '') {
            throw new \InvalidArgumentException('Falta la API key de DeonPay (token dp_...).');
        }
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? self::DEFAULT_BASE_URL), '/');
        $this->timeout = (float) ($config['timeout'] ?? 30);
        $this->maxRetries = max(0, (int) ($config['max_retries'] ?? 2));
        $this->apiVersion = (string) ($config['api_version'] ?? self::API_VERSION);
        $http = $config['http_client'] ?? null;
        if ($http !== null && !$http instanceof HttpClientInterface) {
            throw new \InvalidArgumentException('http_client debe implementar DeonPay\\HttpClient\\HttpClientInterface.');
        }
        $this->http = $http ?? new CurlClient();
        $sleep = $config['sleep'] ?? null;
        if ($sleep !== null && !is_callable($sleep)) {
            throw new \InvalidArgumentException('sleep debe ser callable.');
        }
        $this->sleeper = $sleep ?? static function (float $seconds): void {
            if ($seconds > 0) {
                usleep((int) round($seconds * 1000000));
            }
        };

        $this->attachResources();
    }

    /**
     * Ejecuta una petición contra la API. Lo usan los recursos generados.
     *
     * @param array<string,mixed>|null $query
     * @param array<string,mixed>|null $body
     * @param array<string,mixed> $opts idempotency_key (string), timeout (segundos), api_version (string)
     * @throws DeonPayException
     */
    public function request(
        string $method,
        string $path,
        ?array $query = null,
        ?array $body = null,
        bool $idempotent = false,
        bool $binary = false,
        array $opts = [],
    ): mixed {
        $method = strtoupper($method);
        $url = $this->baseUrl . $path;
        $qs = $this->buildQuery($query);
        if ($qs !== '') {
            $url .= (str_contains($path, '?') ? '&' : '?') . $qs;
        }

        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => $binary ? '*/*' : 'application/json',
            'User-Agent' => 'deonpay-php/' . self::VERSION . ' (PHP ' . PHP_VERSION . ')',
            'DeonPay-Version' => isset($opts['api_version']) ? (string) $opts['api_version'] : $this->apiVersion,
        ];

        $payload = null;
        if ($body !== null) {
            $payload = $body === []
                ? '{}'
                : json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $headers['Content-Type'] = 'application/json';
        }

        $userKey = isset($opts['idempotency_key']) ? (string) $opts['idempotency_key'] : '';
        if ($userKey !== '') {
            $headers['Idempotency-Key'] = $userKey;
        } elseif ($idempotent && $method !== 'GET') {
            $headers['Idempotency-Key'] = self::uuid4();
        }

        $timeout = isset($opts['timeout']) ? (float) $opts['timeout'] : $this->timeout;
        $canRetry = $method === 'GET' || isset($headers['Idempotency-Key']);

        for ($attempt = 0;; $attempt++) {
            $more = $canRetry && $attempt < $this->maxRetries;
            try {
                $res = $this->http->send($method, $url, $headers, $payload, $timeout);
            } catch (ApiConnectionException $e) {
                if ($more) {
                    ($this->sleeper)($this->backoff($attempt, null));
                    continue;
                }
                throw $e;
            }

            $status = (int) $res['status'];
            $resHeaders = [];
            foreach ((array) ($res['headers'] ?? []) as $k => $v) {
                $resHeaders[strtolower((string) $k)] = is_array($v) ? implode(', ', $v) : (string) $v;
            }
            $raw = (string) ($res['body'] ?? '');

            if ($status >= 200 && $status < 300) {
                return $this->parseSuccess($status, $raw, $binary, $resHeaders);
            }

            // Un 500 en una escritura no se reintenta: el efecto pudo ocurrir.
            $retryable = in_array($status, self::RETRY_STATUSES, true) && !($status === 500 && $method !== 'GET');
            if ($more && $retryable) {
                ($this->sleeper)($this->backoff($attempt, $resHeaders['retry-after'] ?? null));
                continue;
            }

            throw $this->buildError($status, $raw, $resHeaders);
        }
    }

    /** @param array<string,mixed>|null $query */
    private function buildQuery(?array $query): string
    {
        if ($query === null) {
            return '';
        }
        $parts = [];
        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $items = [];
                foreach ($value as $item) {
                    if ($item === null) {
                        continue;
                    }
                    $items[] = is_bool($item) ? ($item ? 'true' : 'false') : (string) $item;
                }
                $value = implode(',', $items);
            } else {
                $value = (string) $value;
            }
            $parts[] = rawurlencode((string) $key) . '=' . rawurlencode($value);
        }
        return implode('&', $parts);
    }

    /** @param array<string,string> $headers */
    private function parseSuccess(int $status, string $raw, bool $binary, array $headers): mixed
    {
        if ($binary) {
            return $raw;
        }
        if ($status === 204 || trim($raw) === '') {
            return null;
        }
        $data = json_decode($raw, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException(
                'Respuesta de la API no es JSON válido',
                $status,
                null,
                $raw,
                $headers,
                $this->requestId($headers),
            );
        }
        return $data;
    }

    /** @param array<string,string> $headers */
    private function buildError(int $status, string $raw, array $headers): DeonPayException
    {
        $code = null;
        $message = null;
        $bodyRequestId = null;
        $data = json_decode($raw, true);
        if (is_array($data)) {
            $err = $data['error'] ?? null;
            if (is_array($err)) {
                $code = isset($err['code']) && is_scalar($err['code']) ? (string) $err['code'] : null;
                $message = isset($err['message']) && is_scalar($err['message']) ? (string) $err['message'] : null;
                $bodyRequestId = isset($err['request_id']) && is_string($err['request_id']) ? $err['request_id'] : null;
            } elseif (is_string($err)) {
                $message = $err;
            }
            if ($message === null && isset($data['message']) && is_scalar($data['message'])) {
                $message = (string) $data['message'];
            }
        }
        if ($message === null || $message === '') {
            $message = 'Error HTTP ' . $status;
        }

        $class = match (true) {
            $status === 401 => AuthenticationException::class,
            $status === 403 => PermissionDeniedException::class,
            $status === 404 => NotFoundException::class,
            $status === 400, $status === 409, $status === 422 => InvalidRequestException::class,
            $status === 429 => RateLimitException::class,
            default => ApiException::class,
        };

        return new $class($message, $status, $code, $raw, $headers, $this->requestId($headers, $bodyRequestId));
    }

    /** @param array<string,string> $headers */
    private function requestId(array $headers, ?string $bodyRequestId = null): ?string
    {
        // Prioridad: header Request-Id > error.request_id del cuerpo > x-vercel-id
        return $headers['request-id'] ?? $headers['x-request-id'] ?? $bodyRequestId ?? $headers['x-vercel-id'] ?? null;
    }

    private function backoff(int $attempt, ?string $retryAfter): float
    {
        if ($retryAfter !== null && is_numeric(trim($retryAfter))) {
            return min(20.0, max(0.0, (float) trim($retryAfter)));
        }
        $jitter = mt_rand(0, 250) / 1000;
        return min(8.0, 0.5 * (2 ** $attempt) + $jitter);
    }

    private static function uuid4(): string
    {
        $b = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
        $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
        $h = bin2hex($b);
        return sprintf('%s-%s-%s-%s-%s', substr($h, 0, 8), substr($h, 8, 4), substr($h, 12, 4), substr($h, 16, 4), substr($h, 20, 12));
    }
}
