<?php

namespace AP\Templa\Macros;

use AP\ErrorNode\Error;
use AP\Templa\MacrosInterface;

readonly class Nothing implements MacrosInterface
{
    public function normalizeValue(?string $param, string $name, string $macro): string
    {
        return $macro;
    }

    public function haveParam(): bool
    {
        return true;
    }

    public function paramAllowOnly(): ?array
    {
        return null;
    }

    public function getDocDetails(): string
    {
        return "No modification; uses the macro as-is.";
    }

    public function getOutType(): string
    {
        return "string";
    }

    public function validateParam(?string $param): bool|Error
    {
        return true;
    }
}