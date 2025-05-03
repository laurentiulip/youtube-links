<?php
header('Content-Type: application/json');
include 'config.php';
$response=['success'=>false];
$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);
if($id){
  $stmt = $pdo->prepare("DELETE FROM pending_links WHERE id=?");
  $response['success'] = $stmt->execute([$id]);
}
echo json_encode($response);
