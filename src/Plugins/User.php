<?php

namespace Telegram\Plugins;

use Carbon\Carbon;
use Telegram\Database\Models\User as UserModel;

class User extends AbstractPlugin
{
    public function boot(): void
    {
        $this->userId = $this->bot->payload('*.from.id', $this->bot->payload('*.chat.id'));

        if (!$this->userId) {
            return;
        }

        $this->user = $this->find($this->userId);

        if (!$this->user) {
            $this->createUser();
        }
    }

    public function find($userId)
    {
        return UserModel::find($userId);
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
            'first_message' => Carbon::now(),
            'last_message' => Carbon::now(),
            'extra' => null,
            'note' => null,
        ]);

        $this->user = $this->find($this->userId);
    }
}