<?php

namespace Telegram\Plugins;

use Carbon\Carbon;
use Telegram\Database\Models\User as UserModel;

class User extends AbstractPlugin
{
    protected ?UserModel $model;

    protected $userId;

    public function boot(): void
    {
        $this->userId = $this->bot->payload('*.from.id', $this->bot->payload('*.chat.id'));

        if (!$this->userId) {
            return;
        }

        $this->model = $this->find($this->userId);

        if (!$this->model) {
            $this->createUser();
        }
    }

    public function onAfterRun(): void
    {
        if (!$this->userId) {
            return;
        }

        $this->update([
            'last_message_at' => Carbon::now(),
            'is_blocked' => false,
        ]);
    }

    /**
     * Get user model.
     *
     * @param string|int $userId
     * @return UserModel|null
     */
    public function find(string|int $userId): UserModel|null
    {
        return UserModel::find($userId);
    }

    /**
     * Check exists user in database.
     *
     * @param string|int $userId
     * @return bool
     */
    public static function exists(string|int $userId): bool
    {
        return UserModel::where('id', $userId)->exists();
    }

    /**
     * Get user attribute from database.
     *
     * @param mixed $key
     * @return mixed
     */
    public function __get(mixed $key): mixed
    {
        return $this->model->{$key};
    }

    /**
     * Set database user attritube (without save, just set!).
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function __set(mixed $key, mixed $value): void
    {
        $this->model->{$key} = $value;
    }

    /**
     * Get user attribute from database.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->model->{$key} ?? $default;
    }

    /**
     * Set database user attritube (without save, just set!).
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set(string $key, mixed $value): self
    {
        $this->model->{$key} = $value;

        return $this;
    }

    /**
     * Update user data in database.
     *
     * @param array $attributes
     * @param array $options
     * @return boolean
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        return $this->model->update($attributes, $options);
    }

    /**
     * Save the user model to the database.
     *
     * @return void
     */
    public function save(): void
    {
        $this->model->save();
    }

    /**
     * Get user model.
     *
     * @return UserModel
     */
    public function model(): UserModel
    {
        return $this->model;
    }

    /**
     * Ban user.
     *
     * @param string $comment
     * @param Carbon $endAt
     * @return void
     */
    public function ban(string $comment, Carbon $endAt): void
    {
        $this->model->update([
            'is_banned' => true,
            'ban_comment' => $comment,
            'ban_start_at' => Carbon::now(),
            'ban_end_at' => $endAt,
        ]);
    }

    /**
     * Unban user.
     *
     * @return void
     */
    public function unban(): void
    {
        $this->model->update([
            'is_banned' => false,
            'ban_comment' => null,
            'ban_start_at' => null,
            'ban_end_at' => null,
        ]);
    }

    protected function createUser(): void
    {
        // get utm_source from deep-link
        $utmSource = null;
        $text = $this->bot->payload('*.text');
        if (str_starts_with($text, '/start')) {
            $text = explode(' ', $text);
            if (is_array($text) && count($text) > 1) {
                unset($text[0]);
                $utmSource = implode(' ', $text);
            }
        }

        // get default language code
        $language = 'en';
        if ($this->bot->plugins()->has(Localization::class)) {
            $config = $this->bot->plugins()->config(Localization::class);
            $language = $config['locale'];
        }

        UserModel::create([
            'id' => $this->bot->payload('*.from.id'),
            'firstname' => $this->bot->payload('*.from.first_name'),
            'lastname' => $this->bot->payload('*.from.last_name'),
            'username' => $this->bot->payload('*.from.username'),
            'locale' => $this->bot->payload('*.from.language_code', $language),
            'phone' => null,
            'nickname' => null,
            'avatar' => null,
            'role' => 'user',
            'is_blocked' => false,
            'is_banned' => false,
            'ban_comment' => null,
            'ban_start_at' => null,
            'ban_end_at' => null,
            'utm_source' => $utmSource,
            'bot_version' => null,
            'first_message_at' => Carbon::now(),
            'last_message_at' => Carbon::now(),
            'extra' => null,
            'note' => null,
        ]);

        $this->model = $this->find($this->userId);
    }
}