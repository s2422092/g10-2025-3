
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>一言×色日記</title>
    <style>
        body {
            font-family: "Hiragino Sans", "Helvetica Neue", sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #73a2edff;
            color: white;
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
        p {
            color: #555;
        }
        .date {
            font-size: 1.1em;
            margin: 10px 0 30px;
            color: #666;
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
        .db-status {
            text-align: center;
            margin-top: 30px;
            font-size: 1em;
        }
    </style>
</head>
<body>

<header>
    <h1>一言×色日記</h1>
    <p>あなたの気持ちを色で表現する日記アプリです。</p>
</header>

<main>
    <h2>ようこそ！</h2>
    <p>このアプリでは、あなたの日々の出来事や気持ちを簡単に記録できます。</p>
    <p>今日の日付：</p>
    <div class="date"><?= date("Y年m月d日") ?></div>

    <a href="login.php" class="button">ログイン</a>
    <a href="signup.php" class="button">新規登録</a>
  
    <div class="db-status">
        <hr>
        <p><strong>接続状態：</strong> <?= $db_message ?></p>
    </div>
</main>

<footer>
    &copy; <?= date("Y") ?> 一言×色日記 All rights reserved.
</footer>

</body>
</html>
