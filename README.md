# deonpay-php

SDK oficial de PHP para la API de [DeonPay](https://www.deonpay.mx). Requiere PHP 8.1 o superior con `ext-curl` y `ext-json`; no tiene otras dependencias.

> Todos los montos van en **centavos** (entero). `$500.00 MXN` = `50000`.

## Instalación

```bash
composer require deonpay/deonpay-php
```

## Crear el cliente

Crea un token de pruebas (`dp_test_...`) en el dashboard, en Desarrolladores → API Tokens, desde la vista de Pruebas. El sandbox es gratis en cualquier plan.

```php
<?php
require 'vendor/autoload.php';

$deonpay = new DeonPay\DeonPayClient(getenv('DEONPAY_API_KEY'));
```

## Ejemplos

### Crear una checkout session

```php
$session = $deonpay->checkoutSessions->create([
    'line_items' => [
        ['name' => 'Camiseta', 'quantity' => 1, 'unit_amount' => 50000],
    ],
    'success_url' => 'https://mi-app.com/pago-exitoso',
    'cancel_url' => 'https://mi-app.com/pago-cancelado',
]);

header('Location: ' . $session['url']);
```

### Listar transacciones de una sucursal

```php
$txs = $deonpay->transactions->list([
    'branch_id' => 'd290f1ee-6c54-4b01-90e6-d701748f0851',
    'channel' => ['terminal', 'link'], // se envía como channel=terminal,link
    'limit' => 20,
]);

foreach ($txs['data'] as $tx) {
    echo $tx['id'], ' ', $tx['amount'], PHP_EOL;
}
```

### Reembolso con idempotency_key

```php
$refund = $deonpay->transactions->refund(
    'txn_123',
    ['amount' => 25000],
    ['idempotency_key' => 'reembolso-pedido-981'],
);
```

### Crear un payment intent

```php
$intent = $deonpay->paymentIntents->create([
    'amount' => 129900,
    'currency' => 'MXN',
    'description' => 'Pedido 981',
]);
```

Las operaciones de creación y cobro envían un `Idempotency-Key` automático (UUID v4) si no pasas el tuyo. La misma clave se reutiliza en todos los reintentos de esa llamada.

## Manejo de errores

Todas las excepciones heredan de `DeonPay\Exception\DeonPayException`:

| Estatus | Excepción |
|---|---|
| 401 | `AuthenticationException` |
| 403 | `PermissionDeniedException` |
| 404 | `NotFoundException` |
| 400 / 409 / 422 | `InvalidRequestException` |
| 429 | `RateLimitException` |
| 5xx | `ApiException` |
| Red / timeout | `ApiConnectionException` (sin estatus HTTP) |

```php
use DeonPay\Exception\DeonPayException;
use DeonPay\Exception\NotFoundException;

try {
    $deonpay->paymentLinks->retrieve('no-existe');
} catch (NotFoundException $e) {
    echo 'No encontrado: ', $e->getMessage();
} catch (DeonPayException $e) {
    echo $e->getHttpStatus(), ' ', $e->getErrorCode(), ' ', $e->getMessage();
    echo 'request_id: ', $e->getRequestId(); // el Request-Id (req_...) a compartir con soporte
    // $e->getBody() devuelve el cuerpo crudo; $e->getHeaders() las cabeceras.
}
```

## Verificar webhooks

DeonPay envía la cabecera **`DeonPay-Signature`** (recomendada): `t=<unix>,v1=<hex>`, donde `v1` es el HMAC-SHA256 hex de `"{t}.{cuerpo}"` con el secreto del endpoint. Usa el cuerpo **tal cual llegó**, sin decodificar y volver a codificar.

```php
use DeonPay\Webhook;
use DeonPay\Exception\SignatureVerificationException;

$payload = file_get_contents('php://input');
// Primero DeonPay-Signature; si no viene, cae a la cabecera legada X-DeonPay-Signature.
$signature = $_SERVER['HTTP_DEONPAY_SIGNATURE'] ?? $_SERVER['HTTP_X_DEONPAY_SIGNATURE'] ?? '';

try {
    $event = Webhook::constructEvent($payload, $signature, getenv('DEONPAY_WEBHOOK_SECRET'));
} catch (SignatureVerificationException $e) {
    http_response_code(400);
    exit;
}

if ($event['event'] === 'link.payment.completed') {
    // ...
}
http_response_code(200);
```

- **Tolerancia de 5 minutos**: se rechaza si el timestamp `t` difiere más de 300 s de la hora actual (anti-replay). Ajusta con el 4.º argumento (`$tolerance`); `0` desactiva el chequeo.
- **Rotación de secreto**: durante el periodo de gracia la cabecera trae dos `v1` (secreto nuevo y anterior); basta con que alguno coincida. Las llaves desconocidas se ignoran.
- **Cabecera legada** `X-DeonPay-Signature`: HMAC-SHA256 hex del cuerpo crudo, solo con el secreto vigente y sin timestamp. Las mismas funciones la siguen aceptando. `X-DeonPay-Event-Id` trae el id del evento (`evt_` + 24 hex) y sirve para deduplicar.
- `Webhook::verifySignature($payload, $header, $secret, int $tolerance = 300, ?int $now = null): bool` nunca lanza.

Para probar tu handler, genera una cabecera válida:

```php
$header = Webhook::generateTestHeader($payload, $secret); // "t=1760000000,v1=ab12..."
```

## Configuración

```php
$deonpay = new DeonPay\DeonPayClient('dp_test_...', [
    'base_url' => 'https://uat.deonpay.mx/api', // default: https://www.deonpay.mx/api
    'timeout' => 30,       // segundos (default 30)
    'max_retries' => 2,    // reintentos adicionales (default 2)
]);

// Por llamada:
$deonpay->transactions->list(null, ['timeout' => 5]);
```

Se reintenta (con espera exponencial y respeto de `Retry-After`, tope 20 s) ante error de red, 429, 500, 502, 503 y 504, pero solo en peticiones GET o con `Idempotency-Key`, para no duplicar nunca un cobro.

Para pruebas puedes inyectar tu propio transporte con la clave `http_client` (una implementación de `DeonPay\HttpClient\HttpClientInterface`) y una función `sleep`.

## Versión de la API

La API usa versiones por fecha. El SDK fija la versión contra la que se construyó (`DeonPayClient::API_VERSION`, hoy `2026-10-22`) y la manda siempre en el header `DeonPay-Version`, así tu integración no cambia sola cuando salga una versión nueva. Para usar otra:

```php
$deonpay = new DeonPay\DeonPayClient('dp_test_...', ['api_version' => '2026-10-22']);
$deonpay->transactions->list(null, ['api_version' => '2026-10-22']); // solo esta llamada
```

Una versión desconocida responde `400` con `code = invalid_api_version` (`InvalidRequestException`). El servidor devuelve la versión usada en el header de respuesta `DeonPay-Version`.

## Desarrollo

```bash
composer install
vendor/bin/phpunit
```

Las clases de `src/Resources/` se generan desde el spec OpenAPI con `npm run sdks:generate` en el repositorio de la plataforma; no las edites a mano.

## Licencia

MIT
