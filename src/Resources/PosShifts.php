<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class PosShifts
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar turnos de caja — GET /v1/pos/shifts */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/shifts", $params, null, false, false, $opts);
    }

    /** Abrir turno de caja — POST /v1/pos/shifts */
    public function open(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/pos/shifts", null, $params, true, false, $opts);
    }

    /** Obtener turno de caja — GET /v1/pos/shifts/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/shifts/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Cerrar turno de caja (corte) — POST /v1/pos/shifts/{id}/close */
    public function close(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/pos/shifts/" . rawurlencode($id) . "/close", null, $params, true, false, $opts);
    }

}
