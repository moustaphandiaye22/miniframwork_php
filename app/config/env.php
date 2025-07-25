<?php
// config/env.php

// require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

define('DB_DRIVER', $_ENV['DB_DRIVER'] ?? 'mysql');
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_PATH', $_ENV['DB_PATH'] ?? '');
define('DATABASE_URL', $_ENV['DATABASE_URL'] ?? '');
define('DSN', $_ENV['DSN'] ?? DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME);
