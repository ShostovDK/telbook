<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    header("Location: index.php");
    exit();
} else {
    $error = "Неверный логин или пароль";
}

}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Вход в систему</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
  <header class="header">
    <div class="header-left">
      <img src="logo.jpg" alt="Росатом" class="logo">
      <span class="company-name">РОСАТОМ</span>
    </div>
    <div class="header-center">
        <h1 class="gradient-text" onclick="location.href='index.php'">Телефонный справочник</h1>
    </div>
    <div class="header-right">
      
    </div>
  </header>

  <div class="login-container">
    <h2>Войдите в аккаунт телефонного справочника</h2>
    <?php if (!empty($error)): ?>
      <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form class="login-form" method="POST" action="login.php">
      <div class="form-group">
        <label for="username">Логин:</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="form-group remember">
        <input type="checkbox" id="remember" name="remember">
        <label for="remember">Запомнить меня</label>
      </div>
      <button type="submit" class="auth-btn">Авторизоваться</button>
    </form>
    <div class="login-links">
      <a href="#">Забыли пароль?</a>
      <a href="#">Связаться с поддержкой</a>
    </div>
  </div>
</body>
</html>
