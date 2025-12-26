<?php

class Category
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    public function create(string $name, string $type): int
    {
        $name = trim($name);
        $type = trim($type);

        if (!$this->isValidType($type)) return 0;
        if (strlen($name) < 2 || strlen($name) > 100) return 0;

        // éviter doublon (même name + même type)
        if ($this->existsByNameAndType($name, $type)) return 0;

        $sql = "INSERT INTO categories (name, type) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);

        $ok = $stmt->execute([$name, $type]);
        return $ok ? (int)$this->pdo->lastInsertId() : 0;
    }

    public function getAll(): array
    {
        $sql = "SELECT id, name, type FROM categories ORDER BY type ASC, name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT id, name, type FROM categories WHERE id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // filtre (income / expense)
    public function getByType(string $type): array
    {
        $type = trim($type);
        if (!$this->isValidType($type)) return [];

        $sql = "SELECT id, name, type FROM categories WHERE type = ? ORDER BY name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function update(int $id, string $name, string $type): bool
    {
        $name = trim($name);
        $type = trim($type);

        if ($id <= 0) return false;
        if (!$this->isValidType($type)) return false;
        if (strlen($name) < 2 || strlen($name) > 100) return false;

        // éviter doublon (sauf si c'est la même ligne)
        $sql = "SELECT id FROM categories WHERE name = ? AND type = ? AND id <> ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $type, $id]);
        if ($stmt->fetchColumn()) return false;

        $sql = "UPDATE categories SET name = ?, type = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$name, $type, $id]);
    }

    public function delete(int $id): bool
    {
        if ($id <= 0) return false;

        // sécurité: ne pas supprimer si utilisée par incomes/expenses
        if ($this->isUsedInTransactions($id)) {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }



    private function isValidType(string $type): bool
    {
        return in_array($type, ['income', 'expense'], true);
    }

    private function existsByNameAndType(string $name, string $type): bool
    {
        $sql = "SELECT id FROM categories WHERE name = ? AND type = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $type]);
        return (bool)$stmt->fetchColumn();
    }

    private function isUsedInTransactions(int $categoryId): bool
    {
        // utilisé dans incomes ?
        $stmt = $this->pdo->prepare("SELECT id FROM incomes WHERE category_id = ? LIMIT 1");
        $stmt->execute([$categoryId]);
        if ($stmt->fetchColumn()) return true;

        // utilisé dans expenses ?
        $stmt = $this->pdo->prepare("SELECT id FROM expenses WHERE category_id = ? LIMIT 1");
        $stmt->execute([$categoryId]);
        if ($stmt->fetchColumn()) return true;

        return false;
    }
}
