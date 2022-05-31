<?php

namespace Telegram\Traits;

trait Componentable
{
    protected $components = [];

    protected function runComponents()
    {
        foreach ($this->components as $component) {
            if (file_exists($component['entrypoint'] ?? null)) {
                $class = require $component['entrypoint'];
                $instance = new $class($this, $component['config'] ?? [], $component['entrypoint']);
                $instance($this, $component['config'] ?? [], $component['entrypoint']);
            }
        }
    }

    /**
     * Set components.
     *
     * @param array $components
     * @return void
     */
    public function components(array $components): void
    {
        $this->components = array_merge($this->components, $components);
    }
}