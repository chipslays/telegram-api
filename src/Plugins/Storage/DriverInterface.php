<?php

namespace Telegram\Plugins\Storage;

interface DriverInterface
{
    public function get(string|int $key, mixed $default = null): mixed;

    public function set(string|int $key, mixed $value): void;

    public function has(string|int $key): bool;

    public function delete(string|int $key): void;
}