<?
include("./Includes/Common.php");
if ($userrow['status'] == 1 && $islogin2 == 1) {} else {exit('您无权限！');}

$cid = $_POST['classid'];
?>
<select class="el-input__inner" id="selmember" onchange="SetLid('<?= $cid ?>','1');" style="float:left;width:210px;margin-left:5px;line-height:30px;">
    <option value="0">请选择成员</option>
<?
$class = $DB->query("SELECT * FROM Web_Class WHERE name='{$cid}' limit 1")->fetch();
$members = $DB->query("SELECT * FROM Web_User WHERE class='{$cid}'")->fetchAll();
foreach ($members as $m) {
?>
    <option value="<?= $m['id'] ?>"<?= ($class['lid'] == $m['id'] ? ' selected="selected"' : '') ?>><?= ($class['lid'] == $m['id'] ? '当前团支书：' : '') ?><?= $m['username'] ?></option>
<?
}
?>
</select>