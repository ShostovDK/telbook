<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$isAdmin = ($_SESSION['role'] === 'admin');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Панель управления</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .control-buttons {
      display: flex;
      flex-direction: column;
      gap: 1em;
      margin-top: 2em;
    }

    .control-buttons a {
      display: block;
      padding: 12px 20px;
      background-color: #0077cc;
      color: white;
      text-align: center;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      transition: background-color 0.3s;
    }

    .control-buttons a:hover {
      background-color: #005fa3;
    }
  </style>
</head>
<body>
  <header class="header">
    <div class="header-left">
      <img src="logo.jpg" alt="Росатом" class="logo">
      <span class="company-name">РОСАТОМ</span>
    </div>
    <div class="header-center">
      <h1 class="gradient-text" onclick="location.href='index.php'">Телефонный справочник</h1>
    </div>
    <div class="header-right">
      <span class="user-name"><?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?>)</span>
      <form action="logout.php" method="post" style="display:inline;">
        <button class="logout-btn">Выйти</button>
      </form>
    </div>
  </header>

  <main class="main-content">
    <section class="center-panel">
      <h2>Панель управления</h2>

      <div class="control-buttons">
        <a href="user/add_employee.php">Добавить сотрудника</a>
        <?php if ($isAdmin): ?>
          <a href="admin/edit_employee.php">Редактировать / Удалить сотрудника</a>
          <a href="admin/manage_departments.php">Управление отделами</a>
          <a href="admin/manage_users.php">Управление пользователями</a>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="footer">
    Если обнаружили неточность, напишите на почту it_rosatom@mail.ru<br>
    © 2025 РОСАТОМ. Все права защищены.
  </footer>
</body>
</html>
