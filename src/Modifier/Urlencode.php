<?php

namespace AP\Templa\Modifier;

use AP\Templa\ModifierInterface;

class Urlencode implements ModifierInterface
{
    public function modify(string|int|float|bool|null $value): string
    {
        return urlencode((string)$value);
    }

    public function getInType(): string
    {
        return "string|int|float|bool|null";
    }

    public function getOutType(): string
    {
        return "string";
    }

    public function getDetails(): string
    {
        return "Encodes the value for safe use in URLs using standard URL encoding.";
    }
}