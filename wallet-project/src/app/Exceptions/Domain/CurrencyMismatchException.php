<?php

namespace App\Exceptions\Domain;

use Exception;

class CurrencyMismatchException extends Exception
{
    protected $message = 'Wallet currency mismatch.';
}
