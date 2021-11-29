<?php
function real_ip() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    return $ip;
}
function send_mail($to, $sub, $msg) {
    global $conf;
    if ($conf['mail_cloud'] == 1) {
        $url = 'http://api.sendcloud.net/apiv2/mail/send';
        $data = array(
            'apiUser' => $conf['mail_apiuser'],
            'apiKey' => $conf['mail_apikey'],
            'from' => $conf['mail_name'],
            'fromName' => $conf['title'],
            'to' => $to,
            'subject' => $sub,
            'html' => $msg
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $json = curl_exec($ch);
        curl_close($ch);
        $arr = json_decode($json, true);
        if ($arr['statusCode'] == 200) {
            return true;
        } else {
            return implode("\n", $arr['message']);
        }
    } else {
        if (!function_exists("openssl_sign") && $conf['mail_port'] == 465) {
            $mail_api = 'http://1.mail.qqzzz.net/';
        }
        if ($mail_api) {
            $post[sendto] = $to;
            $post[title] = $sub;
            $post[content] = $msg;
            $post[user] = $conf['mail_name'];
            $post[pwd] = $conf['mail_pwd'];
            $post[nick] = $conf['title'];
            $post[host] = $conf['mail_smtp'];
            $post[port] = $conf['mail_port'];
            $post[ssl] = $conf['mail_port'] == 465 ? 1 : 0;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $mail_api);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $ret = curl_exec($ch);
            curl_close($ch);
            if ($ret == '1') return true;
            else return $ret;
        } else {
            include_once ROOT . 'Includes/Smtp.Class.php';
            $From = $conf['mail_name'];
            $Host = $conf['mail_smtp'];
            $Port = $conf['mail_port'];
            $SMTPAuth = 1;
            $Username = $conf['mail_name'];
            $Password = $conf['mail_pwd'];
            $Nickname = $conf['title'];
            $SSL = $conf['mail_port'] == 465 ? 1 : 0;
            $mail = new SMTP($Host, $Port, $SMTPAuth, $Username, $Password, $SSL);
            $mail->att = array();
            if ($mail->send($to, $From, $sub, $msg, $Nickname)) {
                return true;
            } else {
                return $mail->log;
            }
        }
    }
}
function send_sms($phone, $code, $moban = '1') {
    global $conf;
    $app = $conf['title'];
    $url = 'http://api.978w.cn/yzmsms/index/appkey/' . $conf['sms_appkey'] . '/phone/' . $phone . '/moban/' . $moban . '/app/' . $app . '/code/' . $code;
    $data = get_curl($url);
    $arr = json_decode($data, true);
    if ($arr['status'] == '200') {
        return true;
    } else {
        return $arr['error_msg_zh'];
    }
}
function daddslashes($string, $force = 0, $strip = FALSE) {
    !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
    if (!MAGIC_QUOTES_GPC || $force) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = daddslashes($val, $force, $strip);
            }
        } else {
            $string = addslashes($strip ? stripslashes($string) : $string);
        }
    }
    return $string;
}
function checkmobile() {
    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $ualist = array(
        'android',
        'midp',
        'nokia',
        'mobile',
        'iphone',
        'ipod',
        'blackberry',
        'windows phone'
    );
    if ((dstrpos($useragent, $ualist) || strexists($_SERVER['HTTP_ACCEPT'], "VND.WAP") || strexists($_SERVER['HTTP_VIA'], "wap"))) return true;
    else return false;
}
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    $ckey_length = 4;
    $key = md5($key ? $key : ENCRYPT_KEY);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()) , -$ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb) , 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result.= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb) , 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}
