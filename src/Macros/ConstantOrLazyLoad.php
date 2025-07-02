<?php

namespace AP\Templa\Macros;

use AP\Logger\Log;
use AP\Templa\Macros\Helper\ParamNoAllowed;
use AP\Templa\MacrosInterface;
use Closure;
use RuntimeException;

readonly class ConstantOrLazyLoad implements MacrosInterface
{
    use ParamNoAllowed;

    /**
     * @param Closure|string|array|int|float|bool|null $value_or_callable
     * @param string $out_type
     * @param string $docDetails
     */
    public function __construct(
        public Closure|string|array|int|float|bool|null $value_or_callable,
        public string                                   $out_type,
        public string                                   $docDetails = "",
    )
    {
        if (is_array($this->value_or_callable) && !is_callable($this->value_or_callable)) {
            throw new RuntimeException('no callable');
        }
    }

    public function normalizeValue(?string $param, string $name, string $macro): string|int|float|bool|null
    {
        if (is_callable($this->value_or_callable)) {
            $value = ($this->value_or_callable)($param, $name, $macro);
            if (is_string($value) || is_int($value) || is_float($value) || is_bool($value) || is_null($value)) {
                return $value;
            }
            Log::warn(
                "callable function result must be string|int|float|bool|null",
                [
                    "macro" => $macro
                ]
            );
            return null;
        }

        return $this->value_or_callable;
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