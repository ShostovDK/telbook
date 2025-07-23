<?php
require '../db.php'; // путь к базе, поправь если надо

header('Content-Type: application/json');

$today = date('d.m');
$tomorrow = date('d.m', strtotime('+1 day'));

// Функция для получения именинников
function getBirthdays($pdo, $date) {
    $stmt = $pdo->prepare("SELECT id, name, photo FROM employees WHERE birthday = ?");
    $stmt->execute([$date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$birthdaysToday = getBirthdays($pdo, $today);
$birthdaysTomorrow = getBirthdays($pdo, $tomorrow);

echo json_encode([
    'today' => $birthdaysToday,
    'tomorrow' => array_slice($birthdaysTomorrow, 0, 5), // максимум 5
]);
