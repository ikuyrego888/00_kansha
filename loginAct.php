<?php
session_start();

//POST値
$lid = $_POST["lid"];
$lpw = $_POST["lpw"];

//1. DB接続します
include("funcs.php");
$pdo = db_conn();

//2. データ登録SQL作成
$stmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE lid = :lid"); 
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR);
$status = $stmt->execute();

//3. SQL実行時にエラーがある場合STOP
if($status==false){
    sql_error($stmt);
}

//4. 抽出データ数を取得
$val = $stmt->fetch();

//5. 該当１レコードがあればSESSIONに値を代入
//入力したPasswordと暗号化されたPasswordを比較！[戻り値：true,false]
$pw = password_verify($lpw, $val["lpw"]);
if($pw) { 
  //Login成功時
  $_SESSION["chk_ssid"]  = session_id();
  $_SESSION["userId"] = $val["id"];
  $_SESSION["partnerId"] = $val["partnerId"];
  $_SESSION["loginChk"] = 1;

  //Login成功時（リダイレクト）
  redirect("message.php");

} else {
  //Login失敗時（Logoutを経由：リダイレクト）
  $_SESSION["loginError"] = 1;
  redirect("login.php");
  
}

exit();