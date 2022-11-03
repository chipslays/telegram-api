<?php

namespace Telegram;

use Telegram\Exceptions\BotApiException;
use Telegram\Support\Collection;

class Response extends Collection
{
    public readonly bool $ok;

    public function __construct(array $response)
    {
        if (!isset($response['ok'])) {
            throw new BotApiException('Unknown response: ' . json_encode($response));
        }

        $this->ok = $response['ok'];

        $this->data = $this->ok === true ? $response['result'] : $response;
    }
}
