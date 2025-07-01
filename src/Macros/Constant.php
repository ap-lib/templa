<?php

namespace AP\Templa\Macros;

use AP\Templa\Macros\Helper\ParamNoAllowed;
use AP\Templa\MacrosInterface;

readonly class Constant implements MacrosInterface
{
    use ParamNoAllowed;

    public function __construct(
        public string|int|float|bool|null $value,
        public string                     $out_type,
        public string                     $docDetails = "",
    )
    {
    }

    public function normalizeValue(?string $param, string $name, string $macro): string|int|float|bool|null
    {
        return $this->value;
    }

    public function getOutType(): string
    {
        return $this->out_type;
    }

    public function getDocDetails(): string
    {
        return $this->docDetails;
    }
}