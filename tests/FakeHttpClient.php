<?php

declare(strict_types=1);

namespace DeonPay\Tests;

use DeonPay\HttpClient\HttpClientInterface;

/** Transporte falso: devuelve respuestas en cola (o lanza Throwable) y registra las peticiones. */
final class FakeHttpClient implements HttpClientInterface
{
    /** @var list<array{method:string,url:string,headers:array<string,string>,body:?string,timeout:float}> */
    public array $requests = [];

    /** @var list<array{status:int,headers:array<string,string>,body:string}|\Throwable> */
    private array $queue = [];

    /**
     * @param array<string,string> $headers
     */
    public function queue(int $status, string $body = '', array $headers = []): self
    {
        $this->queue[] = ['status' => $status, 'headers' => $headers, 'body' => $body];
        return $this;
    }

    public function queueException(\Throwable $e): self
    {
        $this->queue[] = $e;
        return $this;
    }

    public function send(string $method, string $url, array $headers, ?string $body, float $timeout): array
    {
        $this->requests[] = [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
            'timeout' => $timeout,
        ];
        if ($this->queue === []) {
            throw new \RuntimeException('FakeHttpClient: no hay respuestas en cola');
        }
        $next = array_shift($this->queue);
        if ($next instanceof \Throwable) {
            throw $next;
        }
        return $next;
    }
}
