<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// DB接続
$host = 'dpg-d4g18ebe5dus739hcjrg-a.singapore-postgres.render.com';
$port = 5432;
$dbname = 'g1020253';
$user = 'g1020253';
$password = 'C1d8rp3nKUp4Ajdh8NyHUTopXpooYIvA';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
$diaryColors = [];
$diaryData = [];
$diaryColorCounts = []; // 棒グラフ用

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $db_available = true;
} catch (PDOException $e) {
    $db_available = false;
}

date_default_timezone_set('Asia/Tokyo');

$year = isset($_GET['year']) ? (int)$_GET['year'] : date("Y");
$month = isset($_GET['month']) ? (int)$_GET['month'] : date("m");

$firstDayStr = sprintf("%04d-%02d-01", $year, $month);
$firstDayTime = strtotime($firstDayStr);
$daysInMonth = date("t", $firstDayTime);
$startWeekDay = date("w", $firstDayTime);

$prevMonth = date("m", mktime(0,0,0,$month-1,1,$year));
$prevYear = date("Y", mktime(0,0,0,$month-1,1,$year));
$nextMonth = date("m", mktime(0,0,0,$month+1,1,$year));
$nextYear = date("Y", mktime(0,0,0,$month+1,1,$year));

$colorMap = [
    '赤' => '#ff4d4d',
    '青' => '#4da6ff',
    '黄色' => '#ffff66',
    '緑' => '#66ff66',
    'オレンジ' => '#ffb84d',
    '紫' => '#cc66ff',
    'ピンク' => '#ff99cc',
    '茶色' => '#b3a87c',
    '灰色' => '#bfbfbf',
    '黒' => '#333333',
    '白' => '#ffffff'
];

// データ取得
if ($db_available) {
    try {
        $sql = "
            SELECT DATE(d.created_at) as diary_date, c.color_name, d.content
            FROM diaries d
            LEFT JOIN color_emotions_flat c ON d.color_id = c.color_id
            WHERE d.user_id = :user_id
            AND DATE(d.created_at) BETWEEN :start AND :end
            ORDER BY d.created_at ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':start' => "$year-$month-01",
            ':end'   => "$year-$month-$daysInMonth"
        ]);

        foreach ($stmt as $row) {
            $date = $row['diary_date'];
            if (!isset($diaryData[$date])) $diaryData[$date] = [];
            $diaryData[$date][] = [
                'content' => $row['content'],
                'color' => $row['color_name'] ?? null
            ];

            // 日付ごとの色カウント（棒グラフ用）
            if ($row['color_name']) {
                if (!isset($diaryColorCounts[$date])) $diaryColorCounts[$date] = [];
                if (!isset($diaryColorCounts[$date][$row['color_name']])) $diaryColorCounts[$date][$row['color_name']] = 0;
                $diaryColorCounts[$date][$row['color_name']]++;
                $diaryColors[$date] = $row['color_name'];
            }
        }
    } catch (PDOException $e) {
        $diaryData = [];
        $diaryColors = [];
        $diaryColorCounts = [];
    }
}

