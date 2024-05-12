<?php
session_start();
include('funcs.php');
sschk();

//1. POSTデータ取得
$userId = $_SESSION["userId"];
$partnerId = $_SESSION["partnerId"];

//2. DB接続します
$pdo = db_conn();

//3. データ登録SQL作成
// openedTimeが空のメッセージに対して既読時間をセット
$stmt = $pdo->prepare("UPDATE kansha_message SET openedTime=sysdate() WHERE userId=:userId AND openedTime IS NULL");
$stmt->bindValue(':userId', $partnerId, PDO::PARAM_INT);
$status = $stmt->execute();

//4．データ登録処理後
if($status == false) {
  //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
  sql_error($stmt);
} else {

//5．message.phpへリダイレクト
    // リダイレクトではなくajax設定とする
    echo json_encode(array("success" => true));
    // $_SESSION["openedMessage"] = 1;
    // redirect("message.php");
}

?>