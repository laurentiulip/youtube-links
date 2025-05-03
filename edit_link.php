<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Neautorizat']);
    exit;
}

$id    = $_POST['id'] ?? null;
$title = $_POST['title'] ?? '';
$url   = $_POST['url'] ?? '';
$desc  = $_POST['description'] ?? '';

if (!$id || !$title || !$url) {
    echo json_encode(['success' => false, 'error' => 'Date incomplete']);
    exit;
}

$stmt = $pdo->prepare("UPDATE links SET title = ?, url = ?, description = ? WHERE id = ?");
$success = $stmt->execute([$title, $url, $desc, $id]);

echo json_encode(['success' => $success]);
