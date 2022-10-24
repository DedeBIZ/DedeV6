<?php
/**
 * 系统配置
 *
 * @version        $Id: sys_info.php 1 22:28 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_Edit');
if (empty($dopost)) $dopost = "";
$configfile = DEDEDATA.'/config.cache.inc.php';
$dlang->extendLang('config'); //加载配置语言包
//更新配置函数
function ReWriteConfig()
{
    global $dsql, $configfile;
    if (!is_writeable($configfile)) {
        echo Lang('config_file_nowriteable',array('file'=>$configfile));
        exit();
    }
    $fp = fopen($configfile, 'w');
    flock($fp, 3);
    fwrite($fp, "<"."?php\r\n");
    $dsql->SetQuery("SELECT `varname`,`type`,`value`,`groupid` FROM `#@__sysconfig` ORDER BY aid ASC");
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        if ($row['type'] == 'number') {
            if ($row['value'] == '') $row['value'] = 0;
            fwrite($fp, "\${$row['varname']} = ".$row['value'].";\r\n");
        } else {
            $row['value'] = stripslashes($row['value']);
            fwrite($fp, "\${$row['varname']} = '".str_replace(array("'","\\"), '', $row['value'])."';\r\n");
        }
    }
    fwrite($fp, "?".">");
    fclose($fp);
}
//保存配置的改动
if ($dopost == "save") {
    CheckCSRF();
    foreach ($_POST as $k => $v) {
        if (preg_match("#^edit___#", $k)) {
            $v = cn_substrR(${$k}, 1024);
        } else {
            continue;
        }
        $k = preg_replace("#^edit___#", "", $k);
        
        $v = $dsql->Esc($v);
        $k = $dsql->Esc($k);
        $dsql->ExecuteNoneQuery("UPDATE `#@__sysconfig` SET `value`=$v WHERE varname=$k");
    }
    ReWriteConfig();
    ShowMsg(Lang("sys_info_success_save"), "sys_info.php");
    exit();
}
//增加新变量
else if ($dopost == 'add') {
    CheckCSRF();
    if ($vartype == 'bool' && ($nvarvalue != 'Y' && $nvarvalue != 'N')) {
        ShowMsg(Lang("sys_info_err_bool"), "-1");
        exit();
    }
    if (trim($nvarname) == '' || preg_match("#[^a-z_]#i", $nvarname)) {
        ShowMsg(Lang("sys_info_err_novarname_isempty"), "-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT varname FROM `#@__sysconfig` WHERE varname LIKE '$nvarname'");
    if (is_array($row)) {
        ShowMsg(Lang("sys_info_err_varname_exists"), "-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT aid FROM `#@__sysconfig` ORDER BY aid DESC");
    $aid = $row['aid'] + 1;
    $inquery = "INSERT INTO `#@__sysconfig` (`aid`,`varname`,`info`,`value`,`type`,`groupid`) VALUES ('$aid','$nvarname','$varmsg','$nvarvalue','$vartype','$vargroup')";
    $rs = $dsql->ExecuteNoneQuery($inquery);
    if (!$rs) {
        ShowMsg(Lang("sys_info_err_varname"), "sys_info.php?gp=$vargroup");
        exit();
    }
    if (!is_writeable($configfile)) {
        ShowMsg(Lang("sys_info_err_configfile",array('configfile'=>$configfile)), "sys_info.php?gp=$vargroup");
        exit();
    } else {
        ReWriteConfig();
        ShowMsg(Lang("sys_info_success"), "sys_info.php?gp=$vargroup");
        exit();
    }
}
//搜索配置
else if ($dopost == 'search') {
    $keywords = isset($keywords) ? strip_tags($keywords) : '';
    $i = 1;
    $config_varmsg = Lang('config_varmsg'); 
    $config_varvalue = Lang('config_varvalue'); 
    $config_varname = Lang('config_varname'); 
    $configstr = <<<EOT
<table width="100%" cellspacing="1" cellpadding="1" id="tdSearch">
    <tbody>
        <tr bgcolor="#f8fcf2" align="center">
            <td width="300">{$config_varmsg}</td>
            <td>{$config_varvalue}</td>
            <td width="220">{$config_varname}</td>
        </tr>
EOT;
    echo $configstr;
    if ($keywords) {
        $dsql->SetQuery("SELECT * FROM `#@__sysconfig` WHERE info LIKE '%$keywords%' OR varname LIKE '%$keywords%' ORDER BY aid ASC");
        $dsql->Execute();
        while ($row = $dsql->GetArray()) {
            $bgcolor = ($i++ % 2 == 0) ? "#f8f8f8" : "#ffffff";
            $row['info'] = Lang($row['varname']);
            $row['info'] = preg_replace("#{$keywords}#", '<span class="text-danger">'.$keywords.'</span>', $row['info']);
            $row['varname'] = preg_replace("#{$keywords}#", '<span class="text-danger">'.$keywords.'</span>', $row['varname']);
    ?>
    <tr align="center" bgcolor="<?php echo $bgcolor ?>">
        <td width="300"><?php echo $row['info'];?>：</td>
        <td align="left" style="padding:6px;">
            <?php
            if ($row['type'] == 'bool') {
                $c1 = '';
                $c2 = '';
                $row['value'] == 'Y' ? $c1 = " checked" : $c2 = " checked";
                echo "<label><input type='radio' name='edit___{$row['varname']}' value='Y'$c1> ".Lang('yes')." </label> ";
                echo "<label><input type='radio' name='edit___{$row['varname']}' value='N'$c2> ".Lang('no')." </label> ";
            } else if ($row['type'] == 'bstring') {
                echo "<textarea name='edit___{$row['varname']}' row='4' id='edit___{$row['varname']}' class='textarea_info' style='width:98%;height:50px'>".dede_htmlspecialchars($row['value'])."</textarea>";
            } else if ($row['type'] == 'number') {
                echo "<input type='text' name='edit___{$row['varname']}' id='edit___{$row['varname']}' value='{$row['value']}' style='width:30%'>";
            } else {
                echo "<input type='text' name='edit___{$row['varname']}' id='edit___{$row['varname']}' value=\"".dede_htmlspecialchars($row['value'])."\" style='width:80%'>";
            }
            ?>
        </td>
        <td><?php echo $row['varname'] ?></td>
    </tr>
<?php }?>
</table>
<?php
    exit;
}
if ($i == 1) {
    echo '<tr bgcolor="#f8f8f8" align="center"><td colspan="3">'.Lang('config_none_result').'</td></tr></table>';
}
exit;
} else if ($dopost == 'make_encode') {
    $chars = 'abcdefghigklmnopqrstuvwxwyABCDEFGHIGKLMNOPQRSTUVWXWY0123456789';
    $hash = '';
    $length = rand(28, 32);
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    echo $hash;
    exit();
}
include DedeInclude('templets/sys_info.htm');
?>