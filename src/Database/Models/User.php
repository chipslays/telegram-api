<?php

namespace Telegram\Database\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps = false;

    protected $casts = [
        'extra' => 'array',
        'ban_start_at' => 'datetime',
        'ban_end_at' => 'datetime',
        'first_message' => 'datetime',
        'last_message' => 'datetime',
    ];

    protected $fillable = [
        'id',
        'firstname',
        'lastname',
        'username',
        'locale',
        'phone',
        'nickname',
        'avatar',
        'role',
        'is_blocked',
        'is_banned',
        'ban_comment',
        'ban_start_at',
        'ban_end_at',
        'utm_source',
        'bot_version',
        'first_message_at',
        'last_message_at',
        'extra',
        'note',
    ];
}