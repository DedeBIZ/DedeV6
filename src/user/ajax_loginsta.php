<?php
/**
 * @version        $Id: ajax_loginsta.php 1 8:38 2010年7月9日Z tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
define('AJAXLOGIN', TRUE);
require_once(dirname(__FILE__)."/config.php");
AjaxHead();
$format = isset($format) ? "json" : "";
if (!$cfg_ml->IsLogin()) {
    if ($format === 'json') {
        echo json_encode(array(
            "code" => -1,
            "msg" => "尚未登录",
            "data" => null,
        ));
    } else {
        echo "";
    }
    exit;
}
$uid  = $cfg_ml->M_LoginID;
!$cfg_ml->fields['face'] && $face = ($cfg_ml->fields['sex'] == '女') ? 'dfgirl' : 'dfboy';
$facepic = empty($face) ? $cfg_ml->fields['face'] : $GLOBALS['cfg_memberurl'].'/templets/images/'.$face.'.png';
if ($format === 'json') {
    echo json_encode(array(
        "code" => 200,
        "msg" => "",
        "data" => array(
            "username" => $cfg_ml->M_UserName,
            "myurl" => $myurl,
            "facepic" => $facepic,
            "memberurl" => $cfg_memberurl,
        ),
    ));
    exit;
}
?>
<div class="userinfo">
    <div class="welcome">您好：<?php echo $cfg_ml->M_UserName; ?>，欢迎登录 </div>
    <div class="userface">
        <a href="<?php echo $cfg_memberurl; ?>/index.php"><img src="<?php echo $facepic; ?>" width="52" height="52" /></a>
    </div>
    <div class="uclink">
        <a href="<?php echo $cfg_memberurl; ?>/index.php">会员中心</a> |
        <a href="<?php echo $cfg_memberurl; ?>/edit_fullinfo.php">资料</a> |
        <a href="<?php echo $cfg_memberurl; ?>/index_do.php?fmdo=login&dopost=exit">退出登录</a>
    </div>
</div><!-- /userinfo -->