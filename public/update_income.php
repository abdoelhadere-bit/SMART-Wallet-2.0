<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Session.php';
require __DIR__ . '/../app/Models/User.php';
require __DIR__ . '/../app/Models/incomes.php';

$config = require __DIR__ . '/../config/config.php';

$db  = new Database($config['db']);
$pdo = $db->getConnection();

$user = new User($pdo, "", "", "", "");
if (!$user->check()) {
    header('Location: ./index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: incomes.php');
    exit;
}

$user_id     = (int) Session::get('userId');
$id          = (int) ($_POST['incomeId'] ?? 0);
$amount      = (float) ($_POST['montant'] ?? 0);
$category_id = (int) ($_POST['category_id'] ?? 0);
$date        = trim($_POST['date'] ?? '');
$desc        = trim($_POST['description'] ?? '');

$income = new Income($pdo);
$income->setId($id);
$income->setUserId($user_id);
$income->setCategoryId($category_id);
$income->setAmount($amount);
$income->setDate($date);
$income->setDescription($desc);

$ok = $income->update();

if (!$ok) {
    Session::set('error', "Modification impossible (id invalide ou données invalides).");
} else {
    Session::set('success', "Revenu modifié avec succès.");
}

header('Location: incomes.php');
exit;
