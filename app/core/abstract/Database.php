<?php

namespace App\Core\Abstract;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            // Lire les variables depuis .env
            $driver = $_ENV['DB_DRIVER'] ?? 'pgsql';
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'pgdbDaf';
            $port = $_ENV['DB_PORT'] ?? 34111;
            $user = $_ENV['DB_USER'] ?? 'pguserDaf';
            $pass = $_ENV['DB_PASSWORD'] ?? 'pgpassword';
            
            $dsn = "$driver:host=$host;port=$port;dbname=$dbname";

            try {
                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }

        return self::$pdo;
    
    }
}
