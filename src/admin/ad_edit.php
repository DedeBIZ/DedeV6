<?php
/**
 * 修改广告
 *
 * @version        $id:ad_edit.php 8:26 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('plus_广告管理');
require_once(DEDEINC.'/typelink/typelink.class.php');
if (empty($dopost)) $dopost = '';
$aid = preg_replace("#[^0-9]#", '', $aid);
$ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? "ad_main.php" : $_COOKIE['ENV_GOBACK_URL'];
if ($dopost == 'delete') {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__myad` WHERE aid='$aid' ");
    ShowMsg("成功删除一则广告代码", $ENV_GOBACK_URL);
    exit();
} else if ($dopost == "gettag") {
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    $row = $dsql->GetOne("SELECT tagname FROM `#@__myad` WHERE aid='$aid' ");
    $tagcode = "{dede:myad name='{$row['tagname']}'/} <script src='{$cfg_phpurl}/ad_js.php?aid=$aid'></script>";
    $showhtml = "<xmp>$tagcode</xmp>";
    $showhtml .= "<iframe name='testfrm' frameborder='0' src='ad_edit.php?aid={$aid}&dopost=testjs' id='testfrm' width='100%' height='350'></iframe>";
    $wintitle = "广告调用";
    $win = new OxWindow();
    $winform = $win->GetWindow("hand", $showhtml);
    $win->Display();
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
    ShowMsg("成功修改一则广告代码", $ENV_GOBACK_URL);
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