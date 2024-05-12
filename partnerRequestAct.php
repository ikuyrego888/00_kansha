<?php
session_start();
include('funcs.php');
sschk();

//1. POSTデータ取得
// ユーザーID（リクエストを送った側）
$userId = $_SESSION["userId"];
// パートナーのログインID
$partnerLid = $_POST["partnerLid"];

//2. DB接続
$pdo = db_conn();

// ユーザーデータを取得
$userStmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id=:id");
$userStmt->bindValue(':id', $userId, PDO::PARAM_INT);
$userStmt->execute();
$userData = $userStmt->fetch();

// SQL実行時にエラーがある場合STOP
if($userData["lid"] == $partnerLid) {
  echo "入力されたユーザーIDはあなたのIDです。パートナーのユーザーIDを入力してください。";
  $_SESSION["myLidError"] = 1;
  redirect("partner_info.php");
}

// パートナーのユーザー情報を抽出
$stmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE lid = :partnerLid"); 
$stmt->bindValue(':partnerLid', $partnerLid, PDO::PARAM_STR);
$status = $stmt->execute();

// SQL実行時にエラーがある場合STOP
if($status == false) {
    sql_error($stmt);
}

$partnerData = $stmt->fetch();

// パートナーのログインIDが存在しない場合STOP
if ($partnerData == false) {
  echo "入力されたログインIDを保有するユーザーは存在しません。";
  $_SESSION["requestError"] = 1;
  redirect("partner_info.php");
}

// パートナーのID（プライマリーキー）を取得
$partnerId = $partnerData["id"];

// データベースpartnerRequestテーブルへデータ登録
$requestData = $pdo->prepare("INSERT INTO kansha_partnerLink (userId, partnerId, status, indate ) VALUES (:userId, :partnerId, :status, sysdate())");
$requestData->bindValue(':userId', $userId, PDO::PARAM_INT);
$requestData->bindValue(':partnerId', $partnerId, PDO::PARAM_INT);
$requestData->bindValue(':status', "pending", PDO::PARAM_STR);
$requestStatus = $requestData->execute();

if($requestStatus == false) {
  //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
  sql_error($requestData);
} else {
  
  //５．express.phpへリダイレクト
  redirect("partner_info.php");

}