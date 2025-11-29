<?php
session_start();

// セッション変数をすべてクリア
$_SESSION = array();

// セッションクッキーも削除
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// セッションを破棄
session_destroy();

// ログイン画面にリダイレクト
header('Location: login.php');
exit;
?>