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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: categories.php');
    exit;
}

$id   = (int)($_POST['id'] ?? 0);
$name = $_POST['name'] ?? '';
$type = $_POST['type'] ?? '';

$categoryModel = new Category($pdo);

$ok = $categoryModel->update($id, $name, $type);

if (!$ok) {
    Session::set('error', "Impossible de modifier (données invalides ou catégorie déjà existante).");
} else {
    Session::set('success', "Catégorie modifiée avec succès.");
}

header('Location: categories.php');
exit;
