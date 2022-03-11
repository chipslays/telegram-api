<?php

namespace Telegram\Support\Traits;

trait Variable
{
    protected array $vars = [];

    public function var(string|int|array $key, mixed $default = null): mixed
    {
        if (func_num_args() === 0) {
            return $this->vars;
        }

        if (is_array($key)) {
            return $this->vars = [...$this->vars, ...$key];
        }

        return $this->vars[$key] ?? $default;
    }
}