<?php
session_start();
include('funcs.php');
sschk();

$userId = $_SESSION["userId"];

//1. POSTデータ取得
// 必須登録項目
$name = $_POST["name"];
$occupation = $_POST["occupation"];
$sex = $_POST["sex"];
$lid = $_POST["lid"];

// パスワード変更
$whetherChange = $_POST["whetherChange"];
$currentLpw = $_POST["currentLpw"];
$newLpw = $_POST["newLpw"];

// 任意登録項目
$lastName = $_POST["lastName"];
$firstName = $_POST["firstName"];
$kanaLastName = $_POST["kanaLastName"];
$kanaFirstName = $_POST["kanaFirstName"];
$postCode_1 = $_POST["postCode_1"];
$postCode_2 = $_POST["postCode_2"];
$postCode = $postCode_1 .$postCode_2;
$prefecture = $_POST["prefecture"];
$city = $_POST["city"];
$houseNumber = $_POST["houseNumber"];
$apartment = $_POST["apartment"];
$telephone = $_POST["telephone"];

// 英数記号を半角にする関数
function convertHarfWidth($str) {
  $str = str_replace('ー', '-', $str);
  $str = mb_convert_kana($str, 'as');
  return $str;
}

$postCode = convertHarfWidth($postCode);
$houseNumber = convertHarfWidth($houseNumber);
$apartment = convertHarfWidth($apartment);
$telephone = convertHarfWidth($telephone);

//2. DB接続します
$pdo = db_conn();

$lpwStmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id = :id");
$lpwStmt->bindValue(':id', $userId, PDO::PARAM_INT);
$lpwStmt->execute();
$lpwChk = $lpwStmt->fetch();

// パスワード変更有無の判定
if($whetherChange) {
  // 現在のパスワードが入力したパスワードと一致しているかを確認
  $pw = password_verify($currentLpw, $lpwChk["lpw"]);
  if($pw) {
    // 新しいパスワードのハッシュ化
    $newLpwHash = password_hash($newLpw, PASSWORD_DEFAULT);
  } else {
    // リダイレクト先のアラート表示用セッション
    $_SESSION["passwordError"] = 1;
    redirect("user_infoUpdate.php");
  }
}

$updateStatus = ($whetherChange) ? ", lpw=:lpw" : "";

//3. データ登録SQL作成
$stmt = $pdo->prepare("UPDATE kansha_userTable SET name=:name, lid=:lid{$updateStatus}, occupation=:occupation, sex=:sex, lastName=:lastName, firstName=:firstName, kanaLastName=:kanaLastName, kanaFirstName=:kanaFirstName, postCode=:postCode, prefecture=:prefecture, city=:city, houseNumber=:houseNumber, apartment=:apartment, telephone=:telephone, indate=sysdate() WHERE id=:id");
$stmt->bindValue(':id', $userId, PDO::PARAM_INT);
$stmt->bindValue(':name', $name, PDO::PARAM_STR);
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR);
if ($whetherChange) {
  $stmt->bindValue(':lpw', $newLpwHash, PDO::PARAM_STR);
}
$stmt->bindValue(':occupation', $occupation, PDO::PARAM_STR);
$stmt->bindValue(':sex', $sex, PDO::PARAM_STR);
$stmt->bindValue(':lastName', $lastName, PDO::PARAM_STR);
$stmt->bindValue(':firstName', $firstName, PDO::PARAM_STR);
$stmt->bindValue(':kanaLastName', $kanaLastName, PDO::PARAM_STR);
$stmt->bindValue(':kanaFirstName', $kanaFirstName, PDO::PARAM_STR);
$stmt->bindValue(':postCode', $postCode, PDO::PARAM_STR);
$stmt->bindValue(':prefecture', $prefecture, PDO::PARAM_STR);
$stmt->bindValue(':city', $city, PDO::PARAM_STR);
$stmt->bindValue(':houseNumber', $houseNumber, PDO::PARAM_STR);
$stmt->bindValue(':apartment', $apartment, PDO::PARAM_STR);
$stmt->bindValue(':telephone', $telephone, PDO::PARAM_STR);

$status = $stmt->execute();

//4．データ登録処理後
if($status == false) {
  //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
  sql_error($stmt);
} else {
  
//5．user_info.phpへリダイレクト
  redirect("user_info.php");

}

?>