<?php

namespace App\Repository;

class VideoRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getListWithoutCaptions(): array
    {
        $stm = $this->pdo->query('SELECT * FROM video WHERE captions = false');
        $stm->execute();
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getListWithoutIndex(): array
    {
        $stm = $this->pdo->query('SELECT * FROM video WHERE indexed = false');
        $stm->execute();
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function add(
        string $id,
        string $playlist,
        string $title,
        string $thumbnail,
        bool $captions = false,
        bool $indexed = false,
    ): void {
        $stm = $this->pdo->prepare('
            INSERT INTO video (id, playlist, title, thumbnail, captions, indexed)
            VALUES (:id, :playlist, :title, :thumbnail, :captions, :indexed)
        ');

        $stm->execute([
            'id' => $id,
            'playlist' => $playlist,
            'title' => $title,
            'thumbnail' => $thumbnail,
            'captions' => (int)$captions,
            'indexed' => (int)$indexed,
        ]);
    }

    public function setCaptions(string $id, bool $captions): void
    {
        $stm = $this->pdo->prepare('UPDATE video SET captions = :captions WHERE id = :id');
        $stm->execute([
            'id' => $id,
            'captions' => (int)$captions,
        ]);
    }

    public function setSummary(string $id, string $summary): void
    {
        $stm = $this->pdo->prepare('UPDATE video SET summary = :summary WHERE id = :id');
        $stm->execute([
            'id' => $id,
            'summary' => $summary,
        ]);
    }

    public function setIndexed(string $id, bool $indexed): void
    {
        $stm = $this->pdo->prepare('UPDATE video SET indexed = :indexed WHERE id = :id');
        $stm->execute([
            'id' => $id,
            'indexed' => (int)$indexed,
        ]);
    }

    public function find(string $id): array|false
    {
        $stm = $this->pdo->query('SELECT * FROM video WHERE id = :id LIMIT 1');
        $stm->bindValue(':id', $id);
        $stm->execute();
        return $stm->fetch(\PDO::FETCH_ASSOC);
    }

    public function getList(): array
    {
        $stm = $this->pdo->query('SELECT id,title FROM video');
        $stm->execute();
        return $stm->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
