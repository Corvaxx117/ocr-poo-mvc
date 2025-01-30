<?php

namespace App\Exceptions;

class ServiceUnavailableException extends \Exception implements HttpExceptionInterface
{
    protected int $statusCode = 503;
    protected string $message = "Service temporairement indisponible.";

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
