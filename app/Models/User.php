<?php

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo){

        $this->pdo = $pdo;
    }

    
    // Récupérer un utilisateur par son ID
     
    public function getById(int $id): array|null {

        $stmt = $this->pdo->prepare(
            "SELECT id, name, email FROM users WHERE id = ?");

        $stmt->execute([$id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByEmail(string $email): array|null{

        $stmt = $this->pdo->prepare(
            "SELECT id, name, email, password from users where email = ?");

        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }
}
