<?php

namespace App\Repository;

class QueryRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function add(
        string $userId,
        string $value,
    ): void {
        $stm = $this->pdo->prepare('
            INSERT INTO query (user_id, value, created_at)
            VALUES (:userId, :value, datetime("now"))
        ');

        $stm->execute([
            'userId' => $userId,
            'value' => $value,
        ]);
    }
}
