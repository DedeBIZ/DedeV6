<?php
/**
 * 更新专题
 *
 * @version        $id:makehtml_spec.php 11:17 2010年7月19日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_MakeHtml');
if (empty($dopost)) $dopost = '';
if ($dopost == "ok") {
    require_once(DEDEINC."/archive/specview.class.php");
    $sp = new SpecView();
    $rurl = $sp->MakeHtml(0);
    ShowMsg("完成所有专题更新，<a href='$rurl' target='_blank'>点击浏览</a>", "javascript:;");
    exit();
}
include DedeInclude('templets/makehtml_spec.htm');
?>