function showmsg($content = '未知的异常', $type = 4, $back = false) {
    switch ($type) {
        case 1:
            $panel = "success";
            break;

        case 2:
            $panel = "info";
            break;

        case 3:
            $panel = "warning";
            break;

        case 4:
            $panel = "danger";
            break;
    }
    echo '<div class="panel panel-' . $panel . '">
     <div class="panel-heading">
       <h3 class="panel-title">提示信息</h3>
       </div>
       <div class="panel-body">';
    echo $content;
    if ($back) {
        echo '<hr/><a href="' . $back . '"><< 返回上一页</a>';
    } else echo '<hr/><a href="javascript:history.back(-1)"><< 返回上一页</a>';
    echo '</div>
   </div>';
}
function sysmsg($msg = '未知的异常', $die = true) {
?>  
   <!DOCTYPE html>
   <html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
   <head>
       <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>站点提示信息</title>
       <style type="text/css">
html{background:#eee}body{background:#fff;color:#333;font-family:"楷体","KaiTi",sans-serif;margin:2em auto;padding:1em 2em;max-width:700px;-webkit-box-shadow:10px 10px 10px rgba(0,0,0,.13);box-shadow:10px 10px 10px rgba(0,0,0,.13);opacity:.8}h1{border-bottom:1px solid #dadada;clear:both;color:#666;font:24px "楷体","KaiTi",,sans-serif;margin:30px 0 0 0;padding:0;padding-bottom:7px}#error-page{margin-top:50px}h3{text-align:center}#error-page p{font-size:9px;line-height:1.5;margin:25px 0 20px}#error-page code{font-family:Consolas,Monaco,monospace}ul li{margin-bottom:10px;font-size:9px}a{color:#21759B;text-decoration:none;margin-top:-10px}a:hover{color:#D54E21}.button{background:#f7f7f7;border:1px solid #ccc;color:#555;display:inline-block;text-decoration:none;font-size:9px;line-height:26px;height:28px;margin:0;padding:0 10px 1px;cursor:pointer;-webkit-border-radius:3px;-webkit-appearance:none;border-radius:3px;white-space:nowrap;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;-webkit-box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);vertical-align:top}.button.button-large{height:29px;line-height:28px;padding:0 12px}.button:focus,.button:hover{background:#fafafa;border-color:#999;color:#222}.button:focus{-webkit-box-shadow:1px 1px 1px rgba(0,0,0,.2);box-shadow:1px 1px 1px rgba(0,0,0,.2)}.button:active{background:#eee;border-color:#999;color:#333;-webkit-box-shadow:inset 0 2px 5px -3px rgba(0,0,0,.5);box-shadow:inset 0 2px 5px -3px rgba(0,0,0,.5)}table{table-layout:auto;border:1px solid #333;empty-cells:show;border-collapse:collapse}th{padding:4px;border:1px solid #333;overflow:hidden;color:#333;background:#eee}td{padding:4px;border:1px solid #333;overflow:hidden;color:#333}
       </style>
   </head>
   <body id="error-page">
       <?php
    echo '<h3>站点提示信息</h3>';
    echo $msg;
?>
   </body>
   </html>
   <?php
    if ($die == true) {
        exit;
    }
}
function getTaskId() {
    $SDB = new PDO("mysql:host=localhost;dbname=l_ci_kayanxin_cn;port=3306","l_ci_kayanxin_cn","kyx1012kyx");
    $time = time();
    $task = $SDB->query("SELECT * FROM Web_Task WHERE addtime<='{$time}' and endtime>='{$time}' order by id desc limit 1")->fetch();
    return $task['id'];
}
function oaddslashes($str) {
    return str_replace('\\','\\\\',str_replace('\'','\\\'',$str));
}
//模拟登录
function login_post($url, $cookie, $post) {
	$curl = curl_init();//初始化curl模块
	curl_setopt($curl, CURLOPT_URL, $url);//登录提交的地址
	curl_setopt($curl, CURLOPT_HEADER, 0);//是否显示头信息
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//是否自动显示返回的信息
	curl_setopt($curl, CURLOPT_POST, 1);//post方式提交
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post);//要提交的信息
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Content-Length: ' . strlen($post)
        )
    );
	$rs = curl_exec($curl);//执行cURL
	curl_close($curl);//关闭cURL资源，并且释放系统资源
	return $rs;
}
//登录成功后获取数据
function get_content($url, $token, $post) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);//post方式提交
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);//要提交的信息
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Content-Length: ' . strlen($post),
        'token: ' . $token
        )
    );//设置网页token
	$rs = curl_exec($ch); //执行cURL抓取页面内容
	curl_close($ch);
	return $rs;
}
//获取本地页面源代码
function get_pageinfo($url, $token) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Cookie: ' . $token
        )
    );//设置网页cookie
	$rs = curl_exec($ch); //执行cURL抓取页面内容
	curl_close($ch);
	return $rs;
}
/*//获取某某标签内代码
function get_tag_data($html,$tag,$class,$value){ 
    //$value 为空，则获取class=$class的所有内容
    $regex = $value ? "/<$tag.*?$class=\"$value\".*?>(.*?)<\/$tag>/is" :  "/<$tag.*?$class=\".*?$value.*?\".*?>(.*?)<\/$tag>/is";
    preg_match_all($regex,$html,$matches,PREG_PATTERN_ORDER); 
    return $matches[1];//返回值为数组 ,查找到的标签内的内容
}*/
?>
