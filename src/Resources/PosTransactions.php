<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class PosTransactions
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Reembolsar/cancelar una venta POS (card-present) — POST /v1/pos/transactions/{id}/refund */
    public function refund(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/pos/transactions/" . rawurlencode($id) . "/refund", null, $params, true, false, $opts);
    }

}
