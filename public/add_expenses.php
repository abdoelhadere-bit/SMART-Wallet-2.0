<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Session.php';
require __DIR__ . '/../app/Models/User.php';
require __DIR__ . '/../app/Models/expenses.php';

$config = require __DIR__ . '/../config/config.php';

$db  = new Database($config['db']);
$pdo = $db->getConnection();

$user = new User($pdo, "", "", "", "");
if (!$user->check()) {
    header('Location: ./index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: expense.php');
    exit;
}

$user_id     = (int) Session::get('userId');
$amount      = (float) ($_POST['montant'] ?? 0);
$category_id = (int) ($_POST['category_id'] ?? 0);
$date        = trim($_POST['date'] ?? '');
$desc        = trim($_POST['description'] ?? '');

$expense = new Expense($pdo);
$expense->setUserId($user_id);
$expense->setCategoryId($category_id);
$expense->setAmount($amount);
$expense->setDate($date);
$expense->setDescription($desc);

$newId = $expense->create();

if ($newId <= 0) {
    $errs = $expense->getErrors();
    Session::set('error', !empty($errs) ? implode(" | ", $errs) : "Ajout impossible.");
} else {
    Session::set('success', "Dépense ajoutée avec succès.");
}

header('Location: expense.php');
exit;
