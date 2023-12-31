<?php
/**
 * DedeBIZ后台账号密码修改工具，改完即删，别留着过年，老铁们～
 * 
 * @version        $id:resetpwd.php tianya $
 * @package        DedeBIZ.Tools
 * @copyright      Copyright (c) 2023 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
define('DEDEBIZ_REPWD_VER', '1.0.0');
/**
 * ToolAlert
 *
 * @param  mixed $content
 * @param  mixed $colors
 * @return string
 */
function ToolAlert($content, $colors = array('#cfe2ff', '#b6d4fe', '#084298'))
{
    define('TOOLS_ALERT_TPL', '<div style="position:relative;padding:0.75rem 1.25rem;margin-bottom:1rem;width:auto;font-size:14px;color:~color~;background:~background~;border-color:~border~;border:1px solid transparent;border-radius:0.5rem">~content~</div>');
    list($background, $border, $color) = $colors;
    return str_replace(array('~color~', '~background~', '~border~', '~content~'), array($color, $background, $border, $content), TOOLS_ALERT_TPL);
}
if (!file_exists(dirname(__FILE__) . '/system/common.inc.php')) {
    echo ToolAlert("请把当前文件放置到DedeBIZ根目录下，通过`http://网站域名/dedebiz_repwd.php`进行操作");
    exit;
}
require_once dirname(__FILE__) . '/system/common.inc.php';
require_once(DEDEINC.'/libraries/oxwindow.class.php');
$dopost = isset($dopost)? $dopost : '';
$adminname = isset($adminname)? HtmlReplace($adminname, -1) : '';
$newpwd = isset($newpwd)? $newpwd : '';
$renewpwd = isset($renewpwd)? $renewpwd : '';
$dbpwd = isset($dbpwd)? $dbpwd : '';
if ($dopost === 'change') {
    if (empty($adminname)) {
        ShowMsg("管理员账号不能为空", -1);
        exit;
    }
    if (empty($newpwd) || $newpwd !== $renewpwd) {
        ShowMsg("新密码不能为空，且两次输入必须保持一致", -1);
        exit;
    }
    if (empty($dbpwd) || $dbpwd !== $cfg_dbpwd) {
        ShowMsg("数据库连接密码不能为空，切必须正确", -1);
        exit;
    }
    $admin = $dsql->GetOne("SELECT * FROM `#@__admin` WHERE `userid` = '$adminname'");
    if (empty($admin)) {
        ShowMsg("不存在当前输入的管理员账号", -1);
        exit;
    }
    if (function_exists('password_hash')) {
		$pwdm = "pwd='',pwd_new='".password_hash($newpwd, PASSWORD_BCRYPT)."'";
		$pwd = "pwd='',pwd_new='".password_hash($newpwd, PASSWORD_BCRYPT)."'";
	} else {
		$pwdm = "pwd='".md5($newpwd)."'";
		$pwd = "pwd='".substr(md5($newpwd), 5, 20)."'";
	}
    $id = $admin['id'];
	$query = "UPDATE `#@__admin` SET $pwd WHERE id='$id'";
    $dsql->ExecuteNoneQuery($query);
    $query = "UPDATE `#@__member` SET $pwdm WHERE mid='$id'";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("管理员密码成功修改为<code>{$newpwd}</code>，请务必删除当前文件", 'javascript:;');
    exit;
}
$wintitle = "DedeBIZ后台账号密码修改工具";
$wecome_info = "DedeBIZ后台账号密码修改工具 V" . DEDEBIZ_REPWD_VER;
$win = new OxWindow();
$win->Init(basename(__FILE__), 'js/blank.js', 'POST');
$win->AddHidden('dopost', 'change');
$win->AddHidden('token', $_SESSION['token']);
$win->AddTitle("<div class='alert alert-info mb-0'>需要技术服务或商业工具，请<a href='https://www.dedebiz.com/service' target='_blank'>联系官方</a></div>");
$win->AddMsgItem('
<tr>
    <td width="260">管理员账号：</td>
    <td><input type="text" name="adminname" id="adminname" class="admin-input-lg" placeholder="输入需要修改密码的管理员账号"></td>
</tr>
<tr>
    <td width="260">密码：</td>
    <td><input type="password" name="newpwd" id="newpwd" class="admin-input-lg" placeholder="新的密码"></td>
</tr>
<tr>
    <td width="260">再次输入密码：</td>
    <td><input type="password" name="renewpwd" id="renewpwd" class="admin-input-lg" placeholder="重复上面的密码"></td>
</tr>
<tr>
    <td width="260">数据库密码：</td>
    <td><input type="password" name="dbpwd" id="dbpwd" class="admin-input-lg" placeholder="输入数据库连接密码"> 查看`data/common.inc.php`中的`cfg_dbpwd`</td>
</tr>
');
$winform = $win->GetWindow('ok');
$win->Display();