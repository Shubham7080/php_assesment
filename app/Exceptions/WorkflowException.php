<?php

namespace App\Exceptions;

use RuntimeException;

class WorkflowException extends RuntimeException
{
    public static function invalidTransition(string $from, string $action): self
    {
        return new self("Cannot {$action} a book in '{$from}' status.");
    }
}
