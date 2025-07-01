<?php

namespace AP\Templa;

use AP\ErrorNode\Error;

interface MacrosInterface
{
    public function haveParam(): bool;

    public function paramAllowOnly(): ?array;

    public function normalizeValue(?string $param, string $name, string $macro): string|int|float|bool|null;

    public function getDocDetails(): string;

    public function getOutType(): string;

    public function validateParam(?string $param): bool|Error;
}