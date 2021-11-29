<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {header("Location:Index.php");}

$grades = $DB->query("SELECT * FROM Web_Grade ORDER BY id")->fetchAll();

$grade = $DB->query("SELECT * FROM Web_Grade WHERE gradeid='{$_GET['GID']}'")->fetch();
if (!$grade && $_GET['GID'] != null) {
    echo '此年级不存在！正在跳转到上一页……';
    exit('<script>setTimeout(function(){history.go(-1)},2000);</script>');
}

//连接数据库
$thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
?>
<!DOCTYPE html>
<html>
  
  <head>
    <title><?= $conf['title'] ?>【成员管理】</title>
    <link rel="icon" href="/Assets/Img/favicon.ico">
    <link rel="stylesheet" href="/Assets/Css/Index.css"></head>
  
  <body>
    <div class="main">
      <div id="appLogin">
        <div class="logins position" id="MainIndex" style="width: 700px;margin-left: -350px;height: 530px;">
          <div class="mip-reg-logo">
          <div class="mip-reg-heading">成员管理<?= ($userrow['status'] == 1 ? '  <b style="color:blue;cursor:pointer;" onclick="ListAddMem();">批量添加</b>' : '') ?></div>
          <div class="mip-reg-body">
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="<?= ($_GET['GID'] == null || $_GET['GID'] == '0' && $userrow['status'] == 1 ? 'ErrorMsg(\'请选择年级\');' : 'OpenAdminAddMember();') ?>" style="float:left;width:30%;padding:10px;">
                  <span>添加成员</span>
                </button>
                <select class="el-input__inner" id="selclass" onchange="LoadMemDiv('GradeAndTask');" style="float:left;width:130px;margin-left:5px;line-height:30px;<?= (!$grades ? 'cursor:not-allowed;" disabled="disabled' : '') ?>">
                    <?
                    if ($_GET['GID'] == null || $_GET['GID'] == '0') {} else {
                        $classes = $thisDB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll();
                    }
                    
                    if (!$grades) {
                    ?>
                    <option value="0">无年级，请添加</option>
                    <?
                    } else {
                    ?>
                    <option value="0">请选择年级</option>
                    <?
                    foreach ($grades as $ge) {
                    ?>
                    <option value="<?= $ge['gradeid'] ?>"<?= ($ge['gradeid'] == $_GET['GID'] ? ' selected="selected"' : '') ?>><?= $ge['name'] ?></option>
                    <?
                    }
                    }
                    ?>
                </select>
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" style="float:left;width:25%;padding:10px;margin-left:5px;" onclick="<?= ($_GET['GID'] == null || $_GET['GID'] == '0' && $userrow['status'] == 1 ? 'ErrorMsg(\'请选择年级\');' : 'LoadMemDiv(\'GradeAndClassAndTaskOtherGetInfo\');') ?>">
                  <span>刷新列表</span>
                </button>
                <input type="text" oninput="SearchClass('2','Admin');" placeholder="请输入姓名" class="el-input__inner" name="class" style="float:left;width:100px;margin-left:5px;margin-left:32px;height:28px;line-height:10px;margin-top:10px;">
                <button type="button" class="el-button w-100 el-button--primary Bg-Reds" style="float:left;width:40%;padding:10px;margin-left:10px;margin-top:10px;height:28px;line-height:10px;" onclick="<?= ($_GET['GID'] == null || $_GET['GID'] == '0' && $userrow['status'] == 1 ? 'ErrorMsg(\'请选择年级\');' : 'AdminSetAllPassword();') ?>">
                  <span>重置所有用户密码</span>
                </button>
              </div>
                <center><select id="SelectClassName" onchange="SelectClassName();" class="el-input__inner" style="width:130px;margin-left:5px;line-height: 20px;height: 30px;position: relative;top: 10px;"<?= (!$classes ? ' disabled="disabled"' : '') ?>>
                    <?= (!$classes ? '<option value="0">无班级</option>' : '') ?>
                    <?
                    foreach ($classes as $cc) {
                    ?>
                    <option value="<?= $cc['name'] ?>"<?= ($_GET['CID'] == $cc['name'] ? ' selected="selected"' : '') ?>><?= $grade['name'].$cc['name'] ?>班</option>
                    <?
                    }
                    ?>
                    <option value="All"><?= $grade['name'] ?>所有班</option>
                </select></center>
            </div>
            <center id="ClassLists" style="height:200px;overflow:auto;">
            <?
            if ($_GET['GID'] == null || $_GET['GID'] == '0') {} else {
                $members = $thisDB->query("SELECT * FROM Web_User WHERE username!='Administrator' ORDER BY class,id")->fetchAll();
            }
            if ($_GET['GID'] == null || $_GET['GID'] == '0') {
            ?>
              <div>请选择年级</div>
            <?
            } elseif (!$members) {
            ?>
              <div>暂无成员信息</div>
            <?
            } else {
            ?>
              <input type="hidden" name="gradeid" value="<?= $grade['gradeid'] ?>" />
              <table style="width:100%;">
                  <thead>
                      <tr>
                          <th style="width:5%;"></th>
                          <th style="width:5%;">ID</th>
                          <th style="width:7%;">成员姓名</th>
                          <th style="width:4%;">所属班级</th>
                          <th style="width:8%;">是否团支书</th>
                          <th style="width:15%;">是否代理团支书</th>
                          <th style="width:18%;">添加时间</th>
                          <th style="width:18%;">打卡任务情况
                          <?
                          $tasks = $DB->query("SELECT * FROM Web_Task order by id desc")->fetchAll();
                          ?>
                              <select id="ChangeTask" onchange="LoadMemDiv('GradeAndClassAndTaskOtherGetInfo');"<?= (!$tasks ? 'disabled="disabled"' : '') ?>>
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
                          <th style="width:25%;">操作</th>
                      </tr>
                  </thead>
                  <tbody id="TbodyList" style="text-align:center;">
                      <?
                      foreach ($members as $m) {
                          $class = $thisDB->query("SELECT * FROM Web_Class WHERE name='{$m['class']}' limit 1")->fetch();
                          
                          if ($_GET['TID'] == null || $_GET['TID'] == 'undefined') {
                              $t = $DB->query("SELECT * FROM Web_Task ORDER BY id DESC limit 1")->fetch();
                              $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}'")->fetch();
                          } else {
                              $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$_GET['TID']}' limit 1")->fetch();
                          }
                          $tid = $task['id'];
                          if (!$task) {
                              $tres = '无此打卡任务';
                          } else {
                              $mis = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' and uid='{$m['id']}' limit 1")->fetch();
                              if (strtotime($mis['addtime']) >= $task['endtime']) {
                                  $unfres = '此用户超时打卡';
                              } else {
                                  $unfres = '此用户谎打卡';
                              }
                              if (!$mis) {
                                  $tres = '此用户未打卡';
                              } else {
                                  $tres = '<b style="color:'.($mis['status'] == 1 || $mis['status'] == 0 ? 'blue' : 'red').';">'.($mis['status'] == 1 || $mis['status'] == 0 ? '此用户已打卡' : ($mis['status'] == 2 ? $unfres : '此用户未打卡')).'</b>';
                              }
                          }
                      ?>
                      <tr id="<?= $m['id'] ?>" classid="<?= $m['class'] ?>"<? 
                      if ($m['class'] != ($thisDB->query("SELECT * FROM Web_Class ORDER BY name limit 1")->fetch())['name'] && ($_GET['CID'] == null || $_GET['CID'] == 'undefined')) {
                          echo ' style="display:none;"';
                      }
                      if ($_GET['CID'] != $m['class'] && $_GET['CID'] != null) {
                          echo ' style="display:none;"';
                      }
                      ?>>
                          <td><input type="checkbox" name="SelectInfoList" value="<?= $m['id'] ?>" /></td>
                          <td><?= $m['id'] ?></td>
                          <td><?= $m['username'] ?></td>
                          <td><?= $m['class'] ?>班</td>
                          <td><button name="LID" id="lid-<?= $m['id'] ?>" class="ClickBT <?= ($class['lid'] == $m['id'] ? 'Bg-Greens' : 'Bg-Reds') ?>" title="点击设置" style="height: 25px;width: 25px;" onclick="AdminSetLid('<?= $m['id'] ?>');"><?= ($class['lid'] == $m['id'] ? '是' : '否') ?></button></td>
                          <td><? if ($class['lid'] == $m['id']) { ?>此人为团支书<? } else { ?><button name="fLID" id="flid-<?= $m['id'] ?>" class="ClickBT <?= (2 == $m['status'] ? 'Bg-Greens' : 'Bg-Reds') ?>" title="点击设置" style="height: 25px;width: 25px;" onclick="AdminSetFLid('<?= $m['id'] ?>');"><?= (2 == $m['status'] ? '是' : '否') ?></button><? } ?></td>
                          <td><?= $m['addtime'] ?></td>
                          <td><?= $tres ?></td>
                          <td>
                              <button class="ClickBT Bg-Primary" style="height: 20px;" onclick="AdminChangeClass('<?= $m['id'] ?>');">转班</button>
                              <button class="ClickBT Bg-Blues" style="height: 20px;" onclick="AdminEditName('<?= $m['id'] ?>');">改名</button>
                              <button class="ClickBT Bg-Blues" style="height: 20px;" onclick="AdminSetPassword('<?= $m['id'] ?>');">重置密码</button>
                              <button class="ClickBT Bg-Reds" style="height: 20px;" onclick="AdminDeleteUser('<?= $m['id'] ?>');">删除</button>
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
            <?
            if ($_GET['GID'] == null || $_GET['GID'] == '0') {} else {
            ?>
            <div style="width:100%;margin-left:10px;">
                <button class="ClickBT Bg-Blues" style="height:20px;padding:0 14px;float:left;" id="CheckBtn" onclick="DoSelectAllLists('HaveNoDisplay','class');">全选</button>
                <select id="AjaxDoType" onchange="AjaxDoType();" style="height:20px;float: left;margin-left: 20px;margin-top: 1.5px;">
                    <option value="0">请选择批量操作方式</option>
                    <option value="AdminChangeClass">批量转班</option>
                    <option value="AdminSetFLid">批量设置为/取消其代理团支书</option>
                    <option value="AdminSetPassword">批量重置密码</option>
                    <option value="AdminDeleteUser">批量删除用户</option>
                </select>
            </div>
            <?
            }
            ?>
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
  <script type="text/javascript">
  <?
  if ($_GET['TYPE'] == 1) {
  ?>
  history.replaceState(null, null, 'LookMembers.php');
  <?
  }
  ?>
  </script>
</html>