<?php
/**
 * 文档统计
 *
 * 如果想显示点击次数，请添加view参数，即把下面js调用放到文档模板适当位置
 * <script src="{dede:field name='phpurl'/}/count.php?view=yes&aid={dede:field name='id'/}&mid={dede:field name='mid'/}"></script>
 * 普通计数器为
 * <script src="{dede:field name='phpurl'/}/count.php?aid={dede:field name='id'/}&mid={dede:field name='mid'/}"></script>
 *
 * @version        $id:count.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
if (isset($aid)) $arcID = $aid;
$cid = empty($cid) ? 1 : intval($cid);
$arcID = $aid = empty($arcID) ? 0 : intval($arcID);
$format = isset($format) ? $format : "";
$maintable = '#@__archives';
$idtype = 'id';
if ($aid == 0) exit();
//获得栏目模型id
if ($cid < 0) {
    $row = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$cid' AND issystem='-1';");
    $maintable = empty($row['addtable']) ? '' : $row['addtable'];
    $idtype = 'aid';
}
$mid = (isset($mid) && is_numeric($mid)) ? $mid : 0;
//UpdateStat();
if (!empty($maintable)) {
    $dsql->ExecuteNoneQuery("UPDATE `{$maintable}` SET click=click+1 WHERE {$idtype}='$aid' ");
}
if (!empty($mid)) {
    $dsql->ExecuteNoneQuery("UPDATE `#@__member_tj` SET pagecount=pagecount+1 WHERE mid='$mid' ");
}
if (!empty($view)) {
    $row = $dsql->GetOne("SELECT click FROM `{$maintable}` WHERE {$idtype}='$aid' ");
    if (is_array($row)) {
        if (!empty($format)) {
            $result = array(
                "code" => 200,
                "data" => array(
                    'click' => $row['click'],
                ),
            );
            echo json_encode($result);
        } else {
            echo "document.write('".$row['click']."');\r\n";
        }
    }
}
exit();
?>