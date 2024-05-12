<?php
session_start();
include("funcs.php");

$newLid = $_POST['newLid'];
$newLpw = $_POST['newLpw'];

// 登録IDエラーで戻ってきた場合のアラート
if (isset($_SESSION["lidError"]) && $_SESSION["lidError"] == 1) {
    echo '<script>alert("このログインIDは使用することができません。他のメールアドレスを使用してください。");</script>';
	// エラーを表示したらセッションを削除する
    unset($_SESSION["lidError"]);
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ユーザー登録</title>
  <link href="css/newRegister.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>

<!-- ヘッダー設定 -->
<header>
	<div id="headerTitle">ユーザー登録</div>
</header>

<!-- アプリ名の表示 -->
<div id="appliTitle">夫婦感謝アプリ</div>
<div id="appliSubtitle">- Let's express gratitude to your partner -</div>

<!-- ユーザー情報登録 -->
<div id="form">
	<form method="POST" action="user_insert.php">
		<table>
			<!-- ユーザーネーム -->
			<tr>
				<td>ユーザー名<span id="must">必須</span></td>
				<td><input type="text" name="name" class="userInfo" placeholder="アプリ内で使う名前を入力" required></td>
			</tr>

			<!-- 生年月日 -->
			<tr>
				<td>生年月日<span id="must">必須</span></td>
				<td>
					<select name="year" id="year" required>
						<?php
							$currentYear = date("Y");
							for ($year = 1950; $year <= $currentYear; $year++) {
								echo "<option value='$year'>$year</option>";
							}
						?>
					</select>
					<label for="year" id="ymd">年 </label>
					<select name="month" id="month" required>
						<?php
							for ($month = 1; $month <= 12; $month++) {
								echo "<option value='$month'>$month</option>";
							}
						?>
					</select>
					<label for="month" id="ymd">月</label>
					<select name="day" id="day" required>
						<?php
							for ($day = 1; $day <= 31; $day++) {
								echo "<option value='$day'>$day</option>";
							}
						?>
					</select>
					<label for="day" id="ymd">日</label>
				</td>
			</tr>

			<!-- 職業 -->
			<tr>
				<td>職業<span id="must">必須</span></td>
				<td>
					<select name="occupation" id="occupation" required>
						<option value="" hidden>選んでください</option>
						<option value="会社員">会社員</option>
						<option value="公務員">公務員</option>
						<option value="自営業/個人事業">自営業/個人事業</option>
						<option value="会社役員">会社役員</option>
						<option value="自由業">自由業</option>
						<option value="専業主婦(夫)">専業主婦(夫)</option>
						<option value="パート/アルバイト">パート/アルバイト</option>
						<option value="無職">無職</option>
						<option value="その他">その他</option>
					</select>
				</td>
			</tr>

			<!-- 性別 -->
			<tr>
				<td>性別<span id="must">必須</span></td>
				<td>
					<label><input type="radio" name="sex" value="男性" required> 男性</label>
					<label><input type="radio" name="sex" value="女性" required> 女性</label>
					<label><input type="radio" name="sex" value="その他" required> その他</label>
				</td>
				<!-- <td>
					<select name="sex" id="sex" required>
						<option value="" hidden>選んでください</option>
						<option value="男性">男性</option>
						<option value="女性">女性</option>
						<option value="その他">その他</option>
					</select>
				</td> -->
			</tr>

			<!-- 結婚記念日 -->
			<tr>
				<td>結婚記念日<span id="must">必須</span></td>
				<td>
					<select name="anniversaryYear" id="year" required>
						<?php
							$currentYear = date("Y");
							for ($year = 1970; $year <= $currentYear; $year++) {
								echo "<option value='$year'>$year</option>";
							}
						?>
					</select>
					<label for="year" id="ymd">年 </label>
					<select name="anniversaryMonth" id="month" required>
						<?php
							for ($month = 1; $month <= 12; $month++) {
								echo "<option value='$month'>$month</option>";
							}
						?>
					</select>
					<label for="month" id="ymd">月</label>
					<select name="anniversaryDay" id="day" required>
						<?php
							for ($day = 1; $day <= 31; $day++) {
								echo "<option value='$day'>$day</option>";
							}
						?>
					</select>
					<label for="day" id="ymd">日</label>
				</td>
			</tr>

			<!-- 新規ユーザーID -->
      		<tr>
				<td>新規ID<span id="must">必須</span></td>
				<td><input type="text" name="newLid" class="userInfo" value="<?=h($newLid)?>" placeholder="メールアドレスを入力" required></td>
			</tr>

			<!-- 新規パスワード -->
      		<tr>
				<td>新規パスワード<span id="must">必須</span></td>
				<td><input type="password" name="newLpw" class="userInfo" value="<?=h($newLpw)?>" placeholder="新規パスワードを入力" required></td>
			</tr>
		</table>
		<div id="notes">※生年月日と結婚記念日以外の情報はあとで変更できます。</div>
		<input type="submit" value="ユーザー登録（確定）" id="register">
		<a href="login.php" id="return">戻る</a>
	</form>
</div>

<script>

</script>

</body>
</html>