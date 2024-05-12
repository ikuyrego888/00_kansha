<?php
session_start();
include('funcs.php');
sschk();
$pdo = db_conn();

// ユーザーテーブル内のユーザーIDとパートナーID
$userId = $_SESSION["userId"];

$stmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id=:id ");
$stmt->bindValue(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch();
$partnerId = $userData["partnerId"];

// 紐付け堂録完了後に初めてアクセスしているかの判定
if ($partnerId) {
	$firstLinkStmt = $pdo->prepare("SELECT * FROM kansha_dialogCount WHERE userId=:userId AND firstLink IS NULL");
	$firstLinkStmt->bindValue(':userId', $userId, PDO::PARAM_INT);
	$firstLinkStmt->execute();
	$firstLink = $firstLinkStmt->fetch(PDO::FETCH_ASSOC);
}

// リクエスト申請中か否かの判定
$requestStmt = $pdo->prepare("SELECT * FROM kansha_partnerLink WHERE userId=:userId AND status='pending'");
$requestStmt->bindValue(':userId', $userId, PDO::PARAM_INT);
$requestStmt->execute();
$requestStatus = $requestStmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ユーザー登録</title>
  <link href="css/mypage.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>

<!-- ヘッダー設定 -->
<header>
	<!-- 機能の表示 -->
	<div id="headerTitle">マイページ画面</div>
	<!-- <div id="logoutMenu"><a href="logout.php" id="logout">ログアウト</a></div> -->
	<div id="logoutMenu">
		<a href="logout.php" id="logout"><img src="imgs/logout_icon.png" alt="" id="logoutIcon"></a>
	</div>
	<!-- アイコンの表示 -->
	<div id="iconBar">
		<img src="imgs/user_icon.png" alt="" id="userIcon">
	</div>
	<div id="iconLine"></div>
</header>

<div>
	<div id="mypageMenuContainer">
		<div class="mypageMenu"><a href="user_info.php">ユーザー情報</a></div>
		<div class="menuLine"></div>

		<div class="mypageMenu" id="partnerInfoMenu"><a href="partner_info.php">パートナー情報</a>
			<?php if(!$partnerId && !$requestStatus) { echo '<span id="clickHere">Click Here!</span>'; } ?>
			<?php if($requestStatus) { echo '<span id="requestStatus">リクエスト</br>申請中</span>'; } ?>
			<?php if($firstLink) { echo '<span id="new">new</span>'; } ?>
			<?php if($firstLink_) { echo '<span id="new">new</span>'; } ?>
		</div>
		<div class="menuLine"></div>

		<div class="mypageMenu"><a href="">感謝BOOKの記録</a></div>
		<div class="menuLine"></div>

		<div class="mypageMenu"><a href="">感謝ポイント</a></div>
		<div class="menuLine"></div>

		<div class="mypageMenu"><a href="">お問い合わせ先</a></div>
		<div class="menuLine"></div>
	</div>
</div>

<script>
	$(document).ready(function() {

		<?php if (!$partnerId && !$requestStatus) {?>
			setInterval(function() {
				$("#clickHere").fadeToggle(300);
			}, 600);
		<?php } ?>

		<?php if ($firstLink) {?>
			setInterval(function() {
				$("#new").fadeToggle(300);
			}, 600);
		<?php } ?>

	})
</script>

<footer>
	<div id="footerIconContainer">
		<a href="message.php"><img src="imgs/message_white.png" alt="" id="" class="footerIcon"></a>
		<a href="public.php"><img src="imgs/public_white.png" alt="" id="" class="footerIcon"></a>
		<a href="mypage.php"><img src="imgs/user_gray.png" alt="" id="" class="footerIcon"></a>
	</div>
</footer>

</body>
</html>