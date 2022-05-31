<?php

use Illuminate\Database\Connection;
use Telegram\Bot;
use Telegram\Keyboard;
use Telegram\Manager;
use Telegram\Support\Validator;

if (!function_exists('bot')) {
    /**
     * Getting saved bot instance from manager for interact.
     *
     * @param string $name
     * @return Bot
     *
     * @throws Exception
     */
    function bot(string $name = 'default'): Bot
    {
        return Manager::get($name);
    }
}

if (!function_exists('keyboard')) {
    /**
     * @param array|string|null $keyboard
     * @param string|null $placeholder
     * @param boolean $oneTime
     * @param boolean $resize
     * @param boolean $selective
     * @param string $bot
     * @return string|Keyboard
     */
    function keyboard(
        array|string $keyboard = null,
        ?string $placeholder = null,
        bool $oneTime = false,
        bool $resize = true,
        bool $selective = false,
        string $bot = 'default',
    ): string|Keyboard
    {
        return bot($bot)->keyboard($keyboard, $placeholder, $oneTime, $resize, $selective);
    }
}

if (!function_exists('payload')) {
    /**
     * @param string|null $key
     * @param mixed $default
     * @param string $bot
     * @return mixed
     */
    function payload(string $key = null, mixed $default = null, $bot = 'default'): mixed
    {
        return bot($bot)->payload($key, $default);
    }
}

if (!function_exists('db')) {
    /**
     * @param string $connetion
     * @param string $bot
     * @return Connection
     */
    function db(string $connetion = 'default', $bot = 'default'): Connection
    {
        return bot($bot)->db($connetion);
    }
}

if (!function_exists('is')) {
    /**
     * This is short alias for `Validate` class.
     *
     * Use cases:
     *
     * `is()->email()->validate('example@email.com')`
     *
     * `is('email')->validate('example@email.com')`
     *
     * `is('contains', 'crab')->validate('chips with crab flavor')`
     *
     * `is()->contains('crab')->validate('chips with crab flavor')`
     *
     * @see https://respect-validation.readthedocs.io/en/latest/
     *
     * @param string|null $method
     * @param string|array|null $arguments
     * @return Validator
     */
    function is(?string $rule = null, $arguments = null) {
        return $rule
            ? Validator::create()->__call($rule, (array) $arguments)
            : Validator::create();
    }
}

if (!function_exists('validate')) {
    /**
     * Simple direct validate.
     *
     * Use cases:
     *
     * `validate('email', 'example@email.com')`
     *
     * `validate('contains', 'crab, 'chips with crab flavor')`
     *
     * @param string $rule
     * @param array|string $data
     * @return bool
     */
    function validate(string $rule, $value1, $value2 = null) {
        if ($value2 !== null) {
            return Validator::create()->__call($rule, (array) $value1)->validate($value2);
        } else {
            return Validator::create()->__call($rule, [])->validate($value1);
        }
    }
}

if (!function_exists('before')) {
    function before(callable $function, $bot = 'default'): void
    {
        bot($bot)->onBeforeRun($function);
    }
}

if (!function_exists('after')) {
    function after(callable $function, $bot = 'default'): void
    {
        bot($bot)->onAfterRun($function);
    }
}
