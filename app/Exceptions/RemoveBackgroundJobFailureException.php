<?php
namespace App\Exceptions;

use Exception;
use Throwable;

class RemoveBackgroundJobFailureException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}