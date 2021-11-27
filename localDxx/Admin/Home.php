<?
if (isset($_POST['Type'])) {
    include ("../Admin/Includes/Common_Two.php");
    if ($_POST['Type'] == 'DoUp') {
        if ($_POST['File'] != null) {
            $Di = '';
            $I = 0;
            if (count(explode('/',$_POST['File'])) != 2) {
                foreach (explode('/',$_POST['File']) as $D) {
                    $I++;
                    if (strpos($D,'.') !== false) {
                        continue;
                    } elseif ($I > 1) {
                        $Di = $Di.'/'.$D;
                        $DAR[] = str_replace('/Admin/','',ROOT) .$Di;
                    }
                }
                //添加新目录
                foreach ($DAR as $DA) {
                    if (is_dir($DA)) {
                        continue;
                    } else {
                        mkdir($DA);
                    }
                }
            }
            $File = file_get_contents('http://api.kayanxin.cn/Update/Dxx/GetFile.php?File='.str_replace('-','',$_POST['NewDate']).$_POST['File']);
            if (file_put_contents(str_replace('/Admin/','',ROOT).$_POST['File'],$File)) {
                $result = array("Code"=>1,"Message"=>"Success","FileMsg"=>$_POST['File']);
                file_put_contents('./VLog.txt',$_POST['VLog']);
                file_put_contents('./Version.txt',json_encode(array("Num"=>$_POST['Version'],"Date"=>$_POST['NewDate'])));
            } else {
                $result = array("Code"=>1,"Message"=>"文件保存失败，请检查目录权限","FileMsg"=>$_POST['File']);
            }
        } else {
            $result = array("Code"=>-1,"Message"=>"Error");
        }
    } else {
        $result = array("Code"=>-3,"Message"=>"网络错误！");
    }
    echo json_encode($result);
    exit();
}
include("./Includes/Common.php");
if ($islogin == 1) {} else {header("Location:Index.php");}
$Version = file_get_contents('./Version.txt');
$Varr = json_decode($Version,true);
$VNum = $Varr['Num'];
$VDate = $Varr['Date'];
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= $conf['title'] ?>【总管理-首页】</title>
    <link rel="icon" href="/Assets/Img/favicon.ico">
    <link rel="stylesheet" href="/Assets/Css/Index.css"></head>
    <link rel="stylesheet" href="//api.kayanxin.cn/Update/Dxx/20211119/Assets/Layui/layui.css" id="layui">
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
    <style>
    .setchmod{padding-bottom:50px;}
    .update_title{overflow: hidden;position: relative;vertical-align: middle;margin-top: 10px;}
    .update_title .layui-layer-ico{display: block;left: 60px !important;top: 1px !important;}
    .update_title span{display: inline-block;color: #333;height: 30px;margin-left: 105px;margin-top: 3px;font-size: 20px;}
    .update_conter{background: #f9f9f9;border-radius: 4px;padding: 20px;margin: 15px 37px;margin-top: 15px;height: 150px;overflow-y: auto;}
    .update_version{font-size: 12px;margin:15px 0 10px 85px}
    .update_newversion{font-size: 12px;margin:15px 0 10px 85px}
    .update_logs{margin-bottom:10px;border-bottom:1px solid #ececec;padding-bottom:10px;}
    .update_tips{font-size: 13px;color: #666;font-weight: 600;}
    .update_tips span{padding-top: 5px;display: block;font-weight: 500;}
    .update_tips div span{font-size: 10;display: unset;}
    .bt-form-submit-btn {
    background: #f6f8f8;
    border-top: 1px solid #edf1f2;
    bottom: 0;
    left: 0;
    padding: 8px 20px 10px;
    position: absolute;
    text-align: right;
    width: 480px;
}
.bt-form-submit-btn .btn:first-child {
    margin-right: 4px;
}
.btn-danger {
    background-color: #cbcbcb;
    border-color: #cbcbcb;
    color: #fff;
}
.btn {
    vertical-align: inherit;
}
.layui-progress-bar {
    position: absolute;
    left: 0;
    top: 0;
    width: 0;
    max-width: 100%;
    height: 6px;
    border-radius: 20px;
    text-align: right;
    background-color: #5FB878;
    transition: all 2s;
    -webkit-transition: all 0.3s;
}
    </style>
  <body>
    <div class="main">
      <div id="appLogin">
        <div class="logins position">
          <div class="mip-reg-logo">
          <div class="mip-reg-heading">首页<button class="ClickBT Bg-Blues" style="height:20px;padding:0 14px;" id="CheckBtn" onclick="CheckUpdate()">检测更新</button></div>
          <div class="mip-reg-body">
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('GradeManaging.php');">
                  <span>年级管理</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('LookMembers.php');">
                  <span>成员管理</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('AddTask.php');">
                  <span>发布任务</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('AddNotice.php');">
                  <span>发布公告</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('LookCIInfo.php');">
                  <span>打卡详情</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('ChooseDT.php');">
                  <span>统计下载</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Greens" onclick="ToHref('//nchat.kayanxin.cn/',1);">
                  <span>在线聊天</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Reds" onclick="AdminLogout();">
                  <span>退出登录</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Reds" onclick="EditAdminPassword();">
                  <span>修改密码</span>
                </button>
              </div>
            </div>
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
  <script src="//api.kayanxin.cn/Update/Dxx/20211119/Assets/Layui/layui.js"></script>
  <script src="/Assets/Js/Main.js"></script>
  <script type="text/javascript">
  var layer,element;
      layui.config({
          base: '/src/js/'
      }).use(['layer','element'],function() {
          layer = layui.layer;
          element = layui.element;
      })
      var NewDate,VLog,Version;
      function CheckUpdate() {
          layer.open({
              type: 1,
              closeBtn: 2,
              id: 'Update',
              title: '检查更新',
              area: '520px',
              //btn: ['取消','关闭'],
              content: '<div class="setchmod bt-form"><div class="update_title"><i class="layui-layer-ico layui-layer-ico20"></i><span>正在获取版本信息……</span></div><div class="update_version">当前版本：V-<?= $VNum/10 ?></a>&nbsp;&nbsp;发布时间：<?= $VDate ?></div><div class="update_newversion" style="display: none;"></div><div class="update_conter"><div class="update_tips"><?= file_get_contents('./VLog.txt') ?></div></div><div class="bt-form-submit-btn" style="display: none;"><button class="ClickBT Bg-Blues" style="height:20px;padding:0 14px;cursor:pointer" id="Update" onclick="Update()">更新</button></div></div>',
              success: function() {
                  setTimeout(function() {
                      $("#Update").css("overflow-x","hidden").parent("div[type='page']").addClass("layer-anim layui-layer-dialog");
                      //获取最新版本信息
                    $.ajax({
                        type: "POST",
                        url: "http://api.kayanxin.cn/Update/Dxx/Update.php",
                        data: {
                            URL: document.URL,
                            Version: '<?= $VNum ?>'
                        },
                        dataType: 'html',
                        success: function (data) {
                            var Data = JSON.parse(data);
                            var div = $("#Update");
                            if (Data.Code == '1') {
                                div.children(".bt-form").children(".update_title").children("i").attr("class","layui-layer-ico layui-layer-ico1");
                            } else if (Data.Code == '2') {
                                div.children(".bt-form").children(".update_title").children("i").attr("class","layui-layer-ico layui-layer-ico0");
                                div.children(".bt-form").children(".update_conter").children(".update_tips").html(Data.VLog);
                                div.children(".bt-form").children(".update_newversion").show().html('最新版本：V-' + ((Data.VNum)/10).toFixed(1) + '&nbsp;&nbsp;发布时间：' + Data.VDate);
                                div.children(".bt-form").children(".bt-form-submit-btn").show();
                                NewDate = Data.VDate;
                                VLog = Data.VLog;
                                Version = Data.VNum;
                            } else {
                                div.children(".bt-form").children(".update_title").children("i").attr("class","layui-layer-ico layui-layer-ico2");
                            }
                            div.children(".bt-form").children(".update_title").children("span").html(Data.Message);
                        }
                    });
                  },200);
              }
          });
      }
      //自动更新
      var Per,AllF;
      function Update() {
          var layalert = layer.confirm('是否进行更新？',{
              icon: 3,
              title: '提示',
              closeBtn: 2,
              btn: ['确定','取消']
          },function() {
              layer.close(layalert);
              var div = $("#Update");
              div.children(".bt-form").children(".update_title").children("i").attr("class","layui-layer-ico layui-layer-ico16");
              div.children(".bt-form").children(".update_title").children("span").html("正在获取更新文件……");
              div.children(".bt-form").children(".update_version").hide();
              div.children(".bt-form").children(".update_conter").children(".update_tips").html("<br><br><center><b>正在获取中……</b></center>");
              div.children(".bt-form").children(".bt-form-submit-btn").hide();
              div.parent("div[type='page']").children(".layui-layer-setwin").hide();
            $.ajax({
                type: "POST",
                url: "http://api.kayanxin.cn/Update/Dxx/Update.php",
                data: {
                    Type: 'Get',
                    URL: document.URL,
                    IP: '<?= $_SERVER['SERVER_ADDR'] ?>',
                    Version: Version
                },
                dataType: 'html',
                success: function (data) {
                    layer.msg('更新中请勿刷新！否则可能会出现不可逆错误',{icon: 4, anim: 6,time: 0,offset: 't'});
                    var Data = JSON.parse(data);
                    var div = $("#Update");
                    var UpdatingLog;
                    if (Data.Code == '1') {
                        UpdatingLog = '<div>总更新文件数：[' + Data.File.length + ']个</div>';
                        for (var i = 0;i < Data.File.length;i++) {
                            var DF = Data.File;
                            UpdatingLog += '<div id="Up-' + (i - (-1)) + '">更新文件名：<span>' + DF[i] + '</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;更新状态：[<b style="color: grey;">Waiting</b>]</div>';
                        }
                        div.children(".bt-form").children(".update_title").children("i").attr("class","layui-layer-ico layui-layer-ico20");
                        div.children(".bt-form").children(".update_newversion").css("margin","unset").html('<div class="layui-progress layui-progress-big" lay-filter="Doing" lay-showpercent="true"><div class="layui-progress-bar layui-bg-blue"></div></div>');
                        Per = 100/(Data.File.length);
                        AllF = Data.File.length;
                        DoUpdate(1);
                    } else {
                        UpdatingLog = '更新已被中断！';
                        div.children(".bt-form").children(".update_title").children("i").attr("class","layui-layer-ico layui-layer-ico2");
                        div.parent("div[type='page']").children(".layui-layer-setwin").show();
                    }
                    div.children(".bt-form").children(".update_title").children("span").html(Data.Message);
                    div.children(".bt-form").children(".update_conter").children(".update_tips").html(UpdatingLog);
                }
            });
          },function() {
              layer.msg('已取消更新！',{
                  icon: 5,
                  anim: 6
              });
          });
      }
      function DoUpdate(Num) {
        setTimeout(function() {
            $.ajax({
                type: "POST",
                url: "/Admin/Home.php",
                data: {
                    Type: 'DoUp',
                    File: $("div[id='Up-" + Num  + "']").children("span").text(),
                    NewDate: NewDate,
                    VLog: VLog,
                    Version: Version
                },
                dataType: 'json',
                success: function (Data) {
                    if (Data.Code == '1') {
                        $("div[id='Up-" + Num  + "']").children("b").css("color","green").text(Data.Message);
                        element.progress('Doing', (Per*Num) + '%');
                        $("div[lay-filter='Doing']").children("div").html('<span class="layui-progress-text">' + (Per*Num).toFixed(1) +'%</span>');
                    } else {
                        $("div[id='Up-" + Num  + "']").children("b").css("color","red").text(Data.Message);
                        element.progress('Doing', (Per*Num) + '%');
                        $("div[lay-filter='Doing']").children("div").html('<span class="layui-progress-text">' + (Per*Num).toFixed(1) +'%</span>');
                    }
                    
                    if (Num - (-1) <= AllF) {
                        DoUpdate(Num - (-1));
                    } else {
                        layer.msg('更新完成！正在刷新页面……[请手动清除浏览器缓存已达到部分功能的更新效果]',{icon: 1,anim: 6,time: 3000},function() {
                            history.go(0);
                        });
                        $("#Update").parent("div[type='page']").children(".layui-layer-setwin").show();
                    }
                }
            });
        }, 100);
      }
  </script>
</html>