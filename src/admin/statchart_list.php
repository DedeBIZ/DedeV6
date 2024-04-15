<?php
/**
 * 流量统计列表
 *
 * @version        $id:statchart.php 2024-04-15 xushubieli $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC.'/datalistcp.class.php');
//检查权限
if ($id == 0 && $reid == 0) {
    CheckPurview('c_List');
}
if (empty($mobile)) $mobile = '';
if ($dopost=="delete") {
    $ids = explode('`',$aids);
    $dquery = "";
    foreach ($ids as $id) {
        if ($dquery=="") $dquery .= "id='$id' ";
        else $dquery .= " OR id='$id' ";
    }
    if($dquery!="") $dquery = " WHERE ".$dquery;
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__statistics_detail` $dquery");
    ShowMsg("成功删除指定的记录", "statchart.php");
    exit();    
} else {
$addsql = " WHERE ip LIKE '%$ip%' ";
$sql = "SELECT * FROM `#@__statistics_detail` $addsql ORDER BY id DESC";
$dlist = new DataListCP();
//文档列表数
$dlist->pageSize = 30;
$tplfile = DEDEADMIN."/templets/statchart_list.htm";
$dlist->SetTemplate($tplfile);      //载入模板
$dlist->SetSource($sql);            //设定查询SQL
$dlist->Display();                  //显示
}
?>