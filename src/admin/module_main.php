<?php
/**
 * 模块管理
 *
 * @version        $Id: module_main.php 1 14:17 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_module');
require_once(DEDEINC."/dedemodule.class.php");
require_once(DEDEINC."/libraries/oxwindow.class.php");
if (empty($action)) $action = '';
require_once(DEDEDATA."/admin/config_update.php");
$mdir = DEDEDATA.'/module';
$mdurl = "";
function TestWriteAble($d)
{
    $tfile = '_dedet.txt';
    $d = preg_replace("#\/$#", '', $d);
    $fp = @fopen($d.'/'.$tfile, 'w');
    if (!$fp) return FALSE;
    else {
        fclose($fp);
        $rs = @unlink($d.'/'.$tfile);
        if ($rs) return TRUE;
      else return FALSE;
    }
}
function ReWriteConfigAuto()
{
    global $dsql;
    $configfile = DEDEDATA.'/config.cache.inc.php';
    if (!is_writeable($configfile)) {
        echo "配置文件 {$configfile} 不支持写入，无法修改系统配置参数";
        exit();
    }
    $fp = fopen($configfile, 'w');
    flock($fp, 3);
    fwrite($fp, "<"."?php\r\n");
    $dsql->SetQuery("SELECT `varname`,`type`,`value`,`groupid` FROM `#@__sysconfig` ORDER BY aid ASC ");
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        if(empty($row['value']) && $row['type'] == 'number') $row['value'] = 0;
        if ($row['type'] == 'number') fwrite($fp, "\${$row['varname']} = ".$row['value'].";\r\n");
        else fwrite($fp, "\${$row['varname']} = '".str_replace("'", '', $row['value'])."';\r\n");
      }
      fwrite($fp, "?".">");
      fclose($fp);
}
/*--------------
function ShowAll();
--------------*/
if ($action == '') {
    $types = array('soft' => '模块', 'templets' => '模板', 'plus' => '小插件', 'patch' => '补丁');
    $dm = new DedeModule($mdir);
    if (empty($moduletype)) $moduletype = '';
    $modules_remote = $dm->GetModuleUrlList($moduletype, $mdurl);
    $modules = array();
    $modules = $dm->GetModuleList($moduletype);
    is_array($modules) || $modules = array();
    if (is_array($modules_remote) && count($modules_remote) > 0) {
        $modules = array_merge($modules, $modules_remote);
    }
    require_once(dirname(__FILE__)."/templets/module_main.htm");
    $dm->Clear();
    exit();
}
/*--------------
function ViewDevelopoer();
--------------*/
else if ($action == 'view_developoer') {
    //检验开发者信息
    $dm = new DedeModule($mdir);
    $info = $dm->GetModuleInfo($hash);
    if ($info == null) {
        ShowMsg("获取模块信息错误，模块文件可能被篡改", -1);
        exit;
    }
    $dev_id = $info['dev_id'];
    $devURL = DEDECDNURL."/developers/$dev_id.json";
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($devURL);
    $devContent = $dhd->GetHtml();
    $devInfo = (array)json_decode($devContent);
    $offUrl = "";
    if ($devInfo['dev_type'] == 1) {
        $offUrl = "<p>官方网址：<code>{$devInfo['offurl']}</code><small>（复制在浏览器中打开）</small></p>";
    }
    $authAt = date("Y-m-d", $devInfo['auth_at']);
    if (!isset($info['dev_id'])) {
        $devInfo['dev_name'] = $info['team']."<span style='display:inline-block;margin-left:10px;padding:.25rem .5rem;line-height:1.5;font-size:12px;color:#fff;background:#dc3545;border-color:#dc3545;border-radius:.2rem'>未认证</span>";
        $authAt = "未知";
    }
    ShowMsg("<p>开发者名称：{$devInfo['dev_name']}</p><p>开发者ID：{$devInfo['dev_id']}</p><span>认证于：{$authAt}</span>", "-1");
    exit;
}
/*--------------
function Setup();
--------------*/
else if ($action == 'setup') {
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);
    if ($infos == null) {
        ShowMsg("获取模块信息错误，模块文件可能被篡改", -1);
        exit;
    }
    $alertMsg = ($infos['lang'] == $cfg_soft_lang ? '' : '<br>（这个模块的语言编码与您系统的编码不一致，请向开发者确认它的兼容性）');
    $filelists = (array)$dm->GetFileLists($hash);
    $filelist = '';
    $prvdirs = array();
    $incdir = array();
    foreach ($filelists as $v) {
        if (empty($v['name'])) continue;
        if ($v['type'] == 'dir') {
            $v['type'] = '目录';
            $incdir[] = $v['name'];
        } else {
            $v['type'] = '文件';
        }
        $filelist .= "{$v['type']}|{$v['name']}\r\n";
    }
    //检测需要的目录权限
    foreach ($filelists as $v) {
        $prvdir = preg_replace("#\/([^\/]*)$#", '/', $v['name']);
        if (!preg_match("#^\.#", $prvdir)) $prvdir = './';
        $n = TRUE;
        foreach ($incdir as $k => $v) {
            if (preg_match("#^".$v."#i", $prvdir)) {
                $n = FALSE;
                break;
            }
        }
        if (!isset($prvdirs[$prvdir]) && $n && is_dir($prvdir)) {
            $prvdirs[$prvdir][0] = 1;
            $prvdirs[$prvdir][1] = TestWriteAble($prvdir);
        }
    }
    $prvdir = "<table cellpadding='1' cellspacing='1' width='350' style='margin-top:10px'>\r\n";
    $prvdir .= "<tr style='background:#F8FCF1'><th width='270'>目录</td><th align='center'>可写</td></tr>\r\n";
    foreach ($prvdirs as $k => $v) {
        if ($v) $cw = '√';
        else $cw = '<span style="color:#dc3545">×</span>';
        $prvdir .= "<tr><td>$k</td>";
        $prvdir .= "<td align='center'>$cw</td></tr>\r\n";
    }
    $prvdir .= "</table>";
    $win = new OxWindow();
    $win->Init("module_main.php", "js/blank.js", "post");
    $wecome_info = "模块管理";
    $devURL = DEDECDNURL."/developers/{$infos['dev_id']}.json";
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($devURL);
    $devContent = $dhd->GetHtml();
    $devInfo = (array)json_decode($devContent);
    $s = "未认证";
    if (($devInfo['dev_id'] == $infos['dev_id']) && !empty($devInfo['dev_id'])) {
      $s = "已认证";
    }
    $win->AddTitle("&nbsp;<a href='module_main.php'>模块管理</a> &gt; 安装模块：{$infos['name']}");
    $win->AddHidden("hash", $hash);
    $win->AddHidden("action", 'setupstart');
    $msg = "<style>.dtb{border-bottom:1px dotted #eee}</style>
    <table width='98%' cellspacing='0' cellpadding='0' class='table'>
  <tr>
    <td width='260' height='26' class='dtb'>模块名称：</td>
    <td class='dtb'>{$infos['name']}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>语言：</td>
    <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>文件大小：</td>
    <td class='dtb'>{$infos['filesize']}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>开发者ID：</td>
    <td class='dtb'>{$infos['dev_id']} <a href='{$cfg_biz_dedebizUrl}/developer?dev_id={$infos['dev_id']}' target='_blank' class='btn btn-success btn-sm'>{$s}</a></td>
  </tr>
  <tr>
    <td height='26' class='dtb'>发布时间：</td>
    <td class='dtb'>{$infos['time']}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>使用协议：</td>
    <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank' class='btn btn-success btn-sm'>浏览</a></td>
  </tr>
  <tr>
    <td height='26' class='dtb' colspan='2'>
    <div class='alert alert-danger mb-0'>注意事项：安装时请确保文件列表中涉及的目录前可写入权限，此外“后台管理目录”、“后台管理目录/templets”目录也必须暂时设置可写入权限</div>
    </td>
  </tr>
  <tr>
    <td height='26'>目录权限检测：<br> ../ 为根目录 <br> ./ 表示当前目录</td>
    <td>$prvdir</td>
  </tr>
  <tr>
    <td height='26'>模块包含的所有文件列表：</td>
    <td></td>
  </tr>
  <tr>
    <td height='160' colspan='2'>
     <textarea name='filelists' id='filelists' style='width:98%;height:160px'>{$filelist}</textarea>
    </td>
  </tr>
  <tr>
    <td height='26'>对于已存在文件处理方法：</td>
    <td>
    <label><input name='isreplace' type='radio' value='1' checked='checked'> 覆盖</label>
    <label><input name='isreplace' type='radio' value='3'> 覆盖，保留副本</label>
    <label><input type='radio' name='isreplace' value='0'> 保留旧文件</label>
   </td>
  </tr>
