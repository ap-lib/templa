<?php

namespace AP\Templa\Modifier;

use AP\Templa\ModifierInterface;

class JsonSubString implements ModifierInterface
{
    public function modify(string|int|float|bool|null $value): string|int|float|bool|null
    {
        return is_string($value) ? substr(json_encode(
            $value,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ), 1, -1) : $value;
    }

    public function getInType(): string
    {
        return "string|int|float|bool|null";
    }

    public function getOutType(): string
    {
        return "string|int|float|bool|null";
    }

    public function getDetails(): string
    {
        return "Escapes the value for safe inclusion inside a JSON string";
    }
}