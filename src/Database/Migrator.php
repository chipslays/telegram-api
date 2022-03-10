<?php

namespace Telegram\Database;

use Telegram\Bot;
use Telegram\Database\Migrations\Users;
use Illuminate\Database\Schema\Builder;
use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;

class Migrator
{
    protected Builder $schema;

    public function __construct(protected Bot $bot, $schema)
    {
        /** @var Capsule */
        $capsule = $this->bot->plugin(Database::class)->capsule();

        $this->schema = $capsule->schema($schema);
    }

    /**
     * Get Migrator instance or run migrations.
     *
     * @param string|null $table
     * @param string $method
     * @return self|null
     */
    public function migrate(string $table = null, string $method = 'up'): self|null
    {
        match ($method) {
            'up' => $this->up($table),
            'down' => $this->down($table),
            default => throw new Exception("Migrate table failed, '{$table}', allowed methods: up/down.", 1),
        };

        return null;
    }

    public function up(string $table)
    {
        match ($table) {
            'users' => call_user_func([Users::class, 'up'], $this->schema),
            // 'storage' => call_user_func([Storage::class, 'up'], $this->schema),
        };
    }

    public function down(string $table)
    {
        match ($table) {
            'users' => call_user_func([Users::class, 'down'], $this->schema),
            // 'storage' => call_user_func([Storage::class, 'down'], $this->schema),
        };
    }
}
