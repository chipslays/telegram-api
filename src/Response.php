<?php

namespace Telegram;

use Telegram\Support\Collection;

class Response extends Collection
{
    protected bool $ok = false;

    public function __construct(array $response)
    {
        $this->ok = $response['ok'] ?? false;

        if ($this->ok === true) {
            $this->items = $response['result'];
        } else {
            $this->items = $response;
        }
    }

    /**
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->ok === true;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->ok === false;
    }
}
