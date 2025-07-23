<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Телефонный справочник</title>
  <link rel="stylesheet" href="styles.css">
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
    <?php session_start(); ?>
    <div class="header-right">
    <?php if (isset($_SESSION['user_id'])): ?>
      <span class="user-name"><?= htmlspecialchars($_SESSION['username']) ?></span>
      <button class="login-btn" onclick="location.href='dashboard.php'">Панель управления</button>
      <form action="logout.php" method="post" style="display:inline;">
        <button class="logout-btn">Выйти</button>
      </form>
    <?php else: ?>
      <button class="login-btn" onclick="location.href='login.php'">Войти</button>
    <?php endif; ?>
</div>

  </header>

  <main class="main-content">
    <!-- Левая колонка -->
    <aside class="sidebar">
  <div class="division-links">
    <a href="#" id="link-giredmet" onclick="switchDivision('giredmet')  ">Гиредмет</a> /
    <a href="#" id="link-graphite" onclick="switchDivision('graphite')">Графит</a>
  </div>

  <ul class="department-list" id="department-list">
    <!-- Сюда будут загружаться отделы -->
  </ul>
</aside>



    <!-- Центральная колонка -->
    <section class="center-panel">
    <input type="text" class="search-bar" id="searchInput" placeholder="Например: Иванов, Дарья, marina@gmail.com">
    <div id="emergencyBlock" class="emergency-block" style="margin-bottom: 1em;">
      <h3>Срочные службы</h3>
      <ul>
        <li><strong>Полиция:</strong> 102</a> / <a href="mailto:police@rosatom.ru">police@rosatom.ru</a></li>
        <li><strong>Скорая помощь:</strong> 103</a> / <a href="mailto:ambulance@rosatom.ru">ambulance@rosatom.ru</a></li>
        <li><strong>Пожарная служба:</strong> 101</a> / <a href="mailto:fire@rosatom.ru">fire@rosatom.ru</a></li>
        <li><strong>Отдел безопасности:</strong> +7 (495) 123-45-67</a></li>
      </ul>
    </div>
      <div id="employeeBlock" style="display:none;">
        <table class="employee-table">
        <thead>
          <tr>
            <th>ФИО</th>
            <th>Телефон</th>
            <th>Кабинет</th>
            <th>Email</th>
            <th>Должность</th>
            <th>Отдел</th>
            <th>Подразделение</th>
          </tr>
        </thead>
        <?php
          require 'db.php';
          $stmt = $pdo->query("
            SELECT e.*, d.name AS department_name, d.division
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
          ");
          $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
          ?>
          <tbody>
          <?php foreach ($employees as $emp): ?>
          <tr onclick="window.location.href='pages/profile.php?id=<?= $emp['id'] ?>'">
            <td><?= htmlspecialchars($emp['name']) ?></td>
            <td><?= htmlspecialchars($emp['phone']) ?></td>
            <td><?= htmlspecialchars($emp['room']) ?></td>
            <td><?= htmlspecialchars($emp['email']) ?></td>
            <td><?= htmlspecialchars($emp['position']) ?></td>
            <td><?= htmlspecialchars($emp['department_name']) ?></td>
            <td><?= htmlspecialchars($emp['division']) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Правая колонка -->
    <aside class="birthday-panel">
      <h3>Поздравляем с днём рождения!</h3>
      <div id="birthday-today">
        <img id="birthday-photo" src="stock-photo.jpg" alt="Фото сотрудника" class="birthday-photo">
        <p id="birthday-name" class="birthday-name">Загрузка...</p>
      </div>

      <div class="upcoming-birthdays">
        <h4>А завтра день рождения у...</h4>
        <ul id="birthday-tomorrow-list" class="birthday-list">
          <li>Загрузка...</li>
        </ul>
      </div>
    </aside>

  </main>
  <footer class="footer">
        Если обнаружили неточность, напишите на почту it_rosatom@mail.ru<br>
        © 2025 РОСАТОМ. Все права защищены.
    </footer>

<script>
async function switchDivision(name) {
  const list = document.getElementById('department-list');
  list.innerHTML = '<li>Загрузка...</li>';

  const institute = name === 'giredmet' ? 'Гиредмет' : 'Графит';

  try {
    const res = await fetch(`api/get_departments.php?institute=${encodeURIComponent(institute)}`);
    const departments = await res.json();
    list.innerHTML = '';

    if (Object.keys(departments).length === 0) {
      list.innerHTML = '<li>Нет отделов</li>';
      return;
    }

    for (const [deptName, divisions] of Object.entries(departments)) {
      const li = document.createElement('li');
      const deptId = divisions[0]?.department_id;
      li.innerHTML = `<strong><a href="#" onclick="loadEmployees(${deptId})">${deptName}</a></strong>`;

      if (divisions.length > 0) {
        const ul = document.createElement('ul');
        divisions.forEach(div => {
          const subLi = document.createElement('li');
          subLi.innerHTML = `<a href="#" onclick="loadEmployees(${deptId}, '${div.division}')">${div.division}</a>`;
          ul.appendChild(subLi);
        });
        li.appendChild(ul);
      }

      list.appendChild(li);
    }

    document.getElementById('link-giredmet').classList.remove('active-division');
    document.getElementById('link-graphite').classList.remove('active-division');
    document.getElementById(`link-${name}`).classList.add('active-division');

  } catch (err) {
    console.error(err);
    list.innerHTML = '<li>Ошибка загрузки</li>';
  }
}
</script>
<!-- Поисковая строка -->
  <script>
    const searchInput = document.getElementById('searchInput');
    const emergencyBlock = document.getElementById('emergencyBlock');
    const employeeBlock = document.getElementById('employeeBlock');
    const rows = document.querySelectorAll('.employee-table tbody tr');

    searchInput.addEventListener('input', function () {
      const query = this.value.toLowerCase().trim();

      if (!query) {
        // Если поле пустое, то просто не фильтруем
        rows.forEach(row => row.style.display = '');
        return;
      }

      rows.forEach(row => {
        const cells = Array.from(row.cells);
        const match = cells.some(cell => cell.textContent.toLowerCase().includes(query));
        row.style.display = match ? '' : 'none';
      });
    });
  </script>

  <script>
    async function loadEmployees(departmentId, division = '') {
  try {
    const url = `api/get_employees.php?department_id=${departmentId}&division=${encodeURIComponent(division)}`;
    const res = await fetch(url);
    const employees = await res.json();

    const tbody = document.querySelector('.employee-table tbody');
    tbody.innerHTML = '';

    employees.forEach(emp => {
      const row = document.createElement('tr');
      row.onclick = () => window.location.href = `pages/profile.php?id=${emp.id}`;
      row.innerHTML = `
        <td>${emp.name}</td>
        <td>${emp.phone}</td>
        <td>${emp.room}</td>
        <td>${emp.email}</td>
        <td>${emp.position}</td>
        <td>${emp.department_name}</td>
        <td>${emp.division || ''}</td>
      `;
      tbody.appendChild(row);
    });

    // Показываем блок сотрудников, скрываем блок экстренных
    document.getElementById('emergencyBlock').style.display = 'none';
    document.getElementById('employeeBlock').style.display = 'block';

  } catch (err) {
    console.error('Ошибка загрузки сотрудников:', err);
  }
}
  </script>

<script>
// Путь к заглушке, если фото нет
const STOCK_PHOTO = '../uploads/birthday-photo/emp_68804f98c8ff92.42160593.jpg';

let todayBirthdays = [];
let currentIndex = 0;
let carouselInterval = null;

function showBirthday(index) {
  const person = todayBirthdays[index];
  if (!person) return;

  const photoElem = document.getElementById('birthday-photo'); // исправлено!
  const nameElem = document.getElementById('birthday-name');

  // Формируем путь к фото
  const photoPath = person.photo
    ? '../uploads/birthday-photo/' + person.photo.replace(/\\/g, '/').split('/').pop()
    : STOCK_PHOTO;

  photoElem.src = photoPath;
  nameElem.textContent = person.name;
}

async function loadBirthdays() {
  try {
    const photoElem = document.getElementById('birthday-photo');
    const nameElem = document.getElementById('birthday-name');
    const tomorrowList = document.getElementById('birthday-tomorrow-list');

    nameElem.textContent = 'Загрузка...';
    photoElem.src = STOCK_PHOTO;
    tomorrowList.innerHTML = '<li>Загрузка...</li>';

    const response = await fetch('api/birthday.php');
    if (!response.ok) throw new Error('Ошибка сети');

    const data = await response.json();

    todayBirthdays = Array.isArray(data.today) ? data.today : [];
    const tomorrowBirthdays = Array.isArray(data.tomorrow) ? data.tomorrow : [];

    // Обновляем список завтрашних дней рождения
    tomorrowList.innerHTML = '';
    if (tomorrowBirthdays.length === 0) {
      tomorrowList.innerHTML = '<li>Именинников нету!</li>';
    } else {
      tomorrowBirthdays.forEach(person => {
        const li = document.createElement('li');
        li.textContent = person.name;
        tomorrowList.appendChild(li);
      });
    }

    if (todayBirthdays.length === 0) {
      nameElem.textContent = 'Именинников нету!';
      photoElem.src = STOCK_PHOTO;
      return;
    }

    currentIndex = 0;
    showBirthday(currentIndex);

    if (todayBirthdays.length > 1) {
      if (carouselInterval) clearInterval(carouselInterval);
      carouselInterval = setInterval(() => {
        currentIndex = (currentIndex + 1) % todayBirthdays.length;
        showBirthday(currentIndex);
      }, 5000);
    } else {
      if (carouselInterval) {
        clearInterval(carouselInterval);
        carouselInterval = null;
      }
    }

  } catch (error) {
    console.error('Ошибка загрузки именинников:', error);
    const nameElem = document.getElementById('birthday-name');
    const tomorrowList = document.getElementById('birthday-tomorrow-list');
    nameElem.textContent = 'Ошибка загрузки';
    tomorrowList.innerHTML = '<li>Ошибка загрузки</li>';
  }
}

window.addEventListener('DOMContentLoaded', loadBirthdays);
</script>
</body>
</html>
