<?php
session_start();
include('funcs.php');
sschk();
$pdo = db_conn();

// 送信したメッセージを引用
$userId = $_SESSION["userId"];
$publicChk_message = $_SESSION["publicChk_message"];
$publicChk_category = $_SESSION["publicChk_category"];

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>感謝を公開する</title>
  <link href="css/index.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>

<!-- ヘッダー設定 -->
<header>
	<!-- 機能の表示 -->
	<div id="headerTitle">公開するメッセージの確認</div>
	<!-- <div id="logoutMenu"><a href="logout.php" id="logout">ログアウト</a></div> -->
	<div id="logoutMenu">
		<a href="logout.php" id="logout"><img src="imgs/logout_icon.png" alt="" id="logoutIcon"></a>
	</div>
	<!-- アイコンの表示 -->
	<div id="iconBar">
		<img src="imgs/public_icon.png" alt="" id="publicIcon">
	</div>
	<div id="minnanoKansha">みんなの感謝</div>
	<div id="iconLine"></div>
</header>

<div id="form">
	<form method="POST" action="publicAct.php" id="publicChkForm">
		<div id="explanation">
			<span style="font-weight: bold;">みんなの感謝とは…</span>
			</br>世の中の"感謝のメッセージ"を集めることで、ユーザーが、感謝の伝え方に関するヒント（例：ナイスな伝え方、感謝する事柄など）をもらい、パートナーへ感謝を上手に伝えることを目指しています。
		</div>
		<!-- メッセージ -->
		<div class="formItem">公開するメッセージの確認・編集</div>
		<div id="shift">
			<textarea name="publishedMessage" id="publishedMessage" placeholder="公開するメッセージを編集しよう" required><?=$publicChk_message?></textarea>
		</div>

		<!-- 感謝のカテゴリ -->
		<div class="formItem">感謝のカテゴリ</div>
		<div id="shift">
			<select name="publishedCategory" id="publishedCategory" required disabled>
				<option value="" hidden>選んでください</option>
				<option value="家事" <?php echo ($publicChk_category == "家事") ? "selected" : ""; ?> disabled>家事</option>
				<option value="育児" <?php echo ($publicChk_category == "育児") ? "selected" : ""; ?> disabled>育児</option>
				<option value="イベント" <?php echo ($publicChk_category == "イベント") ? "selected" : ""; ?> disabled>イベント</option>
				<option value="その他" <?php echo ($publicChk_category == "その他") ? "selected" : ""; ?> disabled>その他</option>
			</select>
		</div>
		
		<div id="attention">
			<table>
				<div>【メッセージ公開上の注意事項】</div>
				<tr><td>⚫︎</td><td>個人が特定される情報（氏名・住所）は公開しないように編集してください。</td></tr>
				<tr><td>⚫︎</td><td>写真・画像が公開されることはありません。</td></tr>
				<tr><td>⚫︎</td><td>ユーザー情報として、年代・世帯人数・職業の情報は公開されます。</td></tr>
				<tr><td>⚫︎</td><td>公開したメッセージについて、本アプリ運営元は一切の責任を負いかねますので、必ず内容をご確認の上、公開してください。</td></tr>
			</table>
		</div>

		<!-- 公開ボタン -->
		<div id="publishActButton">
			<a href="stopPublish.php" id="stopPublish">公開しない</a>
			<input type="submit" value="公開" id="publish">
		</div>

	</form>
</div>

<!-- モーダル用の背景 -->
<div id="modalBackground"></div>

<!-- 確認ダイアログ用のモーダル -->
<div id="confirmDialog">
	<p>メッセージの公開をやめますか？</p>
	<div id="dialogBtn">
		<button type="button" name="" id="confirmBtn">OK</button>
		<button type="button" id="cancelBtn">キャンセル</button>
	</div>
</div>

<!-- モーダル用の背景(背景白) -->
<div id="modalBackgroundWhite"></div>
<!-- 鳥のアイコン -->
<img src="imgs/send_icon.png" alt="" id="sendIcon">
<!-- 送信中のメッセージ（点滅させる） -->
<div id="nowSending">- sending message -</div>
<!-- メッセージ送信完了 -->
<div id="sendCompletely">メッセージを送信しました</br>
	<button type="button" id="sendCompletelyOK">OK</button>
</div>

<script>
	$(document).ready(function() {

		<?php if (isset($_SESSION["nowSending"]) && $_SESSION["nowSending"] == 1) {?>
			// 背景白のモーダル
			$('#modalBackgroundWhite').show();

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
					clearInterval(flashInterval);
					$('#sendCompletely').show();
				}
			});

			// メッセージ送信完了の通知
			$("#sendCompletelyOK").on("click", function() {
				$('#sendCompletely').fadeOut();
				$('#modalBackgroundWhite').fadeOut();
			})

			// 送信中アラートの表示
			let flashInterval = setInterval(function() {
				$("#nowSending").fadeToggle(300);
			}, 600);

		<?php } unset($_SESSION["nowSending"]);?>

		// メッセージを公開するかいないか選択しないとアラート表示
		<?php if (isset($_SESSION["stopPublish"]) && $_SESSION["stopPublish"] == 1) { ?>
			$(".footerIcon").on("click", function() {
				alert("公開するメッセージを確認しましょう。");
				// e.preventDefault();
				// $('#modalBackground, #confirmDialog').fadeIn();
				// $("#confirmBtn").on("click", function() {
					// window.location.href = "message.php";
				// })

				// キャンセルボタンがクリックされたときの処理
				// $('#cancelBtn, #modalBackground').on("click", function() {
					// モーダル背景とダイアログを非表示
					// $('#modalBackground, #confirmDialog').fadeOut();
				// });
			})
		<?php } ?>

	})
</script>

</body>

<footer>
	<div id="footerIconContainer">
		<a href="" id="footerMessage"><img src="imgs/message_white.png" alt="" id="" class="footerIcon"></a>
		<a href="" id="footerPublic"><img src="imgs/public_gray.png" alt="" id="" class="footerIcon"></a>
		<a href="" id="footerMypage"><img src="imgs/user_white.png" alt="" id="" class="footerIcon"></a>
	</div>
</footer>

</html>