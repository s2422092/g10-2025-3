<?php
// =========================================================
// 1. データベース接続
// =========================================================
$host = 'localhost';
$dbname = 'mi11yu17'; // あなたのDB名
$user = 'mi11yu17';   // あなたのユーザー名
$password = '5SQuEDtU'; // あなたのパスワード

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB接続エラー: " . $e->getMessage());
}

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    // ログインしていなければログイン画面へ強制移動
    header("Location: login.php");
    exit();
}
// =========================================================
// 2. カレンダーの日付計算ロジック
// =========================================================
// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// URLパラメータから年・月を取得（なければ現在の年月）
$year = isset($_GET['year']) ? (int)$_GET['year'] : date("Y");
$month = isset($_GET['month']) ? (int)$_GET['month'] : date("m");

// 月初・月末・曜日を計算
$firstDayStr = sprintf("%04d-%02d-01", $year, $month);
$firstDayTime = strtotime($firstDayStr);
$daysInMonth = date("t", $firstDayTime); // その月の日数
$startWeekDay = date("w", $firstDayTime); // 1日の曜日 (0:日, 1:月...)

// 前月・次月のリンク用
$prevMonth = date("m", mktime(0, 0, 0, $month - 1, 1, $year));
$prevYear = date("Y", mktime(0, 0, 0, $month - 1, 1, $year));
$nextMonth = date("m", mktime(0, 0, 0, $month + 1, 1, $year));
$nextYear = date("Y", mktime(0, 0, 0, $month + 1, 1, $year));

// =========================================================
// 3. データベースから「色」を取得
// =========================================================
// 指定した月の日記データを取得し、日付と色名を紐づける
$sql = "
    SELECT d.diary_date, c.color_name 
    FROM diaries d
    LEFT JOIN color_emotions_flat c ON d.color_id = c.color_id
    WHERE d.diary_date BETWEEN :start AND :end
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':start' => "$year-$month-01",
    ':end'   => "$year-$month-$daysInMonth"
]);

// データを配列に整理: ['2025-01-01' => '赤', '2025-01-02' => '青' ...]
$diaryColors = [];
foreach ($stmt as $row) {
    if ($row['diary_date']) {
        $diaryColors[$row['diary_date']] = $row['color_name'];
    }
}

// 色名(日本語)をCSSカラーコードに変換するマップ
$colorMap = [
    '赤' => '#ffb3b3',       // 薄い赤
    '青' => '#b3d9ff',       // 薄い青
    '黄色' => '#ffffb3',     // 薄い黄色
    '緑' => '#c2f0c2',       // 薄い緑
    'オレンジ' => '#ffd9b3', // 薄いオレンジ
    '紫' => '#e6b3ff',       // 薄い紫
    'ピンク' => '#ffcce6',   // 薄いピンク
    '茶色' => '#e6ccb3',     // 薄い茶色
    '灰色' => '#e0e0e0',     // 薄いグレー
    '黒' => '#b3b3b3',       // 薄い黒（文字が見えるように）
    '白' => '#ffffff'
];

$db_message = "データベース接続中: " . $dbname;
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>個人ページ</title>
    <style>
        body {
            font-family: "Hiragino Sans", "Helvetica Neue", sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #f9fafb;
            color: #333;
            padding: 20px;
            text-align: center;
        }
        main {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            margin-top: 0;
            font-size: 2em;
            color: #333;
        }
        /* 月移動のリンクスタイル */
        .month-nav {
            margin-bottom: 10px;
            font-size: 1.2rem;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .month-nav a {
            text-decoration: none;
            color: #3b82f6;
            font-weight: bold;
        }
        .calendar {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        .calendar th, .calendar td {
            border: 1px solid #ddd;
            width: 14.2%;
            height: 60px; /* マスの高さを少し広げました */
            text-align: center;
        }
        .calendar th {
            background-color: #f0f0f0;
        }
        /* マス全体をクリック可能にする設定 */
        .calendar td a {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .calendar td a:hover {
            opacity: 0.7;
        }
        .date {
            font-size: 1.1em;
            margin: 10px 0 30px;
            color: #666;
            text-align: center;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }
        .button:hover {
            background-color: #2563eb;
        }
        footer {
            text-align: center;
            margin-top: 50px;
            font-size: 0.9em;
            color: #777;
        }
        .footer-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .db-status {
            text-align: center;
            margin-top: 30px;
            font-size: 0.8em;
            color: #888;
        }
    </style>
</head>
<body>

<header>
    <h1>マイカレンダー</h1>
    
    <div class="month-nav">
        <a href="?year=<?= $prevYear ?>&month=<?= $prevMonth ?>">＜ 前月</a>
        <span><?= $year ?>年 <?= $month ?>月</span>
        <a href="?year=<?= $nextYear ?>&month=<?= $nextMonth ?>">次月 ＞</a>
    </div>
</header>

<main>
    <table class="calendar">
        <tr>
            <th style="color:red;">日</th>
            <th>月</th>
            <th>火</th>
            <th>水</th>
            <th>木</th>
            <th>金</th>
            <th style="color:blue;">土</th>
        </tr>
        <tr>
        <?php
        // ---------------------------------------------
        // カレンダー生成ループ
        // ---------------------------------------------
        
        // 1. 月初めの空白セル
        for ($i = 0; $i < $startWeekDay; $i++) {
            echo "<td></td>";
        }

        $weekDay = $startWeekDay;

        // 2. 日付セルを作成
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf("%04d-%02d-%02d", $year, $month, $day);
            
            // 色の決定
            $bgColor = '#ffffff'; // デフォルトは白
            
            // DBにその日の日記があれば、色を取得してセット
            if (isset($diaryColors[$dateStr])) {
                $colorName = $diaryColors[$dateStr]; // 例: '赤'
                if (isset($colorMap[$colorName])) {
                    $bgColor = $colorMap[$colorName]; // 例: '#ffb3b3'
                }
            }

            // HTML出力 (背景色を設定し、リンク先をedit.phpに設定)
            echo "<td style='background-color: {$bgColor};'>";
            echo "<a href='edit.php?date={$dateStr}'>{$day}</a>";
            echo "</td>";

            // 土曜日で改行
            if (++$weekDay % 7 == 0) {
                echo "</tr><tr>";
            }
        }

        // 3. 月末の空白セル埋め（レイアウト崩れ防止）
        while ($weekDay % 7 != 0) {
            echo "<td></td>";
            $weekDay++;
        }
        ?>
        </tr>
    </table>

    <p></p>
    <div class="date">今日は <?= date("Y年m月d日") ?> です</div>

    <div class="db-status">
        <hr>
        <p><?= $db_message ?></p>
    </div>
</main>

<footer>
    <div class="footer-buttons">
        <a href="personal.php" class="button">個人ページへ</a>
        <a href="edit.php?date=<?= date('Y-m-d') ?>" class="button">編集</a>
    </div>

    <div class="footer-copy">
        &copy; <?= date("Y") ?> 一言×色日記 All rights reserved.
    </div>
</footer>

</body>
</html>