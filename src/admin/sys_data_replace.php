<?php
/**
 * 数据库字段替换
 *
 * @version        $id:sys_data_replace.php 22:28 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
  }
CheckPurview('sys_Data');
if (empty($action)) $action = '';
if (empty($action)) {
    require_once(DEDEADMIN."/templets/sys_data_replace.htm");
    exit();
}
//列出数据库表里的字段
else if ($action == 'getfields') {
    AjaxHead();
    $dsql->GetTableFields($exptable);
    echo "<div class='alert alert-secondary'>";
    echo "<p>请选择".$exptable."表下面字段</p>";
    while ($row = $dsql->GetFieldObject()) {
        echo "<a href=\"javascript:pf('{$row->name}')\">".$row->name."</a> ";
    }
    echo "</div>";
    exit();
}
//保存会员设置，清空会员数据
else if ($action == 'apply') {
    $validate = empty($validate) ? '' : strtolower($validate);
    $svali = GetCkVdValue();
    if ($validate == "" || $validate != $svali) {
        ShowMsg("验证码不正确", "javascript:;");
        exit();
    }
    if ($exptable == '' || $rpfield == '') {
        ShowMsg("请指定数据表和字段", "javascript:;");
        exit();
    }
    if ($rpstring == '') {
        ShowMsg("请指定被替换文档", "javascript:;");
        exit();
    }
    if ($rptype == 'replace') {
        $condition = empty($condition) ? '' : " WHERE $condition ";
        $rs = $dsql->ExecuteNoneQuery("UPDATE $exptable SET $rpfield=REPLACE($rpfield,'$rpstring','$tostring') $condition ");
        $dsql->ExecuteNoneQuery("OPTIMIZE TABLE `$exptable`");
        if ($rs) {
            ShowMsg("成功完成数据替换", "javascript:;");
            exit();
        } else {
            ShowMsg("数据替换失败", "javascript:;");
            exit();
        }
    } else {
        $condition = empty($condition) ? '' : " And $condition ";
        $rpstring = stripslashes($rpstring);
        $rpstring2 = str_replace("\\", "\\\\", $rpstring);
        $rpstring2 = str_replace("'", "\\'", $rpstring2);
        $dsql->SetQuery("SELECT $keyfield,$rpfield FROM $exptable WHERE $rpfield REGEXP '$rpstring2'  $condition ");
        $dsql->Execute();
        $tt = $dsql->GetTotalRow();
        if ($tt == 0) {
            ShowMsg("根据您指定的正则，找不到任何东西", "javascript:;");
            exit();
        }
        $oo = 0;
        while ($row = $dsql->GetArray()) {
            $kid = $row[$keyfield];
            $rpf = preg_replace("#".$rpstring."#i", $tostring, $row[$rpfield]);
            $rs = $dsql->ExecuteNoneQuery("UPDATE $exptable SET $rpfield='$rpf' WHERE $keyfield='$kid' ");
            if ($rs) {
                $oo++;
            }
        }
        $dsql->ExecuteNoneQuery("OPTIMIZE TABLE `$exptable`");
        ShowMsg("共找到".$tt."条记录，成功替换了".$oo."条", "javascript:;");
        exit();
    }
}
?>