<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {exit('您无权限！');}

$nid = $_POST['nid'];
$notice = $DB->query("SELECT * FROM Web_Notice WHERE id='{$nid}' limit 1")->fetch();
if (!$notice) {
    exit('无此公告！');
} else {
    exit($notice['msg']);
}
?>