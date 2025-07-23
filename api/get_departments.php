<?php
require '../db.php';

$institute = $_GET['institute'] ?? '';
if (!$institute) {
    http_response_code(400);
    echo json_encode(['error' => 'Институт не указан']);
    exit();
}

$stmt = $pdo->prepare("SELECT id, name, division FROM departments WHERE institute = ? ORDER BY name, division");
$stmt->execute([$institute]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Группируем по названию отдела
$grouped = [];
foreach ($data as $row) {
    $name = $row['name'];
    if (!isset($grouped[$name])) {
        $grouped[$name] = [];
    }

    $grouped[$name][] = [
        'division' => $row['division'],
        'department_id' => $row['id']
    ];
}

echo json_encode($grouped, JSON_UNESCAPED_UNICODE);
