<?php

namespace AP\Templa;

use AP\ErrorNode\Error;
use AP\ErrorNode\Errors;
use AP\Logger\Log;
use AP\Templa\Doc\Doc;
use AP\Templa\Doc\MacroDoc;
use AP\Templa\Doc\MacroParam;
use AP\Templa\Doc\ModifierDoc;
use AP\Templa\Macros\Nothing;
use RuntimeException;

class TemplaEngine
{
    /**
     * @var array<string, MacrosInterface>
     */
    private array $macros;

    /**
     * @var array<string, ModifierInterface>
     */
    private array $modifiers = [];

    public function __construct(
        public ?ModifierInterface $final_strings_modifier = null,
        public MacrosInterface    $not_found_macro = new Nothing()
    )
    {

    }

    public function addMacros(string $name, MacrosInterface $macros): static
    {
        if (isset($this->macros[$name])) {
            throw new RuntimeException("duplicate macros name: $name");
        }
        $this->macros[$name] = $macros;
        return $this;
    }

    public function addModifier(string $name, ModifierInterface $modifier): static
    {
        if (isset($this->modifiers[$name])) {
            throw new RuntimeException("duplicate modifiers name: $name");
        }
        $this->modifiers[$name] = $modifier;
        return $this;
    }

    /**
     * @return array<MacroDoc>
     */
    public function getMacrosDocumentation(): array
    {
        $result = [];
        foreach ($this->macros as $name => $macro) {
            $result[] = new MacroDoc(
                $name,
                $macro->getOutType(),
                new MacroParam(
                    $macro->haveParam(),
                    $macro->paramAllowOnly(),
                ),
                $macro->getDocDetails(),
            );
        }
        return $result;
    }

    /**
     * @return array<ModifierDoc>
     */
    public function getModifiersDocumentation(): array
    {
        $result = [];
        foreach ($this->modifiers as $name => $modifier) {
            $result[] = new ModifierDoc(
                $name,
                $modifier->getInType(),
                $modifier->getOutType(),
                $modifier->getDetails(),
            );
        }
        return $result;
    }

    public function getDocumentation(): Doc
    {
        return new Doc(
            $this->getMacrosDocumentation(),
            $this->getModifiersDocumentation(),
        );
    }


    /**
     * supports:
     * {{ macro }} - no params
     * {{ macro: param }} - with params
     * {{ macro | modifier }} - no params with modifier
     * {{ macro | modifier1 | modifierN }} - no params with modifier2
     * {{ macro: param | modifier }} - with params and modifier
     * {{ macro: param | modifier1 | modifierN }} - with params and modifier2
     */
    private const string PATTERN = '\{\{\s*(\w+)(?:\s*:\s*([^}|]+))?(?:\s*\|\s*([^}]+))?\s*}}';

    public function validateArray(array $array, array $required_macros = []): Valid|Errors
    {
        $errors            = [];
        $used_macros_names = [];

        foreach ($array as $key => $value) {
            $res = match (true) {
                is_string($value) => $this->validateString($value),
                is_array($value) => $this->validateArray($value),
                default => null
            };

            if ($res instanceof Valid) {
                foreach ($res->used_macros_names as $used_macros_name) {
                    $used_macros_names[$used_macros_name] = $used_macros_name;
                }
            } elseif ($res instanceof Errors) {
                foreach ($res->getErrors() as $error) {
                    $error->path = array_merge([$key], $error->path);
                    $errors[]    = $error;
                }
            }
        }

        if (empty($errors) && !empty($required_macros)) {
            foreach ($required_macros as $required_macro) {
                if (!key_exists($required_macro, $used_macros_names)) {
                    return Errors::one("required macros: $required_macro not found");
                }
            }
        }

        return empty($errors)
            ? new Valid($used_macros_names)
            : new Errors($errors);
    }

    public function validateString(string $string, array $required_macros = []): Valid|Errors
    {
        $errors            = [];
        $used_macros_names = [];

        if (preg_match_all("/" . self::PATTERN . "/", $string, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name   = $match[1];
                $param  = isset($match[2]) ? trim($match[2]) : null;
                $macro  = $this->macros[$name] ?? $this->not_found_macro;
                $valRes = $macro->validateParam($param);
                if ($valRes instanceof Error) {
                    $valRes->path = [$name];
                    $errors[]     = $valRes;
                }
                $used_macros_names[$name] = $name;
            }
        }

        // check required macros
        if (empty($errors) && !empty($required_macros)) {
            foreach ($required_macros as $required_macro) {
                if (!key_exists($required_macro, $used_macros_names)) {
                    return Errors::one("required macro: $required_macro not found");
                }
            }
        }

        return empty($errors)
            ? new Valid($used_macros_names)
            : new Errors($errors);
    }

    public function array(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $result[$key] = $this->string($value);
            } elseif (is_array($value)) {
                $result[$key] = $this->array($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }


    public function string(string $template): string|int|float|bool|null
    {
        if (preg_match("/^" . self::PATTERN . "$/", $template, $matches)) {
            return $this->makeCallback(
                $matches,
                false
            );
        }

        return preg_replace_callback(
            "/" . self::PATTERN . "/",
            [$this, "makeCallback"],
            $template
        );
    }


    private function makeCallback(
        array $matches,
        bool  $to_string = true
    ): string|int|float|bool|null
    {
        $name  = $matches[1];
        $macro = $this->macros[$name] ?? $this->not_found_macro;
        $param = isset($matches[2]) ? trim($matches[2]) : null;
        $mods  = isset($matches[3]) ? trim($matches[3]) : null;

        $value = $macro->normalizeValue(
            $param,
            $name,
            $matches[0]
        );

        if (!is_null($mods)) {
            $modNames = array_map('trim', explode('|', $mods));
            foreach ($modNames as $modName) {
                $optional = str_starts_with($modName, '?');
                $modName  = ltrim($modName, '?');
                if (isset($this->modifiers[$modName])) {
                    $value = $optional && $value === null
                        ? null
                        : $this->modifiers[$modName]->modify($value);
                } else {
                    Log::warn(
                        message: "modifier not found: " . $modName,
                        context: [
                            "macro" => $matches[0],
                        ],
                        module: "ap:templa"
                    );
                }
            }
        }

        $value = is_string($value) && $this->final_strings_modifier instanceof ModifierInterface
            ? $this->final_strings_modifier->modify($value)
            : $value;

        return $to_string
            ? (string)$value
            : $value;
    }
}