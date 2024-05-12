<?php
session_start();
include('funcs.php');
sschk();

//1. POSTデータ取得
$userId = $_SESSION["userId"];
$publishedMessage = $_POST["publishedMessage"];
// $publishedCategory = $_POST["publishedCategory"];
// デフォルト選択されているselecttタグのoptionはPOSTできないため"SESSION"で取得
$publishedCategory = $_SESSION["publicChk_category"];

$insertedId = $_SESSION["insertedId"];
unset($_SESSION["stopPublish"]);

//2. DB接続します
$pdo = db_conn();

// 3. userTabelのデータを取得
$stmtChk = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id = :id");
$stmtChk->bindValue(':id', $userId, PDO::PARAM_INT);
$stmtChk->execute();
$userData = $stmtChk->fetch();

// 誕生日・職業・結婚記念日を取得
$birthday = new DateTime($userData["birthday"]);
$occupation = $userData["occupation"];
$anniversary = new DateTime($userData["anniversary"]);

// 現在の日付
$currentDate = new DateTime(date("Y-m-d"));

// 年齢を算出
$age = $currentDate -> diff($birthday) -> y;
// echo $age."歳";

// 年代に変換
if ($age > 30 && $age < 70) {
  $ageGroup = floor($age / 10) * 10 ."代";
} else if ($age < 30) {
  $ageGroup = "10〜20代";
} else {
  $ageGroup = "70代〜";
}

// 結婚⚪︎年目を算出
$anniversaryYear = $currentDate -> diff($anniversary) -> y;

// 結婚年数グループに変換
if ($anniversaryYear < 1) {
  $anniversaryYearGroup = "1年未満";
} else if ($anniversaryYear >= 1 && $anniversaryYear < 5) {
    $anniversaryYearGroup = "1年〜5年未満";
} else if ($anniversaryYear >= 5 && $anniversaryYear < 10) {
  $anniversaryYearGroup = "5年〜10年未満";
} else if ($anniversaryYear >= 10 && $anniversaryYear < 20) {
  $anniversaryYearGroup = "10年〜20年未満";
} else if ($anniversaryYear >= 20 && $anniversaryYear < 30) {
  $anniversaryYearGroup = "20年〜30年未満";
} else {
  $anniversaryYearGroup = "30年〜";
}

//4．データ登録SQL作成
$stmt = $pdo->prepare("INSERT INTO kansha_publicMessage ( userId, message, category, age, ageGroup, occupation, anniversary, anniversaryGroup, indate ) VALUES( :userId, :message, :category, :age, :ageGroup, :occupation, :anniversary, :anniversaryGroup, sysdate())");
$stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
$stmt->bindValue(':message', $publishedMessage, PDO::PARAM_STR);
$stmt->bindValue(':category', $publishedCategory, PDO::PARAM_STR);
$stmt->bindValue(':age', $age, PDO::PARAM_INT);
$stmt->bindValue(':ageGroup', $ageGroup, PDO::PARAM_STR);
$stmt->bindValue(':occupation', $occupation, PDO::PARAM_STR);
$stmt->bindValue(':anniversary', $anniversaryYear, PDO::PARAM_INT);
$stmt->bindValue(':anniversaryGroup', $anniversaryYearGroup, PDO::PARAM_STR);
$status = $stmt->execute();

$publicStmt = $pdo->prepare("UPDATE kansha_message SET public=:public WHERE id=:id");
$publicStmt->bindValue(':id', $insertedId, PDO::PARAM_INT);
$publicStmt->bindValue(':public', "公開", PDO::PARAM_STR);
$publicStatus = $publicStmt->execute();

//5．データ登録処理後
if($status == false || $publicStatus == false) {
  //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
  sql_error($stmt);
  sql_error($publicStmt);
} else {

//6．index.phpへリダイレクト
    $_SESSION["publicCompleted"] = 1;
    redirect("message.php");
}

?>