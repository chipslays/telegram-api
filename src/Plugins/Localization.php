<?php

namespace Telegram\Plugins;

use Exception;

class Localization extends AbstractPlugin
{
    protected array $locales = [];

    public string $fallback;

    public function boot(): void
    {
        $this->fallback = $this->config['fallback'] ?? 'en';
        $this->locale = $this->bot->getLanguageId() ??  $this->fallback;
        $this->load($this->locale);
    }

    /**
     * Overwrite locales.
     *
     * @param string $locale
     * @return void
     */
    public function load(string $locale): void
    {
        $this->locales[$locale] = $this->parse($this->getPath($locale));
    }

    /**
     * Patch locales.
     *
     * @param string $path
     * @param string $locale
     * @param string|null $driver
     * @return void
     */
    public function patch(string $path, string $locale, ?string $driver = null): void
    {
        $this->locales[$locale] = isset($this->locales[$locale])
            ? array_merge($this->locales[$locale], $this->parse($path, $driver))
            : $this->parse($path, $driver);
    }

    /**
     * @param string $locale
     * @return string
     */
    protected function getPath(string $locale): string
    {
        return $this->config['path'] . '/' . $locale . '.' . $this->config['driver'];
    }

    /**
     * @param string $path
     * @param string|null $driver
     * @return bool|array
     *
     * @throws Exception
     */
    protected function parse(string $path, ?string $driver = null): bool|array
    {
        if (!file_exists($path)) {
            return false;
        }

        switch ($driver ?? $this->config['driver']) {
            case 'json':
                return json_decode(file_get_contents($path), true);
                break;

            default:
                throw new Exception('Unknown localization driver: ' . $driver ?? $this->config['driver']);
                break;
        }
    }

    /**
     * Get localized text.
     *
     * @param string $key
     * @param array|null $replacements
     * @param string|null $locale
     * @return string
     */
    public function trans(string $key, ?array $replacements = null, string $locale = null): string
    {
        $text = $this->getMessageText($key, $locale);

        if ($replacements) {
            $text = strtr($text, $replacements);
        }

        return $text;
    }

    /**
     * @param string $key
     * @param string|null $locale
     * @return string
     */
    protected function getMessageText(string $key, string $locale = null): string
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
        if (!isset($this->locales[$this->fallback])) {
            $this->load($this->fallback);
        }

        if (isset($this->locales[$this->fallback][$key])) {
            return $this->locales[$this->fallback][$key];
        }

        return $key;
    }
}
