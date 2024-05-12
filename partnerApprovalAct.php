<?php
session_start();
include("funcs.php");
sschk();

// ユーザーID（リクエストを送った側）
$userId = $_SESSION["userId"];

// リクエスト申請番号（id）
$requestId = $_SESSION["requestId"];
// リクエスト申請者側のID（≠承認者）※今ログインしているユーザーではない方
$requestUserId = $_SESSION["requestUserId"];
// リクエスト受信者側のID（=承認者）※今ログインしているユーザー
$requestPartnerId = $_SESSION["requestPartnerId"];

// 承認・拒否のPOST通信
$requestStatus = $_POST["requestStatus"];

// DB接続
$pdo = db_conn();

// パートナーLinkのデータベースを更新
$link = $pdo->prepare("UPDATE kansha_partnerLink SET status=:status, indate=sysdate() WHERE id=:id");
$link->bindValue(':id', $requestId, PDO::PARAM_INT);
$link->bindValue(':status', $requestStatus, PDO::PARAM_STR);
$linkStatus = $link->execute();

// SQL実行時にエラーがある場合STOP
if($linkStatus == false){
    sql_error($link);

  // 承認された場合、ユーザーテーブルのpartnerIdに紐付け（承認者側）
} elseif ($requestStatus == "approve" ) {
  $get = $pdo->prepare("UPDATE kansha_userTable SET partnerId=:partnerId, indate=sysdate() WHERE id=:id");
  $get->bindValue(':id', $userId, PDO::PARAM_INT);
  $get->bindValue(':partnerId', $requestUserId, PDO::PARAM_INT);
  $getStatus = $get->execute();

  // 承認された場合、dialogCountテーブルのpartnerIdに紐付け（承認者側）
  $getDialog = $pdo->prepare("UPDATE kansha_dialogCount SET partnerId=:partnerId, indate=sysdate() WHERE userId=:userId");
  $getDialog->bindValue(':userId', $userId, PDO::PARAM_INT);
  $getDialog->bindValue(':partnerId', $requestUserId, PDO::PARAM_INT);
  $getDialogStatus = $getDialog->execute();

  // SQL実行時にエラーがある場合STOP
  if($getStatus == false || $getDialogStatus == false ) {
    sql_error($get);
  }
 
  $stmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id=:id"); 
  $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
  $status = $stmt->execute();

  if($status==false){
    sql_error($stmt);
  }

  $val = $stmt->fetch();
  $_SESSION["partnerId"] = $val["partnerId"];

  // 承認された場合、ユーザーテーブルのpartnerIdに紐付け（申請者側）
  $send = $pdo->prepare("UPDATE kansha_userTable SET partnerId=:partnerId, indate=sysdate() WHERE id=:id");
  $send->bindValue(':id', $requestUserId, PDO::PARAM_INT);
  $send->bindValue(':partnerId', $requestPartnerId, PDO::PARAM_INT);
  $sendStatus = $send->execute();

  // 承認された場合、dialogCountテーブルのpartnerIdに紐付け（申請者側）
  $sendDialog = $pdo->prepare("UPDATE kansha_dialogCount SET partnerId=:partnerId, indate=sysdate() WHERE userId=:userId");
  $sendDialog->bindValue(':userId', $requestUserId, PDO::PARAM_INT);
  $sendDialog->bindValue(':partnerId', $requestPartnerId, PDO::PARAM_INT);
  $sendDialogStatus = $sendDialog->execute();

  if($sendStatus == false || $sendDialogStatus == false ) {
    sql_error($send);
  }

  redirect("mypage.php");

} else {
  redirect("mypage.php");
}