<?php
/**
 * 文档digg处理ajax文件
 *
 * @version        $id:digg_ajax.php$
 * @package        DedeBIZ.Plus
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
$action = isset($action) ? trim($action) : '';
$format = isset($format) ? $format : '';
$id = empty($id) ? 0 : intval($id);
$cid = empty($cid) ? 1 : intval($cid);
helper('cache');
if ($id < 1) {
	exit();
}
$idtype = 'id';
$maintable = '#@__archives';
//获得栏目模型id
if ($cid < 0) {
	$row = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$cid' AND issystem='-1';");
	$maintable = empty($row['addtable']) ? '' : $row['addtable'];
	$idtype = 'aid';
}
$prefix = 'diggCache';
$key = 'aid-'.$id;
$row = GetCache($prefix, $key);
if (!is_array($row) || $cfg_digg_update == 0) {
	$row = $dsql->GetOne("SELECT goodpost,badpost,scores FROM `$maintable` WHERE $idtype='$id' ");
	if ($cfg_digg_update == 0) {
		if ($action == 'good') {
			$row['goodpost'] = $row['goodpost'] + 1;
			$dsql->ExecuteNoneQuery("UPDATE `$maintable` SET scores = scores + {$cfg_caicai_add},goodpost=goodpost+1,lastpost=".time()." WHERE $idtype='$id'");
		} else if ($action == 'bad') {
			$row['badpost'] = $row['badpost'] + 1;
			$dsql->ExecuteNoneQuery("UPDATE `$maintable` SET scores = scores - {$cfg_caicai_sub},badpost=badpost+1,lastpost=".time()." WHERE $idtype='$id'");
		}
		DelCache($prefix, $key);
	}
	SetCache($prefix, $key, $row);
} else {
	if ($action == 'good') {
		$row['goodpost'] = $row['goodpost'] + 1;
		$row['scores'] = $row['scores'] + $cfg_caicai_sub;
		if ($row['goodpost'] % $cfg_digg_update == 0) {
			$add_caicai_sub = $cfg_digg_update * $cfg_caicai_sub;
			$dsql->ExecuteNoneQuery("UPDATE `$maintable` SET scores = scores + {$add_caicai_sub},goodpost=goodpost+{$cfg_digg_update} WHERE $idtype='$id'");
			DelCache($prefix, $key);
		}
	} else if ($action == 'bad') {
		$row['badpost'] = $row['badpost'] + 1;
		$row['scores'] = $row['scores'] - $cfg_caicai_sub;
		if ($row['badpost'] % $cfg_digg_update == 0) {
			$add_caicai_sub = $cfg_digg_update * $cfg_caicai_sub;
			$dsql->ExecuteNoneQuery("UPDATE `$maintable` SET scores = scores - {$add_caicai_sub},badpost=badpost+{$cfg_digg_update} WHERE $idtype='$id'");
			DelCache($prefix, $key);
		}
	}
	SetCache($prefix, $key, $row);
}
$digg = '';
if (!is_array($row)) exit();

if ($row['goodpost'] + $row['badpost'] == 0) {
	$row['goodper'] = $row['badper'] = 0;
} else {
	$row['goodper'] = number_format($row['goodpost'] / ($row['goodpost'] + $row['badpost']), 3) * 100;
	$row['badper'] = 100 - $row['goodper'];
}
if (empty($formurl)) $formurl = '';
if ($formurl == 'caicai') {
	if ($action == 'good') $digg = $row['goodpost'];
	if ($action == 'bad') $digg  = $row['badpost'];
} else {
	$row['goodper'] = trim(sprintf("%4.2f", $row['goodper']));
	$row['badper'] = trim(sprintf("%4.2f", $row['badper']));
	if (!empty($format)) {
		//输出JSON API的方式
		$result = array(
			"code" => 200,
			"data" => array(
				'goodpost' => $row['goodpost'],
				'goodper' => $row['goodper'],
				'badpost' => $row['badpost'],
				'badper' => $row['badper'],
			),
		);
		$digg = json_encode($result);
	} else {
		//兼容之前的老版本
		$digg = '<div class="diggbox digg_good" onmousemove="this.style.backgroundPosition=\'left bottom\';" onmouseout="this.style.backgroundPosition=\'left top\';" onclick="postDigg(\'good\','.$id.')">
		<div class="digg_act">顶一下</div>
		<div class="digg_num">('.$row['goodpost'].')</div>
		<div class="digg_percent">
			<div class="digg_percent_bar"><span style="width:'.$row['goodper'].'%"></span></div>
			<div class="digg_percent_num">'.$row['goodper'].'%</div>
		</div>
	</div>
	<div class="diggbox digg_bad" onmousemove="this.style.backgroundPosition=\'right bottom\';" onmouseout="this.style.backgroundPosition=\'right top\';" onclick="postDigg(\'bad\','.$id.')">
		<div class="digg_act">踩一下</div>
		<div class="digg_num">('.$row['badpost'].')</div>
		<div class="digg_percent">
			<div class="digg_percent_bar"><span style="width:'.$row['badper'].'%"></span></div>
			<div class="digg_percent_num">'.$row['badper'].'%</div>
		</div>
	</div>';
	}
}
AjaxHead();
echo $digg;
exit();
?>