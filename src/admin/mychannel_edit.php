<?php
/**
 * 修改文档模型
 *
 * @version        $id:mychannel_edit.php 14:49 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('c_Edit');
require_once(DEDEINC."/dedetag.class.php");
require_once(DEDEINC."/libraries/oxwindow.class.php");
if (empty($dopost)) $dopost = '';
$id = isset($id) && is_numeric($id) ? $id : 0;
if ($dopost == "show") {
    $dsql->ExecuteNoneQuery("UPDATE `#@__channeltype` SET isshow=1 WHERE id='$id' ");
    ShowMsg("启用一个文档模型", "mychannel_main.php");
    exit();
} else if ($dopost == "hide") {
    $dsql->ExecuteNoneQuery("UPDATE `#@__channeltype` SET isshow=0 WHERE id='$id' ");
    ShowMsg("隐藏一个文档模型", "mychannel_main.php");
    exit();
} else if ($dopost == "copystart") {
    if ($id == -1) {
        ShowMsg("专题文档模型不支持复制", "-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id'");
    if ($row['id'] > -1) {
        $nrow = $dsql->GetOne("SELECT MAX(id) AS id FROM `#@__channeltype` LIMIT 0,1 ");
        $newid = $nrow['id'] + 1;
        if ($newid < 10) {
            $newid = $newid + 10;
        }
        $idname = $newid;
    } else {
        $nrow = $dsql->GetOne("SELECT MIN(id) AS id FROM `#@__channeltype` LIMIT 0,1 ");
        $newid = $nrow['id'] - 1;
        if ($newid < -10) {
            $newid = $newid - 10;
        }
        $idname = 'w'.($newid * -1);
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id'");
    $wintitle = "复制文档模型";
    $win = new OxWindow();
    $win->Init("mychannel_edit.php", "/static/web/js/admin.blank.js", "post");
    $win->AddTitle("复制文档模型：".$row['typename']."");
    $win->AddHidden("cid", $id);
    $win->AddHidden("id", $id);
    $win->AddHidden("dopost", 'copysave');
    $msg = "<tr>
        <td width='260'>新模型id</td>
        <td><input name='newid' type='text' id='newid' value='{$newid}' class='admin-input-sm'></td>
    </tr>
    <tr>
        <td>新模型名称</td>
        <td><input name='newtypename' type='text' id='newtypename' value='{$row['typename']}{$idname}' class='admin-input-lg'></td>
    </tr>
    <tr>
        <td>新模型标识</td>
        <td><input name='newnid' type='text' id='newnid' value='{$row['nid']}{$idname}' class='admin-input-lg'></td>
    </tr>
    <tr>
        <td>新附加表</td>
        <td><input name='newaddtable' type='text' id='newaddtable' value='{$row['addtable']}{$idname}' class='admin-input-lg'></td>
    </tr>
    <tr>
        <td>复制模板</td>
        <td>
            <label><input type='radio' name='copytemplet' id='copytemplet' value='1' checked> 复制</label>
            <label><input type='radio' name='copytemplet' id='copytemplet' value='0'> 不复制</label>
        </td>
    </tr>";
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("ok", "");
    $win->Display();
    exit();
} else if ($dopost == "export") {
    if ($id == -1) {
        ShowMsg("专题模型不支持导出", "-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id' ");
    $channelconfig = '';
    $row['maintable'] = preg_replace('#biz_#', '#@__', $row['maintable']);
    $row['addtable'] = preg_replace('#biz_#', '#@__', $row['addtable']);
    foreach ($row as $k => $v) {
        if ($k == 'fieldset') $v = "\r\n$v\r\n";
        $channelconfig .= "<channel:{$k}>$v</channel:{$k}>\r\n";
    }
    $wintitle = "导出指定文档模型规则";
    $win = new OxWindow();
    $win->Init();
    $win->AddTitle("导出{$row['typename']}文档模型规则");
    $winform = $win->GetWindow("hand", "<link rel='stylesheet' href='/static/web/css/codemirror.css'><script src='/static/web/js/codemirror.js'></script><script src='/static/web/js/mode/xml/xml.js'></script><script src='/static/web/js/mode/javascript/javascript.js'></script><script src='/static/web/js/mode/css/css.js'></script><script src='/static/web/js/mode/htmlmixed/htmlmixed.js'></script><textarea name='config' id='content' class='form-control'>$channelconfig</textarea><script>var editor = CodeMirror.fromTextArea(document.getElementById('content'), {lineNumbers: true,lineWrapping: true,mode: 'text/html'});</script>");
    $win->Display();
    exit();
} else if ($dopost == "exportin") {
    $wintitle = "导入指定文档模型规则";
    $win = new OxWindow();
    $win->Init("mychannel_edit.php", "/static/web/js/admin.blank.js", "post");
    $win->AddHidden("dopost", "exportinok");
    $win->AddMsgItem("<tr><td><textarea name='exconfig' class='admin-textarea-xl'></textarea></td></tr>");
    $winform = $win->GetWindow("ok");
    $win->Display();
    exit();
} else if ($dopost == "exportinok") {
    require_once(DEDEADMIN."/inc/inc_admin_channel.php");
    function GotoStaMsg($msg)
    {
        global $wintitle, $winform;
        $wintitle = "导入指定文档模型规则";
        $win = new OxWindow();
        $win->Init();
        $win->AddMsgItem($msg);
        $winform = $win->GetWindow("hand");
        $win->Display();
        exit();
    }
    $msg = "操作失败";
    $exconfig = stripslashes($exconfig);
    $dtp = new DedeTagParse();
    $dtp->SetNameSpace('channel', '<', '>');
    $dtp->LoadSource($exconfig);
    if (!is_array($dtp->CTags)) GotoStaMsg("<tr>
        <td>文档模型规则出错</td>
    </tr>
    <tr>
        <td align='center'><button type='button' class='btn btn-success btn-sm' onclick=\"location='mychannel_main.php';\">文档模型管理</button></td>
    </tr>");
    $fields = array();
    foreach ($dtp->CTags as $ctag) {
        $fname = $ctag->GetName('name');
        $fields[$fname] = trim($ctag->GetInnerText());
    }
    if (!isset($fields['nid']) || !isset($fields['fieldset'])) {
        GotoStaMsg("<tr>
            <td>文档模型规则出错</td>
        </tr>
        <tr>
            <td align='center'><button type='button' class='btn btn-success btn-sm' onclick=\"location='mychannel_main.php';\">文档模型管理</button></td>
        </tr>");
    }
    //正常的导入过程
    $mysql_version = $dsql->GetVersion(true);
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE nid='{$fields['nid']}' ");
    if (is_array($row)) {
        GotoStaMsg("<tr>
            <td>已经存在相同的{$fields['nid']}模型</td>
        </tr>
        <tr>
            <td align='center'><button type='button' class='btn btn-success btn-sm' onclick=\"location='mychannel_main.php';\">文档模型管理</button></td>
        </tr>");
    }
    //创建表
    if ($fields['issystem'] != -1) {
        $tabsql = "CREATE TABLE IF NOT EXISTS `{$fields['addtable']}` (`aid` int(11) NOT NULL DEFAULT '0',`typeid` int(11) NOT NULL DEFAULT '0',`redirecturl` varchar(255) NOT NULL DEFAULT '',`templet` varchar(30) NOT NULL DEFAULT '',`userip` char(46) NOT NULL DEFAULT '',";
    } else {
        $tabsql = "CREATE TABLE IF NOT EXISTS `{$fields['addtable']}` (`aid` int(11) NOT NULL DEFAULT '0',`typeid` int(11) NOT NULL DEFAULT '0',`channel` SMALLINT NOT NULL DEFAULT '0',`arcrank` SMALLINT NOT NULL DEFAULT '0',`mid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',`click` INT(10) UNSIGNED NOT NULL DEFAULT '0',`title` varchar(255) NOT NULL DEFAULT '',`senddate` int(11) NOT NULL DEFAULT '0',`flag` set('c','h','p','f','s','j','a','b') DEFAULT NULL,";
    }
    if ($mysql_version < 4.1) {
        $tabsql .= " PRIMARY KEY (`aid`), KEY `typeid` (`typeid`)\r\n) TYPE=MyISAM; ";
    } else {
        $tabsql .= " PRIMARY KEY (`aid`), KEY `typeid` (`typeid`)\r\n) ENGINE=MyISAM DEFAULT CHARSET=".$cfg_db_language."; ";
    }
    $rs = $dsql->ExecuteNoneQuery($tabsql);
    if (!$rs) {
        GotoStaMsg("<tr>
            <td>创建数据表失败：{$dsql->GetError()}</td>
        </tr>
        <tr>
            <td align='center'><button type='button' class='btn btn-success btn-sm' onclick=\"location='mychannel_main.php';\">文档模型管理</button></td>
        </tr>");
        exit();
    }
    if ($fields['issystem'] == 1) $fields['issystem'] = 0;
    if ($fields['issystem'] == 0) {
        $row = $dsql->GetOne("SELECT id FROM `#@__channeltype` ORDER BY id DESC ");
        $fields['newid'] = $row['id'] + 1;
    } else {
        $row = $dsql->GetOne("SELECT id FROM `#@__channeltype` ORDER BY id ASC ");
        $fields['newid'] = $row['id'] - 1;
    }
    $fieldset = $fields['fieldset'];
    $fields['fieldset'] = addslashes($fields['fieldset']);
    $inquery = "INSERT INTO `#@__channeltype` (`id`,`nid`,`typename`,`addtable`,`addcon`,`mancon`,`editcon`,`useraddcon`,`usermancon`,`usereditcon`,`fieldset`,`listfields`,`issystem`,`isshow`,`issend`,`arcsta`,`usertype`,`sendrank`) VALUES ('{$fields['newid']}','{$fields['nid']}','{$fields['typename']}','{$fields['addtable']}','{$fields['addcon']}','{$fields['mancon']}','{$fields['editcon']}','{$fields['useraddcon']}','{$fields['usermancon']}','{$fields['usereditcon']}','{$fields['fieldset']}','{$fields['listfields']}','{$fields['issystem']}','{$fields['isshow']}','{$fields['issend']}','{$fields['arcsta']}','{$fields['usertype']}','{$fields['sendrank']}'); ";
    $rs = $dsql->ExecuteNoneQuery($inquery);
    if (!$rs) GotoStaMsg("<tr>
        <td>导入文档模型时发生错误：{$dsql->GetError()}</td>
    </tr>
    <tr>
        <td align='center'><button type='button' class='btn btn-success btn-sm' onclick=\"location='mychannel_main.php';\">文档模型管理</button></td>
    </tr>");
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
            $dsql->ExecuteNoneQuery(" ALTER TABLE `{$fields['addtable']}` ADD $ntabsql ");
        }
    }
    if ($allfields != '') {
        $dsql->ExecuteNoneQuery("UPDATE `#@__channeltype` SET listfields='$allfields' WHERE id='{$fields['newid']}' ");
    }
    GotoStaMsg("<tr>
        <td>成功导入一个文档模型</td>
    </tr>
    <tr>
        <td align='center'><button type='button' class='btn btn-success btn-sm' onclick=\"location='mychannel_main.php';\">文档模型管理</button></td>
    </tr>");
} else if ($dopost == "copysave") {
    $cid = intval($cid);
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$cid' ", MYSQL_ASSOC);
    foreach ($row as $k => $v) {
        ${strtolower($k)} = addslashes($v);
    }
    $inquery = " INSERT INTO `#@__channeltype` (`id`,`nid`,`typename`,`addtable`,`addcon`,`mancon`,`editcon`,`useraddcon`,`usermancon`,`usereditcon`,`fieldset`,`listfields`,`issystem`,`isshow`,`issend`,`arcsta`,`usertype`,`sendrank` ) VALUES ('$newid','$newnid','$newtypename','$newaddtable','$addcon','$mancon','$editcon','$useraddcon','$usermancon','$usereditcon','$fieldset','$listfields','$issystem','$isshow','$issend','$arcsta','$usertype','$sendrank'); ";
    $mysql_version = $dsql->GetVersion(TRUE);
    if (!$dsql->IsTable($newaddtable)) {
        $dsql->Execute('me', "SHOW CREATE TABLE {$dsql->dbName}.{$addtable}");
        $row = $dsql->GetArray('me', MYSQL_BOTH);
        $tableStruct = $row[1];
        $tb = str_replace('#@__', $cfg_dbprefix, $addtable);
        $tableStruct = preg_replace("/CREATE TABLE `$addtable` /iU", "CREATE TABLE `$newaddtable`", $tableStruct);
        $dsql->ExecuteNoneQuery($tableStruct);
    }
    if ($copytemplet == 1) {
        $tmpletdir = $cfg_basedir.$cfg_templets_dir.'/'.$cfg_df_style;
        copy("{$tmpletdir}/article_{$nid}.htm", "{$tmpletdir}/article_{$newnid}.htm");
        copy("{$tmpletdir}/list_{$nid}.htm", "{$tmpletdir}/list_{$newnid}.htm");
        copy("{$tmpletdir}/index_{$nid}.htm", "{$tmpletdir}/index_{$newnid}.htm");
    }
    $rs = $dsql->ExecuteNoneQuery($inquery);
    if ($rs) {
        ShowMsg("成功复制文档模型，正在前往模型管理", "mychannel_edit.php?id={$newid}&dopost=edit");
        exit();
    } else {
        $errv = $dsql->GetError();
        ShowMsg("复制文档模型失败，错误提示：$errv", "javascript:;");
        exit();
    }
} else if ($dopost == "save") {
    $fieldset = preg_replace("#[\r\n]{1,}#", "\r\n", $fieldset);
    $usertype = empty($usertype) ? '' : $usertype;
    $query = "UPDATE `#@__channeltype` SET typename='$typename',addtable='$addtable',addcon='$addcon',mancon='$mancon',editcon='$editcon',useraddcon='$useraddcon',usermancon='$usermancon',usereditcon='$usereditcon',fieldset='$fieldset',listfields='$listfields',issend='$issend',arcsta='$arcsta',usertype='$usertype',sendrank='$sendrank',needdes='$needdes',needpic='$needpic',titlename='$titlename',onlyone='$onlyone',dfcid='$dfcid' WHERE id='$id' ";
    if (trim($fieldset) != '') {
        $dtp = new DedeTagParse();
        $dtp->SetNameSpace("field", "<", ">");
        $dtp->LoadSource(stripslashes($fieldset));
        if (!is_array($dtp->CTags)) {
            ShowMsg("文本配置参数无效，无法进行解析", "-1");
            exit();
        }
    }
    $trueTable = str_replace("#@__", $cfg_dbprefix, $addtable);
    if (!$dsql->IsTable($trueTable)) {
        ShowMsg("系统找不到您所指定的".$trueTable."表", "-1");
        exit();
    }
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("成功修改一个文档模型", "mychannel_main.php");
    exit();
} else if ($dopost == "gettemplets") {
    require_once(DEDEINC."/libraries/oxwindow.class.php");
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id'");
    $wintitle = "查看模型应用模板";
    $win = new OxWindow();
    $win->Init("", "/static/web/js/admin.blank.js", "");
    $win->AddTitle("栏目".$row['typename']."默认模板文件说明");
    $defaulttemplate = $cfg_templets_dir.'/'.$cfg_df_style;
    $msg = "<tr>
        <td>
            <span>文档模板：{$defaulttemplate}/article_{$row['nid']}.htm</span>
            <a href='tpl.php?acdir={$cfg_df_style}&action=edit&filename=article_{$row['nid']}.htm' class='btn btn-success btn-sm'>修改</a>
        </td>
    </tr>
    <tr>
        <td>
            <span>列表模板：{$defaulttemplate}/list_{$row['nid']}.htm</span>
            <a href='tpl.php?acdir={$cfg_df_style}&action=edit&filename=list_{$row['nid']}.htm' class='btn btn-success btn-sm'>修改</a>
        </td>
    </tr>
    <tr>
        <td>
            <span>封面栏目模板：{$defaulttemplate}/index_{$row['nid']}.htm</span>
            <a href='tpl.php?acdir={$cfg_df_style}&action=edit&filename=index_{$row['nid']}.htm' class='btn btn-success btn-sm'>修改</a>
        </td>
    </tr>";
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", "");
    $win->Display();
    exit();
} else if ($dopost == "delete") {
    CheckPurview('c_Del');
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id'");
    if ($row['issystem'] == 1) {
        ShowMsg("系统文档模型不允许删除", "mychannel_main.php");
        exit();
    }
    if (empty($job)) $job = '';
    //确认提示
    if ($job == "") {
        require_once(DEDEINC."/libraries/oxwindow.class.php");
        $wintitle = "删除指定文档模型";
        $win = new OxWindow();
        $win->Init("mychannel_edit.php", "/static/web/js/admin.blank.js", "POST");
        $win->AddHidden("job", "yes");
        $win->AddHidden("dopost", $dopost);
        $win->AddHidden("id", $id);
        $win->AddTitle("您确定要删除".$row['typename']."模型吗");
        $winform = $win->GetWindow("ok");
        $win->Display();
        exit();
    } else if ($job == "yes") {
        require_once(DEDEINC."/typelink/typeunit.class.admin.php");
        $myrow = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$id'", MYSQL_ASSOC);
        if (!is_array($myrow)) {
            ShowMsg('您所指定的栏目信息不存在', '-1');
            exit();
        }
        //检查栏目的表是否独占数据表
        $addtable = str_replace($cfg_dbprefix, '', str_replace('#@__', $cfg_dbprefix, $myrow['addtable']));
        $row = $dsql->GetOne("SELECT COUNT(id) AS dd FROM `#@__channeltype` WHERE addtable like '{$cfg_dbprefix}{$addtable}' OR addtable LIKE CONCAT('#','@','__','$addtable') ;");
        $isExclusive2 = ($row['dd'] > 1 ? 0 : 1);
        //获取与栏目关连的所有栏目id
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
        //删除栏目配置信息
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__channeltype` WHERE id='$id' ");
        //更新栏目缓存
        UpDateCatCache($dsql);
        ShowMsg("成功删除一个文档模型", "mychannel_main.php");
        exit();
    }
} else if ($dopost == 'modifysearch') {
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
        $mainfields = '<label><input type="checkbox" name="mainfields[]" '.$c1.' value="iscommend"> 是否推荐</label> ';
        $mainfields .= '<label><input type="checkbox" name="mainfields[]" '.$c2.' value="typeid"> 栏目</label> ';
        $mainfields .= '<label><input type="checkbox" name="mainfields[]" '.$c3.' value="writer"> 作者</label> ';
        $mainfields .= '<label><input type="checkbox" name="mainfields[]" '.$c4.' value="source"> 来源</label> ';
        $mainfields .= '<label><input type="checkbox" name="mainfields[]" '.$c5.' value="senddate"> 发布时间</label> ';
        $query = "SELECT * FROM `#@__channeltype` WHERE id='$mid'";
        $channel = $dsql->GetOne($query);
        $searchtype = array('int', 'datetime', 'float', 'textdata', 'textchar', 'text', 'htmltext', 'multitext', 'select', 'radio', 'checkbox');
        $addonfields = '';
        $dtp = new DedeTagParse();
        $dtp->SetNameSpace("field", "<", ">");
        $dtp->LoadSource($channel['fieldset']);
        if ($channel['issystem'] < 0) {
            $checked = in_array('typeid', $addonfieldsarr) ? 'checked' : '';
            $addonfields .= '<label><input type="checkbox" name="addonfields[]" '.$checked.' value="typeid"> 栏目</label> ';
            $checked = in_array('senddate', $addonfieldsarr) ? 'checked' : '';
            $addonfields .= '<label><input type="checkbox" name="addonfields[]" '.$checked.' value="senddate"> 发布时间</label> ';
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
                    $addonfields .= "<label><input type='checkbox' name='addonfields[]' value='$value' $checked> $label</label> ";
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
        $forms = "<form action=\"$cfg_cmspath/apps/advancedsearch.php\" method=\"post\">";
        $forms .= "<input type=\"hidden\" name=\"mid\" value=\"$mid\">";
        $forms .= "<input type=\"hidden\" name=\"dopost\" value=\"search\">";
        $forms .= "<label>关键词：<input type=\"text\" name=\"q\"></label><br>";
        $mainstring = '';
        if (!empty($mainfields) && is_array($mainfields)) {
            $mainstring = implode(',', $mainfields);
            foreach ($mainfields as $mainfield) {
                if ($mainfield == 'typeid') {
                    require_once(DEDEINC."/typelink/typelink.class.php");
                    $tl = new TypeLink(0);
                    $typeOptions = $tl->GetOptionArray(0, 0, $mid);
                    $forms .= "<select name=\"typeid\">\r\n";
                    $forms .= "<option value=\"0\" selected>不限栏目</option>\r\n";
                    $forms .= $typeOptions;
                    $forms .= "</select><br>";
                    $forms .= "<label><input type=\"checkbox\" name=\"includesons\" value=\"1\"> 包含子栏目</label><br>";
                } else if ($mainfield == 'iscommend') {
                    $forms .= "<label><input type=\"checkbox\" name=\"iscommend\" value=\"1\"> 推荐</label><br>";
                } else if ($mainfield == 'writer') {
                    $forms .= "<label>作者：<input type=\"text\" name=\"writer\" value=\"\"></label><br>";
                } else if ($mainfield == 'source') {
                    $forms .= "<label>来源：<input type=\"text\" name=\"source\" value=\"\"></label><br>";
                } else if ($mainfield == 'senddate') {
                    $forms .= "<label>开始时间：<input type=\"text\" name=\"startdate\" value=\"\"></label><br>";
                    $forms .= "<label>结束时间：<input type=\"text\" name=\"enddate\" value=\"\"></label><br>";
                }
            }
        }
        $addonstring = '';
        $intarr = array('int', 'float');
        $textarr = array('textdata', 'textchar', 'text', 'htmltext', 'multitext');
        if ($channel['issystem'] < 0) {
            foreach ((array)$addonfields as $addonfield) {
                if ($addonfield == 'typeid') {
                    require_once(DEDEINC."/typelink/typelink.class.php");
                    $tl = new TypeLink(0);
                    $typeOptions = $tl->GetOptionArray(0, 0, $mid);
                    $forms .= "<select name=\"typeid\">\r\n";
                    $forms .= "<option value=\"0\" selected>不限栏目</option>\r\n";
                    $forms .= $typeOptions;
                    $forms .= "</select><br>";
                    $forms .= "<label><input type=\"checkbox\" name=\"includesons\" value=\"1\"> 包含子栏目</label><br>";
                    $addonstring .= 'typeid:int,';
                } elseif ($addonfield == 'senddate') {
                    $forms .= "<label>开始时间：<input type=\"text\" name=\"startdate\" value=\"\"></label><br>";
                    $forms .= "<label>结束时间：<input type=\"text\" name=\"enddate\" value=\"\"></label><br>";
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
                    $forms .= "$itemname：<input type=\"text\" name=\"start".$name."\" value=\"\"> 到 <input type=\"text\" name=\"end".$name."\" value=\"\"><br>";
                } else if (in_array($type, $textarr)) {
                    $forms .= "$itemname：<input type=\"text\" name=\"$name\" value=\"\"><br>";
                } else if ($type == 'select') {
                    $values = explode(',', $valuearr[$k]);
                    if (is_array($values) && !empty($values)) {
                        $forms .= "$itemname：<select name=\"$name\"><option value=\"\">不限</option>";
                        foreach ($values as $value) {
                            $forms .= "<option value=\"$value\">$value</option>";
                        }
                        $forms .= "</select><br>";
                    }
                } else if ($type == 'radio') {
                    $values = explode(',', $valuearr[$k]);
                    if (is_array($values) && !empty($values)) {
                        $forms .= "$itemname：<label><input type=\"radio\" name=\"".$name."\" value=\"\" checked> 不限</label><br>";
                        foreach ($values as $value) {
                            $forms .= "<label><input type=\"radio\" name=\"".$name."\" value=\"$value\"> $value</label>";
                        }
                    }
                } else if ($type == 'checkbox') {
                    $values = explode(',', $valuearr[$k]);
                    if (is_array($values) && !empty($values)) {
                        $forms .= "$itemname：";
                        foreach ($values as $value) {
                            $forms .= "<label><input type=\"checkbox\" name=\"".$name."[]\" value=\"$value\"> $value</label><br>";
                        }
                    }
                } elseif ($type == 'datetime') {
                    $forms .= "<label>开始时间：<input type=\"text\" name=\"startdate\" value=\"\"></label><br>";
                    $forms .= "<label>结束时间：<input type=\"text\" name=\"enddate\" value=\"\"></label><br>";
                } else {
                    $tmp = '';
                }
                $addonstring .= $tmp.',';
            }
        }
        $forms .= "<input type=\"submit\" name=\"submit\" value=\"开始搜索\" class=\"btn btn-success btn-sm\"></form>";
        $formssql = addslashes($forms);
        $query = "REPLACE INTO `#@__advancedsearch` (mid, maintable, mainfields, addontable, addonfields, forms, template) VALUES ('$mid','$maintable','$mainstring','$addontable','$addonstring','$formssql', '$template')";
        $dsql->ExecuteNoneQuery($query);
        $formshtml = dede_htmlspecialchars($forms);
        echo '<link rel="stylesheet" href="/static/web/css/bootstrap.min.css">
        <link rel="stylesheet" href="/static/web/css/admin.css">
        <link rel="stylesheet" href="/static/web/css/codemirror.css">
		<script src="/static/web/js/codemirror.js"></script>
		<script src="/static/web/js/mode/xml/xml.js"></script>
		<script src="/static/web/js/mode/javascript/javascript.js"></script>
		<script src="/static/web/js/mode/css/css.js"></script>
		<script src="/static/web/js/mode/htmlmixed/htmlmixed.js"></script>';
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$cfg_soft_lang\">";
        echo "<p>下面生成的网页表单，根据自己需求修改样式后粘贴到对应的模板中</p><textarea id='content' class='form-control'>$forms</textarea>";
        echo "<hr>";
        echo "<script>var editor = CodeMirror.fromTextArea(document.getElementById('content'), {
            lineNumbers: true,
            lineWrapping: true,
            mode: 'text/html',
        });</script>";
        echo $forms;
    }
    exit;
}
//删除自定义搜索
else if ($dopost == 'del') {
    $mid = intval($mid);
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__advancedsearch` WHERE mid = '$mid';");
    ShowMsg("成功删除一个自定义搜索", "mychannel_main.php");
    exit();
}
$row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id' ");
require_once(DEDEADMIN."/templets/mychannel_edit.htm");
?>