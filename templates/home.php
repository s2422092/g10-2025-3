<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ログイン中のユーザーIDを取得
$current_user_id = $_SESSION['user_id'];

// ---------------------------------------------------------
// DB接続
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
} catch (PDOException $e) {
    die("DB接続エラー: " . $e->getMessage());
}

$has_recorded = false;
$status_message = "今日の記録は\nまだです";
$has_diary_data = false;
$labels = [];
$data_counts = [];
$bg_colors = [];
$table_missing = false;

try {
    // 今日の記録チェック
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM public.diaries WHERE user_id = :uid AND DATE(created_at) = :today");
    $stmt->execute([':uid' => $current_user_id, ':today' => $today]);
    $has_recorded = $stmt->fetchColumn() > 0;
    $status_message = $has_recorded ? "今日の記録は\n完了済みです" : "今日の記録は\nまだです";

    // みんなの感情グラフ用データ取得
    $target_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

    $sql_chart = "
        SELECT
            c.color_name,
            c.color_code,
            c.feeling_text,
            COUNT(d.diary_id) as count
        FROM public.color_emotions_flat c
        LEFT JOIN public.diaries d
            ON c.color_id = d.color_id
            AND DATE(d.created_at) = :target_date
            AND d.user_id IN (SELECT user_id FROM public.users WHERE is_public = TRUE)
        GROUP BY c.id, c.color_name, c.color_code, c.feeling_text
        ORDER BY c.id ASC
    ";
    $stmt_chart = $pdo->prepare($sql_chart);
    $stmt_chart->execute([':target_date' => $target_date]);
    $chart_rows = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

    $total_count = 0;
    foreach ($chart_rows as $row) {
        $labels[] = $row['feeling_text'];
        $data_counts[] = (int)$row['count'];
        $bg_colors[] = $row['color_code']; // color_code を使用
        $total_count += $row['count'];
    }
    $has_diary_data = $total_count > 0;

} catch (PDOException $e) {
    $table_missing = true;
    $status_message = "データベースの\n準備中です";
}

// 表示用日付
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
body {
    margin: 0;
    padding: 0;
    font-family: "Hiragino Sans","Helvetica Neue",sans-serif;
    background: linear-gradient(135deg,#8fbaff,#ffd7e7);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    color: #333;
}
.header { margin: 30px 0 20px; font-size:2em; color:#4a6fa5; font-weight:bold; }
.container { display:flex; flex-wrap:wrap; justify-content:center; gap:30px; max-width:1000px; width:90%; margin-bottom:50px; }
.card { background: rgba(255,255,255,0.92); backdrop-filter: blur(10px); border-radius:18px; box-shadow:0 15px 40px rgba(0,0,0,0.15); padding:35px 25px; flex:1; min-width:300px; }
h2 { margin-top:0; color:#333; }
.status-message { font-size:1.4em; white-space:pre-wrap; margin:20px 0; }
.btn { display:block; width:80%; margin:12px auto; padding:14px 0; border:none; border-radius:12px; background:#4d8df5; color:white; font-weight:bold; font-size:1.1em; cursor:pointer; box-shadow:0 6px 16px rgba(0,0,0,0.15); transition:.25s ease; text-decoration:none; }
.btn:hover { background:#2f6de0; transform: translateY(-4px) scale(1.03); box-shadow:0 12px 24px rgba(0,0,0,0.22); }
.chart-container { position: relative; height: 350px; width:100%; max-width:450px; margin:20px auto; }
input[type="date"] { padding:10px 12px; font-size:1.1em; border-radius:10px; border:2px solid #4d8df5; outline:none; margin-top:10px; }
input[type="date"]:focus { box-shadow:0 0 6px rgba(77,141,245,0.3); }
.no-data-message, .warning-message { font-size:1.1em; color:#666; margin:20px 0; padding:20px; border-radius:12px; background: rgba(255,255,255,0.8); box-shadow:0 6px 16px rgba(0,0,0,0.05); }
.warning-message { color:#d9534f; background:#fff3cd; border:1px solid #ffc107; }
</style>
</head>
<body>
<div class="header">感情カレンダー</div>

<?php if ($table_missing): ?>
<div class="warning-message">
<strong>お知らせ:</strong> データベーステーブルの作成が必要です。<br>
データベース管理者に「diariesテーブル」の作成を依頼してください。
</div>
<?php endif; ?>

<div class="container">
    <div class="card">
        <h2><?php echo date('Y年n月j日'); ?></h2>
        <div class="status-message"><?php echo $status_message; ?></div>
        <a href="diary.php" class="btn">記録する</a>
        <a href="profile.php" class="btn">マイページ</a>
    </div>

    <div class="card">
        <h2>みんなの感情</h2>
        <form action="" method="GET">
            <input type="date" name="date" value="<?php echo htmlspecialchars($target_date); ?>" onchange="this.form.submit()">
        </form>

        <?php if ($table_missing): ?>
            <div class="no-data-message">データベースの準備が完了すると<br>ここにグラフが表示されます</div>
        <?php elseif ($has_diary_data): ?>
            <div class="chart-container">
                <canvas id="emotionChart"></canvas>
            </div>
        <?php else: ?>
            <div class="no-data-message">この日の日記データはまだありません</div>
        <?php endif; ?>
    </div>
</div>

<?php if ($has_diary_data): ?>
<script>
const labels = <?php echo json_encode($labels); ?>;
const dataCounts = <?php echo json_encode($data_counts); ?>;
const bgColors = <?php echo json_encode($bg_colors); ?>;
const ctx = document.getElementById('emotionChart').getContext('2d');
new Chart(ctx, {
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
                labels: { font: { size: 12 }, padding: 15, usePointStyle: true }
            }
        }
    }
});
</script>
<?php endif; ?>
</body>
</html>
