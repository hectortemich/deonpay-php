<?php

declare(strict_types=1);

namespace DeonPay\Tests;

use DeonPay\Exception\DeonPayException;
use DeonPay\Exception\SignatureVerificationException;
use DeonPay\Webhook;
use PHPUnit\Framework\TestCase;

final class WebhookTest extends TestCase
{
    private const SECRET = 'whsec_test';
    private const PAYLOAD = '{"id":"evt_1","event":"link.payment.completed","type":"link","source":"dashboard","created_at":"2026-03-01T12:00:00Z","data":{"transaction":{"id":"t1","amount":50000}}}';

    private function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, self::SECRET);
    }

    public function testVerifySignatureValid(): void
    {
        $this->assertTrue(Webhook::verifySignature(self::PAYLOAD, $this->sign(self::PAYLOAD), self::SECRET));
    }

    public function testVerifySignatureIgnoresCaseAndWhitespace(): void
    {
        $sig = '  ' . strtoupper($this->sign(self::PAYLOAD)) . " \n";
        $this->assertTrue(Webhook::verifySignature(self::PAYLOAD, $sig, self::SECRET));
    }

    public function testVerifySignatureWrongSecretOrPayload(): void
    {
        $sig = $this->sign(self::PAYLOAD);
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD, $sig, 'otro_secreto'));
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD . ' ', $sig, self::SECRET));
    }

    public function testVerifySignatureEmpty(): void
    {
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD, '', self::SECRET));
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD, '   ', self::SECRET));
    }

    public function testConstructEventReturnsDecodedEvent(): void
    {
        $event = Webhook::constructEvent(self::PAYLOAD, $this->sign(self::PAYLOAD), self::SECRET);
        $this->assertSame('evt_1', $event['id']);
        $this->assertSame('link.payment.completed', $event['event']);
        $this->assertSame(50000, $event['data']['transaction']['amount']);
    }

    public function testConstructEventThrowsOnBadSignature(): void
    {
        try {
            Webhook::constructEvent(self::PAYLOAD, 'deadbeef', self::SECRET);
            $this->fail('Debió lanzar SignatureVerificationException');
        } catch (SignatureVerificationException $e) {
            $this->assertInstanceOf(DeonPayException::class, $e);
        }
    }

    private function signTs(string $payload, int $t, string $secret = self::SECRET): string
    {
        return hash_hmac('sha256', $t . '.' . $payload, $secret);
    }

    public function testTimestampedSignatureValid(): void
    {
        $t = 1800000000;
        $h = "t=$t,v1=" . $this->signTs(self::PAYLOAD, $t);
        $this->assertTrue(Webhook::verifySignature(self::PAYLOAD, $h, self::SECRET, 300, $t + 10));
        $upper = " t=$t , v1=" . strtoupper($this->signTs(self::PAYLOAD, $t)) . ' ';
        $this->assertTrue(Webhook::verifySignature(self::PAYLOAD, $upper, self::SECRET, 300, $t));
        $unknown = "t=$t,v0=zzz,v1=" . $this->signTs(self::PAYLOAD, $t);
        $this->assertTrue(Webhook::verifySignature(self::PAYLOAD, $unknown, self::SECRET, 300, $t));
    }

    public function testRotationAcceptsAnyV1(): void
    {
        $t = 1800000000;
        $h = "t=$t,v1=" . $this->signTs(self::PAYLOAD, $t, 'nuevo') . ',v1=' . $this->signTs(self::PAYLOAD, $t);
        $this->assertTrue(Webhook::verifySignature(self::PAYLOAD, $h, self::SECRET, 300, $t));
        $bad = "t=$t,v1=" . $this->signTs(self::PAYLOAD, $t, 'a') . ',v1=' . $this->signTs(self::PAYLOAD, $t, 'b');
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD, $bad, self::SECRET, 300, $t));
    }

    public function testTolerance(): void
    {
        $t = 1800000000;
        $h = "t=$t,v1=" . $this->signTs(self::PAYLOAD, $t);
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD, $h, self::SECRET, 300, $t + 301));
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD, $h, self::SECRET, 300, $t - 301));
        $this->assertTrue(Webhook::verifySignature(self::PAYLOAD, $h, self::SECRET, 300, $t + 300));
        $this->assertTrue(Webhook::verifySignature(self::PAYLOAD, $h, self::SECRET, 600, $t + 301));
        $this->assertTrue(Webhook::verifySignature(self::PAYLOAD, $h, self::SECRET, 0, $t + 999999));
    }

    public function testTamperedOrMalformedTimestamped(): void
    {
        $t = 1800000000;
        $sig = $this->signTs(self::PAYLOAD, $t);
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD . ' ', "t=$t,v1=$sig", self::SECRET, 300, $t));
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD, "t=$t,v1=$sig", 'otro', 300, $t));
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD, "t=abc,v1=$sig", self::SECRET, 300, $t));
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD, "t=$t,v1=", self::SECRET, 300, $t));
        $this->assertFalse(Webhook::verifySignature(self::PAYLOAD, 't=,v1', self::SECRET, 300, $t));
    }

    public function testGenerateTestHeaderRoundTrip(): void
    {
        $h = Webhook::generateTestHeader(self::PAYLOAD, self::SECRET);
        $this->assertMatchesRegularExpression('/^t=\d+,v1=[0-9a-f]{64}$/', $h);
        $this->assertSame('evt_1', Webhook::constructEvent(self::PAYLOAD, $h, self::SECRET)['id']);
        $this->assertSame(
            't=1800000000,v1=' . $this->signTs(self::PAYLOAD, 1800000000),
            Webhook::generateTestHeader(self::PAYLOAD, self::SECRET, 1800000000)
        );
    }

    public function testConstructEventDistinguishesFailures(): void
    {
        $h = Webhook::generateTestHeader(self::PAYLOAD, self::SECRET);
        try {
            Webhook::constructEvent(self::PAYLOAD . 'x', $h, self::SECRET);
            $this->fail('Debió lanzar');
        } catch (SignatureVerificationException $e) {
            $this->assertStringContainsString('no coincide', $e->getMessage());
        }
        $old = Webhook::generateTestHeader(self::PAYLOAD, self::SECRET, 1000);
        try {
            Webhook::constructEvent(self::PAYLOAD, $old, self::SECRET);
            $this->fail('Debió lanzar');
        } catch (SignatureVerificationException $e) {
            $this->assertStringContainsString('tolerancia', $e->getMessage());
        }
        $this->assertSame('evt_1', Webhook::constructEvent(self::PAYLOAD, $old, self::SECRET, 0)['id']);
    }
}
