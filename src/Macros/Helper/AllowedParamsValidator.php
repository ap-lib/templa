<?php

namespace AP\Templa\Macros\Helper;

use AP\ErrorNode\Error;

trait AllowedParamsValidator
{
    public function validateParam(?string $param): bool|Error
    {
        $allowed_params = $this->paramAllowOnly();

        if (in_array($param, $allowed_params)) {
            return true;
        }

        return new Error(
            empty($allowed_params)
                ? 'there are no available parameters'
                : "invalid param `$param`, allowed only: `" .
                implode(
                    "`, `",
                    $allowed_params
                ) . "`"
        );
    }
}