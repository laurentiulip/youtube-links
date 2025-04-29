<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    
    $stmt = $pdo->prepare("DELETE FROM links WHERE id = ?");
    $success = $stmt->execute([$id]);

    echo json_encode(['success' => $success]);
}
