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

$user_id = (int) Session::get('user_id');
$userRow = $user->getById($user_id);
$user_name = $userRow['name'] ?? 'User';

$categoryModel = new Category($pdo);

// Filtre 
$typeFilter = $_GET['type'] ?? 'all';
if ($typeFilter === 'income' || $typeFilter === 'expense') {
    $categories = $categoryModel->getByType($typeFilter);
} else {
    $categories = $categoryModel->getAll();
}

$error = Session::get('error');
$success = Session::get('success');
Session::remove('error');
Session::remove('success');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
  <title>Gestion des Catégories</title>

  <style>
    body { background:#0f1117; color:#e5e7eb; font-family:Inter,sans-serif; }
    .sidebar { width:280px; height:100vh; position:fixed; left:0; top:0; background:linear-gradient(180deg,#1a1d29,#0f1117); border-right:1px solid rgba(255,255,255,.08); display:flex; flex-direction:column; }
    .sidebar-item { display:flex; align-items:center; padding:14px 20px; margin:6px 12px; border-radius:12px; color:#9ca3af; transition:.2s; text-decoration:none; }
    .sidebar-item:hover { background:rgba(59,130,246,.1); color:#3b82f6; }
    .sidebar-item.active { background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; }
    .sidebar-item svg { width: 22px; height: 22px; margin-right: 12px;}
    .main { margin-left:280px; padding:24px; }
    .card { background:rgba(30,32,40,.75); backdrop-filter:blur(14px); border:1px solid rgba(255,255,255,.06); border-radius:16px; padding:24px; }
    .btn-primary { background:linear-gradient(to right,#3b82f6,#2563eb); padding:10px 16px; border-radius:10px; font-weight:600; }
    .btn-warning { background:rgba(245,158,11,.2); color:#f59e0b; padding:6px 12px; border-radius:8px; }
    .btn-danger { background:rgba(239,68,68,.2); color:#ef4444; padding:6px 12px; border-radius:8px; }
    .btn-secondary { background:#374151; padding:10px 16px; border-radius:10px; }
    table th { text-transform:uppercase; font-size:13px; color:#9ca3af; padding:12px; }
    table td { padding:14px; }
    table tr:hover { background:rgba(255,255,255,.06); }
  </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="p-6 border-b border-gray-700/50">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center font-bold">
        <?= strtoupper(substr($user_name, 0, 1)) ?>
      </div>
      <div>
        <p class="font-semibold"><?= htmlspecialchars($user_name) ?></p>
        <p class="text-xs text-gray-400">Utilisateur</p>
      </div>
    </div>
  </div>

  <nav class="py-4">
        <nav class="py-4">
        <a href="./dashboard.php" class="sidebar-item">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>

        <a href="./income.php" class="sidebar-item">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Revenus
        </a>

        <a href="./expense.php" class="sidebar-item">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Dépenses
        </a>

        <a href="#" class="sidebar-item active">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h6v6H4V6zm10 0h6v6h-6V6zM4 14h6v6H4v-6zm10 4a2 2 0 100-4 2 2 0 000 4z" />
            </svg>
            Categorie
        </a>
    </nav>

  <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-700/50">
    <a href="logout.php" class="sidebar-item bg-red-500/10 hover:bg-red-500/20 text-red-400">Déconnexion</a>
  </div>
</div>

<!-- MAIN -->
<div class="main">
  <div class="flex justify-between items-center mb-6">
    <div>
      <h1 class="text-3xl font-bold">📂 Catégories</h1>
      <p class="text-gray-400">Créer, modifier et supprimer des catégories</p>
    </div>
    <button id="btnAdd" class="btn-primary">+ Ajouter une catégorie</button>
  </div>

  <!-- Alerts -->
  <?php if (!empty($error)): ?>
    <div class="mb-4 p-3 rounded-xl bg-red-500/15 border border-red-500/30 text-red-300">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
    <div class="mb-4 p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300">
      <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>

  <!-- Filter -->
  <div class="card mb-6">
    <form method="GET" class="flex gap-3 items-center">
      <label class="text-gray-300 font-semibold">Filtrer :</label>
      <select name="type" class="bg-gray-900 border border-gray-700 rounded-xl p-2">
        <option value="all" <?= $typeFilter==='all'?'selected':'' ?>>Toutes</option>
        <option value="income" <?= $typeFilter==='income'?'selected':'' ?>>Income</option>
        <option value="expense" <?= $typeFilter==='expense'?'selected':'' ?>>Expense</option>
      </select>
      <button class="btn-secondary" type="submit">Appliquer</button>
      <a class="btn-secondary" href="categories.php">Reset</a>
    </form>
  </div>

  <!-- Table -->
  <div class="card overflow-x-auto">
    <table class="w-full">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Type</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($categories)): ?>
        <tr>
          <td colspan="4" class="text-center text-gray-400 py-6">Aucune catégorie.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($categories as $c): ?>
          <tr>
            <td><?= (int)$c['id'] ?></td>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td>
              <span class="px-2 py-1 rounded-lg text-xs
                <?= $c['type']==='income' ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/20' : 'bg-amber-500/15 text-amber-300 border border-amber-500/20' ?>">
                <?= htmlspecialchars($c['type']) ?>
              </span>
            </td>
            <td class="text-center">
              <div class="flex justify-center gap-2">
                <button class="btn-warning editBtn"
                        data-id="<?= (int)$c['id'] ?>"
                        data-name="<?= htmlspecialchars($c['name']) ?>"
                        data-type="<?= htmlspecialchars($c['type']) ?>">
                  Modifier
                </button>
                <a class="btn-danger"
                   href="delete_category.php?id=<?= (int)$c['id'] ?>"
                   onclick="return confirm('Supprimer cette catégorie ?');">
                   Supprimer
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL -->
<div id="formModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center">
  <div class="bg-gray-800 w-96 p-6 rounded-2xl shadow-2xl border border-gray-700">
    <h2 id="formTitle" class="text-xl font-bold mb-4 text-gray-100">Ajouter une catégorie</h2>

    <form id="categoryForm" class="space-y-4" action="add_category.php" method="POST" data-parsley-validate>
      <input type="hidden" id="categoryId" name="id">

      <div>
        <label class="block mb-1 font-semibold text-gray-200">Nom</label>
        <input type="text" name="name" id="categoryName" required
               class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500"
               data-parsley-minlength="2"
               data-parsley-maxlength="100"
               data-parsley-trigger="change">
      </div>

      <div>
        <label class="block mb-1 font-semibold text-gray-200">Type</label>
        <select name="type" id="categoryType" required
                class="w-full p-2.5 border border-gray-600 rounded-xl bg-gray-900 text-gray-100 focus:ring-2 focus:ring-blue-500">
          <option value="">Choisir</option>
          <option value="income">income</option>
          <option value="expense">expense</option>
        </select>
      </div>

      <div class="flex justify-end gap-2 mt-4">
        <button type="button" id="cancelBtn" class="btn-secondary">Annuler</button>
        <button type="submit" id="submitBtn" class="btn-primary opacity-50 cursor-not-allowed" disabled>Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
  const modal = document.getElementById("formModal");
  const btnAdd = document.getElementById("btnAdd");
  const cancelBtn = document.getElementById("cancelBtn");
  const form = document.getElementById("categoryForm");
  const formTitle = document.getElementById("formTitle");
  const submitBtn = document.getElementById("submitBtn");
  const parsleyCheck = $(form).parsley();

  function toggleSubmit() {
    if (parsleyCheck.isValid()) {
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-50','cursor-not-allowed');
    } else {
      submitBtn.disabled = true;
      submitBtn.classList.add('opacity-50','cursor-not-allowed');
    }
  }

  function openModal() {
    modal.classList.remove("hidden");
    setTimeout(() => { parsleyCheck.validate(); toggleSubmit(); }, 0);
  }
  function closeModal() {
    modal.classList.add("hidden");
  }

  btnAdd.onclick = () => {
    formTitle.textContent = "Ajouter une catégorie";
    form.action = "add_category.php";
    form.reset();
    document.getElementById("categoryId").value = "";
    openModal();
  };

  cancelBtn.onclick = closeModal;

  document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      formTitle.textContent = "Modifier une catégorie";
      form.action = "update_category.php";

      document.getElementById("categoryId").value = this.dataset.id;
      document.getElementById("categoryName").value = this.dataset.name;
      document.getElementById("categoryType").value = this.dataset.type;

      openModal();
    });
  });

  form.addEventListener('input', toggleSubmit);
</script>

</body>
</html>
