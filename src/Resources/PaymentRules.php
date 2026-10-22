<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class PaymentRules
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Reglas de pago del comercio — GET /v1/merchant/payment-rules */
    public function retrieve(array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/merchant/payment-rules", null, null, false, false, $opts);
    }

}
