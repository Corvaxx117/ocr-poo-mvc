<?php

namespace App\Exceptions;

class UnsupportedMediaTypeException extends \Exception implements HttpExceptionInterface
{
    protected int $statusCode = 415;
    protected string $message = "Type de contenu non pris en charge.";

    public function getStatusCode(): int
    {
        return $this->statusCode; 
    }
}
