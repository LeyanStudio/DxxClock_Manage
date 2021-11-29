<?
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
date_default_timezone_set('Asia/Shanghai');
$date = date("Y-m-d H:i:s");

session_start();

require SYSTEM_ROOT.'../Admin/Includes/Config.php';

try {
    $GDB = new PDO("mysql:host={$dbconfig['host']};dbname={$dbconfig['dbname']};port={$dbconfig['port']}",$dbconfig['user'],$dbconfig['pwd']);
} catch (Exception $e) {
    exit('连接数据库失败:'.$e->getMessage());
}

$GDB->exec("set names utf8");

if ($_SERVER['HTTP_HOST'] == 'localhost.kayanxin.cn') {
    header("location:/SelectGrade.php");
} else {
    $urlarr = explode('_',$_SERVER['HTTP_HOST']);
    $newurl = str_replace($urlarr[0].'.','',$_SERVER['HTTP_HOST']);
    if (!$GDB->query("SELECT * FROM Web_Grade WHERE gradeid='{$urlarr[0]}' limit 1")->fetch()) {
        echo '此年级不存在！正在跳转到选择年级界面……';
        exit('<script>setTimeout(function(){window.location.href = \'http://'.$newurl.'/SelectGrade.php\';},2000);</script>');
    }
    $dbname = $urlarr[0].'_l_ci_kayanxin_cn';
}

try {
    $DB = new PDO("mysql:host=localhost;dbname={$dbname};port=3306",'root','525fa938b8e1035e');
} catch(Exception $e) {
    exit('链接数据库失败:'.$e->getMessage());
}

$DB->exec("set names utf8");

$rs = $DB->query("select * from Web_Config");
while ($row = $rs->fetch()) { 
	$conf[$row['x']] = $row['j'];
}

include_once(SYSTEM_ROOT."Function.php");
include_once(SYSTEM_ROOT."Member.php");
?>