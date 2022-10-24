<?php
/**
 * 多媒体发送
 *
 * @version        $Id: select_media_post.php 2022-07-01 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
include_once(dirname(__FILE__).'/config.php');
$cfg_softtype = $cfg_mediatype."|mp4";
$cfg_soft_dir = $cfg_other_medias;
$bkurl = 'select_media.php';
$uploadmbtype = Lang("dialog_media_post");
if (empty($activepath)) {
    $activepath = '';
    $activepath = str_replace('.', '', $activepath);
    $activepath = preg_replace("#\/{1,}#", '/', $activepath);
    if (strlen($activepath) < strlen($cfg_other_medias)) {
        $activepath = $cfg_other_medias;
    }
}
require_once(dirname(__FILE__)."/select_soft_post.php");
?>