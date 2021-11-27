<?php
if(isset($_COOKIE["admin_token"]))
{
	$token=@authcode(daddslashes($_COOKIE['admin_token']), 'DECODE', SYS_KEY);
	list($pid, $sid, $expiretime) = explode("\t", $token);
	$adminrow=$DB->query("SELECT * FROM Web_Admin WHERE username='{$pid}' limit 1")->fetch();
	$session=md5($adminrow['username'].$adminrow['password'].$password_hash);
	if($session==$sid && $expiretime>time()) {
		$islogin=1;
	}else{
		$islogin='-1';
	}
}
?>