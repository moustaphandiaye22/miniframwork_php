<?php

namespace App\Core;

use App\Core\Abstract\Singleton;

class Session extends Singleton
{
    protected function __construct()
    {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function destroy()
    {
        session_destroy();
    }

    public function unset(string $key)
    {
        unset($_SESSION[$key]);
    }

    public function isset(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    public static function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /');
            exit;
        }
    }
}
