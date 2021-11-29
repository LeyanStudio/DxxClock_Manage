<?
include("./Includes/Common.php");
if ($islogin == 1) {} else {header("Location:Index.php");}

foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $grade) {
    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
    foreach ($thisDB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll() as $class) {
        $cids[] = array("cid"=>$class['id'],"cname"=>$class['name'],"gid"=>$grade['id'],"gn"=>($thisDB->query("SELECT * FROM Web_Config WHERE x='gradename'")->fetch())['j']);
    }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= $conf['title'] ?>【总管理-首页】</title>
    <link rel="icon" href="/Assets/Img/favicon.ico">
    <link rel="stylesheet" href="/Assets/Css/Index.css"></head>
    <style>
.NoticeList {
    width: 95%;
    height: 15px;
    padding: 7px 5px;
    background: #e8f7fd;
    margin-bottom: 8px;
    border-radius: 5px;
    cursor: pointer;
    box-shadow: 2px 2px 4px #169AF3;
}
.NoticeList:hover {
    background: #d1f6ff;
    box-shadow: 2px 2px 4px #16c0f3;
}
.NoticeList b {
    display: block;
    width: 27%;
    float: left;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 12px;
}
.NoticeList span {
    margin-left: 10px;
    position: relative;
    top: 1px;
    color: grey;
    font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
    float: right;
}
.NoticeList notread {
    background: #ff4700;
    border-radius: 10px;
    padding: 2px 4px;
    color: white;
    font-weight: bold;
    font-size: 10px;
}
.NoticeList nodismsg {
    display: none;
}
.logins .el-form-item {
    margin: 0 auto 15px auto;
}
    </style>
  <body>
    <div class="main">
      <div id="appLogin">
        <div class="logins position">
          <div class="mip-reg-logo">
          <div class="mip-reg-heading">选择需要下载的任务<a href="javascript:;" style="font-size:10px;">[<b>任务</b>和<b>班级</b>按住ctrl可多选]</a></div>
          <div class="mip-reg-body">
            <br>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <?
                $tasks = $DB->query("SELECT * FROM Web_Task order by id desc")->fetchAll();
                ?>
                <select class="el-input__inner" id="DTChosen" style="line-height:30px;height:100px;" multiple="multiple"<?= (!$tasks ? 'disabled="disabled"' : '') ?>>
                    <?= (!$tasks ? '<option value="0">无打卡任务</option>' : '') ?>
                    <?
                    foreach ($tasks as $tt) {
                    ?>
                    <option value="<?= $tt['gbatch'] ?>"><?= $tt['title'] ?></option>
                    <?
                    }
                    ?>
                </select>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <?
                $grades = $DB->query("SELECT * FROM Web_Grade order by id desc")->fetchAll();
                ?>
                <select class="el-input__inner" id="DGChosen" onchange="ChangeClasses();" style="line-height:30px;"<?= (!$grades ? 'disabled="disabled"' : '') ?>>
                    <?= (!$grades ? '<option value="0">无年级</option>' : '') ?>
                    <option value="0">请选择年级</option>
                    <?
                    foreach ($grades as $g) {
                    ?>
                    <option value="<?= $g['id'] ?>"><?= $g['name'] ?></option>
                    <?
                    }
                    ?>
                </select>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <select class="el-input__inner" id="DCChosen" style="line-height:30px;height:100px;cursor:not-allowed;" multiple="multiple">
                    <option value="0">请选择年级再选择班级</option>
                </select>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="DownLoadDT();">
                  <span>下载表格文件</span>
                </button>
              </div>
            </div>
            <!--div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Greens" onclick="DownLoadDT('1');">
                  <span>下载已完成表格文件</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Reds" onclick="DownLoadDT('3');">
                  <span>下载未完成表格文件</span>
                </button>
              </div>
            </div-->
            <br><br>
            <p class="login-link">
              <a href="javascript:history.go(-1);">返回上一页</a>
            </p>
          </div>
        </div>
      </div>
      <div class="copyright" style="text-align: center; position: absolute;bottom:0;width: 100%; color: #6d6d6d;line-height: 26px; font-size: 14px; margin-bottom: 15px;">
        <a href="/" style="color: #6d6d6d;"><?= $conf['title'] ?> | <?= $conf['copyright'] ?> | Copyright © 2020-<?= date("Y") ?></a></div>
    </div>
    <div class="layui-layer-shade" id="layui-layer-shade2" loadname="PageLoadingTip" times="0" style="background-color: rgb(0, 0, 0); opacity: 0.01;"></div>
    <div class="layui-layer layui-layer-dialog layui-layer-msg" id="layui-layer0" loadname="PageLoadingTip" type="dialog" times="0" showtime="0" contype="string" style="top: 295.5px; left: 592px;"><div style="" id="" class="layui-layer-content layui-layer-padding"><i class="layui-layer-ico layui-layer-ico20"></i>Loading……</div><span class="layui-layer-setwin"></span></div>
  </body>
  <script src="http://nchat.kayanxin.cn/Assets/Js/jquery.js"></script>
  <script src="http://nchat.kayanxin.cn/Assets/Js/layer/layer.js"></script>
  <script src="/Assets/Js/Main.js"></script>
  <script>
      var cids = <?= json_encode($cids) ?>;
  </script>

</html>