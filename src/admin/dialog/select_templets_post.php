<?php
/**
 * 选择模板操作
 *
 * @version        $id:select_templets_post.php 9:43 2010年7月8日 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
$cfg_txttype = "htm|html|tpl|txt";
if (empty($uploadfile)) {
    $uploadfile = '';
}
if (!is_uploaded_file($uploadfile)) {
    ShowMsg("您没有选择上传文件", "-1");
    exit();
}
if (!preg_match("#^text#", $uploadfile_type)) {
    ShowMsg("您上传的不是文本类型附件", "-1");
    exit();
}
if (!preg_match("#\.(".$cfg_txttype.")#i", $uploadfile_name)) {
    ShowMsg("您上传的模板文件类型存在问题，请使用htm、html、tpl、txt扩展名", "-1");
    exit();
}
if ($filename =='') {
    $filename = $uploadfile_name;
}
$filename = trim(preg_replace("#[ \r\n\t\*\%\\\/\?><\|\":]{1,}#", '', $filename));
if ($filename == '' || !preg_match("#\.(".$cfg_txttype.")#i", $filename)) {
    ShowMsg("您上传的文件存在问题，请检查文件类型", "-1");
    exit();
}
$fullfilename = $cfg_basedir.$activepath."/".$filename;
move_uploaded_file($uploadfile, $fullfilename) or die("上传文件到".$fullfilename."失败");
@unlink($uploadfile);
ShowMsg("成功上传文件", "select_templets.php?comeback=".urlencode($filename)."&f=$f&activepath=".urlencode($activepath)."&d=".time());
exit();
?>