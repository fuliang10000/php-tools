<?php

namespace Fuliang\PhpTools\Helper;

use Throwable;

class ToolsException extends \Exception
{
    public function __construct(int $code = ErrorCode::ERROR_400, string $message = "", Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}