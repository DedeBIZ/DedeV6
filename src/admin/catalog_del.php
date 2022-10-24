<?php
/**
 * 删除栏目
 *
 * @version        $Id: catalog_del.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
use DedeBIZ\TypeLink\TypeUnitAdmin;
require_once(dirname(__FILE__).'/config.php');
//检查权限许可
UserLogin::CheckPurview('t_Del,t_AccDel');
$id = trim(preg_replace("#[^0-9]#", '', $id));
//检查栏目操作许可
UserLogin::CheckCatalog($id, Lang("catalog_err_delete_noperm"));
if (empty($dopost)) $dopost = '';
if ($dopost == 'ok') {
    $ut = new TypeUnitAdmin();
    $ut->DelType($id, $delfile);
    UpDateCatCache();
    ShowMsg(Lang("catalog_success_delete"), "catalog_main.php");
    exit();
}
$dsql->SetQuery("SELECT typename,typedir FROM `#@__arctype` WHERE id=".$id);
$row = $dsql->GetOne();
$wintitle = Lang("catalog_delete_confirm");
$wecome_info = "<a href='catalog_main.php'>".Lang('catalog_main')."</a> &gt; ".Lang("catalog_delete_confirm");
DedeWin::Instance()->Init('catalog_del.php', 'js/blank.js', 'POST')
->AddHidden('id', $id)
->AddHidden('dopost', 'ok')
->AddTitle(Lang('catalog_delete_confirm_title',array('typename'=>$row['typename'])))
->AddItem(Lang('catalog_delete_typedir'), $row['typedir'])
->AddItem(Lang('catalog_delete_file'), "<label><input type='radio' name='delfile' value='no' checked='1'> ".Lang("no")."</label> <label><input type='radio' name='delfile' value='yes'> ".Lang("yes")."</label>")
->GetWindow('ok')->Display();
?>