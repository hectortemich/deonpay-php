<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class PosCustomers
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar clientes del programa POS — GET /v1/pos/customers */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/customers", $params, null, false, false, $opts);
    }

    /** Crear cliente del programa POS — POST /v1/pos/customers */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/pos/customers", null, $params, true, false, $opts);
    }

    /** Obtener cliente del programa POS — GET /v1/pos/customers/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/customers/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Actualizar cliente del programa POS — PATCH /v1/pos/customers/{id} */
    public function update(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('PATCH', "/v1/pos/customers/" . rawurlencode($id), null, $params, false, false, $opts);
    }

    /** Historial de órdenes de un cliente POS — GET /v1/pos/customers/{id}/orders */
    public function listOrders(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/customers/" . rawurlencode($id) . "/orders", $params, null, false, false, $opts);
    }

}
