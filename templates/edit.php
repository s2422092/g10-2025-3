<?php
// =========================================================
// 1. データベース接続
// =========================================================
session_start();
$host = 'localhost';
$dbname = 'mi11yu17';
$user = 'mi11yu17';
$password = '5SQuEDtU';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB接続エラー: " . $e->getMessage());
}

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    // ログインしていなければログイン画面へ強制移動
    header("Location: login.html");
    exit();
}

// ログイン中のユーザーIDを取得
$user_id = $_SESSION['user_id'];

// URLから日付を取得（なければ今日の日付）
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$message = "";

// =========================================================
// 2. 保存ボタンが押されたときの処理
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'];
    $color_id = $_POST['color_id'];
    $target_date = $_POST['diary_date'];

    // まず、その日の日記がすでにあるかチェック
    $checkSql = "SELECT diary_id FROM diaries WHERE user_id = :uid AND diary_date = :date";
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute([':uid' => $user_id, ':date' => $target_date]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // --- データがある場合：更新 (UPDATE) ---
        $sql = "UPDATE diaries SET content = :content, color_id = :color_id WHERE diary_id = :diary_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':content', $content);
        $stmt->bindValue(':color_id', $color_id, PDO::PARAM_INT);
        $stmt->bindValue(':diary_id', $existing['diary_id'], PDO::PARAM_INT);
        if ($stmt->execute()) {
            $message = "日記を更新しました！";
        }
    } else {
        // --- データがない場合：新規作成 (INSERT) ---
        $sql = "INSERT INTO diaries (user_id, diary_date, content, color_id) VALUES (:uid, :date, :content, :color_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':uid', $user_id);
        $stmt->bindValue(':date', $target_date);
        $stmt->bindValue(':content', $content);
        $stmt->bindValue(':color_id', $color_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $message = "新しい日記を保存しました！";
        }
    }
}

// =========================================================
// 3. 画面表示用データの取得
// =========================================================

// ① 色のリストをDBから取得
$colorStmt = $pdo->query("SELECT * FROM color_emotions_flat ORDER BY color_id ASC");
$colors = $colorStmt->fetchAll(PDO::FETCH_ASSOC);

// ② すでに書かれた日記があれば取得（編集用）
$diarySql = "SELECT * FROM diaries WHERE user_id = :uid AND diary_date = :date";
$stmt = $pdo->prepare($diarySql);
$stmt->execute([':uid' => $user_id, ':date' => $date]);
$currentDiary = $stmt->fetch(PDO::FETCH_ASSOC);

// フォームの初期値（日記があればその中身、なければ空）
$contentValue = $currentDiary ? $currentDiary['content'] : "";
$colorValue = $currentDiary ? $currentDiary['color_id'] : 1; // デフォルトはID:1

// 表示用のカラーマップ（カレンダーと同じ色味）
$cssColorMap = [
    '赤' => '#ffb3b3', '青' => '#b3d9ff', '黄色' => '#ffffb3', 
    '緑' => '#c2f0c2', 'オレンジ' => '#ffd9b3', '紫' => '#e6b3ff', 
    'ピンク' => '#ffcce6', '茶色' => '#e6ccb3', '灰色' => '#e0e0e0', 
    '黒' => '#b3b3b3', '白' => '#ffffff'
];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日記の編集</title>
    <style>
        body { font-family: "Hiragino Sans", sans-serif; background: #f9fafb; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; font-size: 1.5em; color: #333; text-align: center; }
        .date-display { text-align: center; font-size: 1.2em; font-weight: bold; margin-bottom: 20px; color: #555; }
        
        /* フォーム要素 */
        label { display: block; margin-bottom: 10px; font-weight: bold; color: #555; }
        textarea { width: 100%; height: 150px; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 1em; box-sizing: border-box; resize: vertical; font-family: inherit; }
        
        /* 色選択のデザイン（丸いボタン） */
        .color-picker { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .color-option { position: relative; cursor: pointer; }
        .color-option input { position: absolute; opacity: 0; cursor: pointer; }
        
        .color-circle { 
            width: 40px; height: 40px; border-radius: 50%; display: inline-block; 
            border: 3px solid transparent; transition: 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        /* 選択されたとき */
        .color-option input:checked + .color-circle {
            border-color: #333; transform: scale(1.1);
        }
        .color-label { display: block; text-align: center; font-size: 0.8em; color: #666; margin-top: 4px; }

        /* メッセージ */
        .success-msg { background: #dcfce7; color: #166534; padding: 10px; text-align: center; border-radius: 8px; margin-bottom: 20px; }

        /* ボタン */
        .btn-group { display: flex; justify-content: space-between; margin-top: 30px; }
        .btn { padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: bold; border: none; cursor: pointer; font-size: 1em; }
        
        .btn-save { background: #3b82f6; color: white; }
        .btn-save:hover { background: #2563eb; }
        
        .btn-back { background: white; color: #555; border: 1px solid #ccc; }
        .btn-back:hover { background: #f0f0f0; }
    </style>
</head>
<body>

<div class="container">
    <?php if ($message): ?>
        <div class="success-msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <h1>日記を書く</h1>
    <div class="date-display"><?= date('Y年n月j日', strtotime($date)) ?></div>

    <form action="edit.php?date=<?= $date ?>" method="POST">
        <input type="hidden" name="diary_date" value="<?= $date ?>">

        <label>今日の気分（色）を選んでください</label>
        <div class="color-picker">
            <?php foreach ($colors as $color): ?>
                <?php 
                    // DBの色名からCSSカラーを取得（なければグレー）
                    $bgColor = isset($cssColorMap[$color['color_name']]) ? $cssColorMap[$color['color_name']] : '#ccc';
                    // 以前選んだ色があればチェックを入れる
                    $checked = ($color['color_id'] == $colorValue) ? 'checked' : '';
                ?>
                <label class="color-option">
                    <input type="radio" name="color_id" value="<?= $color['color_id'] ?>" <?= $checked ?>>
                    <span class="color-circle" style="background-color: <?= $bgColor ?>;"></span>
                    <span class="color-label"><?= htmlspecialchars($color['color_name']) ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <label>今日の一言</label>
        <textarea name="content" placeholder="今日はどんな一日でしたか？"><?= htmlspecialchars($contentValue) ?></textarea>

        <div class="btn-group">
            <a href="personal.php" class="btn btn-back">カレンダーに戻る</a>
            <button type="submit" class="btn btn-save">保存する</button>
        </div>
    </form>
</div>

</body>
</html>