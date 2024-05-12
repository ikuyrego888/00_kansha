<?php
session_start();
include('funcs.php');
sschk();
$pdo = db_conn();

// 送信したメッセージを引用
$userId = $_SESSION["userId"];
$partnerId = $_SESSION["partnerId"];
// $loginChk = $_SESSION["loginChk"];

// 24時間前の日時を計算する
// $currentTime = new DateTime();
$currentTime = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
$twentyFourHoursAgo = $currentTime->sub(new DateInterval('PT24H'))->format('Y-m-d H:i:s');

// パートナーから受け取った＋既読後24h以内のメッセージのみをSELECT
$stmt = $pdo->prepare("SELECT * FROM kansha_message WHERE userId = :userId AND openedTime >= :twentyFourHoursAgo ORDER BY indate DESC");
$stmt->bindValue(':userId', $partnerId, PDO::PARAM_INT);
$stmt->bindValue(':twentyFourHoursAgo', $twentyFourHoursAgo, PDO::PARAM_STR);
$stmt->execute();

// 未読メッセージの数を数える用
$unopend = $pdo->prepare("SELECT COUNT(*) as count FROM kansha_message WHERE userId = :userId AND openedTime IS NULL");
$unopend->bindValue(':userId', $partnerId, PDO::PARAM_INT);
$unopend->execute();
$unopendCount = $unopend->fetch(PDO::FETCH_ASSOC);
// 未読メッセージの件数
$newMessageCount = $unopendCount["count"];

// ユーザーデータを取得
$stmtUser = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id = :id");
$stmtUser->bindValue(':id', $userId, PDO::PARAM_INT);
$stmtUser->execute();
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

// パートナーのユーザーデータを取得
$stmtPartner = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id = :id");
$stmtPartner->bindValue(':id', $partnerId, PDO::PARAM_INT);
$stmtPartner->execute();
$partnerData = $stmtPartner->fetch(PDO::FETCH_ASSOC);

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
  <title>パートナーからの感謝</title>
  <link href="css/message.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>

<!-- ヘッダー設定 -->
<header>
	<!-- 機能の表示 -->
	<div id="headerTitle">パートナーからの感謝</div>
	<!-- <div id="logoutMenu"><a href="logout.php" id="logout">ログアウト</a></div> -->
	<div id="logoutMenu">
		<a href="logout.php" id="logout"><img src="imgs/logout_icon.png" alt="" id="logoutIcon"></a>
	</div>
	<!-- アイコンの表示 -->
	<div id="iconBar">
		<img src="imgs/message_icon.png" alt="" id="messageIcon">
	</div>
	<div id="iconLine"></div>
	<button type="button" id="myMessageBtn" onclick="window.location.href = 'myMessage.php';">あなたの感謝</button>

</header>

<?php

$newMessage = ""; //未読メッセージありの場合に使用
$view = ""; //未読メッセージなしの場合に使用

