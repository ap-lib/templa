<?php

namespace AP\Templa\Modifier;

use AP\Templa\ModifierInterface;

class ToLower implements ModifierInterface
{
    public function modify(string|int|float|bool|null $value): string
    {
        return mb_strtolower((string)$value);
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
        return "Converts the value to lowercase string";
    }
}