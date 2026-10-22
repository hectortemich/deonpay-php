<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class BalanceTransactions
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar ledger de conciliación — GET /v1/balance_transactions */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/balance_transactions", $params, null, false, false, $opts);
    }

}
