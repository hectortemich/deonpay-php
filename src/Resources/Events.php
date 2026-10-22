<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class Events
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar eventos — GET /v1/events */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/events", $params, null, false, false, $opts);
    }

    /** Obtener un evento — GET /v1/events/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/events/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Reenviar un evento — POST /v1/events/{id}/resend */
    public function resend(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/events/" . rawurlencode($id) . "/resend", null, $params, true, false, $opts);
    }

}
