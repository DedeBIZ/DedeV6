<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * QRCode
 *
 * @version        $Id: qrcode.lib.php 1 9:29 2020年9月14日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

$GLOBALS['qrcode_id'] = isset($GLOBALS['qrcode_id']) ? $GLOBALS['qrcode_id'] : 1;
function lib_qrcode(&$ctag, &$refObj)
{
    //属性处理
    $attlist = "type|,id|";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    //var_dump($refObj->Fields['id']);
    $id = 0;
    if (empty($type) and empty($id)) {
        if (get_class($refObj) == 'Archives') {
            $type = 'arc';
            $id = $refObj->Fields['id'];
        } elseif (get_class($refObj) == 'ListView' or get_class($refObj) == 'SgListView') {
            $type = 'list';
            $id = $refObj->Fields['id'];
        } elseif (get_class($refObj) == 'PartView' and !empty($refObj->Fields['id'])) {
            $type = 'list';
            $id = $refObj->Fields['id'];
        } elseif (get_class($refObj) == 'PartView' and empty($refObj->Fields['id'])) {
            $type = 'index';
            $id = 0;
        }
    }
    $reval = <<<EOT
  <a href='https://www.dedebiz.com/' id='__dedeqrcode_{$GLOBALS['qrcode_id']}'>二维码生成</a>
  <script>
  	var __dedeqrcode_id={$GLOBALS['qrcode_id']};
  	var __dedeqrcode_aid={$id};
  	var __dedeqrcode_type='{$type}';
  	var __dedeqrcode_dir='{$GLOBALS['cfg_plus_dir']}';
  </script>
  <script src="{$GLOBALS['cfg_static_dir']}/web/js/qrcode.js"></script>
EOT;
    $GLOBALS['qrcode_id']++;
    return $reval;
}