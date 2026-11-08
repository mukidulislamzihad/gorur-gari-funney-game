<?php
session_start();
$dir=__DIR__;$cfgF="$dir/config.json";$hashF="$dir/admin_hash.php";$gameF="$dir/index.html";
$def=json_decode('{"title":"গরুর গাড়ি","tagline":"গ্রামের গরু, শহরের রাস্তা — ধরা পড়লেই শেষ!","speed":17,"turbo":51,"drain":1,"pedDmg":5000,"catchSec":10,"dayLen":180,"offerMin":60000,"offerMax":120000,"cut":2000,"hatChunk":4,"exposure":0.62,"bomb":true,"lb":true,"soundOff":false,"maint":false,"maintMsg":"গেম আপডেট হচ্ছে, একটু পরে আসুন","vals":{"rick":2500,"cng":4000,"bike":1800,"cycle":1200,"van":3500,"car":8000}}',true);
$cfg=is_file($cfgF)?array_replace_recursive($def,json_decode(file_get_contents($cfgF),true)?:[]):$def;
function h($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function page($b){echo '<!DOCTYPE html><html lang="bn"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin</title><style>
body{font-family:"Noto Sans Bengali",Arial,sans-serif;background:#14382f;color:#fff;margin:0;padding:14px}
.w{max-width:760px;margin:auto}h1{color:#ffcf2e;margin:0 0 10px}h2{color:#ffcf2e;border-bottom:2px solid #2a5a4a;padding-bottom:4px;margin-top:22px;font-size:19px}
.c{background:#0f2a23;border:2px solid #2a5a4a;border-radius:12px;padding:10px 14px;margin:8px 0}
label{display:flex;justify-content:space-between;align-items:center;gap:10px;margin:7px 0;font-size:15px}label small{color:#9fc;display:block}
input[type=text],input[type=number],input[type=password],textarea{padding:7px;border-radius:8px;border:2px solid #f7c12c;background:#fff;color:#111;font:inherit;width:170px;box-sizing:border-box}
textarea{width:100%;font-family:monospace;font-size:12px}input.l{width:60%}
button{background:#ffcf2e;border:0;border-radius:8px;padding:9px 22px;font:inherit;font-weight:700;cursor:pointer;margin:6px 6px 0 0}button.r{background:#e8408f;color:#fff}
.ok{background:#2f8f4a;padding:8px 12px;border-radius:8px;margin:8px 0}.er{background:#a8322a;padding:8px 12px;border-radius:8px;margin:8px 0}a{color:#ffcf2e}
</style></head><body><div class="w">'.$b.'</div></body></html>';exit;}
if(isset($_GET['logout'])){session_destroy();header('Location: admin.php');exit;}
if(!is_file($hashF)){
 $m='';if(($_POST['setpw']??'')!==''){if(strlen($_POST['setpw'])<6)$m='<div class="er">কমপক্ষে ৬ অক্ষর</div>';else{file_put_contents($hashF,'<?php return '.var_export(password_hash($_POST['setpw'],PASSWORD_DEFAULT),true).';');header('Location: admin.php');exit;}}
 page('<h1>🔐 পাসওয়ার্ড সেট করুন</h1>'.$m.'<form method="post" class="c"><label>নতুন পাসওয়ার্ড<input type="password" name="setpw"></label><button>সেট করুন</button></form>');}
if(empty($_SESSION['ok'])){
 $m='';if(isset($_POST['pw'])){if(password_verify($_POST['pw'],include $hashF)){session_regenerate_id(true);$_SESSION['ok']=1;$_SESSION['t']=bin2hex(random_bytes(16));header('Location: admin.php');exit;}sleep(1);$m='<div class="er">ভুল পাসওয়ার্ড</div>';}
 page('<h1>🐄 Admin Login</h1>'.$m.'<form method="post" class="c"><label>পাসওয়ার্ড<input type="password" name="pw" autofocus></label><button>লগইন</button></form>');}
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals($_SESSION['t'],$_POST['t']??'')){page('<div class="er">Session শেষ, আবার চেষ্টা করুন</div><a href="admin.php">ফিরে যান</a>');}
 $a=$_POST['a']??'';
 if($a=='save'){
  $n=function($k,$min,$max,$d)use(&$def){$v=floatval($_POST[$k]??$d);return max($min,min($max,$v));};
  $c=['title'=>mb_substr(trim($_POST['title']),0,40),'tagline'=>mb_substr(trim($_POST['tagline']),0,120),
   'speed'=>$n('speed',3,100,17),'turbo'=>$n('turbo',3,200,51),'drain'=>$n('drain',0,20,1),'pedDmg'=>(int)$n('pedDmg',0,100000000,5000),
   'catchSec'=>(int)$n('catchSec',1,120,10),'dayLen'=>(int)$n('dayLen',10,3600,180),'offerMin'=>(int)$n('offerMin',1000,100000000,60000),'offerMax'=>(int)$n('offerMax',1000,100000000,120000),
   'cut'=>(int)$n('cut',0,10000000,2000),'hatChunk'=>(int)$n('hatChunk',1,40,4),'exposure'=>$n('exposure',0.2,2,0.62),
   'bomb'=>isset($_POST['bomb']),'lb'=>isset($_POST['lb']),'soundOff'=>isset($_POST['soundOff']),'maint'=>isset($_POST['maint']),'maintMsg'=>mb_substr(trim($_POST['maintMsg']),0,150),'vals'=>[]];
  if($c['offerMax']<$c['offerMin'])$c['offerMax']=$c['offerMin'];
  foreach(['rick','cng','bike','cycle','van','car'] as $k)$c['vals'][$k]=(int)$n('v_'.$k,0,100000000,0);
  file_put_contents($cfgF,json_encode($c,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX);$cfg=$c;$msg='<div class="ok">✅ সেভ হয়েছে। গেম রিফ্রেশ করলেই কার্যকর।</div>';
 }
 if($a=='reset'){@unlink($cfgF);$cfg=$def;$msg='<div class="ok">✅ ডিফল্টে ফেরানো হয়েছে</div>';}
 if($a=='html'){$x=$_POST['html']??'';if(strpos($x,'</html>')===false||strlen($x)<5000)$msg='<div class="er">❌ ফাইল অসম্পূর্ণ, সেভ হয়নি</div>';else{@copy($gameF,"$dir/index.bak.html");file_put_contents($gameF,$x,LOCK_EX);$msg='<div class="ok">✅ index.html সেভ হয়েছে (ব্যাকআপ: index.bak.html)</div>';}}
 if($a=='restore'&&is_file("$dir/index.bak.html")){copy("$dir/index.bak.html",$gameF);$msg='<div class="ok">✅ ব্যাকআপ থেকে ফেরানো হয়েছে</div>';}
 if($a=='lbdel'||$a=='lbclear'){$lf="$dir/lb.json";$d=is_file($lf)?json_decode(file_get_contents($lf),true):[];if(!is_array($d))$d=[];if($a=='lbclear')$d=[];else{unset($d[(int)($_POST['i']??-1)]);$d=array_values($d);}file_put_contents($lf,json_encode($d,JSON_UNESCAPED_UNICODE),LOCK_EX);$msg='<div class="ok">✅ হয়েছে</div>';}
 if($a=='pw'){if(strlen($_POST['npw']??'')<6)$msg='<div class="er">কমপক্ষে ৬ অক্ষর</div>';else{file_put_contents($hashF,'<?php return '.var_export(password_hash($_POST['npw'],PASSWORD_DEFAULT),true).';');$msg='<div class="ok">✅ পাসওয়ার্ড বদলেছে</div>';}}
}
$t=h($_SESSION['t']);
$N=function($k,$l,$v,$st='1',$sm='')use(&$t){return '<label><span>'.$l.($sm?'<small>'.$sm.'</small>':'').'</span><input type="number" step="'.$st.'" name="'.$k.'" value="'.h($v).'"></label>';};
$C=function($k,$l,$v){return '<label><span>'.$l.'</span><input type="checkbox" name="'.$k.'" '.($v?'checked':'').' style="width:22px;height:22px"></label>';};
$b='<h1>🐄 গরুর গাড়ি — Admin</h1><a href="index.html" target="_blank">▶ গেম দেখুন</a> | <a href="?logout=1">লগআউট</a>'.$msg;
$b.='<form method="post"><input type="hidden" name="t" value="'.$t.'"><input type="hidden" name="a" value="save">';
$b.='<h2>🏷️ লেখা</h2><div class="c"><label>গেমের নাম<input type="text" class="l" name="title" value="'.h($cfg['title']).'"></label><label>ট্যাগলাইন<input type="text" class="l" name="tagline" value="'.h($cfg['tagline']).'"></label></div>';
$b.='<h2>🚜 গাড়ি</h2><div class="c">'.$N('speed','স্বাভাবিক গতি',$cfg['speed']).$N('turbo','টার্বো গতি',$cfg['turbo']).$N('drain','চার্জ ক্ষয়ের হার (১ = স্বাভাবিক)',$cfg['drain'],'0.1').'</div>';
$b.='<h2>💥 ক্ষতির টাকা</h2><div class="c">'.$N('pedDmg','মানুষকে ধাক্কা (৳)',$cfg['pedDmg']);
foreach(['rick'=>'রিকশা','cng'=>'সিএনজি','bike'=>'বাইক','cycle'=>'সাইকেল','van'=>'ভ্যান','car'=>'কার'] as $k=>$l)$b.=$N('v_'.$k,$l.' (৳)',$cfg['vals'][$k]);
$b.='</div><h2>😡 তাড়া / সময়</h2><div class="c">'.$N('catchSec','ধরা পড়লে পালানোর সময় (সেকেন্ড)',$cfg['catchSec']).$N('dayLen','সকাল/বিকেল/রাত বদলের সময় (সেকেন্ড)',$cfg['dayLen']).$N('exposure','আলো (০.২–২)',$cfg['exposure'],'0.02').'</div>';
$b.='<h2>🐄 ঈদের হাট ও দর-কষাকষি</h2><div class="c">'.$N('hatChunk','হাটের দূরত্ব (ব্লক)',$cfg['hatChunk'],'1','দূরত্ব ≈ ব্লক × ২০০ + ১০ মিটার (৪ = ৮১০ মি)').$N('offerMin','ব্যাপারীর অফার সর্বনিম্ন (৳)',$cfg['offerMin']).$N('offerMax','ব্যাপারীর অফার সর্বোচ্চ (৳)',$cfg['offerMax']).$N('cut','ব্যাপারী কত টাকা কম বলবে (৳)',$cfg['cut']).'</div>';
$b.='<h2>⚙️ অন/অফ</h2><div class="c">'.$C('bomb','শব্দ-বোমা চালু',$cfg['bomb']).$C('lb','লিডারবোর্ড চালু',$cfg['lb']).$C('soundOff','শুরুতে সাউন্ড বন্ধ',$cfg['soundOff']).$C('maint','🚧 মেইনটেন্যান্স মোড (গেম বন্ধ)',$cfg['maint']).'<label>মেইনটেন্যান্স বার্তা<input type="text" class="l" name="maintMsg" value="'.h($cfg['maintMsg']).'"></label></div>';
$b.='<button>💾 সেভ করুন</button></form>';
$b.='<form method="post" onsubmit="return confirm(\'ডিফল্টে ফেরাবেন?\')"><input type="hidden" name="t" value="'.$t.'"><input type="hidden" name="a" value="reset"><button class="r">↩ ডিফল্ট</button></form>';
$b.='<h2>📝 index.html সম্পাদনা (গেমের কোড)</h2><form method="post"><input type="hidden" name="t" value="'.$t.'"><input type="hidden" name="a" value="html"><textarea name="html" rows="16">'.h(is_file($gameF)?file_get_contents($gameF):'').'</textarea><button>💾 কোড সেভ</button></form>';
if(is_file("$dir/index.bak.html"))$b.='<form method="post"><input type="hidden" name="t" value="'.$t.'"><input type="hidden" name="a" value="restore"><button class="r">⏪ ব্যাকআপ ফেরান</button></form>';
$lf="$dir/lb.json";$ld=is_file($lf)?json_decode(file_get_contents($lf),true):[];if(!is_array($ld))$ld=[];
$b.='<h2>🏆 লিডারবোর্ড</h2><div class="c">'.($ld?'':'কেউ নেই');
foreach(array_slice($ld,0,50,true) as $i=>$r)$b.='<form method="post" style="display:flex;justify-content:space-between;align-items:center;margin:4px 0"><span>#'.($i+1).' '.h($r['n']).' — ৳'.(int)$r['s'].'</span><input type="hidden" name="t" value="'.$t.'"><input type="hidden" name="a" value="lbdel"><input type="hidden" name="i" value="'.$i.'"><button class="r" style="padding:4px 12px;margin:0">✕</button></form>';
if($ld)$b.='<form method="post" onsubmit="return confirm(\'সব মুছবেন?\')"><input type="hidden" name="t" value="'.$t.'"><input type="hidden" name="a" value="lbclear"><button class="r">🗑 সব মুছুন</button></form>';
$b.='</div>';
$b.='<h2>🔑 পাসওয়ার্ড বদল</h2><form method="post" class="c"><input type="hidden" name="t" value="'.$t.'"><input type="hidden" name="a" value="pw"><label>নতুন পাসওয়ার্ড<input type="password" name="npw"></label><button>বদলান</button></form>';
page($b);
