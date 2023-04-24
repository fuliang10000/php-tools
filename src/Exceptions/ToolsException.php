<?php

namespace Fuliang\PhpTools\Exceptions;

use Composer\Composer;
use Composer\IO\IOInterface;
use Fuliang\PhpTools\Constants\ErrorCode;
use Throwable;

class ToolsException extends \Exception
{
    public function __construct(int $code = ErrorCode::ERROR_400, string $message = "", Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}