<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class WebhookEndpoints
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar webhook endpoints — GET /v1/webhook_endpoints */
    public function list(array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/webhook_endpoints", null, null, false, false, $opts);
    }

    /** Crear webhook endpoint — POST /v1/webhook_endpoints */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/webhook_endpoints", null, $params, true, false, $opts);
    }

    /** Obtener webhook endpoint — GET /v1/webhook_endpoints/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/webhook_endpoints/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Actualizar webhook endpoint — PATCH /v1/webhook_endpoints/{id} */
    public function update(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('PATCH', "/v1/webhook_endpoints/" . rawurlencode($id), null, $params, false, false, $opts);
    }

    /** Eliminar webhook endpoint — DELETE /v1/webhook_endpoints/{id} */
    public function delete(string $id, array $opts = []): mixed
    {
        return $this->client->request('DELETE', "/v1/webhook_endpoints/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Rotar el secret de un webhook endpoint — POST /v1/webhook_endpoints/{id}/rotate_secret */
    public function rotateSecret(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/webhook_endpoints/" . rawurlencode($id) . "/rotate_secret", null, $params, true, false, $opts);
    }

}
