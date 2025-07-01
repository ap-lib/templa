<?php

namespace AP\Templa;

interface ModifierInterface
{
    public function modify(string $value): string|int|float|bool|null;

    public function getInType(): string;

    public function getOutType(): string;

    public function getDetails(): string;
}