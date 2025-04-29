<?php
header('Content-Type: application/json');
include 'config.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $url = trim($_POST['url']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if (empty($title) || empty($url)) {
        $response['error'] = 'Название и URL обязательны для заполнения';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $response['error'] = 'Некорректный URL';
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO links (title, url, description) VALUES (?, ?, ?)");
        $stmt->execute([$title, $url, $description]);
        $response['success'] = true;
    } catch (PDOException $e) {
        $response['error'] = 'Ошибка базы данных: ' . $e->getMessage();
    }
} else {
    $response['error'] = 'Недопустимый метод запроса';
}

echo json_encode($response);
?>
