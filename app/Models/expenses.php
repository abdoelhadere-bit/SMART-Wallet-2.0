<?php
require_once __DIR__ . '/Transactions.php';

class Expense extends Transaction
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table = "expenses";
    }

    protected function categoryType(): string
    {
        return "expense";
    }

    public function getExpenseCategories(): array
    {
        return $this->getCategories();
    }
}
