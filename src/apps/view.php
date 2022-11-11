<?php
/**
 * 关于文档权限设置的说明
 * 文档权限设置限制形式：如果指定了会员等级，那么必须到达这个等级才能浏览，如果指定了金币，浏览时会扣指点的点数，并保存记录到用户业务记录中，如果两者同时指定，那么必须同时满足两个条件
 *
 * @version        $id:view.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
require_once(DEDEINC.'/archive/archives.class.php');
$t1 = ExecTime();
if (empty($okview)) $okview = '';
if (isset($arcID)) $aid = $arcID;
if (!isset($dopost)) $dopost = '';
$arcID = $aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
if ($aid == 0) die("dedebiz");
$arc = new Archives($aid);
if ($arc->IsError) ParamError();
//检查阅读权限
$needMoney = $arc->Fields['money'];
$needRank = $arc->Fields['arcrank'];
require_once(DEDEINC.'/memberlogin.class.php');
$cfg_ml = new MemberLogin();
if ($needRank < 0 && $arc->Fields['mid'] != $cfg_ml->M_ID) {
    ShowMsg('文档尚未审核，非作者本人无权查看', 'javascript:;');
    exit();
}
//设置了权限限制的文档
//arctitle msgtitle moremsg
if ($needMoney > 0 || $needRank > 1) {
    $arctitle = $arc->Fields['title'];
    $arclink = $cfg_phpurl.'/view.php?aid='.$arc->ArcID;
    $arcLinktitle = "<a href=\"{$arclink}\">".$arctitle."</a>";
    $description =  $arc->Fields["description"];
    $pubdate = GetDateTimeMk($arc->Fields["pubdate"]);
    //会员级别不足
    if (($needRank > 1 && $cfg_ml->M_Rank < $needRank && $arc->Fields['mid'] != $cfg_ml->M_ID)) {
        $dsql->Execute('me', "SELECT * FROM `#@__arcrank` ");
        while ($row = $dsql->GetObject('me')) {
            $memberTypes[$row->rank] = $row->membername;
        }
        $memberTypes[0] = "游客或没权限会员";
        $msgtitle = "您没有权限浏览文档：{$arctitle} ";
        $moremsg = "这篇文档需要<span class='text-primary'>".$memberTypes[$needRank]."</span> 才能访问，您目前是：<span class='text-primary'>".$memberTypes[$cfg_ml->M_Rank]."</span>";
        include_once(DEDETEMPLATE.'/plus/view_msg.htm');
        exit();
    }
    //需要金币的情况
    if ($needMoney > 0  && $arc->Fields['mid'] != $cfg_ml->M_ID) {
        $sql = "SELECT aid,money FROM `#@__member_operation` WHERE buyid='ARCHIVE".$aid."' AND mid='".$cfg_ml->M_ID."'";
        $row = $dsql->GetOne($sql);
        //未购买过此文档
        if (!is_array($row)) {
            if ($cfg_ml->M_Money == '' || $needMoney > $cfg_ml->M_Money) {
                $msgtitle = "您没有权限浏览文档：{$arctitle} ";
                $moremsg = "这篇文档需要<span class='text-primary'>".$needMoney." 金币</span> 才能访问，您目前拥有金币：<span class='text-primary'>".$cfg_ml->M_Money." 个</span>";
                include_once(DEDETEMPLATE.'/plus/view_msg.htm');
                $arc->Close();
                exit();
            } else {
                if ($dopost == 'buy') {
                    $inquery = "INSERT INTO `#@__member_operation` (mid,oldinfo,money,mtime,buyid,product,pname) VALUES ('".$cfg_ml->M_ID."','$arctitle','$needMoney','".time()."','ARCHIVE".$aid."','archive',''); ";
                    if ($dsql->ExecuteNoneQuery($inquery)) {
                        $inquery = "UPDATE `#@__member` SET money=money-$needMoney WHERE mid='".$cfg_ml->M_ID."'";
                        if (!$dsql->ExecuteNoneQuery($inquery)) {
                            showmsg('购买失败, 请返回', -1);
                            exit;
                        }
                        showmsg('购买成功，购买扣点不会重扣金币，谢谢', '/plus/view.php?aid='.$aid);
                        exit;
                    } else {
                        showmsg('购买失败，请返回', -1);
                        exit;
                    }
                }
                $msgtitle = "扣金币购买阅读";
                $moremsg = "阅读该文档需要付费<br>这篇文档需要<span class='text-primary'>".$needMoney." 金币</span> 才能访问，您目前拥有金币<span class='text-primary'>".$cfg_ml->M_Money." </span>个<br>确认阅读请点 [<a href='/plus/view.php?aid=".$aid."&dopost=buy' target='_blank'>确认付点阅读</a>]";
                include_once($cfg_basedir.$cfg_templets_dir."/plus/view_msg.htm");
                $arc->Close();
                exit();
            }
        }
    } //金币处理付处理
}
$arc->Display();
if (DEBUG_LEVEL === TRUE) {
    $queryTime = ExecTime() - $t1;
    echo "<div style='width:98%;margin:1rem auto;color:#721c24;background-color:#f8d7da;border-color:#f5c6cb;position:relative;padding:.75rem 1.25rem;border:1px solid transparent;border-radius:.25rem'>页面加载总消耗时间：{$queryTime}</div>\r\n";
}
?>