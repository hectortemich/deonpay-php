<?php

declare(strict_types=1);

namespace DeonPay;

use DeonPay\Exception\SignatureVerificationException;

/**
 * Verificación de webhooks.
 *
 * - Cabecera `DeonPay-Signature` (recomendada): `t=<unix>,v1=<hex>[,v1=<hex>...]`; cada v1 es el
 *   HMAC-SHA256 hex de "{t}.{body crudo}". En una rotación de secreto hay dos v1; basta con que
 *   alguno coincida. Se rechaza si el timestamp se aleja más de la tolerancia (300 s).
 * - Cabecera `X-DeonPay-Signature` (legada): HMAC-SHA256 hex del body crudo, solo secreto vigente.
 */
final class Webhook
{
    public const SIGNATURE_HEADER = 'DeonPay-Signature';
    public const LEGACY_SIGNATURE_HEADER = 'X-DeonPay-Signature';
    public const DEFAULT_TOLERANCE_SECONDS = 300;

    private const OK = 'ok';
    private const MISMATCH = 'mismatch';
    private const EXPIRED = 'expired';
    private const MALFORMED = 'malformed';

    private function __construct()
    {
    }

    /**
     * Verifica la firma en tiempo constante. Acepta el valor de `DeonPay-Signature`
     * (`t=...,v1=...`) o el del header legado (hex). Nunca lanza: devuelve false si es inválida.
     *
     * @param string   $payload   Body CRUDO tal cual llegó.
     * @param int      $tolerance Segundos de tolerancia del timestamp; 0 o menos desactiva el chequeo.
     * @param int|null $now       Hora actual (segundos unix); solo para pruebas.
     */
    public static function verifySignature(
        string $payload,
        string $header,
        string $secret,
        int $tolerance = self::DEFAULT_TOLERANCE_SECONDS,
        ?int $now = null
    ): bool {
        return self::evaluate($payload, $header, $secret, $tolerance, $now) === self::OK;
    }

    /**
     * Verifica la firma y devuelve el evento decodificado.
     *
     * @return array<string,mixed>
     * @throws SignatureVerificationException Si la firma no coincide, el timestamp está fuera de
     *                                        tolerancia o el payload no es JSON.
     */
    public static function constructEvent(
        string $payload,
        string $header,
        string $secret,
        int $tolerance = self::DEFAULT_TOLERANCE_SECONDS,
        ?int $now = null
    ): array {
        $verdict = self::evaluate($payload, $header, $secret, $tolerance, $now);
        if ($verdict === self::EXPIRED) {
            throw new SignatureVerificationException('El timestamp del webhook está fuera de tolerancia');
        }
        if ($verdict !== self::OK) {
            throw new SignatureVerificationException('La firma del webhook no coincide');
        }
        $event = json_decode($payload, true);
        if (!is_array($event)) {
            throw new SignatureVerificationException('El payload del webhook no es JSON válido');
        }
        return $event;
    }

    /** Genera un header `t=<ts>,v1=<hex>` válido para probar tu handler en pruebas unitarias. */
    public static function generateTestHeader(string $payload, string $secret, ?int $timestamp = null): string
    {
        $t = $timestamp ?? time();
        return 't=' . $t . ',v1=' . hash_hmac('sha256', $t . '.' . $payload, $secret);
    }

    private static function evaluate(string $payload, string $header, string $secret, int $tolerance, ?int $now): string
    {
        $header = trim($header);
        if ($header === '' || $secret === '') {
            return self::MALFORMED;
        }

        if (strpos($header, 't=') !== false && strpos($header, 'v1=') !== false) {
            $timestamp = null;
            $signatures = [];
            foreach (explode(',', $header) as $item) {
                $pos = strpos($item, '=');
                if ($pos === false) {
                    continue;
                }
                $key = trim(substr($item, 0, $pos));
                $value = trim(substr($item, $pos + 1));
                if ($key === 't' && $timestamp === null) {
                    $timestamp = $value;
                } elseif ($key === 'v1' && $value !== '') {
                    $signatures[] = strtolower($value);
                }
                // Llaves desconocidas (v0, etc.) se ignoran.
            }
            if ($timestamp === null || !preg_match('/^[0-9]+$/', $timestamp) || $signatures === []) {
                return self::MALFORMED;
            }
            $t = (int) $timestamp;
            if ($tolerance > 0 && abs(($now ?? time()) - $t) > $tolerance) {
                return self::EXPIRED;
            }
            $expected = hash_hmac('sha256', $t . '.' . $payload, $secret);
            $match = false;
            foreach ($signatures as $sig) { // sin cortocircuito
                if (hash_equals($expected, $sig)) {
                    $match = true;
                }
            }
            return $match ? self::OK : self::MISMATCH;
        }

        // Esquema legado: hex del body crudo; ignora espacios y mayúsculas.
        $legacy = strtolower((string) preg_replace('/\s+/', '', $header));
        return hash_equals(hash_hmac('sha256', $payload, $secret), $legacy) ? self::OK : self::MISMATCH;
    }
}
