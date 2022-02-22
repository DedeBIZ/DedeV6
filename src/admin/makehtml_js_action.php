<?php
/**
 * 生成js操作
 *
 * @version        $Id: makehtml_js_action.php 1 11:04 2010年7月19日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_MakeHtml');
require_once(DEDEINC."/arc.partview.class.php");
if (empty($typeid)) $typeid = 0;

if (empty($templet)) $templet = "plus/js.htm";
if (empty($uptype)) $uptype = "all";

if ($uptype == "all") {
    $row = $dsql->GetOne("SELECT id FROM `#@__arctype` WHERE id>'$typeid' AND ispart<>2 ORDER BY id ASC LIMIT 0,1;");
    if (!is_array($row)) {
        echo "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/css/bootstrap.min.css\"><style>.modal {position: static;}</style>";
        echo "<div class=\"alert alert-success\" role=\"alert\">完成所有文件更新</div>";
        exit();
    } else {
        $pv = new PartView($row['id']);
        $pv->SetTemplet($cfg_basedir.$cfg_templets_dir."/".$templet);
        $pv->SaveToHtml($cfg_basedir.$cfg_cmspath."/data/js/".$row['id'].".js", 0);
        $typeid = $row['id'];;
        ShowMsg("成功更新".$cfg_cmspath."/data/js/".$row['id'].".js，继续进行操作", "makehtml_js_action.php?typeid=$typeid", 0, 100);
        exit();
    }
} else {
    $pv = new PartView($typeid);
    $pv->SetTemplet($cfg_basedir.$cfg_templets_dir."/".$templet);
    $pv->SaveToHtml($cfg_basedir.$cfg_cmspath."/data/js/".$typeid.".js", 0);
    echo "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/css/bootstrap.min.css\"><style>.modal {position: static;}</style>";
    echo "<div class=\"alert alert-success\" role=\"alert\">成功更新".$cfg_cmspath."/data/js/".$typeid.".js";
    echo "预览：</div>";
    echo "<hr>";
    echo "<script src='".$cfg_cmspath."/data/js/".$typeid.".js'></script>";
    exit();
}
