<?php
session_start();
include('funcs.php');
sschk();

// 1. 公開しないを選んだメッセージのプライマリーキー
$insertedId = $_SESSION["insertedId"];
unset($_SESSION["stopPublish"]);

//2. DB接続します
$pdo = db_conn();

//3. データ登録SQL作成
$stmt = $pdo->prepare("UPDATE kansha_message SET public=:public WHERE id=:id");
$stmt->bindValue(':id', $insertedId, PDO::PARAM_INT);
$stmt->bindValue(':public', "非公開", PDO::PARAM_STR);

$status = $stmt->execute();

//4．データ登録処理後
if($status == false) {
  //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
  sql_error($stmt);
} else {
  
//5．mypage.phpへリダイレクト
  $_SESSION["stopPublish"] = 1;
  redirect("message.php");

}

?>