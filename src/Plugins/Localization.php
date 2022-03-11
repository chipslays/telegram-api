<?php

namespace Telegram\Plugins;

use Exception;

class Localization extends AbstractPlugin
{
    protected array $locales = [];

    public function boot(): void
    {
        $this->locale = $this->bot->getLanguageId();
        $this->load($this->locale);
    }

    public function load(string $locale): void
    {
        $this->locales[$locale] = $this->parse($this->getPath($locale));
    }

    public function patch(string $path, string $locale, $driver = null): void
    {
        $this->locales[$locale] = isset($this->locales[$locale])
            ? array_merge($this->locales[$locale], $this->parse($path, $driver))
            : $this->parse($path, $driver);
    }

    protected function getPath(string $locale)
    {
        return $this->plugin['path'] . '/' . $locale . '.' . $this->plugin['driver'];
    }

    protected function parse(string $path, $driver = null)
    {
        if (!file_exists($path)) {
            return;
        }

        switch ($driver ?? $this->plugin['driver']) {
            case 'json':
                return json_decode(file_get_contents($path), true);
                break;

            default:
                throw new Exception('Unknown localization driver: ' . $driver ?? $this->plugin['driver']);
                break;
        }
    }

    public function trans(string $key, ?array $replacements = null, string $locale = null)
    {
        $text = $this->getMessageText($key, $locale);

        if ($replacements) {
            $text = strtr($text, $replacements);
        }

        return $text;
    }

    protected function getMessageText(string $key, string $locale = null)
    {
        if (!$locale) {
            $locale = $this->locale;
        }

        if (!isset($this->locales[$locale])) {
            $this->load($locale);
        }

        if (isset($this->locales[$locale][$key])) {
            return $this->locales[$locale][$key];
        }

        // fallback
        if (!isset($this->locales[$this->plugin['fallback']])) {
            $this->load($this->plugin['fallback']);
        }

        if (isset($this->locales[$this->plugin['fallback']][$key])) {
            return $this->locales[$this->plugin['fallback']][$key];
        }

        return $key;
    }
}
