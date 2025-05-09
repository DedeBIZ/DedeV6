<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 投票
 *
 * @version        $id:dedevote.class.php 10:31 2010年7月6日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC."/dedetag.class.php");
class DedeVote
{
    var $VoteInfos;
    var $VoteNotes;
    var $VoteCount;
    var $VoteID;
    var $dsql;
    //php5构造函数
    function __construct($aid)
    {
        $this->dsql = $GLOBALS['dsql'];
        $this->VoteInfos = $this->dsql->GetOne("SELECT * FROM `#@__vote` WHERE aid='$aid'");
        $this->VoteNotes = array();
        $this->VoteCount = 0;
        $this->VoteID = $aid;
        if (!is_array($this->VoteInfos)) {
            return;
        }
        $dtp = new DedeTagParse();
        $dtp->SetNameSpace("v", "<", ">");
        $dtp->LoadSource($this->VoteInfos['votenote']);
        if (is_array($dtp->CTags)) {
            foreach ($dtp->CTags as $ctag) {
                $this->VoteNotes[$ctag->GetAtt('id')]['count'] = $ctag->GetAtt('count');
                $this->VoteNotes[$ctag->GetAtt('id')]['name'] = trim($ctag->GetInnerText());
                $this->VoteCount++;
            }
        }
        $dtp->Clear();
    }
    //兼容php4的构造函数
    function DedeVote($aid)
    {
        $this->__construct($aid);
    }
    function Close()
    {
    }
    /**
     *  获得投票项目总投票次数
     *
     * @access    public
     * @return    int
     */
    function GetTotalCount()
    {
        if (!empty($this->VoteInfos["totalcount"])) {
            return $this->VoteInfos["totalcount"];
        } else {
            return 0;
        }
    }
    /**
     *  添加指定的投票节点的票数
     *
     * @access    public
     * @param     int    $aid  投票ID
     * @return    void
     */
    function AddVoteCount($aid)
    {
        if (isset($this->VoteNotes[$aid])) {
            $this->VoteNotes[$aid]['count']++;
        }
    }
    /**
     *  获得项目的投票表单
     *
     * @access    public
     * @param     string   $tablewidth  表格宽度
     * @param     string   $titlebgcolor  标题颜色
     * @param     string   $titlebackgroup  标题背景
     * @param     string   $tablebg  表格背景
     * @param     string   $itembgcolor  项目背景
     * @return    string
     */
    function GetVoteForm($tablewidth = "100%", $titlebgcolor = "#edede2", $titlebackgroup = "", $tablebg = "#ffffff", $itembgcolor = "#ffffff")
    {
        //省略参数
        if ($tablewidth == "") {
            $tablewidth = "100%";
        }
        if ($titlebgcolor == "") {
            $titlebgcolor = "#98C6EF";
        }
        if ($titlebackgroup != "") {
            $titlebackgroup = "background='$titlebackgroup'";
        }
        if ($tablebg == "") {
            $tablebg = "#ffffff";
        }
        if ($itembgcolor == "") {
            $itembgcolor = "#ffffff";
        }
        $items = "<table width='$tablewidth' id='voteitem' class='table'>\r\n";
        $items .= "<form name='voteform' method='post' action='".$GLOBALS['cfg_phpurl']."/vote.php' target='_blank'>\r\n";
        $items .= "<input type='hidden' name='dopost' value='send' />\r\n";
        $items .= "<input type='hidden' name='aid' value='".$this->VoteID."' />\r\n";
        $items .= "<input type='hidden' name='ismore' value='".$this->VoteInfos['ismore']."' />\r\n";
        $items .= "<tr align='center'><td id='votetitle' $titlebackgroup>".$this->VoteInfos['votename']."</td></tr>\r\n";
        if ($this->VoteCount > 0) {
            foreach ($this->VoteNotes as $k => $arr) {
                if ($this->VoteInfos['ismore'] == 0) {
                    $items .= "<tr><td bgcolor='$itembgcolor'><label class='mb-0'><input type='radio' name='voteitem' value='$k'> ".$arr['name']."</label></td></tr>\r\n";
                } else {
                    $items .= "<tr><td bgcolor='$itembgcolor'><label class='mb-0'><input type=checkbox name='voteitem[]' value='$k'> ".$arr['name']."</label></td></tr>\r\n";
                }
            }
            $items .= "<tr><td>\r\n";
            $items .= "<input type='submit' name='vbt1' class='btn btn-success' value='投票'>\r\n";
            $items .= "<input type='button' name='vbt2' class='btn btn-success' value='查看结果' onclick=window.open('".$GLOBALS['cfg_phpurl']."/vote.php?dopost=view&aid=".$this->VoteID."');>";
            $items .= "</td></tr>\r\n";
        }
        $items .= "</form>\r\n</table>\r\n";
        return $items;
    }
    /**
     * 保存投票数据
     * 请不要在输出任何文档之前使用SaveVote()方法!
     *
     * @access    public
     * @param     string   $voteitem  投票项目
     * @return    string
     */
    function SaveVote($voteitem)
    {
        global $ENV_GOBACK_URL, $memberID, $row;
        if (empty($voteitem)) {
            return '您没选中任何项目';
        }
        $items = '';
        //检查投票是否已过期
        $nowtime = time();
        if ($nowtime > $this->VoteInfos['endtime']) {
            ShowMsg('投票已经过期', $ENV_GOBACK_URL);
            exit();
        }
        if ($nowtime < $this->VoteInfos['starttime']) {
            ShowMsg('投票还没有开始', $ENV_GOBACK_URL);
            exit();
        }
        //检测游客是否已投过票
        if (isset($_COOKIE['VOTE_MEMBER_IP'])) {
            if ($_COOKIE['VOTE_MEMBER_IP'] == $_SERVER['REMOTE_ADDR']) {
                ShowMsg('您已投过票', $ENV_GOBACK_URL);
                exit();
            } else {
                DedeSetCookie('VOTE_MEMBER_IP', $_SERVER['REMOTE_ADDR'], time() * $row['spec'] * 3600, '/');
            }
        } else {
            DedeSetCookie('VOTE_MEMBER_IP', $_SERVER['REMOTE_ADDR'], time() * $row['spec'] * 3600, '/');
        }
        //检查会员是否已投过票
        $nowtime = time();
        $VoteMem = $this->dsql->GetOne("SELECT * FROM `#@__vote_member` WHERE voteid = '$this->VoteID' and userid='$memberID'");
        if (!empty($memberID)) {
            if (isset($VoteMem['id'])) {
                $voteday = date("Y-m-d", $VoteMem['uptime']);
                $day = strtotime("-".$row['spec']." day");
                $day = date("Y-m-d", $day);
                if ($day < $voteday) {
                    ShowMsg('在'.$row['spec'].'天内不能重复投票', $ENV_GOBACK_URL);
                    exit();
                } else {
                    $query = "UPDATE `#@__vote_member` SET uptime='$nowtime' WHERE voteid='$this->VoteID' AND userid='$memberID'";
                    if ($this->dsql->ExecuteNoneQuery($query) == false) {
                        ShowMsg('插入数据过程中出现错误', $ENV_GOBACK_URL);
                        exit();
                    }
                }
            } else {
                $query = "INSERT INTO `#@__vote_member` (id,voteid,userid,uptime) VALUES ('','$this->VoteID','$memberID','$nowtime')";
                if ($this->dsql->ExecuteNoneQuery($query) == false) {
                    ShowMsg('插入数据过程中出现错误', $ENV_GOBACK_URL);
                    exit();
                }
            }
        }
        //必须存在投票项目
        if ($this->VoteCount > 0) {
            foreach ($this->VoteNotes as $k => $v) {
                if ($this->VoteInfos['ismore'] == 0) {
                    //单选项
                    if ($voteitem == $k) {
                        $this->VoteNotes[$k]['count']++;
                        break;
                    }
                } else {
                    //多选项
                    if (is_array($voteitem) && in_array($k, $voteitem)) {
                        $this->VoteNotes[$k]['count']++;
                    }
                }
            }
            foreach ($this->VoteNotes as $k => $arr) {
                $items .= "<v:note id='$k' count='".$arr['count']."'>".$arr['name']."</v:note>\r\n";
            }
        }
        $this->dsql->ExecuteNoneQuery("UPDATE `#@__vote` SET totalcount='".($this->VoteInfos['totalcount'] + 1)."',votenote='".addslashes($items)."' WHERE aid='".$this->VoteID."'");
        return "投票成功";
    }
    /**
     *  获得项目的投票结果
     *
     * @access    public
     * @param     string   $tablewidth  表格宽度
     * @param     string   $tablesplit  表格分隔
     * @return    string
     */
    function GetVoteResult($tablewidth = "600", $tablesplit = "40%")
    {
        $totalcount = $this->VoteInfos['totalcount'];
        if ($totalcount == 0) {
            $totalcount = 1;
        }
        $res = "<table width='$tablewidth' class='table'>\r\n";
        $i = 1;
        foreach ($this->VoteNotes as $k => $arr) {
            $res .= "<tr><td width='260'>".$i."、".$arr['name']."</td>";
            $c = $arr['count'];
            $res .= "<td><div class='progress'><div class='progress-bar' role='progressbar' style='width: ".(($c / $totalcount) * 100)."%' aria-valuenow='".(($c / $totalcount) * 100)."' aria-valuemin='0' aria-valuemax='100'></div></div></td></tr>\r\n";
            $i++;
        }
        $res .= "<tr><td></td><td></td></tr>\r\n";
        $res .= "</table>\r\n";
        return $res;
    }
}//End Class
?>