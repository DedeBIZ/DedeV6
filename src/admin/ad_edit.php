<?php
/**
 * 广告编辑
 *
 * @version        $Id: ad_edit.php 1 8:26 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('plus_广告管理');
if (empty($dopost)) $dopost = '';
$aid = preg_replace("#[^0-9]#", '', $aid);
$ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? "ad_main.php" : $_COOKIE['ENV_GOBACK_URL'];
if ($dopost == 'delete') {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__myad` WHERE aid='$aid'");
    ShowMsg(Lang("ad_success_delete"), $ENV_GOBACK_URL);
    exit();
} else if ($dopost == "gettag") {
    $jscode = "<script src='{$cfg_phpurl}/ad_js.php?aid=$aid'></script>";
    $showhtml = "<xmp style='color:#333333;background-color:#ffffff'>\r\n\r\n$jscode\r\n\r\n</xmp>";
    $showhtml .= Lang('view')."：<iframe name='testfrm' frameborder='0' src='ad_edit.php?aid={$aid}&dopost=testjs' id='testfrm' width='100%' height='200'></iframe>";
    $row = $dsql->GetOne("SELECT tagname FROM `#@__myad` WHERE aid='$aid'");
    $showtag = '{'."dede:myad name='{$row['tagname']}'/".'}';
    $info = Lang("ad_info");
    $wintitle = Lang("ad_title");
    $wecome_info = "<a href='ad_main.php'>".Lang('ad_main')."</a>::".Lang('ad_main_getjs');
    DedeWin::Instance()->Init()->GetWindow("hand", $info)->AddTitle(Lang("ad_edit_title1"))
    ->GetWindow("hand", $showtag)->SetMyWinItem("")
    ->AddTitle(Lang("ad_edit_title2"))->GetWindow("hand", $showhtml)->Display();
    exit();
} else if ($dopost == 'testjs') {
    echo "<script src='{$cfg_phpurl}/ad_js.php?aid=$aid&nocache=1'></script>";
    exit();
} else if ($dopost == 'saveedit') {
    CheckCSRF();
    $starttime = GetMkTime($starttime);
    $endtime = GetMkTime($endtime);
    $query = "UPDATE `#@__myad` SET clsid='$clsid',typeid='$typeid',adname='$adname',timeset='$timeset',starttime='$starttime',endtime='$endtime',normbody='$normbody',expbody='$expbody' WHERE aid='$aid'";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg(Lang("ad_success_edit"), $ENV_GOBACK_URL);
    exit();
}
$row = $dsql->GetOne("SELECT * FROM `#@__myad` WHERE aid='$aid'");
$dsql->Execute('dd', 'SELECT * FROM `#@__myadtype` ORDER BY id DESC');
$option = '';
while ($arr = $dsql->GetArray('dd')) {
    if ($arr['id'] == $row['clsid']) {
        $option .= "<option value='{$arr['id']}' selected='selected'>{$arr['typename']}</option>\n\r";
    } else {
        $option .= "<option value='{$arr['id']}'>{$arr['typename']}</option>\n\r";
    }
}
include DedeInclude('templets/ad_edit.htm');
?>