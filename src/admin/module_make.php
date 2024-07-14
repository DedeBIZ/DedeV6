<?php
/**
 * 模块生成
 *
 * @version        $id:module_make.php 14:17 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
@set_time_limit(0);
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/dedemodule.class.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('sys_module');
if (empty($action)) $action = '';
if ($action == '') {
    $modules = array();
    require_once(dirname(__FILE__)."/templets/module_make.htm");
    exit();
}
//生成项目
else if ($action == 'make') {
    require_once(DEDEINC.'/libraries/dedehttpdown.class.php');
    //校验私钥，确定贡献者身份
    $devURL = DEDECDNURL."/developers/$dev_id.json";
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($devURL);
    $devContent = $dhd->GetHtml();
    $devInfo = (array)json_decode($devContent);
    if (($devInfo['auth_at'] + 60 * 60 * 24 * 365) < time()) {
        ShowMsg("操作失败，登录DedeBIZ官网查看献者账号是否过期", "-1");
        exit();
    }
    $filelist = str_replace("\r", "\n", trim($filelist));
    $filelist = trim(preg_replace("#[\n]{1,}#", "\n", $filelist));
    if ($filelist == '') {
        ShowMsg("您没有指定模块的文件列表，因此不能创建项目", "-1");
        exit();
    }
    if (empty($dev_id)) {
        ShowMsg("贡献者id不能为空", "-1");
        exit();
    }
    if (empty($priv)) {
        ShowMsg("请填写贡献者私钥信息", "-1");
        exit();
    }
    if (strlen($modulname) > 150) {
        ShowMsg("模块名称过长", "-1");
        exit();
    }
    //校验私钥合法性
    $enstr = json_encode(array(
        "module_name" => $modulname,
        "dev_id" => $devInfo['dev_id'],
    ));
    //私钥加密模块信息
    openssl_private_encrypt($enstr, $encotent, $priv);
    $moduleInfo = base64url_encode($encotent);
    openssl_public_decrypt($encotent, $decontent, $devInfo['pub_key']);
    $minfo = (array)json_decode($decontent);
    if ($minfo['module_name'] != $modulname || $minfo['dev_id'] != $devInfo['dev_id']) {
        ShowMsg("贡献者私钥校验失败，请确保填写正确的贡献者私钥", "-1");
        exit();
    }
    //去除转义
    foreach ($_POST as $k => $v) $$k = stripslashes($v);
    if (!isset($autosetup)) $autosetup = 0;
    if (!isset($autodel)) $autodel = 0;
    $mdir = DEDEDATA.'/module';
    $hashcode = md5($modulname.$devInfo['dev_id']);
    $moduleFilename = $mdir.'/'.$hashcode.'.xml';
    $menustring = base64_encode($menustring);
    $indexurl = str_replace('=', '**', $indexurl);
    $dm = new DedeModule($mdir);
    if ($dm->HasModule($hashcode)) {
        $dm->Clear();
        ShowMsg("您指定同名模块已经存在，您要更新这个模块先删除：module/{$hashcode}.xml", "-1");
        exit();
    }
    $readmef = $setupf = $uninstallf = '';
    if (empty($readmetxt)) {
        move_uploaded_file($readme, $mdir."/{$hashcode}-r.html") or die("您没填写说明或上传说明文件");
        $readmef = $dm->GetEncodeFile($mdir."/{$hashcode}-r.html", TRUE);
    } else {
        $readmetxt = preg_replace("#[\r\n]{1,}#", "<br>\r\n", $readmetxt);
        $readmef = base64_encode(trim($readmetxt));
    }
    if ($autosetup == 0) {
        move_uploaded_file($setup, $mdir."/{$hashcode}-s.php") or die("您没上传，或系统无法把setup文件移动到模块目录");
        $setupf = $dm->GetEncodeFile($mdir."/{$hashcode}-s.php", TRUE);
    }
    if ($autodel == 0) {
        move_uploaded_file($uninstall, $mdir."/{$hashcode}-u.php") or die("您没上传，或系统无法把uninstall文件移动到模块目录");
        $uninstallf = $dm->GetEncodeFile($mdir."/{$hashcode}-u.php", TRUE);
    }
    if (trim($setupsql40) == '') $setupsql40 = '';
    else $setupsql40 = base64_encode(trim($setupsql40));
    //if (trim($setupsql41)=='') $setupsql41 = '';
    //else $setupsql41 = base64_encode(trim($setupsql41));
    if (trim($delsql) == '') $delsql = '';
    else $delsql = base64_encode(trim($delsql));
    $pub_key = base64url_encode($devInfo['pub_key']);
    $modulinfo = "<module>
<baseinfo>
name={$modulname}
dev_id={$devInfo['dev_id']}
pubkey={$pub_key}
info={$moduleInfo}
time={$mtime}
hash={$hashcode}
indexname={$indexname}
indexurl={$indexurl}
ismember={$ismember}
autosetup={$autosetup}
autodel={$autodel}
lang=utf-8
moduletype={$moduletype}
</baseinfo>
<systemfile>
<menustring>
$menustring
</menustring>
<readme>
{$readmef}
</readme>
<setupsql40>
$setupsql40
</setupsql40>
<delsql>
$delsql
</delsql>
<setup>
{$setupf}
</setup>
<uninstall>
{$uninstallf}
</uninstall>
<oldfilelist>
$filelist
</oldfilelist>
</systemfile>
";
    $filelists = explode("\n", $filelist);
    foreach ($filelists as $v) {
        $v = trim($v);
        if (!empty($v)) $dm->MakeEncodeFileTest(dirname(__FILE__), $v);
    }
    //测试无误后编译安装包
    $fp = fopen($moduleFilename, 'w');
    fwrite($fp, $modulinfo);
    fwrite($fp, "<modulefiles>\r\n");
    foreach ($filelists as $v) {
        $v = trim($v);
        if (!empty($v)) $dm->MakeEncodeFile(dirname(__FILE__), $v, $fp);
    }
    fwrite($fp, "</modulefiles>\r\n");
    fwrite($fp, "</module>\r\n");
    fclose($fp);
    ShowMsg("成功更新一个模块插件", "module_main.php");
    exit();
}
//修改项目
else if ($action == 'edit') {
    $filelist = str_replace("\r", "\n", trim($filelist));
    $filelist = trim(preg_replace("#[\n]{1,}#", "\n", $filelist));
    if ($filelist == "") {
        ShowMsg("您没有指定模块的文件列表，因此不能创建项目", "-1");
        exit();
    }
    if (empty($dev_id)) {
        ShowMsg("贡献者id不能为空", "-1");
        exit();
    }
    if (empty($priv)) {
        ShowMsg("请填写贡献者私钥信息", "-1");
        exit();
    }
    //校验私钥，确定贡献者身份
    $devURL = DEDECDNURL."/developers/$dev_id.json";
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($devURL);
    $devContent = $dhd->GetHtml();
    $devInfo = (array)json_decode($devContent);
    if (($devInfo['auth_at'] + 60 * 60 * 24 * 365) < time()) {
        ShowMsg("操作失败，登录DedeBIZ官网查看献者账号是否过期", "-1");
        exit();
    }
    if (strlen($modulname) > 150) {
        ShowMsg("模块名称过长", "-1");
        exit();
    }
    //校验私钥合法性
    $enstr = json_encode(array(
        "module_name" => $modulname,
        "dev_id" => $devInfo['dev_id'],
    ));
    //私钥加密模块信息
    openssl_private_encrypt($enstr, $encotent, $priv);
    $moduleInfo = base64url_encode($encotent);
    openssl_public_decrypt($encotent, $decontent, $devInfo['pub_key']);
    $minfo = (array)json_decode($decontent);
    if ($minfo['module_name'] != $modulname || $minfo['dev_id'] != $devInfo['dev_id']) {
        ShowMsg("贡献者私钥校验失败，请确保填写正确的贡献者私钥", "-1");
        exit();
    }
    //已经去除转义
    foreach ($_POST as $k => $v) $$k = stripslashes($v);
    if (!isset($autosetup)) $autosetup = 0;
    if (!isset($autodel)) $autodel = 0;
    $mdir = DEDEDATA.'/module';
    $hashcode = $hash;
    $moduleFilename = $mdir.'/'.$hashcode.'.xml';
    $modulname = str_replace('=', '', $modulname);
    $indexurl = str_replace('=', '**', $indexurl);
    $menustring = base64_encode($menustring);
    $dm = new DedeModule($mdir);
    $readmef = base64_encode($readmetxt);
    $setupf = $uninstallf = '';
    //编译setup文件
    if (is_uploaded_file($setup)) {
        move_uploaded_file($setup, $mdir."/{$hashcode}-s.php") or die("您没上传，或系统无法把setup文件移动到模块目录");
        $setupf = $dm->GetEncodeFile($mdir."/{$hashcode}-s.php", TRUE);
    } else {
        if ($autosetup == 0) $setupf = base64_encode($dm->GetSystemFile($hashcode, 'setup'));
    }
    //编译uninstall文件
    if (is_uploaded_file($uninstall)) {
        move_uploaded_file($uninstall, $mdir."/{$hashcode}-u.php") or die("您没上传，或系统无法把uninstall文件移动到模块目录");
        $uninstallf = $dm->GetEncodeFile($mdir."/{$hashcode}-u.php", true);
    } else {
        if ($autodel == 0) $uninstallf = base64_encode($dm->GetSystemFile($hashcode, 'uninstall'));
    }
    if (trim($setupsql40) == '') $setupsql40 = '';
    else $setupsql40 = base64_encode(htmlspecialchars_decode(trim($setupsql40)));
    //if (trim($setupsql41)=='') $setupsql41 = '';
    //else $setupsql41 = base64_encode(trim($setupsql41));
    if (trim($delsql) == '') $delsql = '';
    else $delsql = base64_encode(strip_tags(trim($delsql)));
    $pub_key = base64url_encode($devInfo['pub_key']);
    $modulinfo = "<module>
<baseinfo>
name={$modulname}
dev_id={$devInfo['dev_id']}
pubkey={$pub_key}
info={$moduleInfo}
time={$mtime}
hash={$hashcode}
indexname={$indexname}
indexurl={$indexurl}
ismember={$ismember}
autosetup={$autosetup}
autodel={$autodel}
lang=utf-8
moduletype={$moduletype}
</baseinfo>
<systemfile>
<menustring>
$menustring
</menustring>
<readme>
{$readmef}
</readme>
<setupsql40>
$setupsql40
</setupsql40>
<delsql>
$delsql
</delsql>
<setup>
{$setupf}
</setup>
<uninstall>
{$uninstallf}
</uninstall>
<oldfilelist>
$filelist
</oldfilelist>
</systemfile>
";
    if ($rebuild == 'yes') {
        $filelists = explode("\n", $filelist);
        foreach ($filelists as $v) {
            $v = trim($v);
            if (!empty($v)) $dm->MakeEncodeFileTest(dirname(__FILE__), $v);
        }
        //测试无误后编译安装包
        $fp = fopen($moduleFilename, 'w');
        fwrite($fp, $modulinfo."\r\n");
        fwrite($fp, "<modulefiles>\r\n");
        foreach ($filelists as $v) {
            $v = trim($v);
            if (!empty($v)) $dm->MakeEncodeFile(dirname(__FILE__), $v, $fp);
        }
        fwrite($fp, "</modulefiles>\r\n");
        fwrite($fp, "</module>\r\n");
        fclose($fp);
    } else {
        $fxml = $dm->GetFileXml($hashcode);
        $fp = fopen($moduleFilename, 'w');
        fwrite($fp, $modulinfo."\r\n");
        fwrite($fp, $fxml);
        fclose($fp);
    }
    ShowMsg("成功更新一个模块插件", "module_main.php");
    exit();
}//ClearAllLink();
?>