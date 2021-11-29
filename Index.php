<?
include("./Includes/Common.php");
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
            <?
            if ($islogin2 == '-1' || $islogin2 == null) {
            ?>
          <div class="mip-reg-logo">
          <div class="mip-reg-heading">登录 | <?= $conf['title'] ?></div>
          <div class="mip-reg-body">
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <div class="el-input">
                  <select class="el-input__inner" name="selectclass" style="line-height:10px;">
                      <option value="0">请选择您所在班级</option>
                      <?
                      foreach ($DB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll() as $c) {
                      ?>
                      <option value="<?= $c['name'] ?>"><?= $conf['gradename'].$c['name'] ?>班</option>
                      <?
                      }
                      ?> 
                  </select>
                  <div></div>
                </div>
              </div>
            </div>
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <div class="el-input">
                  <input type="text" onblur="CheckInput('username');" placeholder="账号[姓名]" class="el-input__inner" name="username">
                  <div></div>
                </div>
              </div>
            </div>
            <div class="el-form-item is-required">
              <div class="el-form-item__content">
                <div class="el-input">
                  <input type="password" onblur="CheckInput('password');" placeholder="密码[默认为123456]" class="el-input__inner" name="password">
                  <div></div>
                </div>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues User_Login">
                  <span>登录</span>
                </button>
              </div>
            </div>
          </div>
            <?
            } else {
            ?>
          <div class="mip-reg-logo">
          <div class="mip-reg-heading">用户【<b><?= $pid ?></b>】您好！</div>
          <div class="mip-reg-body">
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="window.location.href = 'Home.php';">
                  <span>进入首页</span>
                </button>
              </div>
            </div>
            <p class="login-link">
              <a href="javascript:Logout();" style="color:red;font-weight:bold;">退出登录</a>
            </p>
          </div>
            <?
            }
            ?>
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

</html>