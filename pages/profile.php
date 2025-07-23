<?php
session_start();
require '../db.php';
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
  SELECT e.*, d.name AS department_name, d.division, d.institute
  FROM employees e
  LEFT JOIN departments d ON e.department_id = d.id
  WHERE e.id = ?
");
$stmt->execute([$id]);
$employee = $stmt->fetch();
if (!$employee) die("Сотрудник не найден.");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($employee['name']) ?></title>
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
    <?php if (isset($_SESSION['user_id'])): ?>
      <span class="user-name"><?= htmlspecialchars($_SESSION['username']) ?></span>
      <button class="login-btn" onclick="location.href='../dashboard.php'">Панель управления</button>
      <form action="../logout.php" method="post" style="display:inline;">
        <button class="logout-btn">Выйти</button>
      </form>
    <?php else: ?>
      <button class="login-btn" onclick="location.href='../login.php'">Войти</button>
    <?php endif; ?>
  </div>
</header>

<main class="main-content">
  <div class="main-profile">
    <div class="breadcrumbs"><a href="../index.php">← Назад</a></div>
    <div class="profile-card">
      <div class="profile-photo">
        <img src="../uploads/<?= htmlspecialchars($employee['photo']) ?>" alt="Фото">
      </div>
      <div class="profile-info">
        <h2><?= htmlspecialchars($employee['name']) ?></h2>
        <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></p>
        <p><strong>Институт:</strong> <?= htmlspecialchars($employee['institute']) ?></p>
        <p><strong>Отдел:</strong> <?= htmlspecialchars($employee['department_name']) ?></p>
        <p><strong>Подразделение:</strong> <?= htmlspecialchars($employee['division']) ?></p>
        <p><strong>Телефон:</strong> <?= htmlspecialchars($employee['phone']) ?></p>
        <p><strong>Кабинет:</strong> <?= htmlspecialchars($employee['room']) ?></p>
        <?php
        if (!empty($employee['birthday'])) {
          $birthDate = new DateTime($employee['birthday']);
          $months = [
            1 => 'Января', 2 => 'Февраля', 3 => 'Марта', 4 => 'Апреля',
            5 => 'Мая', 6 => 'Июня', 7 => 'Июля', 8 => 'Августа',
            9 => 'Сентября', 10 => 'Октября', 11 => 'Ноября', 12 => 'Декабря'
          ];
          $birthDay = $birthDate->format('j');
          $birthMonth = $months[(int)$birthDate->format('n')];
          echo "<p><strong>Дата рождения:</strong> {$birthDay} {$birthMonth}</p>";
        }
        ?>
      </div>
    </div>
    <div class="profile-description">
      <?= nl2br(htmlspecialchars($employee['description'])) ?>
    </div>
  </div>
</main>

<footer class="footer">
  Если обнаружили неточность, напишите на почту it_rosatom@mail.ru<br>
  © 2025 РОСАТОМ. Все права защищены.
</footer>

</body>
</html>
