<?php
/**
 * 会员短消息
 * 
 * @version        $id:pm.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0, 0);//禁止游客操作
$menutype = 'mydede';
$menutype_son = 'pm';
$id = isset($id) ? intval($id) : 0;
if ($cfg_mb_lit == 'Y') {
    ShowMsg('由于系统开启了会员空间精简版，您不能向其它会员发短信息，不过您可以向他留言', '-1');
    exit();
}
if (!isset($dopost)) {
    $dopost = '';
}
//检查会员是否被禁言
CheckNotAllow();
$state = empty($state) ? 0 : intval($state);
if ($dopost == 'read') {
    $id = intval($id);
    $row = $dsql->GetOne("SELECT * FROM `#@__member_pms` WHERE id='$id' AND (fromid='{$cfg_ml->M_ID}' OR toid='{$cfg_ml->M_ID}')");
    if (!is_array($row)) {
        $result = array(
            "code" => -1,
            "data" => null,
            "msg" => "您指定的消息不存在或您没权限查看",
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
            "sendtime" => MyDate("Y-m-d H:i:s", $row['sendtime']),
        ),
        "msg" => "",
    );
    echo json_encode($result);
    exit();
} else if ($dopost == 'remove'){
    $ids = preg_replace("#[^0-9,]#", "", $ids);
    if ($folder==='inbox') {
        $boxsql="SELECT * FROM `#@__member_pms` WHERE id IN($ids) AND folder LIKE 'inbox' AND toid='{$cfg_ml->M_ID}'";
        $dsql->SetQuery($boxsql);
        $dsql->Execute();
        $query='';
        while($row = $dsql->GetArray())
        {
            if ($row && $row['isadmin']==1) {
                $query = "UPDATE `#@__member_pms` SET writetime='0' WHERE id='{$row['id']}' AND folder='inbox' AND toid='{$cfg_ml->M_ID}' AND isadmin='1';";
                $dsql->ExecuteNoneQuery($query);
            } else {
                $query = "DELETE FROM `#@__member_pms` WHERE id in($ids) AND toid='{$cfg_ml->M_ID}' AND folder LIKE 'inbox'";
            }
        }
    } else if ($folder==='outbox') {
        $query = "DELETE FROM `#@__member_pms` WHERE id in($ids) AND fromid='{$cfg_ml->M_ID}' AND folder LIKE 'outbox' ";
    } else {
        $query = "DELETE FROM `#@__member_pms` WHERE id in($ids) AND fromid='{$cfg_ml->M_ID}' Or toid='{$cfg_ml->M_ID}' AND folder LIKE 'outbox' Or (folder LIKE 'inbox' AND hasview='0')";
    }
    $dsql->ExecuteNoneQuery($query);
    $result = array(
        "code" => 200,
        "data" => "success",
        "msg" => "",
    );
    echo json_encode($result);
    exit;
} else {
    if (!isset($folder)) {
        $folder = 'inbox';
    }
    require_once(DEDEINC."/datalistcp.class.php");
    $wsql = '';
    if ($folder == 'outbox') {
        $wsql = " `fromid`='{$cfg_ml->M_ID}' AND folder LIKE 'outbox' ";
        $tname = "发件箱";
    } elseif ($folder == 'inbox') {
        if ($state === 1) {
            $wsql = " toid='{$cfg_ml->M_ID}' AND folder='inbox' AND writetime!=0 and hasview=1";
            $tname = "收件箱";
        } else if ($state === -1) {
            $wsql = "toid='{$cfg_ml->M_ID}' AND folder='inbox' AND writetime!=0 and hasview=0";
            $tname = "收件箱";
        } else {
            $wsql = " toid='{$cfg_ml->M_ID}' AND folder='inbox' AND writetime!=0";
            $tname = "收件箱";
        }
    } else {
        $wsql = " `fromid` ='{$cfg_ml->M_ID}' AND folder LIKE 'outbox'";
        $tname = "已发信息";
    }
    $query = "SELECT * FROM `#@__member_pms` WHERE $wsql ORDER BY sendtime DESC";
    $dlist = new DataListCP();
    $dlist->pagesize = 10;
    $dlist->SetParameter("dopost", $dopost);
    $dlist->SetTemplate(DEDEMEMBER.'/templets/pm-main.htm');
    $dlist->SetSource($query);
    $dlist->Display();
}
?>