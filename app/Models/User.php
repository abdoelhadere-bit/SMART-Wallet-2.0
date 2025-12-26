<?php

class User
{
    private PDO $pdo;
    private string $name;
    private string $email;
    private string $password;
    private string $confirm;

    public function __construct(PDO $pdo, string $name, string $email, string $password, string $confirm)
    {

        $this->pdo = $pdo;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->confirm = $confirm;
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
    
    public function login(): bool {
    
        $user = $this->findByEmail($this->email);
        if ($user && !password_verify($this->password, PASSWORD_DEFAULT))  {
            
            Session::set('userId', $user['id']);
            Session::set('name', $user['name']);
            return true;
        }
        return false;
    }

    public function register(): array {
        $errors = [];

        $name    = trim($this->name ?? '');
        $email   = trim($this->email ?? '');
        $pass    = $this->password ?? '';
        $confirm = $this->confirm ?? '';

        if ($name === '') {
            $errors[] = "Le nom est requis";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email non valide";
        }

        if (strlen($pass) < 8 || !preg_match('/[A-Za-z]/', $pass) || !preg_match('/\d/', $pass)) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères, des lettres et des chiffres.";
        }

        if ($pass !== $confirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        if (!empty($errors)) {
            return $errors;
        }

        // email exists ?
        $exist = $this->findByEmail($email);
        if ($exist) {
            $errors[] = "Cet email existe déjà.";
            return $errors;
        }

        // insert
        $hashPassword = password_hash($pass, PASSWORD_DEFAULT);

        $ok = $this->create($name, $email, $hashPassword);
        if (!$ok) {
            $errors[] = "Inscription impossible (erreur serveur).";
        }

        return $errors; 
    }

    public function check(): bool
    {
        return Session::get('userId') !== null;
    }

    public function userId(): ?int
    {
        $id = Session::get('userId');
        return $id !== null ? (int)$id : null;
    }


}
