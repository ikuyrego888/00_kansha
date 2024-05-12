<?php
session_start();
include('funcs.php');
sschk();
$pdo = db_conn();

// ユーザーテーブル内のユーザーIDとパートナーID
$userId = $_SESSION["userId"];
$partnerId = $_SESSION["partnerId"];

// パートナーIDに自分のIDを入力した場合のアラート
if (isset($_SESSION["myLidError"]) && $_SESSION["myLidError"] == 1) {
    echo '<script>alert("入力されたユーザーIDはあなたのIDです。パートナーのユーザーIDを入力してください。");</script>';
	// エラーを表示したらセッションを削除する
    unset($_SESSION["myLidError"]);
}

// 現在のパートナーID入力エラーで戻ってきた場合のアラート
if (isset($_SESSION["requestError"]) && $_SESSION["requestError"] == 1) {
    echo '<script>alert("入力されたログインIDを保有するユーザーは存在しません。");</script>';
	// エラーを表示したらセッションを削除する
    unset($_SESSION["requestError"]);
}

$send = $pdo->prepare("SELECT * FROM kansha_partnerLink WHERE userId = :userId AND status='pending'");
$send->bindValue(':userId', $userId, PDO::PARAM_INT);
$send->execute();
$sendRequest = $send->fetch();

$get = $pdo->prepare("SELECT * FROM kansha_partnerLink WHERE partnerId = :userId AND status='pending'");
$get->bindValue(':userId', $userId, PDO::PARAM_INT);
$get->execute();
$getRequest = $get->fetch();

$_SESSION["requestId"] = $getRequest["id"];
$_SESSION["requestUserId"] = $getRequest["userId"];
$_SESSION["requestPartnerId"] = $getRequest["partnerId"];

// リクエスト申請元のデータ取得用（被申請者が承認・拒否で使用）
$requestSource = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id=:id");
$requestSource->bindValue(':id', $getRequest["userId"], PDO::PARAM_INT);
$requestSource->execute();
$requestSourceData = $requestSource->fetch();

$stmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id=:id ");
$stmt->bindValue(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch();

// パートナー情報を表示する場合に使用
if ($userData["partnerId"]) {
	$partnerStmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id=:id ");
	$partnerStmt->bindValue(':id', $userData["partnerId"], PDO::PARAM_INT);
	$partnerStmt->execute();
	$partnerData = $partnerStmt->fetch();

	// 生年月日を"年"月"日"に分解
	$birthday = $partnerData["birthday"];
	$birthday_ = new DateTime($birthday);
	$b_year = $birthday_->format("Y");
	$b_month = $birthday_->format("m");
	$b_day = $birthday_->format("d");

	// 結婚記念日を"年"月"日"に分解
	$anniversary = $partnerData["anniversary"];
	$anniversary_ = new DateTime($anniversary);
	$a_year = $anniversary_->format("Y");
	$a_month = $anniversary_->format("m");
	$a_day = $anniversary_->format("d");

	// 郵便番号を3桁/4桁に分割
	$postCode = h($partnerData["postCode"]);
	$postCode_1 = substr($postCode, 0, 3);
	$postCode_2 = substr($postCode, -4);

	// パートナー紐付き後の初回ログインを判定
	$firstLinkStmt = $pdo->prepare("SELECT * FROM kansha_dialogCount WHERE userId=:userId AND firstLink IS NULL");
	$firstLinkStmt->bindValue(':userId', $userId, PDO::PARAM_INT);
	$firstLinkStmt->execute();
	$firstLink = $firstLinkStmt->fetch(PDO::FETCH_ASSOC);

}

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
	<div id="headerTitle">パートナーの情報</div>
	<!-- <div id="logoutMenu"><a href="logout.php" id="logout">ログアウト</a></div> -->
	<div id="logoutMenu">
		<a href="logout.php" id="logout"><img src="imgs/logout_icon.png" alt="" id="logoutIcon"></a>
	</div>
	<div id="returnMenu"><a href="mypage.php" id="return">＜</a></div>
	<!-- アイコンの表示 -->
	<div id="iconBar">
		<img src="imgs/user_icon.png" alt="" id="userIcon">
	</div>
	<div id="iconLine"></div>
</header>

<?PHP
// パートナーIDが紐付いている場合は、パートナーの情報を表示
if ($userData["partnerId"]) {
?>
	<?php if($firstLink) {?>
		<!-- モーダル用の背景 -->
		<div id="modalBackground"></div>

		<!-- 確認ダイアログ用のモーダル -->
		<div id="firstLinkDialog">
			<p>パートナーの紐づけ登録が完了しました</p>
			<div id="dialogBtn">
				<button type="button" name="" id="confirmBtn">OK</button>
			</div>
		</div>

		<!-- ajaxでdialog.Count.phpへPOST通信する設定とするためformタグは不要（以下JavaScriptでajaxを記載） -->
		<input type="hidden" name="firstLink" id="firstLink" value="1">
	<?php } ?>

	<div id="form">
		<table>
			<!-- ユーザーネーム -->
			<tr>
				<td>ユーザー名</td>
				<td><?=h($partnerData["name"])?></td>
			</tr>

			<!-- 生年月日 -->
			<tr>
				<td>生年月日</td>
				<td><?= $b_year ?>年<?= $b_month ?>月<?= $b_day ?>日</td>
			</tr>

			<!-- 職業 -->
			<tr>
				<td>職業</td>
				<td><?= $partnerData["occupation"] ?></td>
			</tr>

			<!-- 性別 -->
			<tr>
				<td>性別</td>
				<td><?= $partnerData["sex"] ?></td>
			</tr>

			<!-- 結婚記念日 -->
			<tr>
				<td>結婚記念日</td>
				<td><?= $a_year ?>年<?= $a_month ?>月<?= $a_day ?>日</td>
			</tr>

			<!-- ユーザーID -->
			<tr>
				<td>ユーザーID</td>
				<td><?= $partnerData["lid"] ?></td>
			</tr>

			<!-- 氏名（本名） -->
			<tr>
				<td>氏名</td>
				<td><?= empty($partnerData["lastName"] && $partnerData["firstName"]) ? "未登録" : $partnerData["lastName"]." ".$partnerData["firstName"] ?></td>
			</tr>

			<tr>
				<td>カナ氏名</td>
				<td><?= empty($partnerData["kanaLastName"] && $partnerData["kanaFirstName"]) ? "未登録" : $partnerData["kanaLastName"]." ".$partnerData["kanaFirstName"] ?></td>
			</tr>

			<!-- 住所 -->
			<tr>
				<td>住所</td>
				<td>
					<?php if(empty($partnerData["prefecture"]) && empty($partnerData["city"]) && empty($partnerData["houseNumber"]) && empty($partnerData["apartment"]) && empty($postCode_1) && empty($postCode_2)) : ?>
						未登録
					<?php else: ?>
						<?php if($partnerData["prefecture"] || $partnerData["city"] || $partnerData["houseNumber"] || $partnerData["apartment"] || $postCode_1 || $postCode_2) { ?>
						<span class="address"> 〒 </span>
						<?php if($postCode_1) {echo $postCode_1;} else {echo "□□□";} ?>
						<span class="address"> - </span>
						<?php if($postCode_2) {echo $postCode_2;} else {echo "□□□□";} ?>
						<?php }?>
						</br><?=$partnerData["prefecture"].h($partnerData["city"]).h($partnerData["houseNumber"]).h($partnerData["apartment"]);?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td>電話番号</td>
				<td><?= empty($partnerData["telephone"]) ? "未登録" : $partnerData["telephone"] ?></td>
			</tr>

		</table>
	</div>

<!-- パートナー申請を受けている場合 -->
<?php } elseif ($getRequest["status"] == "pending") {
?>

<!-- 申請の承認・否認フォーム -->
<div id="form">
	<!-- パートナー紐付け設定 -->
	<div id="explanation">
		<span id="explainPartner_1">パートナーの紐づけ設定</span>
		</br><span id="explainPartner_2"><span id="explain_br">あなたのパートナーのユーザーIDを</span><span>登録しましょう</span></span>
	</div>
	<div id="applyContainer">
		<div id="application">以下のユーザーから</br>紐づけリクエストが届いています。</div>
		<div id="requestTable">
		<table>
			<tr>
				<td>ユーザーID</td>
				<td><?php echo $requestSourceData["lid"]; ?></td>
			</tr>
		</table>
		</div>
		<!-- <div>ユーザーID：<?php echo $requestSourceData["lid"]; ?></div> -->
		<form method="POST" action="partnerApprovalAct.php" id="approvalDenyForm">
			<div id="approveContainer">
				<div id="approveChk">リクエストを承認しますか？</div>
				<div id="approveRadioBtn">
					<label><input type="radio" name="requestStatus" value="approve" required> 承認</label>
					<label><input type="radio" name="requestStatus" value="deny" required> 否認</label>
				</div>
			</div>
			<div>
				<input type="submit" id="approvalDenyBtn" value="送信する">
			</div>

			<!-- モーダル用の背景 -->
			<div id="modalBackground"></div>

			<!-- 確認ダイアログ用のモーダル -->
			<div id="confirmDialog">
				<p>本当に送信してよろしいですか？</p>
				<div id="dialogBtn">
					<button type="button" name="" id="approvalConfirmBtn">OK</button>
					<button type="button" id="cancelBtn">キャンセル</button>
				</div>
			</div>

		</form>
	</div>
</div>
<?php

// パートナー申請の承認待ちの場合
} elseif ($sendRequest["status"] == "pending") {?>
	<div id="form">
		<!-- パートナー紐付け設定 -->
		<div id="explanation">
			<span id="explainPartner_1">パートナーの紐づけ設定</span>
			</br><span id="explainPartner_2"><span id="explain_br">あなたのパートナーのユーザーIDを</span><span>登録しましょう</span></span>
		</div>
		<div id="partnerLidContainer">
			<div id="application">パートナーリクエスト申請中です。</br>相手からの承認を待ちましょう。</div>
			<div class="explain" id="explainApplication">（リスクエスト送信済み）</div>
			<form action="partnerRequestCancelAct.php" id="cancelRequestForm">
				<input type="submit" id="requestCancelBtn" value="リクエスト取消し">

				<!-- モーダル用の背景 -->
				<div id="modalBackground"></div>

				<!-- 確認ダイアログ用のモーダル -->
				<div id="confirmDialog">
					<p>本当に申請を取り消しますか？</p>
					<div id="dialogBtn">
						<button type="button" name="" id="cancelConfirmBtn">OK</button>
						<button type="button" id="cancelBtn">キャンセル</button>
					</div>
				</div>

			</form>
		</div>
	</div>
<!-- それ以外の場合（パートナー申請前や、パートナー申請記録はあるが否認している場合） -->
<?php } else {
// elseif (!$getRequest || ($getRequest && $getRequest["status"] == "deny") || ($sendRequest && $sendRequest["status"] == "deny")) {
?>

<div id="form">
	<form method="POST" action="partnerRequestAct.php" id="requestForm">
		<!-- パートナー紐付け設定 -->
		<div id="explanation">
			<span id="explainPartner_1">パートナーの紐づけ設定</span>
			</br><span id="explainPartner_2"><span id="explain_br">あなたのパートナーのユーザーIDを</span><span>入力しましょう</span></span>
		</div>
		<div id="partnerLidContainer"><span id="partnerLidItem">■パートナーのユーザーID</span></br>
    		<input type="text" name="partnerLid" id="partnerLid" placeholder="メールアドレスを入力" required>
			<div class="explain" id="explainPartnerLid">※パートナーがユーザーIDに設定しているメールアドレスを入力してください。
				</br>※メールアドレスの入力間違いにご注意ください。
			</div>
			<div><input type="submit" id="requestBtn" value="リクエストを送信"></div>
		</div>

		<!-- モーダル用の背景 -->
		<div id="modalBackground"></div>

		<!-- 確認ダイアログ用のモーダル -->
		<div id="confirmDialog">
			<p>本当にリクエストを送信しますか？</p>
			<div id="dialogBtn">
				<button type="button" name="" id="confirmBtn">OK</button>
				<button type="button" id="cancelBtn">キャンセル</button>
			</div>
		</div>

	</form>
</div>

<?php
}
?>

<script>
	$(document).ready(function () {

		// パートナー紐付け登録完了時の初回のみに表示されるダイアログ
		<?php if($firstLink) {?>
			$('#modalBackground, #firstLinkDialog').fadeIn();
			// クリックしたらデータベース内のfirstLinkにカウント"1"をPOST通信し、2回目からダイアログを表示させないようにする
			$('#confirmBtn, #modalBackground').on("click", function() {
				firstLinkConfirm();
			});
			// ajaxでデータベース内のfirstLinkにカウント"1"をPOST通信する。
			function firstLinkConfirm() {
				$("#firstLink").val("1");
				$.ajax({
					type : "POST",
					url : "dialogCount.php",
					data : $("#firstLink").serialize(),
					dataType : "json",
					success : function(response) {
						// console.log("テスト："+response.success);
						if (response.success) {
							// alert("送信完了");
							$('#firstLinkDialog').fadeOut();
							$('#modalBackground').fadeOut();
						} else {
							alert("送信失敗...");
						}
					}
				})
			}
		<?php } ?>

		// ダイアログ設定（プレビューをクリックした時に削除するかを確認する処理）
		$('#requestBtn').on("click", function(e) {
			// パートナーIDの入力有無をチェック
			if ($("form")[0].checkValidity()) {
				// モーダル背景とダイアログを表示
				$('#modalBackground, #confirmDialog').fadeIn();
			} else {
				// 未入力時のエラーメッセージ
				alert("パートナーのログインIDが入力されていません。");
			}
			// フォームの送信をキャンセル
			e.preventDefault();
		});

		$('#requestCancelBtn').on("click", function(e) {
			// モーダル背景とダイアログを表示
			$('#modalBackground, #confirmDialog').fadeIn();
			// フォームの送信をキャンセル
			e.preventDefault();
		});

		$('#approvalDenyBtn').on("click", function(e) {

			if (!$('input[name="requestStatus"]').is(':checked')) {
            	// 選択されていない場合はモーダルを表示せず、フォームの送信をキャンセル
            	alert("「承認」か「否認」を選択してください。");
            	e.preventDefault();
				return;
			}

			// モーダル背景とダイアログを表示
			$('#modalBackground, #confirmDialog').fadeIn();
			// フォームの送信をキャンセル
			e.preventDefault();
		});

		// キャンセルボタンがクリックされたときの処理
		$('#cancelBtn, #modalBackground').on("click", function() {
			// モーダル背景とダイアログを非表示
			$('#modalBackground, #confirmDialog').fadeOut();
		});

		// OKボタンがクリックされたときの処理
		$('#confirmBtn').on("click", function() {
			// フォームを送信
			$("#requestForm").submit();
		});

		// リクエスト取り消しボタンがクリックされたときの処理
		$('#cancelConfirmBtn').on("click", function() {
			// フォームを送信
			$("#cancelRequestForm").submit();
		});

		// OKボタンがクリックされたときの処理
		$('#approvalConfirmBtn').on("click", function() {
			// フォームを送信
			$("#approvalDenyForm").submit();
		});

	});

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