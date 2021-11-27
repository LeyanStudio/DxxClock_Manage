<?
include("./Includes/Common.php");
if ($userrow['status'] == 1 && $islogin2 == 1) {} else {exit('您无权限！');}

$cid = $_POST['classid'];
$class = $DB->query("SELECT * FROM Web_Class WHERE name='{$cid}' limit 1")->fetch();
$members = $DB->query("SELECT * FROM Web_User WHERE class='{$cid}'")->fetchAll();
$dltzs = $DB->query("SELECT * FROM Web_User WHERE class='{$cid}' and status=2")->fetchAll();
foreach ($dltzs as $d) {
    if ($d['id'] != $class['lid']) {
        $dd[] = $d;
    }
}

$tasks = $DB->query("SELECT * FROM Web_Task order by id desc")->fetchAll();
$user = $DB->query("SELECT * FROM Web_User WHERE id='{$class['lid']}' limit 1")->fetch();
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
<div class="CIIndex">
    <lable>班级号</lable>
    <font id="NorDiv"><?= $cid ?></font>
</div>
<div class="CIIndex">
    <lable>成员数量</lable>
    <font id="NorDiv"><?= (!$members ? '未添加成员' : count($members).'人') ?></font>
</div>
<div class="CIIndex">
    <lable>团支书姓名</lable>
    <font id="NorDiv"><?= (!$members ? '未添加成员' : (!$user ? '暂未设置' : $user['username'])) ?></font>
</div>
<div class="CIIndex">
    <lable>代理团支书姓名</lable>
    <?if (@count($dd) <= 0) {echo '<font id="NorDiv">暂无代理</font>';} else { foreach ($dltzs as $daili) {if ($daili['id'] == $class['lid']) {} else {?><font  id="NorDiv"><? echo $daili['username'];?></font><?}}}?>
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
                $clocking = $DB->query("SELECT * FROM Web_Clocking WHERE class='{$cid}' and taskbatch='{$t['id']}' and status!='3'")->fetchAll();
                $hcs = $DB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$t['id']}' and class='{$cid}' and status='3'")->fetchAll();
        ?>
        <?
        if (time() >= $t['endtime']) {
            $hcounts = @count($hcs);
            $counts = $counts - $hcounts;
        ?>
        <span>任务标题</span>：<b><?= $t['title'] ?></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;打卡人数情况：<b>【<b style="color:red;"><?= (!$clocking ? '无人打卡' : count($clocking).'人') ?></b>（未打卡<b style="color:orange;"><?= $hcounts ?></b>人）】|<b style="color:blue;">【<?= count($members) ?>人</b>】</b><br>
        <?
        } else {
        ?>
        <span>任务标题</span>：<b><?= $t['title'] ?></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;打卡人数情况：<b>【<b style="color:red;"><?= (!$clocking ? '无人打卡' : count($clocking).'人') ?></b>】|<b style="color:blue;">【<?= count($members) ?>人</b>】</b><br>
        <?
        }
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
            foreach ($members as $m) {
                $unclocking = $DB->query("SELECT * FROM Web_Clocking WHERE uid='{$m['id']}' and status='3'")->fetchAll();
        ?>
        <font id="MemsDiv"><?= $m['username'] ?>【<? if (!$unclocking) {?>无未打卡<?} else {?>未打卡<b style="font-size:13px;color:red;color:red;"><?= count($unclocking) ?></b>次<?} ?>】</font>
        <?
            }
        ?>
        <?
        }
        ?>
    </div>
</div>