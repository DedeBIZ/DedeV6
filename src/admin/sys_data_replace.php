<?php
/**
 * 数据库操作替换
 *
 * @version        $Id: sys_data_replace.php 1 22:28 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/config.php');
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
if ($cfg_dbtype == 'pgsql') {
    showMsg( Lang('sys_data_pgsql_tip',array('cfg_dbname'=>$cfg_dbname)), 'javascript:;');
    exit();
}
UserLogin::CheckPurview('sys_Data');
if (empty($action)) $action = '';
if (empty($action)) {
    require_once(DEDEADMIN."/templets/sys_data_replace.htm");
    exit();
}
//列出数据库表里的字段
else if ($action == 'getfields') {
    AjaxHead();
    $it = $dsql->GetTableFields($exptable);
    echo "<div style='margin-top:10px;padding:10px;background-color:#f8f8f8;border:1px solid #dee2e6'>";
    echo Lang('sys_data_getfields',array('exptable'=>$exptable));
    foreach ($it as $row) {
        echo "<a href=\"javascript:pf('{$row->name}')\">".$row->name."</a>\r\n";
    }
    echo "</div>";
    exit();
}
//保存用户设置，清空会员数据
else if ($action == 'apply') {
    $validate = empty($validate) ? '' : strtolower($validate);
    $svali = GetCkVdValue();
    if ($validate == "" || $validate != $svali) {
        ShowMsg(Lang("incorrect_verification_code"), "javascript:;");
        exit();
    }
    if ($exptable == '' || $rpfield == '') {
        ShowMsg(Lang("sys_data_err_exptable"), "javascript:;");
        exit();
    }
    if ($rpstring == '') {
        ShowMsg(Lang("sys_data_err_rpstring"), "javascript:;");
        exit();
    }
    if ($rptype == 'replace') {
        $condition = empty($condition) ? '' : " WHERE $condition ";
        $rs = $dsql->ExecuteNoneQuery("UPDATE $exptable SET $rpfield=REPLACE($rpfield,'$rpstring','$tostring') $condition");
        $dsql->ExecuteNoneQuery("OPTIMIZE TABLE `$exptable`");
        if ($rs) {
            ShowMsg(Lang("sys_data_replace_success"), "javascript:;");
            exit();
        } else {
            ShowMsg(Lang("sys_data_err_replace"), "javascript:;");
            exit();
        }
    } else {
        $condition = empty($condition) ? '' : " And $condition ";
        $rpstring = stripslashes($rpstring);
        $rpstring2 = str_replace("\\", "\\\\", $rpstring);
        $rpstring2 = str_replace("'", "\\'", $rpstring2);
        $dsql->SetQuery("SELECT $keyfield,$rpfield FROM $exptable WHERE $rpfield REGEXP '$rpstring2' $condition");
        $dsql->Execute();
        $tt = $dsql->GetTotalRow();
        if ($tt == 0) {
            ShowMsg(Lang("sys_data_err_none"), "javascript:;");
            exit();
        }
        $oo = 0;
        while ($row = $dsql->GetArray()) {
            $kid = $row[$keyfield];
            $rpf = preg_replace("#".$rpstring."#i", $tostring, $row[$rpfield]);
            $rs = $dsql->ExecuteNoneQuery("UPDATE $exptable SET $rpfield='$rpf' WHERE $keyfield='$kid'");
            if ($rs) {
                $oo++;
            }
        }
        $dsql->ExecuteNoneQuery("OPTIMIZE TABLE `$exptable`");
        ShowMsg(Lang('sys_data_replace_success_rs',array('tt'=>$tt,'oo'=>$oo)), "javascript:;");
        exit();
    }
}
?>