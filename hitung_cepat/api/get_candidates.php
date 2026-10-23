<?php
require '../config/database.php';
header('Content-Type: application/json');

$category_id = $_GET['category_id'] ?? 0;
$stmt = $pdo->prepare("SELECT id, name, photo FROM candidates WHERE category_id = ? ORDER BY name");
$stmt->execute([$category_id]);
echo json_encode($stmt->fetchAll());