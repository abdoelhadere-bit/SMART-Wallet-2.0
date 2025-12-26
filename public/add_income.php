<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$operation = $_GET['file'];
$operationObj = null;
$word = "";
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Session.php';
require __DIR__ . '/../app/Models/User.php';
require __DIR__ . '/../app/Models/'.$operation.'.php';

$config = require __DIR__ . '/../config/config.php';

$db  = new Database($config['db']);
$pdo = $db->getConnection();

$user = new User($pdo, "", "", "", "");
if (!$user->check()) {
    header('Location: ./index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: '.$operation.'.php');
    exit;
}

$user_id     = (int) Session::get('userId');
$amount      = (float) ($_POST['montant'] ?? 0);
$category_id = (int) ($_POST['category_id'] ?? 0);
$date        = trim($_POST['date'] ?? '');
$desc        = trim($_POST['description'] ?? '');

if ($operation === "incomes") {
    $operationObj = new Income($pdo);
    $word = "Revenu";

}elseif ($operation === "expenses") {
    $operationObj = new Expense($pdo);
    $word = "Depense"; 
}

$operationObj->setUserId($user_id);
$operationObj->setCategoryId($category_id);
$operationObj->setAmount($amount);
$operationObj->setDate($date);
$operationObj->setDescription($desc);

$newId = $operationObj->create();

if ($newId <= 0) {
    $errs = $operationObj->getErrors();
    Session::set('error', !empty($errs) ? implode(" | ", $errs) : "Ajout impossible.");
} else {
    Session::set('success', "$word ajoutée avec succès.");
}

header('Location: '.$operation.'.php');
exit;
