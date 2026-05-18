<?php

namespace App\Exceptions\Domain;

use Exception;

class InsufficientFundsException extends Exception
{
    protected $message = 'Insufficient wallet balance.';
}
