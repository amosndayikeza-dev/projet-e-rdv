<?php
namespace App\Core;
use PDO;

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }

    protected function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    protected function commit(): bool
    {
        return $this->db->commit();
    }

    protected function rollBack(): bool
    {
        return $this->db->rollBack();
    }
}