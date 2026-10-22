<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class Reports
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Resumen de ventas (rango libre) — GET /v1/reports/summary */
    public function summary(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/reports/summary", $params, null, false, false, $opts);
    }

    /** Cierre de día — GET /v1/reports/daily-close */
    public function dailyClose(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/reports/daily-close", $params, null, false, false, $opts);
    }

    /** Ranking de vendedores — GET /v1/reports/sellers */
    public function sellers(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/reports/sellers", $params, null, false, false, $opts);
    }

}
