<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class Subscriptions
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar planes de suscripción — GET /v1/subscriptions */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/subscriptions", $params, null, false, false, $opts);
    }

    /** Crear plan de suscripción — POST /v1/subscriptions */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/subscriptions", null, $params, true, false, $opts);
    }

    /** Obtener plan de suscripción — GET /v1/subscriptions/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/subscriptions/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Actualizar plan de suscripción — PUT /v1/subscriptions/{id} */
    public function update(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('PUT', "/v1/subscriptions/" . rawurlencode($id), null, $params, false, false, $opts);
    }

}
