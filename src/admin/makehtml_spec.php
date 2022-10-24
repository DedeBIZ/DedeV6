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
use DedeBIZ\Archive\SpecView;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_MakeHtml');
if (empty($dopost)) $dopost = "";
if ($dopost == "ok") {
    $sp = new SpecView();
    $rurl = $sp->MakeHtml();
    echo "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/web/css/bootstrap.min.css\"><style>.modal {position: static;}</style>";
    echo "<div class=\"alert alert-success\">".Lang('makehtml_spec_make_success')." <a href='$rurl' target='_blank' class='btn btn-success btn-sm'>".Lang('view')."</a></div>";
    exit();
}
include DedeInclude('templets/makehtml_spec.htm');
?>