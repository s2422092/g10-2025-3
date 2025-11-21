<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>一言×色日記 - ログイン</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }
        
        h1 {
            color: #4a6fa5;
            margin-bottom: 30px;
            text-align: center;
        }
        
        form {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 320px;
        }
        </style>
</head>

<body>
    <h1>ログイン画面</h1>
    <form>
        <label>ユーザー名：
            <input type="text" name="username" required>
        </label>
        <br>
        <label>パスワード：
            <input type="password" name="password" required>
        </label>
</br>

        <button type="submit">ログイン</button>
    </form>
<?php
// 単純なリンク
echo '<a href="index.php">トップページへ</a>';
?>
<?php
// 単純なリンク
echo '<a href="signup.php">新規登録ページへ</a>';
?>
</body>
</html>