<?php

namespace App\Exceptions;

class ForbiddenException extends \Exception implements HttpExceptionInterface
{
    protected int $statusCode = 403;
    protected string $message = "Accès interdit.";
    
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
