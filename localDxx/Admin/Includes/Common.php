<?
if ($_SERVER['HTTP_HOST'] != 'localhost.kayanxin.cn') {
    header("location:/");
}

define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
date_default_timezone_set('Asia/Shanghai');
$date = date("Y-m-d H:i:s");

session_start();
require SYSTEM_ROOT.'../Includes/Config.php';

try {
    $DB = new PDO("mysql:host={$dbconfig['host']};dbname={$dbconfig['dbname']};port={$dbconfig['port']}",$dbconfig['user'],$dbconfig['pwd']);
} catch (Exception $e) {
    exit('连接数据库失败:'.$e->getMessage());
}
//连接root数据库
require SYSTEM_ROOT.'../Includes/RootConfig.php';

try {
    $RDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$rdbconfig['dbname']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
} catch (Exception $e) {
    exit('连接数据库失败:'.$e->getMessage());
}

$DB->exec("set names utf8");

$rs = $DB->query("select * from Web_Config");
while ($row = $rs->fetch()) { 
	$conf[$row['x']] = $row['j'];
}

include("Function.php");
include("Member.php");
?>
<link rel="stylesheet" href="http://nchat.kayanxin.cn/Assets/Js/layer/theme/default/layer.css">