</table>";
    $win->AddMsgItem("<div style='line-height:26px'>$msg</div>");
    $winform = $win->GetWindow("ok", "");
    $win->Display();
    $dm->Clear();
    exit();
}
/*---------------
function SetupRun()
--------------*/
else if ($action == 'setupstart') {
    if (!is_writeable($mdir)) {
        ShowMsg("目录 {$mdir} 不支持写入，这将导致程序安装没法正常创建", "-1");
        exit();
    }
    $dm = new DedeModule($mdir);
    $minfos = (array)$dm->GetModuleInfo($hash);
    extract($minfos, EXTR_SKIP);
    $menustring = addslashes($dm->GetSystemFile($hash, 'menustring'));
    $indexurl = str_replace('**', '=', $indexurl);
    $query = "INSERT INTO `#@__sys_module`(`hashcode` , `modname` , `indexname` , `indexurl` , `ismember` , `menustring` )
        VALUES ('$hash' , '$name' , '$indexname' , '$indexurl' , '$ismember' , '$menustring' ) ";
    $rs = $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_module` WHERE hashcode LIKE '$hash' ");
    $rs = $dsql->ExecuteNoneQuery($query);
    if (!$rs) {
        ShowMsg('保存数据库信息失败，无法完成安装'.$dsql->GetError(), 'javascript:;');
        exit();
    }
    $dm->WriteFiles($hash, $isreplace);
    $filename = '';
    if (!isset($autosetup) || $autosetup == 0) $filename = $dm->WriteSystemFile($hash, 'setup');
    if (!isset($autodel) || $autodel == 0) $dm->WriteSystemFile($hash, 'uninstall');
    $dm->WriteSystemFile($hash, 'readme');
    $dm->Clear();
    //用模块的程序安装安装
    if (!isset($autosetup) || $autosetup == 0) {
        include(DEDEDATA.'/module/'.$filename);
        exit();
    }
    //系统自动安装
    else {
        $mysql_version = $dsql->GetVersion(TRUE);
        //默认使用MySQL 4.1 以下版本的SQL语句，对大于4.1版本采用替换处理 TYPE=MyISAM ==> ENGINE=MyISAM DEFAULT CHARSET=#~lang~#
        $setupsql = $dm->GetSystemFile($hash, 'setupsql40');
        $setupsql = preg_replace("#ENGINE=MyISAM#i", 'TYPE=MyISAM', $setupsql);
        $sql41tmp = 'ENGINE=MyISAM DEFAULT CHARSET='.$cfg_db_language;
        if ($mysql_version >= 4.1) {
            $setupsql = preg_replace("#TYPE=MyISAM#i", $sql41tmp, $setupsql);
        }
        //_ROOTURL_
        if ($cfg_cmspath == '/') $cfg_cmspath = '';
        $rooturl = $cfg_basehost.$cfg_cmspath;
        $setupsql = preg_replace("#_ROOTURL_#i", $rooturl, $setupsql);
        $setupsql = preg_replace("#[\r\n]{1,}#", "\n", $setupsql);
        $sqls = @split(";[ \t]{0,}\n", $setupsql);
        foreach ($sqls as $sql) {
            if (trim($sql) != '') $dsql->ExecuteNoneQuery($sql);
        }
        ReWriteConfigAuto();
        $rflwft = "<script language='javascript' type='text/javascript'>\r\n";
        $rflwft .= "if(window.navigator.userAgent.indexOf('MSIE')>=1) top.document.frames.menu.location = 'index_menu_module.php';\r\n";
        $rflwft .= "else top.document.getElementById('menufra').src = 'index_menu_module.php';\r\n";
        $rflwft .= "</script>";
        echo $rflwft;
        UpDateCatCache();
        ShowMsg('模块安装完成', 'module_main.php');
        exit();
    }
}
/*--------------
function DelModule();
--------------*/
else if ($action == 'del') {
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);
    $alertMsg = ($infos['lang'] == $cfg_soft_lang ? '' : '<br>（这个模块的语言编码与您系统的编码不一致，请向开发者确认它的兼容性）');
    $dev_id = empty($infos['dev_id'])? "<a href='{$cfg_biz_dedebizUrl}/developer' target='_blank' class='btn btn-danger btn-sm'>未认证</a>" : "{$infos['dev_id']} <a href='{$cfg_biz_dedebizUrl}/developer?dev_id={$infos['dev_id']}' target='_blank' class='btn btn-danger btn-sm'>已认证</a>";
    $win = new OxWindow();
    $win->Init("module_main.php", "js/blank.js", "post");
    $wecome_info = "模块管理";
    $win->AddTitle("<a href='module_main.php'>模块管理</a> &gt; 删除模块：{$infos['name']}");
    $win->AddHidden('hash', $hash);
    $win->AddHidden('action', 'delok');
    $msg = "<style>.dtb{border-bottom:1px dotted #eee}</style>
    <table width='98%' cellspacing='0' cellpadding='0' class='table'>
    <tr>
      <td width='260' height='26' class='dtb'>模块名称：</td>
      <td class='dtb'>{$infos['name']}</td>
    </tr>
    <tr>
      <td height='26' class='dtb'>语言：</td>
      <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
    </tr>
    <tr>
      <td height='26' class='dtb'>文件大小：</td>
      <td class='dtb'>{$infos['filesize']}</td>
    </tr>
    <tr>
      <td height='26' class='dtb'>开发者ID：</td>
      <td class='dtb'>{$dev_id}</td>
    </tr>
    <tr>
      <td height='26' class='dtb'>发布时间：</td>
      <td class='dtb'>{$infos['time']}</td>
    </tr>
    <tr>
      <td height='26' class='dtb'>使用协议：</td>
      <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank' class='btn btn-success btn-sm'>浏览</a></td>
    </tr>
    <tr>
      <td height='26' colspan='2'>删除模块仅删除这个模块的安装包文件，如果您已经安装，请执行<a href='module_main.php?hash={$hash}&action=uninstall'>卸载程序</a>来删除</td>
    </tr>
</table>";
    $win->AddMsgItem("<div style='line-height:26px'>$msg</div>");
    $winform = $win->GetWindow("ok", "");
    $win->Display();
    $dm->Clear();
    exit();
    } else if ($action == 'delok') {
    $dm = new DedeModule($mdir);
    $modfile = $mdir."/".$dm->GetHashFile($hash);
    unlink($modfile) or die("删除文件 {$modfile} 失败");
    ShowMsg("成功删除一个模块文件", "module_main.php");
    exit();
}
/*--------------
function UnInstall();
--------------*/
else if ($action == 'uninstall') {
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);
    if ($infos['url'] == '') $infos['url'] = '&nbsp;';
    $alertMsg = ($infos['lang'] == $cfg_soft_lang ? '' : '<br>（这个模块的语言编码与您系统的编码不一致，请向开发者确认它的兼容性）');
    $filelists = (array)$dm->GetFileLists($hash);
    $filelist = '';
    foreach ($filelists as $v) {
        if (empty($v['name'])) continue;
        if ($v['type'] == 'dir') $v['type'] = '目录';
        else $v['type'] = '文件';
        $filelist .= "{$v['type']}|{$v['name']}\r\n";
    }
    $dev_id = empty($infos['dev_id'])? "<a href='{$cfg_biz_dedebizUrl}/developer' target='_blank' class='btn btn-danger btn-sm'>未认证</a>" : "{$infos['dev_id']} <a href='{$cfg_biz_dedebizUrl}/developer?dev_id={$infos['dev_id']}' target='_blank' class='btn btn-danger btn-sm'>已认证</a>";
    $win = new OxWindow();
    $win->Init("module_main.php", "js/blank.js", "post");
    $wecome_info = "模块管理";
    $win->AddTitle("<a href='module_main.php'>模块管理</a> &gt; 卸载模块：{$infos['name']}");
    $win->AddHidden("hash", $hash);
    $win->AddHidden("action", 'uninstallok');
    $msg = "<style>.dtb{border-bottom:1px dotted #eee}</style>
    <table width='98%' cellspacing='0' cellpadding='0' class='table'>
  <tr>
    <td width='260' height='26' class='dtb'>模块名称：</td>
    <td class='dtb'>{$infos['name']}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>语言：</td>
    <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>文件大小：</td>
    <td class='dtb'>{$infos['filesize']}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>开发者ID：</td>
    <td class='dtb'>{$dev_id}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>发布时间：</td>
    <td class='dtb'>{$infos['time']}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>使用协议：</td>
    <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank' class='btn btn-success btn-sm'>浏览</a></td>
  </tr>
  <tr>
    <td height='26'>模块包含的文件（文件路径相对于当前目录）</td>
    <td></td>
  </tr>
  <tr>
    <td height='160' colspan='2'>
     <textarea name='filelists' id='filelists' style='width:98%;height:160px'>{$filelist}</textarea>
    </td>
  </tr>
  <tr>
    <td height='26'>对于模块的文件处理方法：</td>
    <td>
    <label><input type='radio' name='isreplace' value='0' checked='checked'> 手工删除文件，仅运行卸载程序</label>
    <label><input name='isreplace' type='radio' value='2'> 删除模块的所有文件</label>
   </td>
  </tr>
</table>";
    $win->AddMsgItem("<div style='line-height:26px'>$msg</div>");
    $winform = $win->GetWindow("ok", "");
    $win->Display();
    $dm->Clear();
    exit();
}
/*--------------
function UnInstallRun();
--------------*/
else if ($action == 'uninstallok') {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_module` WHERE hashcode LIKE '$hash' ");
    $dm = new DedeModule($mdir);
    $minfos = (array)$dm->GetModuleInfo($hash);
    extract($minfos, EXTR_SKIP);
    if (!isset($moduletype) || $moduletype != 'patch') {
        $dm->DeleteFiles($hash, $isreplace);
    }
    @$dm->DelSystemFile($hash, 'readme');
    @$dm->DelSystemFile($hash, 'setup');
    $dm->Clear();
    if (!isset($autodel) || $autodel == 0) {
        include(DEDEDATA."/module/{$hash}-uninstall.php");
        @unlink(DEDEDATA."/module/{$hash}-uninstall.php");
        exit();
    } else {
        @$dm->DelSystemFile($hash, 'uninstall');
        $delsql = $dm->GetSystemFile($hash, 'delsql');
        if (trim($delsql) != '') {
            $sqls = explode(';', $delsql);
            foreach ($sqls as $sql) {
                if (trim($sql) != '') $dsql->ExecuteNoneQuery($sql);
            }
        }
        ReWriteConfigAuto();
        $rflwft = "<script language='javascript' type='text/javascript'>\r\n";
        $rflwft .= "if(window.navigator.userAgent.indexOf('MSIE')>=1) top.document.frames.menu.location = 'index_menu_module.php';\r\n";
        $rflwft .= "else top.document.getElementById('menufra').src = 'index_menu_module.php';\r\n";
        $rflwft .= "</script>";
        echo $rflwft;
        ShowMsg('模块卸载完成', 'module_main.php');
        exit();
    }
}
/*--------------
function ShowReadme();
--------------*/
else if ($action == 'showreadme') {
    $dm = new DedeModule($mdir);
    $msg = $dm->GetSystemFile($hash, 'readme');
    $msg = preg_replace("/(.*)<body/isU", "", $msg);
    $msg = preg_replace("/<\/body>(.*)/isU", "", $msg);
    $dm->Clear();
    $win = new OxWindow();
    $win->Init("module_main.php", "js/blank.js", "post");
    $wecome_info = "模块管理";
    $win->AddTitle("<a href='module_main.php'>模块管理</a> &gt; 使用说明：");
    $win->AddMsgItem("<div style='line-height:26px'>$msg</div>");
    $winform = $win->GetWindow("hand");
    $win->Display();
    exit();
}
/*--------------
function ViewOne();
--------------*/
else if ($action == 'view') {
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);
    if ($infos['url'] == '') $infos['url'] = '&nbsp;';
    $alertMsg = ($infos['lang'] == $cfg_soft_lang ? '' : '<br>（这个模块的语言编码与您系统的编码不一致，请向开发者确认它的兼容性）');
    $filelists = (array)$dm->GetFileLists($hash);
    $filelist = '';
    $setupinfo = '';
    $devURL = DEDECDNURL."/developers/{$infos['dev_id']}.json";
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($devURL);
    $devContent = $dhd->GetHtml();
    $devInfo = (array)json_decode($devContent);
    $s = "未认证";
    if (($devInfo['dev_id'] == $infos['dev_id']) && !empty($devInfo['dev_id'])) {
      $s = "已认证";
    }
    foreach ($filelists as $v) {
        if (empty($v['name'])) continue;
        if ($v['type'] == 'dir') $v['type'] = '目录';
        else $v['type'] = '文件';
        $filelist .= "{$v['type']}|{$v['name']}\r\n";
    }
    if (file_exists(DEDEDATA."/module/{$hash}-readme.php")) {
        $setupinfo = "已安装 <a href='module_main.php?action=uninstall&hash={$hash}'>卸载</a>";
    } else {
        $setupinfo = "未安装 <a href='module_main.php?action=setup&hash={$hash}'>安装</a>";
    }

    $dev_id = empty($infos['dev_id'])? "<a href='module_main.php?action=setup&hash={$hash}' class='btn btn-success btn-sm'>安装</a><a href='{$cfg_biz_dedebizUrl}/developer' target='_blank' class='btn btn-danger btn-sm'>{$s}</a>" : "{$infos['dev_id']} <a href='module_main.php?action=setup&hash={$hash}' class='btn btn-success btn-sm'>安装</a><a href='{$cfg_biz_dedebizUrl}/developer?dev_id={$infos['dev_id']}' target='_blank' class='btn btn-danger btn-sm'>{$s}</a>";
    $win = new OxWindow();
    $win->Init("", "js/blank.js", "");
    $wecome_info = "模块管理";
    $win->AddTitle("<a href='module_main.php'>模块管理</a> &gt; 模块详情：{$infos['name']}");
    $msg = "<style>.dtb{border-bottom:1px dotted #eee}</style>
    <table width='98%' cellspacing='0' cellpadding='0'>
  <tr>
    <td width='260' height='26' class='dtb'>模块名称：</td>
    <td class='dtb'>{$infos['name']}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>语言：</td>
    <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>文件大小：</td>
    <td class='dtb'>{$infos['filesize']}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>开发者ID：</td>
    <td class='dtb'>{$dev_id}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>发布时间：</td>
    <td class='dtb'>{$infos['time']}</td>
  </tr>
  <tr>
    <td height='26' class='dtb'>使用协议：</td>
    <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank' class='btn btn-success btn-sm'>浏览</a></td>
  </tr>
  <tr>
    <td height='26'>模块包含的文件（文件路径相对于当前目录）</td>
    <td></td>
  </tr>
  <tr>
    <td height='160' colspan='2'>
     <textarea name='filelists' id='filelists' style='width:98%;height:160px'>{$filelist}</textarea>
    </td>
  </tr>
