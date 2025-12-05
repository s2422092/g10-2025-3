<?php

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>一言×色日記 - 新規登録</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Hiragino Sans", "Helvetica Neue", sans-serif;
            background: linear-gradient(135deg, #8fbaff, #ffd7e7);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            animation: bgFade 2s ease;
            color: #333;
        }

        @keyframes bgFade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        h1 {
            font-size: 2.4em;
            color: #4a6fa5;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            max-width: 450px;
            width: 90%;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            padding: 45px 35px;
            border-radius: 18px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            animation: floatUp 1.4s ease;
            text-align: center;
        }

        @keyframes floatUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        label {
            display: block;
            font-size: 1em;
            margin-bottom: 15px;
            color: #555;
            text-align: left;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 1em;
            box-sizing: border-box;
            outline: none;
            transition: border 0.25s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #4d8df5;
            box-shadow: 0 0 6px rgba(77,141,245,0.3);
        }

        button {
            margin-top: 20px;
            padding: 14px 32px;
            font-size: 1.1em;
            border: none;
            border-radius: 12px;
            background: #4d8df5;
            color: white;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
            transition: 0.25s ease;
        }

        button:hover {
            background: #2f6de0;
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 12px 24px rgba(0,0,0,0.22);
        }

        p {
            margin-top: 15px;
            font-size: 0.95em;
        }

        a {
            color: #4d8df5;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .error-msg {
            color: red;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .success-msg {
            color: green;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>新規登録</h1>

    <?php if (!empty($error)): ?>
        <p class="error-msg"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p class="success-msg"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>
            メールアドレス：
            <input type="email" name="email" required>
        </label>

        <label>
            ユーザー名：
            <input type="text" name="username" required>
        </label>

        <label>
            パスワード：
            <input type="password" name="password" required>
        </label>

        <label>
            パスワード（確認）：
            <input type="password" name="confirm_password" required>
        </label>

        <button type="submit">新規登録</button>
    </form>

    <p><a href="index.php">トップページへ</a></p>
    <p><a href="login.php">ログイン画面へ</a></p>
</body>

</html>




