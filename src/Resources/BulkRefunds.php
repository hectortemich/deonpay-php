<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class BulkRefunds
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar lotes de devoluciones — GET /v1/bulk_refunds */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/bulk_refunds", $params, null, false, false, $opts);
    }

    /** Crear lote de devoluciones — POST /v1/bulk_refunds */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/bulk_refunds", null, $params, true, false, $opts);
    }

    /** Obtener lote de devoluciones — GET /v1/bulk_refunds/{id} */
    public function retrieve(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/bulk_refunds/" . rawurlencode($id), $params, null, false, false, $opts);
    }

    /** Cancelar lote de devoluciones — POST /v1/bulk_refunds/{id}/cancel */
    public function cancel(string $id, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/bulk_refunds/" . rawurlencode($id) . "/cancel", null, null, true, false, $opts);
    }

}
