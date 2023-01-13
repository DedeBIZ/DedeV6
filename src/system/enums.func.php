<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 联动菜单
 *
 * @version        $id:enums.func.php 2 13:19 2011-3-24 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
//弱不存在缓存文件则写入缓存
if (!file_exists(DEDEDATA.'/enums/system.php')) WriteEnumsCache();
/**
 *  更新枚举缓存
 *
 * @access    public
 * @param     string  $egroup  联动组
 * @return    string
 */
function WriteEnumsCache($egroup = '')
{
    global $dsql;
    $egroups = array();
    if ($egroup == '') {
        $dsql->SetQuery("SELECT egroup FROM `#@__sys_enum` GROUP BY egroup ");
    } else {
        $dsql->SetQuery("SELECT egroup FROM `#@__sys_enum` WHERE egroup='$egroup' GROUP BY egroup ");
    }
    $dsql->Execute('enum');
    while ($nrow = $dsql->GetArray('enum')) {
        $egroups[] = $nrow['egroup'];
    }
    foreach ($egroups as $egroup) {
        $cachefile = DEDESTATIC.'/enums/'.$egroup.'.json';
        $dsql->SetQuery("SELECT ename,evalue,issign FROM `#@__sys_enum` WHERE egroup='$egroup' ORDER BY disorder ASC, evalue ASC ");
        $dsql->Execute('enum');
        $issign = -1;
        $tenum = false; //三级联动标识
        $data = array();
        while ($nrow = $dsql->GetArray('enum')) {
            $data[$nrow['evalue']] = $nrow['ename'];
            if ($issign == -1) $issign = $nrow['issign'];
            if ($nrow['issign'] == 2) $tenum = true;
        }
        file_put_contents($cachefile,json_encode($data));
        if ($tenum) $dsql->ExecuteNoneQuery("UPDATE `#@__stepselect` SET `issign`=2 WHERE egroup='$egroup'; ");
    }
    return '成功更新所有枚举缓存';
}
/**
 *  获取联动表单两级数据的父类与子类
 *
 * @access    public
 * @param     string  $v
 * @return    array
 */
function GetEnumsTypes($v)
{
    $rearr['top'] = $rearr['son'] = 0;
    if ($v == 0) return $rearr;
    if ($v % 500 == 0) {
        $rearr['top'] = $v;
    } else {
        $rearr['son'] = $v;
        $rearr['top'] = $v - ($v % 500);
    }
    return $rearr;
}
/**
 *  获取枚举的select表单
 *
 * @access    public
 * @param     string  $egroup  联动组
 * @param     string  $evalue  联动值
 * @param     string  $formid  表单ID
 * @param     string  $seltitle  选择标题
 * @return    string  成功后返回一个枚举表单
 */
function GetEnumsForm($egroup, $evalue = 0, $formid = '', $seltitle = '')
{
    $cachefile = DEDESTATIC.'/enums/'.$egroup.'.json';
    $data = json_decode(file_get_contents($cachefile));
    foreach ($data as $key => $value) {
        ${'em_'.$egroup.'s'}[$key] = $value;
    }
    if ($formid == '') {
        $formid = $egroup;
    }
    $forms = "<select name='$formid' id='$formid' class='enumselect form-control'>\r\n";
    $forms .= "\t<option value='0' selected='selected'>请选择{$seltitle}</option>\r\n";
    foreach (${'em_'.$egroup.'s'} as $v => $n) {
        $prefix = ($v > 500 && $v % 500 != 0) ? '└─ ' : '';
        if (preg_match("#\.#", $v)) $prefix = '└── ';
        if ($v == $evalue) {
            $forms .= "\t<option value='$v' selected='selected'>$prefix$n</option>\r\n";
        } else {
            $forms .= "\t<option value='$v'>$prefix$n</option>\r\n";
        }
    }
    $forms .= "</select>";
    return $forms;
}
/**
 *  获取一级数据
 *
 * @access    public
 * @param     string    $egroup   联动组
 * @return    array
 */
function getTopData($egroup)
{
    $data = array();
    $cachefile = DEDESTATIC.'/enums/'.$egroup.'.json';
    $data = json_decode(file_get_contents($cachefile));
    foreach ($data as $key => $value) {
        ${'em_'.$egroup.'s'}[$key] = $value;
    }
    foreach (${'em_'.$egroup.'s'} as $k => $v) {
        if ($k >= 500 && $k % 500 == 0) {
            $data[$k] = $v;
        }
    }
    return $data;
}
/**
 *  获取数据的js二级联动代码
 *
 * @access    public
 * @param     string    $egroup   联动组
 * @return    string
 */
function GetEnumsJs($egroup)
{
    global ${'em_'.$egroup.'s'};
    $cachefile = DEDESTATIC.'/enums/'.$egroup.'.json';
    $data = json_decode(file_get_contents($cachefile));
    foreach ($data as $key => $value) {
        ${'em_'.$egroup.'s'}[$key] = $value;
    }
    $jsCode = "";
    $jsCode .= "em_{$egroup}s=new Array();\r\n";
    foreach (${'em_'.$egroup.'s'} as $k => $v) {
        //js中3级栏目存放到第二个key中去
        if (preg_match("#([0-9]{1,})\.([0-9]{1,})#", $k, $matchs)) {
            $valKey = $matchs[1] + $matchs[2] / 1000;
            $jsCode .= "em_{$egroup}s[{$valKey}]='$v';\r\n";
        } else {
            $jsCode .= "em_{$egroup}s[$k]='$v';\r\n";
        }
    }
    return $jsCode;
}

/**
 *  获取枚举的值
 *
 * @access    public
 * @param     string    $egroup   联动组
 * @param     string    $evalue   联动值
 * @return    string
 */
function GetEnumsValue($egroup, $evalue = 0)
{
    $cachefile = DEDESTATIC.'/enums/'.$egroup.'.json';
    $data = json_decode(file_get_contents($cachefile));
    foreach ($data as $key => $value) {
        ${'em_'.$egroup.'s'}[$key] = $value;
    }
    if (isset(${'em_'.$egroup.'s'}[$evalue])) {
        return ${'em_'.$egroup.'s'}[$evalue];
    } else {
        return "保密";
    }
}
?>