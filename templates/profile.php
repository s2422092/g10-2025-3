<?php
// =========================================================
// 1. データベース接続
// =========================================================
session_start(); // セッション開始

$host = 'localhost';
$dbname = 'mi11yu17';
$user = 'mi11yu17';
$password = '5SQuEDtU';

// デフォルト値（DBが使えない場合用）
$username = 'ゲストユーザー';
$email = '未設定';
$db_error = false;

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $db_error = true;
    $error_message = "DB接続エラー: データベースに接続できません";
}

// =========================================================
// 2. ユーザー情報の取得
// =========================================================
// ログインチェック
if (!isset($_SESSION['user_id'])) {
    // ログインしていなければログイン画面へ強制移動
    header("Location: login.php");
    exit();
}

// ログイン中のユーザーIDを取得
$user_id = $_SESSION['user_id'];

// データベースが使える場合のみユーザー情報を取得
if (!$db_error) {
    try {
        // ユーザー名とメールアドレスを取得（カラム名をuser_idに修正）
        $sql = "SELECT username, email FROM users WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // ユーザーが見つかった場合
        if ($userData) {
            $username = $userData['username'];
            $email = $userData['email'];
        } else {
            $username = 'ユーザーID: ' . $user_id;
            $email = '情報取得不可';
        }
    } catch (PDOException $e) {
        // テーブルが存在しない場合などのエラー
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
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ - 個人ページ</title>
    <style>
        body { font-family: "Hiragino Sans", sans-serif; background: #f9fafb; margin: 0; padding: 0; }
        header { background: #f9fafb; padding: 20px; text-align: center; }
        
        main { 
            max-width: 600px; 
            margin: 40px auto; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
            padding: 40px; 
            text-align: center;
        }

        h1 { margin: 0 0 30px 0; font-size: 1.8em; color: #333; }
        
        .profile-icon { font-size: 4em; margin-bottom: 20px; display: block; }
        
        /* 情報表示のデザイン */
        .info-group { margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px; text-align: left; }
        .label { font-size: 0.9em; color: #777; display: block; margin-bottom: 8px; font-weight: bold; }
        .value { font-size: 1.2em; color: #333; font-weight: bold; }

        /* ボタン */
        .btn-container { margin-top: 40px; }
        
        .button { 
            display: inline-block; 
            background: #3b82f6; 
            color: white; 
            padding: 12px 24px; 
            border-radius: 8px; 
            text-decoration: none; 
            margin: 5px;
            font-size: 1em;
            cursor: pointer;
        }
        .button:hover { background: #2563eb; }
        
        .button.outline { background: white; color: #3b82f6; border: 1px solid #3b82f6; }
        .button.outline:hover { background: #eff6ff; }

        .button.logout { background: #ef4444; color: white; border: none; }
        .button.logout:hover { background: #dc2626; }
        
        footer { margin-top: 40px; color: #777; font-size: 0.9em; text-align: center; }
        
        /* エラーメッセージ */
        .warning-message { 
            background: #fff3cd; 
            border: 1px solid #ffc107; 
            color: #856404;
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            font-size: 0.9em;
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

    <div class="btn-container">
        <a href="personal.php" class="button outline">📅 ホームへ戻る</a>
        
        <a href="logout.php" class="button logout">ログアウト</a>
    </div>
</main>

<footer>
    &copy; 2025 一言×色日記 All rights reserved.
</footer>

</body>
</html>