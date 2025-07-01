<?php declare(strict_types=1);

namespace AP\Templa\Tests;


use AP\Templa\Macros\Constant;
use AP\Templa\Macros\LazyLoad;
use AP\Templa\Modifier\Base64;
use AP\Templa\Modifier\JsonSubString;
use AP\Templa\Modifier\ToLower;
use AP\Templa\Modifier\ToString;
use AP\Templa\Modifier\ToUpper;
use AP\Templa\TemplaEngine;
use PHPUnit\Framework\TestCase;

class ReadmeTest extends TestCase
{
    public function template(string $fruit, ?float $price, ?string $email, ?string $phone)
    {
        $template = new TemplaEngine();
        $template->addMacros("fruit", new Constant($fruit, "string", "sold fruit name"));
        $template->addMacros("price", new Constant($price, "float"));
        $template->addMacros("email", new Constant($email, "string|null", "seller's email"));
        $template->addMacros("phone", new Constant($phone, "string|null", "seller's phone"));

        $template->addModifier("string", new ToString());
        $template->addModifier("base64", new Base64());
        $template->addModifier("lower", new ToLower());
        $template->addModifier("upper", new ToUpper());

        return $template;
    }

    public function testRunner(): void
    {

        $fruit = "orange";
        $price = 3.14;
        $email = 'gagarin@cosmos.ru';
        $phone = null;

        $template = $this->template(
            $fruit,
            $price,
            $email,
            $phone
        );

        $webhook_format = [
            "what_fruit"                 => "{{ fruit }}",
            "price_origin"               => "{{ price }}",
            "price_str"                  => "{{ price | string }}",
            "price_str_or_null"          => "{{ price | ?string }}",
            "price_and_postfix"          => "{{ price }} USD",
            "email"                      => "{{ email }}",
            "email_base64"               => "{{ email | base64 }}",
            "email_upper_base64"         => "{{ email | upper | base64 }}",
            "phone_base64_or_null"       => "{{ phone | ?base64 }}",
            "phone_upper_base64_or_null" => "{{ phone | ?upper | ?base64 }}",
        ];

        $webhook_data = $template->array($webhook_format);

        $this->assertEquals(
            [
                "what_fruit"                 => "orange",
                "price_origin"               => $price,
                "price_str"                  => "$price",
                "price_str_or_null"          => is_null($price) ? null : (string)$price,
                "price_and_postfix"          => "$price USD",
                "email"                      => $email,
                "email_base64"               => base64_encode((string)$email),
                "email_upper_base64"         => base64_encode(strtoupper((string)$email)),
                "phone_base64_or_null"       => is_null($phone) ? null : base64_encode($phone),
                "phone_upper_base64_or_null" => is_null($phone) ? null : base64_encode(strtoupper($phone)),
            ],
            $webhook_data
        );
    }

    public function testDocumentation(): void
    {

        $fruit = "orange";
        $price = 3.14;
        $email = 'gagarin@cosmos.ru';
        $phone = null;

        $template = $this->template(
            $fruit,
            $price,
            $email,
            $phone
        );

        $documentation = $template->getDocumentation();

        $this->assertEquals(
            [
                'macros'    => [
                    [
                        'name'    => 'fruit',
                        'type'    => 'string',
                        'param'   => [
                            'allow' => false,
                            'list'  => null,
                        ],
                        'details' => 'sold fruit name',
                    ],
                    [
                        'name'    => 'price',
                        'type'    => 'float',
                        'param'   => [
                            'allow' => false,
                            'list'  => null,
                        ],
                        'details' => '',
                    ],
                    [
                        'name'    => 'email',
                        'type'    => 'string|null',
                        'param'   => [
                            'allow' => false,
                            'list'  => null,
                        ],
                        'details' => "seller's email",
                    ],
                    [
                        'name'    => 'phone',
                        'type'    => 'string|null',
                        'param'   => [
                            'allow' => false,
                            'list'  => null,
                        ],
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
            ],
            json_decode(json_encode($documentation), true)
        );
    }

    public function testInjection()
    {
        $user_input_click_id = 'e5a754f3-9a91-4273-b5c4-055c8bb244cc","price":100000,"hello":"world';

        $template = new TemplaEngine();
        $template->addMacros("price", new Constant(3.14, "float"));
        $template->addMacros("click_id", new Constant($user_input_click_id, "string"));

        $webhook_format = '{ "price": "{{ price }}", "click_id": "{{ click_id }}" }';

        // this approach has an injection vulnerability
        $webhook_data = $template->string($webhook_format);

        $this->assertEquals(
            [
                'price'    => 100000, // injected PRICE
                'click_id' => 'e5a754f3-9a91-4273-b5c4-055c8bb244cc',
                'hello'    => 'world',
            ],
            json_decode($webhook_data, true)
        );

        // to protect JSON values, use the final modifier JsonSubString
        $template->final_strings_modifier = new JsonSubString();
        $webhook_data                     = $template->string($webhook_format);
        $this->assertEquals(
            [
                'price'    => '3.14',
                'click_id' => 'e5a754f3-9a91-4273-b5c4-055c8bb244cc","price":100000,"hello":"world',
            ],
            json_decode($webhook_data, true)
        );
    }

    public function testLazyLoad()
    {
        $sleep = 1;

        $template = new TemplaEngine();

        // use lazy load if need to get data from database, redis, api, grpc, hard-calculate
        $template->addMacros("hard", new LazyLoad(
            function (?string $param, string $name, string $macro) use ($sleep) {
                sleep($sleep);
                return "takeLongTimeToGet";
            },
            "string"
        ));

        // use constants if data allowed in memory
        $template->addMacros("easy", new Constant(
            "dataAllowedOnMemory",
            "string"
        ));

        // easy
        $start = microtime(true);
        $res = $template->string('{ "result": "{{ easy }}" }');
        $this->assertEquals('{ "result": "dataAllowedOnMemory" }', $res);
        $this->assertLessThan(
            $sleep,
            microtime(true) - $start
        );

        // hard
        $start = microtime(true);
        $res = $template->string('{ "result": "{{ hard }}" }');
        $this->assertEquals('{ "result": "takeLongTimeToGet" }', $res);
        $this->assertGreaterThan(
            $sleep,
            microtime(true) - $start
        );
    }

}
