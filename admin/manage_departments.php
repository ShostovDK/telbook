<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = false;
$editing = false;
$edit_id = $_GET['edit'] ?? null;
$edit_dep = null;

// Получение данных для редактирования
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_dep = $stmt->fetch();
    $editing = true;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $division = $_POST['division'] ?? '';
    $institute = $_POST['institute'] ?? '';
    $id = $_POST['id'] ?? null;

    if ($name && $institute) {
        if ($id) {
            // UPDATE
            $stmt = $pdo->prepare("UPDATE departments SET name=?, division=?, institute=? WHERE id=?");
            $stmt->execute([$name, $division, $institute, $id]);
            $success = true;
            $editing = false;
        } else {
            // INSERT
            $stmt = $pdo->prepare("INSERT INTO departments (name, division, institute) VALUES (?, ?, ?)");
            $stmt->execute([$name, $division, $institute]);
            $success = true;
        }
    } else {
        $error = 'Название отдела и институт обязательны';
    }
}

// Удаление
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: manage_departments.php');
    exit();
}

// Получить список отделов
$departments = $pdo->query("SELECT * FROM departments ORDER BY institute, division, name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Управление отделами</title>
  <link rel="stylesheet" href="../styles.css">
</head>
<body>
<header class="header">
  <div class="header-left">
    <img src="../logo.jpg" alt="Росатом" class="logo">
    <span class="company-name">РОСАТОМ</span>
  </div>
  <div class="header-center">
    <h1 class="gradient-text" onclick="location.href='../index.php'">Телефонный справочник</h1>
  </div>
  <div class="header-right">
    <span class="user-name"><?= htmlspecialchars($_SESSION['username']) ?></span>
    <form action="../logout.php" method="post">
      <button class="logout-btn">Выйти</button>
    </form>
  </div>
</header>

<main class="main-content">
  <section class="center-panel">
    <h2><?= $editing ? 'Редактировать отдел' : 'Добавить новый отдел' ?></h2>

    <?php if ($success): ?><p style="color:green;">Сохранено!</p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="POST">
      <input type="hidden" name="id" value="<?= $edit_dep['id'] ?? '' ?>">
      <label>Название отдела:<br>
        <input type="text" name="name" value="<?= htmlspecialchars($edit_dep['name'] ?? '') ?>" required>
      </label><br>
      <label>Подразделение:<br>
        <input type="text" name="division" value="<?= htmlspecialchars($edit_dep['division'] ?? '') ?>">
      </label><br>
      <label>Институт:<br>
        <select name="institute" required>
          <option value="">-- Выберите --</option>
          <option value="Гиредмет" <?= (isset($edit_dep['institute']) && $edit_dep['institute'] === 'Гиредмет') ? 'selected' : '' ?>>Гиредмет</option>
          <option value="Графит" <?= (isset($edit_dep['institute']) && $edit_dep['institute'] === 'Графит') ? 'selected' : '' ?>>Графит</option>
        </select>
      </label><br>
      <button type="submit" class="auth-btn"><?= $editing ? 'Сохранить' : 'Добавить' ?></button>
      <?php if ($editing): ?>
        <a href="manage_departments.php" class="auth-btn" style="margin-left: 10px;">Отмена</a>
      <?php endif; ?>
    </form>

    <h3>Существующие отделы</h3>
    <table class="employee-table">
      <thead>
        <tr><th>ID</th><th>Институт</th><th>Отдел</th><th>Подразделение</th><th>Действия</th></tr>
      </thead>
      <tbody>
        <?php foreach ($departments as $d): ?>
          <tr>
            <td><?= $d['id'] ?></td>
            <td><?= htmlspecialchars($d['institute']) ?></td>
            <td><?= htmlspecialchars($d['name']) ?></td>
            <td><?= htmlspecialchars($d['division']) ?></td>
            <td>
              <a href="?edit=<?= $d['id'] ?>">Редактировать</a> |
              <a href="?delete=<?= $d['id'] ?>" onclick="return confirm('Удалить?')">Удалить</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</main>

<footer class="footer">
  Если обнаружили неточность, напишите на почту it_rosatom@mail.ru<br>
  © 2025 РОСАТОМ. Все права защищены.
</footer>
</body>
</html>
