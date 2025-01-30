<?php

namespace App\Exceptions;

class ConflictException extends \Exception implements HttpExceptionInterface
{
    protected int $statusCode = 409;
    protected string $message = "Conflit avec une ressource existante.";

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
