<?php

namespace App\Exceptions;

use RuntimeException;

class VotingException extends RuntimeException
{
    public function __construct(public readonly string $key)
    {
        parent::__construct($key);
    }
}
