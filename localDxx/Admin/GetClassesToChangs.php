<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {exit('您无权限！');}

$mid = $_POST['mid'];
$gid = $_POST['gid'];
$grade = $DB->query("SELECT * FROM Web_Grade WHERE gradeid='{$gid}'")->fetch();
if (!$grade) {
    exit('{"code":-1,"msg":"该年级不存在！"}');
}
            
//连接数据库
$thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);

$type = $_POST['type'];
?>
<select class="el-input__inner" id="ccselclass" onchange="<?= ($type == 'Array' ? 'AdminDoChangeClasses();' : 'AdminDoChangeClass(\''.$mid.'\');') ?>" style="float:left;width:210px;margin-left:5px;line-height:30px;">
    <option value="0">请选择班级</option>
<?
$user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$mid}' limit 1")->fetch();
$classes = $thisDB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll();
foreach ($classes as $c) {
?>
    <option value="<?= $c['name'] ?>"<?= ($user['class'] == $c['name'] ? ' selected="selected"' : '') ?>><?= ($user['class'] == $c['name'] ? '当前班级：' : '') ?><?= $conf['gradename'].$c['name'] ?>班</option>
<?
}
?>
</select>