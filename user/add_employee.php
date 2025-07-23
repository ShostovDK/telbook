<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['user','admin'])) {
    header('Location: ../login.php');
    exit();
}

$success = false;
$error = '';

$stmt = $pdo->query("SELECT id, name, division FROM departments ORDER BY division, name");
$departments = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $room = $_POST['room'] ?? '';
    $email = $_POST['email'] ?? '';
    $position = $_POST['position'] ?? '';
    $department_id = $_POST['department_id'] ?? null;
    $photoFilename = null;
    if (!empty($_FILES['photo']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if ($_FILES['photo']['size'] > $maxSize) {
            $error = "Файл слишком большой. Максимум 5MB.";
        } elseif (!in_array(mime_content_type($_FILES['photo']['tmp_name']), $allowed)) {
            $error = "Разрешены только JPG, PNG, WEBP.";
        } else {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photoFilename = uniqid('emp_', true) . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/$photoFilename");
        }
    }
    $photo = $photoFilename ?? '';
    $description = $_POST['description'] ?? '';

    if ($name && $department_id) {
        $stmt = $pdo->prepare("INSERT INTO employees (name, phone, room, email, position, department_id, photo, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $room, $email, $position, $department_id, $photo, $description]);
        $success = true;
    } else {
        $error = "Поле ФИО и отдел обязательны.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Добавить сотрудника</title>
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
      <h2>Добавить сотрудника</h2>
      <?php if ($success): ?><p style="color:green;">Сотрудник добавлен!</p><?php endif; ?>
      <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <label>ФИО:<br><input type="text" name="name" required></label><br>
        <label>Телефон:<br><input type="text" name="phone"></label><br>
        <label>Кабинет:<br><input type="text" name="room"></label><br>
        <label>Email:<br><input type="email" name="email"></label><br>
        <label>Должность:<br><input type="text" name="position"></label><br>
        <label>Отдел:<br>
        <select name="department_id" required>
            <option value="">-- выберите отдел --</option>
            <?php
            $currentDivision = null;
            foreach ($departments as $d):
                if ($d['name'] !== $currentDivision) {
                    if ($currentDivision !== null) echo '</optgroup>';
                    $currentDivision = $d['name'];
                    echo '<optgroup label="'.htmlspecialchars($currentDivision).'">';
                }
            ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['division']) ?></option>
            <?php endforeach;
            if ($currentDivision !== null) echo '</optgroup>';
            ?>
        </select>
        </label><br>
        <label>Фото:<br><input type="file" name="photo" accept="image/*"></label><br>
        <label>Описание:<br><textarea name="description"></textarea></label><br>
        <button type="submit" class="auth-btn">Добавить</button>
      </form>
    </section>
  </main>

  <footer class="footer">
    Если обнаружили неточность, напишите на почту it_rosatom@mail.ru<br>© 2025 РОСАТОМ. Все права защищены.
  </footer>
</body>
</html>
