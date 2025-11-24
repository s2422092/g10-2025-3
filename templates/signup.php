<?php
// ------------------------------
// DB接続情報（.env を使わず直書き）
// ------------------------------
$host = 'localhost';
$dbname = 'mi11yu17';
$user = 'mi11yu17';
$password = '5SQuEDtU';
// ------------------------------
// DB接続
// ------------------------------
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB接続エラー: " . $e->getMessage());
}
// ------------------------------
// POST送信時の処理
// ------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password_raw = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    // パスワード一致チェック
    if ($password_raw !== $confirm_password) {
        $error = "パスワードが一致しません。";
    } else {
        // パスワードハッシュ化
        $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
        try {
            // INSERT文
            $sql = "INSERT INTO users (username, password, email)
                    VALUES (:username, :password, :email)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $success = "新規登録が完了しました！ログイン画面へお進みください。";
        } catch (PDOException $e) {
            // UNIQUE制約やその他エラーを表示
            $error = "登録エラー: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規登録</title>
</head>
<body>
<h1>新規登録</h1>
<?php if (!empty($error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <p style="color:green;"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<form method="POST">
    <label>メールアドレス：
        <input type="email" name="email" required>
    </label>
    <br><br>
    <label>ユーザー名：
        <input type="text" name="username" required>
    </label>
    <br><br>
    <label>パスワード：
        <input type="password" name="password" required>
    </label>
    <br><br>
    <label>パスワード（確認）：
        <input type="password" name="confirm_password" required>
    </label>
    <br><br>
    <button type="submit">新規登録</button>
</form>
</body>
</html>




