<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
      transition: transform 0.3s, box-shadow 0.3s;
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

    /* 色ごとに配置 */
    .color-1 { background: rgb(255, 0, 0); transform: rotate(-0deg) translateX(220px) rotate(0deg); }
    .color-2 { background: rgb(0, 0, 250); transform: rotate(-40deg) translateX(220px) rotate(40deg); }
    .color-3 { background: rgb(255, 255, 0); transform: rotate(-80deg) translateX(220px) rotate(80deg); }
    .color-4 { background: rgb(255, 165, 0); transform: rotate(-120deg) translateX(220px) rotate(120deg); }
    .color-5 { background: rgb(0, 128, 0); transform: rotate(-160deg) translateX(220px) rotate(160deg); }
    .color-6 { background: rgb(255, 20, 147); transform: rotate(-200deg) translateX(220px) rotate(200deg); }
    .color-7 { background: rgb(211, 211, 211); transform: rotate(-240deg) translateX(220px) rotate(240deg); }
    .color-8 { background: rgb(128, 0, 128); transform: rotate(-280deg) translateX(220px) rotate(280deg); }
    .color-9 { background: rgb(218, 165, 32); transform: rotate(-320deg) translateX(220px) rotate(320deg); }

    /* レスポンシブ対応 */
    @media(max-width: 700px) {
      .hue-circle {
        width: 90vw;
        height: 90vw;
      }
      .hue-dot {
        width: 14vw;
        height: 14vw;
      }
      .color-1, .color-2, .color-3, .color-4, .color-5, .color-6, .color-7, .color-8, .color-9 {
        transform: rotate(var(--angle)) translateX(36vw) rotate(var(--angle));
      }
    }
  </style>
</head>
<body>
  <h1>色と感情のつながり</h1>
  <p><a href="diary.php">日記を書く</a></p>

  <div class="hue-circle">
    <div class="hue-dot color-1"><span class="word">怒り</span></div>
    <div class="hue-dot color-2"><span class="word">悲しみ</span></div>
    <div class="hue-dot color-3"><span class="word">喜び</span></div>
    <div class="hue-dot color-4"><span class="word">楽しい</span></div>
    <div class="hue-dot color-5"><span class="word">安らぎ</span></div>
    <div class="hue-dot color-6"><span class="word">愛</span></div>
    <div class="hue-dot color-7"><span class="word">不安</span></div>
    <div class="hue-dot color-8"><span class="word">寂しさ</span></div>
    <div class="hue-dot color-9"><span class="word">自信</span></div>
  </div>
</body>
</html>