$jsDiaryData = json_encode($diaryData);
$jsDiaryColorCounts = json_encode($diaryColorCounts);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>マイカレンダー＋感情可視化</title>
<style>
body { font-family: "Hiragino Sans", sans-serif; background: #f9fafb; margin: 0; padding: 0; }
header { background: #f9fafb; padding: 20px; text-align: center; }
.container { display: flex; max-width: 1200px; margin: 20px auto; gap: 20px; flex-wrap: wrap; }
.calendar-container { flex: 1; min-width: 500px; background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 20px; }
.diary-container { width: 400px; background: #fff9e6; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 20px; }
.chart-container { width: 100%; background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 20px; margin-top: 20px; }
h1 { margin: 0 0 10px 0; font-size: 1.8em; color: #333; }
.month-nav { display: flex; justify-content: center; align-items: center; gap: 20px; margin-bottom: 20px; font-size: 1.2em; }
.month-nav a { background: none; border: none; color: #3b82f6; font-weight: bold; font-size: 1em; text-decoration: none; cursor: pointer; padding: 5px 10px; }
.month-nav a:hover { text-decoration: underline; background: #eff6ff; border-radius: 4px; }

.calendar { width: 100%; border-collapse: collapse; }
.calendar th, .calendar td { border: 1px solid #ddd; width: 14.2%; height: 60px; text-align: center; position: relative; }
.calendar th { background: #f0f0f0; height: 40px; }
.calendar td a { display: flex; justify-content: center; align-items: center; width: 100%; height: 100%; text-decoration: none; color: #333; font-weight: bold; }

.diary-mark { position: absolute; bottom: 4px; right: 4px; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; }

footer { text-align: center; margin-top: 40px; color: #777; font-size: 0.9em; }
.footer-buttons { display: flex; justify-content: center; gap: 15px; margin-bottom: 15px; }
.button { background: #3b82f6; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; }
.button:hover { background: #2563eb; }
.warning-message { background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9em; text-align: center; }
#diary-content li { margin-bottom: 6px; }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<header>
<h1>マイカレンダー＋感情可視化</h1>
<div class="month-nav">
    <a href="?year=<?php echo $prevYear; ?>&month=<?php echo $prevMonth; ?>">＜ 前月</a>
    <span><?php echo $year; ?>年 <?php echo $month; ?>月</span>
    <a href="?year=<?php echo $nextYear; ?>&month=<?php echo $nextMonth; ?>">次月 ＞</a>
</div>
</header>

<div class="container">
    <div class="calendar-container">
        <?php if (!$db_available): ?>
        <div class="warning-message">
            データベースに接続できません。色の表示機能は利用できません。
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
            $weekDay = 0;
            echo "<tr>";
            for ($i = 0; $i < $startWeekDay; $i++) { echo "<td></td>"; $weekDay++; }

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = sprintf("%04d-%02d-%02d", $year, $month, $day);
                $bgColor = $diaryColors[$dateStr] ?? "white";
                if (isset($colorMap[$bgColor])) $bgColor = $colorMap[$bgColor];
                $hasDiary = isset($diaryData[$dateStr]);
                echo '<td style="background-color: '.$bgColor.';">';
                echo '<a href="#" class="diary-link" data-date="'.$dateStr.'">'.$day;
                if ($hasDiary) echo '<span class="diary-mark" style="background-color:#333;"></span>';
                echo '</a></td>';

                if (++$weekDay % 7 === 0) echo "</tr><tr>";
            }

            while ($weekDay % 7 !== 0) { echo "<td></td>"; $weekDay++; }
            echo "</tr>";
            ?>
            </tbody>
        </table>
    </div>

    <div class="diary-container" id="diary-display">
        <h2>日記内容</h2>
        <ul id="diary-content">
            <li>日付をクリックすると内容が表示されます。</li>
        </ul>
    </div>

    <div class="chart-container">
        <h2>日別感情棒グラフ</h2>
        <canvas id="emotionChart"></canvas>
    </div>
</div>

<footer>
<div class="footer-buttons">
    <a href="profile.php" class="button">個人ページへ</a>
    <a href="home.php" class="button">ホームへ</a>
</div>
<div class="footer-copy">
    &copy; 2025 一言×色日記 All rights reserved.
</div>
</footer>

<script>
const diaryData = <?php echo $jsDiaryData; ?>;
const diaryColorCounts = <?php echo $jsDiaryColorCounts; ?>;
const colorMap = <?php echo json_encode($colorMap); ?>;

// 日記クリック
document.querySelectorAll('.diary-link').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const date = link.dataset.date;
        const container = document.getElementById('diary-content');
        container.innerHTML = '';

        if (diaryData[date]) {
            diaryData[date].forEach(entry => {
                const li = document.createElement('li');
                li.textContent = entry.content;
                if (entry.color) {
                    const span = document.createElement('span');
                    span.textContent = ` (${entry.color})`;
                    span.style.fontWeight = 'bold';
                    span.style.marginLeft = '6px';
                    li.appendChild(span);
                    if (colorMap[entry.color]) {
                        li.style.backgroundColor = colorMap[entry.color];
                        li.style.padding = '4px';
                        li.style.borderRadius = '4px';
                    }
                }
                container.appendChild(li);
            });
        } else {
            const li = document.createElement('li');
            li.textContent = '記録はありません';
            container.appendChild(li);
        }
    });
});

// 棒グラフ作成
const ctx = document.getElementById('emotionChart').getContext('2d');

const dates = Object.keys(diaryColorCounts);
const colors = Object.keys(colorMap);
const datasets = colors.map(color => ({
    label: color,
    data: dates.map(date => diaryColorCounts[date][color] || 0),
    backgroundColor: colorMap[color]
}));

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: dates,
        datasets: datasets
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            title: { display: true, text: '日別感情棒グラフ' }
        },
        scales: {
            x: { stacked: true },
            y: { stacked: true, beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
