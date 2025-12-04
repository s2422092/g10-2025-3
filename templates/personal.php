<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// =========================================================
// 1. データベース接続
// =========================================================
$host = 'dpg-d4g18ebe5dus739hcjrg-a.singapore-postgres.render.com';
$port = 5432;
$dbname = 'g1020253';
$user = 'g1020253';
$password = 'C1d8rp3nKUp4Ajdh8NyHUTopXpooYIvA';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";


$db_available = false;
$diaryColors = [];

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Connected to Render PostgreSQL!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// =========================================================
// 2. カレンダーの日付計算ロジック
// =========================================================
date_default_timezone_set('Asia/Tokyo');

$year = isset($_GET['year']) ? (int)$_GET['year'] : date("Y");
$month = isset($_GET['month']) ? (int)$_GET['month'] : date("m");

$firstDayStr = sprintf("%04d-%02d-01", $year, $month);
$firstDayTime = strtotime($firstDayStr);
$daysInMonth = date("t", $firstDayTime);
$startWeekDay = date("w", $firstDayTime);

$prevMonth = date("m", mktime(0, 0, 0, $month - 1, 1, $year));
$prevYear = date("Y", mktime(0, 0, 0, $month - 1, 1, $year));
$nextMonth = date("m", mktime(0, 0, 0, $month + 1, 1, $year));
$nextYear = date("Y", mktime(0, 0, 0, $month + 1, 1, $year));

// =========================================================
// 3. データベースから「色」を取得（可能であれば）
// =========================================================
if ($db_available) {
    try {
        $sql = "
            SELECT DATE(d.created_at) as diary_date, c.color_name 
            FROM diaries d
            LEFT JOIN color_emotions_flat c ON d.color_id = c.color_id
            WHERE d.user_id = :user_id
            AND DATE(d.created_at) BETWEEN :start AND :end
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':start' => "$year-$month-01",
            ':end'   => "$year-$month-$daysInMonth"
        ]);

        foreach ($stmt as $row) {
            if ($row['diary_date']) {
                $diaryColors[$row['diary_date']] = $row['color_name'];
            }
        }
    } catch (PDOException $e) {
        // テーブルが存在しない場合などのエラーを無視
        $diaryColors = [];
    }
}

// 色名をCSSカラーコードに変換
$colorMap = [
    '赤' => '#ffb3b3',
    '青' => '#b3d9ff',
    '黄色' => '#ffffb3',
    '緑' => '#c2f0c2',
    'オレンジ' => '#ffd9b3',
    '紫' => '#e6b3ff',
    'ピンク' => '#ffcce6',
    '茶色' => '#e6ccb3',
    '灰色' => '#e0e0e0',
    '黒' => '#b3b3b3',
    '白' => '#ffffff'
];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイカレンダー</title>
    <style>
        body { font-family: "Hiragino Sans", sans-serif; background: #f9fafb; margin: 0; padding: 0; }
        header { background: #f9fafb; padding: 20px; text-align: center; }
        main { max-width: 700px; margin: 20px auto; background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 30px; }
        h1 { margin: 0 0 10px 0; font-size: 1.8em; color: #333; }

        .month-nav { display: flex; justify-content: center; align-items: center; gap: 20px; margin-bottom: 20px; font-size: 1.2em; }
        .month-nav a { background: none; border: none; color: #3b82f6; font-weight: bold; font-size: 1em; text-decoration: none; cursor: pointer; padding: 5px 10px; }
        .month-nav a:hover { text-decoration: underline; background: #eff6ff; border-radius: 4px; }

        .calendar { width: 100%; border-collapse: collapse; }
        .calendar th, .calendar td { border: 1px solid #ddd; width: 14.2%; height: 60px; text-align: center; }
        .calendar th { background: #f0f0f0; height: 40px; }
        
        .calendar td a { display: flex; justify-content: center; align-items: center; width: 100%; height: 100%; text-decoration: none; color: #333; font-weight: bold; }
        .calendar td a:hover { opacity: 0.6; }
        
        footer { text-align: center; margin-top: 40px; color: #777; font-size: 0.9em; }
        .footer-buttons { display: flex; justify-content: center; gap: 15px; margin-bottom: 15px; }
        .button { background: #3b82f6; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; }
        .button:hover { background: #2563eb; }
        
        .warning-message { 
            background: #fff3cd; 
            border: 1px solid #ffc107; 
            color: #856404;
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            font-size: 0.9em;
            text-align: center;
        }
    </style>
</head>
<body>

<header>
    <h1>マイカレンダー</h1>
    <div class="month-nav">
        <a href="?year=<?php echo $prevYear; ?>&month=<?php echo $prevMonth; ?>">＜ 前月</a>
        <span><?php echo $year; ?>年 <?php echo $month; ?>月</span>
        <a href="?year=<?php echo $nextYear; ?>&month=<?php echo $nextMonth; ?>">次月 ＞</a>
    </div>
</header>

<main>
    <?php if (!$db_available): ?>
    <div class="warning-message">
        データベースに接続できません。色の表示機能は利用できませんが、カレンダーは表示されます。
    </div>
    <?php endif; ?>

    <table class="calendar">
        <thead>
            <tr>
                <th style="color:red;">日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th style="color:blue;">土</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $dayCount = 1;
            $weekDay = 0;
            
            echo "<tr>";
            
            // 1. 月初の空白
            for ($i = 0; $i < $startWeekDay; $i++) {
                echo "<td></td>";
                $weekDay++;
            }
            
            // 2. 日付を生成
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = sprintf("%04d-%02d-%02d", $year, $month, $day);
                
                // 色判定
                $bgColor = "white";
                if (isset($diaryColors[$dateStr]) && isset($colorMap[$diaryColors[$dateStr]])) {
                    $bgColor = $colorMap[$diaryColors[$dateStr]];
                }
                
                echo '<td style="background-color: ' . $bgColor . ';">';
                echo '<a href="diary.php?date=' . $dateStr . '">' . $day . '</a>';
                echo '</td>';
                
                // 土曜日で改行
                if (++$weekDay % 7 === 0) {
                    echo "</tr><tr>";
                }
            }
            
            // 3. 月末の空白
            while ($weekDay % 7 !== 0) {
                echo "<td></td>";
                $weekDay++;
            }
            
            echo "</tr>";
            ?>
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 20px;">
        今日は <?php echo date('Y年n月j日'); ?> です
    </div>
</main>

<footer>
    <div class="footer-buttons">
        <a href="profile.php" class="button">個人ページへ</a>
        <a href="home.php" class="button">ホームへ</a>
    </div>
    <div class="footer-copy">
        &copy; 2025 一言×色日記 All rights reserved.
    </div>
</footer>

</body>
</html>