<?php

namespace Telegram\Plugins;

use Carbon\Carbon;
use Telegram\Database\Models\User as UserModel;

class User extends AbstractPlugin
{
    protected UserModel $model;

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

        $this->model->last_message_at = Carbon::now();
        $this->model->is_blocked = false;
        $this->model->save();
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

    public function __get($key)
    {
        return $this->model->{$key};
    }

    public function __set($key, $value)
    {
        $this->model->{$key} = $value;
    }

    public function save()
    {
        $this->model->save();
    }

    public function model()
    {
        return $this->model;
    }

    /**
     * Ban user from context.
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
     * Unban user from context.
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
            dump($config);
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