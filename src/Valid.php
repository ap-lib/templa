<?php

namespace AP\Templa;

readonly class Valid
{
    public function __construct(
        public array $used_macros_names,
    )
    {
    }
}