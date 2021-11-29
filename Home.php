<?
include("./Includes/Common.php");
if ($islogin2 == 1) {} else {header("Location:/Index.php");}
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= $conf['title'] ?>【首页】</title>
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
          <div class="mip-reg-heading">首页<button class="ClickBT Bg-Blues" style="height:20px;padding:0 14px;" id="CheckBtn" onclick="layer.alert('校录密码LZzx（注册后找回密码需要）',{title: '提示',closeBtn: 2,icon: 6,btn: ['知道了']},function(){layer.msg('正在跳转',{
         icon: 20});window.open('https://school.kayanxin.cn/');});">阆中中学校录</button></div>
          <div class="mip-reg-body">
            <?
            if ($userrow['status'] == 0 || $userrow['status'] == 2) {
            ?>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Greens" onclick="ClockIn();">
                  <span>查询学习情况</span>
                </button>
              </div>
            </div>
            <?
            }
            ?>
            <?
            if ($userrow['status'] == 1) {
            ?>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('LookClasses.php');">
                  <span>查看班级</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('LookMembers.php');">
                  <span>查看成员</span>
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
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('LookCIInfo.php?CID=<?= $userrow['class'] ?>&TYPE=1');">
                  <span>打卡详情</span>
                </button>
              </div>
            </div>
            <?
            } elseif ($userrow['status'] == 2) {
            ?>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <!--button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('LookMembers.php?CID=<?= $userrow['class'] ?>&TYPE=1');"-->
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('LookMembers.php');">
                  <span>查看成员</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('AddTask.php');">
                  <span>查看任务</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <!--button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('LookCIInfo.php?CID=<?= $userrow['class'] ?>&TYPE=1');"-->
                <button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="ToHref('LookCIInfo.php');">
                  <span>打卡详情</span>
                </button>
              </div>
            </div>
            <?
            }
            ?>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Greens" onclick="ToHref('//nchat.kayanxin.cn/',1);">
                  <span>在线聊天</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Reds" onclick="Logout();">
                  <span>退出登录</span>
                </button>
              </div>
            </div>
            <div class="el-form-item">
              <div class="el-form-item__content">
                <button type="button" class="el-button w-100 el-button--primary Bg-Reds" onclick="EditPassword();">
                  <span>修改密码</span>
                </button>
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
  <?
  if ($userrow['status'] == 0) {
      $condition = "WHERE type='1' or type='3' or type='6'";
  } elseif ($userrow['status'] == 1) {
      $condition = "WHERE type='4' or type='5' or type='6'";
  } elseif ($userrow['status'] == 2) {
      $condition = "WHERE type='2' or type='3' or type='5' or type='6'";
  }
  
  $nts = $DB->query("SELECT * FROM Web_Notice {$condition} ORDER BY id DESC")->fetchAll();
  if ($islogin2 == 1) {
  ?>
      layer.open({
          type: 1,
          title: '公告栏',
          closeBtn: 2,
          area: ['300px','500px'],
          id: 'NoticeLists',
          btn: false,
          resize: false,
          content: '<?
          if (!$nts) {
              echo '暂无公告';
          } else {
              foreach ($nts as $n) {
                  $ln = $DB->query("SELECT * FROM Web_NoticeLooked WHERE nbatch='{$n['id']}' and uid='{$userrow['id']}'")->fetch();
                  ?><div class="NoticeList" title="点击查看" onclick="LookNotice(\'<?= $n['id'] ?>\',\'<?= $n['status'] ?>\')" id="<?= $n['id'] ?>"><b><?= $n['title'] ?></b><?= (!$ln ? '<notread id="'.$n['id'].'">未查看</notread>' : '') ?><span><?= $n['addtime'] ?></span><nodismsg><?= $n['msg'] ?></nodismsg></div><?
              }
          }
          ?>',
          shade: false,
          success: function() {
              $("#NoticeLists").parent("div[type='page']").css("position","fixed").css("left","30px").css("top","30px");
              $("#NoticeLists").css("padding","20px");
          }
      });
  <?
  }
  ?>
  <?
  $ntes = $DB->query("SELECT * FROM Web_Notice {$condition} ORDER BY id")->fetchAll();
  foreach ($ntes as $nt) {
  $ln = $DB->query("SELECT * FROM Web_NoticeLooked WHERE nbatch='{$nt['id']}' and uid='{$userrow['id']}'")->fetch();
  if ($ln) {} else {
  ?>
      var open<?= $nt['id'] ?>index = layer.open({
          type: 1,
          title: '<?= oaddslashes($nt['title']) ?>',
          closeBtn: false,
          area: ['350px','400px'],
          id: 'NoticeLooking-<?= $nt['id'] ?>',
          btn: ['确定收到'],
          content: '<center><h2><?= oaddslashes($nt['title']) ?></h2><span style="color: grey;">最后一次编辑时间：<?= $nt['addtime'] ?></span></center><br><hr><br><?= oaddslashes($nt['msg']) ?><br><br><hr><br><div style="float:right;"><p style="font-weight: bold;font-size: 14px;">发布者：<?= ($nt['status'] == 1 ? 'SystemAdmin' : 'GradeAdmin') ?></p></div>',
          success: function() {
              $("#NoticeLooking-<?= $nt['id'] ?>").css("padding","20px").css("height","260px");
          },
          yes: function() {
              var loading = layer.load(1);
              $.ajax({
                  type: "POST",
                  url: "/Ajax-Front.php?Act=IndexInfo&Type=ReadNotice",
                  data: {
                      nid: '<?= $nt['id'] ?>'
                  },
                  dataType: 'json',
                  success: function (data) {
                      if (data.code == 1) {
                          $("notread[id='<?= $nt['id'] ?>']").remove();
                          layer.close(loading);
                          layer.close(open<?= $nt['id'] ?>index);
                      } else {
                          ErrorMsg(data.msg);
                      }
                  }
              });
          }
      });
  <?
  }}
  ?>
  <?
  if ($userrow['id'] == 1) {
  ?>
  var username = '<?= $userrow['username'] ?>';
  <?
  } else {
  ?>
  var username = '<?= $userrow['class'].'班 '.$userrow['username'] ?>';
  <?
  }
  ?>
  <?
  $grade = str_replace('高','',str_replace('级','',$conf['gradename']));
  $file = @file_get_contents('./Txts/classchanged/'.$conf['gradename'].'.txt');
  if ((($grade+1) == date("Y") && !@in_array($userrow['id'],@json_decode($file,true)) && $userrow['id'] != '1') && $test == 'true') {
  ?>
  layer.alert('检测到您为高一学生并且未设置当前<b style="color:blue;">新</b>班级，请点击以下按钮进行操作',{
      icon: 20,
      title: '<b style="color: red;">重要公告！</b>',
      closeBtn: false,
      btn: ['更改班级','未转班'],
      btnAlign: 'c',
      btn2: function() {
        var loading = layer.load(1);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=UnChangeClass",
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
      }
  },function() {
      layer.alert('注意：<b style="color: red;">此次操作只能进行一次，如果操作出错，请联系<u>错误班级团支书或者本年级管理或者网站管理</u>进行更改</b>',{
          icon: 5,
          title: '<b style="color: red;">重要公告！</b>',
          closeBtn: false,
          btn: ['了解了'],
          btnAlign: 'c'
      },function() {
          layer.prompt({
              formType: 0,
              value: '',
              title: '请输入新班级号',
              closeBtn: 2,
              placeholder: '请输入新班级号',
              btn: ['确定'],
              shade: 0.1,
              yes: function(index,promptdiv) {
                  var value = promptdiv.find(".layui-layer-input").val();
                  if (value == '') {
                      ErrorMsg('输入信息不能为空！');
                      return false;
                  }
                  var loading = layer.load(1);
                  $.ajax({
                      type: "POST",
                      url: "/Ajax-Front.php?Act=IndexInfo&Type=ChangeClass",
                      data: {
                          classid: value
                      },
                      dataType: 'json',
                      success: function (data) {
                          layer.close(loading);
                          if (data.code == 1) {
                              CorrectMsg(data.msg);
                              layer.close(index);
                          } else {
                              ErrorMsg(data.msg);
                          }
                      }
                  });
              }
          });
      });
  });
  <?
  }
  ?>
  </script>

</html>