<?php

namespace AP\Templa\Modifier;

use AP\Templa\ModifierInterface;

class Base64 implements ModifierInterface
{
    public function modify(string|int|float|bool|null $value): string
    {
        return base64_encode((string)$value);
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
        return "Unpadded base64 encoding, as defined in RFC 4648 section 3.2.";
    }
}