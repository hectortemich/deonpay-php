<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class PosPrintAssets
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Subir una imagen para tickets — POST /v1/pos/print-assets */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/pos/print-assets", null, $params, false, false, $opts);
    }

}
