<?php

namespace App\Core\Abstract;

use App\Core\App;

abstract class Singleton
{
    private static array $instances = [];

    protected function __construct() {}

    private function __clone() {}

    public function __wakeup()
    {
        throw new \Exception("ne peut pas être déserialisé.");
    }

    public static function getInstance(): static
    {
        $class = static::class;
        if (!isset(self::$instances[$class])) {
            // Utiliser le container pour l'injection de dépendance
            self::$instances[$class] = App::getContainer()->resolve($class);
        }
        return self::$instances[$class];
    }
}
