<?php

declare(strict_types=1);

namespace DeonPay\HttpClient;

/** Transporte HTTP. Inyectable para pruebas. */
interface HttpClientInterface
{
    /**
     * Envía una petición HTTP.
     *
     * @param array<string,string> $headers Cabeceras de la petición (nombre => valor).
     * @param float $timeout Segundos.
     * @return array{status:int, headers:array<string,string>, body:string} Cabeceras con nombres en minúsculas.
     *
     * @throws \DeonPay\Exception\ApiConnectionException Ante error de red o timeout.
     */
    public function send(string $method, string $url, array $headers, ?string $body, float $timeout): array;
}
