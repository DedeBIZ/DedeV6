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
use DedeBIZ\libraries\DedeHttpDown;
use DedeBIZ\libraries\DedeModule;
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
  die(DedeAlert(Lang("err_safemode_check"),ALERT_DANGER));
}
UserLogin::CheckPurview('sys_module');
if (empty($action)) $action = '';
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
        echo Lang('config_file_nowriteable',array('file'=>$configfile));
        exit();
    }
    $fp = fopen($configfile, 'w');
    flock($fp, 3);
    fwrite($fp, "<"."?php\r\n");
    $dsql->SetQuery("SELECT `varname`,`type`,`value`,`groupid` FROM `#@__sysconfig` ORDER BY aid ASC");
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        if (empty($row['value']) && $row['type'] == 'number') $row['value'] = 0;
        if ($row['type'] == 'number') fwrite($fp, "\${$row['varname']} = ".$row['value'].";\r\n");
        else fwrite($fp, "\${$row['varname']} = '".str_replace("'", '', $row['value'])."';\r\n");
      }
      fwrite($fp, "?".">");
      fclose($fp);
}
if ($action == '') {
    $types = array('soft' => Lang('module_soft'), 'templets' => Lang('template'), 'plus' => Lang('module_plus'), 'patch' => Lang('module_patch'));
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
else if ($action == 'view_developoer') {
    //检验开发者信息
    $dm = new DedeModule($mdir);
    $info = $dm->GetModuleInfo($hash);
    if ($info == null) {
        ShowMsg(Lang("module_err_viewdev"), -1);
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
        $offUrl = "<p>".Lang('module_offical')."：<code>{$devInfo['offurl']}</code><small>".Lang('module_offical_copy')."</small></p>";
    }
    $authAt = date("Y-m-d", $devInfo['auth_at']);
    if (!isset($info['dev_id'])) {
        $devInfo['dev_name'] = $info['team']."<span style='display:inline-block;margin-left:10px;padding:.25rem .5rem;line-height:1.5;font-size:12px;color:#fff;background:#dc3545;border-color:#dc3545;border-radius:.2rem'>".Lang('module_unauthed')."</span>";
        $authAt = Lang('unknow');
    }
    ShowMsg("<p>".Lang('module_dev_name')."：{$devInfo['dev_name']}</p><p>".Lang('module_dev_id')."：{$devInfo['dev_id']}</p><span>".Lang('module_auth_at')."：{$authAt}</span>", "-1");
    exit;
}
else if ($action == 'setup') {
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);
    if ($infos == null) {
        ShowMsg(Lang("module_err_viewdev"), -1);
        exit;
    }
    $alertMsg = ($infos['lang'] == 'utf-8' ? '' : '<br>'.Lang('module_setup_tip'));
    $filelists = (array)$dm->GetFileLists($hash);
    $filelist = '';
    $prvdirs = array();
    $incdir = array();
    foreach ($filelists as $v) {
        if (empty($v['name'])) continue;
        if ($v['type'] == 'dir') {
            $v['type'] = Lang('dir');
            $incdir[] = $v['name'];
        } else {
            $v['type'] = Lang('file');
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
    $prvdir = "<table cellpadding='1' cellspacing='1'>\r\n";
    $prvdir .= "<tr bgcolor='#f8fcf2'><th width='270'>".Lang('dir')."</td><th align='center'>".Lang('writeable')."</td></tr>\r\n";
    foreach ($prvdirs as $k => $v) {
        if ($v) $cw = '√';
        else $cw = "<span class='text-danger'>×</span>";
        $prvdir .= "<tr><td>$k</td>";
        $prvdir .= "<td align='center'>$cw</td></tr>\r\n";
    }
    $prvdir .= "</table>";
    $wecome_info = Lang("module_main");
    $devURL = DEDECDNURL."/developers/{$infos['dev_id']}.json";
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($devURL);
    $devContent = $dhd->GetHtml();
    $devInfo = (array)json_decode($devContent);
    $s = Lang('module_unauthed');
    if (($devInfo['dev_id'] == $infos['dev_id']) && !empty($devInfo['dev_id'])) {
      $s = Lang('module_authed');
    }
    $msg = "<style>.dtb{border-bottom:1px dotted #eee}</style>
    <table width='98%' cellspacing='0' cellpadding='0' class='table'>
  <tr>
    <td width='260' class='dtb'>".Lang('module_name')."：</td>
    <td class='dtb'>{$infos['name']}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_lang')."：</td>
    <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_filesize')."：</td>
    <td class='dtb'>{$infos['filesize']}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_dev_id')."：</td>
    <td class='dtb'>{$infos['dev_id']} <a href='{$cfg_biz_dedebizUrl}/developer?dev_id={$infos['dev_id']}' target='_blank' class='btn btn-success btn-sm'>{$s}</a></td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_time')."：</td>
    <td class='dtb'>{$infos['time']}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_licence')."：</td>
    <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank' class='btn btn-success btn-sm'>".Lang('view')."</a></td>
  </tr>
  <tr>
    <td class='dtb' colspan='2'>
    <div class='alert alert-danger mb-0'>".Lang('module_setup_tip2')."</div>
    </td>
  </tr>
  <tr>
    <td>".Lang('module_prvdir')."</td>
    <td>$prvdir</td>
  </tr>
  <tr>
    <td>".Lang('module_filelist')."：</td>
    <td></td>
  </tr>
  <tr>
    <td height='160' colspan='2'><textarea name='filelists' id='filelists' style='width:98%;height:160px'>{$filelist}</textarea></td>
  </tr>
  <tr>
    <td>".Lang('module_isreplace')."：</td>
    <td>
    <label><input type='radio' name='isreplace' value='1' checked='checked'> ".Lang('module_isreplace_1')."</label>
    <label><input type='radio' name='isreplace' value='3'> ".Lang('module_isreplace_3')."</label>
    <label><input type='radio' name='isreplace' value='0'> ".Lang('module_isreplace_0')."</label>
   </td>
  </tr>
</table>";
    DedeWin::Instance()->Init("module_main.php", "js/blank.js", "post")
    ->AddTitle("<a href='module_main.php'>".Lang("module_main")."</a> &gt; ".Lang('module_setup')."：{$infos['name']}")
    ->AddHidden("hash", $hash)
    ->AddHidden("action", 'setupstart')
    ->AddMsgItem("<div>$msg</div>")
    ->GetWindow("ok", "")
    ->Display();
    $dm->Clear();
    exit();
}
else if ($action == 'setupstart') {
    if (!is_writeable($mdir)) {
        ShowMsg(Lang("module_err_setupstart",array('mdir'=>$mdir)), "-1");
        exit();
    }
    $dm = new DedeModule($mdir);
    $minfos = (array)$dm->GetModuleInfo($hash);
    extract($minfos, EXTR_SKIP);
    $menustring = addslashes($dm->GetSystemFile($hash, 'menustring'));
    $indexurl = str_replace('**', '=', $indexurl);
    $query = "INSERT INTO `#@__sys_module` (`hashcode`,`modname`,`indexname`,`indexurl`,`ismember`,`menustring` ) VALUES ('$hash','$name','$indexname','$indexurl','$ismember','$menustring')";
    $rs = $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_module` WHERE hashcode LIKE '$hash'");
    $rs = $dsql->ExecuteNoneQuery($query);
    if (!$rs) {
        ShowMsg(Lang('module_err_database',array('err'=>$dsql->GetError())), 'javascript:;');
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
        $rflwft = "<script>\r\n";
        $rflwft .= "if (window.navigator.userAgent.indexOf('MSIE')>=1) top.document.frames.menu.location = 'index_menu_module.php';\r\n";
        $rflwft .= "else top.document.getElementById('menufra').src = 'index_menu_module.php';\r\n";
        $rflwft .= "</script>";
        echo $rflwft;
        UpDateCatCache();
        ShowMsg(Lang('module_success_setup'), 'module_main.php');
        exit();
    }
}
else if ($action == 'del') {
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);
    $alertMsg = ($infos['lang'] == 'utf-8' ? '' : '<br>'.Lang('module_lang_tip'));
    $dev_id = empty($infos['dev_id'])? "<a href='{$cfg_biz_dedebizUrl}/developer' target='_blank' class='btn btn-danger btn-sm'>".Lang('module_unauthed')."</a>" : "{$infos['dev_id']} <a href='{$cfg_biz_dedebizUrl}/developer?dev_id={$infos['dev_id']}' target='_blank' class='btn btn-danger btn-sm'>".Lang('module_authed')."</a>";
    $wecome_info = Lang("module_main");
    $msg = "<style>.dtb{border-bottom:1px dotted #eee}</style>
    <table width='98%' cellspacing='0' cellpadding='0' class='table'>
    <tr>
      <td width='260' class='dtb'>".Lang('module_name')."：</td>
      <td class='dtb'>{$infos['name']}</td>
    </tr>
    <tr>
      <td class='dtb'>".Lang('module_lang')."：</td>
      <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
    </tr>
    <tr>
      <td class='dtb'>".Lang('module_filesize')."：</td>
      <td class='dtb'>{$infos['filesize']}</td>
    </tr>
    <tr>
      <td class='dtb'>".Lang('module_dev_id')."：</td>
      <td class='dtb'>{$dev_id}</td>
    </tr>
    <tr>
      <td class='dtb'>".Lang('module_time')."：</td>
      <td class='dtb'>{$infos['time']}</td>
    </tr>
    <tr>
      <td class='dtb'>".Lang('module_licence')."：</td>
      <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank' class='btn btn-success btn-sm'>".Lang('view')."</a></td>
    </tr>
    <tr>
      <td colspan='2'>".Lang('module_delete_confirm',array('hash'=>$hash))."</td>
    </tr>
</table>";
    DedeWin::Instance()->Init("module_main.php", "js/blank.js", "post")
    ->AddTitle("<a href='module_main.php'>".Lang("module_main")."</a> &gt; ".Lang('module_delete')."：{$infos['name']}")
    ->AddHidden('hash', $hash)
    ->AddHidden('action', 'delok')
    ->AddMsgItem("<div>$msg</div>")
    ->GetWindow("ok", "")
    ->Display();
    $dm->Clear();
    exit();
    } else if ($action == 'delok') {
    $dm = new DedeModule($mdir);
    $modfile = $mdir."/".$dm->GetHashFile($hash);
    unlink($modfile) or die(Lang('module_delete_failed',array('modfile'=>$modfile)));
    ShowMsg(Lang("module_delete_success"), "module_main.php");
    exit();
}
else if ($action == 'uninstall') {
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);
    if ($infos['url'] == '') $infos['url'] = ' ';
    $alertMsg = ($infos['lang'] == 'utf-8' ? '' : Lang('module_lang_tip'));
    $filelists = (array)$dm->GetFileLists($hash);
    $filelist = '';
    foreach ($filelists as $v) {
        if (empty($v['name'])) continue;
        if ($v['type'] == 'dir') $v['type'] = Lang('dir');
        else $v['type'] = Lang('file');
        $filelist .= "{$v['type']}|{$v['name']}\r\n";
    }
    $dev_id = empty($infos['dev_id'])? "<a href='{$cfg_biz_dedebizUrl}/developer' target='_blank' class='btn btn-danger btn-sm'>".Lang('module_unauthed')."</a>" : "{$infos['dev_id']} <a href='{$cfg_biz_dedebizUrl}/developer?dev_id={$infos['dev_id']}' target='_blank' class='btn btn-danger btn-sm'>".Lang('module_authed')."</a>";
    $wecome_info = Lang("module_main");
    $msg = "<style>.dtb{border-bottom:1px dotted #eee}</style>
    <table width='98%' cellspacing='0' cellpadding='0' class='table'>
  <tr>
    <td width='260' class='dtb'>".Lang('module_name')."：</td>
    <td class='dtb'>{$infos['name']}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_lang')."：</td>
    <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_filesize')."：</td>
    <td class='dtb'>{$infos['filesize']}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_dev_id')."：</td>
    <td class='dtb'>{$dev_id}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_time')."：</td>
    <td class='dtb'>{$infos['time']}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_licence')."：</td>
    <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank' class='btn btn-success btn-sm'>".Lang('view')."</a></td>
  </tr>
  <tr>
    <td>".Lang('module_filelist2')."</td>
    <td></td>
  </tr>
  <tr>
    <td height='160' colspan='2'>
     <textarea name='filelists' id='filelists' style='width:98%;height:160px'>{$filelist}</textarea>
    </td>
  </tr>
  <tr>
    <td>".Lang('module_uninstall_isreplace')."</td>
    <td>
    <label><input type='radio' name='isreplace' value='0' checked='checked'> ".Lang('module_uninstall_isreplace_0')."</label>
    <label><input type='radio' name='isreplace' value='2'> ".Lang('module_uninstall_isreplace_1')."</label>
   </td>
  </tr>
</table>";
    DedeWin::Instance()->Init("module_main.php", "js/blank.js", "post")
    ->AddTitle("<a href='module_main.php'>".Lang("module_main")."</a> &gt; ".Lang('module_uninstall')."：{$infos['name']}")
    ->AddHidden("hash", $hash);
    $win->AddHidden("action", 'uninstallok')
    ->AddMsgItem("<div>$msg</div>")
    ->GetWindow("ok", "")
    ->Display();
    $dm->Clear();
    exit();
}
else if ($action == 'uninstallok') {
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_module` WHERE hashcode LIKE '$hash'");
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
        $rflwft = "<script>\r\n";
        $rflwft .= "if (window.navigator.userAgent.indexOf('MSIE')>=1) top.document.frames.menu.location = 'index_menu_module.php';\r\n";
        $rflwft .= "else top.document.getElementById('menufra').src = 'index_menu_module.php';\r\n";
        $rflwft .= "</script>";
        echo $rflwft;
        ShowMsg(Lang('module_uninstallok'), 'module_main.php');
        exit();
    }
}
else if ($action == 'showreadme') {
    $dm = new DedeModule($mdir);
    $msg = $dm->GetSystemFile($hash, 'readme');
    $msg = preg_replace("/(.*)<body/isU", "", $msg);
    $msg = preg_replace("/<\/body>(.*)/isU", "", $msg);
    $dm->Clear();
    $wecome_info = Lang("module_main");
    DedeWin::Instance()->Init("module_main.php", "js/blank.js", "post")
    ->AddTitle("<a href='module_main.php'>".Lang("module_main")."</a> &gt; ".Lang('module_showreadme')."：")
    ->AddMsgItem("<div>$msg</div>")
    ->GetWindow("hand")
    ->Display();
    exit();
}
else if ($action == 'view') {
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);
    if ($infos['url'] == '') $infos['url'] = ' ';
    $alertMsg = ($infos['lang'] == 'utf-8' ? '' : Lang('module_lang_tip'));
    $filelists = (array)$dm->GetFileLists($hash);
    $filelist = '';
    $setupinfo = '';
    $devURL = DEDECDNURL."/developers/{$infos['dev_id']}.json";
    $dhd = new DedeHttpDown();
    $dhd->OpenUrl($devURL);
    $devContent = $dhd->GetHtml();
    $devInfo = (array)json_decode($devContent);
    $s = Lang("module_unauthed");
    if (($devInfo['dev_id'] == $infos['dev_id']) && !empty($devInfo['dev_id'])) {
      $s = Lang("module_authed");
    }
    foreach ($filelists as $v) {
        if (empty($v['name'])) continue;
        if ($v['type'] == 'dir') $v['type'] = Lang('dir');
        else $v['type'] = Lang('file');
        $filelist .= "{$v['type']}|{$v['name']}\r\n";
    }
    if (file_exists(DEDEDATA."/module/{$hash}-readme.php")) {
        $setupinfo = Lang('module_installed')." <a href='module_main.php?action=uninstall&hash={$hash}'>".Lang('module_uninstall')."</a>";
    } else {
        $setupinfo = Lang('module_uninstalled')." <a href='module_main.php?action=setup&hash={$hash}'>".Lang('module_setup')."</a>";
    }

    $dev_id = empty($infos['dev_id'])? "<a href='module_main.php?action=setup&hash={$hash}' class='btn btn-success btn-sm'>".Lang('install')."</a><a href='{$cfg_biz_dedebizUrl}/developer' target='_blank' class='btn btn-danger btn-sm'>{$s}</a>" : "{$infos['dev_id']} <a href='module_main.php?action=setup&hash={$hash}' class='btn btn-success btn-sm'>".Lang('install')."</a><a href='{$cfg_biz_dedebizUrl}/developer?dev_id={$infos['dev_id']}' target='_blank' class='btn btn-danger btn-sm'>{$s}</a>";
    $wecome_info = Lang("module_main");
    $msg = "<style>.dtb{border-bottom:1px dotted #eee}</style>
    <table width='98%' cellspacing='0' cellpadding='0'>
  <tr>
    <td width='260' class='dtb'>".Lang('module_name')."：</td>
    <td class='dtb'>{$infos['name']}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_lang')."：</td>
    <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_filesize')."：</td>
    <td class='dtb'>{$infos['filesize']}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_dev_id')."：</td>
    <td class='dtb'>{$dev_id}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_time')."：</td>
    <td class='dtb'>{$infos['time']}</td>
  </tr>
  <tr>
    <td class='dtb'>".Lang('module_licence')."：</td>
    <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank' class='btn btn-success btn-sm'>浏览</a></td>
  </tr>
  <tr>
    <td>".Lang('module_filelist2')."</td>
    <td></td>
  </tr>
  <tr>
    <td height='160' colspan='2'>
     <textarea name='filelists' id='filelists' style='width:98%;height:160px'>{$filelist}</textarea>
    </td>
  </tr>
</table>";
    DedeWin::Instance()->Init("", "js/blank.js", "")
    ->AddTitle("<a href='module_main.php'>".Lang("module_main")."</a> &gt; ".Lang('module_detail')."：{$infos['name']}")
    ->AddMsgItem("<div>$msg</div>")
    ->GetWindow('hand', '')
    ->Display();
    $dm->Clear();
    exit();
}
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
else if ($action == 'download') {
    ShowMsg(Lang("module_download_unsupport"), "javascript:;");
}
?>