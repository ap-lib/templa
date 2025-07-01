<?php

namespace AP\Templa\Doc;

readonly class Doc
{
    /**
     * @param array<string, MacroDoc> $macros
     * @param array<string, ModifierDoc> $modifiers
     */
    public function __construct(
        public array $macros,
        public array $modifiers,
    )
    {
    }
}