<?php
include 'config.php';

$title = $_POST['title'] ?? '';
$url = $_POST['url'] ?? '';
$description = $_POST['description'] ?? '';
$source = $_POST['source'] ?? ''; // nou: pentru a distinge sursa

if (!$title || !$url) {
  echo json_encode(['success' => false, 'error' => 'Lipsesc datele']);
  exit;
}

// Dacă vine din admin.php, adaugă direct în links
if ($source === 'admin') {
  $stmt = $pdo->prepare("INSERT INTO links (title, url, description, created_at) VALUES (?, ?, ?, NOW())");
  if ($stmt->execute([$title, $url, $description])) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'error' => 'Eroare la inserare în links']);
  }
} else {
  // altfel, trimite la moderare
  $stmt = $pdo->prepare("INSERT INTO pending_links (title, url, description, created_at) VALUES (?, ?, ?, NOW())");
  if ($stmt->execute([$title, $url, $description])) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'error' => 'Eroare la inserare în pending']);
  }
}
