<?php

/**
 * 会员短消息
 * 
 * @version        $Id: pm.php 1 8:38 2010年7月9日Z tianya $
 * @package        DedeBIZ.Member
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0, 0);
$menutype = 'mydede';
$menutype_son = 'pm';
$id = isset($id) ? intval($id) : 0;
if ($cfg_mb_lit == 'Y') {
    ShowMsg('由于系统开启了精简版会员空间，您不能向其它会员发短信息，不过您可以向他留言！', '-1');
    exit();
}

if (!isset($dopost)) {
    $dopost = '';
}
//检查用户是否被禁言
CheckNotAllow();
$state = empty($state) ? 0 : intval($state);

if ($dopost == 'read') {
    $sql = "SELECT * FROM `#@__member_friends` WHERE  mid='{$cfg_ml->M_ID}' AND ftype!='-1' ORDER BY addtime DESC LIMIT 20";
    $friends = array();
    $dsql->SetQuery($sql);
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $friends[] = $row;
    }
    $id = intval($id);
    $row = $dsql->GetOne("SELECT * FROM `#@__member_pms` WHERE id='$id' AND (fromid='{$cfg_ml->M_ID}' OR toid='{$cfg_ml->M_ID}')");
    if (!is_array($row)) {
        $result = array(
            "code" => -1,
            "data" => null,
            "msg" => "对不起，您指定的消息不存在或您没权限查看",
        );
        echo json_encode($result);
        exit();
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__member_pms` SET hasview=1 WHERE id='$id' AND folder='inbox' AND toid='{$cfg_ml->M_ID}'");
    $dsql->ExecuteNoneQuery("UPDATE `#@__member_pms` SET hasview=1 WHERE folder='outbox' AND toid='{$cfg_ml->M_ID}'");
    $result = array(
        "code" => 200,
        "data" => array(
            "subject" => $row['subject'],
            "message" => $row['message'],
            "sendtime" => MyDate("Y-m-d H:i", $row['sendtime']),
        ),
        "msg" => "",
    );
    echo json_encode($result);
    exit();
}
/*-----------------------
function __man(){  }
----------------------*/ 
else {
    if (!isset($folder)) {
        $folder = 'inbox';
    }
    require_once(DEDEINC."/datalistcp.class.php");
    $wsql = '';
    if ($folder == 'outbox') {
        $wsql = " `fromid`='{$cfg_ml->M_ID}' AND folder LIKE 'outbox' ";
        $tname = "发件箱";
    } elseif ($folder == 'inbox') {
        $query = "SELECT * FROM `#@__member_pms` WHERE folder LIKE 'outbox' AND isadmin='1'";
        $dsql->SetQuery($query);
        $dsql->Execute();
        while ($row = $dsql->GetArray()) {
            $row2 = $dsql->GetOne("SELECT * FROM `#@__member_pms` WHERE fromid = '$row[id]' AND toid='{$cfg_ml->M_ID}'");
            if (!is_array($row2)) {
                $row3 = "INSERT INTO
                `#@__member_pms` (`floginid`,`fromid`,`toid`,`tologinid`,`folder`,`subject`,`sendtime`,`writetime`,`hasview`,`isadmin`,`message`)
                VALUES ('admin','{$row['id']}','{$cfg_ml->M_ID}','{$cfg_ml->M_LoginID}','inbox','{$row['subject']}','{$row['sendtime']}','{$row['writetime']}','{$row['hasview']}','{$row['isadmin']}','{$row['message']}')";
                $dsql->ExecuteNoneQuery($row3);
            }
        }
        if ($state === 1) {
            $wsql = " toid='{$cfg_ml->M_ID}' AND folder='inbox' AND writetime!='' and hasview=1";
            $tname = "收件箱";
        } else if ($state === -1) {
            $wsql = "toid='{$cfg_ml->M_ID}' AND folder='inbox' AND writetime!='' and hasview=0";
            $tname = "收件箱";
        } else {
            $wsql = " toid='{$cfg_ml->M_ID}' AND folder='inbox' AND writetime!=''";
            $tname = "收件箱";
        }
    } else {
        $wsql = " `fromid` ='{$cfg_ml->M_ID}' AND folder LIKE 'outbox'";
        $tname = "已发信息";
    }

    $query = "SELECT * FROM `#@__member_pms` WHERE $wsql ORDER BY sendtime DESC";
    $dlist = new DataListCP();
    $dlist->pageSize = 20;
    $dlist->SetParameter("dopost", $dopost);
    $dlist->SetTemplate(DEDEMEMBER.'/templets/pm-main.htm');
    $dlist->SetSource($query);
    $dlist->Display();
}
