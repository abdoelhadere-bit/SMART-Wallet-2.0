<?php

abstract class Transaction
{
    protected PDO $pdo;
    protected string $table = "";

    protected ?int $id = null;
    protected int $userId = 0;
    protected int $categoryId = 0;
    protected float $amount = 0.0;
    protected string $description = "";
    protected string $date = "";
    protected ?string $createdAt = null;

    protected array $errors = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    abstract protected function categoryType(): string;

    public function setId(?int $id): void { $this->id = $id; }
    public function setUserId(int $userId): void { $this->userId = $userId; }
    public function setCategoryId(int $categoryId): void { $this->categoryId = $categoryId; }

    public function setAmount(float $amount): void { $this->amount = (float)$amount; }

    public function setDescription(string $description): void { $this->description = trim($description); }
    public function setDate(string $date): void { $this->date = trim($date); }

     public function getErrors(): array   
    {
        return $this->errors;
    }

    // Validation
    protected function validate(): array
    {
        $errors = [];

        if ($this->userId <= 0) $errors[] = "User invalide.";
        if ($this->categoryId <= 0) $errors[] = "Catégorie invalide.";
        if ($this->amount <= 0) $errors[] = "Le montant doit être > 0.";

        if (strlen($this->description) < 2) $errors[] = "Description trop courte.";
        if (strlen($this->description) > 255) $errors[] = "Description trop longue (max 255).";

        // $dt = DateTime::createFromFormat('Y-m-d', $this->date);
        // if (!$dt || $dt->format('Y-m-d') !== $this->date) {
        //     $errors[] = "Date invalide (format attendu: YYYY-MM-DD).";
        // }

        if (!$this->validateCategoryType()) {
            $errors[] = "Catégorie introuvable ou type incorrect ({$this->categoryType()}).";
        }

        $this->errors = $errors;
        return $errors;
    }

    protected function validateCategoryType(): bool
    {
        $sql = "SELECT id FROM categories WHERE id = ? AND type = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->categoryId, $this->categoryType()]);
        return (bool)$stmt->fetchColumn(); 
    }

 
    // CRUD
    public function create(): int
{
    $errors = $this->validate();
    if (!empty($errors)) return 0;

    $sql = "INSERT INTO {$this->table} (user_id, category_id, montant, description, `date` )
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $this->pdo->prepare($sql);

    $ok = $stmt->execute([
        $this->userId,
        $this->categoryId,
        $this->amount,
        $this->description,
        $this->date
    ]);

    if (!$ok) {
        return 0;
    }
    $this->id = (int)$this->pdo->lastInsertId();
    return $this->id;
}


    public function getAll(int $userId): array
    {
        $sql = "SELECT t.id, t.montant, t.description, t.date,
                       t.category_id, c.name AS category_name
                FROM {$this->table} t
                JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = ?
                ORDER BY t.date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById(int $id, int $userId): ?array
    {
        $sql = "SELECT t.*, c.name AS category_name
                FROM {$this->table} t
                JOIN categories c ON t.category_id = c.id
                WHERE t.id = ? AND t.user_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getByCategory(int $userId, int $categoryId): array
{
    $sql = "SELECT t.id, t.montant, t.description, t.date,
                   t.category_id, c.name AS category_name
            FROM {$this->table} t
            JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ? AND t.category_id = ?
            ORDER BY t.date DESC";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$userId, $categoryId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}


    public function update(): bool
    {
        if ($this->id === null) return false;

        $errors = $this->validate();
        if (!empty($errors)) return false;

        $sql = "UPDATE {$this->table}
                SET category_id = ?, montant = ?, description = ?, date = ?
                WHERE id = ? AND user_id = ?";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $this->categoryId,
            $this->amount,
            $this->description,
            $this->date,
            $this->id,
            $this->userId
        ]);
    }

    public function delete(int $id, int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id, $userId]);
    }


    public function getCategories(): array
    {
        $stmt = $this->pdo->prepare("SELECT id, name FROM categories WHERE type = ? ORDER BY name ASC");
        $stmt->execute([$this->categoryType()]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTotalByUser(int $userId): float
    {
        $sql = "SELECT COALESCE(SUM(montant), 0) FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return (float)$stmt->fetchColumn();
    }
}
