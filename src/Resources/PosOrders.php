<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class PosOrders
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar órdenes/cuentas POS — GET /v1/pos/orders */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/orders", $params, null, false, false, $opts);
    }

    /** Crear orden/cuenta POS — POST /v1/pos/orders */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/pos/orders", null, $params, true, false, $opts);
    }

    /** Obtener orden/cuenta POS — GET /v1/pos/orders/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/orders/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Anular orden/cuenta POS — DELETE /v1/pos/orders/{id} */
    public function void(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('DELETE', "/v1/pos/orders/" . rawurlencode($id), null, $params, false, false, $opts);
    }

    /** Cobrar una orden/cuenta POS (split de pagos) — POST /v1/pos/orders/{id}/charge */
    public function charge(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/pos/orders/" . rawurlencode($id) . "/charge", null, $params, true, false, $opts);
    }

}
