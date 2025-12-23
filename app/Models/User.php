<?php

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    
    // Récupérer un utilisateur par son ID
     
    public function getById(int $id): array|null
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, name, email FROM users WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }


}
