//页面初始化后操作
$(function() {
    $("div[loadname='PageLoadingTip']").remove();
    console.log('\''+document.URL+'\' Loading is end');
});
//初始化pinyin
var pinyindictjs = '<script src="/Assets/Js/pinyin_dict_notone.js"></script>';
var pinyinjs = '<script src="/Assets/Js/pinyinUtil.js"></script>';
$("body").append(pinyindictjs);
$("body").append(pinyinjs);
//获取url参数值
function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg);  //匹配目标参数
    if (r != null) {
        return unescape(r[2]);
    } else {
        return null; //返回参数值
    }
}
//将字符串的字符全部转换为小写字符
function lowerCase(str) {
    let arr = str.split("");
    let newStr = "";
    //通过for循环遍历数组
    for (let i = 0; i < arr.length; i++) {
        if (arr[i] >= 'A' && arr[i] <= 'Z') {
            newStr += arr[i].toLowerCase();
        } else {
            newStr += arr[i];
        }
    }
    return newStr;
}
//将字符串的字符全部转换为大写字符
function upperCase(str) {
    let arr = str.split("");
    let newStr = "";
    // 通过数组的forEach方法来遍历数组
    arr.forEach(function (value) {
        if (value >= 'a' && value <= 'z') {
            newStr += value.toUpperCase();
        } else {
            newStr += value;
        }
    });
    return newStr;
}
//正确消息提示
function CorrectMsg(msg) {
    layer.msg(msg,{
        icon: 1,
        time: 1500,
        anim: 2
    });
}
//错误消息提示
function ErrorMsg(msg) {
    layer.msg(msg,{
        icon: 2,
        time: 1500,
        anim: 6
    });
}
//打开弹窗
function OpenNewWindow(url,title) {
    layer.open({
        type: 1,
        title: title,
        area: ['350px','400px'],
        btn: false,
        id: 'LookGradeInfoDiv',
        content: '<center>加载中</center>',
        resize: false,
        success: function() {
            $("#LookGradeInfoDiv").load(url).css("padding","20px").css("height","315px");
        }
    });
}
//检测输入信息
function CheckInput(type,status = '') {
    if (status == 'select') {
        var tag = 'select';
    } else {
        var tag = 'input';
    }
    var input = $(tag+"[name='"+type+"']").val();
    var phonereg = /^[1][3,4,5,7,8,9][0-9]{9}$/;
    var namereg = /^[\u4E00-\u9FA5]+$/;
    //if (input === '') {
    //    ErrorMsg('输入信息不能为空！');
    //} else {
        if (type == 'username') {
            if (input === '') {
                ErrorMsg('请输入姓名');
                return 'error';
            } else if (!namereg.test(input) && input != 'Administrator') {
                ErrorMsg('姓名必须是中文！');
                return 'error';
            }
        } else if (type == 'password') {
            if (input === '') {
                ErrorMsg('请输入密码');
                return 'error';
            } else if (input.length < 6) {
                ErrorMsg('密码不能少于6位');
                return 'error';
            } else if (input.length > 16) {
                ErrorMsg('密码不能多于16位');
                return 'error';
            }
        } else if (type == 'phone') {
            if (input === '') {
                ErrorMsg('请输入手机号');
                return 'error';
            } else if (input.length < 11 || !phonereg.test(input)) {
                ErrorMsg('请输入正确的手机号');
                return 'error';
            }
        } else if (type == 'selectclass') {
            var username = $("input[name='username']").val();
            if ((input === '' || input === '0') && username != 'Administrator') {
                ErrorMsg('请选择班级');
                return 'error';
            }
        }
    //}
}
//用户登录
$(".User_Login").on("click",function() {
    var username = $("input[name='username']").val();
    var password = $("input[name='password']").val();
    if (username == 'Administrator') {
        var selectclass = 0;
    } else {
        var selectclass = $("select[name='selectclass']").val();
    }
    if (CheckInput('username') == 'error') {
        ErrorMsg('姓名输入有误，请检查');
        return false;
    }
    if (CheckInput('password') == 'error') {
        ErrorMsg('密码输入有误，请检查');
        return false;
    }
    if (CheckInput('selectclass','select') == 'error') {
        ErrorMsg('请选择班级！');
        return false;
    }
    
    var loading = layer.load(3);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=Login",
        data: {
            username: username,
            password: password,
            classid: selectclass
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                setTimeout(function() {
                    layer.msg('正在跳转……',{
                        icon: 16,
                        time: 1500
                    },function(){
                        window.location.href = 'Home.php';
                    });
                }, 1300);
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
});
//退出登录
function Logout() {
    layer.alert('您确定要退出登录？',{
        icon: 3,
        closeBtn: 2,
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=Logout",
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    setTimeout(function() {
                        layer.msg('正在跳转……',{
                            icon: 16,
                            time: 1500
                        },function(){
                            window.location.href = 'Index.php';
                        });
                    }, 1300);
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//跳转到新窗口
function ToHref(url,type = '') {
    if (type == 1) {
        window.open(url);
    } else {
        window.location.href = url;
    }
}
//选择添加方式
function OpenAddClass() {
    var confirmix = layer.confirm('请选择添加方式',{
        icon: 0,
        closeBtn: 2,
        title: '方式选择',
        btn: ['添加单个','批量添加'],
        shade: 0.1
    },function() {
        layer.close(confirmix);
        layer.prompt({
            formType: 0,
            value: '',
            title: '请输入班级',
            closeBtn: 2,
            placeholder: '请输入班级',
            shade: 0.1,
            yes: function(index,promptdiv) {
                var value = promptdiv.find(".layui-layer-input").val();
                if (value == '') {
                    ErrorMsg('输入信息不能为空！');
                    return false;
                }
                if (isNaN(value)) {
                    ErrorMsg('输入信息只能为数字！');
                    return false;
                }
                var loading = layer.load(1);
                $.ajax({
                    type: "POST",
                    url: "/Ajax-Front.php?Act=IndexInfo&Type=AddClass",
                    data: {
                        classval: value
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(loading);
                        if (data.code == 1) {
                            CorrectMsg(data.msg);
                            LoadDiv();
                        } else {
                            ErrorMsg(data.msg);
                        }
                    }
                });
                layer.close(index);
            }
        });
    },function() {
        layer.close(confirmix);
        layer.prompt({
            formType: 2,
            value: '',
            title: '请输入批量班级序号',
            closeBtn: 2,
            placeholder: '请输入批量班级序号【以,隔开】',
            shade: 0.1,
            yes: function(index,promptdiv) {
                var val = (promptdiv.find(".layui-layer-input").val()).split(',');
                if (val == '') {
                    ErrorMsg('输入信息不能为空！');
                    return false;
                }
                if (val.indexOf(',') != '-1') {
                    ErrorMsg('输入信息格式不对！');
                    return false;
                }
                var loading = layer.load(1);
                
                if (($("#ClassLists").text()).indexOf('暂无') != '-1') {
                    //var table = '<table style="width:100%;"><thead><tr><th style="width:5%;">ID</th><th style="width:20%;">班级名</th><th style="width:10%;">成员数量</th><th style="width:15%;">团支书姓名</th><th style="width:20%;">当前周完成情况</th><th style="width:30%;">操作</th></tr></thead><tbody id="TbodyList" style="text-align:center;"></tbody></table>';
                    //$("#ClassLists").html(table);
                }
                
                var c = 0;
                var loadloading = layer.msg('处理中<b>【<b id="AC" style="color:blue;">0</b>/'+val.length+'】</b>',{
                    icon: 20,
                    time: 0
                });
                                
                for (var i = 0;i < val.length;i++) {
                    var value = val[i];
                    $.ajax({
                        type: "POST",
                        url: "/Ajax-Front.php?Act=IndexInfo&Type=AddClass",
                        data: {
                            classval: value
                        },
                        dataType: 'json',
                        success: function (data) {
                            layer.close(loading);
                            if (data.code == 1) {
                                c++;
                                $("b[id='AC']").text(c);
                                if (c >= val.length) {
                                    layer.close(loadloading);
                                    LoadDiv();
                                }
                            } else {
                                ErrorMsg(data.msg);
                            }
                        }
                    });
                }
                layer.close(index);
            }
        });
    });
}
//修改班级信息
function EditClass(id) {
    layer.prompt({
        formType: 0,
        value: $("tr[id="+id+"]").attr("cname"),
        title: '请修改班级号【班级内成员也会转移】',
        closeBtn: 2,
        placeholder: '请修改班级号【班级内成员也会转移】',
        shade: 0.1,
        yes: function(index, promptdiv) {
            var value = promptdiv.find(".layui-layer-input").val();
            if (value == '') {
                ErrorMsg('输入信息不能为空！');
                return false;
            }
            if (isNaN(value)) {
                ErrorMsg('输入信息只能为数字！');
                return false;
            }
            var loading = layer.load(1);
            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=IndexInfo&Type=EditClass",
                data: {
                    id: id,
                    classval: value
                },
                dataType: 'json',
                success: function(data) {
                    layer.close(loading);
                    if (data.code == 1) {
                        CorrectMsg(data.msg);
                        LoadDiv();
                    } else {
                        ErrorMsg(data.msg);
                    }
                }
            });
            layer.close(index);
        }
    });
}
//刷新当前页面（无动作）
function LoadDiv(other = '') {
    var loading = layer.msg('加载中……',{
        icon: 16,
        time: 0
    });
    $("body").load(location.href + other,function() {
        layer.close(loading);
    });
}
//选择任务并查询
function ChangeTask(type = '',gid = '') {
    var classid = $("#selclass").val();
    var val = $("#ChangeTask").val();
    if (val == '0') return false;
    if (type == 'Member') {
        LoadDiv('?CID=' + classid + '&TID=' + val);
    } else if (type == 'Grade') {
        LoadDiv('?GID=' + classid + '&TID=' + val);
    } else if (type == 'MemberAndGrade') {
        LoadDiv('?CID=' + classid + '&TID=' + val + '&GID=' + gid);
    } else/* if (type == 'LookMemberAndGrade') {
        var loading = layer.msg('加载中……',{
            icon: 16,
            time: 0
        });
        $("body").load('LookMembers.php?GID=' + classid + '&TID=' + val + '&CID=' + gid,function() {
            layer.close(loading);
        });
    } else*/ {
        LoadDiv('?CID=' + classid + '&TID=' + val);
    }
}
//搜索页面内班级
function SearchClass(eq = '1',type = '') {
    var val = $("input[name='class']").val();
    if (type == 'Admin' && $("select[id='SelectClassName']").val() != 'All') {
        var classid = $("select[id='SelectClassName']").val();
        $("#TbodyList").children("tr[classid='"+classid+"']").hide();
        if (val == '') {
            $("#TbodyList").children("tr[classid='"+classid+"']").show();
        } else {
            $("#TbodyList").children("tr[classid='"+classid+"']").each(function() {
                var name = $(this).children("td").eq(eq).text();
                if (name.indexOf(val) != '-1') {
                    $(this).show();
                } else if ((pinyinUtil.getPinyin(name, '', false, false)).indexOf(val) != '-1') {
                    $(this).show();
                }
            });
        }
    } else  {
        $("#TbodyList").children("tr").hide();
        if (val == '') {
            $("#TbodyList").children("tr").show();
        } else {
            $("#TbodyList").children("tr").each(function() {
                var name = $(this).children("td").eq(eq).text();
                if (name.indexOf(val) != '-1') {
                    $(this).show();
                } else if ((pinyinUtil.getPinyin(name, '', false, false)).indexOf(val) != '-1') {
                    $(this).show();
                }
            });
        }
    }
}
//删除班级
function Delete(id) {
    layer.alert('您确定要删除此班级？【删除后将不可恢复，班级内所有成员以及旗下的打卡记录和公告查看记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=DelClass",
            data: {
                id: id
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    /*$("#TbodyList").children("tr").each(function() {
                        var name = $(this).children("td").eq(0).text();
                        if (name == id) {
                            $(this).remove();
                        }
                    });*/
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//设置班级团支书
function SetAdmin(id) {
    var num = $("tr[id='"+id+"']").children("td").eq(2).text();
    if (num == '0人') {
        ErrorMsg('班级无成员，请添加成员！');
        return false;
    }
    
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/GetMembers.php",
        data: {
            classid: id
        },
        dataType: 'html',
        success: function (data) {
            layer.close(loading);
            layer.alert(data,{
                title: '选择人员',
                closeBtn: 2,
                btn: false
            });
        }
    });
    
    //ToHref('SetAdmin.php?CID='+id);
}
//选择班级并查询
function SelectClass(tid = '') {
    var val = $("#selclass").val();
    if (val == '' || val == '0') {
        LoadDiv();
    } else if (tid == 'tid') {
        LoadDiv('?CID='+val+'&TID='+$("#ChangeTask").val());
    } else {
        LoadDiv('?CID=' + val);
    }
}
//设置团支部书记
function SetLid(id,type = '') {
    if (type == 1) {
        var classid = id;
        var id = $("#selmember").val();
        var username = $("option[value='"+id+"']").text();
        if (id == '0') {
            return false;
        }
    } else {
        var classid = $("#selclass").val();
        if (classid == '' || classid == '0') {
            ErrorMsg('请选择班级！');
            return false;
        }
    }
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=IndexInfo&Type=SetLid",
        data: {
            id: id,
            classid: classid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                $("button[name='LID']").attr("class","ClickBT Bg-Reds");
                $("button[name='LID']").text("否");
                $("button[id='lid-"+data.lid+"']").attr("class","ClickBT Bg-Greens");
                $("button[id='lid-"+data.lid+"']").text("是");
                if (type == 1) {
                    var div = '<b style="color:red;">'+username+'</b>';
                    $("td[id='"+classid+"-lid']").html(div);
                }
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
//设置代理团支部书记
function SetFLid(id) {
    var classid = $("#selclass").val();
    if (classid == '' || classid == '0') {
        ErrorMsg('请选择班级！');
        return false;
    }
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=IndexInfo&Type=SetFLid",
        data: {
            id: id,
            classid: classid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                if (data.type == 2) {
                    $("button[id='flid-"+data.lid+"']").attr("class","ClickBT Bg-Greens");
                    $("button[id='flid-"+data.lid+"']").text("是");
                } else {
                    $("button[id='flid-"+data.lid+"']").attr("class","ClickBT Bg-Reds");
                    $("button[id='flid-"+data.lid+"']").text("否");
                }
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
//设置团员
function SetVip(id) {
    var classid = $("#selclass").val();
    if (classid == '' || classid == '0') {
        ErrorMsg('请选择班级！');
        return false;
    }
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=IndexInfo&Type=SetVip",
        data: {
            id: id,
            classid: classid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                if (data.type == 2) {
                    $("button[id='Vip-"+data.lid+"']").attr("class","ClickBT Bg-Greens");
                    $("button[id='Vip-"+data.lid+"']").text("是");
                } else {
                    $("button[id='Vip-"+data.lid+"']").attr("class","ClickBT Bg-Reds");
                    $("button[id='Vip-"+data.lid+"']").text("否");
                }
                $("input[name='class']").focus();
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
//选择添加成员方式
function OpenAddMember() {
    var classid = $("#selclass").val();
    var namereg = /^[\u4E00-\u9FA5]+$/;
    
    var confirmix = layer.confirm('请选择添加方式',{
        icon: 0,
        closeBtn: 2,
        title: '方式选择',
        btn: ['添加单个','批量添加'],
        shade: 0.1
    },function() {
        layer.close(confirmix);
        layer.prompt({
            formType: 0,
            value: '',
            title: '请输入姓名（密码默认为123456）',
            closeBtn: 2,
            placeholder: '请输入姓名（密码默认为123456）',
            shade: 0.1,
            yes: function(index,promptdiv) {
                var value = promptdiv.find(".layui-layer-input").val();
                if (value == '') {
                    ErrorMsg('输入信息不能为空！');
                    return false;
                }
                if (!namereg.test(value)) {
                    ErrorMsg('输入信息只能为中文！');
                    return false;
                }
                var loading = layer.load(1);
                $.ajax({
                    type: "POST",
                    url: "/Ajax-Front.php?Act=IndexInfo&Type=AddMember",
                    data: {
                        username: value,
                        classid: classid
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(loading);
                        if (data.code == 1) {
                            CorrectMsg(data.msg);
                            LoadDiv('?CID='+classid);
                        } else {
                            ErrorMsg(data.msg);
                        }
                    }
                });
                layer.close(index);
            }
        });
    },function() {
        layer.close(confirmix);
        layer.prompt({
            formType: 2,
            value: '',
            title: '请输入批量姓名（密码默认为123456）',
            closeBtn: 2,
            placeholder: '请输入批量姓名（密码默认为123456）【以,隔开】',
            shade: 0.1,
            yes: function(index,promptdiv) {
                var val = (promptdiv.find(".layui-layer-input").val()).split(',');
                if (val == '') {
                    ErrorMsg('输入信息不能为空！');
                    return false;
                }
                if (val.indexOf(',') != '-1') {
                    ErrorMsg('输入信息格式不对！');
                    return false;
                }
                var loading = layer.load(1);
                
                if (($("#ClassLists").text()).indexOf('暂无') != '-1') {
                    //var table = '<table style="width:100%;"><thead><tr><th style="width:5%;">ID</th><th style="width:20%;">班级名</th><th style="width:10%;">成员数量</th><th style="width:15%;">团支书姓名</th><th style="width:20%;">当前周完成情况</th><th style="width:30%;">操作</th></tr></thead><tbody id="TbodyList" style="text-align:center;"></tbody></table>';
                    //$("#ClassLists").html(table);
                }
                
                var c = 0;
                var loadloading = layer.msg('处理中<b>【<b id="AM" style="color:blue;">0</b>/'+val.length+'】</b>',{
                    icon: 20,
                    time: 0
                });
                                
                for (var i = 0;i < val.length;i++) {
                    var value = val[i];
                    $.ajax({
                        type: "POST",
                        url: "/Ajax-Front.php?Act=IndexInfo&Type=AddMember",
                        data: {
                            username: value,
                            classid: classid
                        },
                        dataType: 'json',
                        success: function (data) {
                            layer.close(loading);
                            if (data.code == 1) {
                                c++;
                                $("b[id='AM']").text(c);
                                if (c >= val.length) {
                                    layer.close(loadloading);
                                    LoadDiv('?CID='+classid);
                                }
                            } else {
                                ErrorMsg(data.msg);
                            }
                        }
                    });
                }
                layer.close(index);
            }
        });
    });
}
//成员转班
function DoChangeClass(id) {
    var classid = $("#ccselclass").val();
    if (classid == '' || classid == '0') {
        ErrorMsg('请选择班级！');
        return false;
    }
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=IndexInfo&Type=ChangeClass",
        data: {
            mid: id,
            classid: classid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                LoadMemDiv('AndTask');
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
//删除成员
function DeleteUser(id) {
    layer.alert('您确定要删除此成员？【删除后将不可恢复，期间的打卡记录和公告查看记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=DelMember",
            data: {
                id: id
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    /*$("#TbodyList").children("tr").each(function() {
                        var name = $(this).children("td").eq(0).text();
                        if (name == id) {
                            $(this).remove();
                        }
                    });*/
                    LoadMemDiv('AndTask');
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//刷新成员列表
function LoadMemDiv(tid = '',gradeid = '') {
    var classid = $("#selclass").val();
    if (tid == '') {
        LoadDiv('?CID='+classid);
    } else if (tid == 'Grade') {
        LoadDiv('?GID='+classid);
    } else if (tid == 'GradeAndTask') {
        LoadDiv('?GID='+classid+'&TID='+$("#ChangeTask").val());
    } else if (tid == 'GradeAndClassAndTask') {
        LoadDiv('?GID='+gradeid+'&CID='+classid+'&TID='+$("#ChangeTask").val());
    } else if (tid == 'GradeAndClassAndTaskOtherGetInfo') {
        LoadDiv('?GID='+$("#selclass").val()+'&CID='+$("#SelectClassName").val()+'&TID='+$("#ChangeTask").val());
    } else {
        LoadDiv('?CID='+classid+'&TID='+$("#ChangeTask").val());
    }
}
//成员转班
function ChangeClass(id,type = '') {
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/GetClassesToChangs.php",
        data: {
            mid: id,
            type: type
        },
        dataType: 'html',
        success: function (data) {
            layer.close(loading);
            layer.alert(data,{
                title: '选择转向班级',
                closeBtn: 2,
                btn: false
            });
        }
    });
    
    //ToHref('SetAdmin.php?CID='+id);
}
//重置密码
function SetPassword(id) {
    layer.alert('您确定要重置此成员密码？【默认密码为123456】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=SetPassword",
            data: {
                id: id
            },
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
    });
}
//重置所有用户密码
function SetAllPassword() {
    var classid = $("#selclass").val();
    layer.alert('您确定要重置所有成员密码？【默认密码为123456】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=SetAllPassword",
            data: {
                classid: classid
            },
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
    });
}
//修改账户密码
function EditPassword() {
    layer.prompt({
        formType: 1,
        value: '',
        title: '请输入新密码',
        closeBtn: 2,
        placeholder: '请输入新密码',
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
                url: "/Ajax-Front.php?Act=IndexInfo&Type=EditPassword",
                data: {
                    password: value
                },
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);
                    if (data.code == 1) {
                        layer.close(index);
                        CorrectMsg(data.msg);
                        setTimeout(function() {
                            layer.msg('正在跳转……',{
                                icon: 16,
                                time: 1500
                            },function(){
                                window.location.href = 'Index.php';
                            });
                        }, 1300);
                    } else {
                        ErrorMsg(data.msg);
                    }
                }
            });
        }
    });
}
//删除任务
function DeleteTask(id) {
    layer.alert('您确定要删除此任务？【删除后将不可恢复，旗下的打卡记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=DeleteTask",
            data: {
                id: id
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    /*$("#TbodyList").children("tr").each(function() {
                        var name = $(this).children("td").eq(0).text();
                        if (name == id) {
                            $(this).remove();
                        }
                    });*/
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//添加打卡任务
function AddTask(id = '',type = '') {
    var tipmsg = '请输入';
    var title = '';
    var ajaxurl = 'AddTask';
    if (type == 'Edit') {
        tipmsg = '修改';
        var title = $("#"+id).children("td").eq(2).text();
        var ajaxurl = 'EditTask';
    }
    layer.prompt({
        formType: 0,
        value: title,
        title: tipmsg+'任务标题',
        closeBtn: 2,
        placeholder: tipmsg+'任务标题',
        shade: 0.1
    },function(title,tindex) {
        var time = new Date();
        var year = time.getFullYear();
        var month = (time.getMonth() -(-1)) < 10 ? '0' + (time.getMonth() -(-1)) : (time.getMonth() -(-1));
        var timedate = time.getDate();
        var timeenddate = time.getDate() - (-6);
        var startday = (year+'-'+month+'-'+(timedate < 10 ? '0' + timedate : timedate)) + ' 00:00:00';
        var endday = (year+'-'+month+'-'+(timeenddate < 10 ? '0' + timeenddate : timeenddate)) + ' 18:00:00';
        if (type == 'Edit') {
            var startday = $("#"+id).children("td").eq(5).text();
            var endday = $("#"+id).children("td").eq(6).text();
        }
        layer.prompt({
            formType: 0,
            value: startday,
            title: tipmsg+'任务开始时间[示例：2020-09-01 00:00:00]',
            closeBtn: 2,
            placeholder: tipmsg+'任务开始时间[示例：2020-09-01 00:00:00]',
            shade: 0.1
        },function(addtime,aindex) {
            layer.prompt({
                formType: 0,
                value: endday,
                title: tipmsg+'任务结束时间[示例：2020-09-01 18:00:00]',
                closeBtn: 2,
                placeholder: tipmsg+'任务结束时间[示例：2020-09-01 18:00:00]',
                shade: 0.1,
                yes: function(index,promptdiv) {
                    var endtime = promptdiv.find(".layui-layer-input").val();
                    if (title == '' || addtime == '' || endtime == '') {
                        ErrorMsg(''+tipmsg+'信息，信息不能为空！');
                        return false;
                    }
                    if (type == 'Edit') {
                        var ajaxdata = {tid:id,title: title,addtime: addtime,endtime: endtime};
                    } else {
                        var ajaxdata = {title: title,addtime: addtime,endtime: endtime};
                    }
                    var loading = layer.load(1);
                    $.ajax({
                        type: "POST",
                        url: "/Ajax-Front.php?Act=IndexInfo&Type="+ajaxurl,
                        data: ajaxdata,
                        dataType: 'json',
                        success: function (data) {
                            layer.close(loading);
                            if (data.code == 1) {
                                layer.close(tindex);
                                layer.close(aindex);
                                layer.close(index);
                                CorrectMsg(data.msg);
                                LoadDiv();
                            } else {
                                ErrorMsg(data.msg);
                            }
                        }
                    });
                }
            });
        });
    });
}
/*//任务打卡
function ClockIn() {
    layer.alert('先完成大学习后再来打卡！<b style="color:red">[当后台数据自动匹配到您未完成大学习却打了卡，会有严重惩罚！]</b>',{
        icon: 0,
        title: '<b style="color:red">温馨提示！</b>',
        closeBtn: 2,
        btn: ['我已完成大学习！','还未完成']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=ClockIn",
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
    });
}*/
//任务打卡
function ClockIn() {
    layer.msg('正在加载数据进行判断……',{
        icon: 20,
        time: 1500//,
        //shade: 0.5
    },function() {
        var loading = layer.msg('正在连接大学习服务器……',{
            icon: 20,
            time: 0//,
            //shade: 0.5
        });
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=ClockIn",
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == '200') {
                    var json = JSON.stringify(data.data);
                    GetLoginInfo(json);
                } else {
                    //ErrorMsg('连接失败！请重试');
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//获取登录状态
function GetLoginInfo(json) {
    layer.msg('连接成功！正在获取登录信息……',{
        icon: 1,
        time: 1500//,
        //shade: 0.5
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=SetIntoFile",
            data: {
                msg: json
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    GetFinishInfo();
                } else {
                    ErrorMsg('获取失败！请重试');
                }
            }
        });
    });
}
//获取已完成成员列表
function GetFinishInfo() {
    layer.msg('获取成功！正在判断您是否已完成视频学习……',{
        icon: 1,
        time: 1500//,
        //shade: 0.5
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IfThisFinished",
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
    });
}
//批量通过classid添加用户
function ListAddMem() {
    layer.prompt({
        formType: 2,
        value: '',
        title: '请输入信息',
        closeBtn: 2,
        placeholder: '请输入信息【示例：1-张三,李四（每组一行）】',
        shade: 0.1,
        yes: function(index,promptdiv) {
            var value = promptdiv.find(".layui-layer-input").val();
            var time = 0;
            var allcounts = 0;
            var cm = value.split("\n");
            
            var loadmain = layer.open({
                type: 1,
                closeBtn: 2,
                title: '批量处理结果<b>总数<b id="LoadMainNum">【0】</b>人</b>',
                btn: false,
                area: ['270px','470px'],
                id: 'LoadMain',
                content: '<div name="LoadMain" style="text-align:center;"></div>'
            });
            
            for (var i = 0; i < cm.length; i++) {
                var cmcam = (cm[i]).split('-');
                var classid = cmcam[0];
                var username = cmcam[1];
                var ms = username.split(',');
                
                var div = $("div[name='LoadMain']");
                div.append('<b id="class-'+classid+'" style="display:block;font-size:14px;width:100%;margin:7px 0;">'+$("input[id='GradeName']").val()+''+classid+'班批量添加情况：【<b style="color:blue;">0</b>/'+ms.length+'】</b>');
                
                for (var c = 0; c < ms.length; c++) {
                    allcounts++;
                    $("#LoadMainNum").text("【"+allcounts+"】");
                    
                    $.ajax({
                        type: "POST",
                        url: "/Ajax-Front.php?Act=IndexInfo&Type=AddMember",
                        data: {
                            username: ms[c],
                            classid: classid
                        },
                        dataType: 'json',
                        /*timeout: 10000,
                        error: function (XMLHttpRequest,textStatus,errorThrown) {
                            AddMemberAgain(classid,ms[c],time,allcounts);
                        },*/
                        success: function (data) {
                            if (data.code == 1) {
                                time++;
                                var lastc = $("b[id='class-"+data.classid+"']").children("b").text();
                                $("b[id='class-"+data.classid+"']").children("b").text(lastc - (-1));
                                
                                if (time >= allcounts) {
                                    CorrectMsg('批量处理完成！总数为'+allcounts+'人');
                                }
                            } else {
                                ErrorMsg(data.msg);
                            }
                        }
                    });
                }
            }
        }
    });
}
/*//超时重新执行
function AddMemberAgain(classid,username,time,allcounts) {
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=IndexInfo&Type=AddMember",
        data: {
            username: username,
            classid: classid
        },
        dataType: 'json',
        timeout: 10000,
        error: function (XMLHttpRequest,textStatus,errorThrown) {
            AddMemberAgain(classid,username,time,allcounts);
        },
        success: function (data) {
            if (data.code == 1) {
                time++;
                var lastc = $("b[id='class-"+data.classid+"']").children("b").text();
                $("b[id='class-"+data.classid+"']").children("b").text(lastc - (-1));
                
                if (time >= allcounts) {
                    CorrectMsg('批量处理完成！');
                }
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}*/
//查看打卡信息概览图【管理员】
function OpenCLImg() {
    var tid = $("#ChangeTask").val();
    var climgdiv = layer.open({
        type: 1,
        id: 'CLImgDiv',
        area: ['900px', '500px'],
        title: '概览图查看',
        closeBtn: 2,
        btn: ['关闭'],
        content: '',
        success: function() {
            var loading = layer.msg('加载中……', {
                icon: 20,
                time: 0
            });
            //获取班级
            var classes = new Array();
            $("#selclass").children("option").each(function() {
                if ($(this).attr("value") == 'All') {
                } else if ($(this).attr("value") == '0') {
                } else {
                    classes.push($(this).attr("value") + '班');
                }
            });

            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=GetTasksInfo",
                data: {
                    tid: tid
                },
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);/*
                    if (data.indexOf('{"code":-4,"msg":"') != '-1') {
                        var msg = (data.replace('{"code":-4,"msg":"','')).replace('"}','');
                        ErrorMsg(msg);
                        layer.close(climgdiv);
                        return false;
                    } else if (data.indexOf('{"code":-1,"msg":"') != '-1') {
                        var msg = (data.replace('{"code":-1,"msg":"','')).replace('"}','');
                        ErrorMsg(msg);
                        layer.close(climgdiv);
                        return false;
                    }*/
                    
                    if (data.code == '-1' || data.code == '-4') {
                        ErrorMsg(data.msg);
                        layer.close(climgdiv);
                        return false;
                    }
                    //监听div
                    var myChart = echarts.init(document.getElementById('CLImgDiv'));

                    //指定图表的配置项和数据
                    option = {
                        tooltip: {
                            trigger: 'axis'
                        },
                        title: {
                            text: '各班打卡人数线形统计图',
                            x: 'left',
                            left: '30px',
                            top: '-5.5px'
                        },
						legend: {
						    data: [GetTaskName()+' 【已完成】',GetTaskName()+' 【未完成】',GetTaskName()+' 【谎打卡】'],
                            left: '260px'
						}/*,
						grid: {
							top: '50px',
							containLabel: true
						}*/,
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: classes,
                            name: '班级名'
                        },
                        yAxis: {
                            type: 'value',
                            name: '总打卡人数'
                        },
                        series: data
                    };

                    //使用刚指定的配置项和数据显示图表。
                    myChart.setOption(option);

                    //设置css
                    $("div[id='CLImgDiv']").children("div").eq(0).css("overflow", "hidden");
                    $("div[id='CLImgDiv']").children("div").eq(0).children("canvas").css("top", "20px");
                }
            });
        }
    });
}
//查看打卡信息概览图【团支书】
function OpenMyCLImg() {
    var tid = $("#ChangeTask").val();
    var climgdiv = layer.open({
        type: 1,
        id: 'CLImgDiv',
        area: ['400px', '480px'],
        title: '概览图查看',
        closeBtn: 2,
        btn: ['关闭'],
        content: '',
        success: function() {
            var loading = layer.msg('加载中……', {
                icon: 20,
                time: 0
            });
            //获取班级
            var classes = new Array();
            $("#selclass").children("option").each(function() {
                if ($(this).attr("value") == 'All') {
                } else if ($(this).attr("value") == '0') {
                } else {
                    classes.push($(this).attr("value") + '班');
                }
            });

            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=GetTasksInfo",
                data: {
                    tid: tid
                },
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);/*
                    if (data.indexOf('{"code":-4,"msg":"') != '-1') {
                        var msg = (data.replace('{"code":-4,"msg":"','')).replace('"}','');
                        ErrorMsg(msg);
                        layer.close(climgdiv);
                        return false;
                    } else if (data.indexOf('{"code":-1,"msg":"') != '-1') {
                        var msg = (data.replace('{"code":-1,"msg":"','')).replace('"}','');
                        ErrorMsg(msg);
                        layer.close(climgdiv);
                        return false;
                    }*/
                    
                    if (data.code == '-1' || data.code == '-4') {
                        ErrorMsg(data.msg);
                        layer.close(climgdiv);
                        return false;
                    }
                    //监听div
                    var myChart = echarts.init(document.getElementById('CLImgDiv'));

                    //指定图表的配置项和数据
                    option = {
                        tooltip: {
                            trigger: 'axis'
                        },
                        title: {
                            text: '本班打卡人数条形统计图',
                            x: 'center'
                        },
						legend: {
						    data: [GetTaskName()+' 【已完成】',GetTaskName()+' 【未完成】',GetTaskName()+' 【谎打卡】'],
                            bottom: '15px'
						}/*,
						grid: {
							top: '50px',
							containLabel: true
						}*/,
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: classes,
                            name: '班级名'
                        },
                        yAxis: {
                            type: 'value',
                            name: '总打卡人数'
                        },
                        series: data
                    };

                    //使用刚指定的配置项和数据显示图表。
                    myChart.setOption(option);

                    //设置css
                    $("div[id='CLImgDiv']").children("div").eq(0).css("overflow", "hidden");
                    $("div[id='CLImgDiv']").children("div").eq(0).children("canvas").css("top", "20px");
                }
            });
        }
    });
}
//打开文件上传窗口
function CheckIfFinish() {
    layer.alert('<input type="file" id="FFileUpload" onchange="FFFileUpload()" accept=".txt">',{
        title :'<b>请上传统计文档<b style="color:red;">【txt文档为Unicode编码】</b></b>',
        btn: false,
        closeBtn: 2
    });
}
//获取文件内容并判断是否完成了大学习
function FFileUpload(alldo = '',type = '') {
    if (alldo == 'yes') {
        layer.closeAll();
        var i = 0;
        var LoadMainIf = layer.msg('正在判断……',{
            icon: 20,
            time: 0
        });
        
        var tbody = $("tbody[id='TbodyList']").children("tr");
        
        var resultindex = layer.open({
            type: 1,
            title: '未完成成员统计<a href="javascript:DownLoadAll();">【点击下载所有班级图片】</a>',
            btn: false,
            closeBtn: 2,
            area: ['570px','500px'],
            offset: [($("#MainIndex").offset().top - (-15)) + 'px',(window.innerWidth)*0.5 + 'px'],
            id: 'UnFinishedMemListsDiv',
            content: '<table style="text-align:center;width:100%;"><thead><tr><th style="width:10%;">序号</th><th style="width:20%;">姓名</th><th style="width:30%;">班级</th><th style="width:40%;">处理</th></tr></thead><tbody id="UnFinishedMemLists"></tbody></table>',
            shade: false
        });
        
        if (type == 1) {
            AllCheckIfFM(tbody,i,'',LoadMainIf,'2');
        } else {
            //CheckIfFM(tbody,i,'',LoadMainIf,type,'');
        }
        
        
        return false;
    }
    /*var fileObj = $("#FFileUpload")[0].files[0];
    if (typeof (fileObj) == "undefined" || fileObj.size <= 0) {
        ErrorMsg('请上传文件');
        return false;
    }
    var File = new FileReader();
    //File.readAsText(fileObj, 'utf-8');
    File.readAsText(fileObj, 'Unicode');
            
    File.onload = function() {
        var txt = this.result;
        layer.closeAll();
        var i = 0;
        var LoadMainIf = layer.msg('正在判断……',{
            icon: 20,
            time: 0
        });
        
        var tbody = $("tbody[id='TbodyList']").children("tr");
        
        var resultindex = layer.open({
            type: 1,
            title: '未完成成员统计',
            btn: false,
            closeBtn: 2,
            area: ['570px','500px'],
            offset: [($("#MainIndex").offset().top - (-15)) + 'px',(window.innerWidth)*0.5 + 'px'],
            id: 'UnFinishedMemListsDiv',
            content: '<table style="text-align:center;width:100%;"><thead><tr><th style="width:10%;">序号</th><th style="width:20%;">姓名</th><th style="width:30%;">班级</th><th style="width:40%;">处理</th></tr></thead><tbody id="UnFinishedMemLists"></tbody></table>',
            shade: false
        });
        
        if (type == 1) {
            AllCheckIfFM(tbody,i,txt,LoadMainIf,1);
        } else {
            CheckIfFM(tbody,i,txt,LoadMainIf,'',1);
        }
    }*/
}
//获取文件内容并判断是否完成了大学习
function FFFileUpload(alldo = '',type = '') {
    if (alldo == 'yes') {
        layer.closeAll();
        var i = 0;
        var LoadMainIf = layer.msg('正在判断……',{
            icon: 20,
            time: 0
        });
        
        var tbody = $("tbody[id='TbodyList']").children("tr");
        
        var resultindex = layer.open({
            type: 1,
            title: '未完成成员统计<a href="javascript:DownLoadAll();">【点击下载所有班级图片】</a>',
            btn: false,
            closeBtn: 2,
            area: ['570px','500px'],
            offset: [($("#MainIndex").offset().top - (-15)) + 'px',(window.innerWidth)*0.5 + 'px'],
            id: 'UnFinishedMemListsDiv',
            content: '<table style="text-align:center;width:100%;"><thead><tr><th style="width:10%;">序号</th><th style="width:20%;">姓名</th><th style="width:30%;">班级</th><th style="width:40%;">处理</th></tr></thead><tbody id="UnFinishedMemLists"></tbody></table>',
            shade: false
        });
        
        if (type == 1) {
            AllCheckIfFM(tbody,i,'',LoadMainIf,'2');
        } else {
            CheckIfFM(tbody,i,'',LoadMainIf,type,'');
        }
        
        
        return false;
    }
    var fileObj = $("#FFileUpload")[0].files[0];
    if (typeof (fileObj) == "undefined" || fileObj.size <= 0) {
        ErrorMsg('请上传文件');
        return false;
    }
    var File = new FileReader();
    //File.readAsText(fileObj, 'utf-8');
    File.readAsText(fileObj, 'Unicode');
            
    File.onload = function() {
        var txt = this.result;
        layer.closeAll();
        var i = 0;
        var LoadMainIf = layer.msg('正在判断……',{
            icon: 20,
            time: 0
        });
        
        var tbody = $("tbody[id='TbodyList']").children("tr");
        
        var resultindex = layer.open({
            type: 1,
            title: '未完成成员统计<a href="javascript:DownLoadAll();">【点击下载所有班级图片】</a>',
            btn: false,
            closeBtn: 2,
            area: ['570px','500px'],
            offset: [($("#MainIndex").offset().top - (-15)) + 'px',(window.innerWidth)*0.5 + 'px'],
            id: 'UnFinishedMemListsDiv',
            content: '<table style="text-align:center;width:100%;"><thead><tr><th style="width:10%;">序号</th><th style="width:20%;">姓名</th><th style="width:30%;">班级</th><th style="width:40%;">处理</th></tr></thead><tbody id="UnFinishedMemLists"></tbody></table>',
            shade: false
        });
        
        if (type == 1) {
            AllCheckIfFM(tbody,i,txt,LoadMainIf,1);
        } else {
            CheckIfFM(tbody,i,txt,LoadMainIf,'',1);
        }
    }
}
//if finished
function CheckIfFM(tbody,i,txt,index,type = '',ifcheck = '') {
    setTimeout(function() {
        var tlen = $("tbody[id='TbodyList']").children("tr").length;
        i++;
        
        if (i >= 9) {
            $("#ClassLists").scrollTop($("#ClassLists").scrollTop() - -(22));
        }
        
        var name = tbody.eq(i - 1).children("td").eq(1).text();
        var classname = tbody.eq(i - 1).children("td").eq(3).text() + '团支部';
                
        var tbdiv = $("tbody[id='UnFinishedMemLists']");
        var tdlen = tbdiv.children("tr").length || 0;
                
        var tddiv = '<tr id="'+tbody.eq(i - 1).attr("id")+'"><td>'+(tdlen - (-1))+'</td><td>'+((name.replace('【未完成（谎打卡）】','')).replace('【未完成（已超时）】','')).replace('【未打卡】','')+'</td><td>'+tbody.eq(i - 1).children("td").eq(3).text()+'</td><td style="color:grey;">处理中……</td></tr>';
        
        if (tbody.eq(i - 1).attr("ifcheck") == 'true') {
            if (name.indexOf('已完成') == -1) {
                tbdiv.append(tddiv);
                $("#UnFinishedMemListsDiv").scrollTop($("#UnFinishedMemListsDiv").prop("scrollHeight"));
                if (tbody.eq(i - 1).attr("resultstatus") == '2') {
                    var msg = '【未完成（谎打卡）】';
                } else if (tbody.eq(i - 1).attr("resultstatus") == '3') {
                    var msg = '【未打卡】';
                }
                $("tbody[id='UnFinishedMemLists']").children("tr[id='"+tbody.eq(i - 1).attr("id")+"']").children("td").eq(3).css("color","red").text(msg);
                //setUnFinish(tbody.eq(i - 1).attr("id"),2,'','',type);
            }/* else {
                setUnFinish(tbody.eq(i - 1).attr("id"),1,'','',type);
            }*/
        } else {
            tbody.eq(i - 1).attr("ifcheck","true");
            
            if (SearchInArr(txt,name,classname) == 'true') {
                tbody.eq(i - 1).children("td").eq(1).html(name + '<b style="color:green;">【已完成】</b>');
                $("#UnFinishedMemListsDiv").scrollTop($("#UnFinishedMemListsDiv").prop("scrollHeight"));
                
                setUnFinish(tbody.eq(i - 1).attr("id"),1,'','',type);
            } else {
                tbody.eq(i - 1).attr("uncl","true");
                if ((tbody.eq(i - 1).children("td").eq(4).attr("time")) >= $("#TaskEndTime").val()) {
                    var unfres = '（已超时）';
                } else {
                    var unfres = '（谎打卡）';
                }
                
                tbody.eq(i - 1).children("td").eq(1).html(name + '<b style="color:red;">【未完成'+unfres+'】</b>');
                $("#UnFinishedMemListsDiv").scrollTop($("#UnFinishedMemListsDiv").prop("scrollHeight"));
                
                setUnFinish(tbody.eq(i - 1).attr("id"),2,'','',type);
                
                if (tbdiv.text() == '暂无数据') {
                    tbdiv.html(tddiv);
                } else {
                    tbdiv.append(tddiv);
                }
            }
        }
        
        if (i >= tlen) {
            layer.close(index);
            CorrectMsg('检查完毕！'+(ifcheck == '' ? '' : '上传文件结算后，页面刷新获取未打卡人员，刷新完毕后重新点击【检测是否完成学习】即可'));
            if (ifcheck == '1') {
                var resin = $("#UnFinishedMemListsDiv").parent("div[type='page']").attr("times");
                layer.close(resin);
                SetUnClockIn();
            } else {
                $("#UnFinishedMemListsDiv").append('<div id="DDButton"><br><hr><br><center><button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="DivideIntoClasses();" style="width:30%;padding:10px;cursor:pointer;"><span>结果分班排列</span></button></center></div>');
                $("#ClickCheckBtn").attr('onclick','layer.msg(\'正在生成结果……\',{icon: 20,time: 3000},function(){FFileUpload(\'yes\''+(type == 1 ? ',\'1\'' : '')+');});');
            }
            return false;
        }
        CheckIfFM(tbody,i,txt);
    }, 1);
}
//判断是否指定值存在于数组
function SearchInArr(txt,name,classname) {
    var tarr = txt.split(',');
    var res = '';
    for (var i = 0;i < tarr.length;i++) {
        var t = tarr[i];
        if (t.indexOf(name) != -1 && t.indexOf(classname) != -1) {
            res = 'true';
            break;
        }
    }
    return res;
}
//设置未打卡人员
function SetUnClockIn(type = '') {
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=SetUnClockIn",
        data: {
            tid: $("#ChangeTask").val()
        },
        dataType: 'json',
        success: function (data) {
            if (data.code == 1) {
                CorrectMsg(data.msg);
                if (type == 1) {} else {
                    LoadMemDiv('tid');
                }
            } else {
                ErrorMsg(data.msg);
            };
        }
    });
}
//设置打卡记录为未完成
function setUnFinish(id,status,admin = '',gid = '',type = '') {
    if (admin == '1') {
        var ajaxname = 'Admin';
        var ajaxdo = {
            cid: id,
            gid: gid,
            status: status
        };
    } else if (type == '1') {
        var ajaxname = 'Admin';
        var ajaxdo = {
            cid: id,
            gid: $("input[id='gid']").val(),
            status: status
        };
    } else {
        var ajaxname = '';
        var ajaxdo = {
            cid: id,
            status: status
        };
    }
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act="+ajaxname+"IndexInfo&Type=SetUnFinish",
        data: ajaxdo,
        dataType: 'json',
        success: function (data) {
            if (data.code == 1) {
                $("tbody[id='UnFinishedMemLists']").children("tr[id='"+id+"']").children("td").eq(3).css("color","green").text(data.msg)
            } else {
                $("tbody[id='UnFinishedMemLists']").children("tr[id='"+id+"']").children("td").eq(3).css("color","red").text(data.msg)
            };
        }
    });
}
//总管理设置打卡记录为未完成
function AdminsetUnFinish(id,status,admin = '',gid = '',type = '') {
    var ajaxdo = {
        cid: id,
        gid: gid,
        status: status
    };
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=SetUnFinish",
        data: ajaxdo,
        dataType: 'json',
        success: function (data) {
            if (data.code == 1) {
                $("tbody[id='UnFinishedMemLists']").children("tr[id='"+id+"']").children("td").eq(3).css("color","green").text(data.msg)
            } else {
                $("tbody[id='UnFinishedMemLists']").children("tr[id='"+id+"']").children("td").eq(3).css("color","red").text(data.msg)
            };
        }
    });
}
//结果分班排列操作
function DivideIntoClasses() {
    var arrs = new Array();
    var i = 0;
    var CollectArrLoading = layer.msg('正在收集结果信息……', {
        icon: 20,
        time: 0
    });
    $("#UnFinishedMemListsDiv").css("overflow-x","hidden");
    $("div[id='DDButton']").remove();
    $("#UnFinishedMemLists").children("tr").each(function() {
        i++;
        var arr = '{"id":"'+$(this).children("td").eq(0).text()+'","name":"'+$(this).children("td").eq(1).text()+'","classname":"'+$(this).children("td").eq(2).text()+'","dores":"'+$(this).children("td").eq(3).text()+'"}';
        arrs.push(arr);
        if (i == $(this).length) {
            layer.close(CollectArrLoading);
            $("#UnFinishedMemLists").html("");
            var classes = new Array();
            $("#selclass").children("option").each(function() {
                if ($(this).attr("value") == 'All') {
                } else if ($(this).attr("value") == '0') {
                } else {
                    classes.push($(this).attr("value"));
                }
            });
            
            var ListLoading = layer.msg('收集完毕，正在排列',{
                icon: 20,
                time: 0
            });
            //先插入班级
            for (var c = 0;c < classes.length;c++) {
                var adddiv = '<tr id="TrTitle" num="'+classes[c]+'"><td><center><div onclick="SaveMsgImg(\''+classes[c]+'\');">'+$("input[id='GradeName']").val()+''+classes[c]+'班【<b id="'+classes[c]+'-count" style="color: black;">0</b>人】：</div></center></td></tr>';
                $("tbody[id='UnFinishedMemLists']").append(adddiv);
            }
            //再执行函数
            DoListMemsFunc('0',arrs,ListLoading);
            SetUnClockIn('1');
        }
    });
}
//执行排列未完成成员函数
function DoListMemsFunc(d,arrs,ListLoading) {
    setTimeout(function() {
        var msg = JSON.parse(arrs[d]);
        var num = ((msg.classname).replace($("input[id='GradeName']").val()+'','')).replace('班','');
        var div = '<tr class="name-'+num+'" id="'+msg.id+'"><td>'+msg.id+'</td><td>'+msg.name+'</td><td>'+msg.classname+'</td><td style="color: green;">'+msg.dores+'</td></tr>';
        //var titlediv = $("tr[id='TrTitle'][num='"+num+"']").prop("outerHTML");
        //$("tr[id='TrTitle'][num='"+num+"']").prop("outerHTML",titlediv + div);
        $("tr[id='TrTitle'][num='"+num+"']").after(div);
        var count = $("b[id='"+num+"-count']").text();
        $("b[id='"+num+"-count']").text(count - (-1));
        d++;
        if (d >= arrs.length) {
            layer.close(ListLoading);
            CorrectMsg('排列完毕！【点击班级名可下载全部图片】');
            return false;
        } else {
            DoListMemsFunc(d,arrs,ListLoading);
        }
    }, 10);
}
//点击保存对应班级成员内容为图片
function SaveMsgImg(id) {
    var count = $("b[id='"+id+"-count']").text();
    if (count <= 0) {
        ErrorMsg('无未完成成员');
        return false;
    }
    var ImgSaveLoading = layer.msg('生成图片中……',{
        icon: 20,
        time: 0,
        shade: 0.01
    });
    var stdiv = '<table id="STUnFinishedMemListsAll" style="text-align:center;width:100%;"><thead><tr><th style="width:10%;">序号</th><th style="width:20%;">姓名</th><th style="width:30%;">班级</th><th style="width:40%;">处理</th></tr></thead><tbody id="STUnFinishedMemLists"><tr id="TrTitle" num="'+id+'"><td><center><div>'+$("input[id='GradeName']").val()+''+id+'班：</div></center></td></tr></tbody></table>';
    $("body").append(stdiv);
    
    var i = 0;
    var arrs = new Array();
    
    //检测班级是否为1个
    var classes = new Array();
    $("#selclass").children("option").each(function() {
        classes.push($(this).attr("value"));
    });
    
    $("#TbodyList").children("tr[uncl='true']").each(function() {
        if ($(this).children("td").eq(3).text() == $("input[id='GradeName']").val()+''+id+'班') {
            i++;
            var dores = ($(this).children("td").eq(1).text()).replace(((($(this).children("td").eq(1).text()).replace('【未完成（谎打卡）】','')).replace('【未完成（已超时）】','')).replace('【未打卡】',''),'');
            
            var arr = '{"id":"'+i+'","name":"'+((($(this).children("td").eq(1).text()).replace('【未完成（谎打卡）】','')).replace('【未完成（已超时）】','')).replace('【未打卡】','')+'","classname":"'+$("input[id='GradeName']").val()+''+id+'班","dores":"'+dores+'"}';
            arrs.push(arr);
        }
    });
    for (var c = 0;c < arrs.length;c++) {
        var msg = JSON.parse(arrs[c]);
        var div = '<tr class="name-'+id+'" id="'+msg.id+'"><td>'+msg.id+'</td><td>'+msg.name+'</td><td>'+msg.classname+'</td><td style="color: green;">'+msg.dores+'</td></tr>';
        $("#STUnFinishedMemLists").append(div);
    }
    
    html2canvas(document.querySelector("#STUnFinishedMemListsAll")).then(canvas => {
        $("table[id='STUnFinishedMemListsAll']").prop("outerHTML","");
        var a = '<a id="DownloadImg" onclick="" href="'+canvas.toDataURL("image/png")+'" download="'+$("input[id='GradeName']").val()+''+id+'班未完成成员表.png"style="top: 12px;left: 5px;position: relative;">点击下载</a>';
        /*if ($("a[id='DownloadImg']").html() == undefined) {
            $("body").prepend(a);
        } else {
            $("a[id='DownloadImg']").attr("href",canvas.toDataURL("image/png"));
        }*/
        layer.close(ImgSaveLoading);
        var DownloadAlert = layer.open({
            type: 1,
            content: a,
            area: ['250px','100px'],
            id: $("input[id='GradeName']").val()+'-'+id,
            closeBtn: false,
            title: '点击按钮下载',
            shadeClose: true,
            btn: false
        });
        $("a[id='DownloadImg']").attr("onclick","layer.close('"+DownloadAlert+"');")
    //$("a[id='DownloadImg']").trigger("click");
    });
}
//查看班级详细信息
function LookInfo(id) {
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/GetClassInfo.php",
        data: {
            classid: id
        },
        dataType: 'html',
        success: function (data) {
            layer.close(loading);
            layer.open({
                title: $("input[id='GradeName']").val()+''+id+'班信息查看',
                closeBtn: 2,
                btn: ['关闭'],
                area: ['560px','460px'],
                content: data
            });
        }
    });
}
//打开添加公告界面
function OpenAddNotice() {
    var openindex = layer.open({
        type: 1,
        closeBtn: 2,
        area: ['500px','490px'],
        title: '添加公告',
        resize: false,
        btn: ['添加','关闭'],
        id: 'AddNoticeLO',
        content: '<div class="CIIndex"><lable>公告标题</lable><input type="text" name="title" placeholder="请输入公告标题" /></div><div class="CIIndex"><lable>公告内容</lable><textarea type="text" name="msg" placeholder="请输入公告内容" ></textarea></div><div class="CIIndex"><lable>公告查看对象</lable><select id="AddNotice" name="looktype"><option value="0">请选择查看对象</option><option value="1">普通成员</option><option value="2">各班团支书</option><option value="3">所有下级</option></select></div>',
        success: function() {
            $("#AddNoticeLO").css("padding","20px").css("height","350px");
        },
        yes: function() {
            var title = $("input[name='title']").val();
            var msg = $("textarea[name='msg']").val();
            var looktype = $("select[name='looktype']").val();
            if (title == '') {
                ErrorMsg('公告标题不能为空');
                return false;
            }
            if (msg == '') {
                ErrorMsg('公告内容不能为空');
                return false;
            }
            if (looktype == '' || looktype == '0') {
                ErrorMsg('请选择公告查看对象');
                return false;
            }
            var loading = layer.msg('添加中',{
                icon: 20,
                time: 0
            });
            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=IndexInfo&Type=AddNotice",
                data: {
                    title: title,
                    msg: msg.replace(/\n/g,'<br>'),
                    looktype: looktype
                },
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        CorrectMsg(data.msg);
                        setTimeout(function() {
                            layer.close(openindex);
                            LoadDiv();
                        }, 2000);
                    } else {
                        ErrorMsg(data.msg);
                    };
                }
            });
        }
    });
}
//查看公告内容
function LookNoticeMsg(id,admin = '') {
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "Get"+admin+"NoticeInfo.php",
        data: {
            nid: id
        },
        dataType: 'html',
        success: function (data) {
            layer.close(loading);
            layer.alert(data,{
                closeBtn: 2,
                title: '【ID-'+id+'】公告内容查看',
                btn: ['关闭']
            });
        }
    });
}
//修改公告内容
function OpenEditNotice(id) {
    var tr = $("tr[id='"+id+"']");
    var type = tr.children("td").eq(5).attr("looktype");
    
    var openindex = layer.open({
        type: 1,
        closeBtn: 2,
        area: ['500px','490px'],
        title: '修改公告',
        resize: false,
        btn: ['修改','关闭'],
        id: 'AddNoticeLO',
        content: '<div class="CIIndex"><lable>公告标题</lable><input type="text" name="title" placeholder="请输入公告标题" value="'+tr.children("td").eq(2).text()+'" /></div><div class="CIIndex"><lable>公告内容</lable><textarea type="text" name="msg" placeholder="请输入公告内容" >'+tr.children("td").eq(3).children("msg").html()+'</textarea></div><div class="CIIndex"><lable>公告查看对象</lable><select id="AddNotice" name="looktype"><option value="0">请选择查看对象</option><option value="1"'+(type == 1 ? ' selected="selected"' : '')+'>普通成员</option><option value="2"'+(type == 2 ? ' selected="selected"' : '')+'>各班团支书</option><option value="3"'+(type == 3 ? ' selected="selected"' : '')+'>所有下级</option></select></div>',
        success: function() {
            $("#AddNoticeLO").css("padding","20px").css("height","350px");
        },
        yes: function() {
            var title = $("input[name='title']").val();
            var msg = $("textarea[name='msg']").val();
            var looktype = $("select[name='looktype']").val();
            if (title == '') {
                ErrorMsg('公告标题不能为空');
                return false;
            }
            if (msg == '') {
                ErrorMsg('公告内容不能为空');
                return false;
            }
            if (looktype == '' || looktype == '0') {
                ErrorMsg('请选择公告查看对象');
                return false;
            }
            var loading = layer.msg('修改中',{
                icon: 20,
                time: 0
            });
            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=IndexInfo&Type=EditNotice",
                data: {
                    id: id,
                    title: title,
                    msg: msg.replace(/\n/g,'<br>'),
                    looktype: looktype
                },
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        CorrectMsg(data.msg);
                        setTimeout(function() {
                            layer.close(openindex);
                            LoadDiv();
                        }, 2000);
                    } else {
                        ErrorMsg(data.msg);
                    };
                }
            });
        }
    });
}
//删除公告
function DeleteNotice(id) {
    layer.alert('您确定要删除此公告？【删除后将不可恢复！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=DeleteNotice",
            data: {
                id: id
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    /*$("#TbodyList").children("tr").each(function() {
                        var name = $(this).children("td").eq(0).text();
                        if (name == id) {
                            $(this).remove();
                        }
                    });*/
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//查看哪些成员查看公告
function LookLookingMen(id) {
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "GetWhoLooked.php",
        data: {
            nid: id
        },
        dataType: 'html',
        success: function (data) {
            layer.close(loading);
            layer.alert(data,{
                closeBtn: 2,
                title: '【ID-'+id+'】公告查看成员查看',
                btn: ['关闭']
            });
        }
    });
}
//查看公告
function LookNotice(id,status) {
    var div = $("div[id='"+id+"']");
    var openindex = layer.open({
          type: 1,
          title: div.children("b").text(),
          closeBtn: false,
          area: ['350px','400px'],
          id: 'NoticeLooking-'+id,
          btn: ['确定收到'],
          content: '<center><h2>'+div.children("b").text()+'</h2><span style="color: grey;">最后一次编辑时间：'+div.children("span").text()+'</span></center><br><hr><br>'+div.children("nodismsg").html()+'<br><br><hr><br><div style="float:right;"><p style="font-weight: bold;font-size: 14px;">发布者：'+(status == '1' ? 'SystemAdmin' : 'GradeAdmin')+'</p></div>',
          success: function() {
              $("#NoticeLooking-"+id).css("padding","20px").css("height","260px");
          },
          yes: function() {
              var loading = layer.load(1);
              $.ajax({
                  type: "POST",
                  url: "/Ajax-Front.php?Act=IndexInfo&Type=ReadNotice",
                  data: {
                      nid: id
                  },
                  dataType: 'json',
                  success: function (data) {
                      if (data.code == 1) {
                          $("notread[id='"+id+"']").remove();
                          layer.close(loading);
                          layer.close(openindex);
                      } else {
                          ErrorMsg(data.msg);
                      }
                  }
              });
          }
      });
}
//获取多选列表(return)
function GetCheckedList() {
    var arr = new Array();
    var classid = $("select[id='SelectClassName']").val();
    if (classid == 'All' || classid == undefined) {
        $("input[name='SelectInfoList']").each(function() {
            if ($(this).is(":checked")) {
                arr.push($(this).val());
            }
        });
    } else {
        $("tr[classid='"+classid+"']").each(function() {
            if ($(this).children("td").eq(0).children("input[name='SelectInfoList']").is(":checked")) {
                arr.push($(this).children("td").eq(0).children("input[name='SelectInfoList']").val());
            }
        });
    }
    return arr;
}
//获取复选框列表(return)
function GetAllCheckedList() {
    var arr = new Array();
    var classid = $("select[id='SelectClassName']").val();
    if (classid == 'All' || classid == undefined) {
        $("input[name='SelectInfoList']").each(function() {
            arr.push($(this).val());
        });
    } else {
        $("tr[classid='"+classid+"']").each(function() {
            arr.push($(this).children("td").eq(0).children("input[name='SelectInfoList']").val());
        });
    }
    return arr;
}
/*以下为管理员js*/
//管理登录
$(".Admin_Login").on("click",function() {
    var username = $("input[name='username']").val();
    var password = $("input[name='password']").val();
    /*if (username != 'Admin') {
        ErrorMsg('账号输入有误，请检查');
        return false;
    }*/
    if (username == '') {
        ErrorMsg('账号不能为空！');
        return false;
    }
    if (CheckInput('password') == 'error') {
        ErrorMsg('密码输入有误，请检查');
        return false;
    }
    
    var loading = layer.load(3);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=AdminLogin",
        data: {
            username: username,
            password: password
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                setTimeout(function() {
                    layer.msg('正在跳转……',{
                        icon: 16,
                        time: 1500
                    },function(){
                        window.location.href = 'Home.php';
                    });
                }, 1300);
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
});
//退出登录
function AdminLogout() {
    layer.alert('您确定要退出登录？',{
        icon: 3,
        closeBtn: 2,
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminLogout",
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    setTimeout(function() {
                        layer.msg('正在跳转……',{
                            icon: 16,
                            time: 1500
                        },function(){
                            window.location.href = 'Index.php';
                        });
                    }, 1300);
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//修改密码
function EditAdminPassword() {
    layer.prompt({
        formType: 1,
        value: '',
        title: '请输入新密码',
        closeBtn: 2,
        placeholder: '请输入新密码',
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
                url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=EditPassword",
                data: {
                    password: value
                },
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);
                    if (data.code == 1) {
                        layer.close(index);
                        CorrectMsg(data.msg);
                        setTimeout(function() {
                            layer.msg('正在跳转……',{
                                icon: 16,
                                time: 1500
                            },function(){
                                window.location.href = 'Index.php';
                            });
                        }, 1300);
                    } else {
                        ErrorMsg(data.msg);
                    }
                }
            });
        }
    });
}
//改名
function EditName(id) {
    layer.prompt({
        formType: 0,
        value: '',
        title: '请输入新名字',
        closeBtn: 2,
        placeholder: '请输入新名字',
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
                url: "/Ajax-Front.php?Act=IndexInfo&Type=EditName",
                data: {
                    id: id,
                    name: value
                },
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);
                    if (data.code == 1) {
                        layer.close(index);
                        CorrectMsg(data.msg);
                        $("#"+id).children("td").eq(2).text(value);
                    } else {
                        ErrorMsg(data.msg);
                    }
                }
            });
        }
    });
}
//改名
function AdminEditName(id) {
    layer.prompt({
        formType: 0,
        value: '',
        title: '请输入新名字',
        closeBtn: 2,
        placeholder: '请输入新名字',
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
                url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=EditName",
                data: {
                    id: id,
                    gid: $("input[name='gradeid']").val(),
                    name: value
                },
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);
                    if (data.code == 1) {
                        layer.close(index);
                        CorrectMsg(data.msg);
                        $("#"+id).children("td").eq(2).text(value);
                    } else {
                        ErrorMsg(data.msg);
                    }
                }
            });
        }
    });
}
//添加年级
function AddGrade() {
    layer.prompt({
        formType: 0,
        value: '',
        title: '请输入年级名',
        closeBtn: 2,
        placeholder: '请输入年级名',
        shade: 0.1,
        yes: function (index, promptdiv) {
            var name = promptdiv.find(".layui-layer-input").val();
            layer.prompt({
                formType: 0,
                value: '',
                title: '请输入年级号【为该年级唯一值，好查询，不能包含字符】',
                closeBtn: 2,
                placeholder: '请输入年级号【为该年级唯一值，好查询，不能包含字符】',
                shade: 0.1,
                yes: function (index, promptdiv) {
                    var gid = promptdiv.find(".layui-layer-input").val();
                    layer.prompt({
                        formType: 0,
                        value: '',
                        title: '请输入组织ID【大学习官网后台对应团支部ID，此信息用于打卡时的判断】',
                        closeBtn: 2,
                        placeholder: '请输入组织ID【大学习官网后台对应团支部ID，此信息用于打卡时的判断】',
                        shade: 0.1,
                        yes: function (index, promptdiv) {
                            var oid = promptdiv.find(".layui-layer-input").val();
                            if (name == '' || gid == '' || oid == '') {
                                ErrorMsg('输入信息不能为空！');
                                return false;
                            }
                            if (isNaN(oid)) {
						        ErrorMsg('组织ID只能为数字！');
						        return false;
					        }
                            var loading = layer.load(1);
                            $.ajax({
                                type: "POST",
                                url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=AddGrade",
                                data: {
                                    name: name,
                                    gid: gid,
                                    oid: oid
                                },
                                dataType: 'json',
                                success: function (data) {
                                    layer.close(loading);
                                    if (data.code == 1) {
                                        layer.alert(data.msg, {
                                            icon: 1,
                                            closeBtn: 2,
                                            title: '返回结果'
                                        }, function () {
                                            LoadDiv();
                                        });
                                    } else {
                                        ErrorMsg(data.msg);
                                    }
                                }
                            });
                            layer.close(index);
                        }
                    });
                    layer.close(index);
                }
            });
        }
    });
}
//删除年级
function DeleteGrade(id) {
    layer.alert('您确定要删除此年级？【删除后将不可恢复！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=DelGrade",
            data: {
                id: id
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//重置密码
function SetAdminPassword(id) {
    layer.alert('您确定要重置此年级管理密码？【默认密码为123456789】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=SetAdminPassword",
            data: {
                id: id
            },
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
    });
}
//查看班级详细信息
function LookGradeInfo(id,title) {
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "GetGradeInfo.php",
        data: {
            gradeid: id
        },
        dataType: 'html',
        success: function (data) {
            layer.close(loading);
            layer.open({
                title: title+'信息查看',
                closeBtn: 2,
                btn: ['关闭'],
                area: ['530px','460px'],
                content: data
            });
        }
    });
}
//添加打卡任务
function AddAllTask(id = '', type = '') {
    var tipmsg = '请输入';
    var title = '';
    var ajaxurl = 'AddTask';
    if (type == 'Edit') {
        tipmsg = '修改';
        var title = $("#" + id).children("td").eq(2).text();
        var stid = $("#" + id).attr("stid");
        var ajaxurl = 'EditTask';
    }
    layer.prompt({
        formType: 0,
        value: title,
        title: tipmsg + '任务标题',
        closeBtn: 2,
        placeholder: tipmsg + '任务标题',
        shade: 0.1
    }, function (title, tindex) {
        layer.prompt({
            formType: 0,
            value: stid,
            title: tipmsg + '任务ID【打卡时判断用】',
            closeBtn: 2,
            placeholder: tipmsg + '任务ID【打卡时判断用】',
            shade: 0.1
        }, function (stid, tindex) {
            var time = new Date();
            var year = time.getFullYear();
            var month = (time.getMonth() - (-1)) < 10 ? '0' + (time.getMonth() - (-1)) : (time.getMonth() - (-1));
            var timedate = time.getDate();
            var timeenddate = time.getDate() - (-6);
            var startday = (year + '-' + month + '-' + (timedate < 10 ? '0' + timedate : timedate)) + ' 00:00:00';
            var endday = (year + '-' + month + '-' + (timeenddate < 10 ? '0' + timeenddate : timeenddate)) + ' 18:00:00';
            if (type == 'Edit') {
                var startday = $("#" + id).children("td").eq(5).text();
                var endday = $("#" + id).children("td").eq(6).text();
            }
            layer.prompt({
                formType: 0,
                value: startday,
                title: tipmsg + '任务开始时间[示例：2020-09-01 00:00:00]',
                closeBtn: 2,
                placeholder: tipmsg + '任务开始时间[示例：2020-09-01 00:00:00]',
                shade: 0.1
            }, function (addtime, aindex) {
                layer.prompt({
                    formType: 0,
                    value: endday,
                    title: tipmsg + '任务结束时间[示例：2020-09-01 18:00:00]',
                    closeBtn: 2,
                    placeholder: tipmsg + '任务结束时间[示例：2020-09-01 18:00:00]',
                    shade: 0.1,
                    yes: function (index, promptdiv) {
                        var endtime = promptdiv.find(".layui-layer-input").val();
                        if (title == '' || addtime == '' || endtime == '') {
                            ErrorMsg('' + tipmsg + '信息，信息不能为空！');
                            return false;
                        }
                        if (type == 'Edit') {
                            var ajaxdata = {
                                tid: id,
                                title: title,
                                stid: stid,
                                addtime: addtime,
                                endtime: endtime
                            };
                        } else {
                            var ajaxdata = {
                                title: title,
                                stid: stid,
                                addtime: addtime,
                                endtime: endtime
                            };
                        }
                        var loading = layer.load(1);
                        $.ajax({
                            type: "POST",
                            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=" + ajaxurl,
                            data: ajaxdata,
                            dataType: 'json',
                            success: function (data) {
                                layer.close(loading);
                                if (data.code == 1) {
                                    layer.close(tindex);
                                    layer.close(aindex);
                                    layer.close(index);
                                    CorrectMsg(data.msg);
                                    LoadDiv();
                                } else {
                                    ErrorMsg(data.msg);
                                }
                            }
                        });
                    }
                });
            });
        });
    });
}
//删除任务
function DeleteAllTask(id) {
    layer.alert('您确定要删除此任务？【删除后将不可恢复，旗下所有年级对应任务和对应任务打卡记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=DeleteTask",
            data: {
                id: id
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    /*$("#TbodyList").children("tr").each(function() {
                        var name = $(this).children("td").eq(0).text();
                        if (name == id) {
                            $(this).remove();
                        }
                    });*/
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//查看打卡信息概览图【总管理】
function OpenAllCLImg() {
    var tid = $("#ChangeTask").val();
    var climgdiv = layer.open({
        type: 1,
        id: 'CLImgDiv',
        area: ['900px', '500px'],
        title: '概览图查看',
        closeBtn: 2,
        btn: ['关闭'],
        content: '',
        success: function() {
            var loading = layer.msg('加载中……', {
                icon: 20,
                time: 0
            });
            //获取年级
            var classes = new Array();
            $("#selclass").children("option").each(function() {
                if ($(this).attr("value") == 'All') {
                } else if ($(this).attr("value") == '0') {
                } else {
                    classes.push($(this).text());
                }
            });

            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=GetAdminTasksInfo",
                data: {
                    tid: tid
                },
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);/*
                    if (data.indexOf('{"code":-4,"msg":"') != '-1') {
                        var msg = (data.replace('{"code":-4,"msg":"','')).replace('"}','');
                        ErrorMsg(msg);
                        layer.close(climgdiv);
                        return false;
                    } else if (data.indexOf('{"code":-1,"msg":"') != '-1') {
                        var msg = (data.replace('{"code":-1,"msg":"','')).replace('"}','');
                        ErrorMsg(msg);
                        layer.close(climgdiv);
                        return false;
                    }*/
                    
                    if (data.code == '-1' || data.code == '-4') {
                        ErrorMsg(data.msg);
                        layer.close(climgdiv);
                        return false;
                    }
                    //监听div
                    var myChart = echarts.init(document.getElementById('CLImgDiv'));

                    //指定图表的配置项和数据
                    option = {
                        tooltip: {
                            trigger: 'axis'
                        },
                        title: {
                            text: '各年级打卡人数统计图',
                            x: 'left',
                            left: '30px',
                            top: '-5.5px'
                        },
						legend: {
						    data: [GetTaskName()+' 【已完成】',GetTaskName()+' 【未完成】',GetTaskName()+' 【谎打卡】'],
                            left: '230px'
						}/*,
						grid: {
							top: '50px',
							containLabel: true
						}*/,
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: classes,
                            name: '年级名'
                        },
                        yAxis: {
                            type: 'value',
                            name: '总打卡人数'
                        },
                        series: data
                    };

                    //使用刚指定的配置项和数据显示图表。
                    myChart.setOption(option);

                    //设置css
                    $("div[id='CLImgDiv']").children("div").eq(0).css("overflow", "hidden");
                    $("div[id='CLImgDiv']").children("div").eq(0).children("canvas").css("top", "20px");
                }
            });
        }
    });
}
//打开文件上传窗口
function CheckAllIfFinish() {
    layer.alert('<input type="file" id="FFileUpload" onchange="AllFFileUpload()" accept=".txt">',{
        title :'<b>请上传统计文档<b style="color:red;">【txt文档为Unicode编码】</b></b>',
        btn: false,
        closeBtn: 2
    });
}
//点击下载所有班级图片
function DownLoadAll() {
    ErrorMsg('暂不支持！');return false;
    $("#TrTitle").children("td").children("center").children("div").each(function(){
        $(this).trigger("click");
    });
}
//获取文件内容并判断是否完成了大学习
function AllFFileUpload(alldo = '') {
    if (alldo == 'yes') {
        layer.closeAll();
        var i = 0;
        var LoadMainIf = layer.msg('正在判断……',{
            icon: 20,
            time: 0
        });
        
        var tbody = $("tbody[id='TbodyList']").children("tr");
        
        layer.open({
            type: 1,
            title: '未完成成员统计<a href="javascript:DownLoadAll();">【点击下载所有班级图片】</a>',
            btn: false,
            closeBtn: 2,
            area: ['570px','500px'],
            offset: [($("#MainIndex").offset().top - (-15)) + 'px',(window.innerWidth)*0.5 + 'px'],
            id: 'UnFinishedMemListsDiv',
            content: '<table style="text-align:center;width:100%;"><thead><tr><th style="width:10%;">序号</th><th style="width:20%;">姓名</th><th style="width:30%;">班级</th><th style="width:40%;">处理</th></tr></thead><tbody id="UnFinishedMemLists"></tbody></table>',
            shade: false
        });
        
        AllCheckIfFM(tbody,i,'',LoadMainIf,'1');
        
        return false;
    }
    var fileObj = $("#FFileUpload")[0].files[0];
    if (typeof (fileObj) == "undefined" || fileObj.size <= 0) {
        ErrorMsg('请上传文件');
        return false;
    }
    var File = new FileReader();
    //File.readAsText(fileObj, 'utf-8');
    File.readAsText(fileObj, 'Unicode');
            
    File.onload = function() {
        var txt = this.result;
        layer.closeAll();
        var i = 0;
        var LoadMainIf = layer.msg('正在判断……',{
            icon: 20,
            time: 0
        });
        
        var tbody = $("tbody[id='TbodyList']").children("tr");
        
        layer.open({
            type: 1,
            title: '未完成成员统计<a href="javascript:DownLoadAll();">【点击下载所有班级图片】</a>',
            btn: false,
            closeBtn: 2,
            area: ['570px','500px'],
            offset: [($("#MainIndex").offset().top - (-15)) + 'px',(window.innerWidth)*0.5 + 'px'],
            id: 'UnFinishedMemListsDiv',
            content: '<table style="text-align:center;width:100%;"><thead><tr><th style="width:10%;">序号</th><th style="width:20%;">姓名</th><th style="width:30%;">班级</th><th style="width:40%;">处理</th></tr></thead><tbody id="UnFinishedMemLists"></tbody></table>',
            shade: false
        });
        
        AllCheckIfFM(tbody,i,txt,LoadMainIf,1);
    }
}
//if finished
function AllCheckIfFM(tbody,i,txt,index,ifcheck = '',ifonlyog = '') {
    setTimeout(function() {
        var tlen = $("tbody[id='TbodyList']").children("tr").length;
        i++;
        
        if (i >= 9) {
            $("#ClassLists").scrollTop($("#ClassLists").scrollTop() - -(22));
        }
        
        var name = tbody.eq(i - 1).children("td").eq(1).text();
        var classname = tbody.eq(i - 1).attr("classid");
        var gid = tbody.eq(i - 1).attr("gradeid");
                
        var tbdiv = $("tbody[id='UnFinishedMemLists']");
        var tdlen = tbdiv.children("tr").length || 0;
                
        var tddiv = '<tr id="'+tbody.eq(i - 1).attr("id")+'"><td>'+(tdlen - (-1))+'</td><td>'+((name.replace('【未完成（谎打卡）】','')).replace('【未完成（已超时）】','')).replace('【未打卡】','')+'</td><td>'+tbody.eq(i - 1).children("td").eq(3).text()+'</td><td style="color:grey;">处理中……</td></tr>';
        
        if (tbody.eq(i - 1).attr("ifcheck") == 'true') {
            if (name.indexOf('已完成') == -1) {
                tbdiv.append(tddiv);
                $("#UnFinishedMemListsDiv").scrollTop($("#UnFinishedMemListsDiv").prop("scrollHeight"));
                if (tbody.eq(i - 1).attr("resultstatus") == '2') {
                    var msg = '【未完成（谎打卡）】';
                } else if (tbody.eq(i - 1).attr("resultstatus") == '3') {
                    var msg = '【未打卡】';
                }
                $("tbody[id='UnFinishedMemLists']").children("tr[id='"+tbody.eq(i - 1).attr("id")+"']").children("td").eq(3).css("color","red").text(msg);
            } else {
                AdminsetUnFinish(tbody.eq(i - 1).attr("id"),1,'1',gid);
            }
        } else {
            tbody.eq(i - 1).attr("ifcheck","true");
            
            if (SearchInArr(txt,name,classname) == 'true') {
                tbody.eq(i - 1).children("td").eq(1).html(name + '<b style="color:green;">【已完成】</b>');
                $("#UnFinishedMemListsDiv").scrollTop($("#UnFinishedMemListsDiv").prop("scrollHeight"));
                
                AdminsetUnFinish(tbody.eq(i - 1).attr("id"),1,'1',gid);
            } else {
                tbody.eq(i - 1).attr("uncl","true");
                if ((tbody.eq(i - 1).children("td").eq(4).attr("time")) >= $("#TaskEndTime").val()) {
                    var unfres = '（已超时）';
                } else {
                    var unfres = '（谎打卡）';
                }
                
                tbody.eq(i - 1).children("td").eq(1).html(name + '<b style="color:red;">【未完成'+unfres+'】</b>');
                $("#UnFinishedMemListsDiv").scrollTop($("#UnFinishedMemListsDiv").prop("scrollHeight"));
                
                AdminsetUnFinish(tbody.eq(i - 1).attr("id"),2,'1',gid);
                
                if (tbdiv.text() == '暂无数据') {
                    tbdiv.html(tddiv);
                } else {
                    tbdiv.append(tddiv);
                }
            }
        }
        
        var tid = $("#ChangeTask").val();
        
        if (i >= tlen) {
            CorrectMsg('检查完毕！'+(ifcheck == '' ? '' : '上传文件结算后，页面刷新获取未打卡人员，刷新完毕后重新点击【检测是否完成学习】即可'));
            if (ifcheck == '1') {
                var resin = $("#UnFinishedMemListsDiv").parent("div[type='page']").attr("times");
                layer.close(resin);
                AdminSetUnClockIn(gid,tid);
            } else {
                layer.close(index);
                CorrectMsg('检查完毕！');
                if ($("input[name='getclassresult']").val() == 'true') {
                    $("#UnFinishedMemListsDiv").append('<div id="DDButton"><br><hr><br><center><button type="button" class="el-button w-100 el-button--primary Bg-Blues" onclick="DivideIntoClasses();" style="width:50%;padding:10px;cursor:pointer;"><span>结果分班排列</span></button></center></div>');
                    $("#ClickCheckBtn").attr('onclick','layer.msg(\'正在生成结果……\',{icon: 20,time: 2000},function(){FFileUpload(\'yes\',\'1\');});');
                } else {
                    $("#UnFinishedMemListsDiv").append('<div id="DDButton"><br><hr><br><center><button type="button" onclick="AdminSetUnClockIn(\''+gid+'\',\''+tid+'\');" class="el-button w-100 el-button--primary Bg-Blues" style="width:50%;padding:10px;cursor:pointer;"><span>点击记录未打卡成员</span></button></center></div>');
                    //$("#UnFinishedMemListsDiv").append('<div id="DDButton"><br><hr><br><center><button type="button" class="el-button w-100 el-button--primary Bg-Blues" style="width:50%;padding:10px;cursor:pointer;"><span>点击每条对应班级查看对应内容（在此内生成图表 ）</span></button></center></div>');
                    $("#ClickCheckBtn").attr('onclick','layer.msg(\'正在生成结果……\',{icon: 20,time: 2000},function(){AllFFileUpload(\'yes\');})');
                }
                return false;
            }
        }
        AllCheckIfFM(tbody,i,txt);
    }, 1);
}
//总管理设置未打卡人员
function AdminSetUnClockIn(gid,tid) {
    var loading = layer.msg('未打卡成员记录中……',{
        icon: 20,
        time: 0
    });
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=AdminSetUnClockIn",
        data: {
            tid: tid,
            gid: gid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                LoadMemDiv('GradeAndTask');
            } else {
                ErrorMsg(data.msg);
            };
        }
    });
}
//打开添加公告界面
function OpenAddAllNotice() {
    var openindex = layer.open({
        type: 1,
        closeBtn: 2,
        area: ['500px','490px'],
        title: '添加公告',
        resize: false,
        btn: ['添加','关闭'],
        id: 'AddNoticeLO',
        content: '<div class="CIIndex"><lable>公告标题</lable><input type="text" name="title" placeholder="请输入公告标题" /></div><div class="CIIndex"><lable>公告内容</lable><textarea type="text" name="msg" placeholder="请输入公告内容" ></textarea></div><div class="CIIndex"><lable>公告查看对象</lable><select id="AddNotice" name="looktype"><option value="0">请选择查看对象</option><option value="1">普通成员</option><option value="2">各班团支书</option><option value="3">各年级各班成员</option><option value="4">各年级管理员</option><option value="5">各年级管理员&团支书</option><option value="6">各年级所有成员</option></select></div>',
        success: function() {
            $("#AddNoticeLO").css("padding","20px").css("height","350px");
        },
        yes: function() {
            var title = $("input[name='title']").val();
            var msg = $("textarea[name='msg']").val();
            var looktype = $("select[name='looktype']").val();
            if (title == '') {
                ErrorMsg('公告标题不能为空');
                return false;
            }
            if (msg == '') {
                ErrorMsg('公告内容不能为空');
                return false;
            }
            if (looktype == '' || looktype == '0') {
                ErrorMsg('请选择公告查看对象');
                return false;
            }
            var loading = layer.msg('添加中',{
                icon: 20,
                time: 0
            });
            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=AddNotice",
                data: {
                    title: title,
                    msg: msg.replace(/\n/g,'<br>'),
                    looktype: looktype
                },
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        CorrectMsg(data.msg);
                        setTimeout(function() {
                            layer.close(openindex);
                            LoadDiv();
                        }, 2000);
                    } else {
                        ErrorMsg(data.msg);
                    };
                }
            });
        }
    });
}
//修改公告内容
function OpenEditAllNotice(id) {
    var tr = $("tr[id='"+id+"']");
    var type = tr.children("td").eq(5).attr("looktype");
    
    var openindex = layer.open({
        type: 1,
        closeBtn: 2,
        area: ['500px','490px'],
        title: '修改公告',
        resize: false,
        btn: ['修改','关闭'],
        id: 'AddNoticeLO',
        content: '<div class="CIIndex"><lable>公告标题</lable><input type="text" name="title" placeholder="请输入公告标题" value="'+tr.children("td").eq(2).text()+'" /></div><div class="CIIndex"><lable>公告内容</lable><textarea type="text" name="msg" placeholder="请输入公告内容" >'+tr.children("td").eq(3).children("msg").html()+'</textarea></div><div class="CIIndex"><lable>公告查看对象</lable><select id="AddNotice" name="looktype"><option value="0">请选择查看对象</option><option value="1"'+(type == 1 ? ' selected="selected"' : '')+'>普通成员</option><option value="2"'+(type == 2 ? ' selected="selected"' : '')+'>各班团支书</option><option value="3"'+(type == 3 ? ' selected="selected"' : '')+'>各年级各班成员</option><option value="4"'+(type == 4 ? ' selected="selected"' : '')+'>各年级管理员</option><option value="5"'+(type == 5 ? ' selected="selected"' : '')+'>各年级管理员&团支书</option><option value="6"'+(type == 6 ? ' selected="selected"' : '')+'>各年级所有成员</option></select></div>',
        success: function() {
            $("#AddNoticeLO").css("padding","20px").css("height","350px");
        },
        yes: function() {
            var title = $("input[name='title']").val();
            var msg = $("textarea[name='msg']").val();
            var looktype = $("select[name='looktype']").val();
            if (title == '') {
                ErrorMsg('公告标题不能为空');
                return false;
            }
            if (msg == '') {
                ErrorMsg('公告内容不能为空');
                return false;
            }
            if (looktype == '' || looktype == '0') {
                ErrorMsg('请选择公告查看对象');
                return false;
            }
            var loading = layer.msg('修改中',{
                icon: 20,
                time: 0
            });
            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=EditNotice",
                data: {
                    id: id,
                    title: title,
                    msg: msg.replace(/\n/g,'<br>'),
                    looktype: looktype
                },
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        CorrectMsg(data.msg);
                        setTimeout(function() {
                            layer.close(openindex);
                            LoadDiv();
                        }, 2000);
                    } else {
                        ErrorMsg(data.msg);
                    };
                }
            });
        }
    });
}
//删除公告
function DeleteAllNotice(id) {
    layer.alert('您确定要删除此公告？【删除后将不可恢复！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=DeleteNotice",
            data: {
                id: id
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//查看打卡信息概览图【总管理查看年级】
function OpenClassCLImg(gid) {
    var tid = $("#ChangeTask").val();
    var climgdiv = layer.open({
        type: 1,
        id: 'CLImgDiv',
        area: ['900px', '500px'],
        title: '概览图查看',
        closeBtn: 2,
        btn: ['关闭'],
        content: '',
        success: function() {
            var loading = layer.msg('加载中……', {
                icon: 20,
                time: 0
            });
            //获取班级
            var classes = new Array();
            $("#selclass").children("option").each(function() {
                if ($(this).attr("value") == 'All') {
                } else if ($(this).attr("value") == '0') {
                } else {
                    classes.push($(this).attr("value") + '班');
                }
            });

            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=GetAdminClassesTasksInfo",
                data: {
                    gid: gid,
                    tid: tid
                },
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);/*
                    if (data.indexOf('{"code":-4,"msg":"') != '-1') {
                        var msg = (data.replace('{"code":-4,"msg":"','')).replace('"}','');
                        ErrorMsg(msg);
                        layer.close(climgdiv);
                        return false;
                    } else if (data.indexOf('{"code":-1,"msg":"') != '-1') {
                        var msg = (data.replace('{"code":-1,"msg":"','')).replace('"}','');
                        ErrorMsg(msg);
                        layer.close(climgdiv);
                        return false;
                    }*/
                    
                    if (data.code == '-1' || data.code == '-4') {
                        ErrorMsg(data.msg);
                        layer.close(climgdiv);
                        return false;
                    }
                    //监听div
                    var myChart = echarts.init(document.getElementById('CLImgDiv'));

                    //指定图表的配置项和数据
                    option = {
                        tooltip: {
                            trigger: 'axis'
                        },
                        title: {
                            text: '各班打卡人数线形统计图',
                            x: 'left',
                            left: '30px',
                            top: '-5.5px'
                        },
						legend: {
						    data: [GetTaskName()+' 【已完成】',GetTaskName()+' 【未完成】',GetTaskName()+' 【谎打卡】'],
                            left: '260px'
						}/*,
						grid: {
							top: '50px',
							containLabel: true
						}*/,
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: classes,
                            name: '班级名'
                        },
                        yAxis: {
                            type: 'value',
                            name: '总打卡人数'
                        },
                        series: data
                    };

                    //使用刚指定的配置项和数据显示图表。
                    myChart.setOption(option);

                    //设置css
                    $("div[id='CLImgDiv']").children("div").eq(0).css("overflow", "hidden");
                    $("div[id='CLImgDiv']").children("div").eq(0).children("canvas").css("top", "20px");
                }
            });
        }
    });
}
//修改年级信息
function EditGrade(id) {
    var tr = $("tr[id=" + id + "]");
    layer.prompt({
        formType: 0,
        value: tr.children("td").eq(2).text(),
        title: '修改年级名（显示用）',
        closeBtn: 2,
        placeholder: '修改年级名（显示用）',
        shade: 0.1,
        yes: function(index, promptdiv) {
            var gradename = promptdiv.find(".layui-layer-input").val();
            layer.prompt({
                formType: 0,
                value: tr.children("td").eq(3).text(),
                title: '修改判断名',
                closeBtn: 2,
                placeholder: '修改判断名',
                shade: 0.1,
                yes: function(index, promptdiv) {
                    var name = promptdiv.find(".layui-layer-input").val();
                    layer.prompt({
                        formType: 0,
                        value: tr.attr("gid"),
                        title: '修改年级号【为该年级唯一值，好查询，不能包含字符】',
                        closeBtn: 2,
                        placeholder: '修改年级号【为该年级唯一值，好查询，不能包含字符】',
                        shade: 0.1,
                        yes: function(index, promptdiv) {
                            var gid = promptdiv.find(".layui-layer-input").val();
                            layer.prompt({
                                formType: 0,
                                value: tr.attr("oid"),
                                title: '修改组织ID【大学习官网后台对应团支部ID，此信息用于打卡时的判断】',
                                closeBtn: 2,
                                placeholder: '修改组织ID【大学习官网后台对应团支部ID，此信息用于打卡时的判断】',
                                shade: 0.1,
                                yes: function(index, promptdiv) {
                                    var oid = promptdiv.find(".layui-layer-input").val();
                                    if (gradename == '' || name == '' || gid == '' || oid == '') {
                                        ErrorMsg('输入信息不能为空！');
                                        return false;
                                    }
                                    if (isNaN(oid)) {
                                        ErrorMsg('组织ID只能为数字！');
                                        return false;
                                    }
                                    var loading = layer.load(1);
                                    $.ajax({
                                        type: "POST",
                                        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=EditGrade",
                                        data: {
                                            id: id,
                                            gradename: gradename,
                                            name: name,
                                            gid: gid,
                                            oid: oid
                                        },
                                        dataType: 'json',
                                        success: function(data) {
                                            layer.close(loading);
                                            if (data.code == 1) {
                                                CorrectMsg(data.msg);
                                                LoadDiv();
                                            } else {
                                                ErrorMsg(data.msg);
                                            }
                                        }
                                    });
                                    layer.close(index);
                                }
                            });
                            layer.close(index);
                        }
                    });
                }
            });
        }
    });
}
//全选/反选
function DoSelectAllLists(type = '',method = '') {
    var ced = GetCheckedList();
    var allc = GetAllCheckedList();
    if ($("#CheckBtn").text() == '全选') {
        if (type == 'HaveNoDisplay') {
            if (method == 'class') {
                var fordiv = $("tr[classid='"+$("select[id='SelectClassName']").val()+"']");
            }
            fordiv.each(function() {
                if (!$(this).children("td").eq(0).children("input[name='SelectInfoList']").is(":checked")) {
                    $(this).children("td").eq(0).children("input[name='SelectInfoList']").trigger("click");
                }
            });
        } else {
            $("input[name='SelectInfoList']").each(function() {
                if (!$(this).is(":checked")) {
                    $(this).trigger("click");
                }
            });
        }
    } else {
        if (type == 'HaveNoDisplay') {
            if (method == 'class') {
                var fordiv = $("tr[classid='"+$("select[id='SelectClassName']").val()+"']");
            }
            fordiv.each(function() {
                $(this).children("td").eq(0).children("input[name='SelectInfoList']").trigger("click");
            });
        } else {
            $("input[name='SelectInfoList']").trigger("click");
        }
    }
    if ($("#CheckBtn").text() == '反选') {
        $("#CheckBtn").text('全选');
    } else {
        $("#CheckBtn").text('反选');
    }
    if (ced.length >= allc.length) {
        $("#CheckBtn").text('全选');
    } else {
        $("#CheckBtn").text('反选');
    }
}
//监听勾选复选框
$("input[name='SelectInfoList']").on("click",function() {
    var ced = GetCheckedList();
    var allc = GetAllCheckedList();
    if (ced.length >= allc.length || (ced.length < allc.length && $("#CheckBtn").text() == '反选' && ced.length != '0') || ced.length != '0') {
        $("#CheckBtn").text('反选');
    } else if (ced.length == '0') {
        $("#CheckBtn").text('全选');
    } else {
        $("#CheckBtn").text('全选');
    }
});
//批量操作执行
function AjaxDoType() {
    var select = $("#AjaxDoType").val();
    var ced = GetCheckedList();
    if (ced.length == '0') {
        ErrorMsg('请勾选数据！');
        setTimeout(function() {
            var select = $("#AjaxDoType").val("0");
        }, 200);
        return false;
    }
    if (select == '0' || select == '') {
        ErrorMsg('请选择批量操作方式！');
        setTimeout(function() {
            var select = $("#AjaxDoType").val("0");
        }, 200);
        return false;
    }
    if (select == 'ChangeClass') {
        ChangeClass('','Array');
    } else if (select == 'SetFLid') {
        SetSomeFLid();
    } else if (select == 'SetPassword') {
        SetSomePassword();
    } else if (select == 'DeleteUser') {
        DeleteSomeUser();
    } else if (select == 'Delete') {
        DeleteSomeClasses();
    } else if (select == 'DeleteTask') {
        DeleteSomeTasks();
    } else if (select == 'SetNoticeLT') {
        SetSomeNoticeLT();
    } else if (select == 'DeleteNotice') {
        DeleteSomeNotices();
    } else if (select == 'SetAdminPassword') {
        SetSomeAdminPassword();
    } else if (select == 'DeleteGrade') {
        DeleteSomeGrade();
    } else if (select == 'DeleteSomeAllTask') {
        DeleteSomeAllTask();
    } else if (select == 'SetAllNoticeLT') {
        SetSomeAllNoticeLT();
    } else if (select == 'DeleteAllNotice') {
        DeleteSomeAllNotices();
    } else if (select == 'AdminChangeClass') {
        AdminChangeClass('','Array');
    } else if (select == 'AdminSetFLid') {
        SetSomeAllFLid();
    } else if (select == 'AdminSetPassword') {
        SetSomeAllPassword();
    } else if (select == 'AdminDeleteUser') {
        DeleteSomeAllUser();
    } else if (select == 'AdminDelete') {
        DeleteSomeAllClasses();
    } else  {
        ErrorMsg('无此操作！');
    }
    setTimeout(function() {
        var select = $("#AjaxDoType").val("0");
    }, 200);
}
//成员批量转班
function DoChangeClasses() {
    var classid = $("#ccselclass").val();
    if (classid == '' || classid == '0') {
        ErrorMsg('请选择班级！');
        return false;
    }
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=IndexInfo&Type=ChangeClass",
        data: {
            mid: GetCheckedList(),
            classid: classid,
            dotype: 'Array'
            
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                LoadMemDiv('AndTask');
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
//批量重置密码
function SetSomePassword() {
    layer.alert('您确定要重置已选成员密码？【默认密码为123456】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=SetPassword",
            data: {
                id: GetCheckedList(),
                dotype: 'Array'
            },
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
    });
}
//批量删除成员
function DeleteSomeUser() {
    layer.alert('您确定要删除所选成员？【删除后将不可恢复，期间的打卡记录和公告查看记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=DelMember",
            data: {
                id: GetCheckedList(),
                dotype: 'Array'
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadMemDiv('AndTask');
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//批量删除班级
function DeleteSomeClasses() {
    layer.alert('您确定要删除所选班级？【删除后将不可恢复，班级内所有成员以及旗下的打卡记录和公告查看记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=DelClass",
            data: {
                id: GetCheckedList(),
                dotype: 'Array'
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//批量删除班级
function DeleteSomeAllClasses() {
    var gid = $("#AjaxDoType").attr("gid");
    layer.alert('您确定要删除所选班级？【删除后将不可恢复，班级内所有成员以及旗下的打卡记录和公告查看记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=DelClass",
            data: {
                gid: gid,
                id: GetCheckedList(),
                dotype: 'Array'
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//批量删除任务
function DeleteSomeTasks() {
    layer.alert('您确定要删除所选任务？【删除后将不可恢复，旗下的打卡记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=DeleteTask",
            data: {
                id: GetCheckedList(),
                dotype: 'Array'
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//批量设置公告查看对象
function SetSomeNoticeLT() {
    var openindex = layer.open({
        type: 1,
        closeBtn: 2,
        area: ['380px','170px'],
        title: '批量设置公告查看对象',
        resize: false,
        btn: ['修改','关闭'],
        id: 'EditNoticeLT',
        content: '<div class="CIIndex"><lable>公告查看对象</lable><select id="AddNotice" name="looktype"><option value="0">请选择查看对象</option><option value="1">普通成员</option><option value="2">各班团支书</option><option value="3">所有下级</option></select></div>',
        success: function() {
            $("#EditNoticeLT").css("padding","20px").css("height","30px").css("overflow","hidden");
        },
        yes: function() {
            var looktype = $("select[name='looktype']").val();
            if (looktype == '' || looktype == '0') {
                ErrorMsg('请选择公告查看对象');
                return false;
            }
            var loading = layer.msg('设置中',{
                icon: 20,
                time: 0
            });
            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=IndexInfo&Type=SetSomeNoticeLT",
                data: {
                    id: GetCheckedList(),
                    looktype: looktype
                },
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        CorrectMsg(data.msg);
                        setTimeout(function() {
                            layer.close(openindex);
                            LoadDiv();
                        }, 2000);
                    } else {
                        ErrorMsg(data.msg);
                    };
                }
            });
        }
    });
}
//批量删除公告
function DeleteSomeNotices() {
    layer.alert('您确定要删除所选公告？【删除后将不可恢复！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=IndexInfo&Type=DeleteNotice",
            data: {
                id: GetCheckedList(),
                dotype: 'Array'
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//批量重置密码
function SetSomeAdminPassword() {
    layer.alert('您确定要重置所选年级管理密码？【默认密码为123456789】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=SetAdminPassword",
            data: {
                id: GetCheckedList(),
                dotype: 'Array'
            },
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
    });
}
//批量删除年级
function DeleteSomeGrade() {
    layer.alert('您确定要删除所选年级？【删除后将不可恢复！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=DelGrade",
            data: {
                id: GetCheckedList(),
                dotype: 'Array'
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//批量删除任务
function DeleteSomeAllTask() {
    layer.alert('您确定要删除所选任务？【删除后将不可恢复，旗下所有年级对应任务和对应任务打卡记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=DeleteTask",
            data: {
                id: GetCheckedList(),
                dotype: 'Array'
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//批量设置公告查看对象
function SetSomeAllNoticeLT() {
    var openindex = layer.open({
        type: 1,
        closeBtn: 2,
        area: ['380px','170px'],
        title: '批量设置公告查看对象',
        resize: false,
        btn: ['修改','关闭'],
        id: 'EditNoticeLT',
        content: '<div class="CIIndex"><lable>公告查看对象</lable><select id="AddNotice" name="looktype"><option value="0">请选择查看对象</option><option value="1">普通成员</option><option value="2">各班团支书</option><option value="3">各年级各班成员</option><option value="4">各年级管理员</option><option value="5">各年级管理员&团支书</option><option value="6">各年级所有成员</option></select></div>',
        success: function() {
            $("#EditNoticeLT").css("padding","20px").css("height","30px").css("overflow","hidden");
        },
        yes: function() {
            var looktype = $("select[name='looktype']").val();
            if (looktype == '' || looktype == '0') {
                ErrorMsg('请选择公告查看对象');
                return false;
            }
            var loading = layer.msg('设置中',{
                icon: 20,
                time: 0
            });
            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=SetSomeNoticeLT",
                data: {
                    id: GetCheckedList(),
                    looktype: looktype
                },
                dataType: 'json',
                success: function (data) {
                    if (data.code == 1) {
                        CorrectMsg(data.msg);
                        setTimeout(function() {
                            layer.close(openindex);
                            LoadDiv();
                        }, 2000);
                    } else {
                        ErrorMsg(data.msg);
                    };
                }
            });
        }
    });
}
//批量删除公告
function DeleteSomeAllNotices() {
    layer.alert('您确定要删除所选公告？【删除后将不可恢复！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=DeleteNotice",
            data: {
                id: GetCheckedList(),
                dotype: 'Array'
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//选择查看班级
function SelectClassName() {
    var classid = $("select[id='SelectClassName']").val();
    if (classid == 'All') {
        var ifall = $("#TbodyList").children("tr")
    } else {
        var ifall = $("#TbodyList").children("tr[classid='"+classid+"']")
    }
    $("#TbodyList").children("tr").hide();
    $("input[name='SelectInfoList']").prop("outerHTML",$("input[name='SelectInfoList']").prop("outerHTML"))
    ifall.show(function() {
        if ($("select[id='SelectClassName']").val() != 'All') {
            SearchClass('2','Admin');
        }
    });
}
//批量设置团支部书记
function SetSomeFLid() {
    var classid = $("#selclass").val();
    if (classid == '' || classid == '0') {
        ErrorMsg('请选择班级！');
        return false;
    }
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=IndexInfo&Type=SetSomeFLid",
        data: {
            id: GetCheckedList(),
            dotype: 'Array',
            classid: classid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                LoadDiv('?CID=' + classid);
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
//设置团支部书记
function AdminSetLid(id,type = '') {
    if (type == 1) {
        var classid = id;
        var id = $("#selmember").val();
        var username = $("option[value='"+id+"']").text();
        if (id == '0') {
            return false;
        }
    } else {
        var classid = $("#SelectClassName").val();
        if (classid == '' || classid == '0') {
            ErrorMsg('请选择班级！');
            return false;
        }
    }
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=SetLid",
        data: {
            id: id,
            gid: $("input[name='gradeid']").val(),
            classid: classid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                $("button[name='LID']").attr("class","ClickBT Bg-Reds");
                $("button[name='LID']").text("否");
                $("button[id='lid-"+data.lid+"']").attr("class","ClickBT Bg-Greens");
                $("button[id='lid-"+data.lid+"']").text("是");
                if (type == 1) {
                    var div = '<b style="color:red;">'+username+'</b>';
                    $("td[id='"+classid+"-lid']").html(div);
                }
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
//设置代理团支部书记
function AdminSetFLid(id) {
    var classid = $("#SelectClassName").val();
    if (classid == '' || classid == '0') {
        ErrorMsg('请选择班级！');
        return false;
    }
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=SetFLid",
        data: {
            id: id,
            gid: $("input[name='gradeid']").val(),
            classid: classid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                if (data.type == 2) {
                    $("button[id='flid-"+data.lid+"']").attr("class","ClickBT Bg-Greens");
                    $("button[id='flid-"+data.lid+"']").text("是");
                } else {
                    $("button[id='flid-"+data.lid+"']").attr("class","ClickBT Bg-Reds");
                    $("button[id='flid-"+data.lid+"']").text("否");
                }
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
//成员转班
function AdminChangeClass(id,type = '') {
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "GetClassesToChangs.php",
        data: {
            mid: id,
            gid: $("input[name='gradeid']").val(),
            type: type
        },
        dataType: 'html',
        success: function (data) {
            layer.close(loading);
            layer.alert(data,{
                title: '选择转向班级',
                closeBtn: 2,
                btn: false
            });
        }
    });
}
//成员转班
function AdminDoChangeClass(id) {
    var classid = $("#ccselclass").val();
    if (classid == '' || classid == '0') {
        ErrorMsg('请选择班级！');
        return false;
    }
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=ChangeClass",
        data: {
            mid: id,
            gid: $("input[name='gradeid']").val(),
            classid: classid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                LoadDiv('?CID=' + classid + '&GID=' + $("input[name='gradeid']").val());
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
//批量成员转班
function AdminDoChangeClasses() {
    var classid = $("#ccselclass").val();
    if (classid == '' || classid == '0') {
        ErrorMsg('请选择班级！');
        return false;
    }
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=ChangeClass",
        data: {
            mid: GetCheckedList(),
            dotype: 'Array',
            gid: $("input[name='gradeid']").val(),
            classid: classid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                LoadDiv('?CID=' + classid + '&GID=' + $("input[name='gradeid']").val());
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
//重置密码
function AdminSetPassword(id) {
    layer.alert('您确定要重置此成员密码？【默认密码为123456】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=SetPassword",
            data: {
                id: id,
                gid: $("input[name='gradeid']").val(),
            },
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
    });
}
//选择添加成员方式
function OpenAdminAddMember() {
    var gid = $("#selclass").val();
    var classid = $("#SelectClassName").val();
    var namereg = /^[\u4E00-\u9FA5]+$/;
    
    var confirmix = layer.confirm('请选择添加方式',{
        icon: 0,
        closeBtn: 2,
        title: '方式选择',
        btn: ['添加单个','批量添加'],
        shade: 0.1
    },function() {
        layer.close(confirmix);
        layer.prompt({
            formType: 0,
            value: '',
            title: '请输入姓名（密码默认为123456）',
            closeBtn: 2,
            placeholder: '请输入姓名（密码默认为123456）',
            shade: 0.1,
            yes: function(index,promptdiv) {
                var value = promptdiv.find(".layui-layer-input").val();
                if (value == '') {
                    ErrorMsg('输入信息不能为空！');
                    return false;
                }
                if (!namereg.test(value)) {
                    ErrorMsg('输入信息只能为中文！');
                    return false;
                }
                var loading = layer.load(1);
                $.ajax({
                    type: "POST",
                    url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=AddMember",
                    data: {
                        username: value,
                        gid: gid,
                        classid: classid
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(loading);
                        if (data.code == 1) {
                            CorrectMsg(data.msg);
                            LoadDiv('?GID='+gid+'&CID='+classid);
                        } else {
                            ErrorMsg(data.msg);
                        }
                    }
                });
                layer.close(index);
            }
        });
    },function() {
        layer.close(confirmix);
        layer.prompt({
            formType: 2,
            value: '',
            title: '请输入批量姓名（密码默认为123456）',
            closeBtn: 2,
            placeholder: '请输入批量姓名（密码默认为123456）【以,隔开】',
            shade: 0.1,
            yes: function(index,promptdiv) {
                var val = (promptdiv.find(".layui-layer-input").val()).split(',');
                if (val == '') {
                    ErrorMsg('输入信息不能为空！');
                    return false;
                }
                if (val.indexOf(',') != '-1') {
                    ErrorMsg('输入信息格式不对！');
                    return false;
                }
                var loading = layer.load(1);
                
                var c = 0;
                var loadloading = layer.msg('处理中<b>【<b id="AM" style="color:blue;">0</b>/'+val.length+'】</b>',{
                    icon: 20,
                    time: 0
                });
                                
                for (var i = 0;i < val.length;i++) {
                    var value = val[i];
                    $.ajax({
                        type: "POST",
                        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=AddMember",
                        data: {
                            username: value,
                            gid: gid,
                            classid: classid
                        },
                        dataType: 'json',
                        success: function (data) {
                            layer.close(loading);
                            if (data.code == 1) {
                                c++;
                                $("b[id='AM']").text(c);
                                if (c >= val.length) {
                                    layer.close(loadloading);
                                    LoadDiv('?GID='+gid+'&CID='+classid);
                                }
                            } else {
                                ErrorMsg(data.msg);
                            }
                        }
                    });
                }
                layer.close(index);
            }
        });
    });
}
//删除成员
function AdminDeleteUser(id) {
    layer.alert('您确定要删除此成员？【删除后将不可恢复，期间的打卡记录和公告查看记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=DelMember",
            data: {
                id: id,
                gid: $("#selclass").val()
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadDiv('?GID='+$("#selclass").val()+'&CID='+$("#SelectClassName").val());
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//总管理重置所有用户密码
function AdminSetAllPassword() {
    var classid = $("#SelectClassName").val();
    layer.alert('您确定要重置所有成员密码？【默认密码为123456】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=SetAllPassword",
            data: {
                classid: classid,
                gid: $("#selclass").val()
            },
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
    });
}
//批量设置团支部书记
function SetSomeAllFLid() {
    var classid = $("#SelectClassName").val();
    if (classid == '' || classid == '0') {
        ErrorMsg('请选择班级！');
        return false;
    }
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=SetSomeFLid",
        data: {
            id: GetCheckedList(),
            dotype: 'Array',
            classid: classid,
            gid: $("#selclass").val()
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                CorrectMsg(data.msg);
                LoadDiv('?GID='+$("#selclass").val()+'&CID='+$("#SelectClassName").val());
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
//批量重置密码
function SetSomeAllPassword() {
    layer.alert('您确定要重置已选成员密码？【默认密码为123456】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=SetPassword",
            data: {
                id: GetCheckedList(),
                dotype: 'Array',
                gid: $("#selclass").val()
            },
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
    });
}
//批量删除成员
function DeleteSomeAllUser() {
    layer.alert('您确定要删除所选成员？【删除后将不可恢复，期间的打卡记录和公告查看记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=DelMember",
            data: {
                id: GetCheckedList(),
                dotype: 'Array',
                gid: $("#selclass").val()
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    LoadDiv('?GID='+$("#selclass").val()+'&CID='+$("#SelectClassName").val());
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//监听点击列表勾选或者取消勾选
$("#TbodyList").children("tr").children("td").on("click",function(e){
    if ($(e.target).prop("tagName") == 'INPUT' || $(e.target).prop("tagName") == 'A' || $(e.target).prop("tagName") == 'BUTTON' || $(e.target).attr("class") == 'CanClick') {} else {
        $(this).parent("tr").children("td").eq(0).children("input[name='SelectInfoList']").trigger("click");
    }
});

//查看任务信息概览图
function AdminLookTasksInfo() {
    var climgdiv = layer.open({
        type: 1,
        id: 'CLImgDiv',
        area: ['900px', '500px'],
        title: '概览图查看',
        closeBtn: 2,
        btn: ['关闭'],
        content: '',
        success: function() {
            var loading = layer.msg('加载中……', {
                icon: 20,
                time: 0
            });
            //获取任务
            var tasks = new Array();
            $("#TbodyList").children("tr").each(function() {
                tasks.push($(this).children("td").eq(2).text());
            });
            var tasks = tasks.reverse();

            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=AdminLookTasksInfo",
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);
                    
                    if (data.code == '-1' || data.code == '-4') {
                        ErrorMsg(data.msg);
                        layer.close(climgdiv);
                        return false;
                    }
                    //监听div
                    var myChart = echarts.init(document.getElementById('CLImgDiv'));

                    //指定图表的配置项和数据
                    option = {
                        tooltip: {
                            trigger: 'axis'
                        },
                        title: {
                            text: '任务完成情况线形统计图',
                            x: 'left',
                            left: '30px',
                            top: '-5.5px'
                        },
						legend: {
							data: ['已打卡人数','谎打卡人数','未打卡人数']
						}/*,
						grid: {
							top: '50px',
							containLabel: true
						}*/,
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: tasks,
                            name: '任务名'
                        },
                        yAxis: {
                            type: 'value',
                            name: '总打卡人数'
                        },
                        series: data
                    };

                    //使用刚指定的配置项和数据显示图表。
                    myChart.setOption(option);

                    //设置css
                    $("div[id='CLImgDiv']").children("div").eq(0).css("overflow", "hidden");
                    $("div[id='CLImgDiv']").children("div").eq(0).children("canvas").css("top", "20px");
                }
            });
        }
    });
}

//查看任务信息概览图
function LookTasksInfo() {
    var climgdiv = layer.open({
        type: 1,
        id: 'CLImgDiv',
        area: ['900px', '500px'],
        title: '概览图查看',
        closeBtn: 2,
        btn: ['关闭'],
        content: '',
        success: function() {
            var loading = layer.msg('加载中……', {
                icon: 20,
                time: 0
            });
            //获取任务
            var tasks = new Array();
            $("#TbodyList").children("tr").each(function() {
                tasks.push($(this).children("td").eq(2).text());
            });
            var tasks = tasks.reverse();

            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=LookTasksInfo",
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);
                    
                    if (data.code == '-1' || data.code == '-4') {
                        ErrorMsg(data.msg);
                        layer.close(climgdiv);
                        return false;
                    }
                    //监听div
                    var myChart = echarts.init(document.getElementById('CLImgDiv'));

                    //指定图表的配置项和数据
                    option = {
                        tooltip: {
                            trigger: 'axis'
                        },
                        title: {
                            text: '任务完成情况线形统计图',
                            x: 'left',
                            left: '30px',
                            top: '-5.5px'
                        },
						legend: {
							data: ['已打卡人数','谎打卡人数','未打卡人数']
						}/*,
						grid: {
							top: '50px',
							containLabel: true
						}*/,
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: tasks,
                            name: '任务名'
                        },
                        yAxis: {
                            type: 'value',
                            name: '总打卡人数'
                        },
                        series: data
                    };

                    //使用刚指定的配置项和数据显示图表。
                    myChart.setOption(option);

                    //设置css
                    $("div[id='CLImgDiv']").children("div").eq(0).css("overflow", "hidden");
                    $("div[id='CLImgDiv']").children("div").eq(0).children("canvas").css("top", "20px");
                }
            });
        }
    });
}
//查看公告信息概览图
function AdminLookNoticesInfo() {
    var climgdiv = layer.open({
        type: 1,
        id: 'CLImgDiv',
        area: ['900px', '500px'],
        title: '概览图查看',
        closeBtn: 2,
        btn: ['关闭'],
        content: '',
        success: function() {
            var loading = layer.msg('加载中……', {
                icon: 20,
                time: 0
            });
            //获取任务
            var notices = new Array();
            $("#TbodyList").children("tr").each(function() {
                notices.push($(this).children("td").eq(2).text());
            });
            var notices = notices.reverse();

            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=AdminLookNoticesInfo",
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);
                    
                    if (data.code == '-1' || data.code == '-4') {
                        ErrorMsg(data.msg);
                        layer.close(climgdiv);
                        return false;
                    }
                    //监听div
                    var myChart = echarts.init(document.getElementById('CLImgDiv'));

                    //指定图表的配置项和数据
                    option = {
                        tooltip: {
                            trigger: 'axis'
                        },
                        title: {
                            text: '公告查看情况线形统计图',
                            x: 'left',
                            left: '30px',
                            top: '-5.5px'
                        },
						legend: {
							data: ['已查看人数','未查看人数']
						}/*,
						grid: {
							top: '50px',
							containLabel: true
						}*/,
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: notices,
                            name: '任务名'
                        },
                        yAxis: {
                            type: 'value',
                            name: '总查看人数'
                        },
                        series: data
                    };

                    //使用刚指定的配置项和数据显示图表。
                    myChart.setOption(option);

                    //设置css
                    $("div[id='CLImgDiv']").children("div").eq(0).css("overflow", "hidden");
                    $("div[id='CLImgDiv']").children("div").eq(0).children("canvas").css("top", "20px");
                }
            });
        }
    });
}
//查看公告信息概览图
function LookNoticesInfo() {
    var climgdiv = layer.open({
        type: 1,
        id: 'CLImgDiv',
        area: ['900px', '500px'],
        title: '概览图查看',
        closeBtn: 2,
        btn: ['关闭'],
        content: '',
        success: function() {
            var loading = layer.msg('加载中……', {
                icon: 20,
                time: 0
            });
            //获取任务
            var notices = new Array();
            $("#TbodyList").children("tr").each(function() {
                notices.push($(this).children("td").eq(2).text());
            });
            var notices = notices.reverse();

            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=LookNoticesInfo",
                dataType: 'json',
                success: function (data) {
                    layer.close(loading);
                    
                    if (data.code == '-1' || data.code == '-4') {
                        ErrorMsg(data.msg);
                        layer.close(climgdiv);
                        return false;
                    }
                    //监听div
                    var myChart = echarts.init(document.getElementById('CLImgDiv'));

                    //指定图表的配置项和数据
                    option = {
                        tooltip: {
                            trigger: 'axis'
                        },
                        title: {
                            text: '公告查看情况线形统计图',
                            x: 'left',
                            left: '30px',
                            top: '-5.5px'
                        },
						legend: {
							data: ['已查看人数','未查看人数']
						}/*,
						grid: {
							top: '50px',
							containLabel: true
						}*/,
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: notices,
                            name: '任务名'
                        },
                        yAxis: {
                            type: 'value',
                            name: '总查看人数'
                        },
                        series: data
                    };

                    //使用刚指定的配置项和数据显示图表。
                    myChart.setOption(option);

                    //设置css
                    $("div[id='CLImgDiv']").children("div").eq(0).css("overflow", "hidden");
                    $("div[id='CLImgDiv']").children("div").eq(0).children("canvas").css("top", "20px");
                }
            });
        }
    });
}
//获取被选择任务名称
function GetTaskName() {
    var tid = $("#ChangeTask").val();
    return $("#ChangeTask").children("option[value='"+tid+"']").text();
}
//列表信息排序
function OrderMsgBy(eq,clickid,othermsg = '') {
    var loading = layer.msg('排序中……',{
        icon: 20,
        time: 0
    });
    var ordermsg = new Array();
    if (othermsg != '') {
        var othermsgda = new Array();
    }
    $("#TbodyList").children("tr").each(function() {
        if (othermsg != '') {
            if ($(this).children("td").eq(eq).text() != othermsg) {
                ordermsg.push(($(this).children("td").eq(eq).text()).replace('%',''));
            } else {
                othermsgda.push($(this).prop("outerHTML"));
            }
        } else {
            ordermsg.push(($(this).children("td").eq(eq).text()).replace('%',''));
        }
    });
    
    if ($("#"+clickid).attr("order") == 'desc') {
        $("#"+clickid).html((($("#"+clickid).text()).replace('↑','')).replace('↓','') + '  <font color="red">↑</font>');
        $("#"+clickid).attr("order","");
        var ordermsg = ordermsg.sort(function(a,b){
            return a - b;
        });
    } else {
        $("#"+clickid).html((($("#"+clickid).text()).replace('↑','')).replace('↓','') + '  <font color="red">↓</font>');
        $("#"+clickid).attr("order","desc");
        var ordermsg = ordermsg.sort(function(a,b){
            return b - a;
        });
    }
    var divmsg = $("#TbodyList").prop("outerHTML");
    $("#TbodyList").html("");
    $("#TbodyList").parent("table").children("thead").children("tr").children("th").eq(1).text("序号");
    for (var i = 0;i < ordermsg.length;i++) {
        $(divmsg).children("tr").each(function() {
            /*var othermsgdiv = '';
            if ($(this).children("td").eq(eq).text() == othermsg && othermsg != '') {
                othermsgdiv = othermsgdiv + $(this).prop("outerHTML");
            } else */
            if ($(this).children("td").eq(eq).text() == ordermsg[i]+'%') {
                var div = $(this).prop("outerHTML");
                var id = $(div).attr("id");
                if (!$("tr[id='"+id+"']").html()) {
                    $("#TbodyList").append(div);
                    $(this).remove();
                    $("tr[id='"+id+"']").children("td").eq(1).text(i);
                }
            }
            
            /*if (othermsg != '' && i == ((ordermsg.length) - 1)) {
                $("#TbodyList").append(othermsgdiv);
            }*/
        });
    }
    if (othermsg != '') {
        for (var t = 0;t < othermsgda.length;t++) {
            if ($("#"+clickid).attr("order") == 'desc') {
                $("#TbodyList").append(othermsgda[t]);
            } else {
                $("#TbodyList").prepend(othermsgda[t]);
            }
        }
    }
    var i = 1;
    $("#TbodyList").children("tr").each(function() {
        $(this).children("td").eq(1).text(i++);
    });
    
    layer.close(loading);
}
//下载排序后的统计图
function DownloadOrderedImg(txt,type = '') {
    if ($("#thorderwcl").attr("order") == undefined) {
        ErrorMsg('请先进行排序');
        return false;
    }
    var loading = layer.msg('图片生成中……',{
        icon: 20,
        time: 0
    });
    
    $("#TbodyList").parent("table").children("thead").children("tr").children("th:last").hide();
    $("#TbodyList").parent("table").children("thead").children("tr").children("th").eq(0).hide();
    $("#TbodyList").children("tr").each(function() {
        $(this).children("td:last").hide();
        $(this).children("td").eq(0).hide();
    });
    
    if (type == 'class') {
        $("#TbodyList").parent("table").children("thead").children("tr").children("th").eq(4).hide();
        $("#TbodyList").children("tr").each(function() {
            $(this).children("td").eq(4).hide();
        });
    } else if (type == 'grade') {}
    
    var ordertype = ($("#thorderwcl").attr("order") == 'desc' ? '降序' : '升序');
    
    html2canvas(document.querySelector("#TbodyTableList")).then(canvas => {
        layer.close(loading);
        
        var taskname = $("option[value='"+$("#ChangeTask").val()+"']").text();
        var a = '<a id="DownloadImg" onclick="" href="'+canvas.toDataURL("image/png")+'" download="'+taskname+'の'+txt+'-'+ordertype+'.png"style="top: 12px;left: 5px;position: relative;">点击下载</a>';
        var DownloadAlert = layer.open({
            type: 1,
            content: a,
            area: ['250px','100px'],
            closeBtn: false,
            title: '点击按钮下载',
            shadeClose: true,
            btn: false
        });
        $("a[id='DownloadImg']").attr("onclick","layer.close('"+DownloadAlert+"');")
    });
    
    $("#TbodyList").parent("table").children("thead").children("tr").children("th:last").show();
    $("#TbodyList").parent("table").children("thead").children("tr").children("th").eq(0).show();
    $("#TbodyList").children("tr").each(function() {
        $(this).children("td:last").show();
        $(this).children("td").eq(0).show();
    });
    
    if (type == 'class') {
        $("#TbodyList").parent("table").children("thead").children("tr").children("th").eq(4).show();
        $("#TbodyList").children("tr").each(function() {
            $(this).children("td").eq(4).show();
        });
    } else if (type == 'grade') {}
}
//选择添加方式
function OpenAdminAddClass(gid) {
    var confirmix = layer.confirm('请选择添加方式',{
        icon: 0,
        closeBtn: 2,
        title: '方式选择',
        btn: ['添加单个','批量添加'],
        shade: 0.1
    },function() {
        layer.close(confirmix);
        layer.prompt({
            formType: 0,
            value: '',
            title: '请输入班级',
            closeBtn: 2,
            placeholder: '请输入班级',
            shade: 0.1,
            yes: function(index,promptdiv) {
                var value = promptdiv.find(".layui-layer-input").val();
                if (value == '') {
                    ErrorMsg('输入信息不能为空！');
                    return false;
                }
                if (isNaN(value)) {
                    ErrorMsg('输入信息只能为数字！');
                    return false;
                }
                var loading = layer.load(1);
                $.ajax({
                    type: "POST",
                    url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=AddClass",
                    data: {
                        gid: gid,
                        classval: value
                    },
                    dataType: 'json',
                    success: function (data) {
                        layer.close(loading);
                        if (data.code == 1) {
                            CorrectMsg(data.msg);
                            LoadDiv();
                        } else {
                            ErrorMsg(data.msg);
                        }
                    }
                });
                layer.close(index);
            }
        });
    },function() {
        layer.close(confirmix);
        layer.prompt({
            formType: 2,
            value: '',
            title: '请输入批量班级序号',
            closeBtn: 2,
            placeholder: '请输入批量班级序号【以,隔开】',
            shade: 0.1,
            yes: function(index,promptdiv) {
                var val = (promptdiv.find(".layui-layer-input").val()).split(',');
                if (val == '') {
                    ErrorMsg('输入信息不能为空！');
                    return false;
                }
                if (val.indexOf(',') != '-1') {
                    ErrorMsg('输入信息格式不对！');
                    return false;
                }
                var loading = layer.load(1);
                
                if (($("#ClassLists").text()).indexOf('暂无') != '-1') {
                    //var table = '<table style="width:100%;"><thead><tr><th style="width:5%;">ID</th><th style="width:20%;">班级名</th><th style="width:10%;">成员数量</th><th style="width:15%;">团支书姓名</th><th style="width:20%;">当前周完成情况</th><th style="width:30%;">操作</th></tr></thead><tbody id="TbodyList" style="text-align:center;"></tbody></table>';
                    //$("#ClassLists").html(table);
                }
                
                var c = 0;
                var loadloading = layer.msg('处理中<b>【<b id="AC" style="color:blue;">0</b>/'+val.length+'】</b>',{
                    icon: 20,
                    time: 0
                });
                                
                for (var i = 0;i < val.length;i++) {
                    var value = val[i];
                    $.ajax({
                        type: "POST",
                        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=AddClass",
                        data: {
                            gid: gid,
                            classval: value
                        },
                        dataType: 'json',
                        success: function (data) {
                            layer.close(loading);
                            if (data.code == 1) {
                                c++;
                                $("b[id='AC']").text(c);
                                if (c >= val.length) {
                                    layer.close(loadloading);
                                    LoadDiv();
                                }
                            } else {
                                ErrorMsg(data.msg);
                            }
                        }
                    });
                }
                layer.close(index);
            }
        });
    });
}
//删除班级
function AdminDelete(id,gid) {
    layer.alert('您确定要删除此班级？【删除后将不可恢复，班级内所有成员以及旗下的打卡记录和公告查看记录也将删除！】',{
        icon: 3,
        closeBtn: 2,
        title: '温馨提示',
        btn: ['确定','取消']
    },function() {
        var loading = layer.load(3);
        $.ajax({
            type: "POST",
            url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=DelClass",
            data: {
                gid: gid,
                id: id
            },
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                    /*$("#TbodyList").children("tr").each(function() {
                        var name = $(this).children("td").eq(0).text();
                        if (name == id) {
                            $(this).remove();
                        }
                    });*/
                    LoadDiv();
                } else {
                    ErrorMsg(data.msg);
                }
            }
        });
    });
}
//查看班级详细信息
function AdminLookInfo(id,gid) {
    var gradename = $("tr[cname='"+id+"']").children("td").eq(2).text();
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "GetClassInfo.php?GID="+gid,
        data: {
            classid: id
        },
        dataType: 'html',
        success: function (data) {
            layer.close(loading);
            layer.open({
                title: gradename+'信息查看',
                closeBtn: 2,
                btn: ['关闭'],
                area: ['560px','460px'],
                content: data
            });
        }
    });
}
//修改班级信息
function AdminEditClass(id,gid) {
    layer.prompt({
        formType: 0,
        value: $("tr[id="+id+"]").attr("cname"),
        title: '请修改班级号【班级内成员也会转移】',
        closeBtn: 2,
        placeholder: '请修改班级号【班级内成员也会转移】',
        shade: 0.1,
        yes: function(index, promptdiv) {
            var value = promptdiv.find(".layui-layer-input").val();
            if (value == '') {
                ErrorMsg('输入信息不能为空！');
                return false;
            }
            if (isNaN(value)) {
                ErrorMsg('输入信息只能为数字！');
                return false;
            }
            var loading = layer.load(1);
            $.ajax({
                type: "POST",
                url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=EditClass",
                data: {
                    gid: gid,
                    id: id,
                    classval: value
                },
                dataType: 'json',
                success: function(data) {
                    layer.close(loading);
                    if (data.code == 1) {
                        CorrectMsg(data.msg);
                        LoadDiv();
                    } else {
                        ErrorMsg(data.msg);
                    }
                }
            });
            layer.close(index);
        }
    });
}
//设置班级团支书
function AdminSetAdmin(id,gid) {
    var num = $("tr[id='"+id+"']").children("td").eq(2).text();
    if (num == '0人') {
        ErrorMsg('班级无成员，请添加成员！');
        return false;
    }
    
    var loading = layer.load(1);
    $.ajax({
        type: "POST",
        url: "GetMembers.php",
        data: {
            gid: gid,
            classid: id
        },
        dataType: 'html',
        success: function (data) {
            layer.close(loading);
            layer.alert(data,{
                title: '选择人员',
                closeBtn: 2,
                btn: false
            });
        }
    });
    
    //ToHref('SetAdmin.php?CID='+id);
}
//获取未完成按钮个数
function trnums() {
    /*var i = 1;
    $("#TbodyList").children("tr").each(function(){
        if ($(this).find("button").attr("type") == 'settle') {
            i++;
        }
    });*/
    var i = $("button[type='settle']").length;
    return i;
}


//依次点击验证按钮
function ClickSettleBtn(tid,type = '') {
    var loading = layer.msg('正在获取数据……',{
        icon: 20,
        time: 0
    });
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=IndexInfo&Type=SetFiniedFile",
        data: {
            tid: tid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                if (type == 'yes') {
                    CorrectMsg(data.msg);
                } else {
                    var loadloading = layer.msg('处理中<b>【<b id="TS" style="color:blue;">0</b>/'+trnums()+'】</b><b style="color:red;" onclick="LoadClickAgain();">长时间未加载请点击我</b>',{
                        icon: 20,
                        time: 0,
                        shade: 0.01
                    });
                    $("#TS").attr("index",loadloading).attr("num",trnums());
                    ClickSB('0');
                }
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}
function ClickSB(num) {
    $("#TbodyList").children("tr").eq(num).find("button[type='settle']").trigger("click");
    var nr = num-(-1);
    if (nr >= ($("#TbodyList").children("tr").length)) {
    } else {
        setTimeout(function() {
            ClickSB(nr);
        }, 1);
        if (nr >= 12) {
            $("#ClassLists").scrollTop($("#ClassLists").scrollTop() - -(22));
        }
    }
}

//验证是否完成学习
function TaskSettle(tid,cid) {
    $("#cid-"+cid).attr("class","ClickBT Bg-Primary").text("正在获取数据……");
    
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=IndexInfo&Type=TaskSettle",
        data: {
            tid: tid,
            cid: cid
        },
        dataType: 'json',
        success: function (data) {
            var dnum = ($("#TS").text()) - (-1);
            $("#TS").text(dnum);
            if (dnum >= $("#TS").attr("num")) {
                layer.close($("#TS").attr("index"));
                CorrectMsg('结算处理完毕！');
            }
            if (data.code == '1') {
                $("#cid-"+cid).attr("class","ClickBT Bg-Greens").attr("onclick","").attr("type","").text(data.msg);
            } else {
                $("#cid-"+cid).attr("class","ClickBT Bg-Reds").text(data.msg);
            }
        }
    });
}

//依次点击验证按钮
function AdminClickSettleBtn(tid,type = '') {
    var loading = layer.msg('正在获取数据……',{
        icon: 20,
        time: 0
    });
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=SetFiniedFile",
        data: {
            tid: tid
        },
        dataType: 'json',
        success: function (data) {
            layer.close(loading);
            if (data.code == 1) {
                if (type == 'yes') {
                    CorrectMsg(data.msg);
                } else {
                    var loadloading = layer.msg('处理中<b>【<b id="TS" style="color:blue;">0</b>/'+trnums()+'】</b><b style="color:red;" onclick="LoadClickAgain();">长时间未加载请点击我</b>',{
                        icon: 20,
                        time: 0,
                        shade: 0.01
                    });
                    $("#TS").attr("index",loadloading).attr("num",trnums());
                    ClickSB('0');
                }
            } else {
                ErrorMsg(data.msg);
            }
        }
    });
}

//验证是否完成学习
function AdminTaskSettle(tid,cid,gn,goid) {
    $("#cid-"+cid).attr("class","ClickBT Bg-Primary").text("正在获取数据……");
    
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=TaskSettle",
        data: {
            tid: tid,
            cid: cid,
            gn: gn,
            goid: goid
        },
        dataType: 'json',
        success: function (data) {
            var dnum = ($("#TS").text()) - (-1);
            $("#TS").text(dnum);
            if (dnum >= $("#TS").attr("num")) {
                layer.close($("#TS").attr("index"));
                CorrectMsg('结算处理完毕！');
            }
            if (data.code == '1') {
                $("#cid-"+cid).attr("class","ClickBT Bg-Greens").attr("onclick","").attr("type","").text(data.msg);
            } else {
                $("#cid-"+cid).attr("class","ClickBT Bg-Reds").text(data.msg);
            }
        }
    });
}

//重新加载未响应请求
function LoadClickAgain() {
    $("#TbodyList").children("tr").each(function(){
        if ($(this).find("button").text() == '正在获取数据……') {
            $(this).find("button").trigger("click");
        }
    });
    //CorrectMsg('执行成功！');
}
//跳转到下载界面
function DownLoadDT(status = '') {
    var dt = $("#DTChosen").val();
    if (dt.length <= 0) {
        ErrorMsg('请选择任务！');
        return false;
    }
    var dg = $("#DGChosen").val();
    if (dg == '0' || dg == '') {
        ErrorMsg('请选择年级！');
        return false;
    }
    var dc = $("#DCChosen").val();
    if (dc.length <= 0) {
        ErrorMsg('请选择班级！');
        return false;
    }
    
    if (status == '') {
        for (var i = 0;i < dc.length;i++) {
            window.open('DownLoadDT.php?TID='+dt.join(',')+'&GID='+dg+'&CID='+dc[i]);
        }
    } else {
        for (var i = 0;i < dc.length;i++) {
            window.open('DownLoadDT.php?TID='+dt.join(',')+'&ST='+status+'&GID='+dg+'&CID='+dc[i]);
        }
    }
}
//选择年级后显示班级
function ChangeClasses() {
    var gid = $("#DGChosen").val();
    if (gid == '0') {
        $("#DCChosen").html('<option value="0">请选择年级再选择班级</option>').attr("style","line-height:30px;height:100px;cursor:not-allowed;");
    } else {
        $("#DCChosen").attr("style","line-height:30px;height:100px;").html("");
        for (var i = 0;i < cids.length;i++) {
            var data = cids[i];
            if (gid == data.gid) {
                $("#DCChosen").append('<option value="'+data.cname+'">'+data.gn+data.cname+'班</option>');
            }
        }
    }
}
//打印指定区域内容
function Print(obj){
    var newWindow = window.open("打印窗口","_blank");
    var docStr = $(obj).html();
    newWindow.document.write(docStr);
    newWindow.document.close();
    newWindow.print();
    newWindow.close();
}
//获取被开除团员名单
function GetOutVip(cid,num) {
    $.ajax({
        type: "POST",
        url: "/Ajax-Front.php?Act=AdminIndexInfo&Type=GetOutVip",
        data: {
            cid: cid,
            num: num,
            gid: getUrlParam('GID')
        },
        dataType: 'json',
        success: function (data) {
            if (data.code == '1') {
                $("#cid-"+cid).html(data.data);
            } else {
                $("#cid-"+cid).html(data.data);
            }
        }
    });
}
//跳转到指定数量
function LoadNewNum() {
    var num = $("input[name='memnum']").val();
    window.location.href = ((document.URL).split('?'))[0]+'?GID='+getUrlParam('GID')+'&Num='+num;
}
//输入班级号显示班级
function SearchDisplayClass() {
    var cid = $("input[name='Classnum']").val();
    if (cid == '') {
        $(".OutVipList_User").show();
        $("b[name='OutVipList_User']").show();
        return false;
    } else {
        $(".OutVipList_User").hide();
        $("b[name='OutVipList_User']").hide();
    }
    $(".OutVipList_User").each(function() {
        var span = $(this);
        span.hide();
        if (span.attr("id") == 'cid-'+cid) {
            span.show();
        }
    });
    $("b[name='OutVipList_User']").each(function() {
        var span = $(this);
        span.hide();
        if (span.attr("class") == 'cid-'+cid) {
            span.show();
        }
    });
}
var did = 'false';
//批量成员转班
function ListChangeClass() {
    var fileObj = $("#File")[0].files[0];
    if (typeof (fileObj) == "undefined" || fileObj.size <= 0) {
        ErrorMsg('请上传文件');
        return false;
    }
    var File = new FileReader();
    File.readAsText(fileObj, 'utf-8');
    //File.readAsText(fileObj, 'Unicode');
            
    File.onload = function() {
        var txt = this.result;
        var tarr = (txt.split('\n'));
        var mencounts = tarr.length;
        var LoadMainIf;
        LoadMainIf = layer.msg('正在处理……<b>【<b id="docounts" style="color: blue;">0</b>人/'+mencounts+'人】</b>',{
        //var LoadMainIf = layer.msg('正在处理……',{
            icon: 20,
            time: 0
        });
        did = 'true';
        getCCDoing('ListChangeClassDoing',mencounts);
        
        $.ajax({
            type: 'POST',
            url: '/Ajax-Front.php?Act=IndexInfo&Type=ListChangeClass',
            data: {
                men: txt
            },
            dataType: 'json',
            /*complete: function() {
                LoadMainIf = layer.msg('处理中……',{
                    icon: 20,
                    time: 0
                });
            },*/
            success: function(data) {
                did = 'false';
                $("#File").val("");
                layer.close(LoadMainIf);
                if (data.code == 1) {
                    CorrectMsg(data.msg);
                } else {
                    layer.alert(data.msg,{
                        title: '提示',
                        icon: 2,
                        closeBtn: 2
                    });
                }
            }
        });
        
        /*for (var i = 0;i < mencounts;i++) {
            var arr = tarr[i];
            var ar = arr.split('-');
            var uclass = ar[0];
            var uname = ar[1];
            
            doChangeClass(uclass,uname,mencounts,LoadMainIf);
        }*/
    }
}
//批量成员转班 操作
/*function doChangeClass(uclass,uname,mencounts,index) {
    var nowcounts = $("#docounts").text();
    
    if (nowcounts >= mencounts) {
        layer.close(index);
        CorrectMsg('处理完毕！请分班核对');
    }
}*/
//获取转班进度
function getCCDoing(name,all) {
    var count = getCookie(name);
    
    $("#docounts").text(count);
    setTimeout(function() {
        if (did == 'true') {
            getCCDoing(name,all);
        }
    }, 100);
}
//获取cookie
function getCookie(name) {
    var result;
    $.ajax({
        type: 'POST',
        async: false,
        url: '/Ajax-Front.php?Act=getCookie',
        data: {
            name: name
        },
        dataType: 'html',
        success: function(data) {
            result = data;
        }
    });
    return result;
}