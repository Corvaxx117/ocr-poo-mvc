<?php

namespace App\Exceptions;

class InternalServerErrorException extends \Exception implements HttpExceptionInterface
{
    protected int $statusCode = 500;
    protected string $message = "Une erreur interne est survenue.";

    public function getStatusCode (): int
    {
        return $this->statusCode;
    }
}
