<?php
/**
 * 数据库备份还原操作
 *
 * @version        $id:sys_data_done.php 17:19 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
@ob_start();
@set_time_limit(0);
ini_set('memory_limit', '-1');
require_once(dirname(__FILE__).'/config.php');
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
  }
CheckPurview('sys_Data');
if (empty($dopost)) $dopost = '';
$bkdir = DEDEDATA.'/'.$cfg_backup_dir;
//跳转一下页的js
$gotojs = "function GotoNextPage(){document.gonext."."submit();}"."\r\nset"."Timeout('GotoNextPage()',500);";
$dojs = "<script>$gotojs</script>";
//备份数据
if ($dopost == 'bak') {
    if (empty($tablearr)) {
        ShowMsg('您还没选择备份数据表', 'javascript:;');
        exit();
    }
    if (!is_dir($bkdir)) {
        MkdirAll($bkdir, $cfg_dir_purview);
        CloseFtp();
    }
    //初始化使用到的变量
    $tables = explode(',', $tablearr);
    if (!isset($isstruct)) {
        $isstruct = 0;
    }
    if (!isset($startpos)) {
        $startpos = 0;
    }
    if (!isset($iszip)) {
        $iszip = 0;
    }
    if (empty($nowtable)) {
        $nowtable = '';
    }
    if (empty($fsize)) {
        $fsize = 2048;
    }
    $fsizeb = $fsize * 1024;
    //第一页的操作
    if ($nowtable == '') {
        $tmsg = '';
        $dh = dir($bkdir);
        while ($filename = $dh->read()) {
            if (!preg_match("#txt$#", $filename)) {
                continue;
            }
            $filename = $bkdir."/$filename";
            if (!is_dir($filename)) {
                unlink($filename);
            }
        }
        $dh->close();
        $tmsg .= "完成备份目录旧数据清理";
        if ($isstruct == 1) {
            $bkfile = $bkdir."/tables_struct_".substr(md5(time().mt_rand(1000, 5000).$cfg_cookie_encode), 0, 16).".txt";
            $mysql_version = $dsql->GetVersion();
            $fp = fopen($bkfile, "w");
            foreach ($tables as $t) {
                fwrite($fp, "DROP TABLE IF EXISTS `$t`;\r\n\r\n");
                $dsql->SetQuery("SHOW CREATE TABLE ".$dsql->dbName.".".$t);
                $dsql->Execute('me');
                $row = $dsql->GetArray('me', MYSQL_BOTH);
                //去除AUTO_INCREMENT
                $row[1] = preg_replace("#AUTO_INCREMENT=([0-9]{1,})[ \r\n\t]{1,}#i", "", $row[1]);
                $eng1 = "#ENGINE=MyISAM[ \r\n\t]{1,}DEFAULT[ \r\n\t]{1,}CHARSET=".$cfg_db_language."#i";
                $tableStruct = preg_replace($eng1, "TYPE=MyISAM", $row[1]);
                fwrite($fp, ''.$tableStruct.";\r\n\r\n");
            }
            fclose($fp);
            $tmsg .= "完成备份数据表结构信息";
        }
        $tmsg .= "正在进行数据备份初始化工作，请稍后";
        $doneForm = "<form name='gonext' method='post' action='sys_data_done.php'>
           <input type='hidden' name='isstruct' value='$isstruct'>
           <input type='hidden' name='dopost' value='bak'>
           <input type='hidden' name='fsize' value='$fsize'>
           <input type='hidden' name='tablearr' value='$tablearr'>
           <input type='hidden' name='nowtable' value='{$tables[0]}'>
           <input type='hidden' name='startpos' value='0'>
           <input type='hidden' name='iszip' value='$iszip'>\r\n</form>\r\n{$dojs}\r\n";
        PutInfo($tmsg, $doneForm);
        exit();
    }
    //执行分页备份
    else {
        $j = 0;
        $fs = array();
        $bakStr = '';
        //分析表里的字段信息
        $nowtable = str_replace("`", "", $nowtable);
        $dsql->GetTableFields($nowtable);
        $intable = "INSERT INTO `$nowtable` VALUES(";
        while ($r = $dsql->GetFieldObject()) {
            $fs[$j] = trim($r->name);
            $j++;
        }
        $fsd = $j - 1;
        //读取表的文档
        $dsql->SetQuery("SELECT * FROM `$nowtable`");
        $dsql->Execute();
        $m = 0;
        $bakfilename = "$bkdir/{$nowtable}_{$startpos}_".substr(md5(time().mt_rand(1000, 5000).$cfg_cookie_encode), 0, 16).".txt";
        while ($row2 = $dsql->GetArray()) {
            if ($m < $startpos) {
                $m++;
                continue;
            }
            //检测数据是否达到规定大小
            if (strlen($bakStr) > $fsizeb) {
                $fp = fopen($bakfilename, "w");
                fwrite($fp, $bakStr);
                fclose($fp);
                $tmsg = "正在备份{$m}条数据，继续备份{$nowtable}";
                $doneForm = "<form name='gonext' method='post' action='sys_data_done.php'>
                <input type='hidden' name='isstruct' value='$isstruct'>
                <input type='hidden' name='dopost' value='bak'>
                <input type='hidden' name='fsize' value='$fsize'>
                <input type='hidden' name='tablearr' value='$tablearr'>
                <input type='hidden' name='nowtable' value='$nowtable'>
                <input type='hidden' name='startpos' value='$m'>
                <input type='hidden' name='iszip' value='$iszip'>\r\n</form>\r\n{$dojs}\r\n";
                PutInfo($tmsg, $doneForm);
                exit();
            }
            //正常情况
            $line = $intable;
            for ($j = 0; $j <= $fsd; $j++) {
                if ($j < $fsd) {
                    $line .= "'".RpLine(addslashes($row2[$fs[$j]]))."',";
                } else {
                    $line .= "'".RpLine(addslashes($row2[$fs[$j]]))."');\r\n";
                }
            }
            $m++;
            $bakStr .= $line;
        }
        //如果数据比卷设置值小
        if ($bakStr != '') {
            $fp = fopen($bakfilename, "w");
            fwrite($fp, $bakStr);
            fclose($fp);
        }
        for ($i = 0; $i < count($tables); $i++) {
            if ($tables[$i] == $nowtable) {
                if (isset($tables[$i + 1])) {
                    $nowtable = $tables[$i + 1];
                    $startpos = 0;
                    break;
                } else {
                    PutInfo("成功完成所有数据备份", "");
                    exit();
                }
            }
        }
        $tmsg = "正在备份{$m}条数据，继续备份{$nowtable}";
        $doneForm = "<form name='gonext' method='post' action='sys_data_done.php?dopost=bak'>
          <input type='hidden' name='isstruct' value='$isstruct'>
          <input type='hidden' name='fsize' value='$fsize'>
          <input type='hidden' name='tablearr' value='$tablearr'>
          <input type='hidden' name='nowtable' value='$nowtable'>
          <input type='hidden' name='startpos' value='$startpos'>\r\n</form>\r\n{$dojs}\r\n";
        PutInfo($tmsg, $doneForm);
        exit();
    }
    //分页备份代码结束
}
//还原数据
else if ($dopost == 'redat') {
    if ($bakfiles == '') {
        ShowMsg('您还没选择还原数据表', 'javascript:;');
        exit();
    }
    $bakfilesTmp = $bakfiles;
    $bakfiles = explode(',', $bakfiles);
    if (empty($structfile)) {
        $structfile = "";
    }
    if (empty($delfile)) {
        $delfile = 0;
    }
    if (empty($startgo)) {
        $startgo = 0;
    }
    if ($startgo == 0 && $structfile != '') {
        $tbdata = '';
        $fp = fopen("$bkdir/$structfile", 'r');
        while (!feof($fp)) {
            $tbdata .= fgets($fp, 1024);
        }
        fclose($fp);
        $querys = explode(';', $tbdata);
        foreach ($querys as $q) {
            $q = preg_replace("#TYPE=MyISAM#i","ENGINE=MyISAM DEFAULT CHARSET=".$cfg_db_language, $q);
            $rs = $dsql->ExecuteNoneQuery(trim($q).';');
        }
        if ($delfile == 1) {
            @unlink("$bkdir/$structfile");
        }
        $tmsg = "成功完成数据表还原，继续还原其它数据";
        $doneForm = "<form name='gonext' method='post' action='sys_data_done.php?dopost=redat'>
        <input type='hidden' name='startgo' value='1'>
        <input type='hidden' name='delfile' value='$delfile'>
        <input type='hidden' name='bakfiles' value='$bakfilesTmp'>
        </form>\r\n{$dojs}\r\n";
        PutInfo($tmsg, $doneForm);
        exit();
    } else {
        $nowfile = $bakfiles[0];
        $bakfilesTmp = preg_replace("#".$nowfile."[,]{0,1}#", "", $bakfilesTmp);
        $oknum = 0;
        if (filesize("$bkdir/$nowfile") > 0) {
            $fp = fopen("$bkdir/$nowfile", 'r');
            while (!feof($fp)) {
                $line = trim(fgets($fp, 512 * 1024));
                if ($line == "") continue;
                $rs = $dsql->ExecuteNoneQuery($line);
                if ($rs) $oknum++;
            }
            fclose($fp);
        }
        if ($delfile == 1) {
            @unlink("$bkdir/$nowfile");
        }
        if ($bakfilesTmp == "") {
            ShowMsg('成功还原所有数据', 'javascript:;');
            exit();
        }
        $tmsg = "正在还原$nowfile}文件{$oknum}条数据，继续还原其它数据";
        $doneForm = "<form name='gonext' method='post' action='sys_data_done.php?dopost=redat'>
        <input type='hidden' name='startgo' value='1'>
        <input type='hidden' name='delfile' value='$delfile'>
        <input type='hidden' name='bakfiles' value='$bakfilesTmp'>
        </form>\r\n{$dojs}\r\n";
        PutInfo($tmsg, $doneForm);
        exit();
    }
}
function PutInfo($msg1, $msg2)
{
    global $cfg_soft_lang;
    $msginfo = "<!DOCTYPE html><html><head><meta charset='utf-8'><meta http-equiv='X-UA-Compatible' content='IE=Edge,chrome=1'><title>系统提示</title><style>body{margin:0;line-height:1.6;letter-spacing:.6px;font:14px Helvetica Neue,Helvetica,PingFang SC,Tahoma,Arial,sans-serif;color:#545b62;background:#f5f5f5}a{color:#1eb867;text-decoration:none}.tips-box{margin:70px auto 0;width:500px;height:auto;background:#fff;border-radius:.2rem;box-shadow:0 .125rem .25rem rgba(0,0,0,.075)}.tips-head{margin:0 20px;padding:18px 0;border-bottom:1px solid #f5f5f5}.tips-head p{margin:0;padding-left:10px;line-height:16px;text-align:left;border-left:3px solid #dc3545}.tips-body{padding:20px;min-height:130px;color:#545b62}.btn{margin-top:20px;text-align:center}.btn a{display:inline-block;padding:.375rem .75rem;font-size:12px;color:#fff;background:#1eb867;border-radius:.2rem;text-align:center;transition:all .3s}.btn a:focus{background:#006829;border-color:#005b24;box-shadow:0 0 0 0.2rem rgba(72,180,97,.5)}.text-primary{color:#007bff}@media (max-width:768px){.tips{padding:0 15px}.tips,.tips-box{width:100%}}</style></head><body><div class='tips'><div class='tips-box'><div class='tips-head'><p>系统提示</p></div><div class='tips-body'>{$msg1}{$msg2}</div></div></div>";
    echo $msginfo."</body></html>";
}
function RpLine($str)
{
    $str = str_replace("\r", "\\r", $str);
    $str = str_replace("\n", "\\n", $str);
    return $str;
}
?>