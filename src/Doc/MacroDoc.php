<?php

namespace AP\Templa\Doc;

readonly class MacroDoc
{
    public function __construct(
        public string     $name,
        public string     $type,
        public MacroParam $param,
        public string     $details,
    )
    {
    }
}