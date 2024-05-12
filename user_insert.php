<?php
session_start();

//1. POSTデータ取得
$name = $_POST["name"];
$occupation = $_POST["occupation"];
$sex = $_POST["sex"];
$newLid = $_POST["newLid"];
$newLpw = $_POST["newLpw"];

// パスワードをハッシュ化する
$newLpwHash = password_hash($newLpw, PASSWORD_DEFAULT);

// 生年月日の結合
$year = $_POST["year"];
$month = str_pad($_POST["month"], 2, "0", STR_PAD_LEFT);
$day = str_pad($_POST["day"], 2, "0", STR_PAD_LEFT);
$birthday = $year ."-" .$month . "-" .$day;

// 結婚記念日の結合
$anniversaryYear = $_POST["anniversaryYear"];
$anniversaryMonth = str_pad($_POST["anniversaryMonth"], 2, "0", STR_PAD_LEFT);
$anniversaryDay = str_pad($_POST["anniversaryDay"], 2, "0", STR_PAD_LEFT);
$anniversary = $anniversaryYear ."-" .$anniversaryMonth . "-" .$anniversaryDay;

//2. DB接続します
include("funcs.php");
$pdo = db_conn();

//3. 同じIDは登録不可とする
$lidChkStmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE lid = :lidChk"); 
$lidChkStmt->bindValue(':lidChk', $newLid, PDO::PARAM_STR);
$satus = $lidChkStmt->execute();
$lidChk = $lidChkStmt->fetch();

if ($lidChk) {
  $_SESSION["lidError"] = 1;
  redirect("user_register.php");
}

//4．データ登録SQL作成
$stmt = $pdo->prepare("INSERT INTO kansha_userTable ( name, lid, lpw, birthday, occupation, sex, anniversary, admFlg, lifeFlg, indate ) VALUES( :name, :lid, :lpw, :birthday, :occupation, :sex, :anniversary, :admFlg, :lifeFlg, sysdate())");
$stmt->bindValue(':name', $name, PDO::PARAM_STR);
$stmt->bindValue(':lid', $newLid, PDO::PARAM_STR);
$stmt->bindValue(':lpw', $newLpwHash, PDO::PARAM_STR);
$stmt->bindValue(':birthday', $birthday, PDO::PARAM_STR);
$stmt->bindValue(':occupation', $occupation, PDO::PARAM_STR);
$stmt->bindValue(':sex', $sex, PDO::PARAM_STR);
$stmt->bindValue(':anniversary', $anniversary, PDO::PARAM_STR);
$stmt->bindValue(':admFlg', 0, PDO::PARAM_INT);
$stmt->bindValue(':lifeFlg', 0, PDO::PARAM_INT);
$status = $stmt->execute();

// INSERTで登録した最新プライマリーキーを取得
$insertedId = $pdo->lastInsertId();

// ダイアログカウント用テーブル
$dialogStmt = $pdo->prepare("INSERT INTO kansha_dialogCount ( userId, indate ) VALUES( :userId, sysdate())");
$dialogStmt->bindValue(':userId', $insertedId, PDO::PARAM_INT);
$dialogStatus = $dialogStmt->execute();

//5．データ登録処理後
if($status == false || $dialogStatus  == false ) {
  //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
  sql_error($stmt);
} else {
  
  //6．login.phpへリダイレクト
  $_SESSION["insertSuccess"] = 1;
  redirect("login.php");

}

?>