<?php
// ------------------------------
// 1. セッションの開始
// ------------------------------
// セッションを利用するすべてのページ（ファイル）の先頭で実行します。
session_start();

// 動作確認済み //

// ------------------------------
// DB接続情報（.env を使わず直書き）
// ------------------------------
$host = 'dpg-d4g18ebe5dus739hcjrg-a.singapore-postgres.render.com';
$port = 5432;
$dbname = 'g1020253';
$user = 'g1020253';
$password = 'C1d8rp3nKUp4Ajdh8NyHUTopXpooYIvA';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Connected to Render PostgreSQL!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// ------------------------------
// POST送信時の処理
// ------------------------------
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        // ユーザー検索
        $sql = "SELECT user_id, username, password FROM users WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // ログイン成功
            // session_start() は既にファイルの先頭で実行済みです。
            
            // ユーザー情報をセッションに格納
            $_SESSION['user_id'] = $user['user_id']; // ★ 'user_id' キーを使用
            $_SESSION['username'] = $user['username'];
            
            // リダイレクト
            header("Location: home.php");
            exit;
        } else {
            $error = "ユーザー名またはパスワードが正しくありません。";
        }
    } catch (PDOException $e) {
        $error = "ログインエラー: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>一言×色日記 - ログイン</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }
        
        h1 {
            color: #4a6fa5;
            margin-bottom: 30px;
            text-align: center;
        }
        
        form {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 320px;
        }
    </style>
</head>

<body>
    <h1>ログイン画面</h1>
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>ユーザー名：
            <input type="text" name="username" required>
        </label>
        <br><br>
        <label>パスワード：
            <input type="password" name="password" required>
        </label>
        <br><br>
        <button type="submit">ログイン</button>
    </form>
    <p><a href="index.php">トップページへ</a></p>
    <p><a href="signup.php">新規登録ページへ</a></p>
</body>
</html>