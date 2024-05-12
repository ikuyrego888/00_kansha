<?php
session_start();

// ログインエラーで戻ってきた場合のアラート
if (isset($_SESSION["loginError"]) && $_SESSION["loginError"] == 1) {
    echo '<script>alert("ログインできませんでした。IDとパスワードに誤りがないか確認してください。");</script>';
	// エラーを表示したらセッションを削除する
    unset($_SESSION["loginError"]);
}

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <link href="css/login.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <!-- <link rel="stylesheet" href="css/main.css" /> -->
  <title>ログイン・新規登録</title>
</head>
<body>

<header>
	<div id="headerTitle"></div>
</header>

<!-- アプリ名の表示 -->
<div id="appliTitle">夫婦感謝アプリ</div>
<div id="appliSubtitle">- Let's express gratitude to your partner -</div>
<!-- <div id="logo"> -->
    <!-- <img src="imgs/appliLogo.png" alt="" id="appliLogo"> -->
<!-- </div> -->

<!-- ログインフォーム -->
<div id="tab">
    <!-- 以下4行で「ログイン」「新規登録」の切り替えを設定 -->
    <input type="radio" id="loginTab" name="tabItem" checked>
    <label for="loginTab" id="tabItem">ログイン</label>
    <input type="radio" id="registerTab" name="tabItem">
    <label for="registerTab" id="tabItem">新規登録</label>

    <!-- ログイン画面 -->
    <div id="loginContainer" class="tabContainer">
        <form name="form1" action="loginAct.php" method="post">
        <div id="loginIdDiv">ユーザーID</div>
        <input type="text" name="lid" class="inputTag" placeholder="ID（メールアドレス）を入力">
        <div id="loginPassDiv">パスワード</div>
        <input type="password" name="lpw" class="inputTag" placeholder="パスワードを入力"></br>
        <input type="submit" value="ログインする" id="loginDone">
        </form>
    </div>

    <!-- 新規登録画面 -->
    <div id="registerContainer" class="tabContainer">
        <form name="form2" action="user_register.php" method="post">
        <div id="loginIdDiv">新規ユーザーID</div>
        <input type="text" name="newLid" class="inputTag" placeholder="メールアドレスを入力" required>
        <div id="loginPassDiv">新規パスワード</div>
        <input type="password" name="newLpw" pattern=".{4,}" class="inputTag" placeholder="パスワードを入力（英数含む8文字以上）" required></br>
        <input type="submit" value="ユーザー登録画面へ" id="newRegister">
        </form>
    </div>
</div>

<!-- モーダル用の背景 -->
<div id="modalBackground"></div>

<div id="dialog">
	<p>ユーザー登録が完了しました。</p>
    <p>登録したIDとパスワードを入力して</br>ログインしてください。</p>
</div>

<script>
    <?php if (isset($_SESSION["insertSuccess"]) && $_SESSION["insertSuccess"] == 1) {?>
		$('#modalBackground, #dialog').fadeIn();
		<?php unset($_SESSION["insertSuccess"]);?>

		$('#modalBackground').on("click", function() {
			$('#dialog').fadeOut();
			$('#modalBackground').fadeOut();
		});
	<?php } ?>

</script>

</body>
</html>