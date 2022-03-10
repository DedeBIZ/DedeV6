<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * SQL标签
 *
 * @version        $Id: sql.lib.php 2 10:00 2010-11-11 tianya $
 * @package        DedeBIZ.Taglib
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
function lib_sql(&$ctag, &$refObj)
{
    global $dsql, $sqlCt, $cfg_soft_lang;
    $attlist = "sql|appname";
    FillAttsDefault($ctag->CAttribute->Items, $attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    //传递环境参数
    preg_match_all("/~([A-Za-z0-9]+)~/s", $sql, $conditions);
    $appname = empty($appname) ? 'default' : $appname;
    if (is_array($conditions)) {
        foreach ($conditions[1] as $key => $value) {
            if (isset($refObj->Fields[$value])) {
                $sql = str_replace($conditions[0][$key], "'".addslashes($refObj->Fields[$value])."'", $sql);
            }
        }
    }
    $revalue = '';
    $Innertext = trim($ctag->GetInnerText());
    if ($sql == '' || $Innertext == '') return '';
    if (empty($sqlCt)) $sqlCt = 0;
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field', '[', ']');
    $ctp->LoadSource($Innertext);
    $thisrs = 'sq'.$sqlCt;
    $GLOBALS['autoindex'] = 0;
    //引入配置文件
    if ($appname != 'default') {
        require_once(DEDEDATA.'/tag/sql.inc.php');
        global $sqltag;
        $config = $sqltag[$appname];
        if (!isset($config['dbname'])) return '';
        //链接数据库
        $linkid = @mysql_connect($config['dbhost'], $config['dbuser'], $config['dbpwd']);
        if (!$linkid) return '';
        @mysql_select_db($config['dbname']);
        $mysqlver = explode('.', $dsql->GetVersion());
        $mysqlver = $mysqlver[0].'.'.$mysqlver[1];
        //设定数据库编码及长连接
        if ($mysqlver > 4.0) {
            @mysql_query("SET NAMES '".$config['dblanguage']."', character_set_client=binary, sql_mode='', interactive_timeout=3600 ;", $linkid);
        }
        $prefix = "#@__";
        $sql = str_replace($prefix, $config['dbprefix'], $sql);
        //校验SQL字符串并获取数组返回
        $sql = CheckSql($sql);
        $rs = @mysql_query($sql, $linkid);
        while ($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
            $sqlCt++;
            $GLOBALS['autoindex']++;
            //根据程序判断编码类型,并进行转码,这里主要就是gbk和utf-8
            if (substr($cfg_soft_lang, 0, 2) != substr($config['dblanguage'], 0, 2)) {
                $row = AutoCharset($row, $config['dblanguage'], $cfg_soft_lang);
            }
            foreach ($ctp->CTags as $tagid => $ctag) {
                if ($ctag->GetName() == 'array') {
                    $ctp->Assign($tagid, $row);
                } else {
                    if (!empty($row[$ctag->GetName()])) {
                        $ctp->Assign($tagid, $row[$ctag->GetName()]);
                    } else {
                        $ctp->Assign($tagid, "");
                    }
                }
            }
            $revalue .= $ctp->GetResult();
        }
        @mysql_free_result($rs);
    } else {
        $dsql->Execute($thisrs, $sql);
        while ($row = $dsql->GetArray($thisrs)) {
            $sqlCt++;
            $GLOBALS['autoindex']++;
            foreach ($ctp->CTags as $tagid => $ctag) {
                if ($ctag->GetName() == 'array') {
                    $ctp->Assign($tagid, $row);
                } else {
                    if (!empty($row[$ctag->GetName()])) {
                        $ctp->Assign($tagid, $row[$ctag->GetName()]);
                    } else {
                        $ctp->Assign($tagid, "");
                    }
                }
            }
            $revalue .= $ctp->GetResult();
        }
    }
    return $revalue;
}