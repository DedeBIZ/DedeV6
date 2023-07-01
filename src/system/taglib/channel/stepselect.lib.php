<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 获取二级枚举值
 *
 * @version        $id:stepselect.lib.php 16:24 2010年7月26日 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
function ch_stepselect($fvalue, &$arcTag, &$refObj, $fname = '')
{
    return GetEnumsValue2($fname, $fvalue);
}
function GetEnumsValue2($egroup, $evalue = 0)
{
    if (!isset($GLOBALS['em_'.$egroup.'s'])) {
        $cachefile = DEDESTATIC.'/enums/'.$egroup.'.json';
        $data = json_decode(file_get_contents($cachefile));
        foreach ($data as $key => $value) {
            $GLOBALS['em_'.$egroup.'s'][$key] = $value;
        }
    }
    if ($evalue >= 500) {
        if ($evalue % 500 == 0) {
            return (isset($GLOBALS['em_'.$egroup.'s'][$evalue]) ? $GLOBALS['em_'.$egroup.'s'][$evalue] : '');
        } else if (preg_match("#([0-9]{1,})\.([0-9]{1,})#", $evalue, $matchs)) {
            $esonvalue = $matchs[1];
            $etopvalue = $esonvalue - ($esonvalue % 500);
            $esecvalue = $evalue;
            $GLOBALS['em_'.$egroup.'s'][$etopvalue] = empty($GLOBALS['em_'.$egroup.'s'][$etopvalue]) ? ''
                : $GLOBALS['em_'.$egroup.'s'][$etopvalue];
            $GLOBALS['em_'.$egroup.'s'][$esonvalue] = empty($GLOBALS['em_'.$egroup.'s'][$esonvalue]) ? ''
                : $GLOBALS['em_'.$egroup.'s'][$esonvalue];
            $GLOBALS['em_'.$egroup.'s'][$esecvalue] = empty($GLOBALS['em_'.$egroup.'s'][$esecvalue]) ? ''
                : $GLOBALS['em_'.$egroup.'s'][$esecvalue];
            return $GLOBALS['em_'.$egroup.'s'][$etopvalue].' -- '.$GLOBALS['em_'.$egroup.'s'][$esonvalue].' -- '.$GLOBALS['em_'.$egroup.'s'][$esecvalue];
        } else {
            $elimit = $evalue % 500;
            $erevalue = $evalue - $elimit;
            $GLOBALS['em_'.$egroup.'s'][$erevalue] = empty($GLOBALS['em_'.$egroup.'s'][$erevalue]) ? ''
                : $GLOBALS['em_'.$egroup.'s'][$erevalue];
            $GLOBALS['em_'.$egroup.'s'][$evalue] = empty($GLOBALS['em_'.$egroup.'s'][$evalue]) ? ''
                : $GLOBALS['em_'.$egroup.'s'][$evalue];
            return $GLOBALS['em_'.$egroup.'s'][$erevalue].' -- '.$GLOBALS['em_'.$egroup.'s'][$evalue];
        }
    } else {
        return isset($GLOBALS['em_'.$egroup.'s'][$evalue])? $GLOBALS['em_'.$egroup.'s'][$evalue] : '';
    }
}
?>