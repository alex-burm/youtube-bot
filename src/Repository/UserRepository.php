<?php

namespace App\Repository;

class UserRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function add(
        string $id,
        string $firstName,
        string $lastName,
        string $username,
    ): void {
        $stm = $this->pdo->prepare('
            INSERT INTO user (id, first_name, last_name, username, created_at)
            VALUES (:id, :fistName, :lastName, :username, datetime("now"))
        ');

        $stm->execute([
            'id' => $id,
            'fistName' => $firstName,
            'lastName' => $lastName,
            'username' => $username,
        ]);
    }

    public function find(string $id): array|false
    {
        $stm = $this->pdo->query('SELECT * FROM user WHERE id = :id LIMIT 1');
        $stm->bindValue(':id', $id);
        $stm->execute();
        return $stm->fetch(\PDO::FETCH_ASSOC);
    }
}
