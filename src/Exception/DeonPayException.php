<?php

declare(strict_types=1);

namespace DeonPay\Exception;

use Throwable;

/** Excepción base de todos los errores del SDK. */
class DeonPayException extends \Exception
{
    /**
     * @param array<string,string> $headers Cabeceras de respuesta (nombres en minúsculas).
     */
    public function __construct(
        string $message = '',
        private readonly ?int $httpStatus = null,
        private readonly ?string $errorCode = null,
        private readonly ?string $body = null,
        private readonly array $headers = [],
        private readonly ?string $requestId = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    /** Código de error de la API (p. ej. "unauthorized"), si lo hubo. */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /** Cuerpo crudo de la respuesta de error. */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /** @return array<string,string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}
