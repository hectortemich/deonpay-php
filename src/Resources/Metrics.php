<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class Metrics
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Métricas del comercio — GET /v1/merchant/metrics */
    public function retrieve(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/merchant/metrics", $params, null, false, false, $opts);
    }

    /** Serie temporal de revenue — GET /v1/merchant/metrics/series */
    public function series(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/merchant/metrics/series", $params, null, false, false, $opts);
    }

}
