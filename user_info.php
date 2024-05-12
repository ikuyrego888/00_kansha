<?php
session_start();
include('funcs.php');
sschk();
$pdo = db_conn();

// ユーザーテーブル内のユーザーIDとパートナーID
$userId = $_SESSION["userId"];
$partnerId = $_SESSION["partnerId"];

$stmt = $pdo->prepare("SELECT * FROM kansha_userTable WHERE id = :id");
$stmt->bindValue(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$userData = $stmt->fetch();

// 生年月日を"年"月"日"に分解
$birthday = $userData["birthday"];
$birthday_ = new DateTime($birthday);
$b_year = $birthday_->format("Y");
$b_month = $birthday_->format("m");
$b_day = $birthday_->format("d");

// 結婚記念日を"年"月"日"に分解
$anniversary = $userData["anniversary"];
$anniversary_ = new DateTime($anniversary);
$a_year = $anniversary_->format("Y");
$a_month = $anniversary_->format("m");
$a_day = $anniversary_->format("d");

// 郵便番号を3桁/4桁に分割
$postCode = h($userData["postCode"]);
$postCode_1 = substr($postCode, 0, 3);
$postCode_2 = substr($postCode, -4);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ユーザー情報</title>
  <link href="css/mypage.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>

<!-- ヘッダー設定 -->
<header>
	<!-- 機能の表示 -->
	<div id="headerTitle">あなたの情報</div>
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
	<div id="updateBtn">
		<a href="user_infoUpdate.php" id="update">変更する</a>
	</div>
</header>

<!-- ユーザー情報登録 -->
<div>
	<div id="form">
		<table>
			<!-- ユーザーネーム -->
			<tr>
				<td>ユーザー名<span class="must">必須</span></td>
				<td><?=h($userData["name"])?></td>
			</tr>

			<!-- 生年月日 -->
			<tr>
				<td>生年月日<span class="must">必須</span></td>
				<td><?= $b_year ?>年<?= $b_month ?>月<?= $b_day ?>日</td>
			</tr>

			<!-- 職業 -->
			<tr>
				<td>職業<span class="must">必須</span></td>
				<td><?= $userData["occupation"] ?></td>
			</tr>

			<!-- 性別 -->
			<tr>
				<td>性別<span class="must">必須</span></td>
				<td><?= $userData["sex"] ?></td>
			</tr>

			<!-- 結婚記念日 -->
			<tr>
				<td>結婚記念日<span class="must">必須</span></td>
				<td><?= $a_year ?>年<?= $a_month ?>月<?= $a_day ?>日</td>
			</tr>

			<!-- ユーザーID -->
			<tr>
				<td>ユーザーID<span class="must">必須</span></td>
				<td><?= $userData["lid"] ?></td>
			</tr>

			<!-- パスワード -->
			<tr>
				<td>パスワード<span class="must">必須</span></td>
				<td>***********</td>
			</tr>

			<!-- 感謝BOOK送付に必要な情報 -->
			<tr>
				<td id="asterisk" colspan="2">*以下の情報登録は任意ですが、感謝BOOK機能を利用するには登録が必要です。</td>
			</tr>

			<!-- 氏名（本名） -->
			<tr>
				<td>氏名<span class="option">任意</span></td>
				<td><?= empty($userData["lastName"] && $userData["firstName"]) ? "未登録" : $userData["lastName"]." ".$userData["firstName"] ?></td>
			</tr>

			<tr>
				<td>カナ氏名<span class="option">任意</span></td>
				<td><?= empty($userData["kanaLastName"] && $userData["kanaFirstName"]) ? "未登録" : $userData["kanaLastName"]." ".$userData["kanaFirstName"] ?></td>
			</tr>

			<!-- 住所 -->
			<tr>
				<td>住所<span class="option">任意</span></td>
				<td>
					<?php if(empty($userData["prefecture"]) && empty($userData["city"]) && empty($userData["houseNumber"]) && empty($userData["apartment"]) && empty($postCode_1) && empty($postCode_2)) : ?>
						未登録
					<?php else: ?>
						<?php if($userData["prefecture"] || $userData["city"] || $userData["houseNumber"] || $userData["apartment"] || $postCode_1 || $postCode_2) { ?>
						<span class="address"> 〒 </span>
						<?php if($postCode_1) {echo $postCode_1;} else {echo "□□□";} ?>
						<span class="address"> - </span>
						<?php if($postCode_2) {echo $postCode_2;} else {echo "□□□□";} ?>
						<?php }?>
						</br><?=$userData["prefecture"].h($userData["city"]).h($userData["houseNumber"]).h($userData["apartment"]);?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td>電話番号<span class="option">任意</span></td>
				<td><?= empty($userData["telephone"]) ? "未登録" : $userData["telephone"] ?></td>
			</tr>

		</table>
		<!-- <div id="btnContainer">
			<a href="mypage.php" id="returnBtn">戻る</a>
			<a href="user_infoUpdate.php" id="register">変更・登録</a>
		</div> -->
	</div>
</div>

<script>

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