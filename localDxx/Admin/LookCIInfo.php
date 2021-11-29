<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {header("Location:Index.php");}

if ($_GET['GID'] == null || $_GET['GID'] == '0') {
    $iftid = 2;
} else {
    $grade = $DB->query("SELECT * FROM Web_Grade WHERE id='{$_GET['GID']}'")->fetch();
    if (!$grade && $_GET['GID'] != null) {
        echo '此年级不存在！正在跳转到上一页……';
        exit('<script>setTimeout(function(){history.go(-1)},2000);</script>');
    }
    //连接数据库
    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
    
    $tid = ($thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$_GET['TID']}'")->fetch())['id'];
    
    $members = $thisDB->query("SELECT * FROM Web_User ORDER BY id")->fetchAll();
    $clocking = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' ORDER BY id DESC")->fetchAll();
    $expuncis = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' and status!='3' ORDER BY id DESC")->fetchAll();
    
    $clnum = @count($thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' and status='0'")->fetchAll());
}

function get_Grade($class,$uskey) {
    global $ifmem;
    foreach ($ifmem as $im) {
        $zyim = json_encode($im,JSON_UNESCAPED_UNICODE);
        if (strpos($zyim,"$class-$uskey") !== false) {
            $result = $im["$class-$uskey"];
            break;
        } else {
            continue;
        }
    }
    return $result;
}

function get_GradeId($class,$uskey) {
    global $ifmemid;
    foreach ($ifmemid as $im) {
        $zyim = json_encode($im,JSON_UNESCAPED_UNICODE);
        if (strpos($zyim,"$class-$uskey") !== false) {
            $result = $im["$class-$uskey"];
            break;
        } else {
            continue;
        }
    }
    return $result;
}

$grades = $DB->query("SELECT * FROM Web_Grade ORDER BY id")->fetchAll();
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
          <div class="mip-reg-heading">打卡情况  <b style="color:red;cursor:pointer;" id="ClickCheckBtn" onclick="<? //($_GET['GID'] != null || $_GET['GID'] != '0' ? ($clnum == 0 ? 'ErrorMsg(\'请点击列表中的班级，进入对应班级后再进行操作\')' : 'CheckAllIfFinish()') : 'ErrorMsg(\'请选择年级后再进行操作\')') ?>ErrorMsg('请点击列表中的班级，进入对应班级后再进行操作');">查看成员打卡情况</b></div>
          <div class="mip-reg-body">
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="OpenAllCLImg();" style="float:left;width:30%;padding:10px;">
                  <span>查看概览图</span>
                </button>
                <select id="selclass" class="el-input__inner" onchange="LoadMemDiv('GradeAndTask');" style="float:left;width:130px;margin-left:5px;line-height:30px;<?= (!$grades ? 'cursor:not-allowed;" disabled="disabled' : '') ?>">
                    <?
                    if (!$grades) {
                    ?>
                    <option value="0">无年级，请添加</option>
                    <?
                    } else {
                    ?>
                    <option value="0">请选择年级</option>
                    <?
                    foreach ($grades as $cl) {
                    ?>
                    <option value="<?= $cl['id'] ?>"<?= ($cl['id'] == $_GET['GID'] ? ' selected="selected"' : '') ?>><?= $cl['name'] ?></option>
                    <?
                    }
                    ?>
                    <option value="All"<?= ('All' == $_GET['GID'] ? ' selected="selected"' : '') ?> disabled="disabled">全部年级</option>
                    <?
                    }
                    ?>
                </select>
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" style="float:left;width:25%;padding:10px;margin-left:5px;" onclick="<?= ($_GET['GID'] == null || $_GET['GID'] == '0' ? 'ErrorMsg(\'请选择年级\');' : 'LoadMemDiv(\'GradeAndTask\');') ?>">
                  <span>刷新列表</span>
                </button>
                <input type="text" oninput="SearchClass();" placeholder="请输入姓名" class="el-input__inner" name="class" style="float:left;width:100px;margin-left:5px;margin-left:32px;height:28px;line-height:10px;margin-top:10px;">
                <button type="button" class="el-button w-100 el-button--primary Bg-Greens" style="float:left;width:40%;padding:10px;margin-left:10px;margin-top:10px;height:28px;line-height:10px;">
                  <span><?= ($iftid == 2 ? '请选择年级' : '总【'.@count($expuncis).'】人打卡') ?></span>
                </button>
                 <?
                 $tasks = $DB->query("SELECT * FROM Web_Task order by id desc")->fetchAll();
                 ?>
                <center><select id="ChangeTask" onchange="ChangeTask('Grade');" class="el-input__inner" onchange="SelectClass();" style="width:130px;margin-left:5px;line-height: 20px;height: 30px;position: relative;top: 10px;"<?= (!$tasks ? ' disabled="disabled"' : '') ?>>
                    <?= (!$tasks ? '<option value="0">无打卡任务</option>' : '') ?>
                    <?
                    foreach ($tasks as $tt) {
                    ?>
                    <option value="<?= $tt['gbatch'] ?>"<?= ($tt['gbatch'] == $_GET['TID'] ? ' selected="selected"' : '') ?>><?= $tt['title'] ?></option>
                    <?
                    }
                    ?>
                </select><input type="hidden" id="TaskEndTime" value="<?= ($DB->query("SELECT * FROM Web_Task WHERE id='{$_GET['TID']}'")->fetch())['endtime'] ?>" /></center>
              </div>
            </div>
            <center id="ClassLists" style="height:220px;overflow:auto;">
            <?
            if ($iftid == 2) {
            ?>
              <div>请选择年级</div>
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
                          <th style="width:15%;">所属年级</th>
                          <th style="width:20%;">任务名</th>
                          <th style="width:10%;">所属班级</th>
                          <th style="width:25%;">打卡时间</th>
                      </tr>
                  </thead>
                  <tbody id="TbodyList" style="text-align:center;">
                      <?
                      foreach ($clocking as $cl) {
                          $task = $thisDB->query("SELECT * FROM Web_Task WHERE id='{$cl['taskbatch']}' limit 1")->fetch();
                          $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$cl['uid']}' limit 1")->fetch();
                          
                          $cis = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$cl['id']}'")->fetchAll();
                          
                          $counts = @count($cis);
                          if (strtotime($cl['addtime']) >= $task['endtime']) {
                              $unfres = '（已超时）';
                          } else {
                              $unfres = '（谎打卡）';
                          }
                      ?>
                      <tr id="<?= $cl['id'] ?>" classid="<?= $user['class'] ?>" gradeid="<?= ($_GET['GID'] == 'All' ? get_GradeId($user['class'],$user['uskey']) : $grade['id']) ?>" ifcheck="<?= ($cl['status'] == 0 ? 'false' : 'true') ?>" uncl="<?= ($cl['status'] == 2 || $cl['status'] == 3 ? 'true' : 'false') ?>" resultstatus="<?= $cl['status'] ?>">
                          <td><?= $cl['id'] ?></td>
                          <td class="CanClick" style="color:blue;font-weight:bold;"><?= $user['username'].($cl['status'] == 3 ? '<b style="color:red;">【未打卡】</b>' : ($cl['status'] == 1 ? '<b style="color:green;">【已完成】</b>' : ($cl['status'] == 2 ? '<b style="color:red;">【未完成'.$unfres.'】</b>' : ''))) ?></td>
                          <td><?= ($_GET['GID'] == 'All' ? get_Grade($user['class'],$user['uskey']) : $grade['name']) ?></td>
                          <td><?= $task['title'] ?></td>
                          <td class="CanClick" onclick="window.location.href = 'LookClassClInfo.php?GID=<?= ($_GET['GID'] == 'All' ? get_GradeId($user['class'],$user['uskey']) : $grade['id']) ?>&CID=<?= $cl['class'] ?>&TYPE=1';"><?= $cl['class'] ?>班</td>
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
  </script>
</html>