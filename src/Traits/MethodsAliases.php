<?php

namespace Litegram\Traits\Telegram;

use Telegram\Response;
use Telegram\Support\Util;

trait MethodsAliases
{
    /**
     * Reply to message by chat or user ID.
     *
     * @param string|int $chatId
     * @param string|int $messageId
     * @param string $text
     * @param string|null $keyboard
     * @param array $extra
     * @return Response
     */
    public function sendReply($chatId, $messageId, $text = '', $keyboard = null, $extra = [])
    {
        return $this->method('sendMessage', $this->mappingParameters([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_to_message_id' => $messageId,
        ], $keyboard, $extra));
    }

    /**
     * Just send message for icoming chat or user.
     *
     * @param string $text
     * @param string|null $keyboard
     * @param array $extra
     * @return Response
     */
    public function say($text, $keyboard = null, $extra = [])
    {
        return $this->sendMessage(
            $this->getChatId(),
            Util::shuffle($text),
            $keyboard,
            $extra
        );
    }

    /**
     * Reply to incoming message.
     *
     * @param string $text
     * @param string|null $keyboard
     * @param array $extra
     * @return Response
     */
    public function reply($text, $keyboard = null, $extra = [])
    {
        return $this->sendMessage(
            $this->getChatId(),
            $text,
            $keyboard,
            array_merge($extra, ['reply_to_message_id' => $this->payload('*.message_id', $this->payload('*.*.message_id'))])
        );
    }

    /**
     * Send notification or alert.
     *
     * Works only for callback.
     *
     * @param string $text
     * @param boolean $showAlert
     * @param array $extra
     * @return Response
     */
    public function notify($text = '', $showAlert = false, $extra = [])
    {
        return $this->method('answerCallbackQuery', $this->mappingParameters([
            'callback_query_id' => $this->payload('callback_query.id'),
            'text' => Util::shuffle($text),
            'show_alert' => $showAlert,
        ], null, $extra));
    }

    /**
     * Send caht action: typing and etc...
     *
     * @param string $action
     * @param array $extra
     * @return Response
     */
    public function action($action = 'typing', $extra = [])
    {
        return $this->method('sendChatAction', $this->mappingParameters([
            'chat_id' => $this->getChatId(),
            'action' => $action,
        ], null, $extra));
    }

    /**
     * Send dice and other emojis.
     *
     * @param string $emoji
     * @param string|null $keyboard
     * @param array $extra
     * @return Response
     */
    public function dice($emoji = '🎲', $keyboard = null, $extra = [])
    {
        return $this->sendDice($this->getChatId(), $emoji, $keyboard, $extra);
    }

    /**
     * Check user blocked bot or not.
     *
     * @param int|string $chatId
     * @param string $action
     * @param array $extra
     * @return boolean
     */
    public function isActive($chatId, $action = 'typing', $extra = [])
    {
        $response = $this->method('sendChatAction', $this->mappingParameters([
            'chat_id' => $chatId,
            'action' => $action,
        ], null, $extra));

        return !is_null($response) ? $response->get('ok') : false;
    }

    /**
     * Build `api.telegram.org/file/bot123/file_123` url
     *
     * @param string|null $fileId
     * @return string|null
     */
    public function getFileUrl(string $fileId)
    {
        $response = $this->getFile($fileId);
        return $response->get('ok')
            ? 'https://api.telegram.org/file/bot' . $this->config('bot.token') . '/' . $response->get('result.file_path')
            : null;
    }

    /**
     * Download file by `file_id`.
     * @param string $fileId
     * @param string $savePath
     * @return string Full path to saved file
     */
    public function download($fileId, $savePath): string
    {
        $fileUrl = $this->getFileUrl($fileId);

        $extension = '';
        if (strpos(basename($fileUrl), '.') !== false) {
            $filename = explode('.', basename($fileUrl));
            $extension = end($filename);
        }

        $savePath = str_ireplace(['{ext}', '{extension}', '{file_ext}'], $extension, $savePath);
        $savePath = str_ireplace(['{base}', '{basename}', '{base_name}', '{name}'], basename($fileUrl), $savePath);
        $savePath = str_ireplace(['{time}'], time(), $savePath);
        $savePath = str_ireplace(['{md5}'], md5(time() . mt_rand()), $savePath);
        $savePath = str_ireplace(['{rand}', '{random}', '{rand_name}', '{random_name}'], md5(time() . mt_rand()) . ".$extension", $savePath);

        file_put_contents($savePath, file_get_contents($fileUrl));

        return $savePath;
    }

    /**
     * Auto detect caption or text callback.
     *
     * @param string $text
     * @param string|null $keyboard
     * @param array $extra
     * @return Response
     */
    public function editCallbackMessage(string $text, $keyboard = null, $extra = [])
    {
        if ($this->payload()->isCaption()) {
            return $this->editMessageCaption(
                $this->payload('callback_query.message.message_id'),
                $this->payload('callback_query.from.id'),
                $text,
                $keyboard,
                $extra
            );
        } else {
            return $this->editMessageText(
                $this->payload('callback_query.message.message_id'),
                $this->payload('callback_query.from.id'),
                $text,
                $keyboard,
                $extra
            );
        }

    }

    /**
     * Edit source message received from callback.
     *
     * @param string $text
     * @param string|null $keyboard
     * @param array $extra
     * @return Response
     */
    public function editCallbackText(string $text, $keyboard = null, $extra = [])
    {
        return $this->editMessageText(
            $this->payload('callback_query.message.message_id'),
            $this->payload('callback_query.from.id'),
            $text,
            $keyboard,
            $extra
        );
    }

    /**
     * Edit source message with photo received from callback.
     *
     * @param string $text
     * @param string|null $keyboard
     * @param array $extra
     * @return Response
     */
    public function editCallbackCaption(string $text, $keyboard = null, $extra = [])
    {
        return $this->editMessageCaption(
            $this->payload('callback_query.message.message_id'),
            $this->payload('callback_query.from.id'),
            $text,
            $keyboard,
            $extra
        );
    }

    /**
     * Send `print_r` message.
     *
     * @param mixed $text
     * @param string|int|null $userId
     * @return Response
     */
    public function print($data, $userId = null)
    {
        return $this->method('sendMessage', [
            'chat_id' => $userId ?? $this->getChatId(),
            'text' => '<code>' . print_r($data, true) . '</code>',
            'parse_mode' => 'html',
        ]);
    }

    /**
     * Send `var_export` message.
     *
     * @param mixed $text
     * @param string|int|null $userId
     * @return Response
     */
    public function dump($data, $userId = null)
    {
        return $this->method('sendMessage', [
            'chat_id' => $userId ?? $this->getChatId(),
            'text' => '<code>' . var_export($data, true) . '</code>',
            'parse_mode' => 'html',
        ]);
    }

    /**
     * Send `json` message.
     *
     * @param array|string|int $data
     * @param string|int|null $userId
     * @return Response
     */
    public function json($data, $userId = null)
    {
        return $this->method('sendMessage', [
            'chat_id' => $userId ?? $this->getChatId(),
            'text' => '<code>' . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</code>',
            'parse_mode' => 'html',
        ]);
    }

    /**
     * @param string|int|null (Optional) chat_id
     * @return Response
     */
    public function deleteMessageFromCallback($chatId = null)
    {
        return $this->deleteMessage($this->payload('callback_query.message.message_id'), $chatId ?? $this->payload('*.from.id'));
    }
}