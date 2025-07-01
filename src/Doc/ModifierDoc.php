<?php

namespace AP\Templa\Doc;

readonly class ModifierDoc
{
    public function __construct(
        public string $name,
        public string $in_type,
        public string $out_type,
        public string $details,
    )
    {
    }
}