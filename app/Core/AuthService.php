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
        $email = trim($email);

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        Session::start();
        Session::set('user_id', (int)$user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);

        return true;
    }



}

?>