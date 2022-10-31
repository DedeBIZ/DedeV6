<?php
/**
 * @version        $Id: ajax_feedback.php 1 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
AjaxHead();
if ($myurl == '') exit('');
else {
    $uid  = $cfg_ml->M_LoginID;
    $face = $cfg_ml->fields['face'] == '' ? $GLOBALS['cfg_memberurl'].'/images/nopic.gif' : $cfg_ml->fields['face'];
    echo "用户名：{$cfg_ml->M_UserName} <input name='notuser' type='checkbox' id='notuser' value='1'> 匿名评论\r\n";
    if ($cfg_feedback_ck == 'Y') {
        echo "验证码：<input name='validate' type='text' id='validate' size='10' class='form-control text-uppercase'>";
        echo "<img src='{$cfg_cmsurl}/apps/vdimgck.php' id='validateimg' onclick='this.src=this.src+'?'' alt='验证码' title='验证码' />\r\n";
    }
}
?>