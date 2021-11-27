<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {exit('您无权限！');}

$grade = $DB->query("SELECT * FROM Web_Grade WHERE id='{$_POST['gid']}'")->fetch();

if ($_POST['gid'] == null || !$grade) {
    echo '没有此年级';
    exit('<script>setTimeout(function(){history.go(-1);},2000);</script>');
}
$thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);

$cid = $_POST['classid'];
?>
<select class="el-input__inner" id="selmember" onchange="AdminSetLid('<?= $cid ?>','1');" style="float:left;width:210px;margin-left:5px;line-height:30px;">
    <option value="0">请选择成员</option>
<?
$class = $thisDB->query("SELECT * FROM Web_Class WHERE name='{$cid}' limit 1")->fetch();
$members = $thisDB->query("SELECT * FROM Web_User WHERE class='{$cid}'")->fetchAll();
foreach ($members as $m) {
?>
    <option value="<?= $m['id'] ?>"<?= ($class['lid'] == $m['id'] ? ' selected="selected"' : '') ?>><?= ($class['lid'] == $m['id'] ? '当前团支书：' : '') ?><?= $m['username'] ?></option>
<?
}
?>
</select>
<input type="hidden" name="gradeid" value="<?= $grade['gradeid'] ?>" />