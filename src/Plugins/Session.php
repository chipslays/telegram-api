<?php

namespace Telegram\Plugins;

use Telegram\Plugins\Storage\DriverInterface;

class Session extends AbstractPlugin implements DriverInterface
{
    protected Storage $storage;

    public function boot(): void
    {
        $this->storage = $this->bot->plugin(Storage::class);
    }

    public function get(string|int $key, mixed $default = null): mixed
    {
        return $this->storage->get($this->modifyKey($key), $default);
    }

    public function set(string|int $key, mixed $value): void
    {
        $this->storage->set($this->modifyKey($key), $value);
    }

    public function has(string|int $key): bool
    {
        return $this->storage->has($this->modifyKey($key));
    }

    public function delete(string|int $key): void
    {
        $this->storage->delete($this->modifyKey($key));
    }

    protected function modifyKey(string|int $key): string
    {
        return 'SESSION_' . $this->bot->getChatId() . '_' . $key;
    }
}
