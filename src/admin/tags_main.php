<?php
/**
 * 标签管理
 *
 * @version        $id:tag_test_action.php 23:07 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
CheckPurview('sys_Keyword');
require_once(DEDEINC.'/datalistcp.class.php');
$timestamp = time();
if (empty($tag)) $tag = '';
if (empty($action)) {
    $orderby = empty($orderby) ? 'id' : preg_replace("#[^a-z]#i", '', $orderby);
    $orderway = isset($orderway) && $orderway == 'asc' ? 'asc' : 'desc';
    if (!empty($tag)) $where = " where tag like '%$tag%'";
    else $where = '';
    $neworderway = ($orderway == 'desc' ? 'asc' : 'desc');
    $query = "SELECT * FROM `#@__tagindex` $where ORDER BY $orderby $orderway";
    $dlist = new DataListCP();
    $tag = stripslashes($tag);
    $dlist->SetParameter("tag", $tag);
    $dlist->SetParameter("orderway", $orderway);
    $dlist->SetParameter("orderby", $orderby);
    $dlist->pagesize = 30;
    $dlist->SetTemplet(DEDEADMIN."/templets/tags_main.htm");
    $dlist->SetSource($query);
    $dlist->Display();
    exit();
}
/*
function update()
*/
else if ($action == 'update') {
    $tid = (empty($tid) ? 0 : intval($tid));
    $count = (empty($count) ? 0 : intval($count));
    if (empty($tid)) {
        ShowMsg('没有选择要删除的标签', '-1');
        exit();
    }
    $query = "UPDATE `#@__tagindex` SET `count`='$count' WHERE id='$tid' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功保存标签的点击信息", 'tags_main.php');
    exit();
}
/*
function delete()
*/
else if ($action == 'delete') {
    if (@is_array($ids)) {
        $stringids = implode(',', $ids);
    } else if (!empty($ids)) {
        $stringids = $ids;
    } else {
        ShowMsg('没有选择要删除的标签', '-1');
        exit();
    }
    $query = "DELETE FROM `#@__tagindex` WHERE id IN ($stringids)";
    if ($dsql->ExecuteNoneQuery($query)) {
        $query = "DELETE FROM `#@__taglist` WHERE tid IN ($stringids)";
        $dsql->ExecuteNoneQuery($query);
        ShowMsg("删除tags [$stringids] 成功", 'tags_main.php');
    } else {
        ShowMsg("删除tags [$stringids] 失败", 'tags_main.php');
    }
    exit();
} else if ($action == 'get_one') {
    $tid = (empty($tid) ? 0 : intval($tid));
    $row = $dsql->GetOne("SELECT * FROM `#@__tagindex` WHERE id = $tid");
    echo json_encode($row);
    exit;
} else if ($action == 'set_one') {
    $tid = (empty($tid) ? 0 : intval($tid));
    $title = empty($title) ? "" : HtmlReplace($title, 0);
    $kw = empty($kw) ? "" : HtmlReplace($kw, 0);
    $des = empty($des) ? "" : HtmlReplace($des, 0);
    $now = time();
    $dsql->ExecuteNoneQuery("UPDATE `#@__tagindex` SET title='{$title}',keywords='{$kw}',`description`='{$des}',`uptime`='{$now}' WHERE id = {$tid}");
    echo json_encode(array('code' => 200, 'result' => true));
}
/*
function fetch()
*/
else if ($action == 'fetch') {
    $wheresql = '';
    $start = isset($start) && is_numeric($start) ? $start : 0;
    $where = array();
    if (isset($startaid) && is_numeric($startaid) && $startaid > 0) {
        $where[] = " id>=$startaid ";
    } else {
        $startaid = 0;
    }
    if (isset($endaid) && is_numeric($endaid) && $endaid > 0) {
        $where[] = " id<=$endaid ";
    } else {
        $endaid = 0;
    }
    if (!empty($where)) {
        $wheresql = " WHERE arcrank>-1 AND ".implode(' AND ', $where);
    }
    $query = "SELECT id as aid,arcrank,typeid,keywords FROM `#@__archives` $wheresql LIMIT $start, 100";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $complete = true;
    $now = time();
    while ($row = $dsql->GetArray()) {
        $aid = $row['aid'];
        $typeid = $row['typeid'];
        $arcrank = $row['arcrank'];
        $row['keywords'] = trim($row['keywords']);
        if ($row['keywords'] != '' && !preg_match("#,#", $row['keywords'])) {
            $keyarr = explode(' ', $row['keywords']);
        } else {
            $keyarr = explode(',', $row['keywords']);
        }
        foreach ($keyarr as $keyword) {
            $keyword = trim($keyword);
            if ($keyword != '' && strlen($keyword) < 13) {
                $keyword = addslashes($keyword);
                $row = $dsql->GetOne("SELECT id,total FROM `#@__tagindex` WHERE tag LIKE '$keyword'");
                if (is_array($row)) {
                    $tid = $row['id'];
                    $trow = $dsql->GetOne("SELECT COUNT(*) as dd FROM `#@__taglist` WHERE tag LIKE '$keyword'");
                    if (intval($trow['dd']) != $row['total']) {

                        $query = "UPDATE `#@__tagindex` SET `total`=".$trow['dd'].",uptime=$now WHERE id='$tid' ";
                        $dsql->ExecuteNoneQuery($query);
                    }
                } else {
                    $query = "INSERT INTO `#@__tagindex` (`tag`,`count`,`total`,`weekcc`,`monthcc`,`weekup`,`monthup`,`addtime`,`uptime`) VALUES ('$keyword','0','1','0','0','$timestamp','$timestamp','$timestamp','$now');";
                    $dsql->ExecuteNoneQuery($query);
                    $tid = $dsql->GetLastID();
                }
                $query = "REPLACE INTO `#@__taglist` (`tid`,`aid`,`typeid`,`arcrank`,`tag`) VALUES ('$tid', '$aid', '$typeid','$arcrank','$keyword'); ";
                $dsql->ExecuteNoneQuery($query);
            }
        }
        $complete = FALSE;
    }
    if ($complete) {
        ShowMsg("tags获取完成", 'tags_main.php');
        exit();
    }
    $start = $start + 100;
    $goto = "tags_main.php?action=fetch&startaid=$startaid&endaid=$endaid&start=$start";
    ShowMsg('继续获取tags ', $goto);
    exit();
}
?>