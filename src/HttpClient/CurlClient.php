<?php

declare(strict_types=1);

namespace DeonPay\HttpClient;

use DeonPay\Exception\ApiConnectionException;

/** Transporte HTTP basado en ext-curl. */
final class CurlClient implements HttpClientInterface
{
    public function send(string $method, string $url, array $headers, ?string $body, float $timeout): array
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new ApiConnectionException('No se pudo inicializar cURL');
        }

        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        $responseHeaders = [];
        $timeoutMs = max(1, (int) round($timeout * 1000));

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT_MS => min($timeoutMs, 10000),
            CURLOPT_TIMEOUT_MS => $timeoutMs,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
                $len = strlen($line);
                $pos = strpos($line, ':');
                if ($pos !== false) {
                    $name = strtolower(trim(substr($line, 0, $pos)));
                    $responseHeaders[$name] = trim(substr($line, $pos + 1));
                }
                return $len;
            },
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new ApiConnectionException(sprintf('Error de red (cURL %d): %s', $errno, $err));
        }
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [
            'status' => $status,
            'headers' => $responseHeaders,
            'body' => (string) $raw,
        ];
    }
}
