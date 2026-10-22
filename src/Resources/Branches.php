<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class Branches
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar sucursales — GET /v1/branches */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/branches", $params, null, false, false, $opts);
    }

    /** Crear sucursal — POST /v1/branches */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/branches", null, $params, true, false, $opts);
    }

    /** Obtener sucursal — GET /v1/branches/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/branches/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Actualizar sucursal — PATCH /v1/branches/{id} */
    public function update(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('PATCH', "/v1/branches/" . rawurlencode($id), null, $params, false, false, $opts);
    }

    /** Desactivar sucursal — POST /v1/branches/{id}/deactivate */
    public function deactivate(string $id, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/branches/" . rawurlencode($id) . "/deactivate", null, null, false, false, $opts);
    }

}
