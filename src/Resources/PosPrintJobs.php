<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class PosPrintJobs
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Encolar un ticket de impresión en una terminal SDK — POST /v1/pos/print-jobs */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/pos/print-jobs", null, $params, true, false, $opts);
    }

    /** Listar trabajos de impresión — GET /v1/pos/print-jobs */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/print-jobs", $params, null, false, false, $opts);
    }

    /** Obtener un trabajo de impresión — GET /v1/pos/print-jobs/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/pos/print-jobs/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Cancelar un trabajo de impresión en cola — DELETE /v1/pos/print-jobs/{id} */
    public function cancel(string $id, array $opts = []): mixed
    {
        return $this->client->request('DELETE', "/v1/pos/print-jobs/" . rawurlencode($id), null, null, false, false, $opts);
    }

}
