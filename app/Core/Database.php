<?php

class Database
{
    private string $host;
    private string $dbname;
    private string $user;
    private string $pass;
    private string $charset;

    private ?PDO $pdo = null;

    public function __construct(array $config)
    {
        $this->host    = $config['host'];
        $this->dbname  = $config['name'];
        $this->user    = $config['user'];
        $this->pass    = $config['pass'];
        $this->charset = $config['charset'] ?? 'utf8mb4';
    }

    public function getConnection(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        return $this->pdo;
    }
}
