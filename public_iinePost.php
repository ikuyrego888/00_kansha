<?php
session_start();
include('funcs.php');
sschk();

//1. POSTデータ取得
$userId = $_SESSION["userId"];
$messageId = $_POST["messageId"];
$iineFlg = $_POST["iineFlg"];
// echo "テストだよ: ".$userId ." / " .$messageId ." / " .$iineFlg;

//2. DB接続します
$pdo = db_conn();

//３．データ登録SQL作成
// いいねを押した時はINSERTでデータ組成
if($iineFlg == 1) {
  $stmt = $pdo->prepare("INSERT INTO kansha_iineTable ( messageId, userId, iineFlg, indate ) VALUES( :messageId, :userId, :iineFlg, sysdate())");
  $stmt->bindValue(':messageId', $messageId, PDO::PARAM_INT);
  $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
  $stmt->bindValue(':iineFlg', $iineFlg, PDO::PARAM_INT);
  $status = $stmt->execute();

// いいねを解除した時はDELETEでデータ削除
} if($iineFlg == 0) {
  $deleteStmt = $pdo->prepare("DELETE FROM kansha_iineTable WHERE messageId=:messageId AND userId=:userId");
  $deleteStmt->bindValue(':messageId', $messageId, PDO::PARAM_INT);
  $deleteStmt->bindValue(':userId', $userId, PDO::PARAM_INT);
  $status = $deleteStmt->execute();
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