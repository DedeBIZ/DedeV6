<?php
/**
 * @version        $id:buy.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
CheckRank(0, 0);
$menutype = 'mydede';
$menutype_son = 'op';
$_menu_buy = true;
$myurl = $cfg_basehost.$cfg_member_dir.'/index.php?uid='.$cfg_ml->M_LoginID;
$moneycards = '';
$membertypes = '';
$dsql->SetQuery("SELECT * FROM `#@__moneycard_type`");
$dsql->Execute('mct');
while ($row = $dsql->GetObject('mct')) {
    $row->money = sprintf("%01.2f", $row->money);
    $moneycards .= "<tr>
    <td><input type='radio' name='pid' value='{$row->tid}'></td>
    <td>{$row->pname}</td>
    <td>{$row->num}个</td>
    <td>{$row->money}元</td>
    </tr>
    ";
}
$dsql->SetQuery("SELECT `#@__member_type`.*,`#@__arcrank`.membername,`#@__arcrank`.`money` as cm From `#@__member_type` LEFT JOIN `#@__arcrank` on `#@__arcrank`.`rank` = `#@__member_type`.`rank`");
$dsql->Execute('mt');
while ($row = $dsql->GetObject('mt')) {
    $row->money = sprintf("%01.2f", $row->money);
    $membertypes .= "<tr>
    <td><input type='radio' name='pid' value='{$row->aid}'></td>
    <td>{$row->pname}</td>
    <td>{$row->membername}</td>
    <td>{$row->exptime}</td>
    <td>{$row->money}元</td>
    </tr>
    ";
}
$tpl = new DedeTemplate();
$tpl->LoadTemplate(DEDEMEMBER.'/templets/buy.htm');
$tpl->Display();
?>