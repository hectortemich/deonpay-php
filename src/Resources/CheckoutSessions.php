<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class CheckoutSessions
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar checkout sessions — GET /v1/checkout/sessions */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/checkout/sessions", $params, null, false, false, $opts);
    }

    /** Crear checkout session — POST /v1/checkout/sessions */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/checkout/sessions", null, $params, true, false, $opts);
    }

    /** Obtener checkout session — GET /v1/checkout/sessions/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/checkout/sessions/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Cancelar checkout session — PATCH /v1/checkout/sessions/{id} */
    public function cancel(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('PATCH', "/v1/checkout/sessions/" . rawurlencode($id), null, $params, false, false, $opts);
    }

}
