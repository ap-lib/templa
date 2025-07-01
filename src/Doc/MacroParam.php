<?php

namespace AP\Templa\Doc;

readonly class MacroParam
{
    public function __construct(
        public bool   $allow,
        public ?array $list,
    )
    {
    }
}