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

// 色と感情の取得
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
<title>色と感情 詳細</title>
<style>
body {
    margin: 0;
    font-family: "Hiragino Sans","Helvetica Neue",sans-serif;
    background: linear-gradient(135deg,#ffdde1,#ee9ca7);
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 100vh;
    color: #333;
    text-align: center;
    transition: background 0.5s ease;
}

h1 {
    margin-top: 40px;
    font-size: 2.5em;
    color: #4a6fa5;
    text-shadow: 1px 1px 5px rgba(0,0,0,0.1);
}

a {
    text-decoration: none;
    color: #fff;
    background: #4d8df5;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: bold;
    transition: 0.3s;
    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    margin: 10px;
}

a:hover {
    background: #2f6de0;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 24px rgba(0,0,0,0.25);
}

.hue-circle {
    position: relative;
    width: 600px;
    height: 600px;
    margin: 60px auto;
    border-radius: 50%;
    background: radial-gradient(circle at center, rgba(255,255,255,0.1), transparent 70%);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.5s;
}

.hue-dot {
    height: 90px;
    width: 90px;
    border-radius: 50%;
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.5s, box-shadow 0.5s, left 0.5s, top 0.5s;
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.hue-dot:hover {
    transform: scale(1.2);
    box-shadow: 0 12px 24px rgba(0,0,0,0.3);
    z-index: 10;
}

.word {
    position: absolute;
    bottom: 110%;
    left: 50%;
    transform: translateX(-50%);
    padding: 6px 12px;
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 8px;
    font-size: 14px;
    font-weight: bold;
    color: #333;
    white-space: nowrap;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s, transform 0.3s;
}

.hue-dot:hover .word {
    opacity: 1;
    transform: translateX(-50%) translateY(-5px);
}

/* 初期配置用変数 */
<?php
$angle = -0;
foreach ($color_emotions as $index => $ce) {
    $angle = $index * -40;
    $color_class = "color-" . ($index + 1);
    echo ".{$color_class} { background: {$ce['color_code']}; left: calc(50% + 220px * cos(" . deg2rad($angle) . ")); top: calc(50% + 220px * sin(" . deg2rad($angle) . ")); }\n";
}
?>

@media(max-width: 700px) {
  .hue-circle { width: 90vw; height: 90vw; }
  .hue-dot { width: 14vw; height: 14vw; }
}
</style>
</head>
<body>
<h1>色と感情のつながり</h1>
<div>
    <a href="diary.php">日記を書く</a>
    <a href="home.php">ホームに戻る</a>
</div>

<div class="hue-circle" id="hueCircle">
    <?php foreach ($color_emotions as $index => $ce): 
        $color_class = "color-" . ($index + 1);
        $feeling = htmlspecialchars($ce['feeling_text']);
        $color_code = htmlspecialchars($ce['color_code']);
    ?>
    <div class="hue-dot <?= $color_class ?>" data-color="<?= $color_code ?>">
        <span class="word"><?= $feeling ?></span>
    </div>
    <?php endforeach; ?>
</div>

<script>
const dots = document.querySelectorAll('.hue-dot');
const body = document.body;
const circle = document.getElementById('hueCircle');

dots.forEach(dot => {
    dot.addEventListener('click', () => {
        // 背景色変更
        const color = dot.getAttribute('data-color');
        body.style.background = color + '33';

        // 全てのドットを初期位置に戻す
        const radius = 220;
        const centerX = 50;
        const centerY = 50;
        dots.forEach((d, i) => {
            const angle = i * -40;
            d.style.left = `calc(${centerX}% + ${radius}px * cos(${angle}deg))`;
            d.style.top = `calc(${centerY}% + ${radius}px * sin(${angle}deg))`;
        });

        // クリックしたドットを中央に移動
        dot.style.left = '50%';
        dot.style.top = '50%';
    });
});
</script>
</body>
</html>
