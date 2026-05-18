<?php

namespace App\Exceptions\Domain;

use Exception;

class InactiveWalletException extends Exception
{
    protected $message = 'Wallet is inactive.';
}
