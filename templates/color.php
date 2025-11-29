<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>色と感情 詳細</title>
  <style>
    .hue-circle {
      position: relative;
      width: 600px;    /* 円の直径（拡大） */
      height: 600px;   /* 円の直径（拡大） */
      margin: 40px auto;
      border-radius: 50%;
    }
    .hue-dot {
      /* 丸のサイズ（親に対する割合ではなく固定pxに変更して見やすく） */
      height: 90px;          /* 拡大 */
      width: 90px;           /* 拡大 */
      top: calc(50% - 45px); /* 垂直中央から半径分の調整（高さの半分） */
      left: calc(50% + 220px); /* 円の中心からの半径（拡大） */
      transform-origin: -220px center; /* 半径と一致させる */
      border-radius: 999px;
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: default;
    }

    /* 円配置用の回転（角度はそのまま） */
    .color-1 { background: rgb(255, 0, 0);    transform: rotate(-0deg); }
    .color-2 { background: rgb(0, 0, 250);    transform: rotate(-40deg); }
    .color-3 { background: rgb(255, 255, 0);  transform: rotate(-80deg); }
    .color-4 { background: rgb(255, 165, 0);  transform: rotate(-120deg); }
    .color-5 { background: rgb(0, 128, 0);    transform: rotate(-160deg); }
    .color-6 { background: rgb(255, 20, 147); transform: rotate(-200deg); }
    .color-7 { background: rgb(211, 211, 211);transform: rotate(-240deg); }
    .color-8 { background: rgb(128, 0, 128);  transform: rotate(-280deg); }
    .color-9 { background: rgb(218, 165, 32); transform: rotate(-320deg); }

    .mouse:hover .word {
      display: inline;
    }

    .word {
      position: absolute;
      display: none;
      padding: 6px 10px;     /* テキストも少し大きめに */
      color: rgb(0, 0, 0);
      border-radius: 8px;
      background-color: rgba(255, 255, 255, 0.95);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
      white-space: nowrap;
      font-size: 14px;       /* 読みやすく拡大 */
      text-align: center;
      /* 丸の外側・上部に表示（拡大に合わせて調整） */
      top: -38px;
      left: 50%;
      transform: translateX(-50%);
      pointer-events: none;
    }

    /* 逆回転でテキストを水平に保つ */
    .color-1 .word { transform: translateX(-50%) rotate(0deg); }
    .color-2 .word { transform: translateX(-50%) rotate(40deg); }
    .color-3 .word { transform: translateX(-50%) rotate(80deg); }
    .color-4 .word { transform: translateX(-50%) rotate(120deg); }
    .color-5 .word { transform: translateX(-50%) rotate(160deg); }
    .color-6 .word { transform: translateX(-50%) rotate(200deg); }
    .color-7 .word { transform: translateX(-50%) rotate(240deg); }
    .color-8 .word { transform: translateX(-50%) rotate(280deg); }
    .color-9 .word { transform: translateX(-50%) rotate(320deg); }
  </style>
</head>
<body>
  <h1>色と感情のつながり</h1>
  <p><a href="diary.php">日記を書く</a></p>

  <div class="hue-circle hue-4">
    <div class="hue-dot color-1 mouse"><span class="word">怒り</span></div>
    <div class="hue-dot color-2 mouse"><span class="word">悲しみ</span></div>
    <div class="hue-dot color-3 mouse"><span class="word">喜び</span></div>
    <div class="hue-dot color-4 mouse"><span class="word">楽しい</span></div>
    <div class="hue-dot color-5 mouse"><span class="word">安らぎ</span></div>
    <div class="hue-dot color-6 mouse"><span class="word">愛</span></div>
    <div class="hue-dot color-7 mouse"><span class="word">不安</span></div>
    <div class="hue-dot color-8 mouse"><span class="word">寂しさ</span></div>
    <div class="hue-dot color-9 mouse"><span class="word">自信</span></div>
  </div>
</body>
</html>