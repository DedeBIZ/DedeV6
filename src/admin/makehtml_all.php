<?php
/**
 * 生成所有页面
 *
 * @version        $Id: makehtml_all.php 1 8:48 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Archive\PartView;
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/channel/channelunit.func.php");
$action = (empty($action) ? '' : $action);
if ($action == '') {
    require_once(DEDEADMIN."/templets/makehtml_all.htm");
    exit();
} else if ($action == 'make') {
    if (empty($step)) $step = 1;
    //更新文档前优化数据
    if ($step == 1) {
        $starttime = GetMkTime($starttime);
        $mkvalue = ($uptype == 'time' ? $starttime : $startid);
        OptimizeData($dsql);
        ShowMsg(Lang("makehtml_all_step_1"), "makehtml_all.php?action=make&step=2&uptype=$uptype&mkvalue=$mkvalue");
        exit();
    }
    //更新文档
    else if ($step == 2) {
        include_once(DEDEADMIN."/makehtml_archives_action.php");
        exit();
    }
    //更新主页
    if ($step == 3) {
        $pv = new PartView();
        $row = $pv->dsql->GetOne("SELECT * FROM `#@__homepageset`");
        $templet = str_replace("{style}", $cfg_df_style, $row['templet']);
        $homeFile = DEDEADMIN.'/'.$row['position'];
        $homeFile = str_replace("\\", '/', $homeFile);
        $homeFile = preg_replace("#\/{1,}#", '/', $homeFile);
        if ($row['showmod'] == 1) {
            $pv->SetTemplet($cfg_basedir.$cfg_templets_dir.'/'.$templet);
            $pv->SaveToHtml($homeFile);
            $pv->Close();
        } else {
            if (file_exists($homeFile)) echo Lang("makehtml_all_suggest_delete")+$homeFile;
        }
        ShowMsg(Lang("makehtml_all_step_3"), "makehtml_all.php?action=make&step=4&uptype=$uptype&mkvalue=$mkvalue");
        exit();
    }
    //更新栏目
    else if ($step == 4) {
        $mkvalue = intval($mkvalue);
        $typeidsok = $typeids = array();
        $adminID = $cUserLogin->getUserID();
        $mkcachefile = DEDEDATA."/mkall_cache_{$adminID}.php";
        if ($uptype == 'all' || empty($mkvalue)) {
            ShowMsg(Lang("makehtml_all_empty_aids"), "makehtml_list_action.php?gotype=mkallct");
            exit();
        } else {
            if ($uptype == 'time') {
                $query = "SELECT  DISTINCT typeid From `#@__arctiny` WHERE senddate >=".GetMkTime($mkvalue)." AND arcrank>-1";
            } else {
                $query = "SELECT DISTINCT typeid From `#@__arctiny` WHERE id>=$mkvalue AND arcrank>-1";
            }
            $dsql->SetQuery($query);
            $dsql->Execute();
            while ($row = $dsql->GetArray()) {
                $typeids[$row['typeid']] = 1;
            }
            foreach ($typeids as $k => $v) {
                $vs = array();
                $vs = GetParentIds($k);
                if (!isset($typeidsok[$k])) {
                    $typeidsok[$k] = 1;
                }
                foreach ($vs as $k => $v) {
                    if (!isset($typeidsok[$v])) {
                        $typeidsok[$v] = 1;
                    }
                }
            }
        }
        $fp = fopen($mkcachefile, 'w') or die(Lang('makehtml_all_err_cache',array('mkcachefile'=>$mkcachefile)));
        if (count($typeidsok) > 0) {
            fwrite($fp, "<"."?php\r\n");
            $i = -1;
            foreach ($typeidsok as $k => $t) {
                if ($k != '') {
                    $i++;
                    fwrite($fp, "\$idArray[$i]={$k};\r\n");
                }
            }
            fwrite($fp, "?".">");
            fclose($fp);
            ShowMsg(Lang("makehtml_all_step_4"), "makehtml_list_action.php?gotype=mkall");
            exit();
        } else {
            fclose($fp);
            ShowMsg(Lang("makehtml_all_no_to_opt"), "makehtml_all.php?action=make&step=10");
            exit();
        }
    }
    //成功状态
    else if ($step == 10) {
        $adminID = $cUserLogin->getUserID();
        $mkcachefile = DEDEDATA."/mkall_cache_{$adminID}.php";
        @unlink($mkcachefile);
        OptimizeData($dsql);
        ShowMsg(Lang("makehtml_all_step_10"), "javascript:;");
        exit();
    }
}
/**
 *  优化数据
 *
 * @access    public
 * @param     object  $dsql  数据库对象
 * @return    void
 */
function OptimizeData($dsql)
{
    global $cfg_dbprefix;
    $tptables = array("{$cfg_dbprefix}archives", "{$cfg_dbprefix}arctiny");
    $dsql->SetQuery("SELECT maintable,addtable FROM `#@__channeltype`");
    $dsql->Execute();
    while ($row = $dsql->GetObject()) {
        $addtable = str_replace('#@__', $cfg_dbprefix, $row->addtable);
        if ($addtable != '' && !in_array($addtable, $tptables)) $tptables[] = $addtable;
    }
    $tptable = '';
    foreach ($tptables as $t) $tptable .= ($tptable == '' ? "`{$t}`" : ",`{$t}`");
    $dsql->ExecuteNoneQuery("OPTIMIZE TABLE $tptable;");
}
?>