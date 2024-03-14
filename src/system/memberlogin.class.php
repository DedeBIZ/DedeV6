<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 会员登录
 *
 * @version        $id:userlogin.class.php 15:59 2010年7月5日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
helper('cache');
/**
 *  检查账号的合法性
 *
 * @access    public
 * @param     string  $uid  会员UID
 * @param     string  $msgtitle  提示标题
 * @param     string  $ckhas  检查是否存在
 * @return    string
 */
function CheckUserID($uid, $msgtitle = '账号', $ckhas = TRUE)
{
    global $cfg_mb_notallow, $cfg_mb_idmin, $cfg_md_idurl, $cfg_soft_lang, $dsql;
    if ($cfg_mb_notallow != '') {
        $nas = explode(',', $cfg_mb_notallow);
        if (in_array($uid, $nas)) {
            return $msgtitle.'系统禁止的标识';
        }
    }
    if ($cfg_md_idurl == 'Y' && preg_match("/[^a-z0-9]/i", $uid)) {
        return $msgtitle.'必须由英文字母和数字组合';
    }
    if ($cfg_soft_lang == 'utf-8') {
        $ck_uid = utf82gb($uid);
    } else {
        $ck_uid = $uid;
    }
    for ($i = 0; isset($ck_uid[$i]); $i++) {
        if (ord($ck_uid[$i]) > 0x80) {
            if (isset($ck_uid[$i + 1]) && ord($ck_uid[$i + 1]) > 0x40) {
                $i++;
            } else {
                return $msgtitle.'建议用英文字母和数字组合';
            }
        } else {
            if (preg_match("/[^0-9a-z@\.-]/i", $ck_uid[$i])) {
                return $msgtitle.'请使用数字0-9小写a-z大写A-Z符号_@!.-';
            }
        }
    }
    if ($ckhas) {
        $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE userid LIKE '$uid' ");
        if (is_array($row)) return $msgtitle."已存在";
    }
    return 'ok';
}
/**
 *  检查会员是否被禁言
 *
 * @return    void
 */
function CheckNotAllow()
{
    global $cfg_ml;
    if (empty($cfg_ml->M_ID)) return;
    if ($cfg_ml->M_Spacesta == -2) {
        ShowMsg("您已经被禁言，请与管理员联系", "-1");
        exit();
    } else if ($cfg_ml->M_Spacesta == -10) {
        ShowMsg("系统开启了邮件审核机制，帐号需要审核后才能发信息", "-1");
        exit();
    } else if ($cfg_ml->M_Spacesta < 0) {
        ShowMsg('系统开启了审核机制，帐号需要管理员审核后才能发信息', '-1');
        exit();
    }
}
function FormatUsername($username)
{
    $username = str_replace("`", "‘", $username);
    $username = str_replace("'", "‘", $username);
    $username = str_replace("\"", "“", $username);
    $username = str_replace(",", "，", $username);
    $username = str_replace("(", "（", $username);
    $username = str_replace(")", "）", $username);
    return addslashes($username);
}
/**
 * 网站会员登录类
 *
 * @package          MemberLogin
 * @subpackage       DedeBIZ.Libraries
 * @link             https://www.dedebiz.com
 */
