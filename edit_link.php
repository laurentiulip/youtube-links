<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $url = trim($_POST['url']);
    $description = trim($_POST['description']);

    $stmt = $pdo->prepare("UPDATE links SET title = ?, url = ?, description = ? WHERE id = ?");
    $success = $stmt->execute([$title, $url, $description, $id]);

    echo json_encode(['success' => $success]);
}
