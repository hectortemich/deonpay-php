<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class PosTerminals
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar terminales POS — GET /v1/pos/terminals */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/terminals", $params, null, false, false, $opts);
    }

    /** Registrar terminal POS — POST /v1/pos/terminals */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/pos/terminals", null, $params, true, false, $opts);
    }

    /** Obtener terminal POS — GET /v1/pos/terminals/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/terminals/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Actualizar terminal POS — PATCH /v1/pos/terminals/{id} */
    public function update(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('PATCH', "/v1/pos/terminals/" . rawurlencode($id), null, $params, false, false, $opts);
    }

    /** Desactivar terminal POS — POST /v1/pos/terminals/{id}/deactivate */
    public function deactivate(string $id, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/pos/terminals/" . rawurlencode($id) . "/deactivate", null, null, false, false, $opts);
    }

}
