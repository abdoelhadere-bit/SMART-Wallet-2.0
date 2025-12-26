<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Session.php';
require __DIR__ . '/../app/Models/User.php';
require __DIR__ . '/../app/Models/category.php';

$config = require __DIR__ . '/../config/config.php';

$db  = new Database($config['db']);
$pdo = $db->getConnection();

$user = new User($pdo, "", "", "", "");
if (!$user->check()) {
    header('Location: ./index.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);

$categoryModel = new Category($pdo);
$ok = $categoryModel->delete($id);

if (!$ok) {
    Session::set('error', "Suppression impossible : catégorie utilisée dans incomes/expenses (ou id invalide).");
} else {
    Session::set('success', "Catégorie supprimée avec succès.");
}

header('Location: categories.php');
exit;
