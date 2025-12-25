<?php

class AuthService
{
    private User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function login(string $email, string $password): bool
    {
        
        $user = $this->userModel->findByEmail($email);
        
        if ($user === null) {
            return false;
        }

        // if (!password_verify($password, $user['password'])) {
        //     return false;
        // }


        Session::set('user_id', $user['id']);
        Session::set('user_email', $user['email']);

        return true;
    }

    public function register(string $name, string $email, string $password, string $confirm): bool
    {
        $name = trim($name);
        $email = trim($email);

        // validations
        if ($name === '') return false;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
        if (strlen($password) < 8) return false;
        if ($password !== $confirm) return false;

        // Verifier email 
        if ($this->userModel->findByEmail($email)) {
            return false;
        }

        
        $hash = password_hash($password, PASSWORD_DEFAULT);

        return $this->userModel->create($name, $email, $hash);
    }

    public function check(): bool
    {
        return Session::get('user_id') !== null;
    }

    public function userId(): ?int
    {
        $id = Session::get('user_id');
        return $id !== null ? (int)$id : null;
    }
}
