<?php
require '../db.php';

$department_id = $_GET['department_id'] ?? null;
$division = $_GET['division'] ?? null;

$sql = "
    SELECT e.*, d.name AS department_name, d.division
    FROM employees e
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE 1=1
";
$params = [];

if ($department_id) {
    $sql .= " AND e.department_id = ?";
    $params[] = $department_id;
}

if ($division) {
    $sql .= " AND d.division = ?";
    $params[] = $division;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
