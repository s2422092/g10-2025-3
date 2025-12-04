<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>一言×色日記</title>

    <style>
        /* -------------------------
            Base Style
        ------------------------- */
        body {
            margin: 0;
            padding: 0;
            font-family: "Hiragino Sans", "Helvetica Neue", sans-serif;
            background: linear-gradient(135deg, #8fbaff, #ffd7e7);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            animation: bgFade 2s ease;
            text-align: center; /* ページ全体を中央寄せ */
        }

        @keyframes bgFade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* -------------------------
            Header
        ------------------------- */
        header {
            text-align: center;
            padding: 40px 20px 20px;
            color: #fff;
            animation: fadeIn 1.2s ease forwards;
        }

        header h1 {
            font-size: 2.4em;
            margin: 0;
            letter-spacing: 1.5px;
        }

        header p {
            margin-top: 10px;
            font-size: 1.1em;
            opacity: 0.9;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* -------------------------
            Card Main Container
        ------------------------- */
        main {
            max-width: 750px;
            width: 90%;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.15);
            animation: floatUp 1.5s ease;
        }

        @keyframes floatUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        main h2 {
            font-size: 1.8em;
            color: #333;
            margin-top: 0;
        }

        main p {
            color: #555;
            line-height: 1.7;
        }

        .date {
            margin: 10px 0 25px;
            font-size: 1.2em;
            font-weight: bold;
            color: #444;
            opacity: 0;
            transform: translateY(10px);
            transition: 0.6s ease;
        }

        /* -------------------------
            Buttons
        ------------------------- */
        .btn-area, .action-buttons {
            margin: 20px 0 10px;
        }

        .button, .fancy-btn {
            display: inline-block;
            padding: 14px 28px;
            margin: 10px;
            background: #4d8df5;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 1.1em;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s;
        }

        .button:hover, .fancy-btn:hover {
            background: #2f6de0;
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .fancy-btn.accent {
            background: #ff87c3;
        }

        .fancy-btn.accent:hover {
            background: #ff6ab4;
        }

        /* -------------------------
            Footer
        ------------------------- */
        footer {
            text-align: center;
            margin: 40px 0 20px;
            color: #fff;
            font-size: 0.9em;
        }

        /* -------------------------
            DB Status
        ------------------------- */
        .db-status, .beautiful-status {
            text-align: center;
            margin-top: 35px;
            font-size: 1em;
            color: #555;
        }

        .db-status hr, .beautiful-status hr {
            margin-bottom: 15px;
            border: none;
            border-top: 1px solid #ddd;
        }

        /* ----------- Main Card Enhancements ----------- */
        .main-card {
            max-width: 800px;
            width: 92%;
            margin: 30px auto;
            padding: 45px 40px;
            background: rgba(255,255,255,0.92);
            border-radius: 22px;
            backdrop-filter: blur(10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            animation: softRise 1.4s ease;
        }

        @keyframes softRise {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-title {
            font-size: 2.1em;
            margin-bottom: 5px;
            animation: fadeSlide 1.2s ease;
        }

        .fade-text {
            font-size: 1.1em;
            color: #555;
            line-height: 1.8;
            animation: fadeSlide 1.4s ease;
        }

        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .highlight {
            font-weight: bold;
            color: #4d8df5;
            background: linear-gradient(90deg, #bcd8ff 0%, #ffe0f0 100%);
            padding: 3px 8px;
            border-radius: 6px;
        }

        .date-box {
            margin: 30px auto;
            padding: 18px 22px;
            background: linear-gradient(135deg, #eef5ff, #fff3f8);
            border-left: 6px solid #6b9fff;
            border-radius: 14px;
            animation: fadeSlide 1.6s ease;
            box-shadow: 0 4px 18px rgba(0,0,0,0.07);
            display: inline-block;
        }

        .date-label {
            font-size: 0.95em;
            color: #777;
        }

        .date-value {
            font-size: 1.4em;
            font-weight: bold;
            margin-top: 6px;
            color: #333;
            opacity: 0;
            transform: translateY(10px);
            transition: 0.7s ease;
        }

        .action-buttons {
            margin-top: 40px;
            text-align: center;
        }
    </style>
</head>
<body>

<header>
    <h1>一言×色日記</h1>
    <p>あなたの気持ちを、そっと色で記録する日記アプリ。</p>
</header>

<main class="main-card">
    <div class="welcome-block">
        <h2 class="fade-title">ようこそ。</h2>
        <p class="fade-text">
            その日「心に残った一言」や「ふとした気持ち」を、<br>
            <span class="highlight">色</span> と一緒に記録できます。
        </p>

        <div class="date-box">
            <div class="date-label">今日の日付</div>
            <div class="date-value" id="todayDate"><?= date("Y年m月d日") ?></div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="login.php" class="button fancy-btn">ログイン</a>
        <a href="signup.php" class="button fancy-btn accent">新規登録</a>
    </div>


</main>

<footer>
    &copy; <?= date("Y") ?> 一言×色日記 — All rights reserved.
</footer>

<script>
    // 日付のフェードイン
    window.addEventListener("load", () => {
        const dv = document.getElementById("todayDate");
        setTimeout(() => {
            dv.style.opacity = 1;
            dv.style.transform = "translateY(0)";
        }, 300);
    });
</script>

</body>
</html>