// 未読メッセージが1件でもある場合
if ($newMessageCount > 0 ) {
	$newMessage .= '<div id="newMessageContainer"><div id="newMessageImage"><img src="imgs/newMessage.png" alt="" id="newMessageIcon">';
	$newMessage .= '<div id="countDisplay">' .$newMessageCount .'</div></div>'; //newMessageImageとcountDisplayの</div>
	$newMessage .= '<div id="partnerName">'.$partnerData["name"];
	$newMessage .= '<span id="sankara">さんから</span></div>';
	$newMessage .= '<div id="notice">' .$newMessageCount .'件の感謝が届いています</div>';
	$newMessage .= '<div id="noticeEnglish">A message of gratitude</br>has been received for you.</div>';
	$newMessage .= '<div id="iconLine"></div>';
	// $newMessage .= '<form method="POST" action="openedMessageAct.php" id="">';
	// $newMessage .= '<input type="submit" value="読む" id="openButton"></form>';
	// ajax対応とするため上2行をコメントアウト。以下2行を追加
	$newMessage .= '<button type="button" value="" id="openButton">読む</button>';
	$newMessage .= '<input type="hidden" value=""  name="openedMessage" id="openedMessage">';
	$newMessage .= '</div>';  //newMessageContainerの</div>

// 未読メッセージがない場合
} else {
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
	if (empty($view)) {
        // 1件も表示できるメッセージがない場合
        $view .= '<div id="noMessage">今表示できるメッセージはありません。</br>';
		$view .= '</br>パートナーへ日頃の感謝を</br>伝えましょう。</br></div>';
		$view .= '<div id="twentyFour">※パートナーからもらったメッセージは</br>読んでから24時間後に表示されなくなります。</br>';
		$view .= '（感謝BOOKが届くと過去のメッセージを確認できます）</div>';
    }
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

<!-- パートナー紐付け未登録用を知らせるモーダル -->
<div id="helloDialog">
	<p id="explanation_01">ようこそ！</P>
	<p id="explanation_01">夫婦感謝アプリは、</br>あなたとパートナーで日頃の感謝を伝えるアプリです。</br></p>
	<p id="explanation_02">普段、なかなか感謝を伝えられていない...</br>感謝を伝えるタイミングが難しい...</br>感謝の伝え方がわらかない...</P>
	<p id="explanation_01">そんな想いを抱えている人が</br>感謝をじょうずに伝えることを手助けするアプリです。</p>
	<p id="explanation_01">それでは、パートナーを紐づけ登録して、</br>感謝を伝えていきましょう。</p>
</div>

<!-- writeButtonをクリックした時にパートナー紐付け未登録用を知らせるモーダル -->
<div id="partnerNotRegisteredDialog">
	<p>パートナーの紐づけ登録をしましょう</p>
	<div id="dialogBtn">
		<button type="button" name="" id="confirmBtn">OK</button>
	</div>
</div>

<div id="writeButton">
	<div id="memoModal">メッセージを作成</div>
	<div>
		<a href="express.php" id="expressLink"><img src="imgs/write_button.png" alt="" id="writeBtnIcon"></a>
	</div>
</div>

<!-- モーダル用の背景/メッセージ送信時(背景白) -->
<div id="modalBackgroundWhite"></div>
<!-- 鳥のアイコン -->
<img src="imgs/send_icon.png" alt="" id="sendIcon">
<!-- 送信中のメッセージ（点滅させる） -->
<div id="nowSending">- sending message -</div>
<!-- メッセージ送信完了 -->
<div id="sendCompletely">メッセージを送信しました</br>
	<button type="button" id="sendCompletelyOK">OK</button>
</div>

<!-- モーダル用の背景/メッセージ受信時(背景白) -->
<div id="modalBackgroundWhite_"></div>
<!-- 手紙のアイコン -->
<img src="imgs/newMessage.png" alt="" id="unopenedIcon">
<img src="imgs/message_icon.png" alt="" id="openedIcon">

<!-- メッセージ公開完了 -->
<div id="publicCompletely">メッセージを公開しました</br>
	<button type="button" id="publicCompletelyOK">OK</button>
</div>

<!-- メッセージ公開中止 -->
<div id="stopPublish">メッセージの公開をやめました</br>
	<button type="button" id="stopPublishOK">OK</button>
</div>

<script>
	$(document).ready(function() {		
		// ログインした時にパートナー未登録だった場合に表示させるダイアログ
		<?php if (isset($_SESSION["loginChk"]) && $_SESSION["loginChk"] == 1 && !$userData["partnerId"] && !$requestStatus) {?>
			$('#modalBackground, #helloDialog').fadeIn();
			let flashInterval = setInterval(function() {
                $("#clickHere").fadeToggle(300);
            }, 600);

			$('#confirmBtn, #modalBackground').on("click", function() {
				$('#helloDialog').fadeOut();
				$('#modalBackground').fadeOut();
				<?php unset($_SESSION["loginChk"]);?>
			});
		<?php } ?>
		// writeButtonを押した時にパートナー未登録だった場合、express.phpからリダイレクトで戻ってきて以下処理が回る
		<?php if (isset($_SESSION["partnerNotRegistered"]) && $_SESSION["partnerNotRegistered"] == 1) {?>
			$('#modalBackground, #partnerNotRegisteredDialog').fadeIn();
			<?php unset($_SESSION["partnerNotRegistered"]);?>
			flashInterval = setInterval(function() {
                $("#clickHere").fadeToggle(300);
            }, 600);

			$('#confirmBtn, #modalBackground').on("click", function() {
				$('#partnerNotRegisteredDialog').fadeOut();
				$('#modalBackground').fadeOut();
			});
		<?php } ?>

		$("#writeButton").on("mouseover", function() {
			$("#memoModal").show();
		})
		$("#writeButton").on("mouseleave", function() {
			$("#memoModal").hide();
		})

		<?php if (isset($_SESSION["nowSending"]) && $_SESSION["nowSending"] == 1) {?>
			// 背景白のモーダル
			$('#modalBackgroundWhite_').show();

			// アイコン・送信中メッセージの表示
			$('#sendIcon').show();
			$('#nowSending').show();

			let windowWidth = $(window).width(); // ウィンドウの幅
			let imageWidth = $("#sendIcon").width(); // 画像の幅

			// 送信中のメッセージとともにトリが画面左側から右端まで移動する（アニメーション）
			// $("#sendIcon").animate({ left: windowWidth - imageWidth }, 3600);
			$("#sendIcon").fadeIn().animate({ left: windowWidth - imageWidth, opacity: 0 }, {
				duration: 3200,
				step: function(now, fx) {
					// アニメーションの途中で画像の透明度を変化
					$(this).css("opacity", now);
				},
				complete: function() {
					$(this).hide();
					$("#nowSending").hide();
					clearInterval(flashInterval_);
					$('#sendCompletely').show();
				}
			});

			// メッセージ送信完了の通知
			$("#sendCompletelyOK").on("click", function() {
				$('#sendCompletely').fadeOut();
				$('#modalBackgroundWhite_').fadeOut();
			})

			// 送信中アラートの表示
			let flashInterval_ = setInterval(function() {
				$("#nowSending").fadeToggle(300);
			}, 600);

		<?php } unset($_SESSION["nowSending"]);?>

		// 新着メッセージを開いた時のajax設定
		$("#openButton").on("click",function() {
			openedMessage();
		})

		function openedMessage() {
			$.ajax({
				type : "POST",
				url : "openedMessageAct.php",
				data : <?= json_encode($partnerId) ?>,
				dataType : "json",
				success : function(response) {
					// console.log("テスト："+response.success);
					if (response.success) {
						// alert("送信完了");
						$('#newMessageContainer').fadeOut();

						// 背景白のモーダル
						$('#modalBackgroundWhite_').show(-500);

						// // メッセージを開くイメージの表示
						$('#unopenedIcon').fadeIn(1000).delay(500).fadeOut(500);
						$('#openedIcon').delay(1500).fadeIn(1500).delay(500).fadeOut(500, function() {
							$('#modalBackgroundWhite_').fadeOut(500);
						});

						// 新着メッセージを再読み込みして表示する
						$.ajax({
							type: "GET",
							url: "loadMessageAct.php",
							success: function(data) {
								$('#messageField').html(data);
							}
						});
					} else {
						alert("送信失敗...");
					}
				}
			})
		}

		// 新着メッセージを開く場合
		<?php if (isset($_SESSION["openedMessage"]) && $_SESSION["openedMessage"] == 1) {?>

			// 背景白のモーダル
			$('#modalBackgroundWhite_').show(-500);

			// // メッセージを開くイメージの表示
			$('#unopenedIcon').fadeIn(1000).delay(500).fadeOut(500);
			$('#openedIcon').delay(1500).fadeIn(1500).delay(500).fadeOut(500, function() {
				$('#modalBackgroundWhite_').fadeOut(500);
			});
		
		<?php } unset($_SESSION["openedMessage"]);?>		

		// 公開完了した後の表示
		<?php if (isset($_SESSION["publicCompleted"]) && $_SESSION["publicCompleted"] == 1) {?>

			// 背景白のモーダル
			$('#modalBackgroundWhite').show();
			$('#publicCompletely').fadeIn(1500);

			// メッセージ公開完了の通知
			$("#publicCompletelyOK").on("click", function() {
				$('#publicCompletely').fadeOut();
				$('#modalBackgroundWhite').fadeOut();
			})

		<?php } unset($_SESSION["publicCompleted"]);?>	
		
		// 公開取りやめた後の表示
		<?php if (isset($_SESSION["stopPublish"]) && $_SESSION["stopPublish"] == 1) {?>

			// 背景白のモーダル
			$('#modalBackgroundWhite').show();
			$('#stopPublish').fadeIn(1500);

			// メッセージ公開中止の通知
			$("#stopPublishOK").on("click", function() {
				$('#stopPublish').fadeOut();
				$('#modalBackgroundWhite').fadeOut();
			})

		<?php } unset($_SESSION["stopPublish"]);?>

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