<?php

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo){

        $this->pdo = $pdo;
    }

    public function create(string $name, string $email, string $hashPassword): bool{

        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $email, $hashPassword]);
    }   
    
    // Recuperer utilisateur par son ID
     
    public function getById(int $id): array|null {

        $stmt = $this->pdo->prepare(
            "SELECT id, name, email FROM users WHERE id = ?");
    
        $stmt->execute([$id]);
    
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $user ?: null;
    }

    public function findByEmail(string $email): array|null {
        $email = trim($email);
       
        $stmt = $this->pdo->prepare(
            "SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
       
        return $user ?: null;
    }
    
}
