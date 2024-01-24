<?php
/**
 * 添加管理员
 *
 * @version        $id:sys_admin_user_add.php 16:22 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_User');
require_once(DEDEINC."/typelink/typelink.class.php");
if (empty($dopost)) $dopost = '';
if ($dopost == 'add') {
    CheckCSRF();
    if (preg_match("#[^0-9a-zA-Z_@!\.-]#", $userid)) {
        ShowMsg('账号不合法，请使用数字0-9小写a-z大写A-Z符号_@!.-', '-1');
        exit();
    }
    if ($pwd != '' && preg_match("#[^0-9a-zA-Z_@!\.-]#", $pwd)) {
        ShowMsg('密码不合法，请使用数字0-9小写a-z大写A-Z符号_@!.-', '-1');
        exit();
    }
    $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    if ($safecode != $safecodeok) {
        ShowMsg('请填写验证安全码', '-1');
        exit();
    }
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__member` WHERE userid LIKE '$userid' ");
    if ($row['dd'] > 0) {
        ShowMsg('账号已存在', '-1');
        exit();
    }
    $pfd = "pwd";
    if (function_exists('password_hash')) {
        $pfd = "pwd_new";
        $mpwd = password_hash($pwd, PASSWORD_BCRYPT);
        $pwd = password_hash($pwd, PASSWORD_BCRYPT);
    } else {
        $mpwd = md5($pwd);
        $pwd = substr(md5($pwd), 5, 20);
    }
    $typeid = join(',', $typeids);
    if ($typeid == '0') $typeid = '';
    //关连前台会员帐号
    $adminquery = "INSERT INTO `#@__member` (`mtype`,`userid`,`$pfd`,`uname`,`sex`,`rank`,`money`,`email`,`scores`,`matt`,`face`,`safequestion`,`safeanswer`,`jointime`,`joinip`,`logintime`,`loginip` )
        VALUES ('个人','$userid','$mpwd','$uname','男','100','0','$email','1000','10','','0','','0','','0',''); ";
    $dsql->ExecuteNoneQuery($adminquery);
    $mid = $dsql->GetLastID();
    if ($mid <= 0) {
        die($dsql->GetError().' 数据库出错');
    }
    //后台管理员
    $inquery = "INSERT INTO `#@__admin`(id,usertype,userid,$pfd,uname,typeid,tname,email)
        VALUES ('$mid','$usertype','$userid','$pwd','$uname','$typeid','$tname','$email'); ";
    $rs = $dsql->ExecuteNoneQuery($inquery);
    $adminquery = "INSERT INTO `#@__member_person` (`mid`,`onlynet`,`sex`,`uname`,`qq`,`msn`,`tel`,`mobile`,`place`,`oldplace`,`birthday`,`star`,`income`,`education`,`height`,`bodytype`,`blood`,`vocation`,`smoke`,`marital`,`house`,`drink`,`datingtype`,`language`,`nature`,`lovemsg`,`address`,`uptime`)
        VALUES ('$mid', '1', '男', '{$userid}', '', '', '', '', '0', '0','1980-01-01', '1', '0', '0', '160', '0', '0', '0', '0', '0', '0','0', '0', '', '', '', '','0'); ";
    $dsql->ExecuteNoneQuery($adminquery);
    $adminquery = "INSERT INTO `#@__member_tj` (`mid`,`article`,`album`,`archives`,`homecount`,`pagecount`,`feedback`,`friend`,`stow`)
        VALUES ('$mid','0','0','0','0','0','0','0','0'); ";
    $dsql->ExecuteNoneQuery($adminquery);
    $adminquery = "Insert Into `#@__member_space` (`mid`,`pagesize`,`matt`,`spacename`,`spacelogo`,`spacestyle`,`sign`,`spacenews`)
        VALUES ('$mid','10','0','{$uname}的主页','','person','',''); ";
    $dsql->ExecuteNoneQuery($adminquery);
    ShowMsg('成功添加一个账户', 'sys_admin_user.php');
    exit();
}
$randcode = mt_rand(10000, 99999);
$safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
$typeOptions = '';
//递归获取分类
function getTypeOptions($id=0,$sep="└")
{
    global $dsql,$typeOptions;
    $dsql->SetQuery("SELECT id,typename,ispart FROM `#@__arctype` WHERE reid={$id} AND (ispart=0 OR ispart=1 OR ispart=2) ORDER BY sortrank");
    $dsql->Execute($id);
    while ($nrow = $dsql->GetObject($id)) {
        $isDisabled = $nrow->ispart==2? " disabled" : "";
        $typeOptions .= "<option value='{$nrow->id}'{$isDisabled}>{$sep} {$nrow->typename}</option>\r\n";
        getTypeOptions($nrow->id, $sep."─");
    }
}
getTypeOptions(0);
make_hash();
include DedeInclude('templets/sys_admin_user_add.htm');
?>