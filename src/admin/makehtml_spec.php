<?php
/**
 * 生成专题
 *
 * @version        $Id: makehtml_spec.php 1 11:17 2010年7月19日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
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
    echo "<div class=\"alert alert-success\">成功生成所有专题列表 <a href='$rurl' target='_blank' class='btn btn-success btn-sm'>预览</a></div>";
    exit();
}
include DedeInclude('templets/makehtml_spec.htm');