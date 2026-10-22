<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

use DeonPay\DeonPayClient;

final class Pos
{
    public readonly PosTerminals $terminals;
    public readonly PosOrders $orders;
    public readonly PosShifts $shifts;
    public readonly PosCustomers $customers;
    public readonly PosTransactions $transactions;
    public readonly PosReports $reports;
    public readonly PosIntents $intents;
    public readonly PosPrintJobs $printJobs;
    public readonly PosPrintAssets $printAssets;

    public function __construct(DeonPayClient $client)
    {
        $this->terminals = new PosTerminals($client);
        $this->orders = new PosOrders($client);
        $this->shifts = new PosShifts($client);
        $this->customers = new PosCustomers($client);
        $this->transactions = new PosTransactions($client);
        $this->reports = new PosReports($client);
        $this->intents = new PosIntents($client);
        $this->printJobs = new PosPrintJobs($client);
        $this->printAssets = new PosPrintAssets($client);
    }
}
