function set2fig(num){
  let ret;
  if(num < 10){
    ret = '0' + num;
  }else{
    ret = num;
  }
  return ret;
}
function showClock() {
  let nowTime = new Date();
  let nowHour = set2fig(nowTime.getHours());
  let nowMin  = set2fig(nowTime.getMinutes());
  let nowSec  = set2fig(nowTime.getSeconds());
  let msg = nowHour + ":" + nowMin + ":" + nowSec;
  document.getElementById("clock").innerHTML = msg;
}
setInterval('showClock()',1000);