<?
include("./Includes/Common.php");
if (($userrow['status'] == 1 || $userrow['status'] == 2) && $islogin2 == 1) {} else {header("Location:Index.php");}

$classes = $DB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll();
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
          <div class="mip-reg-heading">成员管理<?= ($userrow['status'] == 1 ? '  <b style="color:blue;cursor:pointer;" onclick="ListAddMem();">批量添加</b>  <b style="color:green;cursor:pointer;" onclick="$(\'#File\').trigger(\'click\');">批量转班</b><input type="file" id="File" style="display: none;" accept=".txt" onchange="ListChangeClass();">' : '') ?></div>
          <div class="mip-reg-body">
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="<?= ($_GET['CID'] == null && $userrow['status'] == 1 ? 'ErrorMsg(\'请选择班级\');' : 'OpenAddMember();') ?>" style="float:left;width:30%;padding:10px;">
                  <span>添加成员</span>
                </button>
                <select class="el-input__inner" id="selclass" onchange="SelectClass();" style="float:left;width:130px;margin-left:5px;line-height:30px;<?= (!$classes ? 'cursor:not-allowed;" disabled="disabled' : '') ?>">
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
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" style="float:left;width:25%;padding:10px;margin-left:5px;" onclick="<?= ($_GET['CID'] == null && $userrow['status'] == 1 ? 'ErrorMsg(\'请选择班级\');' : 'LoadMemDiv(\'AndTask\');') ?>">
                  <span>刷新列表</span>
                </button>
                <input type="text" oninput="SearchClass('2');" placeholder="请输入姓名" class="el-input__inner" name="class" style="float:left;width:100px;margin-left:5px;margin-left:32px;height:28px;line-height:10px;margin-top:10px;">
                <button type="button" class="el-button w-100 el-button--primary Bg-Reds" style="float:left;width:40%;padding:10px;margin-left:10px;margin-top:10px;height:28px;line-height:10px;" onclick="<?= ($_GET['CID'] == null && $userrow['status'] == 1 ? 'ErrorMsg(\'请选择班级\');' : 'SetAllPassword();') ?>">
                  <span>重置所有用户密码</span>
                </button>
              </div>
            </div>
            <center id="ClassLists" style="height:232px;overflow:auto;">
            <?
            if ($_GET['CID'] == 'All') {
                $members = $DB->query("SELECT * FROM Web_User WHERE username!='Administrator' ORDER BY class,id")->fetchAll();
            } else {
                $cid = ($_GET['CID'] == null && $userrow['status'] == 2 ? $userrow['class'] : $_GET['CID'] );
                $members = $DB->query("SELECT * FROM Web_User WHERE class='{$cid}' ORDER BY id")->fetchAll();
            }
            if ($_GET['CID'] == null && $userrow['status'] == 1) {
            ?>
              <div>请选择班级</div>
            <?
            } elseif (!$members) {
            ?>
              <div>暂无成员信息</div>
            <?
            } else {
            ?>
              <table style="width:100%;">
                  <thead>
                      <tr>
                          <th style="width:5%;"></th>
                          <?
                          if ($userrow['status'] == 1) {
                          ?>
                          <th style="width:5%;">ID</th>
                          <?
                          if ($_GET['CID'] == 'All') {
                          ?>
                          <th style="width:7%;">成员姓名</th>
                          <th style="width:5%;">班级号</th>
                          <?
                          } else {
                          ?>
                          <th style="width:7%;">成员姓名</th>
                          <?
                          }
                          ?>
                          <th style="width:8%;">是否团支书</th>
                          <th style="width:8%;">是否代理团支书</th>
                          <th style="width:8%;">是否团员</th>
                          <th style="width:20%;">添加时间</th>
                          <th style="width:15%;">打卡任务情况
                          <?
                          } elseif ($userrow['status'] == 2) {
                          ?>
                          <th style="width:5%;">ID</th>
                          <th style="width:15%;">成员姓名</th>
                          <th style="width:15%;">是否团员</th>
                          <th style="width:20%;">添加时间</th>
                          <th style="width:20%;">打卡任务情况
                          <?
                          }
                          ?>
                          <?
                          $tasks = $DB->query("SELECT * FROM Web_Task order by id desc")->fetchAll();
                          ?>
                              <select id="ChangeTask" onchange="ChangeTask('Member');"<?= (!$tasks ? 'disabled="disabled"' : '') ?>>
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
                          <th style="width:25%;">操作</th>
                      </tr>
                  </thead>
                  <tbody id="TbodyList" style="text-align:center;">
                      <?
                      foreach ($members as $m) {
                          $class = $DB->query("SELECT * FROM Web_Class WHERE name='{$m['class']}' limit 1")->fetch();
                          
                          if ($_GET['TID'] == null) {
                              $task = $DB->query("SELECT * FROM Web_Task ORDER BY id DESC limit 1")->fetch();
                          } else {
                              $task = $DB->query("SELECT * FROM Web_Task WHERE id='{$_GET['TID']}' limit 1")->fetch();
                          }
                          $tid = $task['id'];
                          if (!$task) {
                              $tres = '无此打卡任务';
                          } else {
                              $mis = $DB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' and uid='{$m['id']}' limit 1")->fetch();
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
                      <?
                      if ($userrow['status'] == 1) {
                      ?>
                      <tr id="<?= $m['id'] ?>">
                          <td><input type="checkbox" name="SelectInfoList" value="<?= $m['id'] ?>" /></td>
                          <td><?= $m['id'] ?></td>
                          <?
                          if ($_GET['CID'] == 'All') {
                          ?>
                          <td><?= $m['username'] ?></td>
                          <td><?= $m['class'] ?></td>
                          <?
                          } else {
                          ?>
                          <td><?= $m['username'] ?></td>
                          <?
                          }
                          ?>
                          <td><button name="LID" id="lid-<?= $m['id'] ?>" class="ClickBT <?= ($class['lid'] == $m['id'] ? 'Bg-Greens' : 'Bg-Reds') ?>" title="点击设置" style="height: 25px;width: 25px;" onclick="SetLid('<?= $m['id'] ?>');"><?= ($class['lid'] == $m['id'] ? '是' : '否') ?></button></td>
                          <td><? if ($class['lid'] == $m['id']) { ?>此人为团支书<? } else { ?><button name="fLID" id="flid-<?= $m['id'] ?>" class="ClickBT <?= (2 == $m['status'] ? 'Bg-Greens' : 'Bg-Reds') ?>" title="点击设置" style="height: 25px;width: 25px;" onclick="SetFLid('<?= $m['id'] ?>');"><?= (2 == $m['status'] ? '是' : '否') ?></button><? } ?></td>
                          <td>
                              <?
                              $flmsg = @file_get_contents('./Txts/tuanyuan/'.$conf['orgid'].'.txt');
                              $flarr = @json_decode($flmsg,true);
                              ?>
                          <button name="VIP" id="Vip-<?= $m['id'] ?>" class="ClickBT <?= (@in_array($m['id'],$flarr) ? 'Bg-Greens' : 'Bg-Reds') ?>" title="点击设置" style="height: 25px;width: 25px;" onclick="SetVip('<?= $m['id'] ?>');"><?= (@in_array($m['id'],$flarr) ? '是' : '否') ?></button>
                          </td>
                          <td><?= $m['addtime'] ?></td>
                          <td><?= $tres ?></td>
                          <td>
                              <?
                              if ($userrow['status'] == 1) {
                              ?>
                              <button class="ClickBT Bg-Primary" style="height: 20px;" onclick="ChangeClass('<?= $m['id'] ?>');">转班</button>
                              <?
                              }
                              ?>
                              <button class="ClickBT Bg-Blues" style="height: 20px;" onclick="EditName('<?= $m['id'] ?>');">改名</button>
                              <button class="ClickBT Bg-Blues" style="height: 20px;" onclick="SetPassword('<?= $m['id'] ?>');">重置密码</button>
                              <button class="ClickBT Bg-Reds" style="height: 20px;" onclick="DeleteUser('<?= $m['id'] ?>');">删除</button>
                          </td>
                      </tr>
                      <?
                      } elseif ($userrow['status'] == 2) {
                      ?>
                      <tr id="<?= $m['id'] ?>">
                          <td><input type="checkbox" name="SelectInfoList" value="<?= $m['id'] ?>" /></td>
                          <td><?= $m['id'] ?></td>
                          <td><?= ($userrow['id'] == $m['id'] ? '<b style="color:blue;">'.$m['username'].'</b>' : $m['username']) ?></td>
                          <td>
                              <?
                              $flmsg = @file_get_contents('./Txts/tuanyuan/'.$conf['orgid'].'.txt');
                              $flarr = @json_decode($flmsg,true);
                              ?>
                          <button name="VIP" id="Vip-<?= $m['id'] ?>" class="ClickBT <?= (in_array($m['id'],$flarr) ? 'Bg-Greens' : 'Bg-Reds') ?>" title="点击设置" style="height: 25px;width: 25px;" onclick="SetVip('<?= $m['id'] ?>');"><?= (in_array($m['id'],$flarr) ? '是' : '否') ?></button>
                          </td>
                          <td><?= $m['addtime'] ?></td>
                          <td><?= $tres ?></td>
                          <td>
                              <?= ($userrow['id'] == $m['id'] ? '' : '<button class="ClickBT Bg-Blues" onclick="SetPassword(\''.$m['id'] .'\');">重置密码</button><button class="ClickBT Bg-Reds" onclick="DeleteUser(\''.$m['id'] .'\');">删除</button>') ?>
                          </td>
                      </tr>
                      <?
                      }
                      ?>
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
            if ($_GET['CID'] == null && $userrow['status'] == 1) {} else {
            ?>
            <div style="width:100%;margin-left:10px;">
                <button class="ClickBT Bg-Blues" style="height:20px;padding:0 14px;float:left;" id="CheckBtn" onclick="DoSelectAllLists();">全选</button>
                <select id="AjaxDoType" onchange="AjaxDoType();" style="height:20px;float: left;margin-left: 20px;margin-top: 1.5px;">
                    <option value="0">请选择批量操作方式</option>
                    <?
                    if ($userrow['status'] == 1) {
                    ?>
                    <option value="ChangeClass">批量转班</option>
                    <option value="SetFLid">批量设置为/取消其代理团支书</option>
                    <?
                    }
                    ?>
                    <option value="SetPassword">批量重置密码</option>
                    <option value="DeleteUser">批量删除用户</option>
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