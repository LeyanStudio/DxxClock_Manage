<style>
b[id='title'] {
    width: 180px;
    display: block;
    float: left;
    border-left: 3px solid #1660F3;
    padding: 0 10px;
}
pp {
    margin: 7px;
    border-left: 3px solid #ff420a;
    padding: 3px 10px;
    display: block;
    line-height: 18px;
}
</style>
<?
include("./Includes/Common.php");
if ($userrow['status'] == 1 && $islogin2 == 1) {} else {exit('您无权限！');}

$nid = $_POST['nid'];
$notice = $DB->query("SELECT * FROM Web_Notice WHERE id='{$nid}' limit 1")->fetch();
//$ms = explode(',',$notice['status']);

if (!$notice) {
    exit('无此公告！');
} else {
    $classes = $DB->query("SELECT * FROM Web_Class order by name")->fetchAll();
    foreach ($classes as $c) {
        echo '<b id="title">'.$conf['gradename'].$c['name'].'班<b style="color:green;">【'.count($DB->query("SELECT * FROM Web_NoticeLooked WHERE class='{$c['name']}' and nbatch='{$nid}' order by id")->fetchAll()).'人】</b>：</b><br>';
        $i = 0;
        $t = 0;
        /*foreach ($ms as $ui) {
            $u = $DB->query("SELECT * FROM Web_User WHERE id='{$ui}' and class='{$c['name']}' limit 1")->fetch();
            if ($u) {
                $uc[] = $u;
            }
        }
        $ul = @count($uc) + 1;
        foreach ($ms as $uid) {
            $i++;
            $user = $DB->query("SELECT * FROM Web_User WHERE id='{$uid}' and class='{$c['name']}' limit 1")->fetch();
            
            $a = 0;
            foreach ($ms as $mm) {
                $uu = $DB->query("SELECT * FROM Web_User WHERE id='{$mm}' and class='{$c['name']}' limit 1")->fetch();
                if ($uu) {
                    $a++;
                    $aarr[] = $a;
                }
            }
            echo json_encode($aarr);
            
            //if ((!$user && empty($aarr)) || $ms['key'] == 'null') {
            if (!$user || $ms['key'] == 'null') {
            //if ($aarr == null || $ms['key'] == 'null') {
                if ($i == '1') {
                    echo '<center>暂无查看成员</center>';
                }
            } else {
                if ($i == '1') {
                    echo '<p><pp>';
                }
                $idlen = array_search($uid,$ms);
                unset($ms[$idlen]);
                if (count($ms) == 0) {
                    $ms = array("key"=>"null");
                }
                //echo ($i == '1' ? '' : '、').$user['username'].'【<b style="color:blue;">'.$user['class'].'班</b>】';
                echo ($i == '1' ? '' : '、').$user['username'];
                /*$t++;
                if ($t >= $ul) {
                    echo '</p>';
                }
            }
            $aarr = null;
        }*/
        $i = 0;
        $lookedmems = $DB->query("SELECT * FROM Web_NoticeLooked WHERE class='{$c['name']}' and nbatch='{$nid}' order by id")->fetchAll();
        if (!$lookedmems) {
            echo '<center>暂无查看成员</center>';
        } else {
            echo '<pp>';
            foreach ($lookedmems as $mem) {
                $i++;
                $user = $DB->query("SELECT * FROM Web_User WHERE id='{$mem['uid']}' limit 1")->fetch();
                echo ($i == '1' ? '' : '、').$user['username'];
            }
            echo '</pp>';
        }
        echo '</p>';
        //$uc = null;
    }
}
?>