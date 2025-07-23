<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Обработка добавления пользователя
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($username && $password && in_array($role, ['admin', 'user'])) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role]);


        $success = "Пользователь добавлен.";
    } else {
        $error = "Заполните все поля корректно.";
    }
}

// Обработка удаления
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage_users.php");
    exit();
}

// Получение списка пользователей
$users = $pdo->query("SELECT id, username, role FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Управление пользователями</title>
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
      <h2>Добавить пользователя</h2>
      <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
      <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

      <form method="POST">
        <label>Имя пользователя:<br><input type="text" name="username" required></label><br>
        <label>Пароль:<br><input type="password" name="password" required></label><br>
        <label>Статус:<br>
          <select name="role" required>
            <option value="user">Обычный</option>
            <option value="admin">Админ</option>
          </select>
        </label><br>
        <button type="submit" class="auth-btn">Добавить</button>
      </form>

      <h2 style="margin-top:2em;">Список пользователей</h2>
      <table class="employee-table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Имя</th>
      <th>Пароль</th>
      <th>Роль</th> 
      <th>Действия</th>
    </tr>
  </thead>
  <tbody>
    <?php
    // Дополнительно получим пароль из базы (хеш)
    $stmt = $pdo->query("SELECT id, username, role, password FROM users ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['password']) ?></td>
        <td><?= $u['role'] === 'admin' ? 'Админ' : 'Обычный' ?></td>
        <td>
          <?php if ($u['id'] !== $_SESSION['user_id']): ?>
            <a href="?delete=<?= $u['id'] ?>" onclick="return confirm('Удалить пользователя?')">Удалить</a>
          <?php else: ?>
            Нельзя удалить себя
          <?php endif; ?>
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
