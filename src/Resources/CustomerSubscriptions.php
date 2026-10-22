<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class CustomerSubscriptions
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar suscriptores — GET /v1/customer-subscriptions */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/customer-subscriptions", $params, null, false, false, $opts);
    }

    /** Obtener suscriptor — GET /v1/customer-subscriptions/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/customer-subscriptions/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Cancelar suscripción de un cliente — POST /v1/customer-subscriptions/{id}/cancel */
    public function cancel(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/customer-subscriptions/" . rawurlencode($id) . "/cancel", null, $params, true, false, $opts);
    }

    /** Pausar suscripción de un cliente — POST /v1/customer-subscriptions/{id}/pause */
    public function pause(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/customer-subscriptions/" . rawurlencode($id) . "/pause", null, $params, true, false, $opts);
    }

    /** Reanudar suscripción de un cliente — POST /v1/customer-subscriptions/{id}/resume */
    public function resume(string $id, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/customer-subscriptions/" . rawurlencode($id) . "/resume", null, null, true, false, $opts);
    }

    /** Deshacer cancelación programada — POST /v1/customer-subscriptions/{id}/undo_cancel */
    public function undoCancel(string $id, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/customer-subscriptions/" . rawurlencode($id) . "/undo_cancel", null, null, true, false, $opts);
    }

}
