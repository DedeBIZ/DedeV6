<?php
/**
 * 自定义模型管理
 *
 * @version        $Id: mychannel_edit.php 1 14:49 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
use DedeBIZ\Template\DedeTagParse;
use DedeBIZ\TypeLink\TypeLink;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('c_Edit');
if (empty($dopost)) $dopost = "";
$id = isset($id) && is_numeric($id) ? $id : 0;
if ($dopost == "show") {
    $dsql->ExecuteNoneQuery("UPDATE `#@__channeltype` SET isshow=1 WHERE id='$id'");
    ShowMsg(Lang("operation_successful"), "mychannel_main.php");
    exit();
} else if ($dopost == "hide") {
    $dsql->ExecuteNoneQuery("UPDATE `#@__channeltype` SET isshow=0 WHERE id='$id'");
    ShowMsg(Lang("operation_successful"), "mychannel_main.php");
    exit();
}
else if ($dopost == "copystart") {
    if ($id == -1) {
        ShowMsg(Lang("mychannel_error_spec_copy"), "-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id'");
    if ($row['id'] > -1) {
        $nrow = $dsql->GetOne("SELECT MAX(id) AS id FROM `#@__channeltype` LIMIT 0,1");
        $newid = $nrow['id'] + 1;
        if ($newid < 10) {
            $newid = $newid + 10;
        }
        $idname = $newid;
    } else {
        $nrow = $dsql->GetOne("SELECT MIN(id) AS id FROM `#@__channeltype` LIMIT 0,1");
        $newid = $nrow['id'] - 1;
        if ($newid < -10) {
            $newid = $newid - 10;
        }
        $idname = 'w'.($newid * -1);
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id'");
    $wintitle = Lang("mychannel_main")."-".Lang("mychannel_copy");
    $wecome_info = "<a href='mychannel_main.php'>".Lang("mychannel_main")."</a>::".Lang("mychannel_copy");
    $msg = "
        <table width='460' cellspacing='0' cellpadding='0'>
            <tr>
                <td width='170' align='center'>".Lang('mychannel_cid')."：</td>
                <td width='230'><input name='newid' type='text' id='newid' size='6' value='{$newid}'></td>
            </tr>
            <tr>
                <td align='center'>".Lang("mychannel_typename")."：</td>
                <td><input name='newtypename' type='text' id='newtypename' value='{$row['typename']}{$idname}' style='width:260px'></td>
            </tr>
            <tr>
                <td align='center'>".Lang("mychannel_nid")."：</td>
                <td><input name='newnid' type='text' id='newnid' value='{$row['nid']}{$idname}' style='width:260px'></td>
            </tr>
            <tr>
                <td align='center'>".Lang("additional_table")."：</td>
                <td><input name='newaddtable' type='text' id='newaddtable' value='{$row['addtable']}{$idname}' style='width:260px'></td>
            </tr>
            <tr>
                <td align='center'>".Lang("copy_templet")."：</td>
                <td>
                    <label><input type='radio' name='copytemplet' id='copytemplet' value='1' checked='checked'> ".Lang("copy")."</label>
                    <label><input type='radio' name='copytemplet' id='copytemplet' value='0'> ".Lang("nocopy")."</label>
                </td>
            </tr>
        </table>
        ";
    DedeWin::Instance()->Init("mychannel_edit.php", "js/blank.js", "post")
    ->AddTitle(Lang('mychannel_copied')."：[<span class='text-danger'>".$row['typename']."</span>]")
    ->AddHidden("cid", $id)
    ->AddHidden("id", $id)
    ->AddHidden("dopost", 'copysave')
    ->AddMsgItem("<div>$msg</div>")
    ->GetWindow("ok", "")
    ->Display();
    exit();
}
else if ($dopost == "export") {
    if ($id == -1) {
        ShowMsg(Lang("mychannel_error_spec_export"), "-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id'");
    $channelconfig = '';
    $row['maintable'] = preg_replace('#dede_#', '#@__', $row['maintable']);
    $row['addtable'] = preg_replace('#dede_#', '#@__', $row['addtable']);
    foreach ($row as $k => $v) {
        if ($k == 'fieldset') $v = "\r\n$v\r\n";
        $channelconfig .= "<channel:{$k}>$v</channel:{$k}>\r\n";
    }
    $wintitle = Lang("mychannel_export");
    $wecome_info = "<a href='mychannel_main.php'>".Lang("mychannel_main")."</a>::".Lang("mychannel_export");
    DedeWin::Instance()->Init()
    ->AddTitle(Lang("mychannel_export_title",array('typename'=>$row['typename'])))
    ->GetWindow("hand", "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/codemirror.css\"><script type=\"text/javascript\" src=\"js/codemirror.js\"></script><script type=\"text/javascript\" src=\"js/mode/xml/xml.js\"></script><script type=\"text/javascript\" src=\"js/mode/javascript/javascript.js\"></script><script type=\"text/javascript\" src=\"js/mode/css/css.js\"></script><script type=\"text/javascript\" src=\"js/mode/htmlmixed/htmlmixed.js\"></script><textarea name='config' id='content' style='width:98%;height:450px;word-wrap: break-word;word-break:break-all;'>".$channelconfig."</textarea><script type=\"text/javascript\">var editor = CodeMirror.fromTextArea(document.getElementById('content'), {lineNumbers: true,lineWrapping: true,mode: 'text/html'});</script>")
    ->Display();
    exit();
}
else if ($dopost == "exportin") {
    $wintitle = Lang("mychannel_exportin");
    $wecome_info = "<a href='mychannel_main.php'>".Lang('mychannel_main')."</a>::".Lang("mychannel_exportin");
    DedeWin::Instance()->Init("mychannel_edit.php", "js/blank.js", "post")
    ->AddHidden("dopost", "exportinok")
    ->AddTitle(Lang("mychannel_exportin_title"))
    ->AddMsgItem("<textarea name='exconfig' style='width:98%;height:450px;word-wrap: break-word;word-break:break-all;'></textarea>")
    ->GetWindow("ok")
    ->Display();
    exit();
}
else if ($dopost == "exportinok") {
    require_once(DEDEADMIN."/inc/inc_admin_channel.php");
    function GotoStaMsg($msg)
    {
        global $wintitle, $wecome_info, $winform;
        $wintitle = Lang("mychannel_exportin");
        $wecome_info = "<a href='mychannel_main.php'>".Lang('mychannel_main')."</a>::".Lang("mychannel_exportin");
        DedeWin::Instance()->Init()
        ->AddTitle(Lang("mychannel_exportinok_title"))
        ->AddMsgItem($msg)
        ->GetWindow("hand")
        ->Display();
        exit();
    }
    $msg = Lang("no_message");
    $exconfig = stripslashes($exconfig);
    $dtp = new DedeTagParse();
    $dtp->SetNameSpace('channel', '<', '>');
    $dtp->LoadSource($exconfig);
    if (!is_array($dtp->CTags)) GotoStaMsg(Lang("mychannel_err_exportinok_faild"));
    $fields = array();
    foreach ($dtp->CTags as $ctag) {
        $fname = $ctag->GetName('name');
        $fields[$fname] = trim($ctag->GetInnerText());
    }
    if (!isset($fields['nid']) || !isset($fields['fieldset'])) {
        GotoStaMsg(Lang("mychannel_err_exportinok_faild"));
    }
    //正常的导入过程
    $mysql_version = $dsql->GetVersion(true);

    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE nid='{$fields['nid']}'");
    if (is_array($row)) {
        GotoStaMsg(Lang("mychannel_err_nid_exists",array("nid"=>$fields['nid'])));
    }
    //创建表
    if ($fields['issystem'] != -1) {
        $tabsql = "CREATE TABLE IF NOT EXISTS `{$fields['addtable']}`(
                  `aid` int(11) NOT NULL default '0',
                `typeid` int(11) NOT NULL default '0',
                `redirecturl` varchar(255) NOT NULL default '',
                `templet` varchar(30) NOT NULL default '',
                `userip` char(46) NOT NULL default '',";
    } else {
        $tabsql = "CREATE TABLE IF NOT EXISTS `{$fields['addtable']}`(
                  `aid` int(11) NOT NULL default '0',
                `typeid` int(11) NOT NULL default '0',
                `channel` SMALLINT NOT NULL DEFAULT '0',
                `arcrank` SMALLINT NOT NULL DEFAULT '0',
                `mid` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT '0',
                `click` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
                `title` varchar(255) NOT NULL default '',
                `senddate` int(11) NOT NULL default '0',
                `flag` set('c','h','p','f','s','j','a','b') default NULL,";
    }
    if ($mysql_version < 4.1) {
        $tabsql .= "    PRIMARY KEY  (`aid`), KEY `typeid` (`typeid`)\r\n) TYPE=MyISAM; ";
    } else {
        $tabsql .= "    PRIMARY KEY  (`aid`), KEY `typeid` (`typeid`)\r\n) ENGINE=MyISAM DEFAULT CHARSET=".$cfg_db_language."; ";
    }
    $rs = $dsql->ExecuteNoneQuery($tabsql);
    if (!$rs) {
        GotoStaMsg(Lang("mychannel_err_create_table").$dsql->GetError());
        exit();
    }
    if ($fields['issystem'] == 1) $fields['issystem'] = 0;
    if ($fields['issystem'] == 0) {
        $row = $dsql->GetOne("SELECT id FROM `#@__channeltype` ORDER BY id DESC");
        $fields['newid'] = $row['id'] + 1;
    } else {
        $row = $dsql->GetOne("SELECT id FROM `#@__channeltype` ORDER BY id ASC");
        $fields['newid'] = $row['id'] - 1;
    }
    $fieldset = $fields['fieldset'];
    $fields['fieldset'] = addslashes($fields['fieldset']);
    $inquery = "INSERT INTO `#@__channeltype` (`id`,`nid`,`typename`,`addtable`,`addcon`,`mancon`,`editcon`,`useraddcon`,`usermancon`,`usereditcon`,`fieldset`,`listfields`,`issystem`,`isshow`,`issend`,`arcsta`,`usertype`,`sendrank`) VALUES ('{$fields['newid']}','{$fields['nid']}','{$fields['typename']}','{$fields['addtable']}','{$fields['addcon']}' ,'{$fields['mancon']}','{$fields['editcon']}','{$fields['useraddcon']}','{$fields['usermancon']}','{$fields['usereditcon']}','{$fields['fieldset']}','{$fields['listfields']}','{$fields['issystem']}','{$fields['isshow']}','{$fields['issend']}','{$fields['arcsta']}','{$fields['usertype']}','{$fields['sendrank']}');";
    $rs = $dsql->ExecuteNoneQuery($inquery);
    if (!$rs) GotoStaMsg(Lang("mychannel_exportinok_failed",array("error"=>$dsql->GetError())));
    $dtp = new DedeTagParse();
    $dtp->SetNameSpace("field", "<", ">");
    $dtp->LoadSource($fieldset);
    $allfields = '';
    if (is_array($dtp->CTags)) {
        foreach ($dtp->CTags as $ctag) {
            //检测被修改的字段类型
            $dtype = $ctag->GetAtt('type');
            $fieldname = $ctag->GetName();
            $dfvalue = $ctag->GetAtt('default');
            $islist = $ctag->GetAtt('islist');
            $mxlen = $ctag->GetAtt('maxlength');
            $fieldinfos = GetFieldMake($dtype, $fieldname, $dfvalue, $mxlen);
            $ntabsql = $fieldinfos[0];
            $buideType = $fieldinfos[1];
            if ($islist != '') {
                $allfields .= ($allfields == '' ? $fieldname : ','.$fieldname);
            }
            $dsql->ExecuteNoneQuery(" ALTER TABLE `{$fields['addtable']}` ADD  $ntabsql ");
        }
    }
    if ($allfields != '') {
        $dsql->ExecuteNoneQuery("UPDATE `#@__channeltype` SET listfields='$allfields' WHERE id='{$fields['newid']}'");
    }
    GotoStaMsg(Lang("mychannel_exportinok_success"));
}
else if ($dopost == "copysave") {
    $cid = intval($cid);
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$cid' ", PDO::FETCH_ASSOC);
    foreach ($row as $k => $v) {
        ${strtolower($k)} = addslashes($v);
    }
    $inquery = "INSERT INTO `#@__channeltype` (`id`,`nid`,`typename`,`addtable`,`addcon`,`mancon`,`editcon`,`useraddcon`,`usermancon`,`usereditcon`,`fieldset`,`listfields`,`issystem`,`isshow`,`issend`,`arcsta`,`usertype`,`sendrank`) VALUES ('$newid','$newnid','$newtypename','$newaddtable','$addcon','$mancon','$editcon','$useraddcon','$usermancon','$usereditcon','$fieldset','$listfields','$issystem','$isshow','$issend','$arcsta','$usertype','$sendrank');";
    $mysql_version = $dsql->GetVersion(TRUE);
    if (!$dsql->IsTable($newaddtable)) {
        $dsql->Execute('me', "SHOW CREATE TABLE {$dsql->dbName}.{$addtable}");
        $row = $dsql->GetArray('me', PDO::FETCH_BOTH);
        $tableStruct = $row[1];
        $tb = str_replace('#@__', $cfg_dbprefix, $addtable);
        $tableStruct = preg_replace("/CREATE TABLE `$addtable`/iU", "CREATE TABLE `$newaddtable`", $tableStruct);
        $dsql->ExecuteNoneQuery($tableStruct);
    }
    if ($copytemplet == 1) {
        $tmpletdir = $cfg_basedir.$cfg_templets_dir.'/'.$cfg_df_style;
        copy("{$tmpletdir}/article_{$nid}.htm", "{$tmpletdir}/{$newnid}_article.htm");
        copy("{$tmpletdir}/list_{$nid}.htm", "{$tmpletdir}/{$newnid}_list.htm");
        copy("{$tmpletdir}/index_{$nid}.htm", "{$tmpletdir}/{$newnid}_index.htm");
    }
    $rs = $dsql->ExecuteNoneQuery($inquery);
    if ($rs) {
        ShowMsg(Lang("mychannel_copy_success"), "mychannel_edit.php?id={$newid}&dopost=edit");
        exit();
    } else {
        $errv = $dsql->GetError();
        ShowMsg(Lang("mychannel_copy_failed",array("error"=>$errv)), "javascript:;");
        exit();
    }
}
else if ($dopost == "save") {
    $fieldset = preg_replace("#[\r\n]{1,}#", "\r\n", $fieldset);
    $usertype = empty($usertype) ? '' : $usertype;
    $query = "UPDATE `#@__channeltype` SET typename='$typename',addtable='$addtable',addcon='$addcon',mancon='$mancon',editcon='$editcon',useraddcon='$useraddcon',usermancon='$usermancon',usereditcon='$usereditcon',fieldset='$fieldset',listfields='$listfields',issend='$issend',arcsta='$arcsta',usertype='$usertype',sendrank='$sendrank',needdes='$needdes',needpic='$needpic',titlename='$titlename',onlyone='$onlyone',dfcid='$dfcid' WHERE id='$id'";
    if (trim($fieldset) != '') {
        $dtp = new DedeTagParse();
        $dtp->SetNameSpace("field", "<", ">");
        $dtp->LoadSource(stripslashes($fieldset));
        if (!is_array($dtp->CTags)) {
            ShowMsg(Lang("mychannel_err_save_cant_parse"), "-1");
            exit();
        }
    }
    $trueTable = str_replace("#@__", $cfg_dbprefix, $addtable);
    if (!$dsql->IsTable($trueTable)) {
        ShowMsg(Lang("mychannel_err_no_table",array("table"=>$trueTable)), "-1");
        exit();
    }
    $dsql->ExecuteNoneQuery($query);
    ShowMsg(Lang("mychannel_save_success"), "mychannel_main.php");
    exit();
}
else if ($dopost == "gettemplets") {
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id'");
    $wintitle = Lang("mychannel_main")."-".Lang("mychannel_gettemplets");
    $wecome_info = "<a href='mychannel_main.php'>".Lang("mychannel_main")."</a>::".Lang("mychannel_gettemplets");
    $defaulttemplate = $cfg_templets_dir.'/'.$cfg_df_style;
    $msg = "
        ".Lang("temparticle")."：{$defaulttemplate}/article_{$row['nid']}.htm
        <a href='tpl.php?acdir={$cfg_df_style}&action=edit&filename=article_{$row['nid']}.htm'>[".Lang("edit")."]</a><br>
        ".Lang("templist")."：{$defaulttemplate}/list_{$row['nid']}.htm
        <a href='tpl.php?acdir={$cfg_df_style}&action=edit&filename=list_{$row['nid']}.htm'>[".Lang("edit")."]</a>
        <br>
        ".Lang("tempindex")."：{$defaulttemplate}/index_{$row['nid']}.htm
        <a href='tpl.php?acdir={$cfg_df_style}&action=edit&filename=index_{$row['nid']}.htm'>[".Lang("edit")."]</a>
    ";
    DedeWin::Instance()->Init("", "js/blank.js", "")
    ->AddTitle(Lang("mychannel_gettemplets_title",array("typename"=>$row['typename'])))
    ->AddMsgItem("<div>$msg</div>")
    ->GetWindow("hand", "")
    ->Display();
    exit();
}
else if ($dopost == "delete") {
    UserLogin::CheckPurview('c_Del');
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id'");
    if ($row['issystem'] == 1) {
        ShowMsg(Lang("mychannel_err_delete_system"), "mychannel_main.php");
        exit();
    }
    if (empty($job)) $job = "";
    if ($job == "") //确认提示
    {
        $wintitle = Lang("mychannel_main")."-".Lang("mychannel_delete");
        $wecome_info = "<a href='mychannel_main.php'>".Lang("mychannel_main")."</a>::".Lang("mychannel_delete");
        DedeWin::Instance()->Init("mychannel_edit.php", "js/blank.js", "POST")
        ->AddHidden("job", "yes")
        ->AddHidden("dopost", $dopost)
        ->AddHidden("id", $id)
        ->AddTitle(Lang("mychannel_delete_title",array("typename"=>$row['typename'])))
        ->GetWindow("ok")
        ->Display();
        exit();
    } else if ($job == "yes") //操作
    {
        require_once(DEDEINC."/typelink/typeunit.class.admin.php");
        $myrow = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$id'", PDO::FETCH_ASSOC);
        if (!is_array($myrow)) {
            ShowMsg(Lang('mychannel_err_noinfo'), '-1');
            exit();
        }
        //检查频道的表是否独占数据表
        $addtable = str_replace($cfg_dbprefix, '', str_replace('#@__', $cfg_dbprefix, $myrow['addtable']));
        $row = $dsql->GetOne("SELECT COUNT(id) AS dd FROM `#@__channeltype` WHERE  addtable like '{$cfg_dbprefix}{$addtable}' OR addtable LIKE CONCAT('#','@','__','$addtable') ;");
        $isExclusive2 = ($row['dd'] > 1 ? 0 : 1);
        //获取与频道关连的所有栏目id
        $tids = '';
        $dsql->Execute('qm', "SELECT id FROM `#@__arctype` WHERE channeltype='$id'");
        while ($row = $dsql->GetArray('qm')) {
            $tids .= ($tids == '' ? $row['id'] : ','.$row['id']);
        }
        //删除相关信息
        if ($tids != '') {
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE typeid IN($tids);");
            $dsql->ExecuteNoneQuery("DELETE FROM `{$myrow['maintable']}` WHERE typeid IN($tids);");
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__spec` WHERE typeid IN ($tids);");
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE typeid IN ($tids);");
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctype` WHERE id IN ($tids);");
        }
        //删除附加表或附加表内的信息
        if ($isExclusive2 == 1) {
            $dsql->ExecuteNoneQuery("DROP TABLE IF EXISTS `{$cfg_dbprefix}{$addtable}`;");
        } else {
            if ($tids != '' && $myrow['addtable'] != '') {
                $dsql->ExecuteNoneQuery("DELETE FROM `{$myrow['addtable']}` WHERE typeid IN ($tids);");
            }
        }
        //删除频道配置信息
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__channeltype` WHERE id='$id'");
        //更新栏目缓存
        UpDateCatCache($dsql);
        ShowMsg(Lang("mychannel_delete_success"), "mychannel_main.php");
        exit();
    }
} //del
else if ($dopost == 'modifysearch') {
    if (!isset($step)) $step = 0;
    if (empty($step)) {
        $step = 1;
        $mid = intval($mid);
        $query = "SELECT mainfields, addonfields, template FROM `#@__advancedsearch` WHERE mid='$mid'";
        $searchinfo = $dsql->GetOne($query);
        if (!is_array($searchinfo)) {
            $searchinfo = array();
            $searchinfo['mainfields'] = $searchinfo['addonfields'] = $searchinfo['template'] = '';
        }
        $searchinfo['mainfields'] = explode(',', $searchinfo['mainfields']);
        $searchinfo['addonfields'] = explode(',', $searchinfo['addonfields']);
        $addonfieldsarr = array();
        foreach ($searchinfo['addonfields'] as $k) {
            $karr = explode(':', $k);
            $addonfieldsarr[] = $karr[0];
        }
        $template = $searchinfo['template'] == '' ? 'advancedsearch.htm' : $searchinfo['template'];
        $c1 = in_array('iscommend', $searchinfo['mainfields']) ? 'checked' : '';
        $c2 = in_array('typeid', $searchinfo['mainfields']) ? 'checked' : '';
        $c3 = in_array('writer', $searchinfo['mainfields']) ? 'checked' : '';
        $c4 = in_array('source', $searchinfo['mainfields']) ? 'checked' : '';
        $c5 = in_array('senddate', $searchinfo['mainfields']) ? 'checked' : '';
        $mainfields = '<label><input type="checkbox" name="mainfields[]" '.$c1.' value="iscommend" /> '.Lang('iscommend').'</label> ';
        $mainfields .= '<label><input type="checkbox" name="mainfields[]" '.$c2.' value="typeid" /> '.Lang('catalog').'</label> ';
        $mainfields .= '<label><input type="checkbox" name="mainfields[]" '.$c3.' value="writer" /> '.Lang('writer').'</label> ';
        $mainfields .= '<label><input type="checkbox" name="mainfields[]" '.$c4.' value="source" /> '.Lang('source').'</label> ';
        $mainfields .= '<label><input type="checkbox" name="mainfields[]" '.$c5.' value="senddate" /> '.Lang('senddate').'</label> ';
        $query = "SELECT * FROM `#@__channeltype` WHERE id='$mid'";
        $channel = $dsql->GetOne($query);
        $searchtype = array('int', 'datetime', 'float', 'textdata', 'textchar', 'text', 'htmltext', 'multitext', 'select', 'radio', 'checkbox');
        $addonfields = '';
        $dtp = new DedeTagParse();
        $dtp->SetNameSpace("field", "<", ">");
        $dtp->LoadSource($channel['fieldset']);
        if ($channel['issystem'] < 0) {
            $checked = in_array('typeid', $addonfieldsarr) ? 'checked' : '';
            $addonfields .= '<label><input type="checkbox" name="addonfields[]" '.$checked.' value="typeid" /> 栏目</label> ';
            $checked = in_array('senddate', $addonfieldsarr) ? 'checked' : '';
            $addonfields .= '<label><input type="checkbox" name="addonfields[]" '.$checked.' value="senddate" /> 发布时间</label> ';
        }
        if (is_array($dtp->CTags) && !empty($dtp->CTags)) {
            foreach ($dtp->CTags as $ctag) {
                $datatype = $ctag->GetAtt('type');
                $value = $ctag->GetName();
                if ($channel['issystem'] < 0) {
                    $_oo = array('channel', 'arcrank', 'title', 'senddate', 'mid', 'click', 'flag', 'litpic', 'userip', 'lastpost', 'scores', 'goodpost', 'badpost', 'endtime');
                    if (in_array($value, $_oo)) continue;
                }
                $label = $ctag->GetAtt('itemname');
                if (in_array($datatype, $searchtype)) {
                    $checked = in_array($value, $addonfieldsarr) ? 'checked' : '';
                    $addonfields .= "<label><input type=\"checkbox\" name=\"addonfields[]\" $checked value=\"$value\" /> $label</label> ";
                }
            }
        }
        require_once(dirname(__FILE__)."/templets/mychannel_modifysearch.htm");
    } else if ($step == 1) {
        $query = "SELECT * FROM `#@__channeltype` WHERE id='$mid'";
        $channel = $dsql->GetOne($query);
        if (empty($addonfields)) {
            $addonfields = '';
        }
        $template = trim($template);
        $forms = '<form action="'.$cfg_cmspath.'/apps/advancedsearch.php" method="post">';
        $forms .= "<input type=\"hidden\" name=\"mid\" value=\"$mid\" />";
        $forms .= "<input type=\"hidden\" name=\"dopost\" value=\"search\" />";
        $forms .= Lang("keywords")."：<input type=\"text\" name=\"q\" /><br>";
        $mainstring = '';
        if (!empty($mainfields) && is_array($mainfields)) {
            $mainstring = implode(',', $mainfields);
            foreach ($mainfields as $mainfield) {
                if ($mainfield == 'typeid') {
                    $tl = new TypeLink(0);
                    $typeOptions = $tl->GetOptionArray(0, 0, $mid);
                    $forms .= "<br>".Lang('catalog')."：<select name='typeid' style='width:260px'>\r\n";
                    $forms .= "<option value='0' selected>".Lang('unlimited_catalog')."</option>\r\n";
                    $forms .= $typeOptions;
                    $forms .= "</select>";
                    $forms .= "<label><input type=\"checkbox\" name=\"includesons\" value=\"1\" />".Lang("include_son_catalog")."</label><br>";
                } else if ($mainfield == 'iscommend') {
                    $forms .= "<label><input type=\"checkbox\" name=\"iscommend\" value=\"1\" />".Lang("recommend")."</label><br>";
                } else if ($mainfield == 'writer') {
                    $forms .= Lang('writer')."：<input type=\"text\" name=\"writer\" value=\"\" /><br>";
                } else if ($mainfield == 'source') {
                    $forms .= Lang('source')."：<input type=\"text\" name=\"source\" value=\"\" /><br>";
                } else if ($mainfield == 'senddate') {
                    $forms .= Lang('startdate')."：<input type=\"text\" name=\"startdate\" value=\"\" /><br>";
                    $forms .= Lang('enddate')."<input type=\"text\" name=\"enddate\" value=\"\" /><br>";
                }
            }
        }
        $addonstring = '';
        $intarr = array('int', 'float');
        $textarr = array('textdata', 'textchar', 'text', 'htmltext', 'multitext');
        if ($channel['issystem'] < 0) {
            foreach ((array)$addonfields as $addonfield) {
                if ($addonfield == 'typeid') {
                    $tl = new TypeLink(0);
                    $typeOptions = $tl->GetOptionArray(0, 0, $mid);
                    $forms .= "<br>".Lang('catalog')."：<select name='typeid' style='width:260px'>\r\n";
                    $forms .= "<option value='0' selected>".Lang('unlimited_catalog')."</option>\r\n";
                    $forms .= $typeOptions;
                    $forms .= "</select>";
                    $forms .= "<label><input type=\"checkbox\" name=\"includesons\" value=\"1\" />".Lang("include_son_catalog")."</label><br>";
                    $addonstring .= 'typeid:int,';
                } elseif ($addonfield == 'senddate') {
                    $forms .= Lang('startdate')."：<input type=\"text\" name=\"startdate\" value=\"\" /><br>";
                    $forms .= Lang('enddate')."：<input type=\"text\" name=\"enddate\" value=\"\" /><br>";
                    $addonstring .= 'senddate:datetime,';
                }
            }
        }
        if (is_array($addonfields) && !empty($addonfields)) {
            $query = "SELECT * FROM `#@__channeltype` WHERE id='$mid'";
            $channel = $dsql->GetOne($query);
            $dtp = new DedeTagParse();
            $dtp->SetNameSpace("field", "<", ">");
            $dtp->LoadSource($channel['fieldset']);
            $fieldarr = $itemarr = $typearr = array();
            foreach ($dtp->CTags as $ctag) {
                foreach ($addonfields as $addonfield) {

                    if ($ctag->GetName() == $addonfield) {
                        if ($addonfield == 'typeid' || $addonfield == 'senddate') continue;

                        $fieldarr[] = $addonfield;
                        $itemarr[] = $ctag->GetAtt('itemname');
                        $typearr[] = $ctag->GetAtt('type');
                        $valuearr[] = $ctag->GetAtt('default');
                    }
                }
            }
            foreach ($fieldarr as $k => $field) {
                $itemname = $itemarr[$k];
                $name = $field;
                $type = $typearr[$k];
                $tmp = $name.':'.$type;
                if (in_array($type, $intarr)) {
                    $forms .= "<br>$itemname : <input type=\"text\" name=\"start".$name."\" value=\"\" /> ".Lang("to")." <input type=\"text\" name=\"end".$name."\" value=\"\" /><br>";
                } else if (in_array($type, $textarr)) {
                    $forms .= "$itemname : <input type=\"text\" name=\"$name\" value=\"\" /><br>";
                } else if ($type == 'select') {
                    $values = explode(',', $valuearr[$k]);
                    if (is_array($values) && !empty($values)) {
                        $forms .= "<br>$itemname : <select name=\"$name\" ><option value=\"\">".Lang('unlimited')."</option>";
                        foreach ($values as $value) {
                            $forms .= "<option value=\"$value\">$value</option>";
                        }
                        $forms .= "</select>";
                    }
                } else if ($type == 'radio') {
                    $values = explode(',', $valuearr[$k]);
                    if (is_array($values) && !empty($values)) {
                        $forms .= "<br>$itemname : <label><input type=\"radio\" name=\"".$name."\" value=\"\" checked> ".Lang('unlimited')."</label>";
                        foreach ($values as $value) {
                            $forms .= "<label><input type=\"radio\" name=\"".$name."\" value=\"$value\"> $value</label>";
                        }
                    }
                } else if ($type == 'checkbox') {
                    $values = explode(',', $valuearr[$k]);
                    if (is_array($values) && !empty($values)) {
                        $forms .= "<br>$itemname : ";
                        foreach ($values as $value) {
                            $forms .= "<label><input type=\"checkbox\" name=\"".$name."[]\" value=\"$value\"> $value</label>";
                        }
                    }
                } elseif ($type == 'datetime') {
                    $forms .= "<br>".Lang('startdate')."：<input type=\"text\" name=\"startdate\" value=\"\" /><br>";
                    $forms .= Lang('enddate')."：<input type=\"text\" name=\"enddate\" value=\"\" /><br>";
                } else {
                    $tmp = '';
                }
                $addonstring .= $tmp.',';
            }
        }
        $forms .= '<input type="submit" name="submit" value="'.Lang('search').'" /></form>';
        $formssql = addslashes($forms);
        $query = "REPLACE INTO `#@__advancedsearch`(mid,maintable,mainfields,addontable,addonfields,forms,template) VALUES ('$mid','$maintable','$mainstring','$addontable','$addonstring','$formssql','$template')";
        $dsql->ExecuteNoneQuery($query);
        $formshtml = dede_htmlspecialchars($forms);
        echo '<meta charset="utf-8">';
        echo Lang('mychannel_modifysearch_tip')."<br><br><textarea cols=\"100\" rows=\"10\">".$forms."</textarea>";
        echo '<br>'.Lang('view').'：<br><hr>';
        echo $forms;
    }
    exit;
}
//删除自定义搜索
else if ($dopost == 'del') {
    $mid = intval($mid);
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__advancedsearch` WHERE mid = '$mid';");
    ShowMsg(Lang("mychannel_modifysearch_delete_success"), "mychannel_main.php");
    exit();
}
$row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id'");
require_once(DEDEADMIN."/templets/mychannel_edit.htm");
?>