<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {header("Location:Index.php");}

$tasks = $DB->query("SELECT * FROM Web_Task ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
  
  <head>
    <title><?= $conf['title'] ?>【任务管理】</title>
    <link rel="icon" href="/Assets/Img/favicon.ico">
    <link rel="stylesheet" href="/Assets/Css/Index.css"></head>
  
  <body>
    <div class="main">
      <div id="appLogin">
        <div class="logins position" id="MainIndex" style="width: 700px;margin-left: -350px;height: 530px;">
          <div class="mip-reg-logo">
          <div class="mip-reg-heading">任务管理
              <button type="button" class="el-button w-100 el-button--primary Bg-Greens" onclick="AdminLookTasksInfo();" style="width:15%;height:30px;">
                <span>查看概览图</span>
              </button>
          </div>
          <div class="mip-reg-body">
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="AddAllTask();" style="float:left;width:30%;padding:10px;">
                  <span>添加任务</span>
                </button>
                <input type="text" oninput="SearchClass();" placeholder="请输入任务名称" class="el-input__inner" name="class" style="float:left;width:130px;margin-left:5px;">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" style="float:left;width:25%;padding:10px;margin-left:5px;" onclick="LoadDiv();">
                  <span>刷新列表</span>
                </button>
              </div>
            </div>
            <center id="ClassLists" style="height:270px;overflow:auto;">
            <?
            if (!$tasks) {
            ?>
              <div>暂无任务信息</div>
            <?
            } else {
            ?>
              <table style="width:100%;">
                  <thead>
                      <tr>
                          <th style="width:5%;"></th>
                          <th style="width:3%;">ID</th>
                          <th style="width:12%;">任务名</th>
                          <th style="width:17%;">总打卡情况</th>
                          <th style="width:8%;">完成率</th>
                          <th style="width:19%;">添加时间</th>
                          <th style="width:19%;">结束时间</th>
                          <th style="width:17%;">操作</th>
                      </tr>
                  </thead>
                  <tbody id="TbodyList" style="text-align:center;">
                      <?
                      foreach ($tasks as $t) {
                          foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                              //连接数据库
                              $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
                              
                              $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}' limit 1")->fetch();
                              
                              foreach ($thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status!='3'")->fetchAll() as $c) {
                                  $cis[] = $c;
                              }
                              
                              //获取谎打卡人数
                              foreach ($thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='2'")->fetchAll() as $hc) {
                                  $hcs[] = $hc;
                              }
                              
                              //获取用户数量
                              foreach ($thisDB->query("SELECT * FROM Web_User WHERE username!='Administrator'")->fetchAll() as $u) {
                                  $us[] = $u;
                              }
                              
                              /*//判断打卡是否结束并统计未打卡人数
                              if ($thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='0'")->fetchColumn() == 0) {
                                  $rl = 'yes';
                              } else {
                                  foreach ($thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='3'")->fetchAll() as $unclmem) {
                                      $unclmemc[] = $unclmem;
                                  }
                              }*/
                          }
                          $counts = @count($cis);
                          $uscounts = @count($us);
                          $hcounts = @count($hcs);
                          //$unclmemcounts = @count($unclmemc);
                          
                          if ($counts == null) {
                              $res = '暂无人打卡';
                          } else {
                              if ($thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='3'")->fetchColumn() != 0) {
                                  foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                                      //连接数据库
                                      $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
                              
                                      $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}' limit 1")->fetch();
                                      foreach ($thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='3'")->fetchAll() as $unc) {
                                          $uncc[] = $unc;
                                      }
                                  }
                                  $unclmems = @count($uncc);
                              } else {
                                  $unclmems = $uscounts - $counts;
                              }
                              /*if (time() >= $t['endtime']) {
                                  $counts = $counts - $hcounts;
                                  $res = '<b class="CanClick" style="'.($counts == null ? 'color:grey;' : 'color:blue;').'" onclick="ToHref(\'LookCIInfo.php?TID='.$t['id'].'&TYPE=1\');">【'.$counts.'】</b>人完成打卡<br><b class="CanClick" style="'.($hcounts == null ? 'color:grey;' : 'color:orange;').'" onclick="ToHref(\'LookCIInfo.php?TID='.$t['id'].'&TYPE=1\');">【'.$hcounts.'】</b>人谎打卡<br><b style="color:red;" onclick="ToHref(\'LookCIInfo.php?TID='.$t['id'].'&TYPE=1\');">【'.$unclmems.'】</b>人未打卡';
                              } else {*/
                                  $res = '<b class="CanClick" style="'.($counts == null ? 'color:grey;' : 'color:blue;').'" onclick="ToHref(\'LookCIInfo.php?TID='.$t['id'].'&TYPE=1\');">【'.$counts.'】</b>人已打卡<br><b style="color:red;" onclick="ToHref(\'LookCIInfo.php?TID='.$t['id'].'&TYPE=1\');">【'.$unclmems.'】</b>人未打卡';
                              //}
                          }
                          
                      ?>
                      <tr id="<?= $t['id'] ?>" stid="<?= $t['stid'] ?>">
                          <td><input type="checkbox" name="SelectInfoList" value="<?= $t['id'] ?>" /></td>
                          <td><?= $t['id'] ?></td>
                          <td><?= $t['title'] ?></td>
                          <td><?= $res ?></td>
                          <td style="color:<?= ($counts == 0 ? 'red' : 'green') ?>;"><?= ($counts == 0 ? '无人打卡' : @substr(($counts/@count($us))*100,0,6).'%') ?></td>
                          <td><?= date("Y-m-d H:i:s",$t['addtime']) ?></td>
                          <td><?= date("Y-m-d H:i:s",$t['endtime']) ?></td>
                          <td>
                              <button class="ClickBT Bg-Greens" onclick="window.location.href = 'TaskSettle.php?TID=<?= $t['id'] ?>';">结算</button>
                              <button class="ClickBT Bg-Blues" onclick="AddAllTask('<?= $t['id'] ?>','Edit');">编辑</button>
                              <button class="ClickBT Bg-Reds" onclick="DeleteAllTask('<?= $t['id'] ?>');">删除</button>
                          </td>
                      </tr>
                      <?
                          $cis = null;
                          $us = null;
                          $hcs = null;
                          $uncc = null;
                      }
                      ?>
                  </tbody>
              </table>
            <?
            }
            ?>
            </center>
            <br><br><br><br>
            <div style="width:100%;margin-left:10px;">
                <button class="ClickBT Bg-Blues" style="height:20px;padding:0 14px;float:left;" id="CheckBtn" onclick="DoSelectAllLists();">全选</button>
                <select id="AjaxDoType" onchange="AjaxDoType();" style="height:20px;float: left;margin-left: 20px;margin-top: 1.5px;">
                    <option value="0">请选择批量操作方式</option>
                    <option value="DeleteSomeAllTask">批量删除任务</option>
                </select>
            </div>
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
  <script type="text/javascript">
  </script>
</html>