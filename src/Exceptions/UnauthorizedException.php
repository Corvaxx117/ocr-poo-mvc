<?php

namespace App\Exceptions;

class UnauthorizedException extends \Exception implements HttpExceptionInterface
{
    protected int $statusCode = 401;
    protected string $message = "Authentification requise.";

    public function getstatusCode(): int
    {
        return $this->statusCode;
    }
}
