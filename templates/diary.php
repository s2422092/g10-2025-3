
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日記の内容を記載</title>
</head>
<body>
    <h1>日記の内容を記載</h1>
<form action="diary_save.php" method="POST">
    <p>日付選択: <input type="date" id="diary-date" name="diary_date"></p>
    <p>内容の記載:</p>
    <textarea id="diary-content" rows="10" cols="100" name="diary_content"></textarea>
    <input type="hidden" name="user_id" value="1">
    <br>色の選択:</br>
    <select name="diary_color_id">
        <option value="1">:赤い四角特大:赤</option>
        <option value="2">:青い四角特大:青</option>
        <option value="3">:黄色い四角特大:黄</option>
        <option value="4">:オレンジの四角特大:オレンジ</option>
        <option value="5">:緑の四角特大:緑</option>
        <option value="6">:紫の四角特大:ピンク</option>
        <option value="7">:白四角ボタン:灰</option>
        <option value="8">:紫の四角特大:紫</option>
        <option value="9">:黄色い四角特大:金</option>
    </select>
    <a href="color.php"><p>色の見本はこちら</p></a>
    <br>
    <button type="submit">保存</button>
</form>
    </body>
</html>
