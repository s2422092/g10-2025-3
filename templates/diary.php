<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// DB接続設定
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
} catch (PDOException $e) {
    die("DB接続エラー: " . $e->getMessage());
}

// POST送信時の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diary_date = $_POST['diary_date'] ?? '';
    $diary_content = $_POST['diary_content'] ?? '';
    $selected_emotion_id = $_POST['diary_color_id'] ?? null;

    // バリデーション
    if (empty($diary_date) || empty($diary_content) || empty($selected_emotion_id)) {
        $error = "日付・内容・色の選択は必須です";
    } else {
        try {
            // color_emotions_flatテーブルからcolor_idを取得
            $color_stmt = $pdo->prepare("SELECT color_id FROM color_emotions_flat WHERE emotion_id = :emotion_id LIMIT 1");
            $color_stmt->execute([':emotion_id' => $selected_emotion_id]);
            $color_result = $color_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$color_result) {
                $error = "選択された感情に対応する色が見つかりません";
            } else {
                $color_id = $color_result['color_id'];
                $time_slot = '全日';

                // diariesテーブルに挿入
                $stmt = $pdo->prepare("
                    INSERT INTO diaries (content, user_id, color_id, time_slot, created_at) 
                    VALUES (:content, :user_id, :color_id, :time_slot, :created_at)
                ");
                $stmt->execute([
                    ':content' => $diary_content,
                    ':user_id' => $user_id,
                    ':color_id' => $color_id,
                    ':time_slot' => $time_slot,
                    ':created_at' => $diary_date . ' 00:00:00'
                ]);

                // 保存成功後リダイレクト
                header('Location: home.php?success=1');
                exit;
            }
        } catch (PDOException $e) {
            $error = "保存エラー: " . $e->getMessage();
        }
    }
}

// GETリクエスト時またはエラー時：色と感情の取得
try {
    $stmt = $pdo->query("SELECT emotion_id, feeling_text, color_name FROM color_emotions_flat ORDER BY id ASC");
    $color_emotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("色・感情の取得エラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日記の作成</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .header {
            text-align: center;
            margin: 20px 0;
            padding: 15px 30px;
            border-radius: 10px;
            background-color: #fff;
            border: 2px solid #333;
            box-shadow: 3px 3px 0 #333;
        }
        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 3px 3px 0 #333;
            max-width: 600px;
            width: 90%;
        }
        h1 {
            color: #4a6fa5;
            margin-bottom: 20px;
            text-align: center;
        }
        label, select, textarea, input {
            display: block;
            width: 100%;
            margin-bottom: 15px;
            font-size: 1.1em;
        }
        textarea {
            resize: vertical;
        }
        select, input[type="date"] {
            padding: 8px;
            border-radius: 5px;
            border: 2px solid #333;
        }
        button {
            display: block;
            width: 100%;
            padding: 15px;
            border: 2px solid #333;
            border-radius: 8px;
            background: #fff;
            color: #333;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 3px 3px 0 #333;
            transition: all 0.1s ease-in-out;
        }
        button:hover {
            background: #f0f0f0;
            transform: translate(1px, 1px);
            box-shadow: 2px 2px 0 #333;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
        .link {
            margin-top: 15px;
            text-align: center;
        }
        .link a {
            color: #4a6fa5;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header"><h1>日記を記録</h1></div>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="diary-date">日付選択:</label>
        <input type="date" id="diary-date" name="diary_date" required>

        <label for="diary-content">内容の記載:</label>
        <textarea id="diary-content" name="diary_content" rows="8" required></textarea>

        <label for="diary_color_id">色と感情を選択:</label>
        <select name="diary_color_id" id="diary_color_id" required>
            <option value="">選択してください</option>
            <?php foreach ($color_emotions as $ce): ?>
                <option value="<?= htmlspecialchars($ce['emotion_id']) ?>">
                    <?= htmlspecialchars($ce['color_name'] . ' - ' . $ce['feeling_text']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">保存</button>
    </form>

    <div class="link">
        <a href="home.php">ホームに戻る</a>
    </div>
</body>
</html>
