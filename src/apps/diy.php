<?php
/**
 * 自定义表单
 *
 * @version        $id:diy.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
$diyid = isset($diyid) && is_numeric($diyid) ? $diyid : 0;
$action = isset($action) && in_array($action, array('post', 'list', 'view')) ? $action : 'post';
$id = isset($id) && is_numeric($id) ? $id : 0;
if (empty($diyid)) {
    showMsg('操作失败', '/');
    exit();
}
require_once DEDEINC.'/diyform.class.php';
$diy = new diyform($diyid);
if ($action == 'post') {
    if (empty($do)) {
        $postform = $diy->getForm(true);
        include DEDEROOT."/theme/apps/{$diy->postTemplate}";
        exit();
    } elseif ($do == 2) {
        $dede_fields = empty($dede_fields) ? '' : trim($dede_fields);
        $dede_fieldshash = empty($dede_fieldshash) ? '' : trim($dede_fieldshash);
        if (!empty($dede_fields)) {
            if ($dede_fieldshash != md5($dede_fields.$cfg_cookie_encode)) {
                showMsg('数据校验不对', '-1');
                exit();
            }
        }
        $diyform = $dsql->GetOne("SELECT * FROM `#@__diyforms` WHERE diyid='$diyid' ");
        if (!is_array($diyform)) {
            showmsg('表单不存在', '-1');
            exit();
        }
        $addvar = $addvalue = '';
        if (!empty($dede_fields)) {
            $fieldarr = explode(';', $dede_fields);
            if (is_array($fieldarr)) {
                foreach ($fieldarr as $field) {
                    if ($field == '') continue;
                    $fieldinfo = explode(',', $field);
                    if ($fieldinfo[1] == 'textdata') {
                        ${$fieldinfo[0]} = FilterSearch(stripslashes(${$fieldinfo[0]}));
                        ${$fieldinfo[0]} = addslashes(${$fieldinfo[0]});
                    }
                    //获取提交链接，表单添加字段名称为链接，字段标识默认为link，数据类型为单行文本后模板里用<input type="hidden" name="link">使用
                    if ($fieldinfo[0] == 'link') {
                        ${$fieldinfo[0]} = $_SERVER['HTTP_REFERER'];
                    }
                    //获取提交地址，表单添加字段名称为地址，字段标识默认为ip，数据类型为单行文本后模板里用<input type="hidden" name="ip">使用
                    if ($fieldinfo[0] == 'ip') {
                        ${$fieldinfo[0]} = GetIP();
                    }
                    //获取提交日期，表单添加字段名称为日期，字段标识默认为date，数据类型为单行文本后模板里用<input type="hidden" name="date">使用
                    if ($fieldinfo[0] == 'date') {
                        ${$fieldinfo[0]} = date("Y-m-d H:i:s");
                    } else {
                        ${$fieldinfo[0]} = GetFieldValue(${$fieldinfo[0]}, $fieldinfo[1],0,'add','','diy', $fieldinfo[0]);
                    }
                    $addvar .= ', `'.$fieldinfo[0].'`';
                    $addvalue .= ", '".${$fieldinfo[0]}."'";
                }
            }
        }
        $query = "INSERT INTO `{$diy->table}` (`id`, `ifcheck` $addvar) VALUES (NULL, 0 $addvalue); ";
        if ($dsql->ExecuteNoneQuery($query)) {
            $id = $dsql->GetLastID();
            $mailtitle = "{$diy->name}-留言通知";
            $mailbody = '';
            foreach($diy->getFieldList() as $field=>$fieldvalue)
            {
                $mailbody .= "{$fieldvalue[0]}：{${$field}}\r\n";
            }
            if ($cfg_sendmail_bysmtp == 'Y' && !empty($cfg_smtp_server)) {
                $mailtype = 'TXT';
                require_once(DEDEINC.'/libraries/mail.class.php');
                $smtp = new smtp($cfg_smtp_server, $cfg_smtp_port, true, $cfg_smtp_usermail, $cfg_smtp_password);
                $smtp->debug = false;
                $smtp->sendmail($cfg_adminemail, $cfg_webname, $cfg_smtp_usermail, $mailtitle, $mailbody, $mailtype);
            } else {
                @mail($cfg_adminemail, $mailtitle, $mailbody, $headers);
            }
            if ($diy->public == 2) {
                $goto = "diy.php?action=list&diyid={$diy->diyid}";
                $bkmsg = '提交成功，正在前往表单列表';
            } else {
                $goto = !empty($cfg_cmspath) ? $cfg_cmspath : '/';
                $bkmsg = '提交成功，请等待管理员处理';
            }
            ShowMsg($bkmsg, $goto);
        }
    }
} else if ($action == 'list') {
    if (empty($diy->public)) {
        ShowMsg('表单已关闭前台浏览', 'javascript:;');
        exit();
    }
    include_once DEDEINC.'/datalistcp.class.php';
    if ($diy->public == 2)
        $query = "SELECT * FROM `{$diy->table}` ORDER BY id DESC";
    else
        $query = "SELECT * FROM `{$diy->table}` WHERE ifcheck=1 ORDER BY id DESC";
    $datalist = new DataListCP();
    $datalist->pagesize = 10;
    $datalist->SetParameter('action', 'list');
    $datalist->SetParameter('diyid', $diyid);
    $datalist->SetTemplate(DEDEINC."/../theme/apps/{$diy->listTemplate}");
    $datalist->SetSource($query);
    $fieldlist = $diy->getFieldList();
    $datalist->Display();
} else if ($action == 'view') {
    if (empty($diy->public)) {
        showMsg('表单已关闭前台浏览', '/');
        exit();
    }
    if (empty($id)) {
        showMsg('操作失败，未指定id', '/');
        exit();
    }
    if ($diy->public == 2) {
        $query = "SELECT * FROM `{$diy->table}` WHERE id='$id' ";
    } else {
        $query = "SELECT * FROM `{$diy->table}` WHERE id='$id' AND ifcheck=1";
    }
    $row = $dsql->GetOne($query);
    if (!is_array($row)) {
        showmsg('您浏览的记录不存在或待审核', '-1');
        exit();
    }
    $fieldlist = $diy->getFieldList();
    include DEDEROOT."/theme/apps/{$diy->viewTemplate}";
}
?>