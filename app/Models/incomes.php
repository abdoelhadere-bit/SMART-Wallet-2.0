<?php
require_once __DIR__ . '/Transactions.php';

class Income extends Transaction
{

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->table = "incomes";
    }

    protected function categoryType(): string
    {
        return "income";
    }

    public function getIncomeCategories(): array
    {
        return $this->getCategories();
    }
}
