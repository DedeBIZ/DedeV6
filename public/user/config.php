<?php
/**
 * @version        $Id: config.php 1 8:38 2010年7月9日Z tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
//针对会员中心操作进行XSS过滤
function XSSClean($val)
{
    global $cfg_soft_lang;
    if ($cfg_soft_lang == 'gb2312') gb2utf8($val);
    if (is_array($val)) {
        //while (list($key) = each($val))
        foreach ($val as $key => $value) {
            if (in_array($key, array('tags', 'body', 'dede_fields', 'dede_addonfields', 'dopost', 'introduce'))) continue;
            $val[$key] = XSSClean($val[$key]);
        }
        return $val;
    }
    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); //with a ;
        $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); //with a ;
    }

    $val = str_replace("`", "‘", $val);
    $val = str_replace("'", "‘", $val);
    $val = str_replace("\"", "“", $val);
    $val = str_replace(",", "，", $val);
    $val = str_replace("(", "（", $val);
    $val = str_replace(")", "）", $val);

    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true;
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(&#0{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
            $val = preg_replace($pattern, $replacement, $val);
            if ($val_before == $val) {
                $found = false;
            }
        }
    }
    if ($cfg_soft_lang == 'gb2312') utf82gb($val);
    return $val;
}
$_GET = XSSClean($_GET);
$_POST = XSSClean($_POST);
$_REQUEST = XSSClean($_REQUEST);
$_COOKIE = XSSClean($_COOKIE);

require_once(dirname(__FILE__).'/../../system/common.inc.php');
require_once(DEDEINC.'/filter.inc.php');
require_once(DEDEINC.'/memberlogin.class.php');
require_once(DEDEINC.'/dedetemplate.class.php');

//检查CSRF
function CheckCSRF()
{
    $cc_csrf_token_check = GetCookie("dede_csrf_token");
    if (
        !(isset($_POST['_csrf_token'], $cc_csrf_token_check)
            && is_string($_POST['_csrf_token']) && is_string($cc_csrf_token_check)
            && hash_equals($_POST['_csrf_token'], $cc_csrf_token_check))
    ) {
        ShowMsg('CSRF校验失败，请刷新页面重新提交', '-1');
        exit();
    }

    DropCookie("dede_csrf_token");
}

//生成CSRF校验token，在比较重要的表单中应该要加上这个token校验
$cc_csrf_token = GetCookie("dede_csrf_token");
if (!isset($GLOBALS['csrf_token']) || $GLOBALS['csrf_token'] === null) {
    if (
        isset($cc_csrf_token) && is_string($cc_csrf_token)
        && preg_match('#^[0-9a-f]{32}$#iS', $cc_csrf_token) === 1
    ) {
        $GLOBALS['csrf_token'] = $cc_csrf_token;
    } else {
        $GLOBALS['csrf_token'] = md5(uniqid(mt_rand(), TRUE));
    }
}

if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    PutCookie('dede_csrf_token', $GLOBALS['csrf_token'], 7200, '/');
}


//获得当前脚本名称，如果您的系统被禁用了$_SERVER变量，请自行修改这个选项
$dedeNowurl = $s_scriptName = '';
$dedeNowurl = GetCurUrl();
$dedeNowurls = explode('?', $dedeNowurl);
$s_scriptName = $dedeNowurls[0];
$menutype = '';
$menutype_son = '';
$gourl = empty($gourl) ? "" : RemoveXSS($gourl);

//检查是否开放会员功能
if ($cfg_mb_open == 'N') {
    if (defined('AJAXLOGIN')) {
        if ($format === 'json') {
            echo json_encode(array(
                "code" => -1,
                "msg" => "系统关闭了会员功能，因此您无法访问此页面",
                "data" => null,
            ));
            exit;
        } else {
            die('');
        }
    } else {
        ShowMsg("系统关闭了会员功能，因此您无法访问此页面", "javascript:;");
        exit();
    }
}
$keeptime = isset($keeptime) && is_numeric($keeptime) ? $keeptime : -1;
$cfg_ml = new MemberLogin($keeptime);

//判断用户是否登录
$myurl = '';
if ($cfg_ml->IsLogin()) {
    $myurl = $cfg_memberurl."/index.php?uid=".urlencode($cfg_ml->M_LoginID);
    if (!preg_match("#^http[s]?:#i", $myurl)) $myurl = $cfg_basehost.$myurl;
    if ($cfg_ml->fields['face'] == "") {
        $cfg_ml->fields['face'] = $cfg_cmsurl."/static/img/avatar.png";
    }
}

/** 有没新短信 **/
$pms = $dsql->GetOne("SELECT COUNT(*) AS nums FROM `#@__member_pms` WHERE toid='{$cfg_ml->M_ID}' AND `hasview`=0 AND folder = 'inbox'");

