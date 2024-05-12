<?php
session_start();
include('funcs.php');
sschk();
$pdo = db_conn();

// 送信したメッセージを引用
$userId = $_SESSION["userId"];
$partnerId = $_SESSION["partnerId"];

// 自分が送ったメッセージをSELECT
$stmt = $pdo->prepare("SELECT * FROM kansha_message WHERE userId = :userId ORDER BY indate DESC");
$stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
$stmt->execute();

// ユーザーデータを取得
$stmtUser = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id = :id");
$stmtUser->bindValue(':id', $userId, PDO::PARAM_INT);
$stmtUser->execute();
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

// 自分の送信メッセージカウント用
$stmtCount = $pdo->prepare("SELECT YEAR(indate) AS year, MONTH(indate) AS month,
COUNT(*) AS count FROM kansha_message  WHERE userId = :userId GROUP BY YEAR(indate), MONTH(indate) ORDER BY indate DESC");
$stmtCount->bindValue(':userId', $userId, PDO::PARAM_INT);
$stmtCount->execute();

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>パートナーからの感謝</title>
  <link href="css/message.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>

<!-- ヘッダー設定 -->
<header>

	<!-- 機能の表示 -->
	<div id="headerTitle">あなたの感謝</div>
	<!-- <div id="logoutMenu"><a href="logout.php" id="logout">ログアウト</a></div> -->
	<div id="logoutMenu">
		<a href="logout.php" id="logout"><img src="imgs/logout_icon.png" alt="" id="logoutIcon"></a>
	</div>
	<div id="returnMenu"><a href="message.php" id="return">＜</a></div>
	<!-- アイコンの表示 -->
	<div id="iconBar">
		<img src="imgs/message_icon.png" alt="" id="messageIcon">
	</div>
	<div id="iconLine"></div>
	<button type="button" id="countChkBtn">感謝の記録</button>

</header>

<?php

$view = ""; //未読メッセージなしの場合に使用

while ($messages = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$indate = new DateTime($messages["indate"]);
	// 曜日を日本語で取得する
	$weekday = ['日', '月', '火', '水', '木', '金', '土'];
	$indateFormat = $indate -> format('Y/m/d(' .$weekday[$indate->format('w')] .') H:i');

	// $viewのセット
	$view .= '<div id="messageContainer"><div id="dateTime2">' .$indateFormat .' ';
	$view .= "あなた" ."</div>";
	if (!empty($messages["img"]) && $messages["img"] != 1 && $messages["img"] != 2) {
		$view .= '<div id="imageDisplay">';
		$view .= '<img src="upload/';
		$view .= $messages["img"];
		$view .= '" id="image"></div>';	
	};
	$view .= '<div id="messageCard"><div id="message">' .nl2br(h($messages["message"])) .'</div>';
	$view .= '<div id="category">' .h($messages["category"]) .'</div></div></div>';
}

if (empty($view)) {
	// 1件も表示できるメッセージがない場合
	$view .= '<div id="noMessage">あなたが送ったメッセージはありません。</br>';
	$view .= '</br>パートナーへ日頃の感謝を</br>伝えましょう。</br></div>';
}

?>

<div id="messageField">
	<?php
		echo $newMessage;
		echo $view;
	?>
</div>

<!-- モーダル用の背景 -->
<div id="modalBackground"></div>

<!-- writeButtonをクリックした時にパートナー紐付け未登録用を知らせるモーダル -->
<div id="partnerNotRegisteredDialog">
	<p>パートナーの紐づけ登録をしましょう</p>
	<div id="dialogBtn">
		<button type="button" name="" id="confirmBtn">OK</button>
	</div>
</div>

<!-- パートナー未登録だった時に"1"をPOST通信する -->
<form method="POST" action="express.php" id="noPartnerChk">
	<input type="hidden" name="noPartner" id="noPartner" value="1">

	<div id="writeButton">
		<div id="memoModal">メッセージを作成</div>
		<div>
			<a href="express.php" id="expressLink"><img src="imgs/write_button.png" alt="" id="writeBtnIcon"></a>
		</div>
	</div>
</form>

<!-- 感謝カウントの確認ボタンを押した時の設定 -->
<div id="modalBackground_"></div>

<?php

// 送信メッセージ数の確認
$table = '<div id="countTableContainer"><table id="countTable">';
$table .= '<tr><th>年 月</th><th>感謝の回数</th></tr>';
while ($count = $stmtCount->fetch(PDO::FETCH_ASSOC)) {
	$year = $count['year'];
    $month = $count['month'];
	if ($month < 10 ) {
		$month = "0".$month;
	}
    $messageCount = $count['count'];
	$table .= '<tr><td>' .$year .'/' .$month .'</td>' .'<td>' .$messageCount .'回' .'</td></tr>';
}
$table .= '</table></div>';

echo $table;

?>

<script>
	$(document).ready(function() {

		// 感謝の記録ボタンを押した時の設定
		$("#countChkBtn").on("click", function() {
			$('#modalBackground_, #countTableContainer').fadeIn();
		})

		$('#modalBackground_').on("click", function() {
			// モーダル背景とダイアログを非表示
			$('#modalBackground_, #countTableContainer').fadeOut();
		});

		// writeButtonを押した時にパートナー未登録だった場合、expres.phpからリダイレクトで戻ってきて以下処理が回る
		<?php if (isset($_SESSION["partnerNotRegistered"]) && $_SESSION["partnerNotRegistered"] == 1) {?>
			$('#modalBackground, #partnerNotRegisteredDialog').fadeIn();
			<?php unset($_SESSION["partnerNotRegistered"]);?>
			flashInterval = setInterval(function() {
                $("#clickHere").fadeToggle(300);
            }, 600);

			$('#confirmBtn, #modalBackground').on("click", function() {
				$('#partnerNotRegisteredDialog').fadeOut();
				$('#modalBackground').fadeOut();
				// clearInterval(flashInterval);
				// $("#clickHere").hide();
			});
		<?php } ?>

		$("#writeButton").on("mouseover", function() {
			$("#memoModal").show();
		})
		$("#writeButton").on("mouseleave", function() {
			$("#memoModal").hide();
		})

		// パートナー未登録の場合に"1"をPOST通信する
		$("#expressLink").on("click", function(e) {
			e.preventDefault();
			$("#noPartnerChk").submit();
		})
	})

</script>

</body>

<footer>
	<div id="footerIconContainer">
		<a href="message.php"><img src="imgs/message_gray.png" alt="" id="" class="footerIcon"></a>
		<a href="public.php"><img src="imgs/public_white.png" alt="" id="" class="footerIcon"></a>
		<a href="mypage.php"><img src="imgs/user_white.png" alt="" id="mypageIcon" class="footerIcon">
			<!-- <span id="clickHere">←ココをクリック</span> -->
			<span id="clickHere"><span id="arrow">←</span>Click Here!</span>
		</a>
</footer>

</html>