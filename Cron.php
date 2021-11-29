<?
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
date_default_timezone_set('Asia/Shanghai');
$date = date("Y-m-d H:i:s");

require './Admin/Includes/Config.php';
try {
    $DB = new PDO("mysql:host={$dbconfig['host']};dbname={$dbconfig['dbname']};port={$dbconfig['port']}", $dbconfig['user'], $dbconfig['pwd']);
}
catch(Exception $e) {
    exit('连接数据库失败:' . $e->getMessage());
}
require './Admin/Includes/RootConfig.php';
require './Admin/Includes/Function.php';


echo '<script>document.title = \'大学习打卡网站 - 监控\'</script>';

$key = $_GET['Key'];
if ($key == null) {
    $result = array(
        "code" => - 1,
        "msg" => "Key不能为空！"
    );
} else {
    $tid = ($_GET['Tid'] == null ? ($DB->query("SELECT * FROM Web_Task order by id desc limit 1")->fetch()) ['id'] : $_GET['Tid']);
    $t = $DB->query("SELECT * FROM Web_Task WHERE id='{$tid}' limit 1")->fetch();
    if (!$t) {
        $result = array(
            "code" => - 1,
            "msg" => "此任务不存在！"
        );
    } else {
        foreach ($DB->query("SELECT * FROM Web_Grade")->fetchAll() as $g) {
            //连接数据库
            $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}", $rdbconfig['user'], $rdbconfig['pwd']);
            
            $rs = $thisDB->query("select * from Web_Config");
            while ($row = $rs->fetch()) {
                $conf[$row['x']] = $row['j'];
            }
            
            $task = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$t['gbatch']}' limit 1")->fetch();
            $data = array(
                "username" => "阆中中学",
                "password" => "6306533"
            );
            $url = "http://dxx.scyol.com/backend/adminUser/login";
            $tkresult = login_post($url, 'test.txt', json_encode($data));
            $msg = json_decode($tkresult, true) ['data'];
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
                    $tkresult = login_post($url, 'test.txt', json_encode($data));
                    $ra = json_decode($tkresult, true);
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
                $res = get_content('http://dxx.scyol.com/backend/study/student/list', $token, json_encode($data));
                $rarr = json_decode($res, true);
                $json = json_encode($rarr['data'], JSON_UNESCAPED_UNICODE);
                foreach ($thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$task['id']}'")->fetchAll() as $cl) {
                    $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$cl['uid']}'")->fetch();
                    $uc = $user['class'];
                    $uname = $user['username'];
                    
                    $d = false;
                    $count = 0;
                    foreach (json_decode($json,true) as $ra) {
                        if ($ra['orgName'] == $conf['gradename'].$uc.'班团支部' && $ra['name'] == $uname) {
                            $d = true;
                            $count++;
                            $thisDB->exec("UPDATE `Web_Clocking` SET `status`='1', `addtime`='{$date}' WHERE `id`='{$cl['id']}'");
                            break;
                        }
                    }
                }
            }
        }
    }
    /*if ($count > 0) {
        $result = array("code"=>1,"msg"=>"处理完毕，<b>{$count}</b>人完成视频学习！");
    } else {
        $result = array("code"=>-1,"msg"=>"处理完毕，无人完成视频学习！");
    }*/
    $result = array("code"=>1,"msg"=>"处理完毕！");
}

echo json_encode($result,JSON_UNESCAPED_UNICODE);
?>