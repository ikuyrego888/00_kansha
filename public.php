<?php
session_start();
include('funcs.php');
sschk();
$pdo = db_conn();

// 送信したメッセージを引用
$userId = $_SESSION["userId"];
// $partnerId = $_SESSION["partnerId"];

// ユーザーデータを取得
$stmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id = :id");
$stmt->bindValue(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
$partnerId = $userData["partnerId"];

$categoryCondition = "";
$ageCondition = "";
$marriageOption = "";
$textKey = "";

// 自分のメッセージを表示する場合
if (isset($_POST["myMessageChk"])) {
	$publicStmt = $pdo->prepare("SELECT * FROM kansha_publicMessage WHERE userId = :userId ORDER BY indate DESC");
	$publicStmt->bindValue(':userId', $userId, PDO::PARAM_INT);
	$publicStmt->execute();

// 普通に検索する場合
} else {

	// メッセージカテゴリーの検索条件を確認
	if (isset($_POST["categoryOption"])) {
		// チェックボックスで選択したものを格納
		$categoryOption = $_POST["categoryOption"];
		// 空白を入れないとWHEREが正しく読み込まれない
		$categoryCondition = " category IN ('";
		// "explode"は配列変換、"implode"は文字列変換である
		$categoryCondition .= implode("', '", $categoryOption);
		$categoryCondition .= "')";
	}

	// 年齢の検索条件を確認
	if (isset($_POST["ageOption"])) {
		// チェックボックスで選択したものを格納
		$ageOption = $_POST["ageOption"];
		// 空白を入れないとWHEREが正しく読み込まれない
		$ageCondition = " ageGroup IN ('";
		// "explode"は配列変換、"implode"は文字列変換である
		$ageCondition .= implode("', '", $ageOption);
		$ageCondition .= "')";
	}

	// 結婚年数の検索条件を確認
	if (isset($_POST["marriageOption"])) {
		// チェックボックスで選択したものを格納
		$marriageOption = $_POST["marriageOption"];
		// 空白を入れないとWHEREが正しく読み込まれない
		$marriageCondition = " anniversaryGroup IN ('";
		// "explode"は配列変換、"implode"は文字列変換である
		$marriageCondition .= implode("', '", $marriageOption);
		$marriageCondition .= "')";
	}

	// 検索タイプを確認（OR検索かAND検索か/デフォルトはOR検索）
	$search = isset($_POST["searchType"]) && $_POST["searchType"] == "or" ? " OR " : " AND ";

	// キーワード検索の入力条件を確認
	if (isset($_POST["textKey"])) {
		$textKey = $_POST["textKey"];
	}

	// SQLに検索結果を反映させるためのWHERE設定
	$whereData = "";
	// カテゴリにのみ条件入力がある場合
	if (!empty($categoryCondition) && empty($ageCondition) && empty($marriageCondition)) {
	$whereData = " WHERE" .$categoryCondition;
	// 年代にのみ条件入力がある場合
	} else if (empty($categoryCondition) && !empty($ageCondition) && empty($marriageCondition)) {
		$whereData = " WHERE" .$ageCondition;
	// 結婚年数にのみ条件入力がある場合
	} else if (empty($categoryCondition) && empty($ageCondition) && !empty($marriageCondition)) {
		$whereData = " WHERE" .$marriageCondition;
	// カテゴリ＋年代に条件入力がある場合
	} else if (!empty($categoryCondition) && !empty($ageCondition) && empty($marriageCondition)) {
		$whereData = " WHERE" .$categoryCondition .$search .$ageCondition;
	// カテゴリ＋結婚年数に条件入力がある場合
	} else if (!empty($categoryCondition) && empty($ageCondition) && !empty($marriageCondition)) {
		$whereData = " WHERE" .$categoryCondition .$search .$marriageCondition;
	// 年代＋結婚年数に条件入力がある場合
	} else if (empty($categoryCondition) && !empty($ageCondition) && !empty($marriageCondition)) {
		$whereData = " WHERE" .$ageCondition .$search .$marriageCondition;
	// すべてに条件入力がある場合
	} else if (!empty($categoryCondition) && !empty($ageCondition) && !empty($marriageCondition)) {
		$whereData = " WHERE" .$categoryCondition .$search .$ageCondition .$search .$marriageCondition;
	}

	// テキスト検索に条件がある場合は$whereDataに追加（※複数キーワード検索に対応）
	if (!empty($textKey)) {
		// 全角スペースで区切って配列にセットする（＝複数キーワードを全角スペースで認識する）
		$textKeyData = explode("　", $textKey);
		$textKeyArray = [];
		foreach ($textKeyData as $text) {
		$textKeyArray[] = "(message LIKE '%$text%' OR category LIKE '%$text%' OR ageGroup LIKE '%$text%' OR occupation LIKE '%$text%' OR anniversaryGroup LIKE '%$text%')";
		}
		if (empty($whereData)) {
		// 選択条件が空の場合は"WHERE"からセットする
		$whereData .= " WHERE " .implode(" AND ", $textKeyArray);
		} else {
		// 何かしらの検索条件がある場合は"WHERE"は不要なので、"AND"からセットする
		$whereData .= " AND " .implode(" AND ", $textKeyArray);
		}
	}

	if ($partnerId == NULL) {
		$partnerId = "NULL";
	}

	if (empty($whereData)) {
		$publicStmt = $pdo->prepare("SELECT * FROM kansha_publicMessage WHERE userId != :partnerId ORDER BY indate DESC");
		$publicStmt->bindValue(':partnerId', $partnerId, PDO::PARAM_INT);
		$publicStmt->execute();
	} else {
		$publicStmt = $pdo->prepare("SELECT * FROM kansha_publicMessage $whereData AND userId != :partnerId ORDER BY indate DESC");
		$publicStmt->bindValue(':partnerId', $partnerId, PDO::PARAM_INT);
		$publicStmt->execute();
	}
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>パートナーからの感謝</title>
  <link href="css/public.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://unpkg.com/@oddbird/css-toggles@1.0.2/dist/css-toggles.min.js" type="module"></script>
</head>
<body>

<!-- ヘッダー設定 -->
<header>
	<!-- ハンバーガーメニュー -->
	<div id="humbergerMenuContainer">
		<div id="humbergerMenu">
			<div></div>
			<div></div>
			<div></div>
		</div>
		<!-- <div id="searchText">検索</div> -->
	</div>

	<!-- 機能の表示 -->
	<div id="headerTitle">みんなの感謝</div>
	<!-- <div id="logoutMenu"><a href="logout.php" id="logout">ログアウト</a></div> -->
	<div id="logoutMenu">
		<a href="logout.php" id="logout"><img src="imgs/logout_icon.png" alt="" id="logoutIcon"></a>
	</div>
	<!-- アイコンの表示 -->
	<div id="iconBar">
		<img src="imgs/public_icon.png" alt="" id="publicIcon">
	</div>

	<div id="iconLine"></div>

	<!-- 並び替え用のセレクトボタン -->
	<select id="sortSelect" disabled>
		<option value="newest">新しい順</option>
		<option value="oldest">古い順</option>
		<option value="mostIine">いいね多い順</option>
	</select>

	<!-- 自分のメッセージだけ表示させるボタン -->
	<form action="" method="post">
		<button type="submit" name="myMessageChk" id="myMessageBtn">あなたの感謝</button>
	</form>

	<!-- トグルボタン ※使用せず -->
	<!-- <div id="toggleContainer">
  		<button class="toggleButton" id="toggle"></button>
  		<label for="toggle" class="toggleLabel">ソート切替<label>
	</div> -->
</header>

<div id="explanation">
	<div id="explanation_01">みんなの感謝は…</div>
	<div id="explanation_02">世の中の"感謝のメッセージ"を集めることで、</br>
	ユーザーが、感謝の伝え方に関するヒント</br>
	（例：ナイスな伝え方、感謝する事柄など）をもらい、</br>
	パートナーへ感謝を上手に伝えることを目指しています。</div>
	<div id="explanation_03">※パートナーからのメッセージは表示されません。</div>
</div>

<?php

$view = "";

while ($messages = $publicStmt->fetch(PDO::FETCH_ASSOC)) {

	// メッセージID（プライマリーキー）を取得
	$messageId = $messages["id"];

	// メッセージ投稿日を取得
	$indate = new DateTime($messages["indate"]);
	// 曜日を日本語で取得する
	$weekday = ['日', '月', '火', '水', '木', '金', '土'];
	$indateFormat = $indate -> format('Y/m/d(' .$weekday[$indate->format('w')] .')');

	// 過去にこのメッセージに自分が「いいね」を押しているかを判定
	$iineStmt = $pdo->prepare("SELECT * FROM kansha_iineTable WHERE messageId=:messageId AND userId=:userId");
	$iineStmt->bindValue(':messageId', $messageId, PDO::PARAM_INT);
	$iineStmt->bindValue(':userId', $userId, PDO::PARAM_INT);
	$iineStmt->execute();
	$iineChk = $iineStmt->fetch(PDO::FETCH_ASSOC);
	if($iineChk) {
		$iineVal = 1;
	} else {
		$iineVal = 0;
	}

	// メッセージのいいね数をカウント
	$iineCountStmt = $pdo->prepare("SELECT * FROM kansha_iineTable WHERE messageId=:messageId");
	$iineCountStmt->bindValue(':messageId', $messageId, PDO::PARAM_INT);
	$iineCountStmt->execute();
	$iineCount = $iineCountStmt->rowCount();

	// $viewのセット
	$view .= '<div id="messageContainer">';
	$view .= '<div id="writersInfo">';
	$view .= '<span>' .$messages["ageGroup"].'</span>';
	$view .= '<span id="slash">' .' / ' .'</span>';
	$view .= '<span> ' .$messages["occupation"] .' ' .'</span>';
	$view .= '<span id="indateFormat">' .$indateFormat .'</span></div>';
	$view .= '<div id="messageCard">';
	$view .= '<div id="message">' .nl2br(h($messages["message"])) .'</div>';
	$view .= '<div class="messageTag" id="categoryTag">' .$messages["category"] .'</div>';
	$view .= '<div class="messageTag" id="anniversaryTag">' .$messages["anniversaryGroup"] .'</div>';
	if ($messages["userId"] == $userId) {
		$view .= '<div class="messageTag" id="myMessageTag">あなた</div>';
	}
	if ($iineChk) {
		$view .= '<div class="iineContainer"><img src="imgs/iinePost_icon.png" alt="" class="iineBtn" id="iineBtn_' .$messageId .'" data-message-id="' .$messageId .'">';
		$view .= '<span class="iinePostText iineCount" id="iineTextId_' .$messageId .'">' .$iineCount .' いいね</span></div>';
	} else {
		$view .= '<div class="iineContainer"><img src="imgs/iine_icon.png" alt="" class="iineBtn" id="iineBtn_' .$messageId .'" data-message-id="' .$messageId .'">';
		$view .= '<span class="iineText iineCount" id="iineTextId_' .$messageId .'">' .$iineCount .' いいね</span></div>';
	}
	$view .= '</div></div>';
	// いいね判定用のinputフラグ（非表示）
	$view .= '<input type="hidden" name="iineFlg" class="iineFlg" id="iineFlg_' .$messageId .'" value="' .$iineVal .'">';
}

?>

<div id="messageField">
	<?php
	if ($_SERVER["REQUEST_METHOD"] === "POST") {
		echo $view;
	}
	?>
</div>

<div id="sideMenuContainer">
	<div id="modalBackground"></div>

	<div id="sidemenu">
		<form action="" method="post">
			<!-- シチュエーションを選択 -->
			<fieldset>
				<legend class="legendSet">感謝のカテゴリ</legend>
				<input type="checkbox" class="categoryChk" id="category01" name="categoryOption[]" value="家事">
				<label for="category01">家事</label>
				<input type="checkbox" class="categoryChk" id="category02" name="categoryOption[]" value="育児">
				<label for="category02">育児</label>
				<input type="checkbox" class="categoryChk" id="category03" name="categoryOption[]" value="イベント">
				<label for="category03">イベント</label>
				<input type="checkbox" class="categoryChk" id="category04" name="categoryOption[]" value="その他">
				<label for="category04">その他</label>
			</fieldset>

			<!-- 年齢を選択 -->
			<fieldset>
				<legend class="legendSet">年齢</legend>
				<input type="checkbox" class="ageChk" id="age02" name="ageOption[]" value="10〜20代">
				<label for="age02">10〜20代</label>
				<input type="checkbox" class="ageChk"  id="age03" name="ageOption[]" value="30代">
				<label for="age03">30代</label>
				<input type="checkbox" class="ageChk"  id="age04" name="ageOption[]" value="40代">
				<label for="age04">40代</label>
				<input type="checkbox" class="ageChk"  id="age05" name="ageOption[]" value="50代">
				<label for="age05">50代</label></br>
				<input type="checkbox" class="ageChk"  id="age06" name="ageOption[]" value="60代">
				<label for="age06">60代</label>
				<input type="checkbox" class="ageChk" id="age07" name="ageOption[]" value="70代〜">
				<label for="age07">70代〜</label>
			</fieldset>

			<!-- 結婚年数を選択 -->
			<fieldset>
				<legend class="legendSet">結婚年数</legend>
				<input type="checkbox" class="marriageChk" id="marriage01" name="marriageOption[]" value="1年未満">
				<label for="marriage01">1年未満</label>
				<input type="checkbox" class="marriageChk" id="marriage02" name="marriageOption[]" value="1年〜5年未満">
				<label for="marriage02">1年〜5年未満</label>
				<input type="checkbox" class="marriageChk" id="marriage03" name="marriageOption[]" value="5年〜10年未満">
				<label for="marriage03">5年〜10年未満</label></br>
				<input type="checkbox" class="marriageChk" id="marriage04" name="marriageOption[]" value="10年〜20年未満">
				<label for="marriage04">10年〜20年未満</label>
				<input type="checkbox" class="marriageChk" id="marriage05" name="marriageOption[]" value="20年〜30年未満">
				<label for="marriage05">20年〜30年未満</label>
				<input type="checkbox" class="marriageChk" id="marriage06" name="marriageOption[]" value="30年〜">
				<label for="marriage06">30年〜</label>
			</fieldset>

			<!-- 検索タイプを選択 -->
			<fieldset>
				<legend class="legendSet">検索タイプ</legend>
				<input type="radio" id="or" name="searchType" value="or" checked>
				<label for="or">OR検索</label>
				<input type="radio" id="and" name="searchType" value="and">
				<label for="and">AND検索</label>
			</fieldset>

			<!-- キーワード検索 -->
			<fieldset>
				<legend class="legendSet">キーワード検索<span id="andSearch">　*AND検索</span></legend>
				<input type="text" name="textKey" id="textKey" placeholder="（複数入力は全角スペースで区切る）">
			</fieldset>

			<!-- 検索実行ボタン -->
			<input type="submit" value="検索" id="searchButton">
		</form>
	</div>
</div>

<!-- パートナー未登録だった時に"1"をPOST通信する -->
<form method="POST" action="express.php" id="noPartnerChk">
	<input type="hidden" name="noPartner" id="noPartner" value="2">

	<div id="writeButton">
		<div id="memoModal">メッセージを作成</div>
		<div>
			<a href="express.php" id="expressLink"><img src="imgs/write_button.png" alt="" id="writeBtnIcon"></a>
		</div>
	</div>
</form>

<script>

	$(document).ready(function() {

		// ソートボタンを検索するまで押せないようにする設定
		let messageCount = $("#messageField").children().length;
		if (messageCount > 0) {
			$("#sortSelect").prop("disabled", false);
		} else {
			$("#sortSelect").prop("disabled", true);
		}

		// 検索したらみんなの感謝の説明文を非表示にする
		<?php if ($_SERVER["REQUEST_METHOD"] === "POST") { ?>
			$("#explanation").hide();
		<?php } ?>

		// メッセージ作成アイコンの設定
		$("#writeButton").on("mouseover", function() {
			$("#memoModal").show();
		})
		$("#writeButton").on("mouseleave", function() {
			$("#memoModal").hide();
		})

		let windowWidth = $(window).width();
		// ウィンドウの幅が640px以下の場合に改行を挿入する
		if (windowWidth <= 640) {
			$("#marriage06").before("</br>");
		}
	})

	// ハンバーガーメニューをクリックした時の設定
	$("#humbergerMenuContainer").on("click", function() {

		// モーダルを表示させる
		$('#modalBackground').fadeIn()

		// 現在のサイドメニューの位置を取得する
		let menuLeft = parseInt($("#sidemenu").css('left'));
		// console.log("現在の左位置:", menuLeft);
		
		// サイドメニューを表示させる
		if (menuLeft) {
			$("#sidemenu").css("left", "0px");
		}
	});

	// モーダルを押したらサイドメニューを非表示にする
	$("#modalBackground").on("click", function() {
		$("#modalBackground").fadeOut();
		$("#sidemenu").css("left", "-100%");
	});

	// $("#serchButton").on("click", function() {
	// })

	// いいねボタンをクリックした時の設定
	$(".iineBtn").on("click", function() {
		// いいねボタンにセットしている個別のmessageIdを取得（"messageId_XX"の形式）
		let messageId = $(this).attr("id");
		// いいねボタンにセットしている個別のmessageIdを取得（"XX"（プライマリーキー）の形式）
		let messageDataId = $(this).data("message-id");
		// 非表示にしている「いいねフラグ」の個別IDを取得
        let iineFlgId = $("#iineFlg_" + messageDataId).attr("id");
		// いいねの文字のdivタグのIDを取得
		let iineTextId = $("#iineTextId_" + messageDataId).attr("id");
		// 「いいねフラグ」のvalが"0"の時はvalを"1"にしてimgIconも差し替える。valが"1"の時はvalを"0"にしてimgIconを戻す
		if($("#" + iineFlgId).val() == 0 ){
			$("#" + iineFlgId).val("1");
			$("#" + messageId).attr("src", "imgs/iinePost_icon.png");
			$("#" + iineTextId).addClass("iineTextBrown");
			console.log("最終テスト："+$("#"+iineFlgId).val());
		} else {
			$("#" + iineFlgId).val("0");
			$("#" + messageId).attr("src", "imgs/iine_icon.png");
			$("#" + iineTextId).removeClass("iineTextBrown");
			$("#" + iineTextId).removeClass("iinePostText");
			$("#" + iineTextId).addClass("iineText");
			console.log("最終テスト："+$("#"+iineFlgId).val());
		}

		// いいねカウントを増減
		let iineCurrentCount = parseInt($("#"+iineTextId).text().split(" ")[0]);
		let iineNewCount = ($("#"+iineFlgId).val() == 1) ? iineCurrentCount + 1 : iineCurrentCount - 1;
		$("#"+iineTextId).text(iineNewCount + " いいね");
    
		// いいねボタンを押した時の設定
		iinePost(messageDataId, iineFlgId);
	});

	// いいねボタンを押した時のajax設定
	function iinePost(messageDataId, iineFlgId) {
		$.ajax({
			type : "POST",
			url : "public_iinePost.php",
			data : {
				messageId: messageDataId,
                iineFlg: $("#"+iineFlgId).val(),
			},
			dataType : "json",
			success : function(response) {
				// console.log("テスト："+response.success);
				if (response.success) {
					// alert("送信完了");
				} else {
					alert("送信失敗...");
				}
			}
		})
	}

	// セレクトボックスでの並び替え
	$("#sortSelect").on("change", function() {
		let selectOption = $(this).val();
		// switch関数で分岐設定し、breakで分岐毎に終了させる。
		switch (selectOption) {
			case "newest":
				sortNewMessages();
				break;
			case "oldest":
				sortOldMessages();
				break;
			case "mostIine":
				sortIineMessages();
				break;
			default:
				break;
		}
	});

	// 新しい順に並び替える関数
	function sortNewMessages() {
		let messages = $("#messageField").children("#messageContainer");
		messages.sort(function(a, b) {
			let dateA = new Date($(a).find("#indateFormat").text().split("(")[0]);
			let dateB = new Date($(b).find("#indateFormat").text().split("(")[0]);
			return dateB - dateA;
		});
		$("#messageField").empty().append(messages);
	}

	// 古い順に並び替える関数
	function sortOldMessages() {
		let messages = $("#messageField").children("#messageContainer");
		messages.sort(function(a, b) {
			let dateA = new Date($(a).find("#indateFormat").text().split("(")[0]);
			let dateB = new Date($(b).find("#indateFormat").text().split("(")[0]);
			return dateA - dateB;
		});
		$("#messageField").empty().append(messages);
	}

	// いいねが多い順に並び替える関数
	function sortIineMessages() {
		let messages = $("#messageField").children("#messageContainer");
		messages.sort(function(a, b) {
			let countA = parseInt($(a).find(".iineCount").text().split(" ")[0]);
			let countB = parseInt($(b).find(".iineCount").text().split(" ")[0]);
			return countB - countA;
		});
		$("#messageField").empty().append(messages);
	}

	// パートナー未登録の場合に"2"をPOST通信する
	$("#expressLink").on("click", function(e) {
		e.preventDefault();
		$("#noPartnerChk").submit();
	})

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

</script>

</body>

<footer>
	<div id="footerIconContainer">
		<a href="message.php"><img src="imgs/message_white.png" alt="" id="" class="footerIcon"></a>
		<a href="public.php"><img src="imgs/public_gray.png" alt="" id="" class="footerIcon"></a>
		<a href="mypage.php"><img src="imgs/user_white.png" alt="" id="mypageIcon" class="footerIcon">
			<span id="clickHere"><span id="arrow">←</span>Click Here!</span>
		</a>
	</div>
</footer>

</html>