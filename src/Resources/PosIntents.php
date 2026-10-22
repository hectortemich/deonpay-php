<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class PosIntents
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar intents de cobro (terminales SDK) — GET /v1/pos/intents */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/intents", $params, null, false, false, $opts);
    }

    /** Crear intent de cobro para terminal SDK — POST /v1/pos/intents */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/pos/intents", null, $params, true, false, $opts);
    }

    /** Obtener intent de cobro — GET /v1/pos/intents/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/intents/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Cancelar intent de cobro pendiente — DELETE /v1/pos/intents/{id} */
    public function cancel(string $id, array $opts = []): mixed
    {
        return $this->client->request('DELETE', "/v1/pos/intents/" . rawurlencode($id), null, null, false, false, $opts);
    }

}
