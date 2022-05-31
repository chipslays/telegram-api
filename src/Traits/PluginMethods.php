<?php

namespace Telegram\Traits;

use Telegram\Plugins\Database;
use Telegram\Plugins\Session;
use Telegram\Plugins\Storage;
use Telegram\Plugins\Localization;
use Telegram\Plugins\Logger;
use Telegram\Plugins\Telegraph;
use Telegram\Plugins\User;
use Illuminate\Database\Connection;

trait PluginMethods
{
    /**
     * @param string|integer|array|null $key
     * @param mixed $default
     * @return mixed|Storage
     */
    public function storage(string|int|array $key = null, mixed $default = null)
    {
        $storage = $this->plugin(Storage::class);

        if ($key === null) {
            return $storage;
        }

        if (is_array($key)) {
            foreach ($key as $index => $value) {
                $storage->set($index, $value);
            }
            return;
        }

        return $storage->get($key, $default);
    }

    /**
     * @param string|integer|array|null $key
     * @param mixed $default
     * @return mixed|Session
     */
    public function session(string|int|array $key = null, mixed $default = null)
    {
        $session = $this->plugin(Session::class);

        if ($key === null) {
            return $session;
        }

        if (is_array($key)) {
            foreach ($key as $index => $value) {
                $session->set($index, $value);
            }
            return;
        }

        return $session->get($key, $default);
    }

    /**
     * @return Localization
     */
    public function localization(): Localization
    {
        return $this->plugin(Localization::class);
    }

    /**
     * @param string $key
     * @param array|null $replacements
     * @param string|null $locale
     * @return string
     */
    public function trans(string $key, ?array $replacements = null, string $locale = null): string
    {
        return $this->localization()->trans($key, $replacements, $locale);
    }

    /**
     * @return Telegraph
     */
    public function telegraph(): Telegraph
    {
        return $this->plugin(Telegraph::class);
    }

    /**
     * @param string|array $key
     * @param mixed $default
     * @return mixed|User
     */
    public function user(string|array $key = null, mixed $default = null): mixed
    {
        /** @var User */
        $user = $this->plugin(User::class);

        if ($key === null) {
            return $user;
        }

        if (is_array($key)) {
            return $user->model()->update($key);
        }

        return $user->get((string) $key, $default);
    }

    /**
     * @return Logger
     */
    public function log(mixed $data, string $title = 'default', string $filePostfix = 'bot'): void
    {
        /** @var Logger */
        $logger = $this->plugin(Logger::class);
        $logger->put($data, $title, $filePostfix);
    }

    /**
     * Get database connection.
     *
     * @return Connection
     */
    public function db(string $connetion = 'default'): Connection
    {
        $database = $this->plugin(Database::class);

        return $database->connection($connetion);
    }
}