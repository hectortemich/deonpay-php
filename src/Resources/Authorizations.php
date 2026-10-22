<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class Authorizations
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar retenciones (pre-autorizaciones) — GET /v1/authorizations */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/authorizations", $params, null, false, false, $opts);
    }

    /** Obtener retención — GET /v1/authorizations/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/authorizations/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Fijar/quitar auto-renovación de una retención — PATCH /v1/authorizations/{id} */
    public function update(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('PATCH', "/v1/authorizations/" . rawurlencode($id), null, $params, false, false, $opts);
    }

    /** Capturar (cobrar) una retención — POST /v1/authorizations/{id}/capture */
    public function capture(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/authorizations/" . rawurlencode($id) . "/capture", null, $params, true, false, $opts);
    }

    /** Incrementar (ReAuth) una retención — POST /v1/authorizations/{id}/increment */
    public function increment(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/authorizations/" . rawurlencode($id) . "/increment", null, $params, true, false, $opts);
    }

    /** Liberar una retención sin cobrar — POST /v1/authorizations/{id}/void */
    public function void(string $id, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/authorizations/" . rawurlencode($id) . "/void", null, null, true, false, $opts);
    }

}
