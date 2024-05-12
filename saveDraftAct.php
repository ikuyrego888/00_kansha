<?php
session_start();
include('funcs.php');
sschk();

//1. POSTデータ取得
$userId = $_SESSION["userId"];
$message = $_POST["message"];
$category = $_POST["category"];
$img = fileUpload("upfile", "upload");
$deleteImage = $_POST["deleteImage"];
// echo "テスト".$deleteImage;

//2. DB接続します
$pdo = db_conn();

//3. 同一ユーザーで既に下書きがあるかを確認する
$stmtChk = $pdo->prepare("SELECT * FROM kansha_saveDraft WHERE userId = :userId");
$stmtChk->bindValue(':userId', $userId, PDO::PARAM_INT);
$stmtChk->execute();
$draft = $stmtChk->fetch();

//4. データ登録SQL作成

// 下書きがない時は新規で保存する
if(!$draft) {
  $stmt = $pdo->prepare("INSERT INTO kansha_saveDraft ( userId, message, category, img, indate ) VALUES( :userId, :message, :category, :img, sysdate())");
  $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
  $stmt->bindValue(':message', $message, PDO::PARAM_STR);
  $stmt->bindValue(':category', $category, PDO::PARAM_STR);
  $stmt->bindValue(':img', $img, PDO::PARAM_STR);
  $status = $stmt->execute();

  //データ登録処理後
  if($status == false) {
    //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    sql_error($stmt);
  } else {
    
  //express.phpへリダイレクト
  redirect("express.php");

  }

  // 下書きがあるときは上書き保存する
} else if ($draft) {

  // 古いimgファイル名を取得する
  $oldImg = $draft["img"];

  // ファイルが空じゃなくエラーでもない場合（新たなファイルが送られている場合）
  if ($img !== null && $img != 1 && $img != 2) {
    // 古いimgファイルがある場合は、それを"uploadフォルダ"から削除する
    // file_exists関数でフォルダ内のファイル有無を確認して、unlink関数でファイルを削除する
    if(!empty($oldImg) && $oldImg != 1 && $oldImg != 2) {
      $oldFilePath = "upload/" . $oldImg;
      if(file_exists($oldFilePath)) {
        unlink($oldFilePath);
      }
    }
  }
  // $deleteImage = "1"とは画像を削除している状態（なので、以下は削除していない場合）
  else if ($deleteImage != 1) {
    // ファイルが選択されていない場合は、下書きのファイルを使う
    $img = $oldImg;

  // $deleteImage = "1"とは画像を削除している状態
  } else {
    // 削除されているので"uploadフォルダ"から削除する
    if(!empty($oldImg) && $oldImg != 1 && $oldImg != 2) {
      $oldFilePath = "upload/" . $oldImg;
      if(file_exists($oldFilePath)) {
        unlink($oldFilePath);
      }
    }
    // 削除されている場合は$img="3"とする
    $img = 3;
  }

  // ファイルが送られているかどうかを判定
  $updateStatus = ($img !== null && $img != 1 && $img != 2 ) ? ", img=:img" : "";
  
  $stmt = $pdo->prepare("UPDATE kansha_saveDraft SET message=:message, category=:category{$updateStatus}, indate=sysdate() WHERE userId=:userId");
  // $stmt = $pdo->prepare("UPDATE kansha_saveDraft SET message=:message, category=:category, img=:img, indate=sysdate() WHERE userId=:userId");
  $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
  $stmt->bindValue(':message', $message, PDO::PARAM_STR);
  $stmt->bindValue(':category', $category, PDO::PARAM_STR);
  if ($img !== null && $img != 1 && $img != 2 ) {
    $stmt->bindValue(':img', $img, PDO::PARAM_STR);
  }
  $status = $stmt->execute();

  // 画像が削除されている場合はデータベースも"null"にする
  if($img == 3) {
    $stmtDeleteImg = $pdo->prepare("UPDATE kansha_saveDraft SET img=:img WHERE userId = :userId");
    $stmtDeleteImg->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmtDeleteImg->bindValue(':img', null, PDO::PARAM_STR);
    $stmtDeleteImg->execute();
    // try {
    //   $stmtDeleteImg->execute();
    // } catch (Exception $e) {
    //     echo '捕捉した例外: ',  $e->getMessage(), "\n";
    //     var_dump($stmtDeleteImg->errorInfo());
    //     exit;
    // }
  }
  
  //データ登録処理後
  if($status == false) {
    echo "テスト";
    //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    sql_error($stmt);
  } else {
    
  //express.phpへリダイレクト
  redirect("express.php");

  }
}
?>