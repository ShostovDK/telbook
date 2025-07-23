<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$stmt = $pdo->query("SELECT id, name, division FROM departments ORDER BY division, name");
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
$success = false;
$error = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id=?");
    $stmt->execute([$id]);
    $emp = $stmt->fetch();

    if (!$emp) die("Сотрудник не найден");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $photoFilename = $emp['photo']; // оставляем текущее фото по умолчанию

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

                // Если старое фото существует, можно удалить файл (опционально)
                if ($emp['photo'] && file_exists("../uploads/" . $emp['photo'])) {
                    unlink("../uploads/" . $emp['photo']);
                }
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare("UPDATE employees SET name=?, phone=?, room=?, email=?, position=?, department_id=?, photo=?, description=?, birthday=? WHERE id=?");
            $stmt->execute([
                $_POST['name'], $_POST['phone'], $_POST['room'], $_POST['email'],
                $_POST['position'], $_POST['department_id'], $photoFilename, $_POST['description'],
                $_POST['birthday'], $id
            ]);
            $success = true;

            $stmt = $pdo->prepare("SELECT * FROM employees WHERE id=?");
            $stmt->execute([$id]);
            $emp = $stmt->fetch();
        }
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: edit_employee.php');
    exit();
}


$query = "
SELECT e.*, d.name AS department_name, d.division 
FROM employees e 
LEFT JOIN departments d ON e.department_id = d.id 
ORDER BY e.name
";
$stmt = $pdo->query($query);
$employees = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Редактировать сотрудников</title>
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
      <?php if ($id): ?>
        <h2>Редактировать: <?= htmlspecialchars($emp['name']) ?></h2>
        <?php if ($success): ?><p style="color:green;">Сохранено!</p><?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
          <label>ФИО:<br><input type="text" name="name" value="<?= htmlspecialchars($emp['name']) ?>" required></label><br>
          <label>Телефон:<br><input type="text" name="phone" value="<?= htmlspecialchars($emp['phone']) ?>"></label><br>
          <label>Кабинет:<br><input type="text" name="room" value="<?= htmlspecialchars($emp['room']) ?>"></label><br>
          <label>Email:<br><input type="email" name="email" value="<?= htmlspecialchars($emp['email']) ?>"></label><br>
          <label>Должность:<br><input type="text" name="position" value="<?= htmlspecialchars($emp['position']) ?>"></label><br>
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
                <option value="<?= $d['id'] ?>" <?= $d['id'] == $emp['department_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($d['division']) ?>
                </option>
                <?php endforeach;
                if ($currentDivision !== null) echo '</optgroup>';
                ?>
            </select>
            </label><br>
            <label>Фото:<br>
                <?php if (!empty($emp['photo']) && file_exists("../uploads/" . $emp['photo'])): ?>
                <img src="../uploads/<?= htmlspecialchars($emp['photo']) ?>" alt="Фото сотрудника" style="max-width: 150px; display: block; margin-bottom: 10px;">
                <?php endif; ?>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
            </label><br>
            <label>Описание:<br><textarea name="description"><?= htmlspecialchars($emp['description']) ?></textarea></label><br>
          <label>День рождения (дд.мм):<br>
            <input type="text" name="birthday" 
                value="<?= isset($emp['birthday']) ? htmlspecialchars($emp['birthday']) : '' ?>" 
                pattern="\d{2}\.\d{2}" placeholder="например: 23.07">
        </label><br>
          <button type="submit" class="auth-btn">Сохранить</button>
          <a href="edit_employee.php" class="auth-btn" style="margin-left:10px;">Назад к списку</a>
        </form>
      <?php else: ?>
        <h2>Сотрудники</h2>

        <input type="text" id="searchInput" class="search-bar" placeholder="Поиск сотрудников..." style="margin-bottom: 1em; width: 100%;">

        <table class="employee-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>ФИО</th>
              <th>Телефон</th>
              <th>Email</th>
              <th>Отдел</th>
              <th>Подразделение</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($employees as $e): ?>
              <tr>
                <td><?= $e['id'] ?></td>
                <td><?= htmlspecialchars($e['name']) ?></td>
                <td><?= htmlspecialchars($e['phone']) ?></td>
                <td><?= htmlspecialchars($e['email']) ?></td>
                <td><?= htmlspecialchars($e['department_name']) ?></td>
                <td><?= htmlspecialchars($e['division']) ?></td>
                <td>
                  <a href="edit_employee.php?id=<?= $e['id'] ?>">Редактировать</a> |
                  <a href="edit_employee.php?delete=<?= $e['id'] ?>" onclick="return confirm('Удалить сотрудника?')">Удалить</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>

  <footer class="footer">
    Если обнаружили неточность, напишите на почту it_rosatom@mail.ru<br>
    © 2025 РОСАТОМ. Все права защищены.
  </footer>
  <script>
  document.getElementById('searchInput').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('.employee-table tbody tr');

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(query) ? '' : 'none';
    });
  });
</script>

</body>
</html>
