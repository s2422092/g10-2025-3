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
        .btn-area {
            margin: 20px 0 10px;
        }

        .button {
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

        .button:hover {
            background: #2f6de0;
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        /* -------------------------
            Footer
        ------------------------- */
        footer {
            text-align: center;
            margin: 40px 0 20px;
            color: #fff;
            font-size: 0.9em;
            animation: fadeIn 2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* -------------------------
            DB Status
        ------------------------- */
        .db-status {
            text-align: center;
            margin-top: 35px;
            font-size: 1em;
            color: #555;
        }
        .db-status hr {
            margin-bottom: 15px;
            border: none;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>

<header>
    <h1>一言×色日記</h1>
    <p>あなたの気持ちを、そっと色で記録する日記アプリ。</p>
</header>

<main>
    <h2>ようこそ。</h2>
    <p>その日「心に残った一言」や「ふとした気持ち」を、色と一緒に記録できます。</p>

    <p>今日の日付：</p>
    <div class="date" id="todayDate"><?= date("Y年m月d日") ?></div>

    <div class="btn-area">
        <a href="login.php" class="button">ログイン</a>
        <a href="signup.php" class="button">新規登録</a>
    </div>

    <div class="db-status">
        <hr>
        <p><strong>接続状態：</strong> <?= $db_message ?></p>
    </div>
</main>

<footer>
    &copy; <?= date("Y") ?> 一言×色日記 — All rights reserved.
</footer>

<script>
    // 日付をフェードイン
    window.addEventListener("load", () => {
        const dateEl = document.getElementById("todayDate");
        setTimeout(() => {
            dateEl.style.opacity = 1;
            dateEl.style.transform = "translateY(0)";
        }, 300);
    });
</script>

</body>
</html>
