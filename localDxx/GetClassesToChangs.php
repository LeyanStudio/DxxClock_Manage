<?
include("./Includes/Common.php");
if (($userrow['status'] == 1 || $userrow['status'] == 2) && $islogin2 == 1) {} else {exit('您无权限！');}

$mid = $_POST['mid'];
$type = $_POST['type'];
?>
<select class="el-input__inner" id="ccselclass" onchange="<?= ($type == 'Array' ? 'DoChangeClasses();' : 'DoChangeClass(\''.$mid.'\');') ?>" style="float:left;width:210px;margin-left:5px;line-height:30px;">
    <option value="0">请选择班级</option>
<?
$user = $DB->query("SELECT * FROM Web_User WHERE id='{$mid}' limit 1")->fetch();
$classes = $DB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll();
foreach ($classes as $c) {
?>
    <option value="<?= $c['id'] ?>"<?= ($user['class'] == $c['name'] ? ' selected="selected"' : '') ?>><?= ($user['class'] == $c['name'] ? '当前班级：' : '') ?><?= $conf['gradename'].$c['name'] ?>班</option>
<?
}
?>
</select>