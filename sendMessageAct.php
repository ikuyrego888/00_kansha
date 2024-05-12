<?php
session_start();
include('funcs.php');
sschk();

//1. POSTデータ取得
$userId = $_SESSION["userId"];
$message = $_POST["message"];
$category = $_POST["category"];
// ここでファイルデータを生成せず、以下のIF文の中で生成
// $img = fileUpload("upfile", "upload");
$deleteImage = $_POST["deleteImage"];
$publicChk = $_POST["publicChk"];

//2. DB接続します
$pdo = db_conn();

//3. 下書きがある場合に情報取得
$stmtChk = $pdo->prepare("SELECT * FROM kansha_saveDraft WHERE userId = :userId");
$stmtChk->bindValue(':userId', $userId, PDO::PARAM_INT);
$stmtChk->execute();
$draft = $stmtChk->fetch();

//4. ファイルがアップロードされた時
// UPLOAD_ERR_OKはエラーコードがないことを示している
if (isset($_FILES['upfile']) && $_FILES['upfile']['error'] === UPLOAD_ERR_OK) {
  // ファイルが選択された場合、新しいファイルをアップロード
  $img = fileUpload("upfile", "upload");

  // 下書きがある場合は、古いファイルを削除
  if ($draft && !empty($draft["img"]) && $draft["img"] != 1 && $draft["img"] != 2) {
      $oldFilePath = "upload/" . $draft["img"];
      if (file_exists($oldFilePath)) {
          unlink($oldFilePath);
      }
  }

  // $deleteImage = "1"とは画像を削除している状態（なので、以下は削除していない場合）
} else if ($deleteImage != 1) {
  // ファイルが選択されていない場合、下書きのファイルを使う
  $img = $draft["img"];

  // $deleteImage = "1"とは画像を削除している状態
} else {
  if ($draft && !empty($draft["img"]) && $draft["img"] != 1 && $draft["img"] != 2) {
    $oldFilePath = "upload/" . $draft["img"];
    if (file_exists($oldFilePath)) {
      unlink($oldFilePath);
    }
  }
  // 削除されている場合は$img="null"とする
  $img = null;
}

//5．データ登録SQL作成
$stmt = $pdo->prepare("INSERT INTO kansha_message ( userId, message, category, img, public, indate ) VALUES( :userId, :message, :category, :img, :public, sysdate())");
$stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
$stmt->bindValue(':message', $message, PDO::PARAM_STR);
$stmt->bindValue(':category', $category, PDO::PARAM_STR);
$stmt->bindValue(':img', $img, PDO::PARAM_STR);
$stmt->bindValue(':public', $publicChk, PDO::PARAM_STR);
$status = $stmt->execute();

// INSERTで登録した最新プライマリーキーを取得
$insertedId = $pdo->lastInsertId();

//6．下書きデータがある場合はデータを削除する
if ($draft) {
  $stmtDeleteDraft = $pdo->prepare("DELETE FROM kansha_saveDraft WHERE userId = :userId");
  $stmtDeleteDraft->bindValue(':userId', $userId, PDO::PARAM_INT);
  $stmtDeleteDraft->execute();
}

//7．データ登録処理後
if($status == false) {
  //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
  sql_error($stmt);
} else {

  if ($publicChk == "非公開") {
//8．index.phpへリダイレクト
    $_SESSION["nowSending"] = 1;
    redirect("message.php");
  }
  else if ($publicChk == "公開") {
    $_SESSION["insertedId"] = $insertedId;
    $_SESSION["publicChk_message"] = $message;
    $_SESSION["publicChk_category"] = $category;
    $_SESSION["nowSending"] = 1;
    $_SESSION["stopPublish"] = 1;
    redirect("publicChk.php");
  }
}

?>