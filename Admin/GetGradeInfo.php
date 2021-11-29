<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {exit('您无权限！');}

$gid = $_POST['gradeid'];
$grade = $DB->query("SELECT * FROM Web_Grade WHERE id='{$gid}'")->fetch();
$tasks = $DB->query("SELECT * FROM Web_Task ORDER BY id DESC")->fetchAll();
$thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
?>
<style>
.CIIndex {
    width: 100%;
    min-height: 30px;
    line-height: 30px;
    margin-bottom: 10px;
}
/*.CIIndex lable {
    width: 140px;
    display: block;
    float: left;
    border-left: 3px solid #1660F3;
    padding: 0 10px;
    border-radius: 6px;
    box-shadow: 1px 1px 4px;
}*/
.CIIndex lable {
    width: 140px;
    display: block;
    float: left;
    border-left: 3px solid #1660F3;
    padding: 0 10px;
}
.CIIndex font[id='NorDiv'] {
    margin-left: 15px;
}
.CIIndex div font#MemsDiv {
    border: 1px solid #1683F3;
    padding: 0 8px;
    width: 130px;
    float: left;
    font-size: 10px;
    margin-right: 13px;
    margin-bottom: 10px;
    cursor: pointer;
    border-radius: 5px;
    box-shadow: 1px 1px 3px #0D3491;
}
.CIIndex div font#MemsDiv:hover {
    border: 1px solid #bbd9f9;
    box-shadow: 1px 1px 6px #5877c1;
}
.CIIndex div span {
    margin-left: 10px;
    border-left: 3px solid #ff420a;
    padding: 3px 10px;
}
</style>
<?
$classes = $thisDB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll();
$members = $thisDB->query("SELECT * FROM Web_User WHERE username!='Administrator'")->fetchAll();
?>
<div class="CIIndex">
    <lable>年级号</lable>
    <font id="NorDiv"><?= $grade['gradeid'] ?></font>
</div>
<div class="CIIndex">
    <lable>班级数量</lable>
    <font id="NorDiv"><?= (!$classes ? '未添加班级' : count($classes).'个') ?></font>
</div>
<div class="CIIndex">
    <lable>成员数量</lable>
    <font id="NorDiv"><?= (!$members ? '未添加成员' : count($members).'人') ?></font>
</div>
<div class="CIIndex">
    <lable>团支书姓名</lable>
    <div><br><span style="display: block;margin-top: 10px;"><?
    $i = 0;
    
    if (($thisDB->query("SELECT count(*) FROM Web_Class WHERE lid!='0'")->fetchColumn()) == 0) {
        echo '暂未设置团支书';
    }
    
    foreach ($classes as $cl) {
        $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$cl['lid']}'")->fetch();
        if ($cl['lid'] == '0') {} else {
            $i++;
            echo ($i == 1 ? '' : '、').$user['username'];
        }
        $tzsnames[] = $user['username'];
    }
    ?></span></div>
</div>
<div class="CIIndex">
    <lable>代理团支书姓名</lable>
    <div><br><span style="display: block;margin-top: 10px;"><?
    $i = 0;
    
    if (($thisDB->query("SELECT count(*) FROM Web_User WHERE status='2'")->fetchColumn()) == 0) {
        echo '暂未设置代理团支书';
    }
    
    foreach ($members as $ml) {
        if ($ml['status'] == 2 && !in_array($ml['username'],$tzsnames)) {
            $i++;
            echo ($i == 1 ? '' : '、').$ml['username'];
        }
    }
    ?></span></div>
</div>
<div class="CIIndex">
    <lable>每期打卡完成情况</lable>
    <br>
    <div style="margin-top: 10px;">
        <?
        if (!$tasks) {
        ?>
        暂未发布打卡任务
        <?
        } else {
            foreach ($tasks as $t) {
                $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}' limit 1")->fetch();
                $clocking = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status!='3'")->fetchAll();
                $hcs = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='3'")->fetchAll();
        ?>
        <?
        if (time() >= $t['endtime']) {
            $hcounts = @count($hcs);
            $counts = $counts - $hcounts;
        ?>
        <span>任务标题</span>：<b><?= $t['title'] ?></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;打卡人数情况：<b>【<b style="color:red;"><?= (!$clocking ? '无人打卡' : count($clocking).'人') ?></b><? if(!$clocking){}else{ ?>（未打卡<b style="color:orange;"><?= $hcounts ?></b>人）<? } ?>】|<b style="color:blue;">【<?= count($members) ?>人</b>】</b><br>
        <?
        } else {
        ?>
        <span>任务标题</span>：<b><?= $t['title'] ?></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;打卡人数情况：<b>【<b style="color:red;"><?= (!$clocking ? '无人打卡' : count($clocking).'人') ?></b>】|<b style="color:blue;">【<?= count($members) ?>人</b>】</b><br>
        <?
        }
        ?>
        <?
            }
        ?>
        <?
        }
        ?>
    </div>
</div>
<div class="CIIndex">
    <lable>成员姓名【打卡详情】</lable>
    <br>
    <div style="margin-top: 10px;">
        <?
        if (!$members) {
        ?>
        暂未添加成员
        <?
        } else {
            foreach ($classes as $c) {
        ?>
        <p><span><?= $grade['name'].$c['name'] ?>班</span></p>
        <div style="margin-top: 10px;float: left;">
        <?
                $memberss = $thisDB->query("SELECT * FROM Web_User WHERE class='{$c['name']}'")->fetchAll();
                foreach ($memberss as $m) {
                    $unclocking = $thisDB->query("SELECT * FROM Web_Clocking WHERE uid='{$m['id']}' and status='3'")->fetchAll();
        ?>
        <font id="MemsDiv"><?= $m['username'] ?>【<? if (!$unclocking) {?>无未打卡<?} else {?>未打卡<b style="font-size:13px;color:red;color:red;"><?= count($unclocking) ?></b>次<?} ?>】</font>
        <?
                }
            echo '</div>';
            }
            echo '</div>';
        ?>
        <?
        }
        ?>
    </div>
</div>