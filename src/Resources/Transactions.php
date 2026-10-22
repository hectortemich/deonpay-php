<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class Transactions
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar transacciones — GET /v1/transactions */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/transactions", $params, null, false, false, $opts);
    }

    /** Obtener transacción — GET /v1/transactions/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/transactions/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Reembolsar o cancelar transacción — POST /v1/transactions/{id} */
    public function refund(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/transactions/" . rawurlencode($id), null, $params, true, false, $opts);
    }

    /** Reenviar recibo de una transacción — POST /v1/transactions/{id}/send_receipt */
    public function sendReceipt(string $id, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/transactions/" . rawurlencode($id) . "/send_receipt", null, null, true, false, $opts);
    }

}
