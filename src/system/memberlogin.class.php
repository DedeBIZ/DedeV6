<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 会员登录类
 *
 * @version        $id:userlogin.class.php 15:59 2010年7月5日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
//使用缓存助手
helper('cache');
/**
 *  检查用户名的合法性
 *
 * @access    public
 * @param     string  $uid  用户UID
 * @param     string  $msgtitle  提示标题
 * @param     string  $ckhas  检查是否存在
 * @return    string
 */
function CheckUserID($uid, $msgtitle = '用户名', $ckhas = TRUE)
{
    global $cfg_mb_notallow, $cfg_mb_idmin, $cfg_md_idurl, $cfg_soft_lang, $dsql;
    if ($cfg_mb_notallow != '') {
        $nas = explode(',', $cfg_mb_notallow);
        if (in_array($uid, $nas)) {
            return $msgtitle.'为系统禁止的标识';
        }
    }
    if ($cfg_md_idurl == 'Y' && preg_match("/[^a-z0-9]/i", $uid)) {
        return $msgtitle.'必须由英文字母或数字组成';
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
                return $msgtitle.'不能含有[@]、[.]、[-]以外的特殊符号';
            }
        }
    }
    if ($ckhas) {
        $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE userid LIKE '$uid' ");
        if (is_array($row)) return $msgtitle."已经存在";
    }
    return 'ok';
}
/**
 *  检查用户是否被禁言
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
        ShowMsg("系统开启了邮件审核机制，因此您的帐号需要审核后才能发信息", "-1");
        exit();
    } else if ($cfg_ml->M_Spacesta < 0) {
        ShowMsg('系统开启了审核机制，因此您的帐号需要管理员审核后才能发信息', '-1');
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
    var $M_Scores;
    var $M_UserName;
    var $M_Rank;
    var $M_Face;
    var $M_LoginTime;
    var $M_KeepTime;
    var $M_Spacesta;
    var $fields;
    var $isAdmin;
    var $M_UpTime;
    var $M_ExpTime;
    var $M_HasDay;
    var $M_JoinTime;
    var $M_Honor = '';
    var $M_SendMax = 0;
    var $memberCache = 'memberlogin';
    //php5构造函数
    function __construct($kptime = -1, $cache = FALSE)
    {
        global $dsql;
        if ($kptime == -1) {
            $this->M_KeepTime = 3600 * 24 * 7;
        } else {
            $this->M_KeepTime = $kptime;
        }
        $formcache = FALSE;
        $this->M_ID = $this->GetNum(GetCookie("DedeUserID"));
        $this->M_LoginTime = GetCookie("DedeLoginTime");
        $this->fields = array();
        $this->isAdmin = FALSE;
        if (empty($this->M_ID)) {
            $this->ResetUser();
        } else {
            $this->M_ID = intval($this->M_ID);

            if ($cache) {
                $this->fields = GetCache($this->memberCache, $this->M_ID);
                if (empty($this->fields)) {
                    $this->fields = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='{$this->M_ID}' ");
                } else {
                    $formcache = TRUE;
                }
            } else {
                $this->fields = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='{$this->M_ID}' ");
            }
            if (is_array($this->fields)) {
                //间隔一小时更新一次用户登录时间
                if (time() - $this->M_LoginTime > 3600) {
                    $dsql->ExecuteNoneQuery("update `#@__member` set logintime='".time()."',loginip='".GetIP()."' WHERE mid='".$this->fields['mid']."';");
                    PutCookie("DedeLoginTime", time(), $this->M_KeepTime);
                }
                $this->M_LoginID = $this->fields['userid'];
                $this->M_MbType = $this->fields['mtype'];
                $this->M_Money = $this->fields['money'];
                $this->M_UserName = FormatUsername($this->fields['uname']);
                $this->M_Scores = $this->fields['scores'];
                $this->M_Face = $this->fields['face'];
                $this->M_Rank = $this->fields['rank'];
                $this->M_Spacesta = $this->fields['spacesta'];
                $sql = "SELECT titles From `#@__scores` WHERE integral<={$this->fields['scores']} ORDER BY integral DESC";
                $scrow = $dsql->GetOne($sql);
                $this->fields['honor'] = $scrow['titles'];
                $this->M_Honor = $this->fields['honor'];
                if ($this->fields['matt'] == 10) $this->isAdmin = TRUE;
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
     *  删除缓存,每次登录时和在修改用户资料的地方会清除
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
        global $dsql, $cfg_mb_rank;
        $nowtime = time();
        $mhasDay = $this->M_ExpTime - ceil(($nowtime - $this->M_UpTime) / 3600 / 24) + 1;
        if ($mhasDay <= 0) {
            $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET uptime='0',exptime='0',`rank`='$cfg_mb_rank' WHERE mid='".$this->fields['mid']."';");
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
     *  验证用户是否已经登录
     *
     * @return    bool
     */
    function IsLogin()
    {
        if ($this->M_ID > 0) return TRUE;
        else return FALSE;
    }
    /**
     *  检测用户上传空间
     *
     * @return    int
     */
    function GetUserSpace()
    {
        global $dsql;
        $uid = $this->M_ID;
        $row = $dsql->GetOne("SELECT sum(filesize) AS fs FROM `#@__uploads` WHERE mid='$uid'; ");
        return $row['fs'];
    }
    /**
     *  检查用户空间信息
     *
     * @return    void
     */
    function CheckUserSpace()
    {
        global $cfg_mb_max;
        $uid = $this->M_ID;
        $hasuse = $this->GetUserSpace();
        $maxSize = $cfg_mb_max * 1024 * 1024;
        if ($hasuse >= $maxSize) {
            ShowMsg('您的空间已满，不允许上传新文件', '-1');
            exit();
        }
    }
    /**
     *  更新用户信息统计表
     *
     * @access    public
     * @param     string  $field  字段信息
     * @param     string  $uptype  更新类型
     * @return    string
     */
    function UpdateUserTj($field, $uptype = 'add')
    {
        global $dsql;
        $mid = $this->M_ID;
        $arr = $dsql->GetOne("SELECT * `#@__member_tj` WHERE mid='$mid' ");
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
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_tj` WHERE mid='$mid' ");
        $dsql->ExecuteNoneQuery($inquery);
    }
    /**
     *  重置用户信息
     *
     * @return    void
     */
    function ResetUser()
    {
        $this->fields = '';
        $this->M_ID = 0;
        $this->M_LoginID = '';
        $this->M_Rank = 0;
        $this->M_Face = "";
        $this->M_Money = 0;
        $this->M_UserName = "";
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
     *  用户登录
     *  把登录密码转为指定长度md5数据
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
     * 投稿是否被限制
     *
     * @return bool
     */
    function IsSendLimited()
    {
        global $dsql;
        $arr = $dsql->GetOne("SELECT COUNT(*) as dd FROM `#@__arctiny` WHERE mid='{$this->M_ID}'");
        if ($this->isAdmin === true ) {
            return false;
        }
        if (is_array($arr)) {
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
     *  把数据库密码转为特定长度
     *  如果数据库密码是明文的，本程序不支持
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
     *  检查用户是否合法
     *
     * @access    public
     * @param     string  $loginuser  登录用户名
     * @param     string  $loginpwd  用户密码
     * @return    string
     */
    function CheckUser(&$loginuser, $loginpwd)
    {
        global $dsql;
        //检测用户名的合法性
        $rs = CheckUserID($loginuser, '用户名', FALSE);
        //用户名不正确时返回验证错误，原登录名通过引用返回错误提示信息
        if ($rs != 'ok') {
            $loginuser = $rs;
            return '0';
        }
        //matt=10 是管理员关连的前台帐号，为了安全起见，这个帐号只能从后台登录，不能直接从前台登录
        $row = $dsql->GetOne("SELECT mid,matt,pwd,pwd_new,logintime FROM `#@__member` WHERE userid LIKE '$loginuser' ");
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
                    $dsql->ExecuteNoneQuery($inquery);
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
        global $dsql;
        $rs = CheckUserID($loginuser, '用户名', FALSE);
        //用户名不正确时返回验证错误，原登录名通过引用返回错误提示信息
        if ($rs != 'ok') {
            return -1;
        }
        $row = $dsql->GetOne("SELECT loginerr,logintime FROM `#@__member` WHERE userid LIKE '$loginuser'");
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
        global $dsql;
        $rs = CheckUserID($loginuser, '用户名', FALSE);
        //用户名不正确时返回验证错误，原登录名通过引用返回错误提示信息
        if ($rs != 'ok') {
            return;
        }
        $loginip = GetIP();
        $inquery = "UPDATE `#@__member` SET loginip='$loginip',logintime='".time()."',loginerr=loginerr+1 WHERE userid='".$loginuser."'";
        $dsql->ExecuteNoneQuery($inquery);
    }
    /**
     *  保存用户cookie
     *
     * @access    public
     * @param     string  $uid  用户id
     * @param     string  $logintime  登录限制时间
     * @return    void
     */
    function PutLoginInfo($uid, $logintime = 0)
    {
        global $cfg_login_adds, $dsql;
        //登录增加积分(上一次登录时间必须大于两小时)
        if (time() - $logintime > 7200 && $cfg_login_adds > 0) {
            $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET `scores`=`scores`+{$cfg_login_adds} WHERE mid='$uid' ");
        }
        $this->M_ID = $uid;
        $this->M_LoginTime = time();
        $loginip = GetIP();
        $inquery = "UPDATE `#@__member` SET loginip='$loginip',logintime='".$this->M_LoginTime."',loginerr=0 WHERE mid='".$uid."'";
        $dsql->ExecuteNoneQuery($inquery);
        if ($this->M_KeepTime > 0) {
            PutCookie('DedeUserID', $uid, $this->M_KeepTime);
            PutCookie('DedeLoginTime', $this->M_LoginTime, $this->M_KeepTime);
        } else {
            PutCookie('DedeUserID', $uid);
            PutCookie('DedeLoginTime', $this->M_LoginTime);
        }
    }
    /**
     *  获得会员目前的状态
     *
     * @access    public
     * @param     object  $dsql  数据库连接
     * @return    string
     */
    function GetSta($dsql)
    {
        $sta = '';
        if ($this->M_Rank == 0) {
            $sta .= "您目前的身份是：普通会员";
        } else {
            $row = $dsql->GetOne("SELECT membername FROM `#@__arcrank` WHERE `rank`='".$this->M_Rank."'");
            $sta .= "您目前的身份是：".$row['membername'];
            $rs = $dsql->GetOne("SELECT id FROM `#@__admin` WHERE userid='".$this->M_LoginID."'");
            if (!is_array($rs)) {
                if ($this->M_Rank > 10 && $this->M_HasDay > 0) $sta .= " 剩余<span class='text-primary'>".$this->M_HasDay."</span>天";
                elseif ($this->M_Rank > 10) $sta .= "<span class='text-danger'>会员已到期</span>";
            }
        }
        $sta .= " 积分<span class='text-primary'>{$this->M_Scores}</span>分，金币<span class='text-primary'>{$this->M_Money}</span>个";
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