/**
 *  检查用户是否有权限进行某个操作
 *
 * @param     int  $rank  权限值
 * @param     int  $money  金币
 * @return    void
 */
function CheckRank($rank = 0, $money = 0)
{
    global $cfg_ml, $cfg_memberurl, $cfg_mb_spacesta,$dsql;
    if (!$cfg_ml->IsLogin()) {
        header("Location:{$cfg_memberurl}/login.php?gourl=".urlencode(GetCurUrl()));
        exit();
    } else {
        if ($cfg_mb_spacesta == '-10') {
            //var_dump($cfg_ml->fields);
            //如果启用注册邮件验证
            if ($cfg_ml->fields['spacesta'] == '-10') {
                if (empty($cfg_ml->fields['email'])) {
                    ShowMsg("邮箱地址为空，请设置一个可用的邮箱地址", "edit_email.php", 0, 5000);
                    exit;
                }
                $msg = "您尚未进行邮件验证，请到邮箱查阅</br>重新发送邮件验证 <a href='{$cfg_memberurl}/index_do.php?fmdo=sendMail'><span style='color:#dc3545'>点击此处</span></a>";
                ShowMsg($msg, "-1", 0, 5000);
                exit;
            }
        }
        if ($cfg_ml->M_Rank < $rank) {
            $needname = "";
            if ($cfg_ml->M_Rank == 0) {
                $row = $dsql->GetOne("SELECT membername FROM `#@__arcrank` WHERE rank='$rank'");
                $myname = "普通会员";
                $needname = $row['membername'];
            } else {
                $dsql->SetQuery("SELECT membername From `#@__arcrank` WHERE rank='$rank' OR rank='".$cfg_ml->M_Rank."' ORDER BY rank DESC");
                $dsql->Execute();
                $row = $dsql->GetObject();
                $needname = $row->membername;
                if ($row = $dsql->GetObject()) {
                    $myname = $row->membername;
                } else {
                    $myname = "普通会员";
                }
            }
            ShowMsg("对不起，需要：<span style='font-size:11pt;color:red'>$needname</span> 才能访问本页面<br>您目前的等级是：<span style='font-size:11pt;color:red'>$myname</span> ", "-1", 0, 5000);
            exit();
        } else if ($cfg_ml->M_Money < $money) {
            ShowMsg("对不起，需要花费金币：<span style='font-size:11pt;color:red'>$money</span> 才能访问本页面<br>您目前拥有的金币是：<span style='font-size:11pt;color:red'>".$cfg_ml->M_Money."</span>  ", "-1", 0, 5000);
            exit();
        }
    }
}

/**
 *  更新文档统计
 *
 * @access    public
 * @param     int  $channelid  频道模型id
 * @return    string
 */
function countArchives($channelid)
{
    global $cfg_ml, $dsql;
    $id = (int)$channelid;
    if ($cfg_ml->IsLogin()) {
        $channeltype = array(1 => 'article', 2 => 'album', 3 => 'soft', -8 => 'infos');
        if (isset($channeltype[$id])) {
            $_field = $channeltype[$id];
        } else {
            $_field = 'articles';
        }
        $row = $dsql->GetOne("SELECT COUNT(*) AS nums FROM #@__archives WHERE channel='$id' AND mid='".$cfg_ml->M_ID."'");

        $dsql->ExecuteNoneQuery("UPDATE #@__member_tj SET ".$_field."='".$row['nums']."' WHERE mid='".$cfg_ml->M_ID."'");
    } else {
        return FALSE;
    }
}
