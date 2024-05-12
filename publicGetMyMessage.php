<?php
session_start();
include('funcs.php');
sschk();

//1. POSTデータ取得
$userId = $_SESSION["userId"];
// echo "テストだよ: ".$userId ." / " .$messageId ." / " .$iineFlg;

//2. DB接続します
$pdo = db_conn();

//３．データ登録SQL作成
$stmt = $pdo->prepare("SELECT * FROM kansha_publicMessage WHERE userId = :userId");
$stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
$status = $stmt->execute();

//4．データ登録処理後
if($status == true) {
  // 成功した場合の応答
  $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
  // レスポンス用の配列を作成
  $response = array(
      'success' => true,
      'messages' => $messages
  );
  echo json_encode($response);
  // echo json_encode(array("success" => true));
} else {
  // 失敗した場合の応答
  echo json_encode(array("success" => false));
}

?>