<?php
session_start();
include('funcs.php');
sschk();

//1. POSTデータ取得
$userId = $_SESSION["userId"];

$firstLink = $_POST["firstLink"];
$firstLink_ = $_POST["firstLink_"];
// echo "テストだよ".$firstLink;

//2. DB接続します
$pdo = db_conn();

$stmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id=:id ");
$stmt->bindValue(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch();

// 3. userTabelのデータを取得
if ($firstLink) {
  $stmtDialog = $pdo->prepare("UPDATE kansha_dialogCount SET firstLink=:firstLink WHERE userId = :userId");
  $stmtDialog->bindValue(':userId', $userId, PDO::PARAM_INT);
  $stmtDialog->bindValue(':firstLink', $firstLink, PDO::PARAM_INT);
  $status = $stmtDialog->execute();
}

//4．データ登録処理後
if($status == true) {
  // 成功した場合の応答
  echo json_encode(array("success" => true));
} else {
  // 失敗した場合の応答
  echo json_encode(array("success" => false));
}

?>