<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class Products
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar productos — GET /v1/products */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/products", $params, null, false, false, $opts);
    }

    /** Crear producto — POST /v1/products */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/products", null, $params, true, false, $opts);
    }

    /** Obtener producto — GET /v1/products/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/products/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Actualizar producto — PATCH /v1/products/{id} */
    public function update(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('PATCH', "/v1/products/" . rawurlencode($id), null, $params, false, false, $opts);
    }

    /** Eliminar producto — DELETE /v1/products/{id} */
    public function delete(string $id, array $opts = []): mixed
    {
        return $this->client->request('DELETE', "/v1/products/" . rawurlencode($id), null, null, false, false, $opts);
    }

}
