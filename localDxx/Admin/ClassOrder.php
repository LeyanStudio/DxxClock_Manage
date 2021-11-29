<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {header("Location:Index.php");}

$grade = $DB->query("SELECT * FROM Web_Grade WHERE id='{$_GET['GID']}'")->fetch();
if (!$grade) {
    echo '没有此年级';
    exit('<script>setTimeout(function(){history.go(-1);},2000);</script>');
}
$thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
$gname = ($thisDB->query("SELECT * FROM Web_Config WHERE x='gradename'")->fetch())['j'];

$num = ($_GET['Num'] == null || $_GET['Num'] <= 0 ? '1' : $_GET['Num']);
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= $conf['title'] ?>【总管理-首页】</title>
    <link rel="icon" href="/Assets/Img/favicon.ico">
    <link rel="stylesheet" href="/Assets/Css/Index.css"></head>
    <script src="http://nchat.kayanxin.cn/Assets/Js/jquery.js"></script>
    <script src="http://nchat.kayanxin.cn/Assets/Js/layer/layer.js"></script>
    <script src="/Assets/Js/Main.js"></script>
    <style>
.NoticeList {
    width: 95%;
    height: 15px;
    padding: 7px 5px;
    background: #e8f7fd;
    margin-bottom: 8px;
    border-radius: 5px;
    cursor: pointer;
    box-shadow: 2px 2px 4px #169AF3;
}
.NoticeList:hover {
    background: #d1f6ff;
    box-shadow: 2px 2px 4px #16c0f3;
}
.NoticeList b {
    display: block;
    width: 27%;
    float: left;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 12px;
}
.NoticeList span {
    margin-left: 10px;
    position: relative;
    top: 1px;
    color: grey;
    font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
    float: right;
}
.NoticeList notread {
    background: #ff4700;
    border-radius: 10px;
    padding: 2px 4px;
    color: white;
    font-weight: bold;
    font-size: 10px;
}
.NoticeList nodismsg {
    display: none;
}
.logins .el-form-item {
    margin: 0 auto 15px auto;
}
    </style>
  <body>
    <div class="main">
      <div id="appLogin">
        <div class="logins position" style="width: 400px;margin-left: -200px;">
          <div class="mip-reg-logo">
          <div class="mip-reg-heading" style="color: red;"><b>[<?= $gname ?>]</b>班级排序明细[根据网站内<b><u>总任务情况</u></b>排序]</div>
          <div class="mip-reg-body">
            <div class="el-form-item">
              <div class="el-form-item__content">
                <input type="text" class="el-input__inner" name="Classnum" style="height: 30px;" oninput="SearchDisplayClass();" value="" placeholder="请输入班级号" />
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <div class="el-input__inner" id="OutVipList" style="width: 350px;position: relative;right: 15px;height: 220px;overflow-x: hidden;overflow-y: auto;text-align: left;line-height: 23px;">
                    <center><h2>目前网站总任务数：<b style="font-size:26px;color:red;"><u><?= ($thisDB->query("SELECT count(*) FROM Web_Task")->fetchColumn()) ?></u></b></h2></center>
                    <?
                    foreach ($thisDB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll() as $c) {
                        $finished = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE class={$c['name']} and status=1")->fetchColumn();
                        $unfinished = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE class={$c['name']} and status!=1")->fetchColumn();
                        $finishedpercent = ($finished/(($thisDB->query("SELECT count(*) FROM Web_Clocking WHERE class={$c['name']}")->fetchColumn())))*100;
                        $finishedpercent = number_format($finishedpercent,3);
                        $clarr[] = array("name"=>"{$c['name']}","finished"=>"$finished","unfinished"=>"$unfinished","finishedpercent"=>"$finishedpercent");
                    }
                    
                    foreach ($clarr as $key=>$val) {
                        $num1[$key] = $val['name'];
                        $num2[$key] = $val['finished'];
                        $num3[$key] = $val['unfinished'];
                        $num4[$key] = $val['finishedpercent'];
                    }
                    array_multisort($num4,SORT_DESC,$num2,$num3,$num1,$clarr);
                    
                    $i = 0;
                    foreach ($clarr as $class) {
                        $finished = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE class={$class['name']} and status=1")->fetchColumn();
                        $unfinished = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE class={$class['name']} and status!=1")->fetchColumn();
                        $finishedpercent = ($finished/(($thisDB->query("SELECT count(*) FROM Web_User WHERE class={$class['name']}")->fetchColumn())*($thisDB->query("SELECT count(*) FROM Web_Task")->fetchColumn())))*100;
                        $finishedpercent = number_format($finishedpercent,3);
                        $i++;
                    ?>
                    <p>
                        <b name="OutVipList_User" class="cid-<?= $class['name'] ?>"><?= $gname.$class['name'].'班' ?></b>
                        <center>
                            <span class="OutVipList_User" style="color:red;" id="cid-<?= $class['name'] ?>">排名：本年级<b>第<u><?= $i ?></u>名</b></span><br class="OutVipList_User" style="color:red;" id="cid-<?= $class['name'] ?>">
                            <span class="OutVipList_User" style="color:green;" id="cid-<?= $class['name'] ?>">已完成：<b><u><?= $finished ?></u>次</b></span><br class="OutVipList_User" style="color:red;" id="cid-<?= $class['name'] ?>">
                            <span class="OutVipList_User" style="color:grey;" id="cid-<?= $class['name'] ?>">未完成：<b><u><?= $unfinished ?></u>次</b></span><br class="OutVipList_User" style="color:red;" id="cid-<?= $class['name'] ?>">
                            <span class="OutVipList_User" style="color:blue;" id="cid-<?= $class['name'] ?>">总完成率：<b><u><?= $finishedpercent ?>%</u></b></span><br class="OutVipList_User" style="color:red;" id="cid-<?= $class['name'] ?>">
                            <span class="OutVipList_User" style="color:brown;" id="cid-<?= $class['name'] ?>">平均每期约<b><u><?= number_format($finished/($thisDB->query("SELECT count(*) FROM Web_Task")->fetchColumn())) ?>人</u></b>完成</span><br class="OutVipList_User" style="color:red;" id="cid-<?= $class['name'] ?>">
                        </center>
                    </p>
                    <?
                    }
                    ?>
                </div>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="Print('#OutVipList');">
                  <span>打印名单</span>
                </button>
              </div>
            </div>
            <!--div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Greens" onclick="DownLoadDT('1');">
                  <span>下载已完成表格文件</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Reds" onclick="DownLoadDT('3');">
                  <span>下载未完成表格文件</span>
                </button>
              </div>
            </div-->
            <br><br>
            <p class="login-link">
              <a href="javascript:history.go(-1);">返回上一页</a>
            </p>
          </div>
        </div>
      </div>
      <div class="copyright" style="text-align: center; position: absolute;bottom:0;width: 100%; color: #6d6d6d;line-height: 26px; font-size: 14px; margin-bottom: 15px;">
        <a href="/" style="color: #6d6d6d;"><?= $conf['title'] ?> | <?= $conf['copyright'] ?> | Copyright © 2020-<?= date("Y") ?></a></div>
    </div>
    <div class="layui-layer-shade" id="layui-layer-shade2" loadname="PageLoadingTip" times="0" style="background-color: rgb(0, 0, 0); opacity: 0.01;"></div>
    <div class="layui-layer layui-layer-dialog layui-layer-msg" id="layui-layer0" loadname="PageLoadingTip" type="dialog" times="0" showtime="0" contype="string" style="top: 295.5px; left: 592px;"><div style="" id="" class="layui-layer-content layui-layer-padding"><i class="layui-layer-ico layui-layer-ico20"></i>Loading……</div><span class="layui-layer-setwin"></span></div>
  </body>
  <script>
      var cids = <?= json_encode($cids) ?>;
  </script>

</html>