class MemberLogin
{
    var $M_ID;
    var $M_LoginID;
    var $M_MbType;
    var $M_Money;
    var $M_UserMoney;
    var $M_Scores;
    var $M_UserName;
    var $M_Rank;
    var $M_Face;
    var $M_LoginTime;
    var $M_KeepTime;
    var $M_Spacesta;
    var $fields;
    var $M_UpTime;
    var $M_ExpTime;
    var $M_HasDay;
    var $M_JoinTime;
    var $M_Honor = '';
    var $M_SendMax = 1;
    var $memberCache = 'memberlogin';
    var $dsql;
    //php5构造函数
    function __construct($kptime = -1, $cache = FALSE)
    {
        global $dsql;
        $this->dsql = $dsql;
        if ($kptime == -1) {
            $this->M_KeepTime = 3600 * 24 * 7;
        } else {
            $this->M_KeepTime = $kptime;
        }
        $formcache = FALSE;
        $this->M_ID = $this->GetNum(GetCookie("DedeUserID"));
        $this->M_LoginTime = GetCookie("DedeLoginTime");
        $this->fields = array();
        if (empty($this->M_ID)) {
            $this->ResetUser();
        } else {
            $this->M_ID = intval($this->M_ID);
            if ($cache) {
                $this->fields = GetCache($this->memberCache, $this->M_ID);
                if (empty($this->fields)) {
                    $this->fields = $this->dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='{$this->M_ID}' ");
                } else {
                    $formcache = TRUE;
                }
            } else {
                $this->fields = $this->dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='{$this->M_ID}' ");
            }
            if (is_array($this->fields)) {
                //间隔一小时更新一次会员登录时间
                if (time() - $this->M_LoginTime > 3600) {
                    $this->dsql->ExecuteNoneQuery("update `#@__member` set logintime='".time()."',loginip='".GetIP()."' WHERE mid='".$this->fields['mid']."';");
                    PutCookie("DedeLoginTime", time(), $this->M_KeepTime);
                }
                $this->M_LoginID = $this->fields['userid'];
                $this->M_MbType = $this->fields['mtype'];
                $this->M_Money = $this->fields['money'];
                $this->M_UserMoney = $this->fields['user_money'];
                $this->M_UserName = FormatUsername($this->fields['uname']);
                $this->M_Scores = $this->fields['scores'];
                $this->M_Face = $this->fields['face'];
                $this->M_Rank = $this->fields['rank'];
                $this->M_Spacesta = $this->fields['spacesta'];
                $sql = "SELECT titles From `#@__scores` WHERE integral<={$this->fields['scores']} ORDER BY integral DESC";
                $scrow = $this->dsql->GetOne($sql);
                $this->fields['honor'] = $scrow['titles'];
                $this->M_Honor = $this->fields['honor'];
                $this->M_UpTime = $this->fields['uptime'];
                $this->M_ExpTime = $this->fields['exptime'];
                $this->M_SendMax = $this->fields['send_max'];
                $this->M_JoinTime = MyDate('Y-m-d', $this->fields['jointime']);
                if ($this->M_Rank > 10 && $this->M_UpTime > 0) {
                    $this->M_HasDay = $this->Judgemember();
                }
                if (!$formcache) {
                    SetCache($this->memberCache, $this->M_ID, $this->fields, 1800);
                }
            } else {
                $this->ResetUser();
            }
        }
    }
    function MemberLogin($kptime = -1)
    {
        $this->__construct($kptime);
    }
    /**
     *  删除缓存，每次登录时和在修改会员资料的地方会清除
     *
     * @access    public
     * @param     string
     * @return    string
     */
    function DelCache($mid)
    {
        DelCache($this->memberCache, $mid);
    }
    /**
     *  判断会员是否到期
     *
     * @return    string
     */
    function Judgemember()
    {
        global $cfg_mb_rank;
        $nowtime = time();
        $mhasDay = $this->M_ExpTime - ceil(($nowtime - $this->M_UpTime) / 3600 / 24) + 1;
        if ($mhasDay <= 0) {
            $this->dsql->ExecuteNoneQuery("UPDATE `#@__member` SET uptime='0',exptime='0',`rank`='$cfg_mb_rank' WHERE mid='".$this->fields['mid']."';");
        }
        return $mhasDay;
    }
    /**
     *  退出cookie的会话
     *
     * @return    void
     */
    function ExitCookie()
    {
        $this->ResetUser();
    }
    /**
     *  验证会员是否已经登录
     *
     * @return    bool
     */
    function IsLogin()
    {
        if ($this->M_ID > 0) return TRUE;
        else return FALSE;
    }
    /**
     *  检测会员上传空间
     *
     * @return    int
     */
    function GetUserSpace()
    {
        $uid = $this->M_ID;
        $row = $this->dsql->GetOne("SELECT sum(filesize) AS fs FROM `#@__uploads` WHERE mid='$uid';");
        return intval($row['fs']);
    }
    /**
     *  检查会员空间是否已满
     *
     * @return    bool
     */
    function CheckUserSpaceIsFull()
    {
        global $cfg_mb_max;
        if ($cfg_mb_max == 0) {
            return false;
        }
        $hasuse = $this->GetUserSpace();
        $maxSize = $cfg_mb_max * 1024 * 1024;
        if ($hasuse >= $maxSize) {
            return true;
        }
        return false;
    }
    /**
     *  更新会员信息统计表
     *
     * @access    public
     * @param     string  $field  字段信息
     * @param     string  $uptype  更新类型
     * @return    string
     */
    function UpdateUserTj($field, $uptype = 'add')
    {
        $mid = $this->M_ID;
        $arr = $this->dsql->GetOne("SELECT * `#@__member_tj` WHERE mid='$mid' ");
        if (!is_array($arr)) {
            $arr = array('article' => 0, 'album' => 0, 'archives' => 0, 'homecount' => 0, 'pagecount' => 0, 'feedback' => 0, 'friend' => 0, 'stow' => 0);
        }
        extract($arr);
        if (isset($$field)) {
            if ($uptype == 'add') {
                $$field++;
            } else if ($$field > 0) {
                $$field--;
            }
        }
        $inquery = "INSERT INTO `#@__member_tj` (`mid`,`article`,`album`,`archives`,`homecount`,`pagecount`,`feedback`,`friend`,`stow`) VALUES ('$mid','$article','$album','$archives','$homecount','$pagecount','$feedback','$friend','$stow'); ";
        $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__member_tj` WHERE mid='$mid' ");
        $this->dsql->ExecuteNoneQuery($inquery);
    }
    /**
     *  重置会员信息
     *
     * @return    void
     */
    function ResetUser()
    {
        $this->fields = '';
        $this->M_ID = 0;
        $this->M_LoginID = '';
        $this->M_Rank = 0;
        $this->M_Face = '';
        $this->M_Money = 0;
        $this->M_UserMoney = 0;
        $this->M_UserName = '';
        $this->M_LoginTime = 0;
        $this->M_MbType = '';
        $this->M_Scores = 0;
        $this->M_Spacesta = -2;
        $this->M_UpTime = 0;
        $this->M_ExpTime = 0;
        $this->M_JoinTime = 0;
        $this->M_HasDay = 0;
        DropCookie('DedeUserID');
        DropCookie('DedeLoginTime');
    }
    /**
     *  获取整数值
     *
     * @access    public
     * @param     string  $fnum  处理的数值
     * @return    string
     */
    function GetNum($fnum)
    {
        $fnum = preg_replace("/[^0-9\.]/", '', $fnum);
        return $fnum;
    }
    /**
     *  会员登录，把登录密码转为指定长度md5数据
     *
     * @access    public
     * @param     string  $pwd  需要加密的密码
     * @return    string
     */
    function GetEncodePwd($pwd)
    {
        global $cfg_mb_pwdtype;
        if (empty($cfg_mb_pwdtype)) $cfg_mb_pwdtype = '32';
        switch ($cfg_mb_pwdtype) {
            case 'l16':
                return substr(md5($pwd), 0, 16);
            case 'r16':
                return substr(md5($pwd), 16, 16);
            case 'm16':
                return substr(md5($pwd), 8, 16);
            default:
                return md5($pwd);
        }
    }    
    /**
     *  会员登录，把数据库密码转为特定长度，如果数据库密码是明文，本程序不支持
     *
     * @access    public
     * @param     string
     * @return    string
     */
    function GetShortPwd($dbpwd)
    {
        global $cfg_mb_pwdtype;
        if (empty($cfg_mb_pwdtype)) $cfg_mb_pwdtype = '32';
        $dbpwd = trim($dbpwd);
        if (strlen($dbpwd) == 16) {
            return $dbpwd;
        } else {
            switch ($cfg_mb_pwdtype) {
                case 'l16':
                    return substr($dbpwd, 0, 16);
                case 'r16':
                    return substr($dbpwd, 16, 16);
                case 'm16':
                    return substr($dbpwd, 8, 16);
                default:
                    return $dbpwd;
            }
        }
    }
    /**
     * 投稿是否被限制
     *
     * @return array
     */
    function IsSendLimited()
    {
        $ttime = strtotime("today");
        $arr = $this->dsql->GetOne("SELECT COUNT(*) as dd FROM `#@__arctiny` WHERE mid='{$this->M_ID}' AND senddate >= $ttime");
        if (is_array($arr)) {
            if ($this->M_SendMax < 0) {
                return false;
            }
            if ($arr['dd'] >= $this->M_SendMax) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
    /**
     *  检查会员是否合法
     *
     * @access    public
     * @param     string  $loginuser  登录账号
     * @param     string  $loginpwd  密码
     * @return    string
     */
    function CheckUser(&$loginuser, $loginpwd)
    {
        //检测账号的合法性
        $rs = CheckUserID($loginuser, '账号', FALSE);
        //账号不正确时返回验证错误，原登录名通过引用返回错误提示信息
        if ($rs != 'ok') {
            $loginuser = $rs;
            return '0';
        }
        //matt=10是管理员关连的前台帐号，为了安全起见，这个帐号只能从后台登录，不能直接从前台登录
        $row = $this->dsql->GetOne("SELECT mid,matt,pwd,pwd_new,logintime FROM `#@__member` WHERE userid LIKE '$loginuser' ");
        if (is_array($row)) {
            if (!empty($row['pwd_new']) && !password_verify($loginpwd, $row['pwd_new'])) {
                $this->loginError($loginuser);
                return -1;
            } else if (!empty($row['pwd']) && $this->GetShortPwd($row['pwd']) != $this->GetEncodePwd($loginpwd)) {
                $this->loginError($loginuser);
                return -1;
            } else {
                if (empty($row['pwd_new']) && function_exists('password_hash')) {
                    //升级密码
                    $newpwd = password_hash($loginpwd, PASSWORD_BCRYPT);
                    $inquery = "UPDATE `#@__member` SET pwd='',pwd_new='{$newpwd}' WHERE mid='".$row['mid']."'";
                    $this->dsql->ExecuteNoneQuery($inquery);
                }
                //管理员帐号不允许从前台登录
                if ($row['matt'] == 10) {
                    return -2;
                } else {
                    $this->PutLoginInfo($row['mid'], $row['logintime']);
                    return 1;
                }
            }
        } else {
            return 0;
        }
    }
    /**
     * 是否需要验证码
     *
     * @param  mixed $loginuser
     * @return bool
     */
    function isNeedCheckCode($loginuser)
    {
        $num = $this->getLoginError($loginuser);
        return $num >= 3 ? true : false;
    }
    /**
     * 1分钟以内登录错误的次数
     *
     * @param  mixed $loginuser
     * @return int 登录错误次数
     */
    function getLoginError($loginuser)
    {
        $rs = CheckUserID($loginuser, '账号', FALSE);
        //账号不正确时返回验证错误，原登录名通过引用返回错误提示信息
        if ($rs != 'ok') {
            return -1;
        }
        if (!TableHasField("#@__member", "loginerr")) {
            return 0;
        }
        $row = $this->dsql->GetOne("SELECT loginerr,logintime FROM `#@__member` WHERE userid LIKE '$loginuser'");
        if (is_array($row)) {
            //1分钟内如果输错3次则需要验证码
            return (time() - (int)$row['logintime']) < 60 ?  (int)$row['loginerr'] : 0;
        } else {
            return -1;
        }
    }
    /**
     * 记录登录错误
     *
     * @return void
     */
    function loginError($loginuser)
    {
        $rs = CheckUserID($loginuser, '账号', FALSE);
        //账号不正确时返回验证错误，原登录名通过引用返回错误提示信息
        if ($rs != 'ok') {
            return;
        }
        $loginip = GetIP();
        $inquery = "UPDATE `#@__member` SET loginip='$loginip',logintime='".time()."',loginerr=loginerr+1 WHERE userid='".$loginuser."'";
        $this->dsql->ExecuteNoneQuery($inquery);
    }
    /**
     *  保存会员cookie
     *
     * @access    public
     * @param     string  $uid  会员id
     * @param     string  $logintime  登录限制时间
     * @return    void
     */
    function PutLoginInfo($uid, $logintime = 0)
    {
        global $cfg_login_adds;
        //登录添加积分，上一次登录时间必须大于两小时
        if (time() - $logintime > 7200 && $cfg_login_adds > 0) {
            $this->dsql->ExecuteNoneQuery("UPDATE `#@__member` SET `scores`=`scores`+{$cfg_login_adds} WHERE mid='$uid' ");
        }
        $this->M_ID = $uid;
        $this->M_LoginTime = time();
        $loginip = GetIP();
        $inquery = "UPDATE `#@__member` SET loginip='$loginip',logintime='".$this->M_LoginTime."',loginerr=0 WHERE mid='".$uid."'";
        $this->dsql->ExecuteNoneQuery($inquery);
        if ($this->M_KeepTime > 0) {
            PutCookie('DedeUserID', $uid, $this->M_KeepTime);
            PutCookie('DedeLoginTime', $this->M_LoginTime, $this->M_KeepTime);
        } else {
            PutCookie('DedeUserID', $uid);
            PutCookie('DedeLoginTime', $this->M_LoginTime);
        }
    }
    function GetMemberTypeName()
    {
        if ($this->M_Rank == 0) {
            return '注册会员';
        } else {
            $row = $this->dsql->GetOne("SELECT membername FROM `#@__arcrank` WHERE `rank`='".$this->M_Rank."'");
            return $row['membername'];
        }
    }
    /**
     *  获得会员目前的状态
     *
     * @access    public
     * @return    string
     */
    function GetSta()
    {
        $sta = '';
        if ($this->M_Rank == 0) {
            $sta .= "您目前等级是：注册会员";
        } else {
            $row = $this->dsql->GetOne("SELECT membername FROM `#@__arcrank` WHERE `rank`='".$this->M_Rank."'");
            $sta .= "您目前等级是：".$row['membername'];
            $rs = $this->dsql->GetOne("SELECT id FROM `#@__admin` WHERE userid='".$this->M_LoginID."'");
            if (!is_array($rs)) {
                if ($this->M_Rank > 10 && $this->M_HasDay > 0) $sta .= "，剩余".$this->M_HasDay."天";
                elseif ($this->M_Rank > 10) $sta .= "，<span class='text-danger'>会员已到期</span>";
            }
        }
        $sta .= "，积分{$this->M_Scores}分，金币{$this->M_Money}个，余额{$this->M_UserMoney}元";
        return $sta;
    }
    //获取能够发布文档的栏目
    public static function GetEnabledChannels() {
        global $dsql;
        $result = array();
        $dsql->SetQuery("SELECT channeltype FROM `#@__arctype` GROUP BY channeltype");
        $dsql->Execute();
        $candoChannel = '';
        while ($row = $dsql->GetObject()) {
            $result[] = $row->channeltype;
        }
        return $result;
    }
}//End Class
?>