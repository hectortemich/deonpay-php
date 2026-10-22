<?php
// Generado por scripts/openapi/gen-sdks.ts desde el spec OpenAPI. NO editar a mano.

declare(strict_types=1);

namespace DeonPay\Resources;

/** Recursos de primer nivel de DeonPayClient ($client->paymentLinks, $client->pos->orders...). */
trait HasResources
{
    public readonly PaymentLinks $paymentLinks;
    public readonly CheckoutSessions $checkoutSessions;
    public readonly Products $products;
    public readonly Transactions $transactions;
    public readonly BalanceTransactions $balanceTransactions;
    public readonly Subscriptions $subscriptions;
    public readonly CustomerSubscriptions $customerSubscriptions;
    public readonly Customers $customers;
    public readonly Metrics $metrics;
    public readonly BulkRefunds $bulkRefunds;
    public readonly Branches $branches;
    public readonly PaymentRules $paymentRules;
    public readonly Authorizations $authorizations;
    public readonly PaymentIntents $paymentIntents;
    public readonly Invoices $invoices;
    public readonly Catalog $catalog;
    public readonly Reports $reports;
    public readonly WebhookEndpoints $webhookEndpoints;
    public readonly Events $events;
    public readonly Pos $pos;

    private function attachResources(): void
    {
        $this->paymentLinks = new PaymentLinks($this);
        $this->checkoutSessions = new CheckoutSessions($this);
        $this->products = new Products($this);
        $this->transactions = new Transactions($this);
        $this->balanceTransactions = new BalanceTransactions($this);
        $this->subscriptions = new Subscriptions($this);
        $this->customerSubscriptions = new CustomerSubscriptions($this);
        $this->customers = new Customers($this);
        $this->metrics = new Metrics($this);
        $this->bulkRefunds = new BulkRefunds($this);
        $this->branches = new Branches($this);
        $this->paymentRules = new PaymentRules($this);
        $this->authorizations = new Authorizations($this);
        $this->paymentIntents = new PaymentIntents($this);
        $this->invoices = new Invoices($this);
        $this->catalog = new Catalog($this);
        $this->reports = new Reports($this);
        $this->webhookEndpoints = new WebhookEndpoints($this);
        $this->events = new Events($this);
        $this->pos = new Pos($this);
    }
}
