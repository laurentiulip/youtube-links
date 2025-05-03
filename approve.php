<?php
header('Content-Type: application/json');
include 'config.php';
$response=['success'=>false];

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);
if($id){
  $pdo->beginTransaction();
  // preia datele
  $stmt = $pdo->prepare("SELECT title,url,description FROM pending_links WHERE id=?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  if($row){
    // inserează în links
    $ins = $pdo->prepare("INSERT INTO links (title,url,description) VALUES (?,?,?)");
    $ins->execute([$row['title'],$row['url'],$row['description']]);
    // șterge din pending
    $del = $pdo->prepare("DELETE FROM pending_links WHERE id=?");
    $del->execute([$id]);
    $pdo->commit();
    $response['success']=true;
  } else {
    $pdo->rollBack();
    $response['error']='Nu există acel ID';
  }
}
echo json_encode($response);
