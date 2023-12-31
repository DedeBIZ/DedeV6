<?php
/**
 * 更新标签
 *
 * @version        $id:makehtml_taglist.php 11:17 2020年8月19日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
$tagid = isset($tagid)? (int)$tagid : 0;
$action = isset($action)? $action : '';
$search = isset($search)? HtmlReplace($search, 0)  : '';
if ($action == "search") {
    if (!empty($search)) {
        $sql="SELECT * FROM `#@__tagindex` WHERE tag like '%$search%' ORDER BY id DESC"; 
        $dsql->Execute('al',$sql);
        $result = array();
        while($row=$dsql->GetObject('al')){ 
            $row->text= $row->tag;    
            $result[] = $row;
        }
        $result = array(
            "code" => 200,
            "data" => $result,
        );
        echo json_encode($result);
        exit;
    }
    $result = array(
        "code" => 200,
        "data" => null,
    );
    echo json_encode($result);
    exit;
}
include DedeInclude('templets/makehtml_tag_list.htm');
?>