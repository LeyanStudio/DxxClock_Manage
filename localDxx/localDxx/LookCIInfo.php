<?
include("./Includes/Common.php");
if (($userrow['status'] == 1 || $userrow['status'] == 2) && $islogin2 == 1) {} else {header("Location:/Home.php");}

$tid = $_GET['TID'];

if ($_GET['CID'] == null || $_GET['CID'] == '0') {
    $iftid = 2;
}

if ($_GET['CID'] == 'All') {
    $clocking = $DB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' ORDER BY id DESC")->fetchAll();
    $expuncis = $DB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' and status!='3' ORDER BY id DESC")->fetchAll();
    $clnum = $DB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$tid}' and status='0'")->fetchColumn();
} else {
    if ($userrow['status'] == 2) {
        $clnum = $DB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$tid}' and status='0'")->fetchColumn();
    }
    
    if ($_GET['TID'] == null) {
        $tid = ($DB->query("SELECT * FROM Web_Task order by id desc limit 1")->fetch())['id'];
    } else {
        $tid = $_GET['TID'];
    }
    
    if ($_GET['CID'] == null && $userrow['status'] == 2) {
        $clocking = $DB->query("SELECT * FROM Web_Clocking WHERE class='{$userrow['class']}' and taskbatch='{$tid}' ORDER BY id DESC")->fetchAll();
        $expuncis = $DB->query("SELECT * FROM Web_Clocking WHERE class='{$userrow['class']}' and taskbatch='{$tid}' and status!='3' ORDER BY id DESC")->fetchAll();
    } else {
        $clocking = $DB->query("SELECT * FROM Web_Clocking WHERE class='{$_GET['CID']}' and taskbatch='{$tid}' ORDER BY id DESC")->fetchAll();
        $expuncis = $DB->query("SELECT * FROM Web_Clocking WHERE class='{$_GET['CID']}' and taskbatch='{$tid}' and status!='3' ORDER BY id DESC")->fetchAll();
    }
}
$classes = $DB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html>
  
  <head>
    <title><?= $conf['title'] ?>【打卡情况管理】</title>
    <link rel="icon" href="/Assets/Img/favicon.ico">
    <link rel="stylesheet" href="/Assets/Css/Index.css"></head>
    <style>
        tr {
            height: 20px;
        }
        tr#TrTitle td center div {
            font-weight: bold;
            background: linear-gradient(to left,#1645F3,#1681F3,#16BDF3);
            /*width: 20%;*/
            width: 30%;
            padding: 5px 0;
            position: absolute;
            height: 15px;
            color: white;
            line-height: 16px;
            transform: translate(0, -12px);
            border-radius: 0 10px 10px 0;
            box-shadow: 2px 2px 4px #165DF3;
            cursor: pointer;
        }
        a#DownloadImg {
            width: 100%;
            padding: 20px 35%;
            font-weight: bold;
            font-size: 16px;
            border: 1px solid #164CF3;
            margin-top: 10px;
        }
        a#DownloadImg:hover {
            color: #16ACF3;
        /*tr#TrTitle td center div:after {
            position: absolute;
            content: '';
            width: 30%;
            height: 30px;
            top: -2px;
            left: 97%;
            background: linear-gradient(to right,#164DF2,#16BDF3);
            filter: blur(2px);
        }*/
        }
    </style>
  <body>
    <div class="main">
      <div id="appLogin">
        <div class="logins position" id="MainIndex" style="width: 700px;margin-left: -350px;height: 530px;">
          <div class="mip-reg-logo">
          <div class="mip-reg-heading">打卡情况<?= ($userrow['status'] == 1 ? '  <b style="color:red;cursor:pointer;" id="ClickCheckBtn" onclick="'.('All' == $_GET['CID'] ? ($clnum == '0' ? 'layer.msg(\'正在生成结果……\',{icon: 20,time: 2000},function(){FFFileUpload(\'yes\');})' : 'CheckIfFinish()') : 'ErrorMsg(\'请选择【全部成员】后再进行操作\')').';">查看成员打卡情况</b>' : '') ?><?= ($userrow['status'] == 2 ? '  <b style="color:red;cursor:pointer;" id="ClickCheckBtn" onclick="'.($clnum == '0' ? 'layer.msg(\'正在获取结果……\',{icon: 20,time: 3000},function(){FFFileUpload(\'yes\');})' : 'ErrorMsg(\'总管理还未进行验证，请等待！\')').';">查看成员打卡情况</b>' : '') ?>  <b style="color:green;cursor:pointer;" id="ClickCheckBtn" onclick="StatisticsCL();">一键统计完成情况</b></div>
          <div class="mip-reg-body">
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="Open<?= ($userrow['status'] == 2 ? 'My' : '') ?>CLImg();" style="float:left;width:30%;padding:10px;">
                  <span>查看概览图</span>
                </button>
                <select id="selclass" class="el-input__inner" onchange="LoadMemDiv('AndTask');" style="float:left;width:130px;margin-left:5px;line-height:30px;<?= (!$classes ? 'cursor:not-allowed;" disabled="disabled' : '') ?>">
                    <?
                    if (!$classes) {
                    ?>
                    <option value="0">无班级，请添加</option>
                    <?
                    } elseif ($userrow['status'] == 2) {
                    ?>
                    <option value="<?= ($_GET['CID'] == null && $userrow['status'] == 2 ? $userrow['class'] : $_GET['CID'] ) ?>" selected="selected"><?= $conf['gradename'] ?><?= ($_GET['CID'] == null && $userrow['status'] == 2 ? $userrow['class'] : $_GET['CID'] ) ?>班</option>
                    <?
                    } else {
                    ?>
                    <option value="0">请选择班级</option>
                    <?
                    foreach ($classes as $cl) {
                    ?>
                    <option value="<?= $cl['name'] ?>"<?= ($cl['name'] == $_GET['CID'] ? ' selected="selected"' : '') ?>><?= $conf['gradename'] ?><?= $cl['name'] ?>班</option>
                    <?
                    }
                    if ($userrow['status'] == 1) {
                    ?>
                    <option value="All"<?= ('All' == $_GET['CID'] ? ' selected="selected"' : '') ?>><?= $conf['gradename'] ?>全部成员</option>
                    <?
                    }
                    }
                    ?>
                </select>
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" style="float:left;width:25%;padding:10px;margin-left:5px;" onclick="<?= ($_GET['CID'] == null && $userrow['status'] == 1 ? 'ErrorMsg(\'请选择班级\');' : 'LoadMemDiv(\'tid\');') ?>">
                  <span>刷新列表</span>
                </button>
                <input type="text" oninput="SearchClass();" placeholder="请输入姓名" class="el-input__inner" name="class" style="float:left;width:100px;margin-left:5px;margin-left:32px;height:28px;line-height:10px;margin-top:10px;">
                <button type="button" class="el-button w-100 el-button--primary Bg-Greens" style="float:left;width:40%;padding:10px;margin-left:10px;margin-top:10px;height:28px;line-height:10px;">
                  <span><?= ($iftid == 2 ? '请选择班级' : '总【'.count($expuncis).'】人打卡') ?></span>
                </button>
                 <?
                 $tasks = $DB->query("SELECT * FROM Web_Task order by id desc")->fetchAll();
                 ?>
                <center><select id="ChangeTask" onchange="ChangeTask('Member');" class="el-input__inner" onchange="SelectClass();" style="width:130px;margin-left:5px;line-height: 20px;height: 30px;position: relative;top: 10px;"<?= (!$tasks ? ' disabled="disabled"' : '') ?>>
                    <?= (!$tasks ? '<option value="0">无打卡任务</option>' : '') ?>
                    <?
                    foreach ($tasks as $tt) {
                    ?>
                    <option value="<?= $tt['id'] ?>"<?= ($tt['id'] == $_GET['TID'] ? ' selected="selected"' : '') ?>><?= $tt['title'] ?></option>
                    <?
                    }
                    ?>
                </select><input type="hidden" id="TaskEndTime" value="<?= ($DB->query("SELECT * FROM Web_Task WHERE id='{$_GET['TID']}'")->fetch())['endtime'] ?>" /></center>
              </div>
            </div>
            <center id="ClassLists" style="height:220px;overflow:auto;">
            <?
            $members = $DB->query("SELECT * FROM Web_User WHERE class='{$_GET['CID']}' ORDER BY id")->fetchAll();
            if (($iftid == 2) && $userrow['status'] == 1) {
            ?>
              <div>请选择班级</div>
            <?
            } elseif (!$members) {
            ?>
              <div>暂无成员信息</div>
            <?
            } elseif (!$clocking) {
            ?>
              <div>暂无打卡记录</div>
            <?
            } else {
            ?>
              <table style="width:100%;">
                  <thead>
                      <tr>
                          <th style="width:5%;">ID</th>
                          <th style="width:25%;">打卡者</th>
                          <th style="width:25%;">任务名</th>
                          <th style="width:20%;">所属班级</th>
                          <th style="width:25%;">打卡时间</th>
                      </tr>
                  </thead>
                  <tbody id="TbodyList" style="text-align:center;">
                      <?
                      foreach ($clocking as $cl) {
                          $task = $DB->query("SELECT * FROM Web_Task WHERE id='{$cl['taskbatch']}' limit 1")->fetch();
                          $user = $DB->query("SELECT * FROM Web_User WHERE id='{$cl['uid']}' limit 1")->fetch();
                          if ($userrow['status'] == 1) {
                              $cis = $DB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$cl['id']}'")->fetchAll();
                          } else {
                              foreach ($DB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$cl['id']}'")->fetchAll() as $c) {
                                  if ((($DB->query("SELECT * FROM Web_User WHERE id='{$c['uid']}' limit 1")->fetch())['class']) == $userrow['class']) {
                                      $cis[] = $c;
                                  }
                              }
                          }
                          $counts = @count($cis);
                          if (strtotime($cl['addtime']) >= $task['endtime']) {
                              $unfres = '（已超时）';
                          } else {
                              $unfres = '（谎打卡）';
                          }
                      ?>
                      <tr id="<?= $cl['id'] ?>" ifcheck="<?= ($cl['status'] == 0 ? 'false' : 'true') ?>" uncl="<?= ($cl['status'] == 2 || $cl['status'] == 3 ? 'true' : 'false') ?>" resultstatus="<?= $cl['status'] ?>">
                          <td><?= $cl['id'] ?></td>
                          <td class="CanClick" style="color:blue;font-weight:bold;"><?= $user['username'].($cl['status'] == 3 ? '<b style="color:red;">【未打卡】</b>' : ($cl['status'] == 1 ? '<b style="color:green;">【已完成】</b>' : ($cl['status'] == 2 ? '<b style="color:red;">【未完成'.$unfres.'】</b>' : ''))) ?></td>
                          <td><?= $task['title'] ?></td>
                          <td><?= $conf['gradename'] ?><?= $cl['class'] ?>班</td>
                          <td time="<?= strtotime($cl['addtime']) ?>"><?= ($cl['status'] == 3 ? '<font color="red">未打卡</font>' : $cl['addtime']) ?></td>
                      </tr>
                      <?
                      }
                      ?>
                  </tbody>
              </table>
            <?
            }
            ?>
            </center>
            <br><br><br><br>
            <p class="login-link">
              <a href="javascript:history.go(-1);">返回上一页</a>
            </p>
          </div>
        </div>
      </div>
    <div class="layui-layer-shade" id="layui-layer-shade2" loadname="PageLoadingTip" times="0" style="background-color: rgb(0, 0, 0); opacity: 0.01;"></div>
    <div class="layui-layer layui-layer-dialog layui-layer-msg" id="layui-layer0" loadname="PageLoadingTip" type="dialog" times="0" showtime="0" contype="string" style="top: 295.5px; left: 592px;"><div style="" id="" class="layui-layer-content layui-layer-padding"><i class="layui-layer-ico layui-layer-ico20"></i>Loading……</div><span class="layui-layer-setwin"></span></div>
    </div>
  </body>
  <script src="http://nchat.kayanxin.cn/Assets/Js/jquery.js"></script>
  <script src="http://nchat.kayanxin.cn/Assets/Js/layer/layer.js"></script>
  <script src="/Assets/Js/echarts.min.js"></script>
  <script src="/Assets/Js/Main.js"></script>
  <script src="/Assets/Js/html2canvas.js"></script>
  <script type="text/javascript">
  <?
  if($_GET['TYPE'] == 1) {
  ?>
  history.replaceState(null, null, 'LookCIInfo.php');
  <?
  }
  ?>
  function StatisticsCL() {
      var loading = layer.msg('命令执行中，请等待处理！',{
          icon: 20,
          time: 0,
          shade: 0.01
      });
      
      $.ajax({
        type: 'GET',
        url: 'http://l-ci.kayanxin.cn/Cron.php?Key=e35428174714229c5d8c510b2e5f88ad',
        dataType: 'html',
        success: function(data) {
            layer.close(loading);
            CorrectMsg('处理完毕！[如果刷新未变化，多半是在向大学习官方服务器请求数据时超时了，请等待几分钟后再尝试]');
            setTimeout(function() {
                history.go(0);
            },500);
        },
        /*error: function(XMLHttpRequest, textStatus, errorThrown) {
            layer.close(loading);
            ErrorMsg('处理失败，错误代码：'+XMLHttpRequest+'-'+textStatus+'-'+errorThrown);
        }*/
    });
  }
  </script>
</html>