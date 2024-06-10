<?php
/**
 * 删除栏目
 *
 * @version        $id:catalog_del.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
//检查权限许可
CheckPurview('t_Del,t_AccDel');
require_once(DEDEINC.'/typelink/typeunit.class.admin.php');
require_once(DEDEINC.'/libraries/oxwindow.class.php');
$id = trim(preg_replace("#[^0-9]#", '', $id));
//检查栏目操作许可
CheckCatalog($id, "您无权删除本栏目");
if (empty($dopost)) $dopost = '';
if ($dopost == 'ok') {
    $ut = new TypeUnit();
    $ut->DelType($id, $delfile);
    UpDateCatCache();
    ShowMsg("成功删除一个栏目", "catalog_main.php");
    exit();
}
$dsql->SetQuery("SELECT typename,typedir FROM `#@__arctype` WHERE id=".$id);
$row = $dsql->GetOne();
$wintitle = "删除栏目";
$win = new OxWindow();
$win->Init('catalog_del.php', '/static/web/js/admin.blank.js', 'POST');
$win->AddHidden('id', $id);
$win->AddHidden('dopost', 'ok');
$win->AddTitle("您要确定要删除{$row['typename']}栏目吗");
$win->AddItem('栏目生成目录：', $row['typedir']);
$winform = $win->GetWindow('ok');
$win->Display();
?>