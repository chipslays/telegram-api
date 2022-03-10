<?php

namespace Telegram\Plugins\Storage;

abstract class AbstractDriver
{
    public function __construct(protected array $config = [])
    {
    }
}
