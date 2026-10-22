<?php

declare(strict_types=1);

namespace DeonPay\Tests;

use DeonPay\DeonPayClient;
use DeonPay\Exception\ApiConnectionException;
use DeonPay\Exception\ApiException;
use DeonPay\Exception\AuthenticationException;
use DeonPay\Exception\DeonPayException;
use DeonPay\Exception\InvalidRequestException;
use DeonPay\Exception\NotFoundException;
use DeonPay\Exception\PermissionDeniedException;
use DeonPay\Exception\RateLimitException;
use PHPUnit\Framework\TestCase;

final class DeonPayClientTest extends TestCase
{
    private FakeHttpClient $http;
    /** @var list<float> */
    private array $sleeps = [];

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->sleeps = [];
    }

    /** @param array<string,mixed> $config */
    private function client(array $config = []): DeonPayClient
    {
        return new DeonPayClient('dp_test_abc', $config + [
            'http_client' => $this->http,
            'sleep' => function (float $s): void {
                $this->sleeps[] = $s;
            },
        ]);
    }

    public function testAuthorizationAndUserAgentHeaders(): void
    {
        $this->http->queue(200, '{"data":[]}');
        $this->client()->paymentLinks->list();

        $req = $this->http->requests[0];
        $this->assertSame('GET', $req['method']);
        $this->assertSame('https://www.deonpay.mx/api/v1/links', $req['url']);
        $this->assertSame('Bearer dp_test_abc', $req['headers']['Authorization']);
        $this->assertSame('application/json', $req['headers']['Accept']);
        $this->assertStringStartsWith('deonpay-php/0.4.0 (PHP ', $req['headers']['User-Agent']);
        $this->assertArrayNotHasKey('Content-Type', $req['headers']);
        $this->assertNull($req['body']);
    }

    public function testApiVersionHeaderDefaultAndOverrides(): void
    {
        $this->assertSame('2026-10-22', DeonPayClient::API_VERSION);

        $this->http->queue(200, '{}')->queue(200, '{}')->queue(200, '{}');
        $this->client()->paymentLinks->retrieve('x');
        $this->client(['api_version' => '2026-01-01'])->paymentLinks->retrieve('x');
        $this->client(['api_version' => '2026-01-01'])->paymentLinks->retrieve('x', ['api_version' => '2026-02-02']);

        $this->assertSame('2026-10-22', $this->http->requests[0]['headers']['DeonPay-Version']);
        $this->assertSame('2026-01-01', $this->http->requests[1]['headers']['DeonPay-Version']);
        $this->assertSame('2026-02-02', $this->http->requests[2]['headers']['DeonPay-Version']);
    }

    public function testInvalidApiVersionMapsToInvalidRequest(): void
    {
        $this->http->queue(400, '{"error":{"code":"invalid_api_version","message":"Versión desconocida"}}');
        try {
            $this->client()->paymentLinks->retrieve('x');
            $this->fail('Se esperaba InvalidRequestException');
        } catch (InvalidRequestException $e) {
            $this->assertSame('invalid_api_version', $e->getErrorCode());
        }
    }

    public function testEmptyApiKeyThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DeonPayClient('');
    }

    public function testBaseUrlTrailingSlashStripped(): void
    {
        $this->http->queue(200, '{}');
        $this->client(['base_url' => 'https://uat.deonpay.mx/api/'])->paymentLinks->retrieve('x');
        $this->assertSame('https://uat.deonpay.mx/api/v1/links/x', $this->http->requests[0]['url']);
    }

    public function testQueryOmitsNullBoolsAndCommaLists(): void
    {
        $this->http->queue(200, '{"data":[]}');
        $this->client()->transactions->list([
            'status' => null,
            'has_more' => true,
            'archived' => false,
            'channel' => ['terminal', 'link'],
            'branch_id' => 'b1',
            'limit' => 10,
        ]);

        $url = $this->http->requests[0]['url'];
        $this->assertStringContainsString('has_more=true', $url);
        $this->assertStringContainsString('archived=false', $url);
        $this->assertStringContainsString('channel=terminal%2Clink', $url);
        $this->assertStringContainsString('branch_id=b1', $url);
        $this->assertStringContainsString('limit=10', $url);
        $this->assertStringNotContainsString('status', $url);
    }

    public function testJsonBodyAndContentType(): void
    {
        $this->http->queue(201, '{"id":"l1"}');
        $res = $this->client()->paymentLinks->create(['name' => 'Café', 'amount' => 5000]);

        $req = $this->http->requests[0];
        $this->assertSame('POST', $req['method']);
        $this->assertSame('application/json', $req['headers']['Content-Type']);
        $this->assertSame(['name' => 'Café', 'amount' => 5000], json_decode((string) $req['body'], true));
        $this->assertSame(['id' => 'l1'], $res);
    }

    public function testPost500IsNotRetried(): void
    {
        // El efecto (cobro, factura) pudo ocurrir antes del 500.
        $this->http->queue(500, '{"error":{"code":"internal_error","message":"x"}}')->queue(200, '{"id":"l1"}');
        try {
            $this->client()->paymentLinks->create(['name' => 'a']);
            $this->fail('Se esperaba una excepción');
        } catch (ApiException $e) {
            $this->assertSame(500, $e->getHttpStatus());
        }
        $this->assertCount(1, $this->http->requests);
    }

    public function testIdempotencyKeyGeneratedAndReusedAcrossRetries(): void
    {
        $this->http->queue(503, '{"error":{"code":"boom","message":"x"}}')->queue(200, '{"id":"l1"}');
        $res = $this->client()->paymentLinks->create(['name' => 'a']);

        $this->assertSame(['id' => 'l1'], $res);
        $this->assertCount(2, $this->http->requests);
        $k1 = $this->http->requests[0]['headers']['Idempotency-Key'];
        $k2 = $this->http->requests[1]['headers']['Idempotency-Key'];
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $k1
        );
        $this->assertSame($k1, $k2);
        $this->assertCount(1, $this->sleeps);
    }

    public function testUserIdempotencyKeyIsUsed(): void
    {
        $this->http->queue(200, '{}');
        $this->client()->transactions->refund('t1', ['amount' => 100], ['idempotency_key' => 'mi-clave']);
        $this->assertSame('mi-clave', $this->http->requests[0]['headers']['Idempotency-Key']);
    }

    public function testNoRetryOnPostWithoutIdempotencyKey(): void
    {
        // update (PATCH) no está marcado como idempotente: no debe reintentarse.
        $this->http->queue(500, 'boom')->queue(200, '{}');
        try {
            $this->client()->paymentLinks->update('l1', ['name' => 'x']);
            $this->fail('Debió lanzar ApiException');
        } catch (ApiException $e) {
            $this->assertSame(500, $e->getHttpStatus());
        }
        $this->assertCount(1, $this->http->requests);
        $this->assertSame([], $this->sleeps);
    }

    public function testRetriesGetOnNetworkError(): void
    {
        $this->http->queueException(new ApiConnectionException('red'))->queue(200, '{"id":"l1"}');
        $res = $this->client()->paymentLinks->retrieve('l1');
        $this->assertSame(['id' => 'l1'], $res);
        $this->assertCount(2, $this->http->requests);
    }

    public function testNetworkErrorWithoutRetriesThrows(): void
    {
        $this->http->queueException(new ApiConnectionException('red'));
        $this->expectException(ApiConnectionException::class);
        $this->client(['max_retries' => 0])->paymentLinks->retrieve('l1');
    }

    public function testMaxRetriesExhausted(): void
    {
        $this->http->queue(503)->queue(503)->queue(503)->queue(200, '{}');
        try {
            $this->client(['max_retries' => 2])->paymentLinks->list();
            $this->fail('Debió lanzar ApiException');
        } catch (ApiException $e) {
            $this->assertSame(503, $e->getHttpStatus());
        }
        $this->assertCount(3, $this->http->requests);
    }

    public function testRetryAfterIsHonoredOn429(): void
    {
        $this->http->queue(429, '{"error":{"code":"rate_limited","message":"lento"}}', ['retry-after' => '3'])
            ->queue(200, '{"ok":true}');
        $res = $this->client()->paymentLinks->list();
        $this->assertSame(['ok' => true], $res);
        $this->assertSame([3.0], $this->sleeps);
    }

    public function testRetryAfterIsCappedAt20(): void
    {
        $this->http->queue(429, '', ['retry-after' => '999'])->queue(200, '{}');
        $this->client()->paymentLinks->list();
        $this->assertSame([20.0], $this->sleeps);
    }

    public function testBackoffWithoutRetryAfterIsWithinBounds(): void
    {
        $this->http->queue(502)->queue(200, '{}');
        $this->client()->paymentLinks->list();
        $this->assertCount(1, $this->sleeps);
        $this->assertGreaterThanOrEqual(0.5, $this->sleeps[0]);
        $this->assertLessThanOrEqual(0.75, $this->sleeps[0]);
    }

    public function testNotFoundMapsCodeAndMessage(): void
    {
        $this->http->queue(
            404,
            '{"error":{"code":"not_found","message":"No existe"}}',
            ['x-request-id' => 'req_123']
        );
        try {
            $this->client()->paymentLinks->retrieve('nope');
            $this->fail('Debió lanzar NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertSame(404, $e->getHttpStatus());
            $this->assertSame('not_found', $e->getErrorCode());
            $this->assertSame('No existe', $e->getMessage());
            $this->assertSame('req_123', $e->getRequestId());
            $this->assertSame('{"error":{"code":"not_found","message":"No existe"}}', $e->getBody());
            $this->assertSame('req_123', $e->getHeaders()['x-request-id']);
            $this->assertInstanceOf(DeonPayException::class, $e);
        }
    }

    public function testRequestIdPrefersHeaderThenBody(): void
    {
        $body = '{"error":{"code":"not_found","message":"m","request_id":"req_body"}}';
        $this->http->queue(404, $body, ['request-id' => 'req_hdr', 'x-vercel-id' => 'gru1::abc']);
        try {
            $this->client()->paymentLinks->retrieve('nope');
            $this->fail('Debió lanzar NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertSame('req_hdr', $e->getRequestId());
        }

        $this->http->queue(404, $body, ['x-vercel-id' => 'gru1::abc']);
        try {
            $this->client()->paymentLinks->retrieve('nope');
            $this->fail('Debió lanzar NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertSame('req_body', $e->getRequestId());
        }
    }

    public function testRequestIdFallsBackToVercelId(): void
    {
        $this->http->queue(404, '{}', ['x-vercel-id' => 'gru1::abc']);
        try {
            $this->client()->paymentLinks->retrieve('nope');
            $this->fail('Debió lanzar NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertSame('gru1::abc', $e->getRequestId());
        }
    }

    /** @return array<string,array{int,class-string}> */
    public static function statusProvider(): array
    {
        return [
            '401' => [401, AuthenticationException::class],
            '403' => [403, PermissionDeniedException::class],
            '400' => [400, InvalidRequestException::class],
            '409' => [409, InvalidRequestException::class],
            '422' => [422, InvalidRequestException::class],
            '429' => [429, RateLimitException::class],
            '500' => [500, ApiException::class],
        ];
    }

    /**
     * @dataProvider statusProvider
     * @param class-string $class
     */
    public function testStatusMapsToException(int $status, string $class): void
    {
        $this->http->queue($status, '{"error":{"code":"c","message":"m"}}');
        try {
            // max_retries 0 para que 429/500 no reintenten.
            $this->client(['max_retries' => 0])->paymentLinks->list();
            $this->fail('Debió lanzar excepción');
        } catch (DeonPayException $e) {
            $this->assertInstanceOf($class, $e);
            $this->assertSame($status, $e->getHttpStatus());
            $this->assertSame('c', $e->getErrorCode());
        }
    }

    public function testNonJsonErrorBecomesApiExceptionWithRawBody(): void
    {
        $this->http->queue(502, '<html>Bad Gateway</html>');
        try {
            $this->client(['max_retries' => 0])->paymentLinks->list();
            $this->fail('Debió lanzar ApiException');
        } catch (ApiException $e) {
            $this->assertSame(502, $e->getHttpStatus());
            $this->assertSame('<html>Bad Gateway</html>', $e->getBody());
            $this->assertNull($e->getErrorCode());
            $this->assertSame('Error HTTP 502', $e->getMessage());
        }
    }

    public function testBinaryResponseReturnsRawBytes(): void
    {
        $pdf = "%PDF-1.4\n\x00\x01\x02binary";
        $this->http->queue(200, $pdf, ['content-type' => 'application/pdf']);
        $res = $this->client()->invoices->pdf('inv1');
        $this->assertSame($pdf, $res);
        $this->assertSame('GET', $this->http->requests[0]['method']);
        $this->assertSame('https://www.deonpay.mx/api/v1/invoices/inv1/pdf', $this->http->requests[0]['url']);
    }

    public function testNoContentReturnsNull(): void
    {
        $this->http->queue(204, '');
        $this->assertNull($this->client()->paymentLinks->delete('l1'));
        $this->assertSame('DELETE', $this->http->requests[0]['method']);
    }

    public function testRetrieveResource(): void
    {
        $this->http->queue(200, '{"id":"abc"}');
        $res = $this->client()->paymentLinks->retrieve('abc');
        $this->assertSame(['id' => 'abc'], $res);
        $this->assertSame('GET', $this->http->requests[0]['method']);
        $this->assertSame('https://www.deonpay.mx/api/v1/links/abc', $this->http->requests[0]['url']);
    }

    public function testNestedResourcePosOrdersCharge(): void
    {
        $this->http->queue(200, '{"status":"paid"}');
        $res = $this->client()->pos->orders->charge('o1', ['amount' => 1000]);

        $req = $this->http->requests[0];
        $this->assertSame(['status' => 'paid'], $res);
        $this->assertSame('POST', $req['method']);
        $this->assertSame('https://www.deonpay.mx/api/v1/pos/orders/o1/charge', $req['url']);
        $this->assertArrayHasKey('Idempotency-Key', $req['headers']);
        $this->assertSame(['amount' => 1000], json_decode((string) $req['body'], true));
    }

    public function testPathIdIsUrlEncoded(): void
    {
        $this->http->queue(200, '{}');
        $this->client()->paymentLinks->retrieve('a/b c');
        $this->assertSame('https://www.deonpay.mx/api/v1/links/a%2Fb%20c', $this->http->requests[0]['url']);
    }

    public function testPerCallTimeoutOverride(): void
    {
        $this->http->queue(200, '{}')->queue(200, '{}');
        $c = $this->client(['timeout' => 12]);
        $c->paymentLinks->list();
        $c->paymentLinks->list(null, ['timeout' => 3]);
        $this->assertSame(12.0, $this->http->requests[0]['timeout']);
        $this->assertSame(3.0, $this->http->requests[1]['timeout']);
    }

    public function testInvalidJsonOnSuccessThrowsApiException(): void
    {
        $this->http->queue(200, 'no es json');
        $this->expectException(ApiException::class);
        $this->client()->paymentLinks->list();
    }
}
