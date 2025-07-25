<?php

namespace App\Core\Abstract;
use App\Core\Abstract\Database;

use PDO;
use PDOException;

abstract class AbstractRepository extends Singleton
{
    protected PDO $pdo;


    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    abstract public function selectAll();

    abstract public function insert();

    abstract public function update();

    abstract public function delete();

    abstract public function selectById();

    abstract public function selectBy(Array $filtre);
}
