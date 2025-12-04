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

    if (empty($diary_date) || empty($diary_content) || empty($selected_emotion_id)) {
        $error = "日付・内容・色の選択は必須です";
    } else {
        try {
            $color_stmt = $pdo->prepare("SELECT color_id FROM color_emotions_flat WHERE emotion_id = :emotion_id LIMIT 1");
            $color_stmt->execute([':emotion_id' => $selected_emotion_id]);
            $color_result = $color_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$color_result) {
                $error = "選択された感情に対応する色が見つかりません";
            } else {
                $color_id = $color_result['color_id'];
                $time_slot = '全日';

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
    $stmt = $pdo->query("SELECT emotion_id, feeling_text, color_name, color_code FROM color_emotions_flat ORDER BY id ASC");
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
/* 背景・フォント */
body {
    font-family: "Hiragino Sans","Helvetica Neue",sans-serif;
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #e0f7fa, #ffe0b2);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: background 0.5s ease;
}

/* カード風フォーム */
.card {
    background: rgba(255,255,255,0.95);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    padding: 40px 30px;
    max-width: 500px;
    width: 90%;
    text-align: center;
}

.card h1 {
    margin-bottom: 25px;
    color: #4a6fa5;
    font-size: 2em;
}

label {
    display: block;
    text-align: left;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

input[type="date"],
textarea,
select {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 2px solid #4a6fa5;
    margin-bottom: 20px;
    font-size: 1em;
    outline: none;
}

textarea { resize: vertical; min-height: 120px; }

button {
    width: 100%;
    padding: 14px 0;
    border: none;
    border-radius: 12px;
    background: #4a6fa5;
    color: #fff;
    font-weight: bold;
    font-size: 1.1em;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    transition: 0.2s;
}

button:hover {
    background: #3b5c90;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

.error {
    color: #d9534f;
    margin-bottom: 15px;
}

/* 色選択を少し見やすく */
select option {
    padding: 5px;
}
.link {
    margin-top: 20px;
    text-align: center;
}
.link a {
    text-decoration: none;
    color: #4a6fa5;
    font-weight: bold;
}
.link a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<div class="card">
    <h1>日記を記録</h1>

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
            <?php
            foreach ($color_emotions as $ce) {
                $id = htmlspecialchars($ce['emotion_id']);
                $feeling = htmlspecialchars($ce['feeling_text']);
                $color_code = htmlspecialchars($ce['color_code']);
                $emoji = '⬛';
                switch ($ce['color_name']) {
                    case '赤': $emoji = '🟥'; break;
                    case '青': $emoji = '🟦'; break;
                    case '黄': $emoji = '🟨'; break;
                    case 'オレンジ': $emoji = '🟧'; break;
                    case '緑': $emoji = '🟩'; break;
                    case '紫': $emoji = '🟪'; break;
                    case '白': $emoji = '⬜'; break;
                }
                echo "<option value=\"$id\" data-color=\"$color_code\">$emoji $feeling</option>";
            }
            ?>
        </select>

        <button type="submit">保存</button>
    </form>

    <div class="link">
        <a href="home.php">ホームに戻る</a>
    </div>
</div>

<script>
// 選択した色に応じて背景色を変更
const select = document.getElementById('diary_color_id');
const body = document.body;

select.addEventListener('change', () => {
    const selectedOption = select.options[select.selectedIndex];
    const color = selectedOption.getAttribute('data-color');
    if (color) {
        body.style.background = color + '33'; // 少し透明感をつける
    } else {
        body.style.background = 'linear-gradient(135deg, #e0f7fa, #ffe0b2)';
    }
});
</script>

</body>
</html>
