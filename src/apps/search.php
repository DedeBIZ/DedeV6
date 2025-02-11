<?php
/**
 * 搜索页
 *
 * @version        $id:search.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
require_once(DEDEINC."/archive/searchview.class.php");
$pagesize = (isset($pagesize) && is_numeric($pagesize)) ? $pagesize : 10;
$typeid = (isset($typeid) && is_numeric($typeid)) ? $typeid : 0;
$channeltype = (isset($channeltype) && is_numeric($channeltype)) ? $channeltype : 0;
$kwtype = (isset($kwtype) && is_numeric($kwtype)) ? $kwtype : 0;
$mid = (isset($mid) && is_numeric($mid)) ? $mid : 0;
unset($typeArr);
if (!isset($orderby)) $orderby = '';
else $orderby = preg_replace("#[^a-z]#i", '', $orderby);
if (!isset($searchtype)) $searchtype = 'titlekeyword';
else $searchtype = preg_replace("#[^a-z]#i", '', $searchtype);
if (!isset($keyword)) {
    if (!isset($q)) $q = '';
    $keyword = $q;
}
$oldkeyword = $keyword = FilterSearch(stripslashes($keyword));
//查找栏目信息
if (empty($typeid)) {
    $typenameCacheFile = DEDEDATA.'/cache/typename.inc';
    if (!file_exists($typenameCacheFile) || filemtime($typenameCacheFile) < time() - (3600 * 24)) {
        $fp = fopen(DEDEDATA.'/cache/typename.inc', 'w');
        fwrite($fp, "<"."?php\r\n");
        $dsql->SetQuery("SELECT id,typename,channeltype FROM `#@__arctype`");
        $dsql->Execute();
        while ($row = $dsql->GetArray()) {
            fwrite($fp, "\$typeArr[{$row['id']}] = '{$row['typename']}';\r\n");
        }
        fwrite($fp, '?'.'>');
        fclose($fp);
    }
    //引入栏目缓存并看关键词是否有相关栏目文档
    require_once($typenameCacheFile);
    if (isset($typeArr) && is_array($typeArr)) {
        foreach ($typeArr as $id => $typename) {
            //$keywordn = str_replace($typename, ' ', $keyword);
            $keywordn = $keyword;
            if ($keyword != $keywordn) {
                $keyword = HtmlReplace($keywordn);
                $typeid = intval($id);
                break;
            }
        }
    }
}
$typeid = intval($typeid);
$keyword = addslashes(cn_substr($keyword, 30));
$typeid = intval($typeid);
if ($cfg_notallowstr != '' && preg_match("#".$cfg_notallowstr."#i", $keyword)) {
    ShowMsg("搜索关键词中存在非法文档，被系统禁止", "-1");
    exit();
}
if (($keyword != '' && strlen($keyword) < 2) && empty($typeid)) {
    ShowMsg('您输入文字太少了，请重新填写', '-1');
    exit();
}
if (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $keyword)) {
    showMsg('您输入信息不符合，请重新填写', '-1');
    exit();
}
//检查搜索间隔时间
$ip = GetIP();
$now = time();
$row = $dsql->GetOne("SELECT * FROM `#@__search_limits` WHERE ip='{$ip}'");
if (is_array($row)) {
    if (($now - $row['searchtime']) < $cfg_search_time) {
        ShowMsg("搜索间隔为".$cfg_search_time."秒，请稍后重试", "-1");
        exit;
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__search_limits` SET `searchtime`='{$now}' WHERE `ip`='{$ip}';");
} else {
    $dsql->ExecuteNoneQuery("INSERT INTO `#@__search_limits` (`ip`, `searchtime`) VALUES ('{$ip}', '{$now}');");
}
//开始时间
if (empty($starttime)) $starttime = -1;
else {
    $starttime = (is_numeric($starttime) ? $starttime : -1);
    if ($starttime > 0) {
        $dayst = GetMkTime("2008-1-2 0:0:0") - GetMkTime("2008-1-1 0:0:0");
        $starttime = time() - ($starttime * $dayst);
    }
}
$t1 = ExecTime();
$sp = new SearchView($typeid, $keyword, $orderby, $channeltype, $searchtype, $starttime, $pagesize, $kwtype, $mid);
$keyword = $oldkeyword;
$sp->Display();
?>