<?
include("./Includes/Common.php");
if ($userrow['status'] == 1 && $islogin2 == 1) {} else {header("Location:/Home.php");}

$classes = $DB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html>
  
  <head>
    <title><?= $conf['title'] ?>【班级管理】</title>
    <link rel="icon" href="/Assets/Img/favicon.ico">
    <link rel="stylesheet" href="/Assets/Css/Index.css">
    <style>
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
        }
    </style>
  </head>
  
  <body>
    <div class="main">
      <div id="appLogin">
        <div class="logins position" id="MainIndex" style="width: 700px;margin-left: -350px;height: 530px;">
          <div class="mip-reg-logo">
          <div class="mip-reg-heading">班级管理
              <button type="button" class="el-button w-100 el-button--primary Bg-Greens" onclick="DownloadOrderedImg('班级排序结果','class');" style="width:18%;height:30px;">
                <span>下载完成率排序图</span>
              </button>
          </div>
          <div class="mip-reg-body">
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="OpenAddClass();" style="float:left;width:30%;padding:10px;">
                  <span>添加班级</span>
                </button>
                <input type="text" oninput="SearchClass('2');" placeholder="请输入班级号" class="el-input__inner" name="class" style="float:left;width:130px;margin-left:5px;">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" style="float:left;width:25%;padding:10px;margin-left:5px;" onclick="ChangeTask();">
                  <span>刷新列表</span>
                </button>
              </div>
            </div>
            <center id="ClassLists" style="height:270px;overflow:auto;">
            <?
            if (!$classes) {
            ?>
              <div>暂无班级信息</div>
            <?
            } else {
            ?>
              <table style="width:100%;" id="TbodyTableList">
                  <thead>
                      <tr>
                          <th style="width:5%;"></th>
                          <th style="width:5%;">ID</th>
                          <th style="width:15%;">班级名</th>
                          <th style="width:8%;">成员数量</th>
                          <th style="width:9%;">团支书姓名</th>
                          <th style="width:20%;">打卡任务情况
                          <?
                          $tasks = $DB->query("SELECT * FROM Web_Task order by id desc")->fetchAll();
                          ?>
                              <select id="ChangeTask" onchange="ChangeTask();"<?= (!$tasks ? 'disabled="disabled"' : '') ?>>
                                  <?= (!$tasks ? '<option value="0">无打卡任务</option>' : '') ?>
                                  <?
                                  foreach ($tasks as $tt) {
                                  ?>
                                  <option value="<?= $tt['id'] ?>"<?= ($tt['id'] == $_GET['TID'] ? ' selected="selected"' : '') ?>><?= $tt['title'] ?></option>
                                  <?
                                  }
                                  ?>
                              </select>
                          </th>
                          <th style="width:8%;color:blue;cursor:pointer;" id="thorderwcl" onclick="OrderMsgBy('6','thorderwcl','无人打卡');" title="点击我进行排序">完成率</th>
                          <th style="width:30%;">操作</th>
                      </tr>
                  </thead>
                  <tbody id="TbodyList" style="text-align:center;">
                      <?
                      foreach ($classes as $c) {
                          $ms = $DB->query("SELECT * FROM Web_User WHERE class='{$c['name']}'")->fetchAll();
                          $counts = count($ms);
                          $user = $DB->query("SELECT * FROM Web_User WHERE id='{$c['lid']}' limit 1")->fetch();
                          
                          if ($_GET['TID'] == null) {
                              $task = $DB->query("SELECT * FROM Web_Task ORDER BY id DESC limit 1")->fetch();
                          } else {
                              $task = $DB->query("SELECT * FROM Web_Task WHERE id='{$_GET['TID']}' limit 1")->fetch();
                          }
                          $tid = $task['id'];
                          if (!$task) {
                              $tres = '无此打卡任务';
                          } else {
                              $cis = $DB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' and status!='3'")->fetchAll();
                              foreach ($cis as $ci) {
                                  if ($DB->query("SELECT * FROM Web_User WHERE id='{$ci['uid']}' and class='{$c['name']}' limit 1")->fetch()) {
                                      $cs[] = $ci;
                                  }
                              }
                              $cisc = @count($cs);
                              if (!$cisc || $cisc == 0) {
                                  $tres = '本班级无人打卡';
                              } else {
                                  /*if (time() >= $task['endtime']) {
                                      if ($DB->query("SELECT count(*) FROM Web_Clocking WHERE class='{$c['name']}' and status='0'")->fetchColumn() == 0) {
                                          $unclmems = $DB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$tid}' and class='{$c['name']}' and status='3'")->fetchColumn();
                                      } else {
                                          $unclmems = $DB->query("SELECT count(*) FROM Web_User WHERE class='{$c['name']}' and username!='Administrator'")->fetchColumn() - $cisc;
                                      }
                                      
                                      $hcs = $DB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$tid}' and class='{$c['name']}' and status='2'")->fetchColumn();
                                      
                                      $tres = '<b style="color:blue;">【'.($cisc - $hcs).'】</b>人已打卡<br><b style="color:orange;">【'.$hcs.'】</b>人谎打卡<br><b style="color:red;">【'.$unclmems.'】</b>人未打卡';
                                      
                                      $cs = null;
                                      
                                      $cis = $DB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' and status='1'")->fetchAll();
                                      foreach ($cis as $ci) {
                                          if ($DB->query("SELECT * FROM Web_User WHERE id='{$ci['uid']}' and class='{$c['name']}' limit 1")->fetch()) {
                                              $cs[] = $ci;
                                          }
                                      }
                                      
                                      $cisc = @count($cs);
                                      
                                      $cs = null;
                                  } else {*/
                                      if ($DB->query("SELECT count(*) FROM Web_Clocking WHERE class='{$c['name']}' and taskbatch='{$tid}' and status='3'")->fetchColumn() != 0) {
                                          $unclmems = $DB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$tid}' and class='{$c['name']}' and status='3'")->fetchColumn();
                                      } else {
                                          $unclmems = $DB->query("SELECT count(*) FROM Web_User WHERE class='{$c['name']}' and username!='Administrator'")->fetchColumn() - $cisc;
                                      }
                                      $tres = '<b style="color:blue;">【'.$cisc.'】</b>人已打卡<br><b style="color:red;">【'.$unclmems.'】</b>人未打卡';
                                      $cs = null;
                                  //}
                              }
                          }
                      ?>
                      <tr id="<?= $c['id'] ?>" cname="<?= $c['name'] ?>">
                          <td><input type="checkbox" name="SelectInfoList" value="<?= $c['id'] ?>" /></td>
                          <td><?= $c['id'] ?></td>
                          <td><?= $conf['gradename'].$c['name'] ?>班</td>
                          <td class="CanClick" onclick="window.location.href = '/LookMembers.php?CID=<?= $c['name'] ?>&TYPE=1';"><?= ($counts == null ? '0' : $counts) ?>人</td>
                          <td id="<?= $c['name'] ?>-lid"><?= ($c['lid'] == 0 ? '暂无' : '<b style="color:red;">'.$user['username'].'</b>') ?></td>
                          <td><?= $tres ?></td>
                          <td style="color:<?= (!$cisc || $cisc == 0 ? 'red' : 'green') ?>;"><?= (!$cisc || $cisc == 0 ? '无人打卡' : substr(($cisc/($DB->query("SELECT count(*) FROM Web_User WHERE class='{$c['name']}' and username!='Administrator'")->fetchColumn()))*100,0,6).'%') ?></td>
                          <td>
                              <button class="ClickBT Bg-Blues" style="height: 20px;" onclick="SetAdmin('<?= $c['name'] ?>');">设置团支书</button>
                              <button class="ClickBT Bg-Greens" style="height: 20px;" onclick="LookInfo('<?= $c['name'] ?>');">查看详细信息</button><br>
                              <button class="ClickBT Bg-Primary" style="height: 20px;margin-top: 6px;" onclick="EditClass('<?= $c['id'] ?>');">编辑班级信息</button>
                              <button class="ClickBT Bg-Reds" style="height: 20px;margin-top: 6px;" onclick="Delete('<?= $c['id'] ?>');">删除</button>
                          </td>
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
            <div style="width:100%;margin-left:10px;">
                <button class="ClickBT Bg-Blues" style="height:20px;padding:0 14px;float:left;" id="CheckBtn" onclick="DoSelectAllLists();">全选</button>
                <select id="AjaxDoType" onchange="AjaxDoType();" style="height:20px;float: left;margin-left: 20px;margin-top: 1.5px;">
                    <option value="0">请选择批量操作方式</option>
                    <option value="Delete">批量删除班级</option>
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
  <script src="/Assets/Js/Main.js"></script>
  <script src="/Assets/Js/html2canvas.js"></script>
  <script type="text/javascript">
  </script>
</html>