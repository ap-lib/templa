<?php

namespace AP\Templa\Macros\Helper;

use AP\ErrorNode\Error;

trait AllowedParamsValidator
{
    public function validateParam(?string $param): bool|Error
    {
        $allowed_params = $this->paramAllowOnly();
        return in_array($param, $allowed_params)
            ? true
            : new Error(
                "invalid param `$param`, allowed only: `" .
                implode(
                    "`, `",
                    $allowed_params
                ) . "`"
            );
    }
}