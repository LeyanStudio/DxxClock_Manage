<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {header("Location:Index.php");}

$notices = $DB->query("SELECT * FROM Web_Notice ORDER BY id DESC")->fetchAll();

function get_LookMem($type) {
    if ($type == 1) {
        $result = array("color"=>"#565656","name"=>"普通成员");
    } elseif ($type == 2) {
        $result = array("color"=>"#3987FB","name"=>"各班团支书");
    } elseif ($type == 3) {
        $result = array("color"=>"#142C5D","name"=>"各年级各班成员");
    } elseif ($type == 4) {
        $result = array("color"=>"#23D96E","name"=>"各年级管理员");
    } elseif ($type == 5) {
        $result = array("color"=>"#F4BF75","name"=>"各年级管理员&团支书");
    } elseif ($type == 6) {
        $result = array("color"=>"#DD5145","name"=>"各年级所有成员");
    }
    return $result;
}
?>
<!DOCTYPE html>
<html>
  
  <head>
    <title><?= $conf['title'] ?>【公告管理】</title>
    <link rel="icon" href="/Assets/Img/favicon.ico">
    <link rel="stylesheet" href="/Assets/Css/Index.css"></head>
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
    width: 160px;
    display: block;
    float: left;
    border-left: 3px solid #1660F3;
    padding: 0 10px;
}
.CIIndex input, textarea, select[id='AddNotice'] {
    width: 150px;
    height: 30px;
    border: 2px solid #5186ef;
    border-radius: 6px;
    box-shadow: 2px 2px 4px #043088;
    padding: 0 8px;
    outline: none;
}
.CIIndex select[id='AddNotice'] {
    padding: 0 4px;
    font-size: 14px;
}
.CIIndex textarea {
    padding: 8px 8px;
    height: 200px;
    width: 250px;
}
msg {
    display: none;
}
</style>
  
  <body>
    <div class="main">
      <div id="appLogin">
        <div class="logins position" id="MainIndex" style="width: 700px;margin-left: -350px;height: 530px;">
          <div class="mip-reg-logo">
          <div class="mip-reg-heading">公告管理
              <button type="button" class="el-button w-100 el-button--primary Bg-Greens" onclick="AdminLookNoticesInfo();" style="width:15%;height:30px;">
                <span>查看概览图</span>
              </button>
         </div>
          <div class="mip-reg-body">
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="OpenAddAllNotice();" style="float:left;width:30%;padding:10px;">
                  <span>添加公告</span>
                </button>
                <input type="text" oninput="SearchClass();" placeholder="请输入标题" class="el-input__inner" name="class" style="float:left;width:130px;margin-left:5px;">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" style="float:left;width:25%;padding:10px;margin-left:5px;" onclick="LoadDiv();">
                  <span>刷新列表</span>
                </button>
              </div>
            </div>
            <center id="ClassLists" style="height:270px;overflow:auto;">
            <?
            if (!$notices) {
            ?>
              <div>暂无公告</div>
            <?
            } else {
            ?>
              <table style="width:100%;">
                  <thead>
                      <tr>
                          <th style="width:5%;"></th>
                          <th style="width:5%;">ID</th>
                          <th style="width:12%;">公告标题</th>
                          <th style="width:12%;">公告内容</th>
                          <th style="width:22%;">最后一次修改时间</th>
                          <th style="width:14%;">查看对象</th>
                          <th style="width:7%;">查看人数</th>
                          <th style="width:8%;">查看率</th>
                          <th style="width:13%;">操作</th>
                      </tr>
                  </thead>
                  <tbody id="TbodyList" style="text-align:center;">
                      <?
                      foreach ($notices as $n) {
                          $grades = $DB->query("SELECT * FROM Web_Grade")->fetchAll();
                              foreach ($grades as $ge) {
                                  //连接数据库
                                  $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$ge['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
                                  
                                  $notice = $thisDB->query("SELECT * FROM Web_Notice WHERE gbatch='{$n['gbatch']}'")->fetch();
                                  foreach ($thisDB->query("SELECT * FROM Web_NoticeLooked WHERE nbatch='{$notice['id']}' ORDER BY id")->fetchAll() as $nts) {
                                      $ntlookeds[] = $nts;
                                  }
                              
                                  //获取用户数量
                                  foreach ($thisDB->query("SELECT * FROM Web_User")->fetchAll() as $u) {
                                      $us[] = $u;
                                  }
                              }
                              $counts = @count($ntlookeds);
                      ?>
                      <tr id="<?= $n['id'] ?>">
                          <td><input type="checkbox" name="SelectInfoList" value="<?= $n['id'] ?>" /></td>
                          <td><?= $n['id'] ?></td>
                          <td><?= $n['title'] ?></td>
                          <td><button class="ClickBT Bg-Blues" title="点击查看公告内容" style="height: 25px;width: 70px;" onclick="LookNoticeMsg('<?= $n['id'] ?>','Admin');">点击查看</button><msg id="m-<?= $n['id'] ?>"><?= str_replace('<br>',"\n",$n['msg']) ?></msg></td>
                          <td><?= $n['addtime'] ?></td>
                          <td looktype="<?= $n['type'] ?>" style="color:<?= get_LookMem($n['type'])['color'] ?>;"><?= get_LookMem($n['type'])['name'] ?></td>
                          <td class="CanClick" onclick="LookLookingMen('<?= $n['id'] ?>');"><?= $counts ?>人</td>
                          <td style="color:<?= ($counts == 0 ? 'red' : 'green') ?>;"><?= ($counts == 0 ? '无人查看' : substr(($counts/@count($us))*100,0,6).'%') ?></td>
                          <td>
                              <button class="ClickBT Bg-Blues" style="height: 28px;" onclick="OpenEditAllNotice('<?= $n['id'] ?>');">编辑</button>
                              <button class="ClickBT Bg-Reds" style="height: 28px;" onclick="DeleteAllNotice('<?= $n['id'] ?>');">删除</button>
                          </td>
                      </tr>
                      <?
                      $ntlookeds = null;
                      $us = null;
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
                    <option value="SetAllNoticeLT">批量设置查看对象</option>
                    <option value="DeleteAllNotice">批量删除公告</option>
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