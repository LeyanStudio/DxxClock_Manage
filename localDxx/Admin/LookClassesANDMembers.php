<style>
b[id='title'] {
    width: 160px;
    display: block;
    float: left;
    border-left: 3px solid #1660F3;
    padding: 0 10px;
}
pp {
    margin: 7px;
    border-left: 3px solid #ff420a;
    padding: 3px 10px;
    display: block;
    line-height: 18px;
    word-break: break-word;
}
</style>
<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {exit('您无权限！');}

$gid = $_GET['GID'];
$grade = $DB->query("SELECT * FROM Web_Grade WHERE id='{$gid}'")->fetch();
//$ms = explode(',',$notice['status']);

if (!$grade) {
    exit('无此年级！'.$gid);
} else {
    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
    $classes = $thisDB->query("SELECT * FROM Web_Class order by name")->fetchAll();
    foreach ($classes as $c) {
        echo '<b id="title">高2020级'.$c['name'].'班</b><br>';
        $i = 0;
        $mems = $thisDB->query("SELECT * FROM Web_User WHERE class='{$c['name']}' order by id")->fetchAll();
        if (!$mems) {
            echo '<center>暂无班级内成员</center>';
        } elseif ($_GET['TYPE'] != 1) {
            echo '<pp>';
            foreach ($mems as $mem) {
                $i++;
                echo ($i == '1' ? '' : '、').$mem['username'];
            }
            echo '</pp>';
        }
        echo '</p>';
    }
}
?>