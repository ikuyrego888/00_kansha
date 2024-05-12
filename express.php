<?php
session_start();
include('funcs.php');
sschk();
$pdo = db_conn();

$userId = $_SESSION["userId"];

// パートナーIDの紐付きが完了しているか確認する
$stmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id = :id");
$stmt->bindValue(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch();

// 紐付け登録が完了していない場合にredirectで画面を戻す
if(!$userData["partnerId"] && $_POST["noPartner"] == 1) {
	$_SESSION["partnerNotRegistered"] = 1;
	unset($_POST["noPartner"]);
	redirect("myMessage.php");

} else if(!$userData["partnerId"] && $_POST["noPartner"] == 2) {
	$_SESSION["partnerNotRegistered"] = 1;
	unset($_POST["noPartner"]);
	redirect("public.php");

} elseif(!$userData["partnerId"]) {
	$_SESSION["partnerNotRegistered"] = 1;
	redirect("message.php");
};

// 同一ユーザーで既に下書きがあるかを確認する
$stmtChk = $pdo->prepare("SELECT * FROM kansha_saveDraft WHERE userId = :userId");
$stmtChk->bindValue(':userId', $userId, PDO::PARAM_INT);
$stmtChk->execute();
$draft = $stmtChk->fetch();

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>感謝をつたえる</title>
  <link href="css/index.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>

<!-- ヘッダー設定 -->
<header>
	<!-- 機能の表示 -->
	<div id="headerTitle">パートナーへ感謝をつたえる</div>
	<!-- <div id="logoutMenu"><a href="logout.php" id="logout">ログアウト</a></div> -->
	<div id="logoutMenu">
		<a href="logout.php" id="logout"><img src="imgs/logout_icon.png" alt="" id="logoutIcon"></a>
	</div>
	<!-- アイコンの表示 -->
	<div id="iconBar">
		<img src="imgs/write_icon.png" alt="" id="writeIcon">
	</div>
	<div id="iconLine"></div>
</header>

<div id="form">
	<form method="POST" action="sendMessageAct.php" enctype="multipart/form-data" id="messageForm">
		<!-- メッセージ -->
		<div class="formItem">感謝のメッセージ</div>
		<div id="shift">
			<textarea name="message" id="message" placeholder="パートナーへ日頃の感謝をつたえよう" required><?php echo h($draft["message"]); ?></textarea>
		</div>

		<!-- 感謝のカテゴリ -->
		<div class="formItem">感謝のカテゴリ</div>
		<div id="shift">
			<select name="category" id="category" required>
				<option value="" hidden>選んでください</option>
				<option value="家事" <?php echo ($draft["category"] == "家事") ? "selected" : ""; ?> >家事</option>
				<option value="育児" <?php echo ($draft["category"] == "育児") ? "selected" : ""; ?> >育児</option>
				<option value="イベント" <?php echo ($draft["category"] == "イベント") ? "selected" : ""; ?> >イベント</option>
				<option value="その他" <?php echo ($draft["category"] == "その他") ? "selected" : ""; ?> >その他</option>
			</select>
		</div>

		<!-- 写真・画像 -->
		<div class="formItem" id="photoIamge">写真・画像<img src="imgs/img_icon.png" alt="" id="imgIcon"></div>
		
		<!-- ファイルinputタグ -->
		<div id="shift">
			<!-- input/fileをdisplay:noneとした上で、次行のbuttonはsubmitされないようにtype=buttonとする -->
			<input type="file" name="upfile" id="upfile" style="display: none;">
			<button type="button" id="upfileButton">ファイルを選択</button>
			
			<!-- 下書きにファイルがある場合は"ファイル選択中"を表示させる -->
			<span id="fileChk"><?php echo (!empty($draft["img"]) && $draft["img"] != 1 && $draft["img"] != 2) ? "ファイルを選択中" : "ファイルは選択されていません"; ?></span>
			
			<!-- 選択したファイルを表示させる -->
			<div id="previewImageSet" style="display: none;">
				<img src="" id="previewImage">
			</div>

			<!-- データベースに下書きファイルがある場合は表示させる -->
			<?php if (!empty($draft["img"]) && $draft["img"] != 1 && $draft["img"] != 2) { ?>
				<script>
					// データベースにファイルがある場合、srcを設定
					$("#previewImage").attr("src", "upload/<?php echo $draft["img"]; ?>");
					$("#previewImageSet").show();
				</script>
			<?php } ?>

			<!-- プレビューを削除したことを認識するフラグ用のinputタグ -->
			<input type="hidden" name="deleteImage" id="deleteImage" value="">

		</div>

		<!-- モーダル用の背景 -->
		<div id="modalBackground"></div>

		<!-- 確認ダイアログ用のモーダル -->
		<div id="confirmDialog">
			<p>選択中の画像を削除しますか？</p>
			<div id="dialogBtn">
				<button type="button" name="" id="confirmBtn">OK</button>
				<button type="button" id="cancelBtn">キャンセル</button>
			</div>
		</div>

		<!-- メッセージの公開/非公開 -->
		<div class="formItem">メッセージの公開/非公開</div>
		<div id="shift">
			<select name="publicChk" id="publicChk" required>
				<option value="非公開" selected>非公開</option>
				<option value="公開">公開</option>
			</select>
		</div>
		
		<div id="ActButton">
			<a href="message.php" id="return">戻る</a>
			<button type="button" id="saveButton">保存</button>
			<input type="submit" value="送信" id="sendButton">
		</div>

	</form>
</div>

<script>

	$(document).ready(function () {

		// ボタンがクリックされたらファイル選択ボタンをクリックする
		$("#upfileButton").on("click", function () {
			$("#upfile").click();
		});

		// ファイルを選択したら"ファイル選択中"が表示されるようにする（下書きファイルがあったら上書きする）
		$("#upfile").on("change", function() {
			// $("#deleteImage").val("");
			displayPreview();
		});

		// ダイアログ設定（プレビューをクリックした時に削除するかを確認する処理）
		$('#previewImage').on("click", function() {
			// モーダル背景とダイアログを表示
			$('#modalBackground, #confirmDialog').fadeIn();
		});

		// キャンセルボタンがクリックされたときの処理
		$('#cancelBtn, #modalBackground').on("click", function() {
			// モーダル背景とダイアログを非表示
			$('#modalBackground, #confirmDialog').fadeOut();
		});

		// OKボタンがクリックされたときの処理
		$('#confirmBtn').on("click", function() {
			// 選択したファイルを削除した場合はupfileを空にする。
			$("#upfile").val("");
			$("#previewImage").attr("src", "");
			$("#fileChk").text("ファイルは選択されていません");
			$('#modalBackground, #confirmDialog').fadeOut();
			$("#previewImageSet").hide();
			// プレビューを削除した場合のフラグ用inputタグに"1"をセット（下書き用データベースのimgカラムを削除する時に使う）
			$("#deleteImage").val("1");
		});
	});

	// ディスプレイ表示用の関数
	function displayPreview() {
		let fileInput = $("#upfile")[0];

		if (fileInput.files.length > 0) {
			// ファイルが選択された場合にファイル選択中を表示
			$("#fileChk").text("ファイルを選択中");

			// ファイルが選択された場合にファイルプレビューを表示
			if (fileInput.files[0] !== undefined) {
				let fileReader = new FileReader();
				fileReader.readAsDataURL(fileInput.files[0]);
				fileReader.onload = function(e) {
					$("#previewImage").attr("src", e.target.result);
					$("#previewImageSet").show();
				}
			}
		} else {
			// ファイルが選択されていない場合
			$("#fileChk").text("ファイルは選択されていません");
			$("#previewImage").attr("src", "");
			$("#previewImageSet").hide();
		}
	}

	// 保存ボタンがクリックされた時の処理（下書きデータベースへ送信）
	$("#saveButton").on("click", function() {
		$("#messageForm").attr("action", "saveDraftAct.php");
		$("#messageForm").submit();
	});

</script>

</body>

<footer>
	<div id="footerIconContainer">
		<a href="message.php"><img src="imgs/message_gray.png" alt="" id="" class="footerIcon"></a>
		<a href="public.php"><img src="imgs/public_white.png" alt="" id="" class="footerIcon"></a>
		<a href="mypage.php"><img src="imgs/user_white.png" alt="" id="" class="footerIcon"></a>
	</div>
</footer>

</html>