<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class PaymentIntents
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar payment intents — GET /v1/payment_intents */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/payment_intents", $params, null, false, false, $opts);
    }

    /** Crear payment intent — POST /v1/payment_intents */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/payment_intents", null, $params, true, false, $opts);
    }

    /** Obtener payment intent — GET /v1/payment_intents/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/payment_intents/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Cancelar payment intent — POST /v1/payment_intents/{id}/cancel */
    public function cancel(string $id, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/payment_intents/" . rawurlencode($id) . "/cancel", null, null, true, false, $opts);
    }

}
