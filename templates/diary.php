<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// データベース接続
$host = 'localhost';
$dbname = 'mi11yu17';
$user = 'mi11yu17';
$password = '5SQuEDtU';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('データベース接続エラー: ' . $e->getMessage());
}

// POSTデータを取得
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diary_date = $_POST['diary_date'] ?? '';
    $diary_content = $_POST['diary_content'] ?? '';
    $selected_emotion_id = $_POST['diary_color_id'] ?? null; // フォームから感情IDを取得
    $user_id = $_SESSION['user_id'];
    
    // バリデーション
    if (empty($diary_date) || empty($diary_content) || empty($selected_emotion_id)) {
        die('日付、内容、色の選択は必須です');
    }
    
    // color_emotions_flatテーブルから対応するcolor_idを取得
    try {
        $color_sql = "SELECT color_id FROM color_emotions_flat WHERE emotion_id = :emotion_id LIMIT 1";
        $color_stmt = $pdo->prepare($color_sql);
        $color_stmt->execute([':emotion_id' => $selected_emotion_id]);
        $color_result = $color_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$color_result) {
            die('選択された感情に対応する色が見つかりません');
        }
        
        $color_id = $color_result['color_id'];
        
    } catch (PDOException $e) {
        die('色情報の取得エラー: ' . $e->getMessage());
    }
    
    // time_slotを設定（日付のみなので全日とする）
    $time_slot = '全日';
    
    // データベースに挿入
    try {
        $sql = "INSERT INTO diaries (content, user_id, color_id, time_slot, created_at) 
                VALUES (:content, :user_id, :color_id, :time_slot, :created_at)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':content' => $diary_content,
            ':user_id' => $user_id,
            ':color_id' => $color_id, // color_emotions_flatから取得したcolor_id
            ':time_slot' => $time_slot,
            ':created_at' => $diary_date . ' 00:00:00'
        ]);
        
        // 成功したらリダイレクト
        header('Location: home.php?success=1');
        exit;
        
    } catch (PDOException $e) {
        die('保存エラー: ' . $e->getMessage());
    }
} else {
    // GETリクエストの場合はフォームを表示
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>日記の内容を記載</title>
        <style>
            select[name="diary_color_id"] {
                font-size: 16px;
                padding: 5px;
            }
            option {
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <h1>日記の内容を記載</h1>
        <form action="diary_save.php" method="POST">
            <p>日付選択: <input type="date" id="diary-date" name="diary_date" required></p>
            <p>内容の記載:</p>
            <textarea id="diary-content" rows="10" cols="100" name="diary_content" required></textarea>
            <br>色の選択:</br>
            <select name="diary_color_id" required>
                <option value="">選択してください</option>
                <option value="1">🟥 怒り</option>
                <option value="2">🟦 悲しみ</option>
                <option value="3">🟨 喜び</option>
                <option value="4">🟧 楽しい</option>
                <option value="5">🟩 安らぎ</option>
                <option value="6">🟪 愛</option>
                <option value="7">⬜ 不安</option>
                <option value="8">🟪 寂しさ</option>
                <option value="9">🟨 自信</option>
            </select>
            <a href="color.php"><p>色の見本はこちら</p></a>
            <br>
            <button type="submit">保存</button>
        </form>
    </body>
    </html>
    <?php
}
?>