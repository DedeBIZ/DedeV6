<?php
/**
 * 模板发送
 *
 * @version        $Id: select_templets_post.php 2022-07-01 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
$cfg_txttype = "htm|html|tpl|txt";
if (empty($uploadfile)) {
    $uploadfile = "";
}
if (!is_uploaded_file($uploadfile)) {
    ShowMsg(Lang("friendlink_err_imglogo_empty"), "-1");
    exit();
}
if (!preg_match("#^text#", $uploadfile_type)) {
    ShowMsg(Lang("dialog_template_err_upload"), "-1");
    exit();
}
if (!preg_match("#\.(".$cfg_txttype.")#i", $uploadfile_name)) {
    ShowMsg(Lang("dialog_template_err_format"), "-1");
    exit();
}
if ($filename =='') {
    $filename = $uploadfile_name;
}
$filename = trim(preg_replace("#[ \r\n\t\*\%\\\/\?><\|\":]{1,}#", '', $filename));
if ($filename == '' || !preg_match("#\.(".$cfg_txttype.")#i", $filename)) {
    ShowMsg(Lang("dialog_template_err_ftype"), "-1");
    exit();
}
$fullfilename = $cfg_basedir.$activepath."/".$filename;
move_uploaded_file($uploadfile, $fullfilename) or die(Lang('media_err_upload',array('filename'=>$fullfilename)));
@unlink($uploadfile);
ShowMsg(Lang("dialog_soft_success_upload"), "select_templets.php?comeback=".urlencode($filename)."&f=$f&activepath=".urlencode($activepath)."&d=".time());
exit();
?>