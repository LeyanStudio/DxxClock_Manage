<?
include("./Includes/Common_Two.php");
if ($islogin == 1) {} else {header("Location:Index.php");}

function createtable($list,$filename){
    global $dkpaiming;
    header("Content-type:application/vnd.ms-excel");
    ////header('Content-Type: application/json; charset=GBK');
    header("Content-Disposition:filename=".$filename.".xls");
    
    foreach ($list as $r) {
        $row['gradename'] = $r['gradename'];
        $row['class'] = $r['class'];
        $row['taskname'] = $r['taskname'];
    }
  
    $strexport="{$row['gradename']}{$row['class']}班  {$row['taskname']}  完成情况\r\r"."序号\t姓名\t是否完成\r";
    $i = 0;
    foreach ($list as $row){
        ($row['status'] == 4 ? '' : $i++);
        $strexport .= ($row['status'] == 4 ? '' : $i)."\t";
        $strexport .= $row['username']."\t";
        $strexport .= ($row['status'] == 4 ? '' : ($row['status'] == 1 ? '已完成' : '未完成'))."\r";
    }
    
    foreach ($dkpaiming as $key=>$val) {
        $num1[$key] = $val['cname'];
        $num2[$key] = $val['tid'];
        $num3[$key] = $val['pc'];
    }
    $newfile = array_multisort($num3,SORT_DESC,$num2,$num1,$dkpaiming);

    //获取排名
    $i = 0;
    foreach ($dkpaiming as $dkpm) {
        $i++;
        if ($dkpm['cname'] == $_GET['CID']) {
            $pm = $i;
            $fned = $dkpm['fned'];
            $unfned = $dkpm['unfned'];
            $alluc = $dkpm['alluc'];
            $pctge = $dkpm['pc'];
            break;
        }
    }
    
    $strexport .= "\t\t\t\t\t\r";
    //$strexport .= "完成人数\t{$fned}人\t未完成人数\t{$unfned}人\t完成百分比\t{$pctge}%\t班级总人数\t{$alluc}人\t班级排名\t本年级第{$pm}名\r";
    $strexport .= "完成人数\t{$fned}人\t未完成人数\t{$unfned}人\t完成百分比\t{$pctge}%\t班级排名\t本年级第{$pm}名\r";
    
    $strexport=iconv('UTF-8',"GBK//IGNORE",$strexport);
    exit($strexport);
}

/*if ($_GET['ST'] == null) {
    $if = '';
    $fln = '统计记录';
} else {*/
    $gn = ($DB->query("SELECT * FROM Web_Grade WHERE id='{$_GET['GID']}'")->fetch())['name'];
    //$if = ' and status='.$_GET['ST'];
    /*if ($_GET['ST'] == 1) {
        $fln = '['.$gn.$_GET['CID'].'班]已完成情况统计记录';
    } elseif ($_GET['ST'] == 3) {
        $fln = '['.$gn.$_GET['CID'].'班]未完成情况统计记录';
    }*/
    $fln = '['.$gn.$_GET['CID'].'班]大学习完成情况统计记录';
//}

if ($_GET['TID'] == null) {
    echo '请选择任务';
    exit('<script>setTimeout(function(){history.go(-1);},2000);</script>');
}
//获取任务数据
foreach ($DB->query("SELECT * FROM Web_Grade WHERE id='{$_GET['GID']}'")->fetchAll() as $grade) {
    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);


    foreach (explode(',',$_GET['TID']) as $tk) {
        $t = $thisDB->query("SELECT * FROM Web_Task WHERE gbatch='{$tk}'")->fetch();
        $tid = $t['id'];
        
        foreach ($thisDB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll() as $class) {
            //获取年级内各班级打卡排名
            $finishedcounts = $thisDB->query("SELECT count(*) FROM Web_Clocking WHERE taskbatch='{$tid}' and class='{$class['name']}' and status='1'")->fetchColumn();
            $usercounts = $thisDB->query("SELECT count(*) FROM Web_User WHERE class='{$class['name']}'")->fetchColumn();
            $percentage = substr(($finishedcounts/$usercounts)*100,0,6);
            $dkpaiming[] = array("cname"=>$class['name'],"tid"=>$tid,"pc"=>$percentage,"fned"=>$finishedcounts,"unfned"=>($usercounts - $finishedcounts),"alluc"=>$usercounts);
        }
    
        //获取年级内班级完成情况
        //foreach ($thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' and class='{$_GET['CID']}'{$if} ORDER BY (SELECT class FROM Web_User WHERE id=uid)")->fetchAll() as $cl) {
        foreach ($thisDB->query("SELECT * FROM Web_Clocking WHERE taskbatch='{$tid}' and class='{$_GET['CID']}' ORDER BY status")->fetchAll() as $cl) {
            $user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$cl['uid']}'")->fetch();
            $gn = ($thisDB->query("SELECT * FROM Web_Config WHERE x='gradename'")->fetch())['j'];
            if ($cl['status'] != $lastst && $lastst != null) {
                $clockings[] = array("taskname"=>"","username"=>"","class"=>"","status"=>"4","gradename"=>"");
            }
            $clockings[] = array("taskname"=>$t['title'],"username"=>$user['username'],"class"=>$user['class'],"status"=>$cl['status'],"gradename"=>$gn,"tid"=>$tid);
            $lastst = $cl['status'];
        }
    }
}
//获取文件名
$tt = 0;
foreach (explode(',',$_GET['TID']) as $tk) {
    $tt++;
    $t = $DB->query("SELECT * FROM Web_Task WHERE gbatch='{$tk}'")->fetch();
    $tknames .= ($tt == 1 ? '' : 'の').$t['title'];
}
//输出数据
createtable($clockings,$tknames.$fln.'['.date("Y-m-d H:i").']');
?>