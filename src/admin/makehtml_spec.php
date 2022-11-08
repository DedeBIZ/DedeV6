<?php
/**
 * 生成专题
 *
 * @version        $id:makehtml_spec.php 11:17 2010年7月19日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_MakeHtml');
if (empty($dopost)) $dopost = "";
if ($dopost == "ok") {
    require_once(DEDEINC."/archive/specview.class.php");
    $sp = new SpecView();
    $rurl = $sp->MakeHtml(0);
    echo "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/web/css/bootstrap.min.css\"><style>.modal {position: static;}</style>";
    echo "<div class=\"alert alert-success\">完成所有专题更新，<a href='$rurl' target='_blank'>浏览专题</a></div>";
    exit();
}
include DedeInclude('templets/makehtml_spec.htm');
?>