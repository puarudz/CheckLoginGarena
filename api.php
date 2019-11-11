<?php
$passmd5 = md5(urldecode($_GET['pass']));
$username = $_GET['user'];
runne:
$keycap = md5(microtime_float());
file_put_contents("captcha/".$keycap.".png",curl("https://gop.captcha.garena.com/image?key=".$keycap));
$captcha = json_decode(file_get_contents("https://ocr.v4u.vn/api.php?format=hard&img=http://".$_SERVER['HTTP_HOST']."/captcha/".$keycap.".png"),true);
unlink("captcha/".$keycap.".png");
$get = json_decode(curl("https://sso.garena.com/api/prelogin?account=".$username."&captcha_key=".$keycap."&captcha=".$captcha['code']."&format=json&id=".microtime_float()."&app_id=10100"),true);
if($get['error'] == "error_captcha") goto runne;
if($get['error'] == "error_require_captcha") goto runne;

$pass= EnCode($passmd5,hash('sha256',hash('sha256',$passmd5.$get['v1']).$get['v2']));

$url= json_decode(curl("https://sso.garena.com/api/login?account=".$username."&password=".$pass."&format=json&id=".microtime_float()."&app_id=10100"),true);
    if(empty($url['uid']))
    {
        $showinfo['error'] = "Sai tài khoản hoặc mật khẩu";
    }
    else
    {
        
        $zPuaru = json_decode(curl("https://account.garena.com/api/account/init?session_key=".$url['session_key']),true); 
        //$zPuaru = json_decode(curl("http://gameprofile.garenanow.com/api/game_profile/?uid=".$url['uid']."&region=vn"),true);
        if($zPuaru['user_info']['email_v'] == 0) $showinfo['verimail'] = "Chưa xác thực mail";        
        else $showinfo['verimail'] = "Đã xác thực mail"; 
        if(!empty($zPuaru['user_info']['email'])) $showinfo['email'] = $zPuaru['user_info']['email']; 
        if(!empty($zPuaru['user_info']['username'])) $showinfo['username'] = $zPuaru['user_info']['username']; 
        if(!empty($zPuaru['user_info']['mobile_no'])) $showinfo['mobile_no'] = $zPuaru['user_info']['mobile_no']; 
        
    }
    echo json_encode($showinfo);

function EnCode($plaintext,$key)
{
  $chiperRaw = openssl_encrypt(hex2bin($plaintext), "AES-256-ECB", hex2bin($key), OPENSSL_RAW_DATA);
  return substr(bin2hex($chiperRaw),0,32);
}

function curl($url)
{
    $ch = @curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    $head[] = "Connection: keep-alive";
    $head[] = "Keep-Alive: 300";
    $head[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
    $head[] = "Accept-Language: en-us,en;q=0.5";
    curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.80 (Windows NT 6.0) Presto/2.12.388 Version/12.14');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Expect:'
    ));
    $page = curl_exec($ch);
    curl_close($ch);
    return $page;
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    $return = ((float)$usec + (float)$sec);
    $return = str_replace(".","",$return);
    return substr($return,0,-1);
}

?>
