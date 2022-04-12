<?php

namespace Telegram\Plugins;

use Illuminate\Database\Connection;
use Illuminate\Database\Capsule\Manager as Capsule;

class Database extends AbstractPlugin
{
    protected Capsule $capsule;

    public function boot(): void
    {
        $driver = $this->config['driver'];
        $config = $this->config['drivers'][$driver];
        $config['driver'] = $driver;

        $this->capsule = new Capsule;
        $this->capsule->addConnection($config);
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }

    /**
     * Get database connection.
     *
     * @param string $name
     * @return Connection
     */
    public function connection(string $name = 'default')
    {
        return $this->capsule->connection($name);
    }

    /**
     * Get `Capsule` instance.
     *
     * @return Capsule
     */
    public function capsule(): Capsule
    {
        return $this->capsule;
    }
}