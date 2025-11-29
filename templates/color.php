
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>色と感情 詳細</title>
<style>
.hue-dot {
  /* 15%: 丸のサイズ */
  height: 15%;
  width: 10%;
  top: calc(50% - 15% / 2);
  /* 30px: 中心からの距離 */
  left: calc(50% + 150px);
  transform-origin: -150px center;
  border-radius: 999px;
  position: absolute;
}
.color-1 {background: rgb(255,0,0); transform: rotate(-0deg);}
.color-2 {background: rgb(0,0,250); transform: rotate(-40deg);}
.color-3 {background: rgb(255,255,0); transform: rotate(-80deg);}
.color-4 {background: rgb(255,165,0); transform: rotate(-120deg);}
.color-5 {background: rgb(0,128,  0); transform: rotate(-160deg);}
.color-6 {background: rgb(255, 20,147); transform: rotate(-200deg);}
.color-7 {background: rgb(211,211,211); transform: rotate(-240deg);}
.color-8 {background: rgb(128,  0,128); transform: rotate(-280deg);}
.color-9 {background: rgb(218,165, 32); transform: rotate(-320deg);}
.mouse {
  /* margin: 30px 0px 0px 30px;
  position: relative; */
}
.mouse:hover .word {
  display: inline;
}
.word {
  position   : absolute;
  display: none;
  padding: 2px;
  color: rgb(0, 0, 0);
  border-radius: 5px;
  background-color:rgb(255, 255, 255);
  width:100px;
  top: 50px;
  left: 0px;
  font-size: 12px;
  text-align: center;
}
</style>
</head>
<body>
  <h1>色と感情のつながり</h1>
<div class="hue-circle hue-4">
    <div class="hue-dot color-1 mouse">
      <span class="word">怒り</span>
    </div>
    <div class="hue-dot color-2 mouse">
      <span class="word">悲しみ</span>
    </div>
    <div class="hue-dot color-3 mouse">
      <span class="word">喜び</span>
    </div>
    <div class="hue-dot color-4 mouse">
      <span class="word">楽しい</span>
    </div>
    <div class="hue-dot color-5 mouse">
      <span class="word">安らぎ</span>
    </div>
    <div class="hue-dot color-6 mouse">
      <span class="word">愛</span>
    </div>
    <div class="hue-dot color-7 mouse">
      <span class="word">不安</span>
    </div>
    <div class="hue-dot color-8 mouse">
      <span class="word">寂しさ</span>
    </div>
    <div class="hue-dot color-9 mouse">
      <span class="word">自信</span>
    </div>
  </div>
</body>
</html>
