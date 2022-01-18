<?php if (!defined('DEDEINC')) exit("Request Error!");
/**
 * 栏目小助手,本文件仅做一个映射
 *
 * @version        $Id: channelunit.func.php 2 16:46 2010年7月6日Z tianya $
 * @package        DedeBIZ.Helpers
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

if (!isset($cfg_mainsite)) extract($GLOBALS, EXTR_SKIP);
global $PubFields, $pTypeArrays, $idArrary, $envs, $v1, $v2;

$pTypeArrays = $idArrary = $PubFields = $envs = array();
$PubFields['phpurl'] = $cfg_phpurl;
$PubFields['indexurl'] = $cfg_mainsite.$cfg_indexurl;
$PubFields['templeturl'] = $cfg_templeturl;
$PubFields['memberurl'] = $cfg_memberurl;
$PubFields['specurl'] = $cfg_specialurl;
$PubFields['indexname'] = $cfg_indexname;
$PubFields['templetdef'] = $cfg_templets_dir.'/'.$cfg_df_style;
$envs['typeid'] = 0;
$envs['reid'] = 0;
$envs['aid'] = 0;
$envs['keyword'] = '';
$envs['idlist'] = '';

helper('channelunit');
