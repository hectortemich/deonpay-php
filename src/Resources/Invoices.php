<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class Invoices
{
    public function __construct(private DeonPayClient $client)
    {
    }

    /** Listar facturas CFDI — GET /v1/invoices */
    public function list(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/invoices", $params, null, false, false, $opts);
    }

    /** Emitir factura CFDI manual — POST /v1/invoices */
    public function create(?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/invoices", null, $params, true, false, $opts);
    }

    /** Obtener factura CFDI — GET /v1/invoices/{id} */
    public function retrieve(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/invoices/" . rawurlencode($id), null, null, false, false, $opts);
    }

    /** Cancelar factura CFDI ante el SAT — POST /v1/invoices/{id}/cancel */
    public function cancel(string $id, ?array $params = null, array $opts = []): mixed
    {
        return $this->client->request('POST', "/v1/invoices/" . rawurlencode($id) . "/cancel", null, $params, true, false, $opts);
    }

    /** Descargar PDF de la factura — GET /v1/invoices/{id}/pdf */
    public function pdf(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/invoices/" . rawurlencode($id) . "/pdf", null, null, false, true, $opts);
    }

    /** Descargar XML (CFDI) de la factura — GET /v1/invoices/{id}/xml */
    public function xml(string $id, array $opts = []): mixed
    {
        return $this->client->request('GET', "/v1/invoices/" . rawurlencode($id) . "/xml", null, null, false, true, $opts);
    }

}