</table>";
    $win->AddMsgItem("<div style='line-height:26px'>$msg</div>");
    $winform = $win->GetWindow('hand', '');
    $win->Display();
    $dm->Clear();
    exit();
}
/*--------------
function Edit();
--------------*/
else if ($action == 'edit') {
    $dm = new DedeModule($mdir);
    $minfos = (array)$dm->GetModuleInfo($hash);
    extract($minfos, EXTR_SKIP);
    if (!isset($lang)) $lang = 'gb2312';
    if (!isset($moduletype)) $moduletype = 'soft';
    $menustring = $dm->GetSystemFile($hash, 'menustring');
    $setupsql40 = dede_htmlspecialchars($dm->GetSystemFile($hash, 'setupsql40'));
    $readmetxt = $dm->GetSystemFile($hash, 'readme');
    $delsql = $dm->GetSystemFile($hash, 'delsql');
    $filelist = $dm->GetSystemFile($hash, 'oldfilelist', false);
    $indexurl = str_replace('**', '=', $indexurl);
    $dm->Clear();
    require_once(dirname(__FILE__).'/templets/module_edit.htm');
    exit();
}
/*--------------
function Download();
--------------*/
else if ($action == 'download') {
    ShowMsg("暂不支持模块下载功能", "javascript:;");
}