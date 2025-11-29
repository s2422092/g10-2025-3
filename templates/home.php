<?php
// ---------------------------------------------------------
// 1. DB接続設定
// ---------------------------------------------------------
$host = 'localhost';
$dbname = 'mi11yu17';
$user = 'mi11yu17';
$password = '5SQuEDtU';
$current_user_id = 1; // ログインユーザーID（仮）
try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    // ---------------------------------------------------------
    // 2. 左側：今日の記録チェック
    // ---------------------------------------------------------
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM public.diaries WHERE user_id = :uid AND DATE(created_at) = :today");
    $stmt->execute([':uid' => $current_user_id, ':today' => $today]);
    $has_recorded = $stmt->fetchColumn() > 0;
    $status_message = $has_recorded ? "今日の記録は\n完了済みです" : "今日の記録は\nまだです";
    // ---------------------------------------------------------
    // 3. 右側：みんなの感情（全9種類を取得）
    // ---------------------------------------------------------
    $target_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    // ★ポイント: color_emotions_flat を主軸(FROM)にして LEFT JOIN することで
    // 投稿がない感情も必ず取得し、件数を0として扱います。
    $sql_chart = "
        SELECT
            c.color_name,
            c.feeling_text,
            COUNT(d.diary_id) as count
        FROM public.color_emotions_flat c
        LEFT JOIN public.diaries d
            ON c.color_id = d.color_id
            AND DATE(d.created_at) = :target_date
            AND d.user_id IN (SELECT user_id FROM public.users WHERE is_public = TRUE)
        GROUP BY c.id, c.color_name, c.feeling_text
        ORDER BY c.id ASC
    ";
    $stmt_chart = $pdo->prepare($sql_chart);
    $stmt_chart->execute([':target_date' => $target_date]);
    $chart_rows = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);
    // JSへ渡す配列作成
    $labels = [];
    $data_counts = [];
    $bg_colors = [];
    foreach ($chart_rows as $row) {
        $labels[] = $row['feeling_text'];
        $data_counts[] = (int)$row['count']; // 数値型にキャスト
        $bg_colors[] = $row['color_name'];   // DBに入っている 'rgb(255,0,0)' 等
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>感情カレンダー</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* レイアウトCSS (前回と同じ) */
        body { font-family: sans-serif; background: #FDFDFD; padding: 20px; color: #333; }
        .container { display: flex; flex-wrap: wrap; justify-content: center; gap: 40px; max-width: 1000px; margin: 0 auto; }
        .header { text-align: center; border: 2px solid #333; padding: 15px; margin-bottom: 30px; border-radius: 10px; background: #fff; max-width: 400px; margin-left: auto; margin-right: auto; }
        .left-col, .right-col { flex: 1; min-width: 300px; text-align: center; }
        .btn { display: block; width: 80%; margin: 10px auto; padding: 15px; border: 2px solid #333; background: #fff; color: #333; text-decoration: none; font-weight: bold; border-radius: 8px; box-shadow: 3px 3px 0 #333; }
        .btn:hover { background: #F0F0F0; transform: translate(1px, 1px); box-shadow: 2px 2px 0 #333; }
        .status-message { font-size: 1.5em; white-space: pre-wrap; margin: 20px 0; }
        .chart-container { position: relative; height: 350px; width: 100%; max-width: 450px; margin: 20px auto; }
        input[type="date"] { padding: 8px; font-size: 1.1em; border: 2px solid #333; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="header"><h1>サイト名</h1></div>
    <div class="container">
        <div class="left-col">
            <h2 style="margin-top:0;"><?php echo date('Y年n月j日'); ?></h2>
            <div class="status-message"><?php echo $status_message; ?></div>
            <a href="diary.php" class="btn">記録する</a>
            <a href="profile.php" class="btn">マイページ</a>
        </div>
        <div class="right-col">
            <h2>みんなの感情</h2>
            <form action="" method="GET">
                <input type="date" name="date" value="<?php echo htmlspecialchars($target_date); ?>" onchange="this.form.submit()">
            </form>
            <div class="chart-container">
                <canvas id="emotionChart"></canvas>
            </div>
        </div>
    </div>
    <script>
        // PHPからデータ受け取り
        const labels = <?php echo json_encode($labels); ?>;
        const dataCounts = <?php echo json_encode($data_counts); ?>;
        const bgColors = <?php echo json_encode($bg_colors); ?>;
        const ctx = document.getElementById('emotionChart').getContext('2d');
        const emotionChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: dataCounts,
                    backgroundColor: bgColors,
                    borderColor: '#333',
                    borderWidth: 1 // 線を少し細くして見やすく
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom', // 全9種類あるので下に配置すると見やすいです
                        labels: {
                            font: { size: 12 },
                            padding: 15,
                            usePointStyle: true, // ■ではなく●にする
                        }
                    },
                    tooltip: {
                        callbacks: {
                            // 0件の場合はツールチップを出さない、等の調整も可能
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>