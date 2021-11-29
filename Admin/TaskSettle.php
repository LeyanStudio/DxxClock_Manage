<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {header("Location:Index.php");}

$task = $DB->query("SELECT * FROM Web_Task WHERE id='{$_GET['TID']}'")->fetch();
?>
<!DOCTYPE html>
<html>
  
  <head>
    <title><?= $conf['title'] ?>【任务【ID - <?= $_GET['TID'] ?>】结算】</title>
    <link rel="icon" href="/Assets/Img/favicon.ico">
    <link rel="stylesheet" href="/Assets/Css/Index.css"></head>
  
  <body>
    <div class="main">
      <div id="appLogin">
        <div class="logins position" id="MainIndex" style="width: 700px;margin-left: -350px;height: 530px;">
          <div class="mip-reg-logo">
          <div class="mip-reg-heading">【任务【ID - <?= $_GET['TID'] ?>】结算】
              <button type="button" class="el-button w-100 el-button--primary Bg-Greens" onclick="AdminClickSettleBtn('<?= $_GET['TID'] ?>','yes');" style="width:15%;height:30px;">
                <span>刷新数据</span>
              </button>
          </div>
          <div class="mip-reg-body">
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="AdminClickSettleBtn('<?= $_GET['TID'] ?>');" style="float:left;width:30%;padding:10px;">
                  <span>一键结算</span>
                </button>
                <input type="text" oninput="SearchClass('1');" placeholder="请输入姓名" class="el-input__inner" name="class" style="float:left;width:130px;margin-left:5px;">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" style="float:left;width:25%;padding:10px;margin-left:5px;" onclick="LoadDiv();">
                  <span>刷新列表</span>
                </button>
              </div>
            </div>
            <center id="ClassLists" style="height:285px;overflow:auto;">
            <?
            if (!$task) {
            ?>
              <div>暂无任务信息</div>
            <?
            } else {
                foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $t = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$task['gbatch']}'")->fetch();
                    $gn = ($thisDB->query("SELECT * FROM Web_Config WHERE x='gradename'")->fetch())['j'];
                    $goid = ($thisDB->query("SELECT * FROM Web_Config WHERE x='orgid'")->fetch())['j'];
                    //exit($t['id']);
                    foreach ($thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch={$t['id']}")->fetchAll() as $cl) {
                        $user = $thisDB->query("SELECT * FROM Web_User WHERE id={$cl['uid']}")->fetch();
                        $clocks[] = array("id"=>$cl['id'],"username"=>$user['username'],"class"=>$user['class'],"gradename"=>$gn,"goid"=>$goid,"addtime"=>$cl['addtime'],"status"=>$cl['status']);
                    }
                }
            ?>
              <table style="width:100%;">
                  <thead>
                      <tr>
                          <th style="width:7%;">ID</th>
                          <th style="width:12%;">姓名</th>
                          <th style="width:15%;">所对年级</th>
                          <th style="width:10%;">所对班级</th>
                          <th style="width:20%;">结算时间</th>
                          <th style="width:25%;">操作</th>
                      </tr>
                  </thead>
                  <tbody id="TbodyList" style="text-align:center;">
                      <?
                      foreach ($clocks as $t) {
                          //$user = $thisDB->query("SELECT * FROM Web_User WHERE id={$t['uid']}")->fetch();
                      ?>
                      <tr id="<?= $t['id'] ?>" stid="<?= $t['stid'] ?>">
                          <td><?= $t['id'] ?></td>
                          <td><?= $t['username'] ?></td>
                          <td><?= $t['gradename'] ?></td>
                          <td><?= $t['class'] ?>班</td>
                          <td><?= ($t['addtime'] == '0000-00-00 00:00:00' ? '<b style="color:red;">未结算</b>' : $t['addtime']) ?></td>
                          <td>
                              <?
                              if ($t['status'] == 0) {
                              ?>
                              <button class="ClickBT Bg-Greens" type="settle" style="height:20px;" id="cid-<?= $t['id'] ?>" onclick="AdminTaskSettle('<?= $_GET['TID'] ?>','<?= $t['id'] ?>','<?= $t['gradename'] ?>','<?= $t['goid'] ?>');">获取是否完成学习</button>
                              <?
                              } elseif ($t['status'] == 3) {
                              ?>
                              <button class="ClickBT Bg-Reds" type="settle" style="height:20px;" id="cid-<?= $t['id'] ?>" onclick="AdminTaskSettle('<?= $_GET['TID'] ?>','<?= $t['id'] ?>','<?= $t['gradename'] ?>','<?= $t['goid'] ?>');">此用户未完成学习</button>
                              <?
                              } else {
                              ?>
                              <button class="ClickBT Bg-Blues" type="settled" style="height:20px;">此用户已完成学习</button>
                              <?
                              }
                              ?>
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