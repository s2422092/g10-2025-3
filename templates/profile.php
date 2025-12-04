<?php
session_start();

// ---------------------------------------------------------
// DB接続
// ---------------------------------------------------------
$host = 'dpg-d4g18ebe5dus739hcjrg-a.singapore-postgres.render.com';
$port = 5432;
$dbname = 'g1020253';
$user = 'g1020253';
$password = 'C1d8rp3nKUp4Ajdh8NyHUTopXpooYIvA';
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

$db_error = false;
$username = 'ゲストユーザー';
$email = '未設定';
$is_public = false;

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    $db_error = true;
    $error_message = "DB接続エラー: データベースに接続できません";
}

// ---------------------------------------------------------
// ログインチェック
// ---------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// ---------------------------------------------------------
// ユーザー情報取得
// ---------------------------------------------------------
if (!$db_error) {
    try {
        $stmt = $pdo->prepare("SELECT username, email, is_public FROM users WHERE user_id = :uid");
        $stmt->execute([':uid' => $user_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            $username = $userData['username'];
            $email = $userData['email'];
            $is_public = $userData['is_public'] ? true : false;
        } else {
            $username = 'ユーザーID: ' . $user_id;
            $email = '情報取得不可';
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'does not exist') !== false) {
            $db_error = true;
            $error_message = "usersテーブルがまだ作成されていません";
            $username = 'ユーザーID: ' . $user_id;
            $email = '（データベース準備中）';
        } else {
            $db_error = true;
            $error_message = "データ取得エラー: " . $e->getMessage();
        }
    }
}

// ---------------------------------------------------------
// 公開設定の切り替え処理
// ---------------------------------------------------------
if (!$db_error && isset($_POST['toggle_public'])) {
    // 現在の is_public の値を反転
    $new_status = !$is_public;

    // PDO::PARAM_BOOL を使って boolean として更新
    $stmt = $pdo->prepare("UPDATE users SET is_public = :status WHERE user_id = :uid");
    $stmt->bindValue(':status', $new_status, PDO::PARAM_BOOL);
    $stmt->bindValue(':uid', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // 更新後、画面に反映
    $is_public = $new_status;

    // リロードしてフォームの二重送信を防ぐ
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>マイページ - 個人ページ</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:"Hiragino Sans","Helvetica Neue",sans-serif; }

body {
    background: linear-gradient(135deg,#dbeafe,#fef2f2);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}

header {
    width: 100%;
    padding: 25px 20px;
    text-align: center;
    background: transparent;
}
header h1 {
    font-size: 2em;
    color: #1e40af;
    font-weight: bold;
}

main {
    width: 90%;
    max-width: 500px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    padding: 40px 30px;
    margin: 30px 0;
    text-align: center;
}

.profile-icon {
    font-size: 5em;
    margin-bottom: 25px;
    display: inline-block;
    background: #e0e7ff;
    border-radius: 50%;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.info-group {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
    text-align: left;
}
.label {
    font-size: 0.9em;
    color: #6b7280;
    font-weight: bold;
    margin-bottom: 5px;
    display: block;
}
.value {
    font-size: 1.2em;
    color: #111827;
    font-weight: 600;
}

.btn-container {
    margin-top: 35px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.button {
    display: block;
    width: 100%;
    text-align: center;
    font-size: 1em;
    padding: 12px 0;
    border-radius: 10px;
    text-decoration: none;
    transition: 0.25s ease;
    font-weight: 600;
}
.button.main {
    background: #3b82f6;
    color: #fff;
}
.button.main:hover { background: #2563eb; }
.button.outline {
    background: #fff;
    color: #3b82f6;
    border: 2px solid #3b82f6;
}
.button.outline:hover { background: #eff6ff; }
.button.logout {
    background: #ef4444;
    color: #fff;
    border: none;
}
.button.logout:hover { background: #dc2626; }

.warning-message {
    background: #fff4e5;
    border: 1px solid #ffcc80;
    color: #7c4d00;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 0.95em;
    text-align: left;
}

footer {
    margin-bottom: 20px;
    color: #6b7280;
    font-size: 0.85em;
    text-align: center;
}

@media (max-width: 480px) {
    main { padding: 30px 20px; }
    .profile-icon { font-size: 4em; padding: 15px; }
    .button { font-size: 0.95em; padding: 10px 0; }
}
</style>
</head>
<body>

<header>
    <h1>マイページ</h1>
</header>

<main>
    <?php if ($db_error): ?>
    <div class="warning-message">
        <strong>お知らせ:</strong> <?php echo htmlspecialchars($error_message); ?><br>
        一部の情報が表示できない可能性があります。
    </div>
    <?php endif; ?>

    <div class="profile-icon">👤</div>

    <div class="info-group">
        <span class="label">ユーザー名</span>
        <span class="value"><?php echo htmlspecialchars($username); ?></span>
    </div>

    <div class="info-group">
        <span class="label">メールアドレス</span>
        <span class="value"><?php echo htmlspecialchars($email); ?></span>
    </div>

    <!-- 公開設定 -->
    <div class="info-group">
        <span class="label">公開設定</span>
        <span class="value">
            <?php echo $is_public ? '現在: 公開中 🔓' : '現在: 非公開 🔒'; ?>
        </span>
        <form method="POST" style="margin-top:10px;">
            <input type="hidden" name="toggle_public" value="1">
            <button type="submit" class="button outline">
                <?php echo $is_public ? '🔓 公開中 → 非公開にする' : '🔒 非公開 → 公開にする'; ?>
            </button>
        </form>
    </div>

    <div class="btn-container">
        <a href="home.php" class="button main">🏠 ホームへ戻る</a>
        <a href="personal.php" class="button outline">📅 個人日記へ</a>
        <a href="logout.php" class="button logout">ログアウト</a>
    </div>

</main>

<footer>
    &copy; 2025 一言×色日記 All rights reserved.
</footer>

</body>
</html>
