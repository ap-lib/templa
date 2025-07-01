# AP\Templa

AP\Templa is a lightweight, flexible PHP macro engine for safely injecting and transforming dynamic values inside arrays or strings, with built-in macro and modifier support.

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## Installation

```bash
composer require ap-lib/templa
```

## Features

* Define named macros with type-safe values
* Support for macro parameters
* Powerful modifier chain (e.g. `| string | base64 | upper`)
* Works with arrays and nested structures
* Macro documentation generation for transparency

## Requirements

* PHP 8.3 or higher

## Getting started

```php
use AP\\Templa\\TemplaEngine;
use AP\\Templa\\Macros\\Constant;
use AP\\Templa\\Modifier\\ToString;
use AP\\Templa\\Modifier\\Base64;
use AP\\Templa\\Modifier\\ToUpper;
use AP\\Templa\\Modifier\\ToLower;

// create engine
$template = new TemplaEngine();

// create macros
$template->addMacros("fruit", new Constant("orange", "string", "sold fruit name"));
$template->addMacros("price", new Constant(3.14, "float"));
$template->addMacros("email", new Constant("gagarin@cosmos.ru", "string|null", "seller's email"));
$template->addMacros("phone", new Constant(null, "string|null", "seller's phone"));

// register modifiers
$template->addModifier("string", new ToString());
$template->addModifier("base64", new Base64());
$template->addModifier("upper", new ToUpper());
$template->addModifier("lower", new ToLower());

```

## Using user-provided format:
```php
// user-defined input template
$webhook_format = [
    "what_fruit"                 => "{{ fruit }}",
    "price_origin"               => "{{ price }}",
    "price_str"                  => "{{ price | string }}",
    "price_and_postfix"          => "{{ price }} USD",
    "email"                      => "{{ email }}",
    "email_base64"               => "{{ email | base64 }}",
    "email_upper_base64"         => "{{ email | upper | base64 }}",
    "phone_base64_or_null"       => "{{ phone | ?base64 }}",
    "phone_upper_base64_or_null" => "{{ phone | ?upper | ?base64 }}",
];

// process data
$webhook_data = $template->array($webhook_format);

print_r($webhook_data);
```

**Output:**

```php
[
    "what_fruit"                 => "orange",
    "price_origin"               => 3.14,
    "price_str"                  => "3.14",
    "price_and_postfix"          => "3.14 USD",
    "email"                      => "gagarin@cosmos.ru",
    "email_base64"               => base64_encode("gagarin@cosmos.ru"),
    "email_upper_base64"         => base64_encode(strtoupper("gagarin@cosmos.ru")),
    "phone_base64_or_null"       => null,
    "phone_upper_base64_or_null" => null,
]
```

## Documentation

You can also introspect the engine to get macro and modifier documentation:

```php
$documentation = $template->getDocumentation();
```

**Example output:**

```php
[
    'macros' => [
        [
            'name'    => 'fruit',
            'type'    => 'string',
            'param'   => ['allow' => false, 'list' => null],
            'details' => 'sold fruit name',
        ],
        [
            'name'    => 'price',
            'type'    => 'float',
            'param'   => ['allow' => false, 'list' => null],
            'details' => '',
        ],
        [
            'name'    => 'email',
            'type'    => 'string|null',
            'param'   => ['allow' => false, 'list' => null],
            'details' => "seller's email",
        ],
        [
            'name'    => 'phone',
            'type'    => 'string|null',
            'param'   => ['allow' => false, 'list' => null],
            'details' => "seller's phone",
        ],
    ],
    'modifiers' => [
        [
            'name'     => 'string',
            'in_type'  => 'string|int|float|bool|null',
            'out_type' => 'string',
            'details'  => 'Converts the value to string',
        ],
        [
            'name'     => 'base64',
            'in_type'  => 'string|int|float|bool|null',
            'out_type' => 'string',
            'details'  => 'Unpadded base64 encoding, as defined in RFC 4648 section 3.2.',
        ],
        [
            'name'     => 'lower',
            'in_type'  => 'string|int|float|bool|null',
            'out_type' => 'string',
            'details'  => 'Converts the value to lowercase string',
        ],
        [
            'name'     => 'upper',
            'in_type'  => 'string|int|float|bool|null',
            'out_type' => 'string',
            'details'  => 'Converts the value to uppercase string',
        ],
    ],
]
```

## JSON Injection Protection

When injecting user data into a JSON template, you **must** escape values properly. If you simply render a macro directly inside a JSON string, malicious values can break your JSON structure:

```php
$user_input_click_id = 'e5a754f3-9a91-4273-b5c4-055c8bb244cc","price":100000,"hello":"world';

$template = new TemplaEngine();
$template->addMacros("price", new Constant(3.14, "float"));
$template->addMacros("click_id", new Constant($user_input_click_id, "string"));

$webhook_format = '{ "price": "{{ price }}", "click_id": "{{ click_id }}" }';

// unsafe: vulnerable to JSON injection
echo $template->string($webhook_format);
```

**Result (unsafe):**

```json
{
  "price": 100000,
  "click_id": "e5a754f3-9a91-4273-b5c4-055c8bb244cc",
  "hello": "world"
}
```

---

**To protect against this**, you should use the built-in `JsonSubString` final modifier, which safely escapes values for JSON string inclusion:

```php
$template->final_strings_modifier = new JsonSubString();
$safe_output = $template->string($webhook_format);
```

**Safe result:**

```json
{
  "price": "3.14",
  "click_id": "e5a754f3-9a91-4273-b5c4-055c8bb244cc\",\"price\":100000,\"hello\":\"world"
}
```

Now, user-supplied values cannot break the JSON structure.
