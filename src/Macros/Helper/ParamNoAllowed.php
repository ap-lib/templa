<?php

namespace AP\Templa\Macros\Helper;

use AP\ErrorNode\Error;

trait ParamNoAllowed
{
    public function haveParam(): bool
    {
        return false;
    }

    public function validateParam(?string $param): bool|Error
    {
        return is_null($param)
            ? true
            : new Error('param no allowed');
    }

    public function paramAllowOnly(): ?array
    {
        return null;
    }
}