<?php
/**
 * 生成Tag
 *
 * @version        $Id: makehtml_taglist.php 1 11:17 2020年8月19日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
$tagid = isset($tagid)? (int)$tagid : 0;
$action = isset($action)? $action : '';
$search = isset($search)? HtmlReplace($search, 0)  : '';

if ($action == "search") {
    if (!empty($search)) {
        $sql="select * from #@__tagindex where tag like '%$search%' order by id desc"; 
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
    }

    exit;
}

include DedeInclude('templets/makehtml_taglist.htm');