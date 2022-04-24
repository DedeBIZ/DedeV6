<?php
/**
 * 交易支付
 * 
 * @version        $Id: mypay.php 1 13:52 2010年7月9日Z tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
CheckRank(0, 0);
$menutype = 'mydede';
$menutype_son = 'op';
require_once(DEDEINC.'/datalistcp.class.php');
setcookie('ENV_GOBACK_URL', GetCurUrl(), time() + 3600, '/');
if (!isset($dopost)) $dopost = '';
if ($dopost == '') {
    $query = "SELECT * FROM `#@__member_operation` WHERE mid='".$cfg_ml->M_ID."' And product='archive' order by aid desc";
    $dlist = new DataListCP();
    $dlist->pagesize = 30;
    $dlist->SetTemplate(DEDEMEMBER.'/templets/mypay.htm');
    $dlist->SetSource($query);
    $dlist->Display();
} elseif ($dopost == 'del') {
    $ids = preg_replace("#[^0-9,]#", "", $ids);
    $query = "DELETE FROM `#@__member_operation` WHERE aid in($ids) And mid='{$cfg_ml->M_ID}' And product='archive'";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功删除指定的交易记录!", "mypay.php");
    exit();
}