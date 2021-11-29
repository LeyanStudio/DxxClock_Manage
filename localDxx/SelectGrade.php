<?
require './Admin/Includes/Config.php';
try {
    $GDB = new PDO("mysql:host={$dbconfig['host']};dbname={$dbconfig['dbname']};port={$dbconfig['port']}",$dbconfig['user'],$dbconfig['pwd']);
} catch (Exception $e) {
    exit('连接数据库失败:'.$e->getMessage());
}

$GDB->exec("set names utf8");

$rs = $GDB->query("select * from Web_Config");
while ($row = $rs->fetch()) { 
	$conf[$row['x']] = $row['j'];
}

header('gradeid: 1');
?>
<!DOCTYPE html>
<html>
  
  <head>
    <title><?= $conf['title'] ?></title>
    <link rel="icon" href="/Assets/Img/favicon.ico">
    <link rel="stylesheet" href="/Assets/Css/Index.css"></head>
  
  <body>
    <div class="main">
      <div id="appLogin">
        <div class="logins position">
          <div class="mip-reg-logo">
          <div class="mip-reg-heading">选择年级并访问</div>
          <div class="mip-reg-body">
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <div class="el-input">
                  <select class="el-input__inner" name="selectgrade" onchange="ToGrade();" style="line-height:10px;">
                      <option value="0">请选择您所在年级</option>
                      <?
                      foreach ($GDB->query("SELECT * FROM Web_Grade ORDER BY gradeid")->fetchAll() as $gd) {
                      ?>
                      <option value="<?= $gd['gradeid'] ?>"><?= $gd['name'] ?></option>
                      <?
                      }
                      ?> 
                  </select>
                  <div></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="copyright" style="text-align: center; position: absolute;bottom:0;width: 100%; color: #6d6d6d;line-height: 26px; font-size: 14px; margin-bottom: 15px;">
        <a href="/" style="color: #6d6d6d;"><?= $conf['title'] ?> | <?= $conf['copyright'] ?> | Copyright © 2020-<?= date("Y") ?></a></div>
    <div class="layui-layer-shade" id="layui-layer-shade2" loadname="PageLoadingTip" times="0" style="background-color: rgb(0, 0, 0); opacity: 0.01;"></div>
    <div class="layui-layer layui-layer-dialog layui-layer-msg" id="layui-layer0" loadname="PageLoadingTip" type="dialog" times="0" showtime="0" contype="string" style="top: 295.5px; left: 592px;"><div style="" id="" class="layui-layer-content layui-layer-padding"><i class="layui-layer-ico layui-layer-ico20"></i>Loading……</div><span class="layui-layer-setwin"></span></div>
    </div>
  </body>
  <script src="http://nchat.kayanxin.cn/Assets/Js/jquery.js"></script>
  <script src="http://nchat.kayanxin.cn/Assets/Js/layer/layer.js"></script>
  <script src="/Assets/Js/Main.js"></script>
  <script type="text/javascript">
      function ToGrade() {
          var gid = $("select[name='selectgrade']").val();
          if (gid == 0 || gid == '') {
              ErrorMsg('请选择年级');
              return false;
          }
          ToHref('http://'+gid+'_localhost.kayanxin.cn');
      }
  </script>

</html>