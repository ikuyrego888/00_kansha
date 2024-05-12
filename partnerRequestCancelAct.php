<?php
session_start();
include('funcs.php');
sschk();

//1. POSTデータ取得
// ユーザーID（リクエストを送った側）
$userId = $_SESSION["userId"];

//2. DB接続
$pdo = db_conn();

//3. 取り消されたリクエスト申請データを削除する
$stmt = $pdo->prepare("DELETE FROM kansha_partnerLink WHERE userId=:userId AND status='pending'"); 
$stmt->bindValue(':userId', $userId, PDO::PARAM_STR);
$status = $stmt->execute();

//4．データ登録処理後
if($status == false){
    sql_error($stmt);
} else {
  
  //５．partner_info.phpへリダイレクト
  redirect("partner_info.php");

}