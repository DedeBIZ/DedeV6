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
require_once(dirname(__FILE__).'/config.php');
CheckPurview('sys_ArcBatch');
require_once(DEDEINC.'/libraries/oxwindow.class.php');
if (empty($dopost)) {
    $win = new OxWindow();
    $win->Init("sys_repair.php", "js/blank.js", "POST' enctype='multipart/form-data' ");
    $win->mainTitle = "系统修复工具";
    $wecome_info = "<a href='index_body.php'>系统主页</a> &gt; 系统错误修复工具";
    $win->AddTitle('本工具用于检测和修复您的系统可能存在的错误');
    $msg = "
    <table>
    <tr>
    <td>
    由于手动升级时用户没运行指定的SQL语句，或自动升级的遗漏处理或处理出错，可能会导致一些错误，使用本工具会自动检测并处理<br>
    本工具目前主要执行下面动作：<br>
    1、修复/优化数据表；<br>
    2、更新系统缓存；<br>
    3、检测系统变量一致性<br>
    4、检测微表与主表数据一致性<br>
    <br>
    <a href='sys_repair.php?dopost=1' class='btn btn-danger'>常规检测</a>
    </td>
  </tr>
 </table>
    ";
    $win->AddMsgItem("<div style='line-height:26px'>$msg</div>");
    $winform = $win->GetWindow('hand', '');
    $win->Display();
    exit();
}
/*-------------------
数据结构常规检测
function 1_test_db() {  }
--------------------*/
else if ($dopost == 1) {
    $win = new OxWindow();
    $win->Init("sys_repair.php", "js/blank.js", "POST' enctype='multipart/form-data' ");
    $win->mainTitle = "系统修复工具";
    $wecome_info = "<a href='sys_repair.php'>系统错误修复工具</a> &gt; 检测数据结构";
    $win->AddTitle('本工具用于检测和修复您的系统可能存在的错误');
    $msg = "
    <table>
    <tr>
    <td>
    <span class='text-dark'>已完成数据结构完整性检测</span>
    <br>
    如果您系统有下面几种问题之一，请检测微表正确性：<br>
    1、无法获得主键，因此无法进行后续操作<br>
    2、更新数据库archives表时出错<br>
    3、列表显示数据目与实际文档数不一致<br>
    <br>
    <a href='sys_repair.php?dopost=2' class='btn btn-danger'>检测数据</a>
    </td>
  </tr>
 </table>
    ";
    $win->AddMsgItem("<div style='line-height:26px'>$msg</div>");
    $winform = $win->GetWindow('hand', '');
    $win->Display();
    exit();
}
/*-------------------
检测微表正确性并尝试修复
function 2_test_arctiny() {  }
--------------------*/
else if ($dopost == 2) {
    $msg = '';
    $allarcnum = 0;
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__archives` ");
    $allarcnum = $arcnum = $row['dd'];
    $msg .= "#@__archives 表总记录数：{$arcnum} <br>";
    $shtables = array();
    $dsql->Execute('me', " SELECT addtable FROM `#@__channeltype` WHERE id < -1 ");
    while ($row = $dsql->GetArray('me')) {
        $addtable = strtolower(trim(str_replace('#@__', $cfg_dbprefix, $row['addtable'])));
        if (empty($addtable)) {
            continue;
        } else {
            if (!isset($shtables[$addtable])) {
                $shtables[$addtable] = 1;
                $row = $dsql->GetOne("SELECT COUNT(aid) AS dd FROM `$addtable` ");
                $msg .= "{$addtable} 表总记录数：{$row['dd']} <br>";
                $allarcnum += $row['dd'];
            }
        }
    }
    $msg .= "总有效记录数：{$allarcnum} <br> ";
    $errall = "<a href='index_body.php' class='btn btn-success btn-sm'>完成修正</a>";
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__arctiny` ");
    $msg .= "微统计表记录数：{$row['dd']}<br>";
    if ($row['dd'] == $allarcnum) {
        $msg .= "<span class='text-dark'>两者记录一致，无需修正</span><br>";
    } else {
        $sql = "TRUNCATE TABLE `#@__arctiny`";
        $dsql->ExecuteNoneQuery($sql);
        $msg .= "<span class='text-danger'>两者记录不一致，尝试进行简单修正</span><br>";
        //导入普通模型微数据
        $sql = "INSERT INTO `#@__arctiny`(id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid)  
            SELECT id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid FROM `#@__archives` ";
        $dsql->ExecuteNoneQuery($sql);
        //导入单表模型微数据
        foreach ($shtables as $tb => $v) {
            $sql = "INSERT INTO `#@__arctiny`(id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid)  
                SELECT aid, typeid, 0, arcrank, channel, senddate, 0, mid FROM `$tb` ";
            $rs = $dsql->ExecuteNoneQuery($sql);
            $doarray[$tb]  = 1;
        }
        $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__arctiny` ");
        if ($row['dd'] == $allarcnum) {
            $msg .= "<span class='text-dark'>修正记录成功</span><br>";
        } else {
            $msg .= "<span class='text-danger'>修正记录失败，建议进行高级综合检测</span><br>";
            $errall = "<a href='sys_repair.php?dopost=3' class='btn btn-danger'>结合性检测</a> ";
        }
    }
    UpDateCatCache();
    $win = new OxWindow();
    $win->Init("sys_repair.php", "js/blank.js", "POST' enctype='multipart/form-data' ");
    $win->mainTitle = "系统修复工具";
    $wecome_info = "<a href='sys_repair.php'>系统错误修复工具</a> &gt; 检测微表正确性";
    $win->AddTitle('本工具用于检测和修复您的系统可能存在的错误');
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
    $win->AddMsgItem("<div style='line-height:26px'>$msg</div>");
    $winform = $win->GetWindow('hand', '');
    $win->Display();
    exit();
}
/*-------------------
高级方式修复微表(会删除不合法主键的内容)
function 3_re_arctiny() {  }
--------------------*/
else if ($dopost == 3) {
    $errnum = 0;
    $sql = " TRUNCATE TABLE `#@__arctiny`";
    $dsql->ExecuteNoneQuery($sql);
    $sql = "SELECT arc.id, arc.typeid, arc.typeid2, arc.arcrank, arc.channel, arc.senddate, arc.sortrank, arc.mid, ch.addtable FROM `#@__archives` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel ";
    $dsql->Execute('me', $sql);
    while ($row = $dsql->GetArray('me')) {
        $sql = "INSERT INTO `#@__arctiny`(id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid)
            VALUES('{$row['id']}','{$row['typeid']}','{$row['typeid2']}','{$row['arcrank']}', '{$row['channel']}','{$row['senddate']}','{$row['sortrank']}','{$row['mid']}'); ";
        $rs = $dsql->ExecuteNoneQuery($sql);
        if (!$rs) {
            $addtable = trim($addtable);
            $errnum++;
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='{$row['id']}' ");
            if (!empty($addtable)) $dsql->ExecuteNoneQuery("DELETE FROM `$addtable` WHERE id='{$row['id']}' ");
        }
    }
    //导入单表模型微数据
    $dsql->SetQuery("SELECT id,addtable FROM `#@__channeltype` WHERE id < -1 ");
    $dsql->Execute();
    $doarray = array();
    while ($row = $dsql->GetArray()) {
        $tb = str_replace('#@__', $cfg_dbprefix, $row['addtable']);
        if (empty($tb) || isset($doarray[$tb])) {
            continue;
        } else {
            $sql = "INSERT INTO `#@__arctiny`(id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid)  
                SELECT aid, typeid, 0, arcrank, channel, senddate, 0, mid FROM `$tb` ";
            $rs = $dsql->ExecuteNoneQuery($sql);
            $doarray[$tb]  = 1;
        }
    }
    $win = new OxWindow();
    $win->Init("sys_repair.php", "js/blank.js", "POST' enctype='multipart/form-data'");
    $win->mainTitle = "系统修复工具";
    $wecome_info = "<a href='sys_repair.php'>系统错误修复工具</a> &gt; 高级综合检测修复";
    $win->AddTitle('本工具用于检测和修复您的系统可能存在的错误');
    $msg = "
    <table>
    <tr>
    <td>
    完成所有修复操作，移除错误记录 {$errnum} 条
    <br>
    <a href='index_body.php' class='btn btn-success btn-sm'>完成修正</a>
    </td>
  </tr>
 </table>
    ";
    $win->AddMsgItem("<div style='line-height:26px'>$msg</div>");
    $winform = $win->GetWindow('hand', '');
    $win->Display();
    exit();
}