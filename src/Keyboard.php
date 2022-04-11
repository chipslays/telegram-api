<?php

namespace Telegram;

use Telegram\Bot;
use Exception;

class Keyboard
{
    protected array $keyboards = [];

    public function __construct(protected ?Bot &$bot = null)
    {
        //
    }

    public function show(
        array|string $keyboard,
        ?string $placeholder = null,
        bool $oneTime = false,
        bool $resize = true,
        bool $selective = false
    ): string {
        if (!is_array($keyboard)) {
            $keyboard = $this->getKeyboard($keyboard);
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
            return $this->inline($keyboard);
        }

        return $this->markup($keyboard, $placeholder, $oneTime, $resize, $selective);
    }

    public function markup(
        array|string $keyboard,
        ?string $placeholder = null,
        bool $oneTime = false,
        bool $resize = true,
        bool $selective = false
    ): string {
        if (!is_array($keyboard)) {
            $keyboard = $this->getKeyboard($keyboard);
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

    public function inline(array|string $keyboard): string
    {
        if (!is_array($keyboard)) {
            $keyboard = $this->getKeyboard($keyboard);
        }

        foreach ($keyboard as &$item) {
            $item = array_map(function ($value) {
                // if (isset($value['callback_data']) && is_array($value['callback_data']) && $this->bot !== null) {
                //     $value['callback_data'] = $value['callback_data'][0] . ':' . $this->bot->encodeCallbackData($value['callback_data'][1]);
                // }
                return $value;
            }, $item);
        }

        return json_encode(['inline_keyboard' => $keyboard]);
    }

    public function hide(bool $selective = false): string
    {
        $markup = [
            'hide_keyboard' => true,
            'selective' => $selective,
        ];

        return json_encode($markup);
    }

    public function forceReply(?string $placeholder = null, bool $selective = false): string
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
    public function set(array $keyboards = []): void
    {
        $this->keyboards = $keyboards;
    }

    /**
     * Merge already saved keyboards with new.
     */
    public function add(array $keyboards = []): void
    {
        $this->keyboards = array_merge($this->keyboards, $keyboards);
    }

    /**
     * Delete all saved keyboards.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->keyboards = [];
    }

    private function getKeyboard(string $keyboard)
    {
        if (!isset($this->keyboards[$keyboard])) {
            throw new Exception("Keyboard note exists: '{$keyboard}'", 1);
        }

        return $this->keyboards[$keyboard];
    }
}
