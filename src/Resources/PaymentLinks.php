<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class PaymentLinks
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar links de pago — GET /v1/links */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/links", $params, null, false, false, $opts);
    }

    /** Crear link de pago — POST /v1/links */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/links", null, $params, true, false, $opts);
    }

    /** Obtener link de pago — GET /v1/links/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/links/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Actualizar link de pago — PATCH /v1/links/{id} */
    public function update(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('PATCH', "/v1/links/" . rawurlencode($id), null, $params, false, false, $opts);
    }

    /** Eliminar link de pago — DELETE /v1/links/{id} */
    public function delete(string $id, array $opts = []): mixed
    {
        return $this->client->request('DELETE', "/v1/links/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Transacciones de un link — GET /v1/links/{id}/transactions */
    public function listTransactions(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/links/" . rawurlencode($id) . "/transactions", $params, null, false, false, $opts);
    }

}
