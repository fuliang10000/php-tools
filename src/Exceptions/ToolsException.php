<?php

namespace Fuliang\PhpTools\Exceptions;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Fuliang\PhpTools\Constants\ErrorCode;
use Throwable;

class ToolsException extends \Exception implements PluginInterface
{
    public function __construct(int $code = ErrorCode::ERROR_400, string $message = "", Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        // TODO: Implement activate() method.
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // TODO: Implement deactivate() method.
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // TODO: Implement uninstall() method.
    }
}