<?php
/**
 * 栏目页
 *
 * @version        $id:list.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
$t1 = ExecTime();
$tid = (isset($tid) && is_numeric($tid) ? $tid : 0);
$mod = (isset($mod) && is_numeric($mod) ? $mod : 0);
$channelid = (isset($channelid) && is_numeric($channelid) ? $channelid : 0);
if ($tid == 0 && $channelid == 0) die("dedebiz");
if (isset($TotalResult)) $TotalResult = intval(preg_replace("/[^\d]/", '', $TotalResult));
//如果指定了文档模型id但没有指定栏目id，那么自动获得为这个文档模型的第一个顶级栏目作为栏目默认栏目
if (!empty($channelid) && empty($tid)) {
    $tinfos = $dsql->GetOne("SELECT tp.id,ch.issystem FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.channeltype='$channelid' And tp.reid=0 order by sortrank asc");
    if (!is_array($tinfos)) die(" No catalogs in the channel! ");
    $tid = $tinfos['id'];
} else {
    $tinfos = $dsql->GetOne("SELECT ch.issystem FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id='$tid' ");
}
if ($tinfos['issystem'] == -1) {
    $nativeplace = ((empty($nativeplace) || !is_numeric($nativeplace)) ? 0 : $nativeplace);
    $infotype = ((empty($infotype) || !is_numeric($infotype)) ? 0 : $infotype);
    if (!empty($keyword)) $keyword = FilterSearch($keyword);
    $cArr = array();
    if (!empty($nativeplace)) $cArr['nativeplace'] = $nativeplace;
    if (!empty($infotype)) $cArr['infotype'] = $infotype;
    if (!empty($keyword)) $cArr['keyword'] = $keyword;
    include(DEDEINC."/archive/sglistview.class.php");
    $lv = new SgListView($tid, $cArr, $mod);
} else {
    include(DEDEINC."/archive/listview.class.php");
    $lv = new ListView($tid, 1, $mod);
}
//对设置了会员级别的栏目进行处理
if (isset($lv->Fields['corank']) && $lv->Fields['corank'] > 0) {
    require_once(DEDEINC.'/memberlogin.class.php');
    $cfg_ml = new MemberLogin();
    if ($cfg_ml->M_Rank < $lv->Fields['corank']) {
        $dsql->Execute('me', "SELECT * FROM `#@__arcrank`");
        while ($row = $dsql->GetObject('me')) {
            $memberTypes[$row->rank] = $row->membername;
        }
        $memberTypes[0] = "游客或没权限会员";
        $msgtitle = "您没有权限浏览栏目：{$lv->Fields['typename']}";
        $moremsg = "该栏目需要等级<span class='text-primary'>".$memberTypes[$lv->Fields['corank']]."</span>才能浏览，您目前等级是<span class='text-primary'>".$memberTypes[$cfg_ml->M_Rank]."</span><a href='{$cfg_memberurl}/buy.php' class='btn btn-success btn-sm ml-2'>升级会员</a>";
        include_once(DEDETEMPLATE.'/apps/view_msg_catalog.htm');
        exit();
    }
}
if ($lv->IsError) ParamError();
$lv->Display();
if (DEBUG_LEVEL === TRUE) {
    $queryTime = ExecTime() - $t1;
    echo DedeAlert("页面加载总消耗时间：{$queryTime}", ALERT_DANGER);
}
?>