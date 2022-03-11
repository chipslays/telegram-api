<?php

namespace Telegram\Plugins\Storage;

class FileDriver extends AbstractDriver implements DriverInterface
{
    public function get(string|int $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        return unserialize(file_get_contents($this->getPath($key)));
    }

    public function set(string|int $key, mixed $value): void
    {
        file_put_contents($this->getPath($key), serialize($value));
    }

    public function has(string|int $key): bool
    {
        return file_exists($this->getPath($key));
    }

    public function delete(string|int $key): void
    {
        if ($this->has($key)) {
            $filepath = $this->getPath($key);
            unlink($filepath);
        }
    }

    protected function getPath(string|int $key): string
    {
        return rtrim($this->config['path'], '\\/') . '/' . md5($key) . '.strg';
    }
}
