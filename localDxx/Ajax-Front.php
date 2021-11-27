<?php
if ($_GET['Act'] == 'SetUnFinishMem') {
    require './Admin/Includes/Config.php';
    require './Admin/Includes/RootConfig.php';
    $username = $_GET['user'];
    $password = md5($_GET['pass']);
    try {
        $GDB = new PDO("mysql:host={$dbconfig['host']};dbname={$dbconfig['dbname']};port={$dbconfig['port']}", $dbconfig['user'], $dbconfig['pwd']);
    }
    catch(Exception $e) {
        exit('连接数据库失败:' . $e->getMessage());
    }
    $adminrow = $GDB->query("SELECT * FROM Web_Admin WHERE username='{$username}' limit 1")->fetch();
    if (!$adminrow) {
        exit('{"code":-1,"msg":"没有此管理员，请联系网站管理添加！"}');
    }
    if ($username == $adminrow['username'] && $password == $adminrow['password']) {
        foreach ($GDB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            foreach ($thisDB->query("SELECT * FROM Web_Task")->fetchAll() as $task) {
                if ($thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='3'")->fetchColumn() != 0) {
                    continue;
                }
                foreach ($thisDB->query("SELECT * FROM Web_User WHERE username!='Administrator'")->fetchAll() as $us) {
                    $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$us['id']}' limit 1")->fetch();
                    if ($thisDB->query("SELECT * FROM Web_Clocking WHERE uid='{$us['id']}' and taskbatch='{$task['id']}' LIMIT 1")->fetch()) {
                    } else {
                        $sql[] = $thisDB->exec("INSERT INTO `Web_Clocking` (`uid`, `addtime`, `taskbatch`, `class`, `status`) VALUES ('{$user['id']}', '{$date}', '{$task['id']}', '{$user['class']}', '3')");
                    }
                }
            }
        }
        if (!empty($sql)) {
            exit('{"code":1,"msg":"设置成功！"}');
        } else {
            exit('{"code":-1,"msg":"设置失败！"}');
        }
    } elseif ($password != $adminrow['password']) {
        exit('{"code":-1,"msg":"账户密码输入错误！"}');
    }
    exit();
}
if (strpos($_GET['Act'], 'Admin') !== false) {
    include ("./Admin/Includes/Common_Two.php");
} else {
    include ("./Includes/Common_Two.php");
}
$act = isset($_GET['Act']) ? daddslashes($_GET['Act']) : null;
@header('Content-Type: application/json; charset=UTF-8');
//获取随机颜色
function randomColor() {
    $str = '#';
    for ($i = 0; $i < 6; $i++) {
        $randNum = rand(0, 15);
        switch ($randNum) {
            case 10:
                $randNum = 'A';
                break;

            case 11:
                $randNum = 'B';
                break;

            case 12:
                $randNum = 'C';
                break;

            case 13:
                $randNum = 'D';
                break;

            case 14:
                $randNum = 'E';
                break;

            case 15:
                $randNum = 'F';
                break;
        }
        $str.= $randNum;
    }
    return $str;
}
function ifinfinished($name, $class, $filename, $ifmsg) {
    $filemsg = json_decode(file_get_contents($filename) , true);
    foreach ($filemsg as $f) {
        if ($f['name'] == $name && $f['orgName'] == $ifmsg) {
            $result = 'yes';
        } else {
            continue;
        }
    }
    if ($result == 'yes') {
        return true;
    } else {
        return false;
    }
}
switch ($act) {
    case 'Captcha':
        $CAPTCHA_ID = 'b31335edde91b2f98dacd393f6ae6de8';
        $PRIVATE_KEY = '170d2349acef92b7396c7157eb9d8f47';
        require_once './Includes/Class.Geetestlib.php';
        $GtSdk = new GeetestLib($CAPTCHA_ID, $PRIVATE_KEY);
        $data = array(
            'user_id' => isset($pid) ? $pid : 'public', // 网站用户id
            'client_type' => "web", // web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            'ip_address' => $ip
            // 请在此处传输用户请求验证时所携带的IP
            
        );
        $status = $GtSdk->pre_process($data, 1);
        $_SESSION['gtserver'] = $status;
        $_SESSION['user_id'] = isset($pid) ? $pid : 'public';
        echo $GtSdk->get_response_str();
        break;

    case 'Login':
        $username = daddslashes($_POST['username']);
        $pwd = daddslashes($_POST['password']);
        $classid = daddslashes($_POST['classid']);
        $password = md5($pwd);
        $logintime = time();
        if (preg_match('/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\-|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/', $username) || preg_match('/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\-|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/', $password)) {
            exit('{"code":-1,"msg":"姓名或密码不能包含特殊字符！"}');
        }
        if (@preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $userrow)) {
            exit('{"code":-1,"msg":"姓名必须是中文！"}');
        }
        $userrow = $DB->query("SELECT * FROM Web_User WHERE username='{$username}' and class='{$classid}' limit 1")->fetch();
        if (!$userrow) {
            exit('{"code":-1,"msg":"选择的班级中不存在此账户，可能未开通，请联系团支书！"}');
        }
        if ($username == $userrow['username'] && $password == $userrow['password']) {
            //$city=get_ip_city($clientip);
            $session = md5($username . $password . $password_hash);
            $expiretime = time() + 604800;
            $token = @authcode("{$username}\t{$userrow['class']}\t{$session}\t{$expiretime}", 'ENCODE', SYS_KEY);
            setcookie("user_token", $token, time() + 604800);
            exit('{"code":1,"msg":"账户登录成功!"}');
        } elseif ($password != $userrow['password']) {
            exit('{"code":-1,"msg":"账户密码输入错误！"}');
        }
        break;

    case 'Logout':
        $result = setcookie("user_token", "", time() - 604800);
        //$logouttime = time();
        if ($result) {
            //$sql = $DB->exec("UPDATE `Web_User` SET `logouttime`='$logouttime', `loginstatus`='7' WHERE `id`='{$userrow['id']}'");
            exit('{"code":1,"msg":"账户注销成功！跳转中..."}');
        } else {
            exit('{"code":-1,"msg":"账户注销失败！"}');
        }
        break;

    case 'SetIntoFile':
        $msg = daddslashes($_POST['msg']);
        if ($conf['token'] == null) {
            $token = $msg;
        } else {
            $data = array(
                "pageNo" => "1",
                "pageSize" => "100"
            );
            $ta = json_decode($conf['token'], true);
            $tk = $ta['token'];
            $return = json_decode(get_content('http://dxx.scyol.com/backend/stages/list', $tk, json_encode($data)) , true);
            if (strpos($return['msg'], '未登录') !== false) {
                $data = array(
                    "username" => "阆中中学",
                    "password" => "6306533"
                );
                $url = "http://dxx.scyol.com/backend/adminUser/login";
                $result = login_post($url, 'test.txt', json_encode($data));
                $ra = json_decode($result, true);
                $rs = $ra['data'];
                $token = str_replace('\\', '\\\\', json_encode($rs));
            } else {
                $token = str_replace('\\', '\\\\', $conf['token']);
            }
        }
        $do = $DB->exec("UPDATE `Web_Config` SET `j`='{$token}' WHERE `x`='token'");
        if ($do) {
            exit('{"code":1,"msg":"信息存入成功！"}');
        } elseif (str_replace('\\', '\\\\', $conf['token']) == $token) {
            exit('{"code":1,"msg":"信息存入成功！"}');
        } else {
            exit('{"code":-1,"msg":"信息存入失败！"}');
        }
        break;

    case 'IfThisFinished':
        $msg = json_decode($conf['token'], true);
        $token = $msg['token'];
        $time = time();
        $task = $DB->query("SELECT * FROM Web_Task WHERE addtime<='{$time}' and endtime>='{$time}' order by id desc limit 1")->fetch();
        if ($conf['orgid'] == null) {
            exit('{"code":-1,"msg":"本年级未设置对应组织ID，请联系总管理设置"}');
        }
        $class = $DB->query("SELECT * FROM Web_Class WHERE name='{$userrow['class']}'")->fetch();
        
        if ($class['orgid'] == null) {
            $cdata = array(
                "pid" => $conf['orgid'],
                "orgName" => "{$conf['gradename']}{$userrow['class']}班团支部",
                "stagesId" => $task['stid'],
                "pageNo" => "1",
                "pageSize" => "10"
            );
            $res = get_content('http://dxx.scyol.com/backend/study/organize/list', $token, json_encode($cdata));
            $ra = json_decode($res, true);
            if (@count($ra['data']) == 0) {
                exit('{"code":-1,"msg":"未从大学习后台获取到此班级，请联系总管理"}');
            } else {
                foreach ($ra['data'] as $r) {
                    $DB->exec("UPDATE `Web_Class` SET `orgid`='{$r['orgId']}' WHERE `name`='{$userrow['class']}'");
                    $coid = $r['orgId'];
                }
            }
        } else {
            $coid = $class['orgid'];
        }
        $data = array(
            "orgId" => $coid,
            "stagesId" => $task['stid'],
            "name" => $userrow['username'],
            "tel" => "",
            "pageNo" => "1",
            "pageSize" => "10000"
        );
        /*if ($DB->query("SELECT * FROM Web_Clocking WHERE uid='{$userrow['id']}' and taskbatch='{$task['id']}' and status='1' limit 1")->fetch()) {
            exit('{"code":-1,"msg":"本周您已打卡！"}');
        }*/
        $result = get_content('http://dxx.scyol.com/backend/study/student/list', $token, json_encode($data));
        $rarr = json_decode($result, true);
        if (@count($rarr['data']) >= 1) {
            $cl = $DB->query("SELECT * FROM Web_Clocking WHERE uid='{$userrow['id']}' and taskbatch='{$task['id']}' and status!='1' limit 1")->fetch();
            $DB->exec("UPDATE `Web_Clocking` SET `status`='1', `addtime`='{$date}' WHERE `id`='{$cl['id']}'");
            /*$cid = ($DB->query("select LAST_INSERT_ID()")->fetch()) ['LAST_INSERT_ID()'];
            if ($sql) {
                exit('{"code":1,"msg":"打卡成功！","cid":"' . $cid . '"}');
            } else {
                exit('{"code":-1,"msg":"打卡失败！"}');
            }*/
            exit('{"code":1,"msg":"您已完成学习！再接再厉！"}');
        } else {
            exit('{"code":-1,"msg":"您未完成学习！请尽快完成！"}');
        }
        break;

    case 'IndexInfo':
        if ($islogin2 != 1) {
            exit('{"code":-4,"msg":"未登录！"}');
        }
        $type = $_GET['Type'];
        if ($type == 'AddClass' && $userrow['status'] == 1) {
            $classval = daddslashes($_POST['classval']);
            if (!is_numeric($classval)) {
                exit('{"code":-1,"msg":"输入信息只能为数字！"}');
            }
            $row = $DB->query("SELECT * FROM Web_Class WHERE name='$classval' limit 1")->fetch();
            if ($row) {
                exit('{"code":-1,"msg":"该班级已存在！"}');
            }
            $sql = $DB->exec("INSERT INTO `Web_Class` (`name`, `addtime`) VALUES ('{$classval}', '{$date}')");
            $cid = ($DB->query("select LAST_INSERT_ID()")->fetch()) ['LAST_INSERT_ID()'];
            if ($sql) {
                exit('{"code":1,"msg":"添加成功！","cid":"' . $cid . '"}');
            } else {
                exit('{"code":-1,"msg":"添加失败！"}');
            }
        } elseif ($type == 'EditClass' && $userrow['status'] == 1) {
            $id = daddslashes($_POST['id']);
            $classval = daddslashes($_POST['classval']);
            if (!is_numeric($classval)) {
                exit('{"code":-1,"msg":"输入信息只能为数字！"}');
            }
            $row = $DB->query("SELECT * FROM Web_Class WHERE id='$id' limit 1")->fetch();
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            $sql[] = $DB->exec("UPDATE `Web_User` SET `class`='{$classval}' WHERE `class`='{$row['name']}'");
            $sql[] = $DB->exec("UPDATE `Web_Class` SET `name`='{$classval}' WHERE `id`='{$id}'");
            if (!empty($sql)) {
                exit('{"code":1,"msg":"修改成功！"}');
            } elseif ($classval == $row['name']) {
                exit('{"code":1,"msg":"与之前相同，无需修改！"}');
            } else {
                exit('{"code":-1,"msg":"修改失败！"}');
            }
        } elseif ($type == 'DelClass' && $userrow['status'] == 1) {
            $id = daddslashes($_POST['id']);
            $row = $DB->query("SELECT * FROM Web_Class WHERE id='$id' limit 1")->fetch();
            if (!$row && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    $r = $DB->query("SELECT * FROM Web_Class WHERE id='$i' limit 1")->fetch();
                    foreach ($DB->query("SELECT * FROM Web_User WHERE class='{$r['name']}' limit 1")->fetchAll() as $u) {
                        $sql[] = $DB->exec("DELETE FROM `Web_NoticeLooked` WHERE uid='{$u['id']}'");
                    }
                    $sql[] = $DB->exec("DELETE FROM `Web_Class` WHERE id='{$i}'");
                    $sql[] = $DB->exec("DELETE FROM `Web_User` WHERE class='{$i}'");
                    $sql[] = $DB->exec("DELETE FROM `Web_Clocking` WHERE class='{$i}'");
                }
            } else {
                foreach ($DB->query("SELECT * FROM Web_User WHERE class='{$row['name']}' limit 1")->fetchAll() as $u) {
                    $sql[] = $DB->exec("DELETE FROM `Web_NoticeLooked` WHERE uid='{$u['id']}'");
                }
                $sql[] = $DB->exec("DELETE FROM `Web_Class` WHERE id='{$id}'");
                $sql[] = $DB->exec("DELETE FROM `Web_User` WHERE class='{$id}'");
                $sql[] = $DB->exec("DELETE FROM `Web_Clocking` WHERE class='{$id}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"删除成功！"}');
            } else {
                exit('{"code":-1,"msg":"删除失败！"}');
            }
        } elseif ($type == 'SetLid' && $userrow['status'] == 1) {
            $id = daddslashes($_POST['id']);
            $user = $DB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
            $classid = daddslashes($_POST['classid']);
            if ($classid == 'All') {
                $classid = $user['class'];
            }
            $row = $DB->query("SELECT * FROM Web_Class WHERE name='$classid' limit 1")->fetch();
            if (!$user) {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            if ($row['lid'] == $id) {
                $lid = 0;
                $sql[] = $DB->exec("UPDATE `Web_Class` SET `lid`='{$lid}' WHERE `name`='{$classid}'");
                $sql[] = $DB->exec("UPDATE `Web_User` SET `status`='0' WHERE `id`='{$user['id']}'");
            } else {
                $lid = $id;
                $sql[] = $DB->exec("UPDATE `Web_User` SET `status`='0' WHERE `id`='{$row['lid']}'");
                $sql[] = $DB->exec("UPDATE `Web_Class` SET `lid`='{$lid}' WHERE `name`='{$classid}'");
                $sql[] = $DB->exec("UPDATE `Web_User` SET `status`='2' WHERE `id`='{$user['id']}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"设置成功！","lid":"' . $lid . '"}');
            } else {
                exit('{"code":-1,"msg":"设置失败！"}');
            }
        } elseif ($type == 'SetFLid' && $userrow['status'] == 1) {
            $id = daddslashes($_POST['id']);
            $user = $DB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
            $classid = daddslashes($_POST['classid']);
            if ($classid == 'All') {
                $classid = $user['class'];
            }
            $row = $DB->query("SELECT * FROM Web_Class WHERE name='$classid' limit 1")->fetch();
            if (!$user) {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            if ($user['id'] == $row['lid']) {
                exit('{"code":-1,"msg":"已是团支书，不能设置为代理团支书！"}');
            }
            if ($user['status'] == 2) {
                $dtype = 0;
                $sql = $DB->exec("UPDATE `Web_User` SET `status`='0' WHERE `id`='{$user['id']}'");
            } else {
                $dtype = 2;
                $sql = $DB->exec("UPDATE `Web_User` SET `status`='2' WHERE `id`='{$user['id']}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"设置成功！","type":"' . $dtype . '","lid":"' . $user['id'] . '"}');
            } else {
                exit('{"code":-1,"msg":"设置失败！"}');
            }
        } elseif ($type == 'SetVip' && ($userrow['status'] == 1 || $userrow['status'] == 2)) {
            $id = daddslashes($_POST['id']);
            $user = $DB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
            $classid = daddslashes($_POST['classid']);
            if ($classid == 'All') {
                $classid = $user['class'];
            }
            $row = $DB->query("SELECT * FROM Web_Class WHERE name='$classid' limit 1")->fetch();
            if (!$user) {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            
            $flmsg = @file_get_contents('./Txts/tuanyuan/'.$conf['orgid'].'.txt');
            $flarr = @json_decode($flmsg,true);
            if ($flmsg == null || $flmsg == 'null') {
                $dtype = 2;
                $do = file_put_contents('./Txts/tuanyuan/'.$conf['orgid'].'.txt',"[{$user['id']}]");
            } elseif (@in_array($user['id'],$flarr)) {
                $dtype = 0;
                foreach ($flarr as $fa) {
                    if ($fa != $user['id']) {
                        $ar[] = $fa;
                    }
                }
                $do = file_put_contents('./Txts/tuanyuan/'.$conf['orgid'].'.txt',@json_encode($ar));
            } else {
                $dtype = 2;
                @array_push($flarr,$user['id']);
                $do = file_put_contents('./Txts/tuanyuan/'.$conf['orgid'].'.txt',@json_encode($flarr));
            }
            if ($do) {
                exit('{"code":1,"msg":"设置成功！","type":"' . $dtype . '","lid":"' . $user['id'] . '"}');
            } else {
                exit('{"code":-1,"msg":"设置失败！"}');
            }
        } elseif ($type == 'SetSomeFLid' && $userrow['status'] == 1) {
            foreach (daddslashes($_POST['id']) as $id) {
                $user = $DB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
                $classid = daddslashes($_POST['classid']);
                if ($classid == 'All') {
                    $classid = $user['class'];
                }
                $row = $DB->query("SELECT * FROM Web_Class WHERE name='$classid' limit 1")->fetch();
                if (!$user) {
                    continue;
                }
                if (!$row) {
                    continue;
                }
                if ($user['id'] == $row['lid']) {
                    continue;
                }
                if ($user['status'] == 2) {
                    $dtype = 0;
                    $sql[] = $DB->exec("UPDATE `Web_User` SET `status`='0' WHERE `id`='{$user['id']}'");
                } else {
                    $dtype = 2;
                    $sql[] = $DB->exec("UPDATE `Web_User` SET `status`='2' WHERE `id`='{$user['id']}'");
                }
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"设置成功！","type":"' . $dtype . '","lid":"' . $user['id'] . '"}');
            } else {
                exit('{"code":-1,"msg":"设置失败！"}');
            }
        } elseif ($type == 'AddMember' && ($userrow['status'] == 1 || $userrow['status'] == 2)) {
            $username = daddslashes($_POST['username']);
            $classid = daddslashes($_POST['classid']);
            $user = $DB->query("SELECT * FROM Web_User WHERE username='{$username}' and class='{$classid}' limit 1")->fetch();
            $row = $DB->query("SELECT * FROM Web_Class WHERE name='$classid' limit 1")->fetch();
            if (@preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $userrow)) {
                exit('{"code":-1,"msg":"输入信息只能为中文！"}');
            }
            if ($user) {
                exit('{"code":-1,"msg":"该用户已存在！"}');
            }
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            if ($userrow['status'] == 2) {
                if ($classid != $userrow['class']) {
                    exit('{"code":-1,"msg":"您无权限添加新成员于此班级！"}');
                }
            }
            $p = md5(123456);
            $uskey = str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $sql = $DB->exec("INSERT INTO `Web_User` (`username`, `password`, `class`, `addtime`, `uskey`) VALUES ('{$username}', '{$p}', '{$classid}', '{$date}', '{$uskey}')");
            $mid = ($DB->query("select LAST_INSERT_ID()")->fetch()) ['LAST_INSERT_ID()'];
            if ($sql) {
                exit('{"code":1,"msg":"添加成功！","mid":"' . $mid . '","classid":"' . $classid . '"}');
            } else {
                exit('{"code":-1,"msg":"添加失败！"}');
            }
        } elseif ($type == 'ChangeClass' && $userrow['status'] == 1) {
            $mid = daddslashes($_POST['mid']);
            $classid = daddslashes($_POST['classid']);
            $user = $DB->query("SELECT * FROM Web_User WHERE id='{$mid}' limit 1")->fetch();
            $row = $DB->query("SELECT * FROM Web_Class WHERE id='{$classid}' limit 1")->fetch();
            if (!$user && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            if ($_POST['dotype'] == 'Array') {
                foreach ($mid as $mi) {
                    $sql[] = $DB->exec("UPDATE `Web_User` SET `class`='{$row['name']}' WHERE `id`='{$mi}'");
                    $sql[] = $DB->exec("UPDATE `Web_Clocking` SET `class`='{$row['name']}' WHERE `uid`='{$mi}'");
                }
            } else {
                $sql[] = $DB->exec("UPDATE `Web_User` SET `class`='{$row['name']}' WHERE `id`='{$mid}'");
                $sql[] = $DB->exec("UPDATE `Web_Clocking` SET `class`='{$row['name']}' WHERE `uid`='{$mid}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"转班成功！"}');
            } else {
                exit('{"code":-1,"msg":"转班失败！"}');
            }
        } elseif ($type == 'DelMember' && ($userrow['status'] == 1 || $userrow['status'] == 2)) {
            $id = daddslashes($_POST['id']);
            $user = $DB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
            $row = $DB->query("SELECT * FROM Web_Class WHERE name='{$user['class']}' limit 1")->fetch();
            if (!$user && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            if (!$row && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            if ($userrow['status'] == 2) {
                if ($user['class'] != $userrow['class']) {
                    exit('{"code":-1,"msg":"您无权限删除此成员！"}');
                } else if ($user['id'] == $userrow['id']) {
                    exit('{"code":-1,"msg":"不能操作自己账户！"}');
                }
            }
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    $u = $DB->query("SELECT * FROM Web_User WHERE id='{$i}' limit 1")->fetch();
                    /*if (!$DB->query("SELECT * FROM Web_Class WHERE name='{$u['class']}' limit 1")->fetch()) {
                        exit('{"code":-1,"msg":"该班级不存在！"}');
                    }*/
                    if ($userrow['status'] == 2) {
                        if ($u['class'] != $userrow['class']) {
                            exit('{"code":-1,"msg":"您无权限删除此成员！"}');
                        } else if ($u['id'] == $userrow['id']) {
                            exit('{"code":-1,"msg":"不能操作自己账户！"}');
                        }
                    }
                    $sql[] = $DB->exec("DELETE FROM `Web_User` WHERE id='{$i}'");
                    $sql[] = $DB->exec("DELETE FROM `Web_Clocking` WHERE uid='{$i}'");
                    $sql[] = $DB->exec("DELETE FROM `Web_NoticeLooked` WHERE uid='{$i}'");
                }
            } else {
                $sql[] = $DB->exec("DELETE FROM `Web_User` WHERE id='{$id}'");
                $sql[] = $DB->exec("DELETE FROM `Web_Clocking` WHERE uid='{$id}'");
                $sql[] = $DB->exec("DELETE FROM `Web_NoticeLooked` WHERE uid='{$id}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"删除成功！"}');
            } else {
                exit('{"code":-1,"msg":"删除失败！"}');
            }
        } elseif ($type == 'SetPassword' && ($userrow['status'] == 1 || $userrow['status'] == 2)) {
            $id = daddslashes($_POST['id']);
            $user = $DB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
            if (!$user && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            if ($userrow['status'] == 2) {
                if ($user['class'] != $userrow['class']) {
                    exit('{"code":-1,"msg":"您无权限重置此用户密码！"}');
                } else if ($user['id'] == $userrow['id']) {
                    exit('{"code":-1,"msg":"不能操作自己账户！"}');
                }
            }
            $p = md5(123456);
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    $u = $DB->query("SELECT * FROM Web_User WHERE id='{$i}' limit 1")->fetch();
                    if ($userrow['status'] == 2) {
                        if ($u['class'] != $userrow['class']) {
                            exit('{"code":-1,"msg":"您无权限重置此用户密码！"}');
                        } else if ($u['id'] == $userrow['id']) {
                            exit('{"code":-1,"msg":"不能操作自己账户！"}');
                        }
                    }
                    $sql[] = $DB->exec("UPDATE `Web_User` SET `password`='{$p}' WHERE `id`='{$i}'");
                }
            } else {
                $sql[] = $DB->exec("UPDATE `Web_User` SET `password`='{$p}' WHERE `id`='{$id}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"重置成功！"}');
            } elseif ($user['password'] == $p) {
                exit('{"code":1,"msg":"与之前相同，无需重置！"}');
            } else {
                exit('{"code":-1,"msg":"重置失败！"}');
            }
        } elseif ($type == 'EditName' && ($userrow['status'] == 1 || $userrow['status'] == 2)) {
            $id = daddslashes($_POST['id']);
            $name = daddslashes($_POST['name']);
            $user = $DB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
            if (!$user && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            if ($userrow['status'] == 2) {
                if ($user['class'] != $userrow['class']) {
                    exit('{"code":-1,"msg":"您无权限修改此用户名字！"}');
                } else if ($user['id'] == $userrow['id']) {
                    exit('{"code":-1,"msg":"不能操作自己账户！"}');
                }
            }
            
            $sql = $DB->exec("UPDATE `Web_User` SET `username`='{$name}' WHERE `id`='{$id}'");
            if ($sql) {
                exit('{"code":1,"msg":"修改成功！"}');
            } elseif ($user['username'] == $name) {
                exit('{"code":1,"msg":"与之前相同，无需修改！"}');
            } else {
                exit('{"code":-1,"msg":"修改失败！"}');
            }
        } elseif ($type == 'SetAllPassword' && $userrow['status'] == 1) {
            $classid = daddslashes($_POST['classid']);
            $row = $DB->query("SELECT * FROM Web_Class WHERE name='{$classid}' limit 1")->fetch();
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            $p = md5(123456);
            $sql = $DB->exec("UPDATE `Web_User` SET `password`='{$p}' WHERE `class`='{$classid}'");
            if ($sql) {
                exit('{"code":1,"msg":"重置成功！"}');
            } else {
                exit('{"code":-1,"msg":"重置失败，可能用户密码并未修改！"}');
            }
        } elseif ($type == 'EditPassword') {
            $password = md5(daddslashes($_POST['password']));
            if ($password == md5(123456) || $password == md5(123456789) || $password == md5(123123)) {
                exit('{"code":-1,"msg":"密码过于简单，请重新修改！"}');
            }
            $sql = $DB->exec("UPDATE `Web_User` SET `password`='{$password}' WHERE `id`='{$userrow['id']}'");
            if ($sql) {
                $result = setcookie("user_token", "", time() - 604800);
                exit('{"code":1,"msg":"修改成功！"}');
            } elseif ($password == $userrow['password']) {
                exit('{"code":-1,"msg":"与之前相同，无需修改！"}');
            } else {
                exit('{"code":-1,"msg":"修改失败！"}');
            }
        } elseif ($type == 'DeleteTask' && $userrow['status'] == 1) {
            $id = daddslashes($_POST['id']);
            $row = $DB->query("SELECT * FROM Web_Task WHERE id='$id' limit 1")->fetch();
            if (!$row && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该任务不存在！"}');
            } elseif ($row['status'] == 1) {
                exit('{"code":-1,"msg":"此任务为总管理所创建，您无权限操作！"}');
            }
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    $row = $DB->query("SELECT * FROM Web_Task WHERE id='$i' limit 1")->fetch();
                    if ($row['status'] == 1) {
                        continue;
                    }
                    $sql[] = $DB->exec("DELETE FROM `Web_Task` WHERE id='{$i}'");
                    $sql[] = $DB->exec("DELETE FROM `Web_Clocking` WHERE taskbatch='{$i}'");
                }
            } else {
                $sql[] = $DB->exec("DELETE FROM `Web_Task` WHERE id='{$id}'");
                $sql[] = $DB->exec("DELETE FROM `Web_Clocking` WHERE taskbatch='{$id}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"删除成功！"}');
            } else {
                exit('{"code":-1,"msg":"删除失败！"}');
            }
        } elseif ($type == 'AddTask' && $userrow['status'] == 1) {
            exit('{"code":-1,"msg":"暂不允许年级管理单独添加任务！"}');
            $title = daddslashes($_POST['title']);
            $addtime = strtotime(daddslashes($_POST['addtime']));
            $endtime = strtotime(daddslashes($_POST['endtime']));
            if ($title == null || $addtime == null || $endtime == null) {
                exit('{"code":-1,"msg":"输入信息不能为空！"}');
            }
            $task = $DB->query("SELECT * FROM Web_Task WHERE addtime<='{$addtime}' and endtime>='{$addtime}' or addtime<='{$endtime}' and endtime>='{$endtime}' order by id desc limit 1")->fetch();
            if ($task) {
                exit('{"code":-1,"msg":"此时间段任务已存在！"}');
            }
            $sql = $DB->exec("INSERT INTO `Web_Task` (`title`, `addtime`, `endtime`) VALUES ('{$title}', '{$addtime}', '{$endtime}')");
            $tid = ($DB->query("select LAST_INSERT_ID()")->fetch()) ['LAST_INSERT_ID()'];
            if ($sql) {
                exit('{"code":1,"msg":"添加成功！","tid":"' . $tid . '"}');
            } else {
                exit('{"code":-1,"msg":"添加失败！"}');
            }
        } elseif ($type == 'EditTask' && $userrow['status'] == 1) {
            $tid = daddslashes($_POST['tid']);
            $title = daddslashes($_POST['title']);
            $addtime = strtotime(daddslashes($_POST['addtime']));
            $endtime = strtotime(daddslashes($_POST['endtime']));
            if ($title == null || $addtime == null || $endtime == null) {
                exit('{"code":-1,"msg":"输入信息不能为空！"}');
            }
            $task = $DB->query("SELECT * FROM Web_Task WHERE id='{$tid}' limit 1")->fetch();
            if (!$task) {
                exit('{"code":-1,"msg":"此任务不存在！"}');
            } elseif ($task['status'] == 1) {
                exit('{"code":-1,"msg":"此任务为总管理所创建，您无权限操作！"}');
            }
            $sql = $DB->exec("UPDATE `Web_Task` SET `title`='{$title}', `addtime`='{$addtime}', `endtime`='{$endtime}' WHERE `id`='{$tid}'");
            if ($sql) {
                exit('{"code":1,"msg":"修改成功！"}');
            } else if ($task['title'] == $title) {
                exit('{"code":-1,"msg":"与之前相同，无需修改！"}');
            } else {
                exit('{"code":-1,"msg":"修改失败！"}');
            }
        } elseif ($type == 'ClockIn') {
            $time = time();
            $task = $DB->query("SELECT * FROM Web_Task WHERE addtime<='{$time}' and endtime>='{$time}' order by id desc limit 1")->fetch();
            if (!$task) {
                exit('{"code":-1,"msg":"本周无打卡任务！"}');
            }
            /*if (file_exists('ClockInLock.lock')) {
                exit('{"code":-1,"msg":"管理员已设为禁止打卡（现采用周日统一自动结算）！"}');
            }
            $clockin = $DB->query("SELECT * FROM Web_Clocking WHERE uid='{$userrow['id']}' and taskbatch='{$task['id']}' and status='1' limit 1")->fetch();
            if ($clockin) {
                exit('{"code":-1,"msg":"本周您已打卡！"}');
            }*/
            $data = array(
                "username" => "阆中中学",
                "password" => "6306533"
            );
            $url = "http://dxx.scyol.com/backend/adminUser/login";
            $result = login_post($url, 'test.txt', json_encode($data));
            exit($result);
        } elseif ($type == 'SetUnFinish' && $userrow['status'] == 1) {
            $cid = daddslashes($_POST['cid']);
            $status = daddslashes($_POST['status']);
            $cl = $DB->query("SELECT * FROM Web_Clocking WHERE id='{$cid}' order by id desc limit 1")->fetch();
            if (!$cl) {
                exit('{"code":-1,"msg":"此打卡情况不存在！"}');
            } elseif ($cl['status'] == 3) {
                exit('{"code":-1,"msg":"此打卡情况所属用户未打卡！"}');
            }
            $sql = $DB->exec("UPDATE `Web_Clocking` SET `status`='{$status}' WHERE `id`='{$cid}'");
            if ($sql) {
                exit('{"code":1,"msg":"标记为【谎打卡】成功！"}');
            } else if ($cl['status'] == $status) {
                exit('{"code":1,"msg":"已标记为【谎打卡】，无需重复标记！"}');
            } else {
                exit('{"code":-1,"msg":"标记为【谎打卡】失败！"}');
            }
        } elseif ($type == 'SetUnFinish' && $userrow['status'] == 2) {
            $cid = daddslashes($_POST['cid']);
            $status = daddslashes($_POST['status']);
            $cl = $DB->query("SELECT * FROM Web_Clocking WHERE id='{$cid}' order by id desc limit 1")->fetch();
            if (!$cl) {
                exit('{"code":-1,"msg":"此打卡情况不存在！"}');
            }
            if ($cl['status'] == 2) {
                exit('{"code":1,"msg":"此成员已被System检测为【谎打卡】"}');
            } else {
                exit('{"code":-1,"msg":"此成员无异常行为！"}');
            }
        } elseif ($type == 'AddNotice' && $userrow['status'] == 1) {
            $title = daddslashes($_POST['title']);
            $msg = daddslashes($_POST['msg']);
            $looktype = daddslashes($_POST['looktype']);
            $sql = $DB->exec("INSERT INTO `Web_Notice` (`title`, `msg`, `type`, `addtime`) VALUES ('{$title}', '{$msg}', '{$looktype}', '{$date}')");
            if ($sql) {
                exit('{"code":1,"msg":"添加成功！"}');
            } else {
                exit('{"code":-1,"msg":"添加失败！"}');
            }
        } elseif ($type == 'EditNotice' && $userrow['status'] == 1) {
            $id = daddslashes($_POST['id']);
            $title = daddslashes($_POST['title']);
            $msg = daddslashes($_POST['msg']);
            $looktype = daddslashes($_POST['looktype']);
            $nt = $DB->query("SELECT * FROM Web_Notice WHERE id='{$id}' order by id desc limit 1")->fetch();
            if (!$nt) {
                exit('{"code":-1,"msg":"此公告不存在！"}');
            } elseif ($nt['status'] == 1) {
                exit('{"code":-1,"msg":"此公告为总管理所发布，您无权限操作！"}');
            }
            $sql = $DB->exec("UPDATE `Web_Notice` SET `title`='{$title}', `msg`='{$msg}', `type`='{$looktype}' WHERE `id`='{$id}'");
            if ($sql) {
                $DB->exec("UPDATE `Web_Notice` SET `addtime`='{$date}', `status`='' WHERE `id`='{$id}'");
                exit('{"code":1,"msg":"修改成功！"}');
            } elseif ($title == $nt['title'] && $msg == $nt['msg'] && $looktype == $nt['type']) {
                exit('{"code":1,"msg":"与之前相同，无需修改！"}');
            } else {
                exit('{"code":-1,"msg":"修改失败！"}');
            }
        } elseif ($type == 'SetSomeNoticeLT' && $userrow['status'] == 1) {
            $id = daddslashes($_POST['id']);
            $looktype = daddslashes($_POST['looktype']);
            foreach ($id as $i) {
                $row = $DB->query("SELECT * FROM Web_Notice WHERE id='$i' limit 1")->fetch();
                if ($row['status'] == 1) {
                    continue;
                }
                $sql[] = $DB->exec("UPDATE `Web_Notice` SET `type`='{$looktype}' WHERE `id`='{$i}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"批量设置成功！"}');
            } else {
                exit('{"code":-1,"msg":"批量设置失败！"}');
            }
        } elseif ($type == 'DeleteNotice' && $userrow['status'] == 1) {
            $id = daddslashes($_POST['id']);
            $row = $DB->query("SELECT * FROM Web_Notice WHERE id='$id' limit 1")->fetch();
            if (!$row && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该公告不存在！"}');
            } elseif ($row['status'] == 1) {
                exit('{"code":-1,"msg":"此公告为总管理所发布，您无权限操作！"}');
            }
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    $row = $DB->query("SELECT * FROM Web_Notice WHERE id='$i' limit 1")->fetch();
                    if ($row['status'] == 1) {
                        continue;
                    }
                    $sql[] = $DB->exec("DELETE FROM `Web_Notice` WHERE id='{$i}'");
                    $sql[] = $DB->exec("DELETE FROM `Web_NoticeLooked` WHERE nbatch='{$i}'");
                }
            } else {
                $sql[] = $DB->exec("DELETE FROM `Web_Notice` WHERE id='{$id}'");
                $sql[] = $DB->exec("DELETE FROM `Web_NoticeLooked` WHERE nbatch='{$id}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"删除成功！"}');
            } else {
                exit('{"code":-1,"msg":"删除失败！"}');
            }
        } elseif ($type == 'ReadNotice') {
            $id = daddslashes($_POST['nid']);
            $row = $DB->query("SELECT * FROM Web_Notice WHERE id='$id' limit 1")->fetch();
            //$ss = explode(',',$row['status']);
            if (!$row) {
                exit('{"code":-1,"msg":"该公告不存在！"}');
            }
            if ($DB->query("SELECT * FROM Web_NoticeLooked WHERE nbatch='{$id}' and uid='{$userrow['id']}'")->fetch()) {
                exit('{"code":1,"msg":"之前已阅读！"}');
            }
            /*if ($row['status'] == null) {
                $status = $userrow['id'];
            } else {
                $status = $row['status'].','.$userrow['id'];
            }
            
            if (in_array($userrow['id'],$ss)) {
                exit('{"code":1,"msg":"之前已阅读！"}');
            } else {
                $sql = $DB->exec("UPDATE `Web_Notice` SET `status`='{$status}' WHERE `id`='{$id}'");
            }*/
            $sql = $DB->exec("INSERT INTO `Web_NoticeLooked` (`nbatch`, `uid`, `class`, `addtime`) VALUES ('{$id}', '{$userrow['id']}',  '{$userrow['class']}', '{$date}')");
            if ($sql) {
                exit('{"code":1,"msg":"阅读成功！"}');
            } else {
                exit('{"code":-1,"msg":"阅读失败！"}');
            }
        } elseif ($type == 'TaskSettle' && $userrow['status'] == 1) {
            $tid = daddslashes($_POST['tid']);
            $cid = daddslashes($_POST['cid']);
            $task = $DB->query("SELECT * FROM Web_Task WHERE id='{$tid}' limit 1")->fetch();
            if (!$task) {
                exit('{"code":-1,"msg":"此任务不存在！"}');
            }
            $data = array(
                "username" => "阆中中学",
                "password" => "6306533"
            );
            $url = "http://dxx.scyol.com/backend/adminUser/login";
            $result = login_post($url, 'test.txt', json_encode($data));
            $msg = json_decode($result, true) ['data'];
            if ($conf['token'] == null) {
                $token = str_replace('\\', '\\\\', json_encode($msg));
            } else {
                $data2 = array(
                    "pageNo" => "1",
                    "pageSize" => "100"
                );
                $ta = json_decode($conf['token'], true);
                $tk = $ta['token'];
                $return = json_decode(get_content('http://dxx.scyol.com/backend/stages/list', $tk, json_encode($data2)) , true);
                if (strpos($return['msg'], '未登录') !== false) {
                    $url = "http://dxx.scyol.com/backend/adminUser/login";
                    $result = login_post($url, 'test.txt', json_encode($data));
                    $ra = json_decode($result, true);
                    $rs = $ra['data'];
                    $token = str_replace('\\', '\\\\', json_encode($rs));
                } else {
                    $token = str_replace('\\', '\\\\', $conf['token']);
                }
            }
            $do = $DB->exec("UPDATE `Web_Config` SET `j`='{$token}' WHERE `x`='token'");
            if ($do || str_replace('\\', '\\\\', $conf['token']) == $token) {
                $msg = json_decode(str_replace('\\\\', '\\', $token) , true);
                $token = $msg['token'];
                $time = time();
                if ($conf['orgid'] == null) {
                    exit('{"code":-1,"msg":"本年级未设置对应组织ID，请联系总管理设置"}');
                }
                $clocking = $DB->query("SELECT * FROM Web_Clocking WHERE id='{$cid}' limit 1")->fetch();
                $user = $DB->query("SELECT * FROM Web_User WHERE id='{$clocking['uid']}'")->fetch();
                if (!$user) {
                    exit('{"code":-1,"msg":"没有此用户！"}');
                }
                if (!file_exists('./Txts/' . $conf['gradename'] . '-' . $tid . '.txt')) {
                    $data = array(
                        "orgId" => $conf['orgid'],
                        "stagesId" => $task['stid'],
                        "name" => "",
                        "tel" => "",
                        "pageNo" => "1",
                        "pageSize" => "10000"
                    );
                    $result = get_content('http://dxx.scyol.com/backend/study/student/list', $token, json_encode($data));
                    $rarr = json_decode($result, true);
                    file_put_contents('./Txts/' . $conf['gradename'] . '-' . $tid . '.txt', json_encode($rarr['data'], JSON_UNESCAPED_UNICODE));
                }
                if (ifinfinished($user['username'], $user['class'], './Txts/' . $conf['gradename'] . '-' . $tid . '.txt', $conf['gradename'] . $user['class'] . '班团支部')) {
                    if ($clocking) {
                        $sql = $DB->exec("UPDATE `Web_Clocking` SET `status`='1', `addtime`='{$date}' WHERE `id`='{$clocking['id']}'");
                    } else {
                        exit('{"code":-1,"msg":"没有此记录，请删除任务重新添加！"}');
                    }
                    if ($sql) {
                        exit('{"code":1,"msg":"此用户已完成视频学习！"}');
                    } else {
                        exit('{"code":-1,"msg":"结算失败！"}');
                    }
                } else {
                    $sql = $DB->exec("UPDATE `Web_Clocking` SET `status`='3', `addtime`='{$date}' WHERE `id`='{$clocking['id']}'");
                    exit('{"code":-1,"msg":"此用户未完成视频学习！"}');
                }
            } else {
                exit('{"code":-1,"msg":"大学习官网登录失败！"}');
            }
        } elseif ($type == 'SetFiniedFile' && $userrow['status'] == 1) {
            $tid = daddslashes($_POST['tid']);
            $task = $DB->query("SELECT * FROM Web_Task WHERE id='{$tid}' limit 1")->fetch();
            if (!$task) {
                exit('{"code":-1,"msg":"此任务不存在！"}');
            }
            $data = array(
                "username" => "阆中中学",
                "password" => "6306533"
            );
            $url = "http://dxx.scyol.com/backend/adminUser/login";
            $result = login_post($url, 'test.txt', json_encode($data));
            $msg = json_decode($result, true) ['data'];
            if ($conf['token'] == null) {
                $token = str_replace('\\', '\\\\', json_encode($msg));
            } else {
                $data2 = array(
                    "pageNo" => "1",
                    "pageSize" => "100"
                );
                $ta = json_decode($conf['token'], true);
                $tk = $ta['token'];
                $return = json_decode(get_content('http://dxx.scyol.com/backend/stages/list', $tk, json_encode($data2)) , true);
                if (strpos($return['msg'], '未登录') !== false) {
                    $url = "http://dxx.scyol.com/backend/adminUser/login";
                    $result = login_post($url, 'test.txt', json_encode($data));
                    $ra = json_decode($result, true);
                    $rs = $ra['data'];
                    $token = str_replace('\\', '\\\\', json_encode($rs));
                } else {
                    $token = str_replace('\\', '\\\\', $conf['token']);
                }
            }
            $do = $DB->exec("UPDATE `Web_Config` SET `j`='{$token}' WHERE `x`='token'");
            if ($do || str_replace('\\', '\\\\', $conf['token']) == $token) {
                $msg = json_decode(str_replace('\\\\', '\\', $token) , true);
                $token = $msg['token'];
                $data = array(
                    "orgId" => $conf['orgid'],
                    "stagesId" => $task['stid'],
                    "name" => "",
                    "tel" => "",
                    "pageNo" => "1",
                    "pageSize" => "10000"
                );
                $result = get_content('http://dxx.scyol.com/backend/study/student/list', $token, json_encode($data));
                $rarr = json_decode($result, true);
                $do = file_put_contents('./Txts/' . $conf['gradename'] . '-' . $tid . '.txt', json_encode($rarr['data'], JSON_UNESCAPED_UNICODE));
                if ($do) {
                    exit('{"code":1,"msg":"已完成视频学习成员信息获取成功！"}');
                } else {
                    exit('{"code":-1,"msg":"已完成视频学习成员信息获取失败！"}');
                }
            } else {
                exit('{"code":-1,"msg":"大学习官网登录失败！"}');
            }
        } elseif ($type == 'UnChangeClass') {
            $file = @file_get_contents('./Txts/classchanged/'.$conf['gradename'].'.txt');
            if (@in_array($userrow['id'],@json_decode($file,true))) {
                exit('{"code":-1,"msg":"请不要重复操作！"}');
            } else {
                if ($file == null) {
                    $result = json_encode([$userrow['id']]);
                } else {
                    $r = @json_decode($file,true);
                    $r[] = $userrow['id'];
                    $result = json_encode($r);
                }
                $do = file_put_contents('./Txts/classchanged/'.$conf['gradename'].'.txt',$result);
            }
            if ($do) {
                exit('{"code":1,"msg":"设置成功！"}');
            } else {
                exit('{"code":-1,"msg":"设置失败！"}');
            }
        } elseif ($type == 'ChangeClass') {
            $cid = daddslashes($_POST['classid']);
            if (!is_numeric($cid)) {
                exit('{"code":-1,"msg":"班级号必须为数字！"}');
            }
            $file = @file_get_contents('./Txts/classchanged/'.$conf['gradename'].'.txt');
            if (@in_array($userrow['id'],@json_decode($file,true))) {
                exit('{"code":-1,"msg":"请不要重复操作！"}');
            } else {
                if (!$DB->query("SELECT * FROM Web_Class WHERE name={$cid}")->fetch()) {
                    exit('{"code":-1,"msg":"没有此班级！"}');
                } else {
                    $sql = $DB->exec("UPDATE `Web_User` SET `class`='{$cid}' WHERE `id`='{$userrow['id']}'");
                }
                if ($file == null) {
                    $result = json_encode([$userrow['id']]);
                } else {
                    $r = @json_decode($file,true);
                    $r[] = $userrow['id'];
                    $result = json_encode($r);
                }
                $do = file_put_contents('./Txts/classchanged/'.$conf['gradename'].'.txt',$result);
            }
            if ($do) {
                if ($sql) {
                    exit('{"code":1,"msg":"设置成功！"}');
                } elseif ($cid == $userrow['class']) {
                    exit('{"code":1,"msg":"设置成功！"}');
                } else {
                    exit('{"code":-1,"msg":"错误！"}');
                }
            } else {
                exit('{"code":-1,"msg":"设置失败！"}');
            }
        } elseif ($type == 'ListChangeClass' && $userrow['status'] == 1) {
            $file = @file_get_contents('./Txts/classchanged/'.$conf['gradename'].'.txt');
            
            $men = str_replace("\r","",daddslashes($_POST['men']));
            $errornum = 0;
            $i = 0;
            foreach (explode("\n",$men) as $m) {
                $i++;
                setcookie("ListChangeClassDoing", '0');
                
                $arr = explode('-',$m);
                $uclass = $arr[0];
                $uname = $arr[1];
                
                $row = $DB->query("SELECT * FROM Web_Class WHERE id='{$uclass}' limit 1")->fetch();
                if (!$row) {
                    $errornum++;
                    $errormsg .= $uclass.'班'.$uname.'<br>';
                    continue;
                }
                $user = $DB->query("SELECT * FROM Web_User WHERE username='{$uname}' limit 1")->fetch();
                if (!$user) {
                    $p = md5(123456);
                    $uskey = str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
                    $sql = $DB->exec("INSERT INTO `Web_User` (`username`, `password`, `class`, `addtime`, `uskey`) VALUES ('{$uname}', '{$p}', '{$uclass}', '{$date}', '{$uskey}')");
                    //$errornum++;
                    //$errormsg .= $uclass.'班'.$uname.'<br>';
                    continue;
                }
                
                if ($DB->exec("UPDATE `Web_User` SET `class`='{$uclass}' WHERE `id`='{$user['id']}'")) {
                    $DB->exec("UPDATE `Web_Clocking` SET `class`='{$uclass}' WHERE `uid`='{$user['id']}'");
                } else {
                    if ($user['class'] == $uclass) {} else {
                        $errornum++;
                        $errormsg .= $uclass.'班'.$uname.'<br>';
                        continue;
                    }
                }
                
                if ($file == null) {
                    $result = json_encode([$user['id']]);
                } else {
                    $r = @json_decode($file,true);
                    $r[] = $user['id'];
                    $result = json_encode($r);
                }
                $do = file_put_contents('./Txts/classchanged/'.$conf['gradename'].'.txt',$result);
                setcookie("ListChangeClassDoing", $i);
            }
            
            if ($errornum == 0) {
                exit('{"code":1,"msg":"处理成功！"}');
            } else {
                exit(json_encode(array("code"=>-1,"msg"=>'部分处理成功！错误次数：<b style=color:red>'.$errornum.'</b>次<hr>错误名单<br>'.$errormsg)));
            }
        } else {
            exit('{"code":-4,"msg":"No Type or No Permission"}');
        }
        break;
        
    case 'getCookie':
        exit($_COOKIE[daddslashes($_POST['name'])]);
        break;

    case 'GetTasksInfo':
        $tid = daddslashes($_POST['tid']);
        $tasks = $DB->query("SELECT * FROM Web_Task WHERE id='{$tid}' ORDER BY id")->fetchAll();
        if ($tasks) {
            echo '[';
            if ($userrow['status'] == 1) {
                $classes = $DB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll();
            } else {
                $classes = $DB->query("SELECT * FROM Web_Class WHERE name='{$userrow['class']}' ORDER BY name")->fetchAll();
            }
            foreach ($tasks as $t) {
                $i = 0;
                echo '{"name": "' . $t['title'] . ' 【已完成】","areaStyle": {"normal": {"color": "#0096888c"}},"itemStyle": {"normal": {"color": "#009688","lineStyle": {"color": "#009688"}}},"smooth": "true","type": "' . ($userrow['status'] == 1 ? 'line' : 'bar') . '","data": [';
                foreach ($classes as $cs) {
                    $clcs = $DB->query("SELECT * FROM Web_Clocking WHERE class='{$cs['name']}' and taskbatch='{$t['id']}' and status!='3' ORDER BY id")->fetchAll();
                    $hcs = $DB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$t['id']}' and class='{$cs['name']}' and status='2'")->fetchAll();
                    if (time() >= $t['endtime']) {
                        $hcounts = @count($hcs);
                        $clcscount = @count($clcs) - $hcounts;
                    } else {
                        $clcscount = @count($clcs);
                    }
                    echo $clcscount;
                    $i++;
                    if ($i < count($classes)) {
                        echo ',';
                    }
                }
                echo ']},';
                echo '{"name": "' . $t['title'] . ' 【未完成】","areaStyle": {"normal": {"color": "#cc00018c"}},"itemStyle": {"normal": {"color": "#CC0001","lineStyle": {"color": "#CC0001"}}},"smooth": "true","type": "' . ($userrow['status'] == 1 ? 'line' : 'bar') . '","data": [';
                $i = 0;
                foreach ($classes as $cs) {
                    $clcounts = $DB->query("SELECT count(*) FROM Web_User WHERE class='{$cs['name']}'")->fetchColumn();
                    $clcs = $DB->query("SELECT count(*) FROM Web_Clocking WHERE class='{$cs['name']}' and taskbatch='{$t['id']}' and status!='3'")->fetchColumn();
                    echo $clcounts - $clcs;
                    $i++;
                    if ($i < count($classes)) {
                        echo ',';
                    }
                }
                echo ']}';
                //if (time() >= $t['endtime']) {
                if (false) {
                    echo ',';
                    echo '{"name": "' . $t['title'] . ' 【谎打卡】","areaStyle": {"normal": {"color": "#FF9B028c"}},"itemStyle": {"normal": {"color": "#FF9B02","lineStyle": {"color": "#FF9B02"}}},"smooth": "true","type": "' . ($userrow['status'] == 1 ? 'line' : 'bar') . '","data": [';
                    $i = 0;
                    foreach ($classes as $cs) {
                        $clcounts = $DB->query("SELECT count(*) FROM Web_User WHERE class='{$cs['name']}'")->fetchColumn();
                        $clcs = $DB->query("SELECT count(*) FROM Web_Clocking WHERE class='{$cs['name']}' and taskbatch='{$t['id']}' and status!='3'")->fetchColumn();
                        $hcs = $DB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$t['id']}' and class='{$cs['name']}' and status='2'")->fetchAll();
                        $hcounts = @count($hcs);
                        echo $hcounts;
                        $i++;
                        if ($i < count($classes)) {
                            echo ',';
                        }
                    }
                    echo ']}';
                }
                /*if (($DB->query("SELECT * FROM Web_Task ORDER BY id DESC LIMIT 1")->fetch())['id'] != $t['id']) {
                    echo ',';
                }*/
            }
            echo ']';
        } else {
            exit('{"code":-1,"msg":"没有打卡任务，请添加！"}');
        }
        break;

    case 'AdminLogin':
        $username = daddslashes($_POST['username']);
        $pwd = daddslashes($_POST['password']);
        $password = md5($pwd);
        if (preg_match('/select|insert|update|CR|document|LF|eval|delete|script|alert|\'|\/\*|\#|\--|\ --|\/|\*|\-|\+|\=|\~|\*@|\*!|\$|\%|\^|\&|\(|\)|\/|\/\/|\.\.\/|\.\/|union|into|load_file|outfile/', $password)) {
            exit('{"code":-1,"msg":"密码不能包含特殊字符！"}');
        }
        $adminrow = $DB->query("SELECT * FROM Web_Admin WHERE username='{$username}' limit 1")->fetch();
        if (!$adminrow) {
            exit('{"code":-1,"msg":"没有此管理员，请联系网站管理添加！"}');
        }
        if ($username == $adminrow['username'] && $password == $adminrow['password']) {
            //$city=get_ip_city($clientip);
            $session = md5($username . $password . $password_hash);
            $expiretime = time() + 604800;
            $token = @authcode("{$username}\t{$session}\t{$expiretime}", 'ENCODE', SYS_KEY);
            setcookie("admin_token", $token, time() + 604800);
            exit('{"code":1,"msg":"账户登录成功!"}');
        } elseif ($password != $adminrow['password']) {
            exit('{"code":-1,"msg":"账户密码输入错误！"}');
        }
        break;

    case 'AdminLogout':
        $result = setcookie("admin_token", "", time() - 604800);
        if ($result) {
            exit('{"code":1,"msg":"账户注销成功！跳转中..."}');
        } else {
            exit('{"code":-1,"msg":"账户注销失败！"}');
        }
        break;

    case 'SetUnClockIn':
        $tid = daddslashes($_POST['tid']);
        $t = $DB->query("SELECT * FROM Web_Task WHERE id='$tid' limit 1")->fetch();
        if (!$t) {
            exit('{"code":-1,"msg":"该任务不存在！"}');
        }
        foreach ($DB->query("SELECT * FROM Web_User WHERE username!='Administrator'")->fetchAll() as $us) {
            if ($DB->query("SELECT * FROM Web_Clocking WHERE uid='{$us['id']}' LIMIT 1")->fetch()) {
            } else {
                $task = $DB->query("SELECT * FROM Web_Task WHERE id='{$tid}'")->fetch();
                $user = $DB->query("SELECT * FROM Web_User WHERE id='{$us['id']}' limit 1")->fetch();
                $sql[] = $DB->exec("INSERT INTO `Web_Clocking` (`uid`, `addtime`, `taskbatch`, `class`, `status`) VALUES ('{$user['id']}', '{$date}', '{$task['id']}', '{$user['class']}', '3')");
            }
        }
        if (!empty($sql)) {
            exit('{"code":1,"msg":"设置成功！"}');
        } else {
            exit('{"code":-1,"msg":"设置失败！"}');
        }
        break;

    case 'AdminSetUnClockIn':
        $gid = daddslashes($_POST['gid']);
        $tid = daddslashes($_POST['tid']);
        $row = $DB->query("SELECT * FROM Web_Grade WHERE id='$gid' limit 1")->fetch();
        if (!$row) {
            exit('{"code":-1,"msg":"该年级不存在！"}');
        }
        $t = $DB->query("SELECT * FROM Web_Task WHERE gbatch='$tid' limit 1")->fetch();
        if (!$t) {
            exit('{"code":-1,"msg":"该任务不存在！' . $tid . '"}');
        }
        //连接数据库
        $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$row['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
        foreach ($thisDB->query("SELECT * FROM Web_User WHERE username!='Administrator'")->fetchAll() as $us) {
            $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}'")->fetch();
            $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$us['id']}' limit 1")->fetch();
            if ($thisDB->query("SELECT * FROM Web_Clocking WHERE uid='{$us['id']}' and taskbatch='{$task['id']}' LIMIT 1")->fetch()) {
            } else {
                $sql[] = $thisDB->exec("INSERT INTO `Web_Clocking` (`uid`, `addtime`, `taskbatch`, `class`, `status`) VALUES ('{$user['id']}', '{$date}', '{$task['id']}', '{$user['class']}', '3')");
            }
        }
        if (!empty($sql)) {
            exit('{"code":1,"msg":"设置成功！"}');
        } else {
            exit('{"code":-1,"msg":"设置失败！"}');
        }
        break;

    case 'AdminIndexInfo':
        if ($islogin != 1) {
            exit('{"code":-4,"msg":"未登录！"}');
        }
        $type = $_GET['Type'];
        if ($type == 'AddClass') {
            $gid = daddslashes($_POST['gid']);
            $classval = daddslashes($_POST['classval']);
            if (!is_numeric($classval)) {
                exit('{"code":-1,"msg":"输入信息只能为数字！"}');
            }
            $row = $DB->query("SELECT * FROM Web_Grade WHERE id='{$gid}' limit 1")->fetch();
            if (!$row) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$row['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $row = $thisDB->query("SELECT * FROM Web_Class WHERE name='$classval' limit 1")->fetch();
            if ($row) {
                exit('{"code":-1,"msg":"该班级已存在！"}');
            }
            $sql = $thisDB->exec("INSERT INTO `Web_Class` (`name`, `addtime`) VALUES ('{$classval}', '{$date}')");
            $cid = ($thisDB->query("select LAST_INSERT_ID()")->fetch()) ['LAST_INSERT_ID()'];
            if ($sql) {
                exit('{"code":1,"msg":"添加成功！","cid":"' . $cid . '"}');
            } else {
                exit('{"code":-1,"msg":"添加失败！"}');
            }
        } elseif ($type == 'DelClass') {
            $gid = daddslashes($_POST['gid']);
            $row = $DB->query("SELECT * FROM Web_Grade WHERE id='{$gid}' limit 1")->fetch();
            if (!$row) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$row['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $id = daddslashes($_POST['id']);
            $row = $thisDB->query("SELECT * FROM Web_Class WHERE id='$id' limit 1")->fetch();
            if (!$row && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    $r = $thisDB->query("SELECT * FROM Web_Class WHERE id='$i' limit 1")->fetch();
                    foreach ($thisDB->query("SELECT * FROM Web_User WHERE class='{$r['name']}' limit 1")->fetchAll() as $u) {
                        $sql[] = $thisDB->exec("DELETE FROM `Web_NoticeLooked` WHERE uid='{$u['id']}'");
                    }
                    $sql[] = $thisDB->exec("DELETE FROM `Web_Class` WHERE id='{$i}'");
                    $sql[] = $thisDB->exec("DELETE FROM `Web_User` WHERE class='{$i}'");
                    $sql[] = $thisDB->exec("DELETE FROM `Web_Clocking` WHERE class='{$i}'");
                }
            } else {
                foreach ($thisDB->query("SELECT * FROM Web_User WHERE class='{$row['name']}' limit 1")->fetchAll() as $u) {
                    $sql[] = $thisDB->exec("DELETE FROM `Web_NoticeLooked` WHERE uid='{$u['id']}'");
                }
                $sql[] = $thisDB->exec("DELETE FROM `Web_Class` WHERE id='{$id}'");
                $sql[] = $thisDB->exec("DELETE FROM `Web_User` WHERE class='{$id}'");
                $sql[] = $thisDB->exec("DELETE FROM `Web_Clocking` WHERE class='{$id}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"删除成功！"}');
            } else {
                exit('{"code":-1,"msg":"删除失败！"}');
            }
        } elseif ($type == 'EditClass') {
            $gid = daddslashes($_POST['gid']);
            $row = $DB->query("SELECT * FROM Web_Grade WHERE id='{$gid}' limit 1")->fetch();
            if (!$row) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$row['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $id = daddslashes($_POST['id']);
            $classval = daddslashes($_POST['classval']);
            if (!is_numeric($classval)) {
                exit('{"code":-1,"msg":"输入信息只能为数字！"}');
            }
            $row = $thisDB->query("SELECT * FROM Web_Class WHERE id='$id' limit 1")->fetch();
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            $sql[] = $thisDB->exec("UPDATE `Web_User` SET `class`='{$classval}' WHERE `class`='{$row['name']}'");
            $sql[] = $thisDB->exec("UPDATE `Web_Class` SET `name`='{$classval}' WHERE `id`='{$id}'");
            if (!empty($sql)) {
                exit('{"code":1,"msg":"修改成功！"}');
            } elseif ($classval == $row['name']) {
                exit('{"code":1,"msg":"与之前相同，无需修改！"}');
            } else {
                exit('{"code":-1,"msg":"修改失败！"}');
            }
        } elseif ($type == 'EditPassword') {
            $password = md5(daddslashes($_POST['password']));
            if ($password == md5(123456) || $password == md5(123456789) || $password == md5(123123)) {
                exit('{"code":-1,"msg":"密码过于简单，请重新修改！"}');
            }
            $sql = $DB->exec("UPDATE `Web_Admin` SET `password`='{$password}' WHERE `id`='{$adminrow['id']}'");
            if ($sql) {
                $result = setcookie("admin_token", "", time() - 604800);
                exit('{"code":1,"msg":"修改成功！"}');
            } elseif ($password == $adminrow['password']) {
                exit('{"code":-1,"msg":"与之前相同，无需修改！"}');
            } else {
                exit('{"code":-1,"msg":"修改失败！"}');
            }
        } elseif ($type == 'AddGrade') {
            $name = daddslashes($_POST['name']);
            $gid = daddslashes($_POST['gid']);
            $oid = daddslashes($_POST['oid']);
            $zzif = '/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\（|\）|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\||\s+/';
            if (preg_match($zzif, $gid)) {
                exit('{"code":-1,"msg":"年级号不能含有特殊字符！"}');
            }
            $row = $DB->query("SELECT * FROM Web_Grade WHERE name='$name' or gradeid='$gid' limit 1")->fetch();
            if ($row) {
                exit('{"code":-1,"msg":"该年级已存在，或者请换一个年级值！"}');
            }
            $databasename = $gid . '_l_ci_kayanxin_cn';
            $sql = $DB->exec("INSERT INTO `Web_Grade` (`name`, `gradeid`, `orgid`, `databasename`, `addtime`) VALUES ('{$name}', '{$gid}', '{$oid}', '{$databasename}', '{$date}')");
            $gid = ($DB->query("select LAST_INSERT_ID()")->fetch()) ['LAST_INSERT_ID()'];
            if ($sql) {
                $RDB->exec("CREATE DATABASE `{$databasename}`");
                //连接数据库
                $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$databasename};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                //获取sql命令文件
                $sqlsfile = file_get_contents('./Admin/Includes/NewSql.sql');
                //进行sql分割执行
                foreach (explode('--------------------', $sqlsfile) as $s) {
                    $thisDB->exec($s);
                }
                $thisDB->exec("INSERT INTO `Web_Config` (`x`, `j`) VALUES ('gradename', '{$name}')");
                $thisDB->exec("INSERT INTO `Web_Config` (`x`, `j`) VALUES ('orgid', '{$oid}')");
                $thisDB->exec("INSERT INTO `Web_Config` (`x`, `j`) VALUES ('token', '')");
                $thisDB->exec("INSERT INTO `Web_User` (`id`, `username`, `password`, `class`, `addtime`, `status`) VALUES (NULL, 'Administrator', '25f9e794323b453885f5181f1b624d0b', '', '', '1')");
                //$thisDB->exec("ALTER TABLE `Web_User` ADD `token` TEXT NOT NULL AFTER `uskey`");
                exit('{"code":1,"msg":"添加成功！年级管理员账号Administrator，密码为123456【请及时修改】","gid":"' . $gid . '"}');
            } else {
                exit('{"code":-1,"msg":"添加失败！"}');
            }
        } elseif ($type == 'EditGrade') {
            $id = daddslashes($_POST['id']);
            $gradename = daddslashes($_POST['gradename']);
            $name = daddslashes($_POST['name']);
            $gid = daddslashes($_POST['gid']);
            $oid = daddslashes($_POST['oid']);
            $zzif = '/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\（|\）|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\||\s+/';
            if (preg_match($zzif, $gid)) {
                exit('{"code":-1,"msg":"年级号不能含有特殊字符！"}');
            }
            $row = $DB->query("SELECT * FROM Web_Grade WHERE id='{$id}' limit 1")->fetch();
            if (!$row) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            $databasename = $row['gradeid'] . '_l_ci_kayanxin_cn';
            $newdatabasename = $gid . '_l_ci_kayanxin_cn';
            $sql = $DB->exec("UPDATE `Web_Grade` SET `name`='{$name}', `gradeid`='{$gid}', `orgid`='{$oid}', `databasename`='{$newdatabasename}' WHERE `id`='{$id}'");
            if ($sql) {
                @file_put_contents('./Txts/'.$oid.'.txt',$gradename);
                if ($row['gradeid'] == $gid) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$newdatabasename};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    //执行改名命令
                    $thisDB->exec("UPDATE `Web_Config` SET `j`='{$name}' WHERE `x`='gradename'");
                    //执行改编号命令
                    $thisDB->exec("UPDATE `Web_Config` SET `j`='{$oid}' WHERE `x`='orgid'");
                } else {
                    //创建新数据库
                    $RDB->exec("CREATE DATABASE `{$newdatabasename}`");
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$newdatabasename};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    //获取sql命令文件
                    $sqlsfile = file_get_contents('./Admin/Includes/NewSql.sql');
                    //进行sql分割执行
                    foreach (explode('--------------------', $sqlsfile) as $s) {
                        $thisDB->exec($s);
                    }
                    //将旧数据库内数据转入新数据库内
                    foreach ($RDB->query("SHOW TABLES FROM `{$databasename}`") as $table) {
                        $tablename = $table[0];
                        $RDB->exec("INSERT INTO {$newdatabasename}.{$tablename} SELECT * FROM {$databasename}.{$tablename}");
                    }
                    //删除旧数据库
                    $RDB->exec("DROP DATABASE `{$databasename}`");
                    //连接数据库
                    $thatDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$newdatabasename};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $thatDB->exec("INSERT INTO `Web_Config` (`x`, `j`) VALUES ('gradename', '{$name}')");
                    $thatDB->exec("INSERT INTO `Web_Config` (`x`, `j`) VALUES ('orgid', '{$oid}')");
                    $thisDB->exec("INSERT INTO `Web_Config` (`x`, `j`) VALUES ('token', '')");
                }
                exit('{"code":1,"msg":"修改成功！","gid":"' . $gid . '"}');
            } elseif ($row['name'] == $name) {
                file_put_contents('./Txts/'.$row['orgid'].'.txt',$gradename);
                exit('{"code":1,"msg":"与之前相同，无需重置！"}');
            } else {
                exit('{"code":-1,"msg":"修改失败！"}');
            }
        } elseif ($type == 'DelGrade') {
            $id = daddslashes($_POST['id']);
            $row = $DB->query("SELECT * FROM Web_Grade WHERE id='$id' limit 1")->fetch();
            if (!$row && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    $r = $DB->query("SELECT * FROM Web_Grade WHERE id='$i' limit 1")->fetch();
                    $sql[] = $DB->exec("DELETE FROM `Web_Grade` WHERE id='{$i}'");
                    $RDB->exec("DROP DATABASE `{$r['databasename']}`");
                }
            } else {
                $sql[] = $DB->exec("DELETE FROM `Web_Grade` WHERE id='{$id}'");
                $RDB->exec("DROP DATABASE `{$row['databasename']}`");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"删除成功！' . $row['databasename'] . '"}');
            } else {
                exit('{"code":-1,"msg":"删除失败！"}');
            }
        } elseif ($type == 'SetAdminPassword') {
            $id = daddslashes($_POST['id']);
            $row = $DB->query("SELECT * FROM Web_Grade WHERE id='$id' limit 1")->fetch();
            if (!$row && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            $p = md5(123456789);
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    $r = $DB->query("SELECT * FROM Web_Grade WHERE id='$i' limit 1")->fetch();
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$r['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $sql[] = $thisDB->exec("UPDATE `Web_User` SET `password`='{$p}' WHERE `id`='1'");
                }
            } else {
                //连接数据库
                $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$row['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                $admin = $thisDB->query("SELECT * FROM Web_User WHERE id='1' limit 1")->fetch();
                $sql[] = $thisDB->exec("UPDATE `Web_User` SET `password`='{$p}' WHERE `id`='1'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"重置成功！"}');
            } elseif ($admin['password'] == $p) {
                exit('{"code":1,"msg":"与之前相同，无需重置！"}');
            } else {
                exit('{"code":-1,"msg":"重置失败！"}');
            }
        } elseif ($type == 'AddTask') {
            $title = daddslashes($_POST['title']);
            $stid = daddslashes($_POST['stid']);
            $addtime = strtotime(daddslashes($_POST['addtime']));
            $endtime = strtotime(daddslashes($_POST['endtime']));
            if ($title == null || $addtime == null || $endtime == null) {
                exit('{"code":-1,"msg":"输入信息不能为空！"}');
            }
            $task = $DB->query("SELECT * FROM Web_Task WHERE addtime<='{$addtime}' and endtime>='{$addtime}' or addtime<='{$endtime}' and endtime>='{$endtime}' order by id desc limit 1")->fetch();
            if ($task) {
                exit('{"code":-1,"msg":"此时间段任务已存在！"}');
            }
            $gbatch = 'TK-' . rand(000000, 999999);
            $sql = $DB->exec("INSERT INTO `Web_Task` (`title`, `stid`, `gbatch`, `addtime`, `endtime`, `status`) VALUES ('{$title}', '{$stid}', '{$gbatch}', '{$addtime}', '{$endtime}', '1')");
            $tid = ($DB->query("select LAST_INSERT_ID()")->fetch()) ['LAST_INSERT_ID()'];
            if ($sql) {
                //为旗下年级添加任务
                foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $thisDB->exec("INSERT INTO `Web_Task` (`title`, `stid`, `gbatch`, `addtime`, `endtime`, `status`) VALUES ('{$title}', '{$stid}', '{$gbatch}', '{$addtime}', '{$endtime}', '1')");
                    $tid = ($thisDB->query("select LAST_INSERT_ID()")->fetch()) ['LAST_INSERT_ID()'];
                    //导入成员进打卡表
                    foreach ($thisDB->query("SELECT * FROM Web_Task WHERE id='{$tid}'")->fetchAll() as $task) {
                        /*if ($thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='3'")->fetchColumn() != 0) {
                            continue;
                        }*/
                        foreach ($thisDB->query("SELECT * FROM Web_User WHERE username!='Administrator'")->fetchAll() as $us) {
                            /*if ($thisDB->query("SELECT * FROM Web_Clocking WHERE uid='{$us['id']}' and taskbatch='{$tid}' LIMIT 1")->fetch()) {
                            } else {
                                $sql[] = $thisDB->exec("INSERT INTO `Web_Clocking` (`uid`, `addtime`, `taskbatch`, `class`, `status`) VALUES ('{$user['id']}', '{$date}', '{$tid}', '{$user['class']}', '0')");
                            }*/
                            $thisDB->exec("INSERT INTO `Web_Clocking` (`uid`, `addtime`, `taskbatch`, `class`, `status`) VALUES ('{$us['id']}', '{$date}', '{$tid}', '{$us['class']}', '3')");
                        }
                    }
                }
                exit('{"code":1,"msg":"添加成功！","tid":"' . $tid . '"}');
            } else {
                exit('{"code":-1,"msg":"添加失败！"}');
            }
        } elseif ($type == 'EditTask') {
            $tid = daddslashes($_POST['tid']);
            $title = daddslashes($_POST['title']);
            $stid = daddslashes($_POST['stid']);
            $addtime = strtotime(daddslashes($_POST['addtime']));
            $endtime = strtotime(daddslashes($_POST['endtime']));
            if ($title == null || $addtime == null || $endtime == null) {
                exit('{"code":-1,"msg":"输入信息不能为空！"}');
            }
            $task = $DB->query("SELECT * FROM Web_Task WHERE id='{$tid}' limit 1")->fetch();
            if (!$task) {
                exit('{"code":-1,"msg":"此任务不存在！"}');
            }
            $sql = $DB->exec("UPDATE `Web_Task` SET `title`='{$title}', `stid`='{$stid}', `addtime`='{$addtime}', `endtime`='{$endtime}', `status`='1' WHERE `id`='{$tid}'");
            if ($sql) {
                //为旗下年级修改任务
                foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $thisDB->exec("UPDATE `Web_Task` SET `title`='{$title}', `stid`='{$stid}', `addtime`='{$addtime}', `endtime`='{$endtime}' WHERE `gbatch`='{$task['gbatch']}'");
                    //如没有任务就创建任务
                    if (!$thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$task['gbatch']}'")->fetch()) {
                        $thisDB->exec("INSERT INTO `Web_Task` (`title`, `stid`, `gbatch`, `addtime`, `endtime`, `status`) VALUES ('{$title}', '{$stid}', '{$task['gbatch']}', '{$addtime}', '{$endtime}', '1')");
                    }
                }
                exit('{"code":1,"msg":"修改成功！","tid":"' . $tid . '"}');
            } else if ($task['title'] == $title) {
                //为旗下年级修改任务
                foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $thisDB->exec("UPDATE `Web_Task` SET `title`='{$title}', `addtime`='{$addtime}', `endtime`='{$endtime}' WHERE `gbatch`='{$task['gbatch']}'");
                    //如没有任务就创建任务
                    if (!$thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$task['gbatch']}'")->fetch()) {
                        $thisDB->exec("INSERT INTO `Web_Task` (`title`, `gbatch`, `addtime`, `endtime`, `status`) VALUES ('{$title}', '{$task['gbatch']}', '{$addtime}', '{$endtime}', '1')");
                    }
                }
                exit('{"code":-1,"msg":"与之前相同，无需修改！"}');
            } else {
                exit('{"code":-1,"msg":"修改失败！"}');
            }
        } elseif ($type == 'DeleteTask') {
            $id = daddslashes($_POST['id']);
            $row = $DB->query("SELECT * FROM Web_Task WHERE id='$id' limit 1")->fetch();
            if (!$row && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该任务不存在！"}');
            }
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    //为旗下年级删除任务
                    foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                        $r = $DB->query("SELECT * FROM Web_Task WHERE id='$i' limit 1")->fetch();
                        //连接数据库
                        $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                        $task = @$thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$r['gbatch']}' limit 1")->fetch();
                        @$thisDB->exec("DELETE FROM `Web_Task` WHERE gbatch='{$r['gbatch']}'");
                        @$thisDB->exec("DELETE FROM `Web_Clocking` WHERE taskbatch='{$task['id']}'");
                    }
                    $sql[] = $DB->exec("DELETE FROM `Web_Task` WHERE id='{$i}'");
                }
            } else {
                //为旗下年级删除任务
                foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $task = @$thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$row['gbatch']}' limit 1")->fetch();
                    @$thisDB->exec("DELETE FROM `Web_Task` WHERE gbatch='{$row['gbatch']}'");
                    @$thisDB->exec("DELETE FROM `Web_Clocking` WHERE taskbatch='{$task['id']}'");
                }
                $sql[] = $DB->exec("DELETE FROM `Web_Task` WHERE id='{$id}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"删除成功！"}');
            } else {
                exit('{"code":-1,"msg":"删除失败！"}');
            }
        } elseif ($type == 'SetUnFinish') {
            $cid = daddslashes($_POST['cid']);
            $gid = daddslashes($_POST['gid']);
            $status = daddslashes($_POST['status']);
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE id='{$gid}' limit 1")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！' . $gid . '"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $cl = $thisDB->query("SELECT * FROM Web_Clocking WHERE id='{$cid}' order by id desc limit 1")->fetch();
            if (!$cl) {
                exit('{"code":-1,"msg":"此打卡情况不存在！"}');
            } elseif ($cl['status'] == 3) {
                exit('{"code":-1,"msg":"此打卡情况所属用户未打卡！"}');
            }
            $sql = $thisDB->exec("UPDATE `Web_Clocking` SET `status`='{$status}' WHERE `id`='{$cid}'");
            if ($sql) {
                exit('{"code":1,"msg":"标记为【谎打卡】成功！"}');
            } else if ($cl['status'] == $status) {
                exit('{"code":1,"msg":"已标记为【谎打卡】，无需重复标记！"}');
            } else {
                exit('{"code":-1,"msg":"标记为【谎打卡】失败！"}');
            }
        } elseif ($type == 'AddNotice') {
            $title = daddslashes($_POST['title']);
            $msg = daddslashes($_POST['msg']);
            $looktype = daddslashes($_POST['looktype']);
            $gbatch = 'NE-' . rand(000000, 999999);
            $sql = $DB->exec("INSERT INTO `Web_Notice` (`title`, `gbatch`, `msg`, `type`, `addtime`, `status`) VALUES ('{$title}', '{$gbatch}', '{$msg}', '{$looktype}', '{$date}', '1')");
            if ($sql) {
                //为旗下年级添加公告
                foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $thisDB->exec("INSERT INTO `Web_Notice` (`title`, `gbatch`, `msg`, `type`, `addtime`, `status`) VALUES ('{$title}', '{$gbatch}', '{$msg}', '{$looktype}', '{$date}', '1')");
                }
                exit('{"code":1,"msg":"添加成功！"}');
            } else {
                exit('{"code":-1,"msg":"添加失败！"}');
            }
        } elseif ($type == 'EditNotice') {
            $id = daddslashes($_POST['id']);
            $title = daddslashes($_POST['title']);
            $msg = daddslashes($_POST['msg']);
            $looktype = daddslashes($_POST['looktype']);
            $nt = $DB->query("SELECT * FROM Web_Notice WHERE id='{$id}' order by id desc limit 1")->fetch();
            if (!$nt) {
                exit('{"code":-1,"msg":"此公告不存在！"}');
            }
            $sql = $DB->exec("UPDATE `Web_Notice` SET `title`='{$title}', `msg`='{$msg}', `type`='{$looktype}', `addtime`='{$date}' WHERE `id`='{$id}'");
            if ($sql) {
                //为旗下年级添加公告
                foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $thisDB->exec("UPDATE `Web_Notice` SET `title`='{$title}', `msg`='{$msg}', `type`='{$looktype}', `addtime`='{$date}' WHERE `gbatch`='{$nt['gbatch']}'");
                    //如没有公告就创建公告
                    if (!$thisDB->query("SELECT * FROM Web_Notice WHERE gbatch='{$nt['gbatch']}'")->fetch()) {
                        $thisDB->exec("INSERT INTO `Web_Notice` (`title`, `gbatch`, `msg`, `type`, `addtime`, `status`) VALUES ('{$title}', '{$nt['gbatch']}', '{$msg}', '{$looktype}', '{$date}', '1')");
                    }
                }
                exit('{"code":1,"msg":"修改成功！"}');
            } elseif ($title == $nt['title'] && $msg == $nt['msg'] && $looktype == $nt['type']) {
                //为旗下年级修改公告
                foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $thisDB->exec("UPDATE `Web_Notice` SET `title`='{$title}', `msg`='{$msg}', `type`='{$looktype}', `addtime`='{$date}' WHERE `gbatch`='{$nt['gbatch']}'");
                    //如没有公告就创建公告
                    if (!$thisDB->query("SELECT * FROM Web_Notice WHERE gbatch='{$nt['gbatch']}'")->fetch()) {
                        $thisDB->exec("INSERT INTO `Web_Notice` (`title`, `gbatch`, `msg`, `type`, `addtime`, `status`) VALUES ('{$title}', '{$nt['gbatch']}', '{$msg}', '{$looktype}', '{$date}', '1')");
                    }
                }
                exit('{"code":1,"msg":"与之前相同，无需修改！"}');
            } else {
                exit('{"code":-1,"msg":"修改失败！"}');
            }
        } elseif ($type == 'SetSomeNoticeLT') {
            $id = array_reverse(daddslashes($_POST['id']));
            $looktype = daddslashes($_POST['looktype']);
            foreach ($id as $i) {
                $row = $DB->query("SELECT * FROM Web_Notice WHERE id='$i' limit 1")->fetch();
                //为旗下年级修改公告
                foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $r = $thisDB->query("SELECT * FROM Web_Notice WHERE gbatch='{$row['gbatch']}' limit 1")->fetch();
                    if (!$r) { //如没有公告就创建公告
                        $sql[] = $thisDB->exec("INSERT INTO `Web_Notice` (`title`, `gbatch`, `msg`, `type`, `addtime`, `status`) VALUES ('{$row['title']}', '{$row['gbatch']}', '{$row['msg']}', '{$looktype}', '{$date}', '1')");
                    } else {
                        $sql[] = $thisDB->exec("UPDATE `Web_Notice` SET `type`='{$looktype}', `addtime`='{$date}' WHERE `id`='{$r['id']}'");
                    }
                }
                $sql[] = $DB->exec("UPDATE `Web_Notice` SET `type`='{$looktype}', `addtime`='{$date}' WHERE `id`='{$i}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"批量设置成功！"}');
            } else {
                exit('{"code":-1,"msg":"批量设置失败！"}');
            }
        } elseif ($type == 'DeleteNotice') {
            $id = daddslashes($_POST['id']);
            $row = $DB->query("SELECT * FROM Web_Notice WHERE id='$id' limit 1")->fetch();
            if (!$row && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该公告不存在！"}');
            }
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    $r = $DB->query("SELECT * FROM Web_Notice WHERE id='$i' limit 1")->fetch();
                    //为旗下年级删除公告
                    foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                        //连接数据库
                        $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                        $nt = $thisDB->query("SELECT * FROM Web_Notice WHERE gbatch='{$r['gbatch']}' limit 1")->fetch();
                        $thisDB->exec("DELETE FROM `Web_Notice` WHERE gbatch='{$r['gbatch']}'");
                        $thisDB->exec("DELETE FROM `Web_NoticeLooked` WHERE nbatch='{$nt['id']}'");
                    }
                    $sql[] = $DB->exec("DELETE FROM `Web_Notice` WHERE id='{$i}'");
                }
            } else {
                //为旗下年级删除公告
                foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $nt = $thisDB->query("SELECT * FROM Web_Notice WHERE gbatch='{$row['gbatch']}' limit 1")->fetch();
                    $thisDB->exec("DELETE FROM `Web_Notice` WHERE gbatch='{$row['gbatch']}'");
                    $thisDB->exec("DELETE FROM `Web_NoticeLooked` WHERE nbatch='{$nt['id']}'");
                }
                $sql[] = $DB->exec("DELETE FROM `Web_Notice` WHERE id='{$id}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"删除成功！"}');
            } else {
                exit('{"code":-1,"msg":"删除失败！"}');
            }
        } elseif ($type == 'SetLid') {
            $id = daddslashes($_POST['id']);
            $gid = daddslashes($_POST['gid']);
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE gradeid='{$gid}'")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
            $classid = daddslashes($_POST['classid']);
            if ($classid == 'All') {
                $classid = $user['class'];
            }
            $row = $thisDB->query("SELECT * FROM Web_Class WHERE name='$classid' limit 1")->fetch();
            if (!$user) {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            if ($row['lid'] == $id) {
                $lid = 0;
                $sql[] = $thisDB->exec("UPDATE `Web_Class` SET `lid`='{$lid}' WHERE `name`='{$classid}'");
                $sql[] = $thisDB->exec("UPDATE `Web_User` SET `status`='0' WHERE `id`='{$user['id']}'");
            } else {
                $lid = $id;
                $sql[] = $thisDB->exec("UPDATE `Web_User` SET `status`='0' WHERE `id`='{$row['lid']}'");
                $sql[] = $thisDB->exec("UPDATE `Web_Class` SET `lid`='{$lid}' WHERE `name`='{$classid}'");
                $sql[] = $thisDB->exec("UPDATE `Web_User` SET `status`='2' WHERE `id`='{$user['id']}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"设置成功！","lid":"' . $lid . '"}');
            } else {
                exit('{"code":-1,"msg":"设置失败！"}');
            }
        } elseif ($type == 'SetFLid') {
            $id = daddslashes($_POST['id']);
            $gid = daddslashes($_POST['gid']);
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE gradeid='{$gid}'")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
            $classid = daddslashes($_POST['classid']);
            if ($classid == 'All') {
                $classid = $user['class'];
            }
            $row = $thisDB->query("SELECT * FROM Web_Class WHERE name='$classid' limit 1")->fetch();
            if (!$user) {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            if ($user['id'] == $row['lid']) {
                exit('{"code":-1,"msg":"已是团支书，不能设置为代理团支书！"}');
            }
            if ($user['status'] == 2) {
                $dtype = 0;
                $sql = $thisDB->exec("UPDATE `Web_User` SET `status`='0' WHERE `id`='{$user['id']}'");
            } else {
                $dtype = 2;
                $sql = $thisDB->exec("UPDATE `Web_User` SET `status`='2' WHERE `id`='{$user['id']}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"设置成功！","type":"' . $dtype . '","lid":"' . $user['id'] . '"}');
            } else {
                exit('{"code":-1,"msg":"设置失败！"}');
            }
        } elseif ($type == 'ChangeClass') {
            $mid = daddslashes($_POST['mid']);
            $gid = daddslashes($_POST['gid']);
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE gradeid='{$gid}'")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $classid = daddslashes($_POST['classid']);
            $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$mid}' limit 1")->fetch();
            $row = $thisDB->query("SELECT * FROM Web_Class WHERE name='{$classid}' limit 1")->fetch();
            if (!$user && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            if ($_POST['dotype'] == 'Array') {
                foreach ($mid as $mi) {
                    $sql[] = $thisDB->exec("UPDATE `Web_User` SET `class`='{$row['name']}' WHERE `id`='{$mi}'");
                    $sql[] = $thisDB->exec("UPDATE `Web_Clocking` SET `class`='{$row['name']}' WHERE `uid`='{$mi}'");
                }
            } else {
                $sql[] = $thisDB->exec("UPDATE `Web_User` SET `class`='{$row['name']}' WHERE `id`='{$mid}'");
                $sql[] = $thisDB->exec("UPDATE `Web_Clocking` SET `class`='{$row['name']}' WHERE `uid`='{$mid}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"转班成功！"}');
            } else {
                exit('{"code":-1,"msg":"转班失败！"}');
            }
        } elseif ($type == 'SetPassword') {
            $id = daddslashes($_POST['id']);
            $gid = daddslashes($_POST['gid']);
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE gradeid='{$gid}'")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
            if (!$user && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            $p = md5(123456);
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    $u = $thisDB->query("SELECT * FROM Web_User WHERE id='{$i}' limit 1")->fetch();
                    $sql[] = $thisDB->exec("UPDATE `Web_User` SET `password`='{$p}' WHERE `id`='{$i}'");
                }
            } else {
                $sql[] = $thisDB->exec("UPDATE `Web_User` SET `password`='{$p}' WHERE `id`='{$id}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"重置成功！"}');
            } elseif ($user['password'] == $p) {
                exit('{"code":1,"msg":"与之前相同，无需重置！"}');
            } else {
                exit('{"code":-1,"msg":"重置失败！"}');
            }
        } elseif ($type == 'EditName') {
            $id = daddslashes($_POST['id']);
            $name = daddslashes($_POST['name']);
            $gid = daddslashes($_POST['gid']);
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE gradeid='{$gid}'")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
            if (!$user && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            $sql = $thisDB->exec("UPDATE `Web_User` SET `username`='{$name}' WHERE `id`='{$id}'");
            if ($sql) {
                exit('{"code":1,"msg":"修改成功！"}');
            } elseif ($user['username'] == $name) {
                exit('{"code":1,"msg":"与之前相同，无需修改！"}');
            } else {
                exit('{"code":-1,"msg":"修改失败！"}');
            }
        } elseif ($type == 'AddMember') {
            $gid = daddslashes($_POST['gid']);
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE gradeid='{$gid}'")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $username = daddslashes($_POST['username']);
            $classid = daddslashes($_POST['classid']);
            $user = $thisDB->query("SELECT * FROM Web_User WHERE username='{$username}' and class='{$classid}' limit 1")->fetch();
            $row = $thisDB->query("SELECT * FROM Web_Class WHERE name='$classid' limit 1")->fetch();
            if (@preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $userrow)) {
                exit('{"code":-1,"msg":"输入信息只能为中文！"}');
            }
            if ($user) {
                exit('{"code":-1,"msg":"该用户已存在！"}');
            }
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            $p = md5(123456);
            $uskey = str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $sql = $thisDB->exec("INSERT INTO `Web_User` (`username`, `password`, `class`, `addtime`, `uskey`) VALUES ('{$username}', '{$p}', '{$classid}', '{$date}', '{$uskey}')");
            $mid = ($thisDB->query("select LAST_INSERT_ID()")->fetch()) ['LAST_INSERT_ID()'];
            if ($sql) {
                exit('{"code":1,"msg":"添加成功！","mid":"' . $mid . '","classid":"' . $classid . '"}');
            } else {
                exit('{"code":-1,"msg":"添加失败！"}');
            }
        } elseif ($type == 'DelMember') {
            $id = daddslashes($_POST['id']);
            $gid = daddslashes($_POST['gid']);
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE gradeid='{$gid}'")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
            $row = $thisDB->query("SELECT * FROM Web_Class WHERE name='{$user['class']}' limit 1")->fetch();
            if (!$user && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
            if (!$row && $_POST['dotype'] != 'Array') {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            if ($_POST['dotype'] == 'Array') {
                foreach ($id as $i) {
                    $u = $thisDB->query("SELECT * FROM Web_User WHERE id='{$i}' limit 1")->fetch();
                    $sql[] = $thisDB->exec("DELETE FROM `Web_User` WHERE id='{$i}'");
                    $sql[] = $thisDB->exec("DELETE FROM `Web_Clocking` WHERE uid='{$i}'");
                    $sql[] = $thisDB->exec("DELETE FROM `Web_NoticeLooked` WHERE uid='{$i}'");
                }
            } else {
                $sql[] = $thisDB->exec("DELETE FROM `Web_User` WHERE id='{$id}'");
                $sql[] = $thisDB->exec("DELETE FROM `Web_Clocking` WHERE uid='{$id}'");
                $sql[] = $thisDB->exec("DELETE FROM `Web_NoticeLooked` WHERE uid='{$id}'");
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"删除成功！"}');
            } else {
                exit('{"code":-1,"msg":"删除失败！"}');
            }
        } elseif ($type == 'SetAllPassword') {
            $classid = daddslashes($_POST['classid']);
            $gid = daddslashes($_POST['gid']);
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE gradeid='{$gid}'")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $row = $thisDB->query("SELECT * FROM Web_Class WHERE name='{$classid}' limit 1")->fetch();
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            $p = md5(123456);
            $sql = $thisDB->exec("UPDATE `Web_User` SET `password`='{$p}' WHERE `class`='{$classid}'");
            if ($sql) {
                exit('{"code":1,"msg":"重置成功！"}');
            } else {
                exit('{"code":-1,"msg":"重置失败，可能用户密码并未修改！"}');
            }
        } elseif ($type == 'SetSomeFLid') {
            $gid = daddslashes($_POST['gid']);
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE gradeid='{$gid}'")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            foreach (daddslashes($_POST['id']) as $id) {
                $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$id}' limit 1")->fetch();
                $classid = daddslashes($_POST['classid']);
                if ($classid == 'All') {
                    $classid = $user['class'];
                }
                $row = $thisDB->query("SELECT * FROM Web_Class WHERE name='$classid' limit 1")->fetch();
                if (!$user) {
                    continue;
                }
                if (!$row) {
                    continue;
                }
                if ($user['id'] == $row['lid']) {
                    continue;
                }
                if ($user['status'] == 2) {
                    $dtype = 0;
                    $sql[] = $thisDB->exec("UPDATE `Web_User` SET `status`='0' WHERE `id`='{$user['id']}'");
                } else {
                    $dtype = 2;
                    $sql[] = $thisDB->exec("UPDATE `Web_User` SET `status`='2' WHERE `id`='{$user['id']}'");
                }
            }
            if (!empty($sql)) {
                exit('{"code":1,"msg":"设置成功！"}');
            } else {
                exit('{"code":-1,"msg":"设置失败！"}');
            }
        } elseif ($type == 'SetFiniedFile') {
            $tid = daddslashes($_POST['tid']);
            $t = $DB->query("SELECT * FROM Web_Task WHERE id='{$tid}' limit 1")->fetch();
            if (!$t) {
                exit('{"code":-1,"msg":"此任务不存在！"}');
            }
            foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
                //连接数据库
                $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}' limit 1")->fetch();
                $data = array(
                    "username" => "阆中中学",
                    "password" => "6306533"
                );
                $url = "http://dxx.scyol.com/backend/adminUser/login";
                $result = login_post($url, 'test.txt', json_encode($data));
                $msg = json_decode($result, true) ['data'];
                $rs = $thisDB->query("select * from Web_Config");
                while ($row = $rs->fetch()) {
                    $conf[$row['x']] = $row['j'];
                }
                if ($conf['token'] == null) {
                    $token = str_replace('\\', '\\\\', json_encode($msg));
                } else {
                    $data2 = array(
                        "pageNo" => "1",
                        "pageSize" => "100"
                    );
                    $ta = json_decode($conf['token'], true);
                    $tk = $ta['token'];
                    $return = json_decode(get_content('http://dxx.scyol.com/backend/stages/list', $tk, json_encode($data2)) , true);
                    if (strpos($return['msg'], '未登录') !== false) {
                        $url = "http://dxx.scyol.com/backend/adminUser/login";
                        $result = login_post($url, 'test.txt', json_encode($data));
                        $ra = json_decode($result, true);
                        $rs = $ra['data'];
                        $token = str_replace('\\', '\\\\', json_encode($rs));
                    } else {
                        $token = str_replace('\\', '\\\\', $conf['token']);
                    }
                }
                $do = $thisDB->exec("UPDATE `Web_Config` SET `j`='{$token}' WHERE `x`='token'");
                if ($do || str_replace('\\', '\\\\', $conf['token']) == $token) {
                    $msg = json_decode(str_replace('\\\\', '\\', $token) , true);
                    $token = $msg['token'];
                    $data = array(
                        "orgId" => $conf['orgid'],
                        "stagesId" => $task['stid'],
                        "name" => "",
                        "tel" => "",
                        "pageNo" => "1",
                        "pageSize" => "10000"
                    );
                    $result = get_content('http://dxx.scyol.com/backend/study/student/list', $token, json_encode($data));
                    $rarr = json_decode($result, true);
                    $do = file_put_contents(ROOT .'../Txts/' . $conf['gradename'] . '-' . $task['id'] . '.txt', json_encode($rarr['data'], JSON_UNESCAPED_UNICODE));
                }
            }
            if ($do) {
                exit('{"code":1,"msg":"已完成视频学习成员信息获取成功！"}');
            } else {
                exit('{"code":-1,"msg":"已完成视频学习成员信息获取失败！"}');
            }
        } elseif ($type == 'TaskSettle') {
            $tid = daddslashes($_POST['tid']);
            $cid = daddslashes($_POST['cid']);
            $gn = daddslashes($_POST['gn']);
            $goid = daddslashes($_POST['goid']);
            $t = $DB->query("SELECT * FROM Web_Task WHERE id='{$tid}' limit 1")->fetch();
            if (!$t) {
                exit('{"code":-1,"msg":"此任务不存在！"}');
            }
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE orgid='{$goid}'")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}' limit 1")->fetch();
            $rs = $thisDB->query("select * from Web_Config");
            while ($row = $rs->fetch()) {
                $conf[$row['x']] = $row['j'];
            }
            $data = array(
                "username" => "阆中中学",
                "password" => "6306533"
            );
            $url = "http://dxx.scyol.com/backend/adminUser/login";
            $result = login_post($url, 'test.txt', json_encode($data));
            $msg = json_decode($result, true) ['data'];
            if ($conf['token'] == null) {
                $token = str_replace('\\', '\\\\', json_encode($msg));
            } else {
                $data2 = array(
                    "pageNo" => "1",
                    "pageSize" => "100"
                );
                $ta = json_decode($conf['token'], true);
                $tk = $ta['token'];
                $return = json_decode(get_content('http://dxx.scyol.com/backend/stages/list', $tk, json_encode($data2)) , true);
                if (strpos($return['msg'], '未登录') !== false) {
                    $url = "http://dxx.scyol.com/backend/adminUser/login";
                    $result = login_post($url, 'test.txt', json_encode($data));
                    $ra = json_decode($result, true);
                    $rs = $ra['data'];
                    $token = str_replace('\\', '\\\\', json_encode($rs));
                } else {
                    $token = str_replace('\\', '\\\\', $conf['token']);
                }
            }
            $do = $thisDB->exec("UPDATE `Web_Config` SET `j`='{$token}' WHERE `x`='token'");
            if ($do || str_replace('\\', '\\\\', $conf['token']) == $token) {
                $msg = json_decode(str_replace('\\\\', '\\', $token) , true);
                $token = $msg['token'];
                $time = time();
                if ($conf['orgid'] == null) {
                    exit('{"code":-1,"msg":"本年级未设置对应组织ID，请联系总管理设置"}');
                }
                $clocking = $thisDB->query("SELECT * FROM Web_Clocking WHERE id='{$cid}' limit 1")->fetch();
                $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$clocking['uid']}'")->fetch();
                if (!$user) {
                    exit('{"code":-1,"msg":"没有此用户！"}');
                }
                if (!file_exists('./Txts/' . $conf['gradename'] . '-' . $task['id'] . '.txt')) {
                    $data = array(
                        "orgId" => $conf['orgid'],
                        "stagesId" => $task['stid'],
                        "name" => "",
                        "tel" => "",
                        "pageNo" => "1",
                        "pageSize" => "10000"
                    );
                    $result = get_content('http://dxx.scyol.com/backend/study/student/list', $token, json_encode($data));
                    $rarr = json_decode($result, true);
                    file_put_contents('./Txts/' . $conf['gradename'] . '-' . $task['id'] . '.txt', json_encode($rarr['data'], JSON_UNESCAPED_UNICODE));
                }
                if (ifinfinished($user['username'], $user['class'], './Txts/' . $conf['gradename'] . '-' . $task['id'] . '.txt', $conf['gradename'] . $user['class'] . '班团支部')) {
                    if ($clocking) {
                        $sql = $thisDB->exec("UPDATE `Web_Clocking` SET `status`='1', `addtime`='{$date}' WHERE `id`='{$clocking['id']}'");
                    } else {
                        exit('{"code":-1,"msg":"没有此记录，请删除任务重新添加！"}');
                    }
                    if ($sql) {
                        exit('{"code":1,"msg":"此用户已完成视频学习！"}');
                    } else {
                        exit('{"code":-1,"msg":"结算失败！"}');
                    }
                } else {
                    $sql = $thisDB->exec("UPDATE `Web_Clocking` SET `status`='3', `addtime`='{$date}' WHERE `id`='{$clocking['id']}'");
                    exit('{"code":-1,"msg":"此用户未完成视频学习！"}');
                }
            } else {
                exit('{"code":-1,"msg":"大学习官网登录失败！"}');
            }
        } elseif ($type == 'GetOutVip') {
            $classid = daddslashes($_POST['cid']);
            $gid = daddslashes($_POST['gid']);
            $num = daddslashes($_POST['num']);
            $grade = $DB->query("SELECT * FROM Web_Grade WHERE id='{$gid}'")->fetch();
            if (!$grade) {
                exit('{"code":-1,"msg":"该年级不存在！"}');
            }
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            $row = $thisDB->query("SELECT * FROM Web_Class WHERE name='{$classid}' limit 1")->fetch();
            if (!$row) {
                exit('{"code":-1,"msg":"该班级不存在！"}');
            }
            
            foreach ($thisDB->query("SELECT * FROM Web_User WHERE username!='Administrator' ORDER BY id DESC")->fetchAll() as $user) {
                if ($user['class'] == $row['name']) {
                    //获取用户打卡未完成数量
                    $unfinishedcount = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE uid='{$user['id']}' and status!='1'")->fetchColumn();
                
                    $usarr[] = array("un"=>$user['username'],"uid"=>$user['id'],"uncounts"=>$unfinishedcount);
                }
                
                //获取总用户打卡未完成数量
                $allunfinishedcount = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE uid='{$user['id']}' and status!='1'")->fetchColumn();
                
                $allusarr[] = array("un"=>$user['username'],"uid"=>$user['id'],"uncounts"=>$allunfinishedcount);
            }
            
            foreach ($usarr as $key=>$val) {
                $num1[$key] = $val['un'];
                $num2[$key] = $val['uid'];
                $num3[$key] = $val['uncounts'];
            }
            
            array_multisort($num3,SORT_DESC,$num2,$num1,$usarr);
            
            foreach ($allusarr as $key=>$val) {
                $allnum1[$key] = $val['un'];
                $allnum2[$key] = $val['uid'];
                $allnum3[$key] = $val['uncounts'];
            }
            
            array_multisort($allnum3,SORT_DESC,$allnum2,$allnum1,$allusarr);
            //shuffle($usarr);
            
            $i = 0;
            foreach ($usarr as $ur) {
                $i++;
                
                $flmsg = @file_get_contents('./Txts/tuanyuan/'.($DB->query("SELECT * FROM Web_Grade WHERE id='{$gid}'")->fetch())['orgid'].'.txt');
                $flarr = @json_decode($flmsg,true);
                
                $nm .= $ur['un'].'  未完成次数：<b style=\"color:red;\">['.$ur['uncounts'].']</b>次 ';
                if ($flmsg == null || $flmsg == 'null') {
                    $nm .= '<b style=\"color:red;\">年级未设置</b>';
                } else {
                    if (in_array($ur['uid'],$flarr)) {
                        $nm .= '<b style=\"color:green;\">团员</b>';
                    } else {
                        $nm .= '<b style=\"color:grey;\">非团员</b>';
                    }
                }
                
                $auc = $thisDB->query("SELECT count(*) FROM Web_User WHERE class='{$classid}'")->fetchColumn();
                $ai = 0;
                foreach ($allusarr as $au) {
                    $ai++;
                    if ($au['uid'] == $ur['uid']) {
                        break;
                    }
                }
                $nm .= ' [班级第<b style=\"color:#41A5EE;\">'.($auc - $i + 1).'</b>名] [年级第<b style=\"color:#102D62;\">'.(@count($allusarr) - $ai + 1).'</b>名]<br>';
                
                //if ($i >= $num && in_array($ur['uid'],$flarr)) {
                if ($i >= $num) {
                    break;
                }
            }
            
            exit('{"code":1,"msg":"获取成功！","data":"'.$nm.'"}');
        } else {
            exit('{"code":-4,"msg":"No Type or No Permission"}');
        }
        break;

    case 'GetAdminTasksInfo':
        $tid = daddslashes($_POST['tid']);
        $t = $DB->query("SELECT * FROM Web_Task WHERE gbatch='{$tid}' LIMIT 1")->fetch();
        if ($t) {
            echo '[';
            $grades = $DB->query("SELECT * FROM Web_Grade ORDER BY id")->fetchAll();
            echo '{"name": "' . $t['title'] . ' 【已完成】","areaStyle": {"normal": {"color": "#0096888c"}},"itemStyle": {"normal": {"color": "#009688","lineStyle": {"color": "#009688"}}},"smooth": "true","type": "' . (count($grades) == 1 ? 'bar' : 'line') . '","data": [';
            $i = 0;
            foreach ($grades as $g) {
                //连接数据库
                $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}' limit 1")->fetch();
                $clcs = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status!='3'")->fetchAll();
                $hcs = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='2'")->fetchAll();
                if (time() >= $task['endtime']) {
                    $hcounts = @count($hcs);
                    $clcscount = @count($clcs) - $hcounts;
                } else {
                    $clcscount = @count($clcs);
                }
                echo $clcscount;
                $i++;
                echo ($i == 1 ? ',' : '');
            }
            echo ']},';
            echo '{"name": "' . $t['title'] . ' 【未完成】","areaStyle": {"normal": {"color": "#cc00018c"}},"itemStyle": {"normal": {"color": "#CC0001","lineStyle": {"color": "#CC0001"}}},"smooth": "true","type": "' . (count($grades) == 1 ? 'bar' : 'line') . '","data": [';
            $i = 0;
            foreach ($grades as $g) {
                //连接数据库
                $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}' limit 1")->fetch();
                $users = $thisDB->query("SELECT count(*) FROM Web_User WHERE username!='Administrator'")->fetchColumn();
                $clcs = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status!='3'")->fetchColumn();
                echo $users - $clcs;
                $i++;
                echo ($i == 1 ? ',' : '');
            }
            echo ']}';
            //if (time() >= $task['endtime']) {
            if (false) {
                echo ',';
                echo '{"name": "' . $t['title'] . ' 【谎打卡】","areaStyle": {"normal": {"color": "#FF9B028c"}},"itemStyle": {"normal": {"color": "#FF9B02","lineStyle": {"color": "#FF9B02"}}},"smooth": "true","type": "' . (count($grades) == 1 ? 'bar' : 'line') . '","data": [';
                $i = 0;
                foreach ($grades as $g) {
                    //连接数据库
                    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                    $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}' limit 1")->fetch();
                    $clcs = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status!='3'")->fetchColumn();
                    $hcs = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='2'")->fetchAll();
                    $hcounts = @count($hcs);
                    echo $hcounts;
                    $i++;
                    if ($i < count($grades)) {
                        echo ',';
                    }
                }
                echo ']}';
            }
            /*if (($DB->query("SELECT * FROM Web_Task ORDER BY id DESC LIMIT 1")->fetch())['id'] != $t['id']) {
                    echo ',';
                }*/
            echo ']';
        } else {
            exit('{"code":-1,"msg":"没有打卡任务，请添加！"}');
        }
        break;

    case 'GetAdminClassesTasksInfo':
        $gid = daddslashes($_POST['gid']);
        $tid = daddslashes($_POST['tid']);
        $row = $DB->query("SELECT * FROM Web_Grade WHERE id='$gid' limit 1")->fetch();
        if (!$row) {
            exit('{"code":-1,"msg":"该年级不存在！"}');
        }
        //连接数据库
        $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$row['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
        $tasks = $DB->query("SELECT * FROM Web_Task WHERE gbatch='{$tid}' ORDER BY id")->fetchAll();
        if ($tasks) {
            echo '[';
            $classes = $thisDB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll();
            foreach ($tasks as $tt) {
                $t = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$tt['gbatch']}'")->fetch();
                $i = 0;
                echo '{"name": "' . $t['title'] . ' 【已完成】","areaStyle": {"normal": {"color": "#0096888c"}},"itemStyle": {"normal": {"color": "#009688","lineStyle": {"color": "#009688"}}},"smooth": "true","type": "line","data": [';
                foreach ($classes as $cs) {
                    $clcs = $thisDB->query("SELECT * FROM Web_Clocking WHERE class='{$cs['name']}' and taskbatch='{$t['id']}' and status!='3'")->fetchAll();
                    $hcs = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$t['id']}' and class='{$cs['name']}' and status='2'")->fetchAll();
                    if (time() >= $t['endtime']) {
                        $hcounts = @count($hcs);
                        $clcscount = @count($clcs) - $hcounts;
                    } else {
                        $clcscount = @count($clcs);
                    }
                    echo $clcscount;
                    $i++;
                    if ($i < count($classes)) {
                        echo ',';
                    }
                }
                echo ']},';
                echo '{"name": "' . $t['title'] . ' 【未完成】","areaStyle": {"normal": {"color": "#cc00018c"}},"itemStyle": {"normal": {"color": "#CC0001","lineStyle": {"color": "#CC0001"}}},"smooth": "true","type": "line","data": [';
                $i = 0;
                foreach ($classes as $cs) {
                    $clcounts = $thisDB->query("SELECT count(*) FROM Web_User WHERE class='{$cs['name']}'")->fetchColumn();
                    $clcs = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE class='{$cs['name']}' and taskbatch='{$t['id']}' and status!='3'")->fetchColumn();
                    echo $clcounts - $clcs;
                    $i++;
                    if ($i < count($classes)) {
                        echo ',';
                    }
                }
                echo ']}';
                //if (time() >= $t['endtime']) {
                if (false) {
                    echo ',';
                    echo '{"name": "' . $t['title'] . ' 【谎打卡】","areaStyle": {"normal": {"color": "#FF9B028c"}},"itemStyle": {"normal": {"color": "#FF9B02","lineStyle": {"color": "#FF9B02"}}},"smooth": "true","type": "line","data": [';
                    $i = 0;
                    foreach ($classes as $cs) {
                        $clcounts = $thisDB->query("SELECT count(*) FROM Web_User WHERE class='{$cs['name']}'")->fetchColumn();
                        $clcs = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE class='{$cs['name']}' and taskbatch='{$t['id']}' and status!='3'")->fetchColumn();
                        $hcs = $thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$t['id']}' and class='{$cs['name']}' and status='2'")->fetchAll();
                        $hcounts = @count($hcs);
                        echo $hcounts;
                        $i++;
                        if ($i < count($classes)) {
                            echo ',';
                        }
                    }
                    echo ']}';
                }
                /*if (($thisDB->query("SELECT * FROM Web_Task ORDER BY id DESC LIMIT 1")->fetch())['id'] != $t['id']) {
                    echo ',';
                }*/
            }
            echo ']';
        } else {
            exit('{"code":-1,"msg":"没有打卡任务，请添加！"}');
        }
        break;

    case 'AdminLookTasksInfo':
        $tasks = $DB->query("SELECT * FROM Web_Task ORDER BY id")->fetchAll();
        if ($tasks) {
            echo '[';
            $grades = $DB->query("SELECT * FROM Web_Grade ORDER BY id")->fetchAll();
            $i = 0;
            foreach ($grades as $g) {
                //连接数据库
                $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                $i++;
                foreach ($tasks as $t) {
                    $users = $thisDB->query("SELECT count(*) FROM Web_User WHERE username!='Administrator'")->fetchColumn();
                    $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}' limit 1")->fetch();
                    $clcs = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status!='3'")->fetchColumn();
                    $errorc = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='2'")->fetchColumn();
                    $finishedcount[$i][] = $clcs - $errorc;
                    $errorfinishedcount[$i][] = $errorc;
                    $unfinishedcount[$i][] = $users - $clcs;
                }
            }
            exit(json_encode($unfinishedcount));
            for ($i = 0;$i < count($finishedcount[1]);$i++) {
                $resfinishedcount[] = $finishedcount[1][$i] + $finishedcount[2][$i];
                $reserrorfinishedcount[] = $errorfinishedcount[1][$i] + $errorfinishedcount[2][$i];
                $resunfinishedcount[] = $unfinishedcount[1][$i] + $unfinishedcount[2][$i];
            }
            
            echo '{"name": "已打卡人数","areaStyle": {"normal": {"color": "#0096888c"}},"itemStyle": {"normal": {"color": "#009688","lineStyle": {"color": "#009688"}}},"smooth": "true","type": "line","data": ' . json_encode($resfinishedcount) . '},';
            //echo '{"name": "谎打卡人数","areaStyle": {"normal": {"color": "#FF9B028c"}},"itemStyle": {"normal": {"color": "#FF9B02","lineStyle": {"color": "#FF9B02"}}},"smooth": "true","type": "line","data": '.json_encode($reserrorfinishedcount).'},';
            echo '{"name": "未打卡人数","areaStyle": {"normal": {"color": "#cc00018c"}},"itemStyle": {"normal": {"color": "#CC0001","lineStyle": {"color": "#CC0001"}}},"smooth": "true","type": "line","data": ' . json_encode($resunfinishedcount) . '}';
            echo ']';
        } else {
            exit('{"code":-1,"msg":"没有打卡任务，请添加！"}');
        }
        break;

    case 'LookTasksInfo':
        $tasks = $DB->query("SELECT * FROM Web_Task ORDER BY id")->fetchAll();
        if ($tasks) {
            echo '[';
            if ($userrow['id'] == 1) {
                foreach ($tasks as $task) {
                    $users = $DB->query("SELECT count(*) FROM Web_User WHERE username!='Administrator'")->fetchColumn();
                    $clcs = $DB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status!='3'")->fetchColumn();
                    $errorc = $DB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and status='2'")->fetchColumn();
                    $finishedcount[] = $clcs - $errorc;
                    $errorfinishedcount[] = $errorc;
                    $unfinishedcount[] = $users - $clcs;
                }
            } else {
                foreach ($tasks as $task) {
                    $users = $DB->query("SELECT count(*) FROM Web_User WHERE class='{$userrow['class']}'")->fetchColumn();
                    $clcs = $DB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and class='{$userrow['class']}' and status!='3'")->fetchColumn();
                    $errorc = $DB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$task['id']}' and class='{$userrow['class']}' and status='2'")->fetchColumn();
                    $finishedcount[] = $clcs - $errorc;
                    $errorfinishedcount[] = $errorc;
                    $unfinishedcount[] = $users - $clcs;
                }
            }
            echo '{"name": "已打卡人数","areaStyle": {"normal": {"color": "#0096888c"}},"itemStyle": {"normal": {"color": "#009688","lineStyle": {"color": "#009688"}}},"smooth": "true","type": "line","data": ' . json_encode($finishedcount) . '},';
            //echo '{"name": "谎打卡人数","areaStyle": {"normal": {"color": "#FF9B028c"}},"itemStyle": {"normal": {"color": "#FF9B02","lineStyle": {"color": "#FF9B02"}}},"smooth": "true","type": "line","data": '.json_encode($errorfinishedcount).'},';
            echo '{"name": "未打卡人数","areaStyle": {"normal": {"color": "#cc00018c"}},"itemStyle": {"normal": {"color": "#CC0001","lineStyle": {"color": "#CC0001"}}},"smooth": "true","type": "line","data": ' . json_encode($unfinishedcount) . '}';
            echo ']';
        } else {
            exit('{"code":-1,"msg":"没有打卡任务，请添加！"}');
        }
        break;

    case 'AdminLookNoticesInfo':
        $notices = $DB->query("SELECT * FROM Web_Notice ORDER BY id")->fetchAll();
        if ($notices) {
            echo '[';
            $grades = $DB->query("SELECT * FROM Web_Grade ORDER BY id")->fetchAll();
            foreach ($grades as $g) {
                //连接数据库
                $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
                foreach ($notices as $n) {
                    $users = $thisDB->query("SELECT count(*) FROM Web_User WHERE username!='Administrator'")->fetchColumn();
                    $notice = $thisDB->query("SELECT * FROM Web_Notice WHERE gbatch='{$n['gbatch']}' limit 1")->fetch();
                    $nlcs = $thisDB->query("SELECT count(*) FROM Web_NoticeLooked WHERE nbatch='{$notice['id']}'")->fetchColumn();
                    $lookedcount[] = $nlcs;
                    $unlookedcount[] = $users - $nlcs;
                }
            }
            echo '{"name": "已查看人数","areaStyle": {"normal": {"color": "#0096888c"}},"itemStyle": {"normal": {"color": "#009688","lineStyle": {"color": "#009688"}}},"smooth": "true","type": "line","data": ' . json_encode($lookedcount) . '},';
            echo '{"name": "未查看人数","areaStyle": {"normal": {"color": "#cc00018c"}},"itemStyle": {"normal": {"color": "#CC0001","lineStyle": {"color": "#CC0001"}}},"smooth": "true","type": "line","data": ' . json_encode($unlookedcount) . '}';
            echo ']';
        } else {
            exit('{"code":-1,"msg":"没有打卡任务，请添加！"}');
        }
        break;

    case 'LookNoticesInfo':
        $notices = $DB->query("SELECT * FROM Web_Notice ORDER BY id")->fetchAll();
        if ($notices) {
            echo '[';
            foreach ($notices as $n) {
                $users = $DB->query("SELECT count(*) FROM Web_User WHERE username!='Administrator'")->fetchColumn();
                $nlcs = $DB->query("SELECT count(*) FROM Web_NoticeLooked WHERE nbatch='{$n['id']}'")->fetchColumn();
                $lookedcount[] = $nlcs;
                $unlookedcount[] = $users - $nlcs;
            }
            echo '{"name": "已查看人数","areaStyle": {"normal": {"color": "#0096888c"}},"itemStyle": {"normal": {"color": "#009688","lineStyle": {"color": "#009688"}}},"smooth": "true","type": "line","data": ' . json_encode($lookedcount) . '},';
            echo '{"name": "未查看人数","areaStyle": {"normal": {"color": "#cc00018c"}},"itemStyle": {"normal": {"color": "#CC0001","lineStyle": {"color": "#CC0001"}}},"smooth": "true","type": "line","data": ' . json_encode($unlookedcount) . '}';
            echo ']';
        } else {
            exit('{"code":-1,"msg":"没有打卡任务，请添加！"}');
        }
        break;

    default:
        exit('{"code":-4,"msg":"No Act"}');
        break;
    }
?>

