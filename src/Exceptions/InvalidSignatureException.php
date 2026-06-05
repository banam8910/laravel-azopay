<?php

namespace Ftech\AzoPay\Exceptions;

class InvalidSignatureException extends AzoPayException
{
    public static function reason(string $reason): self
    {
        return new self("AzoPay webhook signature verification failed: {$reason}.");
    }
}
