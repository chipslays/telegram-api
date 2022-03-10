<?php

namespace Telegram\Database\Migrations;

use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\Blueprint;

class Users
{
    public static function up(Builder $schema): void
    {
        if (!$schema->hasTable('users')) {
            $schema->create('users', function (Blueprint $table) {
                $table->bigInteger('id')->unique()->primary()->index();
                $table->text('firstname')->nullable();
                $table->string('lastname')->nullable();
                $table->string('username')->nullable();
                $table->string('locale', 3)->default('en');
                $table->string('phone')->nullable();
                $table->string('nickname')->nullable();
                $table->string('avatar')->nullable();
                $table->string('role')->default('user')->nullable();
                $table->boolean('is_blocked')->default(0);
                $table->boolean('is_banned')->default(0);
                $table->text('ban_comment')->nullable();
                $table->dateTime('ban_start_at')->nullable();
                $table->dateTime('ban_end_at')->nullable();
                $table->string('utm_source')->nullable();
                $table->string('bot_version')->nullable();
                $table->dateTime('first_message_at');
                $table->dateTime('last_message_at');
                $table->json('extra')->nullable();
                $table->text('note')->nullable();
            });
        }
    }

    public static function down(Builder $schema): void
    {
        $schema->dropIfExists('users');
    }
}