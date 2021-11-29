<?php
if(isset($_COOKIE["user_token"]))
{
	$token=@authcode(daddslashes($_COOKIE['user_token']), 'DECODE', SYS_KEY);
	if (count(explode("\t", $token)) <= 3) {
	    $islogin2 = '-1';
	} else {
	    list($pid, $class, $sid, $expiretime) = explode("\t", $token);
	    $userrow=$DB->query("SELECT * FROM Web_User WHERE username='{$pid}' and class='{$class}' limit 1")->fetch();
	    $session=md5($userrow['username'].$userrow['password'].$password_hash);
	    if($session==$sid && $expiretime>time()) {
	    	$islogin2=1;
	    }else{
	    	$islogin2='-1';
	    }
	}
}
?>