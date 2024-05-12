<?php
session_start();
include('funcs.php');
sschk();

//1. POSTデータ取得
$userId = $_SESSION["userId"];
$partnerId = $_SESSION["partnerId"];

//2. DB接続します
$pdo = db_conn();

// 24時間前の日時を計算する
// $currentTime = new DateTime();
$currentTime = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
$twentyFourHoursAgo = $currentTime->sub(new DateInterval('PT24H'))->format('Y-m-d H:i:s');

// パートナーから受け取った＋既読後24h以内のメッセージのみをSELECT
$stmt = $pdo->prepare("SELECT * FROM kansha_message WHERE userId = :userId AND openedTime >= :twentyFourHoursAgo ORDER BY indate DESC");
$stmt->bindValue(':userId', $partnerId, PDO::PARAM_INT);
$stmt->bindValue(':twentyFourHoursAgo', $twentyFourHoursAgo, PDO::PARAM_STR);
$stmt->execute();

$view ="";

while ($messages = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $indate = new DateTime($messages["indate"]);
  // 曜日を日本語で取得する
  $weekday = ['日', '月', '火', '水', '木', '金', '土'];
  $indateFormat = $indate -> format('Y/m/d(' .$weekday[$indate->format('w')] .') H:i');

  // $viewのセット
  $view .= '<div id="messageContainer"><div id="dateTime">' .$indateFormat .' ';
  $view .= $partnerData["name"] ."</div>";
  if (!empty($messages["img"]) && $messages["img"] != 1 && $messages["img"] != 2) {
    $view .= '<div id="imageDisplay">';
    $view .= '<img src="upload/';
    $view .= $messages["img"];
    $view .= '" id="image"></div>';	
  };
  $view .= '<div id="messageCard"><div id="message">' .nl2br(h($messages["message"])) .'</div>';
  $view .= '<div id="category">' .h($messages["category"]) .'</div></div></div>';
}

echo $view;

?>