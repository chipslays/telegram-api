<?php

namespace Telegram;

use Telegram\Bot;
use Exception;

class Keyboard
{
    protected static array $keyboards = [];

    protected static ?Bot $bot = null;

    public function __construct(?Bot &$bot = null)
    {
        self::$bot = $bot;
    }

    public static function show(
        array|string $keyboard,
        ?string $placeholder = null,
        bool $oneTime = false,
        bool $resize = true,
        bool $selective = false
    ): string {
        if (!is_array($keyboard)) {
            $keyboard = static::getKeyboard($keyboard);
        }

        $inlineKeys = [
            'text', 'callback_data', 'url', 'login_url', 'switch_inline_query',
            'switch_inline_query_current_chat', 'callback_game', 'pay'
        ];

        $markupReplyKeys = [
            'request_contact', 'request_location', 'request_poll'
        ];

        if (
            isset($keyboard[0][0]) &&
            is_array($keyboard[0][0]) &&
            in_array(array_key_last($keyboard[0][0]), $inlineKeys) &&
            in_array(key($keyboard[0][0]), $inlineKeys) &&
            !in_array(array_key_last($keyboard[0][0]), $markupReplyKeys) &&
            !in_array(key($keyboard[0][0]), $markupReplyKeys)
        ) {
            return static::inline($keyboard);
        }

        return static::markup($keyboard, $placeholder, $oneTime, $resize, $selective);
    }

    public static function markup(
        array|string $keyboard,
        ?string $placeholder = null,
        bool $oneTime = false,
        bool $resize = true,
        bool $selective = false
    ): string {
        if (!is_array($keyboard)) {
            $keyboard = static::getKeyboard($keyboard);
        }

        $markup = [
            'keyboard' => $keyboard,
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $oneTime,
            'selective' => $selective,
        ];

        if ($placeholder && trim($placeholder) !== '') {
            $markup['input_field_placeholder'] = $placeholder;
        }

        return json_encode($markup);
    }

    public static function inline(array|string $keyboard): string
    {
        if (!is_array($keyboard)) {
            $keyboard = static::getKeyboard($keyboard);
        }

        foreach ($keyboard as &$item) {
            $item = array_map(function ($value) {
                if (isset($value['callback_data']) && is_array($value['callback_data']) && self::$bot !== null) {
                    $value['callback_data'] = $value['callback_data'][0] . ':' . self::$bot->encodeCallbackData($value['callback_data'][1]);
                }
                return $value;
            }, $item);
        }

        return json_encode(['inline_keyboard' => $keyboard]);
    }

    public static function hide(bool $selective = false): string
    {
        $markup = [
            'hide_keyboard' => true,
            'selective' => $selective,
        ];

        return json_encode($markup);
    }

    public static function forceReply(?string $placeholder = null, bool $selective = false): string
    {
        $markup = [
            'force_reply' => true,
            'selective' => $selective,
        ];

        if ($placeholder && trim($placeholder) !== '') {
            $markup['input_field_placeholder'] = $placeholder;
        }

        return json_encode($markup);
    }

    /**
     * Set new keyboards with delete previous.
     */
    public static function set(array $keyboards = []): void
    {
        static::$keyboards = $keyboards;
    }

    /**
     * Merge already saved keyboards with new.
     */
    public static function add(array $keyboards = []): void
    {
        static::$keyboards = array_merge(static::$keyboards, $keyboards);
    }

    /**
     * Delete all saved keyboards.
     *
     * @return void
     */
    public static function clear(): void
    {
        static::$keyboards = [];
    }

    private static function getKeyboard(string $keyboard)
    {
        if (!isset(static::$keyboards[$keyboard])) {
            throw new Exception("Keyboard note exists: '{$keyboard}'", 1);
        }

        $keyboard = static::$keyboards[$keyboard];
    }
}
