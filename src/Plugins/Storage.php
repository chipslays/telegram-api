<?php

namespace Telegram\Plugins;

use Telegram\Plugins\Storage\DriverInterface;
use Telegram\Plugins\Storage\FileDriver;
use Exception;

class Storage extends AbstractPlugin implements DriverInterface
{
    protected DriverInterface $driver;

    public function boot(): void
    {
        switch ($this->config['driver']) {
            case 'file':
                $this->driver = new FileDriver($this->config['file']);
                break;

            default:
                throw new Exception("Unknown storage driver: '{$this->config['driver']}'");
                break;
        }
    }

    public function get(string|int $key, mixed $default = null): mixed
    {
        return $this->driver->get($key, $default);
    }

    public function set(string|int $key, mixed $value): void
    {
        $this->driver->set($key, $value);
    }

    public function has(string|int $key): bool
    {
        return $this->driver->has($key);
    }

    public function delete(string|int $key): void
    {
        $this->driver->delete($key);
    }
}
