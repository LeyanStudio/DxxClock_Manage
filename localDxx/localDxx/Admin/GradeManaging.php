<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {header("Location:Index.php");}

$grades = $DB->query("SELECT * FROM Web_Grade ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html>
  
  <head>
    <title><?= $conf['title'] ?>【年级管理】</title>
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
          <div class="mip-reg-heading">年级管理
              <button type="button" class="el-button w-100 el-button--primary Bg-Greens" onclick="DownloadOrderedImg('年级排序结果');" style="width:18%;height:30px;">
                <span>下载完成率排序图</span>
              </button>
          </div>
          <div class="mip-reg-body">
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="AddGrade();" style="float:left;width:30%;padding:10px;">
                  <span>添加年级</span>
                </button>
                <input type="text" oninput="SearchClass();" placeholder="请输入年级号" class="el-input__inner" name="class" style="float:left;width:130px;margin-left:5px;">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" style="float:left;width:25%;padding:10px;margin-left:5px;" onclick="ChangeTask();">
                  <span>刷新列表</span>
                </button>
              </div>
            </div>
            <center id="ClassLists" style="height:270px;overflow:auto;">
            <?
            if (!$grades) {
            ?>
              <div>暂无年级信息</div>
            <?
            } else {
            ?>
              <table style="width:100%;" id="TbodyTableList">
                  <thead>
                      <tr>
                          <th style="width:5%;"></th>
                          <th style="width:5%;">ID</th>
                          <th style="width:8%;">年级名</th>
                          <th style="width:4%;">判断名</th>
                          <th style="width:10%;">班级数量</th>
                          <th style="width:10%;">成员数量</th>
                          <th style="width:20%;">打卡任务情况
                          <?
                          $tasks = $DB->query("SELECT * FROM Web_Task order by id desc")->fetchAll();
                          ?>
                              <select id="ChangeTask" onchange="ChangeTask();"<?= (!$tasks ? 'disabled="disabled"' : '') ?>>
                                  <?= (!$tasks ? '<option value="0">无打卡任务</option>' : '') ?>
                                  <?
                                  foreach ($tasks as $tt) {
                                  ?>
                                  <option value="<?= $tt['gbatch'] ?>"<?= ($tt['gbatch'] == $_GET['TID'] ? ' selected="selected"' : '') ?>><?= $tt['title'] ?></option>
                                  <?
                                  }
                                  ?>
                              </select>
                          </th>
                          <th style="width:8%;color:blue;cursor:pointer;" id="thorderwcl" onclick="OrderMsgBy('6','thorderwcl','无人打卡');" title="点击我进行排序">完成率</th>
                          <th style="width:15%;">域名</th>
                          <th style="width:15%;">操作</th>
                          <!--th style="width:10%;">其它</th-->
                      </tr>
                  </thead>
                  <tbody id="TbodyList" style="text-align:center;">
                      <?
                      foreach ($grades as $g) {
                          $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
                          
                          if ($_GET['TID'] == null) {
                              $task = $thisDB->query("SELECT * FROM Web_Task ORDER BY id DESC limit 1")->fetch();
                          } else {
                              $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$_GET['TID']}' limit 1")->fetch();
                          }
                          $tid = $task['id'];
                          if (!$task) {
                              $tres = '无此打卡任务';
                          } else {
                              $cis = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' and status!='3'")->fetchAll();
                              $cisc = count($cis);
                              if (!$cisc || $cisc == 0) {
                                  $tres = '本年级无人打卡';
                              } else {
                                  /*if (time() >= $task['endtime']) {
                                    if ($thisDB->query("SELECT count(*) FROM Web_Clocking WHERE status='0'")->fetchColumn() == 0) {
                                        $unclmems = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$tid}' and status='3'")->fetchColumn();
                                    } else {
                                        $unclmems = $thisDB->query("SELECT count(*) FROM Web_User WHERE username!='Administrator'")->fetchColumn() - $cisc;
                                    }
                                      
                                    $hcs = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='2'")->fetchColumn();
                                      
                                    $tres = '<b style="color:blue;">【'.($cisc - $hcs).'】</b>人已打卡<br><b style="color:orange;">【'.$hcs.'】</b>人谎打卡<br><b style="color:red;">【'.$unclmems.'】</b>人未打卡';
                                    $cs = null;
                                  } else {*/
                                    if ($thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$tid}' and status='3'")->fetchColumn() != 0) {
                                        $unclmems = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$tid}' and status='3'")->fetchColumn();
                                    } else {
                                        $unclmems = $thisDB->query("SELECT count(*) FROM Web_User WHERE username!='Administrator'")->fetchColumn() - $cisc;
                                    }
                                    $tres = '<b style="color:blue;">【'.$cisc.'】</b>人已打卡<br><b style="color:red;">【'.$unclmems.'】</b>人未打卡';
                                    $cs = null;
                                  //}
                              }
                          }
                          $gname = @file_get_contents('../Txts/'.$g['orgid'].'.txt');
                      ?>
                      <tr id="<?= $g['id'] ?>" gid="<?= $g['gradeid'] ?>" oid="<?= $g['orgid'] ?>">
                          <td><input type="checkbox" name="SelectInfoList" value="<?= $g['id'] ?>" /></td>
                          <td><?= $g['id'] ?></td>
                          <td><?= $gname ?></td>
                          <td><?= $g['name'] ?></td>
                          <td><b class="CanClick" onclick="window.location.href = 'LookClasses.php?GID=<?= $g['id'] ?>&TYPE=1';"><?
                              $classes = $thisDB->query("SELECT * FROM Web_Class")->fetchAll();
                              echo (!$classes ? '0' : count($classes))
                          ?>个
                          </b></td>
                          <td><b class="CanClick" onclick="OpenNewWindow('LookClassesANDMembers.php?GID=<?= $g['id'] ?>','年级【ID-<?= $g['id'] ?>】');"><?
                              $users = $thisDB->query("SELECT * FROM Web_User WHERE username!='Administrator'")->fetchAll();
                              echo (!$users ? '0' : count($users))
                          ?>人
                          </b></td>
                          <td><?= $tres ?></td>
                          <td style="color:<?= (!$cisc || $cisc == 0 ? 'red' : 'green') ?>;"><?= (!$cisc || $cisc == 0 ? '无人打卡' : @substr(($cisc/@count($users))*100,0,6).'%') ?></td>
                          <td><a href="http://<?= $g['gradeid'] ?>_<?= $_SERVER['HTTP_HOST'] ?>" target="_blank"><?= $g['gradeid'] ?>_<?= $_SERVER['HTTP_HOST'] ?></a></td>
                          <td>
                              <button class="ClickBT Bg-Greens" style="height: 20px;" onclick="LookGradeInfo('<?= $g['id'] ?>','<?= $g['name'] ?>');">查看详细信息</button>
                              <button class="ClickBT Bg-Blues" style="height: 20px;margin-top: 6px;" onclick="SetAdminPassword('<?= $g['id'] ?>');">重置管理密码</button><br>
                              <button class="ClickBT Bg-Primary" style="height: 20px;margin-top: 6px;" onclick="EditGrade('<?= $g['id'] ?>');">编辑信息</button>
                              <button class="ClickBT Bg-Blues" style="height: 20px;margin-top: 6px;" onclick="window.open('DownloadMems.php?GID=<?= $g['id'] ?>');">下载名单</button>
                              <button class="ClickBT Bg-Reds" style="height: 20px;margin-top: 6px;" onclick="window.open('OutVips.php?GID=<?= $g['id'] ?>');">未完成统计</button>
                              <button class="ClickBT Bg-Reds" style="height: 20px;margin-top: 6px;" onclick="window.open('ClassOrder.php?GID=<?= $g['id'] ?>');">班级排序明细</button>
                              <button class="ClickBT Bg-Reds" style="height: 20px;margin-top: 6px;" onclick="DeleteGrade('<?= $g['id'] ?>');">删除</button>
                          </td>
                          <!--td>
                              <?
                              $grade = @str_replace('高','',@str_replace('级','',$g['name']));
                              if (($grade+1) == @date("Y")) {
                                  $file = @file_get_contents('../Txts/classchanged/'.$g['name'].'.txt');
                              ?>
                              <b>[<?= @count(@json_decode($file,true)) ?>]</b>人已完成换班操作
                              <?
                              } else {
                              ?>
                              无
                              <?
                              } 
                              ?>
                          </td-->
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
                    <option value="SetAdminPassword">批量重置管理密码</option>
                    <option value="DeleteGrade">批量删除年级</option>
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