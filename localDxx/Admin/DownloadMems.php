<?
include("./Includes/Common_Two.php");
if ($islogin == 1) {} else {header("Location:Index.php");}

function createtable($list,$filename){
    global $dkpaiming;
    header("Content-type:application/vnd.ms-excel");
    ////header('Content-Type: application/json; charset=GBK');
    header("Content-Disposition:filename=".$filename.".xls");
  
    $strexport="序号\t姓名\t所属班级\t所属年级\r";
    $i = 1;
    foreach ($dkpaiming as $row){
        $strexport .= $i++."\t";
        $strexport .= $row['username']."\t";
        $strexport .= $row['class']."\t";
        $strexport .= $row['gradename']."\r";
    }
    $strexport=iconv('UTF-8',"GBK//IGNORE",$strexport);
    exit($strexport);
}

$gn = ($DB->query("SELECT * FROM Web_Grade WHERE id='{$_GET['GID']}'")->fetch())['name'];
$fln = $gn.'成员名单';

if ($_GET['GID'] == null) {
    echo '请选择年级';
    exit('<script>setTimeout(function(){history.go(-1);},2000);</script>');
}
//获取成员数据
foreach ($DB->query("SELECT * FROM Web_Grade WHERE id='{$_GET['GID']}'")->fetchAll() as $grade) {
    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$grade['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
    $tuanyuan = @file_get_contents('../Txts/tuanyuan/'.$grade['orgid'].'.txt');
    $tyarr = @json_decode($tuanyuan,true);

    foreach ($thisDB->query("SELECT * FROM Web_Class ORDER BY name")->fetchAll() as $class) {
        foreach ($thisDB->query("SELECT * FROM Web_User WHERE class='{$class['name']}'")->fetchAll() as $u) {
            if (!in_array($u['id'],$tyarr)) {
                continue;
            }
            $dkpaiming[] = array("class"=>$class['name'],"username"=>$u['username'],"status"=>(in_array($u['id'],$tyarr) ? '是' : '否'),"gradename"=>$gn);
        }
    }
}
//输出数据
createtable($clockings,$fln);
?>