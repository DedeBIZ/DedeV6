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
                showMsg('表单校验失败', '-1');
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
            $link = $_SERVER['HTTP_REFERER'];
            $date = GetDateTimeMk(time());
            $ip = GetIP();
            $fieldarr = explode(';', $dede_fields);
            if (is_array($fieldarr)) {
                foreach ($fieldarr as $field) {
                    if ($field == '') continue;
                    $fieldinfo = explode(',', $field);
                    if ($fieldinfo[1] == 'textdata') {
                        ${$fieldinfo[0]} = FilterSearch(stripslashes(${$fieldinfo[0]}));
                        ${$fieldinfo[0]} = addslashes(${$fieldinfo[0]});
                    } else {
                        ${$fieldinfo[0]} = GetFieldValue(${$fieldinfo[0]}, $fieldinfo[1],0,'add','','diy', $fieldinfo[0]);
                    }
                    $addvar .= ', `'.$fieldinfo[0].'`';
                    $addvalue .= ", '".${$fieldinfo[0]}."'";
                }
            }
        }
        //判断$name是否输入中文包括繁体则提交失败，$name改成您表单字段标识，恢复注释代码使用
        /*if (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $name)) {
            showMsg('您输入信息不符合，请重新填写', '-1');
            exit();
        }*/
        //判断$message是否大于70字符则提交失败，$message改成您表单字段标识，恢复注释代码使用
        /*if (mb_strlen($message) > 70) {
            showmsg('您输入文字太多了，请重新填写', '-1');
            exit();
        }*/
        //获取表单提交的链接、时间、ip，字段标识默认为link、date、ip，前台表单可以不用出现该输入框，但是biz_fields和biz_fieldshash的值要最新，下面是重复提交表单限制，恢复注释代码使用
        /*$result = $dsql->getOne("SELECT count(*) AS dd FROM `{$diy->table}` WHERE ip='$ip' AND date_format(date,'Y-m-d') = date_format(now(),'Y-m-d')");
        if ($result['dd'] >= 3) {
            showmsg('您重复提交太多了，请等待平台联系', '-1');
            exit();
        }*/
        $query = "INSERT INTO `{$diy->table}` (`id`, `ifcheck` $addvar) VALUES (NULL, 0 $addvalue); ";
        if ($dsql->ExecuteNoneQuery($query)) {
            $id = $dsql->GetLastID();
            $mailtitle = "{$diy->name}通知";
            $mailbody = '';
            foreach($diy->getFieldList() as $field=>$fieldvalue)
            {
                $mailbody .= "{$fieldvalue[0]}：{${$field}}\r\n";
            }
            $headers = "From: ".$cfg_adminemail."Reply-To: ".$cfg_adminemail;
            $mailbody = mb_convert_encoding($mailbody, "GBK", "UTF-8");
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
                $goto = 'javascript:history.go(-1);';
                $bkmsg = '提交成功，请等待平台联系';
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
    if ($diy->public == 2) {
        $query = "SELECT * FROM `{$diy->table}` ORDER BY id DESC";
    } else {
        $query = "SELECT * FROM `{$diy->table}` WHERE ifcheck=1 ORDER BY id DESC";
    }
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
        showmsg('您浏览的记录不存在或未审核', '-1');
        exit();
    }
    $fieldlist = $diy->getFieldList();
    include DEDEROOT."/theme/apps/{$diy->viewTemplate}";
}
?>