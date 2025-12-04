<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// セッションからログインユーザーIDを取得
$current_user_id = $_SESSION['user_id'];

// ---------------------------------------------------------
// 1. DB接続設定
// ---------------------------------------------------------
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
    echo "Connected to Render PostgreSQL!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}


$has_recorded = false;
$status_message = "今日の記録は\nまだです";
$has_diary_data = false;
$labels = [];
$data_counts = [];
$bg_colors = [];
$table_missing = false;

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // ---------------------------------------------------------
    // 2. 左側：今日の記録チェック
    // ---------------------------------------------------------
    $today = date('Y-m-d');
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM public.diaries WHERE user_id = :uid AND DATE(created_at) = :today");
        $stmt->execute([':uid' => $current_user_id, ':today' => $today]);
        $has_recorded = $stmt->fetchColumn() > 0;
        $status_message = $has_recorded ? "今日の記録は\n完了済みです" : "今日の記録は\nまだです";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'does not exist') !== false) {
            $table_missing = true;
            $status_message = "データベースの\n準備中です";
        } else {
            throw $e;
        }
    }
    
    // ---------------------------------------------------------
    // 3. 右側：みんなの感情（全9種類を取得）
    // ---------------------------------------------------------
    if (!$table_missing) {
        $target_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        
        try {
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
            $total_count = 0;
            
            foreach ($chart_rows as $row) {
                $labels[] = $row['feeling_text'];
                $count = (int)$row['count'];
                $data_counts[] = $count;
                $bg_colors[] = $row['color_name'];
                $total_count += $count;
            }
            
            $has_diary_data = $total_count > 0;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'does not exist') !== false) {
                $table_missing = true;
            } else {
                throw $e;
            }
        }
    }
    
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
    exit;
}

// カレンダー表示用の日付（テーブルがない場合も使用）
$target_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>感情カレンダー</title>
    <?php if ($has_diary_data): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
    <style>
        body { font-family: sans-serif; background: #FDFDFD; padding: 20px; color: #333; }
        .container { display: flex; flex-wrap: wrap; justify-content: center; gap: 40px; max-width: 1000px; margin: 0 auto; }
        .header { text-align: center; border: 2px solid #333; padding: 15px; margin-bottom: 30px; border-radius: 10px; background: #fff; max-width: 400px; margin-left: auto; margin-right: auto; }
        .left-col, .right-col { flex: 1; min-width: 300px; text-align: center; }
        .btn { display: block; width: 80%; margin: 10px auto; padding: 15px; border: 2px solid #333; background: #fff; color: #333; text-decoration: none; font-weight: bold; border-radius: 8px; box-shadow: 3px 3px 0 #333; }
        .btn:hover { background: #F0F0F0; transform: translate(1px, 1px); box-shadow: 2px 2px 0 #333; }
        .status-message { font-size: 1.5em; white-space: pre-wrap; margin: 20px 0; }
        .chart-container { position: relative; height: 350px; width: 100%; max-width: 450px; margin: 20px auto; }
        input[type="date"] { padding: 8px; font-size: 1.1em; border: 2px solid #333; border-radius: 5px; }
        .no-data-message { font-size: 1.2em; color: #666; margin: 40px 0; padding: 20px; background: #f9f9f9; border-radius: 8px; }
        .warning-message { font-size: 1em; color: #d9534f; margin: 20px 0; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="header"><h1>サイト名</h1></div>
    
    <?php if ($table_missing): ?>
    <div class="warning-message">
        <strong>お知らせ:</strong> データベーステーブルの作成が必要です。<br>
        データベース管理者に「diariesテーブル」の作成を依頼してください。
    </div>
    <?php endif; ?>
    
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
            
            <?php if ($table_missing): ?>
                <div class="no-data-message">
                    データベースの準備が完了すると<br>ここにグラフが表示されます
                </div>
            <?php elseif ($has_diary_data): ?>
                <div class="chart-container">
                    <canvas id="emotionChart"></canvas>
                </div>
            <?php else: ?>
                <div class="no-data-message">
                    この日の日記データはまだありません
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($has_diary_data): ?>
    <script>
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
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12 },
                            padding: 15,
                            usePointStyle: true,
                        }
                    },
                    tooltip: {
                        callbacks: {}
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>