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
$posrCode = h($userData["postCode"]);
$postCode_1 = substr($posrCode, 0, 3);
$postCode_2 = substr($posrCode, -4);

// 現在のパスワード入力エラーで戻ってきた場合のアラート
if (isset($_SESSION["passwordError"]) && $_SESSION["passwordError"] == 1) {
    echo '<script>alert("現在のパスワードが正しく入力されていません");</script>';
	// エラーを表示したらセッションを削除する
    unset($_SESSION["passwordError"]);
}

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
	<div id="headerTitle">登録情報の変更</div>
	<!-- <div id="logoutMenu"><a href="logout.php" id="logout">ログアウト</a></div> -->
	<div id="logoutMenu">
		<a href="logout.php" id="logout"><img src="imgs/logout_icon.png" alt="" id="logoutIcon"></a>
	</div>
	<div id="returnMenu"><a href="user_info.php" id="return">＜</a></div>
	<!-- アイコンの表示 -->
	<div id="iconBar">
		<img src="imgs/user_icon.png" alt="" id="userIcon">
	</div>
	<div id="iconLine"></div>
</header>

<!-- ユーザー情報登録 -->
<div>
	<div id="form">
		<form method="POST" action="user_infoUpdateAct.php">
			<table>
				<!-- ユーザーネーム -->
				<tr>
					<td>ユーザー名<span class="must">必須</span></td>
					<td><input type="text" name="name" class="userInfoUpdate" id="name" value=<?=h($userData["name"])?> placeholder="アプリ内で使う名前を入力" required></td>
				</tr>

				<!-- 生年月日 -->
				<tr>
					<td>生年月日<span class="must">必須</span></td>
					<td>
						<select name="year" id="year" required disabled>
							<?php
								$currentYear = date("Y");
								for ($year = 1950; $year <= $currentYear; $year++) {
									$selectedYear = ($year == $b_year) ? "selected" : "";
									echo "<option value='$year' $selectedYear>$year</option>";
								}
							?>
						</select>
						<label for="year" id="ymd">年 </label>
						<select name="month" id="month" required disabled>
							<?php
								for ($month = 1; $month <= 12; $month++) {
									$selectedMonth = ($month == $b_month) ? "selected" : "";
									echo "<option value='$month' $selectedMonth>$month</option>";
								}
							?>
						</select>
						<label for="month" id="ymd">月</label>
						<select name="day" id="day" required disabled>
							<?php
								for ($day = 1; $day <= 31; $day++) {
									$selectedDay = ($day == $b_day) ? "selected" : "";
									echo "<option value='$day' $selectedDay>$day</option>";
								}
							?>
						</select>
						<label for="day" id="ymd">日</label>
					</td>
				</tr>

				<!-- 職業 -->
				<tr>
					<td>職業<span class="must">必須</span></td>
					<td>
						<select name="occupation" id="occupation" required>
							<option value="" hidden>選んでください</option>
							<option value="会社員" <?php echo ($userData["occupation"] == "会社員") ? "selected" : ""; ?>>会社員</option>
							<option value="公務員" <?php echo ($userData["occupation"] == "公務員") ? "selected" : ""; ?>>公務員</option>
							<option value="自営業/個人事業" <?php echo ($userData["occupation"] == "自営業/個人事業") ? "selected" : ""; ?>>自営業/個人事業</option>
							<option value="会社役員" <?php echo ($userData["occupation"] == "会社役員") ? "selected" : ""; ?>>会社役員</option>
							<option value="自由業" <?php echo ($userData["occupation"] == "自由業") ? "selected" : ""; ?>>自由業</option>
							<option value="専業主婦(夫)" <?php echo ($userData["occupation"] == "専業主婦(夫)") ? "selected" : ""; ?>>専業主婦(夫)</option>
							<option value="パート/アルバイト" <?php echo ($userData["occupation"] == "パート/アルバイト") ? "selected" : ""; ?>>パート/アルバイト</option>
							<option value="無職" <?php echo ($userData["occupation"] == "無職") ? "selected" : ""; ?>>無職</option>
							<option value="その他" <?php echo ($userData["occupation"] == "その他") ? "selected" : ""; ?>>その他</option>
						</select>
					</td>
				</tr>

				<!-- 性別（ラジオボタン） -->
				<tr>
					<td>性別<span class="must">必須</span></td>
					<td>
						<label><input type="radio" name="sex" value="男性" <?php echo ($userData["sex"] == "男性") ? "checked" : ""; ?> required> 男性</label>
						<label><input type="radio" name="sex" value="女性" <?php echo ($userData["sex"] == "女性") ? "checked" : ""; ?> required> 女性</label>
						<label><input type="radio" name="sex" value="その他" <?php echo ($userData["sex"] == "その他") ? "checked" : ""; ?> required> その他</label>
					</td>
				</tr>

				<!-- 結婚記念日 -->
				<tr>
					<td>結婚記念日<span class="must">必須</span></td>
					<td>
						<select name="anniversaryYear" id="year" required disabled>
							<?php
								$currentYear = date("Y");
								for ($year = 1950; $year <= $currentYear; $year++) {
									$selected_A_Year = ($year == $a_year) ? "selected" : "";
									echo "<option value='$year' $selected_A_Year>$year</option>";
								}
							?>
						</select>
						<label for="year" id="ymd">年 </label>
						<select name="anniversaryMonth" id="month" required disabled>
							<?php
								for ($month = 1; $month <= 12; $month++) {
									$selected_A_Month = ($month == $a_month) ? "selected" : "";
									echo "<option value='$month' $selected_A_Month>$month</option>";
								}
							?>
						</select>
						<label for="month" id="ymd">月</label>
						<select name="anniversaryDay" id="day" required disabled>
							<?php
								for ($day = 1; $day <= 31; $day++) {
									$selected_A_Day = ($day == $a_day) ? "selected" : "";
									echo "<option value='$day' $selected_A_Day>$day</option>";
								}
							?>
						</select>
						<label for="day" id="ymd">日</label>
					</td>
				</tr>

				<!-- ユーザーID -->
				<tr>
					<td>ユーザーID<span class="must">必須</span></td>
					<td><input type="text" name="lid" class="userInfoUpdate" value="<?=h($userData["lid"])?>" placeholder="新規IDを入力" required></td>
				</tr>

				<!-- パスワード -->
				<tr>
					<td>パスワード<span class="must">必須</span></td>
					<td><input type="checkbox" id="changePassword"><label for="changePassword">パスワードを変更する</label></br>
						<div id="passwordContainer">
							<div class="passwordItem">現在のパスワード</br>
								<input type="password" name="currentLpw" class="updatePassword" value="" placeholder="＊＊＊＊＊＊＊＊">
							</div>
							<div class="passwordItem">新しいパスワード</br>
								<input type="password" name="newLpw" pattern=".{4,}" id="newLpw" class="updatePassword" value="" placeholder="＊＊＊＊＊＊＊＊">
								</br><span class="explain">※英数記号含む半角8文字以上で設定してください。</span>
							</div>
							<div class="passwordItem">新しいパスワード（確認）</br>
								<input type="password" name="confirmNewLpw" pattern=".{4,}" id="confirmNewLpw" class="updatePassword" value="" placeholder="＊＊＊＊＊＊＊＊">
							</div>
						</div>
						<!-- パスワード変更有無を判定するinputタグ -->
						<input type="hidden" name="whetherChange" id="whetherChange" value="">
					</td>
				</tr>

				<!-- 感謝BOOK送付に必要な情報 -->
				<tr>
					<td id="asterisk" colspan="2">※以下の情報登録は任意ですが、感謝BOOK機能を利用するには登録が必要です。</td>
				</tr>

				<!-- 氏名（本名） -->
				<tr>
					<td>氏名<span class="option">任意</span></td>
					<td>
						<input type="text" name="lastName" id="lastName"  value="<?=h($userData["lastName"])?>" placeholder="姓を入力">
						<input type="text" name="firstName" id="firstName"  value="<?=h($userData["firstName"])?>" placeholder="名を入力">
					</td>
				</tr>

				<tr>
					<td>カナ氏名<span class="option">任意</span></td>
					<td>
						<input type="text" name="kanaLastName" id="lastName"  value="<?=h($userData["kanaLastName"])?>" placeholder="姓(カナ)を入力">
						<input type="text" name="kanaFirstName" id="firstName"  value="<?=h($userData["kanaFirstName"])?>" placeholder="名(カナ)を入力">
					</td>
				</tr>

				<!-- 住所 -->
				<tr>
					<td>住所<span class="option">任意</span></td>
					<td>
						<div class="address">郵便番号</br>
							<span class="address"> 〒 </span>
							<input type="text" pattern="[0-9０-９]*" maxlength="3" name="postCode_1" value="<?=$postCode_1?>" class="postCode" placeholder="">
							<span class="address"> - </span>
							<input type="text" pattern="[0-9０-９]*" maxlength="4" name="postCode_2"  value="<?=$postCode_2?>" class="postCode" placeholder=""></br>
						</div>
						<div class="address">都道府県</br>
							<select name="prefecture" id="prefecture">
								<option value="" selected disabled>都道府県を選択</option>
								<option value="北海道" <?php echo ($userData["prefecture"] == "北海道") ? "selected" : ""; ?>>北海道</option>
								<option value="青森県" <?php echo ($userData["prefecture"] == "青森県") ? "selected" : ""; ?>>青森県</option>
								<option value="岩手県" <?php echo ($userData["prefecture"] == "岩手県") ? "selected" : ""; ?>>岩手県</option>
								<option value="宮城県" <?php echo ($userData["prefecture"] == "宮城県") ? "selected" : ""; ?>>宮城県</option>
								<option value="秋田県" <?php echo ($userData["prefecture"] == "秋田県") ? "selected" : ""; ?>>秋田県</option>
								<option value="山形県" <?php echo ($userData["prefecture"] == "山形県") ? "selected" : ""; ?>>山形県</option>
								<option value="福島県" <?php echo ($userData["prefecture"] == "福島県") ? "selected" : ""; ?>>福島県</option>
								<option value="茨城県" <?php echo ($userData["prefecture"] == "茨城県") ? "selected" : ""; ?>>茨城県</option>
								<option value="栃木県" <?php echo ($userData["prefecture"] == "栃木県") ? "selected" : ""; ?>>栃木県</option>
								<option value="群馬県" <?php echo ($userData["prefecture"] == "群馬県") ? "selected" : ""; ?>>群馬県</option>
								<option value="埼玉県" <?php echo ($userData["prefecture"] == "埼玉県") ? "selected" : ""; ?>>埼玉県</option>
								<option value="千葉県" <?php echo ($userData["prefecture"] == "千葉県") ? "selected" : ""; ?>>千葉県</option>
								<option value="東京都" <?php echo ($userData["prefecture"] == "東京都") ? "selected" : ""; ?>>東京都</option>
								<option value="神奈川県" <?php echo ($userData["prefecture"] == "神奈川県") ? "selected" : ""; ?>>神奈川県</option>
								<option value="新潟県" <?php echo ($userData["prefecture"] == "新潟県") ? "selected" : ""; ?>>新潟県</option>
								<option value="富山県" <?php echo ($userData["prefecture"] == "富山県") ? "selected" : ""; ?>>富山県</option>
								<option value="石川県" <?php echo ($userData["prefecture"] == "石川県") ? "selected" : ""; ?>>石川県</option>
								<option value="福井県" <?php echo ($userData["prefecture"] == "福井県") ? "selected" : ""; ?>>福井県</option>
								<option value="山梨県" <?php echo ($userData["prefecture"] == "山梨県") ? "selected" : ""; ?>>山梨県</option>
								<option value="長野県" <?php echo ($userData["prefecture"] == "長野県") ? "selected" : ""; ?>>長野県</option>
								<option value="岐阜県" <?php echo ($userData["prefecture"] == "岐阜県") ? "selected" : ""; ?>>岐阜県</option>
								<option value="静岡県" <?php echo ($userData["prefecture"] == "静岡県") ? "selected" : ""; ?>>静岡県</option>
								<option value="愛知県" <?php echo ($userData["prefecture"] == "愛知県") ? "selected" : ""; ?>>愛知県</option>
								<option value="三重県" <?php echo ($userData["prefecture"] == "三重県") ? "selected" : ""; ?>>三重県</option>
								<option value="滋賀県" <?php echo ($userData["prefecture"] == "滋賀県") ? "selected" : ""; ?>>滋賀県</option>
								<option value="京都府" <?php echo ($userData["prefecture"] == "京都府") ? "selected" : ""; ?>>京都府</option>
								<option value="大阪府" <?php echo ($userData["prefecture"] == "大阪府") ? "selected" : ""; ?>>大阪府</option>
								<option value="兵庫県" <?php echo ($userData["prefecture"] == "兵庫県") ? "selected" : ""; ?>>兵庫県</option>
								<option value="奈良県" <?php echo ($userData["prefecture"] == "奈良県") ? "selected" : ""; ?>>奈良県</option>
								<option value="和歌山県" <?php echo ($userData["prefecture"] == "和歌山県") ? "selected" : ""; ?>>和歌山県</option>
								<option value="鳥取県" <?php echo ($userData["prefecture"] == "鳥取県") ? "selected" : ""; ?>>鳥取県</option>
								<option value="島根県" <?php echo ($userData["prefecture"] == "島根県") ? "selected" : ""; ?>>島根県</option>
								<option value="岡山県" <?php echo ($userData["prefecture"] == "岡山県") ? "selected" : ""; ?>>岡山県</option>
								<option value="広島県" <?php echo ($userData["prefecture"] == "広島県") ? "selected" : ""; ?>>広島県</option>
								<option value="山口県" <?php echo ($userData["prefecture"] == "山口県") ? "selected" : ""; ?>>山口県</option>
								<option value="徳島県" <?php echo ($userData["prefecture"] == "徳島県") ? "selected" : ""; ?>>徳島県</option>
								<option value="香川県" <?php echo ($userData["prefecture"] == "香川県") ? "selected" : ""; ?>>香川県</option>
								<option value="愛媛県" <?php echo ($userData["prefecture"] == "愛媛県") ? "selected" : ""; ?>>愛媛県</option>
								<option value="高知県" <?php echo ($userData["prefecture"] == "高知県") ? "selected" : ""; ?>>高知県</option>
								<option value="福岡県" <?php echo ($userData["prefecture"] == "福岡県") ? "selected" : ""; ?>>福岡県</option>
								<option value="佐賀県" <?php echo ($userData["prefecture"] == "佐賀県") ? "selected" : ""; ?>>佐賀県</option>
								<option value="長崎県" <?php echo ($userData["prefecture"] == "長崎県") ? "selected" : ""; ?>>長崎県</option>
								<option value="熊本県" <?php echo ($userData["prefecture"] == "熊本県") ? "selected" : ""; ?>>熊本県</option>
								<option value="大分県" <?php echo ($userData["prefecture"] == "大分県") ? "selected" : ""; ?>>大分県</option>
								<option value="宮崎県" <?php echo ($userData["prefecture"] == "宮崎県") ? "selected" : ""; ?>>宮崎県</option>
								<option value="鹿児島県" <?php echo ($userData["prefecture"] == "鹿児島県") ? "selected" : ""; ?>>鹿児島県</option>
								<option value="沖縄県" <?php echo ($userData["prefecture"] == "沖縄県") ? "selected" : ""; ?>>沖縄県</option>
							</select>
						</div>
						<div class="address">市区町村</br>
							<input type="text" name="city" value="<?=h($userData["city"])?>" class="userInfoUpdate" placeholder=""></br>
						</div>
						<div class="address">丁目・番地</br>
							<input type="text" name="houseNumber" pattern="[0-9a-zA-Z!@#$%^&*()_+-=]" value="<?=h($userData["houseNumber"])?>" class="userInfoUpdate" placeholder="半角で記入してください"></br>
							<span class="explain">※番地がない場合は「番地なし」と入力してください。</span>
						</div>
						<div class="address">ビル・マンション・部屋番号</br>
							<input type="text" name="apartment" value="<?=h($userData["apartment"])?>" class="userInfoUpdate" placeholder="マンション名を必ずご記入ください"></br>
						</div>
					</td>
				</tr>
				<tr>
					<td>電話番号<span class="option">任意</span></td>
					<td>
						<input type="text" name="telephone" pattern="[0-9０-９]*" maxlength="15" value="<?=h($userData["telephone"])?>" class="userInfoUpdate" placeholder="ハイフンなしで入力してください"></br>
						<span class="explain">※ハイフンなしの電話番号を入力してください。</span>
					</td>
				</tr>

			</table>
			<div id="btnContainer">
				<!-- <a href="user_info.php" id="returnBtn">戻る</a> -->
				<input type="submit" value="登録する" id="register">
			</div>
		</form>
	</div>
</div>

<script>
	// "passwordContainer"のチェックボックスの状態が変更されたときの処理
	$(document).ready(function() {
		$("#passwordContainer").hide();
		$("#changePassword").change(function() {
			if ($(this).is(":checked")) {
				$("#passwordContainer").show();
				$(".updatePassword").attr("required", "required")
				$("#whetherChange").val("1")
			} else {
				$("#passwordContainer").hide();
				$(".updatePassword").removeAttr("required", "required")
				$("#whetherChange").val("")
			}
		});
    });

	// "新しいパスワード"と"新しいパスワード（確認）"の値が一致していないとsubmitできないようにする
	$("form").submit(function() {
		if ($("#changePassword").is(":checked")) {
			let newPassword = $("#newLpw").val();
			let confirmNewPassword = $("#confirmNewLpw").val();
			if (newPassword !== confirmNewPassword) {
				alert("「新しいパスワード」と「新しいパスワード（確認）」が一致しません。");
				// 送信をキャンセルする
				return false;
			}
		}
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