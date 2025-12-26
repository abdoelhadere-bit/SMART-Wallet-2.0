<?php
ini_set('display_errors', 1);
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

$user_id = (int) Session::get('userId');
$id      = (int) ($_GET['id'] ?? 0);

$expense = new Expense($pdo);
$ok = $expense->delete($id, $user_id);

if (!$ok) {
    Session::set('error', "Suppression impossible.");
} else {
    Session::set('success', "Dépense supprimée.");
}

header('Location: expense.php');
exit;
