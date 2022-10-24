<?php
/**
 * 系统修复工具
 *
 * @version        $Id: sys_repair.php 1 22:28 2010年7月20日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DedeWin;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__).'/config.php');
UserLogin::CheckPurview('sys_ArcBatch');
if (empty($dopost)) {
    $wecome_info = "<a href='index_body.php'>".Lang('admin_home')."</a> &gt; ". Lang("sys_repair");
    $msg = Lang("sys_repair_msg");
    DedeWin::Instance()->Init("sys_repair.php", "js/blank.js", "POST' enctype='multipart/form-data' ")
    ->AddTitle(Lang('sys_repair_title'))->AddMsgItem("<div>$msg</div>")
    ->GetWindow('hand', '')->Display();
    exit();
}
//数据结构常规检测
else if ($dopost == 1) {
    $wecome_info = "<a href='sys_repair.php'>".Lang("sys_repair")."</a> &gt; ".Lang('sys_repair_test_db');
    $msg = Lang("sys_repair_test_db_msg");
    DedeWin::Instance()->Init("sys_repair.php", "js/blank.js", "POST' enctype='multipart/form-data' ")
    ->AddTitle(Lang('sys_repair_test_db_title'))->AddMsgItem("<div>$msg</div>")
    ->GetWindow('hand', '')->Display();
    exit();
}
//检测微表正确性并尝试修复
else if ($dopost == 2) {
    $msg = '';
    $allarcnum = 0;
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__archives`");
    $allarcnum = $arcnum = $row['dd'];
    $msg .= "#@__archives ".Lang('sys_repair_test_arctiny_tt')."：{$arcnum} <br>";
    $shtables = array();
    $dsql->Execute('me', "SELECT addtable FROM `#@__channeltype` WHERE id < -1");
    while ($row = $dsql->GetArray('me')) {
        $addtable = strtolower(trim(str_replace('#@__', $cfg_dbprefix, $row['addtable'])));
        if (empty($addtable)) {
            continue;
        } else {
            if (!isset($shtables[$addtable])) {
                $shtables[$addtable] = 1;
                $row = $dsql->GetOne("SELECT COUNT(aid) AS dd FROM `$addtable`");
                $msg .= "{$addtable} ".Lang('sys_repair_test_arctiny_tt')."：{$row['dd']} <br>";
                $allarcnum += $row['dd'];
            }
        }
    }
    $msg .= Lang('sys_repair_test_arctiny_tt2')."：{$allarcnum} <br> ";
    $errall = "<a href='index_body.php' class='btn btn-success btn-sm'>".Lang('sys_repair_test_arctiny_ok')."</a>";
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__arctiny`");
    $msg .= Lang('sys_repair_test_arctiny_tt3')."：{$row['dd']}<br>";
    if ($row['dd'] == $allarcnum) {
        $msg .= "<span class='text-dark'>".Lang('sys_repair_test_arctiny_same')."</span><br>";
    } else {
        $sql = "TRUNCATE TABLE `#@__arctiny`";
        $dsql->ExecuteNoneQuery($sql);
        $msg .= "<span class='text-danger'>".Lang('sys_repair_test_arctiny_diff')."</span><br>";
        //导入普通模型微数据
        $sql = "INSERT INTO `#@__arctiny`(id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid)  
            SELECT id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid FROM `#@__archives` ";
        $dsql->ExecuteNoneQuery($sql);
        //导入单表模型微数据
        foreach ($shtables as $tb => $v) {
            $sql = "INSERT INTO `#@__arctiny`(id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid) SELECT aid, typeid, 0, arcrank, channel, senddate, 0, mid FROM `$tb`";
            $rs = $dsql->ExecuteNoneQuery($sql);
            $doarray[$tb]  = 1;
        }
        $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__arctiny`");
        if ($row['dd'] == $allarcnum) {
            $msg .= "<span class='text-dark'>".Lang('sys_repair_test_arctiny_same2')."</span><br>";
        } else {
            $msg .= "<span class='text-danger'>".Lang('sys_repair_test_err_arctiny')."</span><br>";
            $errall = "<a href='sys_repair.php?dopost=3' class='btn btn-danger'>".Lang('sys_repair_test_check')."</a> ";
        }
    }
    UpDateCatCache();
    $wecome_info = "<a href='sys_repair.php'>".Lang("sys_repair")."</a> &gt; ".Lang('sys_repair_test_check_title');
    $msg = "
    <table>
    <tr>
    <td>
    {$msg}
    <br>
    {$errall}
    </td>
  </tr>
 </table>
    ";
    DedeWin::Instance()->Init("sys_repair.php", "js/blank.js", "POST' enctype='multipart/form-data' ")
    ->AddTitle(Lang('sys_repair_test_check_title2'))->AddMsgItem("<div>$msg</div>")
    ->GetWindow('hand', '')->Display();
    exit();
}
//高级方式修复微表，会删除不合法主键的内容
else if ($dopost == 3) {
    $errnum = 0;
    $sql = " TRUNCATE TABLE `#@__arctiny`";
    $dsql->ExecuteNoneQuery($sql);
    $sql = "SELECT arc.id, arc.typeid, arc.typeid2, arc.arcrank, arc.channel, arc.senddate, arc.sortrank, arc.mid, ch.addtable FROM `#@__archives` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel";
    $dsql->Execute('me', $sql);
    while ($row = $dsql->GetArray('me')) {
        $sql = "INSERT INTO `#@__arctiny`(id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid) VALUES ('{$row['id']}','{$row['typeid']}','{$row['typeid2']}','{$row['arcrank']}', '{$row['channel']}','{$row['senddate']}','{$row['sortrank']}','{$row['mid']}'); ";
        $rs = $dsql->ExecuteNoneQuery($sql);
        if (!$rs) {
            $addtable = trim($addtable);
            $errnum++;
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='{$row['id']}'");
            if (!empty($addtable)) $dsql->ExecuteNoneQuery("DELETE FROM `$addtable` WHERE id='{$row['id']}'");
        }
    }
    //导入单表模型微数据
    $dsql->SetQuery("SELECT id,addtable FROM `#@__channeltype` WHERE id < -1");
    $dsql->Execute();
    $doarray = array();
    while ($row = $dsql->GetArray()) {
        $tb = str_replace('#@__', $cfg_dbprefix, $row['addtable']);
        if (empty($tb) || isset($doarray[$tb])) {
            continue;
        } else {
            $sql = "INSERT INTO `#@__arctiny`(id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid) SELECT aid, typeid, 0, arcrank, channel, senddate, 0, mid FROM `$tb`";
            $rs = $dsql->ExecuteNoneQuery($sql);
            $doarray[$tb]  = 1;
        }
    }
    $wecome_info = "<a href='sys_repair.php'>".Lang("sys_repair")."</a> &gt; ".Lang('sys_repair_test_retiny');
    $msg = "
    <table>
        <tr>
            <td>".Lang('sys_repair_test_retiny_success',array('errnum'=>$errnum))."<br><a href='index_body.php' class='btn btn-success btn-sm'>".Lang('sys_repair_test_arctiny_ok')."</a></td>
        </tr>
    </table>
    ";
    DedeWin::Instance()->Init("sys_repair.php", "js/blank.js", "POST' enctype='multipart/form-data'")
    ->AddTitle(Lang('sys_repair_test_retiny_title'))->AddMsgItem("<div>$msg</div>")
    ->GetWindow('hand', '')->Display();
    exit();
}
?>