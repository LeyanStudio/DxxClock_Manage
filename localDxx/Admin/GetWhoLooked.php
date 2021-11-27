<style>
div[class='GradeTitle'] {
    width: 320px;
    display: block;
    float: left;
    border-left: 3px solid #55c302;
    padding: 0 10px;
}
b[id='title'] {
    width: 170px;
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
if ($islogin == 1) {} else {exit('您无权限！');}

$notice = $DB->query("SELECT * FROM Web_Notice WHERE id='{$_POST['nid']}' limit 1")->fetch();

if (!$notice) {
	exit('无此公告！');
} else {
	$grades = $DB->query("SELECT * FROM Web_Grade")->fetchAll();
	foreach ($grades as $g) {
	    //连接数据库
	    $thisDB = new PDO("mysql:host={$rdbconfig['host']};dbname={$g['databasename']};port={$rdbconfig['port']}",$rdbconfig['user'],$rdbconfig['pwd']);
	    
	    $nid = ($thisDB->query("SELECT * FROM Web_Notice WHERE gbatch='{$notice['gbatch']}' limit 1")->fetch())['id'];
	    
	    echo '<div class="GradeTitle"><b>'.$g['name'].'</b><div>'.($notice['type'] == 4 || $notice['type'] == 5 || $notice['type'] == 6 ? '【管理员'.($thisDB->query("SELECT * FROM Web_NoticeLooked WHERE class='0' and nbatch='{$nid}' limit 1")->fetch() ? '<b style="color:green;">已查看</b>' : '<b style="color:red;">未查看</b>').'】' : '').'<b style="color:blue;">&nbsp;&nbsp;总【'.count($thisDB->query("SELECT * FROM Web_NoticeLooked WHERE nbatch='{$nid}'")->fetchAll()).'】人查看</b></div>';
	    
		$classes = $thisDB->query("SELECT * FROM Web_Class order by name")->fetchAll();
		foreach ($classes as $c) {
			echo '<b id="title">'.$g['name'].$c['name'].'班<b style="color:green;">【'.count($thisDB->query("SELECT * FROM Web_NoticeLooked WHERE class='{$c['name']}' and nbatch='{$nid}' order by id")->fetchAll()).'人】</b>：</b><br>';
			$i = 0;
			$lookedmems = $thisDB->query("SELECT * FROM Web_NoticeLooked WHERE class='{$c['name']}' and nbatch='{$nid}' order by id")->fetchAll();
			if (!$lookedmems) {
				echo '<center>暂无查看成员</center>';
			} else {
				echo '<pp>';
				foreach ($lookedmems as $mem) {
					$i++;
					$user = $thisDB->query("SELECT * FROM Web_User WHERE id='{$mem['uid']}' limit 1")->fetch();
					echo ($i == '1' ? '' : '、').$user['username'];
				}
				echo '</pp>';
			}
			echo '</p>';
		}
		echo '</div>';
	}
}
?>