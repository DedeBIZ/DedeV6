<?php
/**
 * 交易操作
 * 
 * @version        $id:search.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/datalistcp.class.php");
CheckRank(0, 0);
$menutype = 'mydede';
$menutype_son = 'op';
setcookie("ENV_GOBACK_URL", GetCurUrl(), time() + 3600, "/");
if (!isset($dopost)) $dopost = '';
function GetSta($sta)
{
    if ($sta == 0) return '未付款';
    else if ($sta == 1) return '已付款';
    else return '已完成';
}
if ($dopost == '') {
    $sql = "SELECT * FROM `#@__member_operation` WHERE mid='".$cfg_ml->M_ID."' AND product<>'archive' ORDER BY aid DESC";
    $dlist = new DataListCP();
    $dlist->pagesize = 10;
    $dlist->SetTemplate(DEDEMEMBER."/templets/operation.htm");
    $dlist->SetSource($sql);
    $dlist->Display();
} else if ($dopost == 'del') {
    $ids = preg_replace("#[^0-9,]#", "", $ids);
    $query = "DELETE FROM `#@__member_operation` WHERE aid IN($ids) AND mid='{$cfg_ml->M_ID}'";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功删除指定的交易记录", "operation.php");
    exit();
}
?>