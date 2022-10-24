<?php
/**
 * 自定义表单列表
 *
 * @version        $Id: diy_list.php 1 18:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Archive\DiyForm;
use DedeBIZ\libraries\DataListCP;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('c_New');
$diyid = isset($diyid) && is_numeric($diyid) ? $diyid : 0;
$action = isset($action) && in_array($action, array('post', 'list', 'edit', 'check', 'delete','excel')) ? $action : '';
if (empty($diyid)) {
    showMsg(Lang("illegal_operation"), 'javascript:;');
    exit();
}
$diy = new DiyForm($diyid);
if ($action == 'post') {
    if (empty($do)) {
        $postform = $diy->getForm('post', '', 'admin');
        include DEDEADMIN.'/templets/diy_post.htm';
    } else if ($do == 2) {
        $dede_fields = empty($dede_fields) ? '' : trim($dede_fields);
        $dede_fieldshash = empty($dede_fieldshash) ? '' : trim($dede_fieldshash);
        if (!empty($dede_fields)) {
            if ($dede_fieldshash != md5($dede_fields.$cfg_cookie_encode)) {
                showMsg(Lang("diy_err_checkdata"), '-1');
                exit();
            }
        }
        $diyform = $dsql->getOne("SELECT * FROM `#@__diyforms` WHERE diyid=$diyid");
        if (!is_array($diyform)) {
            showmsg(Lang("diy_err_not_exists"), '-1');
            exit();
        }
        $addvar = $addvalue = '';
        if (!empty($dede_fields)) {
            $fieldarr = explode(';', $dede_fields);
            if (is_array($fieldarr)) {
                foreach ($fieldarr as $field) {
                    if ($field == '') {
                        continue;
                    }
                    $fieldinfo = explode(',', $field);
                    if ($fieldinfo[1] == 'htmltext' || $fieldinfo[1] == 'textdata') {
                        ${$fieldinfo[0]} = HtmlReplace(stripslashes(${$fieldinfo[0]}),1);
                        ${$fieldinfo[0]} = addslashes(${$fieldinfo[0]});
                        ${$fieldinfo[0]} = getFieldValue(${$fieldinfo[0]}, $fieldinfo[1], 0, 'add', '', 'member');
                    } else {
                        ${$fieldinfo[0]} = getFieldValue(${$fieldinfo[0]}, $fieldinfo[1], 0, 'add', '', 'member');
                    }
                    $addvar .= ', `'.$fieldinfo[0].'`';
                    $addvalue .= ", '".${$fieldinfo[0]}."'";
                }
            }
        }
        $query = "INSERT INTO `{$diy->table}` (`id`, `ifcheck` $addvar) VALUES (NULL, 0 $addvalue)";
        if ($dsql->ExecuteNoneQuery($query)) {
            $goto = "diy_list.php?action=list&diyid={$diy->diyid}";
            showmsg(Lang('diy_success_send'), $goto);
        } else {
            showmsg(Lang('diy_err_send'), '-1');
        }
    }
} else if ($action == 'list') {
    $query = "SELECT * FROM {$diy->table} ORDER BY id DESC";
    $datalist = new DataListCP();
    $datalist->pagesize = 30;
    $datalist->SetParameter('action', 'list');
    $datalist->SetParameter('diyid', $diyid);
    $datalist->SetTemplate(DEDEADMIN.'/templets/diy_list.htm');
    $datalist->SetSource($query);
    $fieldlist = $diy->getFieldList();
    $datalist->Display();
} else if ($action == 'edit') {
    if (empty($do)) {
        $id = isset($id) && is_numeric($id) ? $id : 0;
        if (empty($id)) {
            showMsg(Lang('diy_err_no_select'), 'javascript:;');
            exit();
        }
        $query = "SELECT * FROM {$diy->table} WHERE id=$id";
        $row = $dsql->GetOne($query);
        if (!is_array($row)) {
            showmsg(Lang("diy_err_not_exists"), '-1');
            exit();
        }
        $postform = $diy->getForm('edit', $row, 'admin');
        $fieldlist = $diy->getFieldList();
        $c1 = $row['ifcheck'] == 1 ? 'checked' : '';
        $c2 = $row['ifcheck'] == 0 ? 'checked' : '';
        include DEDEADMIN.'/templets/diy_edit_content.htm';
    } else if ($do == 2) {
        $dede_fields = empty($dede_fields) ? '' : trim($dede_fields);
        $diyform = $dsql->GetOne("SELECT * FROM `#@__diyforms` WHERE diyid=$diyid");
        $diyco = $dsql->GetOne("SELECT * FROM `$diy->table` WHERE id='$id'");
        if (!is_array($diyform)) {
            showmsg(Lang("diy_err_not_exists"), '-1');
            exit();
        }
        $addsql = '';
        if (!empty($dede_fields)) {
            $fieldarr = explode(';', $dede_fields);
            if (is_array($fieldarr)) {
                foreach ($fieldarr as $field) {
                    if ($field == '') {
                        continue;
                    }
                    $fieldinfo = explode(',', $field);
                    if ($fieldinfo[1] == 'htmltext' || $fieldinfo[1] == 'textdata') {
                        ${$fieldinfo[0]} = HtmlReplace(stripslashes(${$fieldinfo[0]}),1);
                        ${$fieldinfo[0]} = addslashes(${$fieldinfo[0]});
                        ${$fieldinfo[0]} = GetFieldValue(${$fieldinfo[0]}, $fieldinfo[1], 0, 'add', '', 'member');
                        ${$fieldinfo[0]} = empty(${$fieldinfo[0]}) ? $diyco[$fieldinfo[0]] : ${$fieldinfo[0]};
                    } else {

                        ${$fieldinfo[0]} = GetFieldValue(${$fieldinfo[0]}, $fieldinfo[1], 0, 'add', '', 'diy', $fieldinfo[0]);
                        ${$fieldinfo[0]} = empty(${$fieldinfo[0]}) ? $diyco[$fieldinfo[0]] : ${$fieldinfo[0]};
                    }
                    $addsql .= !empty($addsql) ? ',`'.$fieldinfo[0]."`='".${$fieldinfo[0]}."'" : '`'.$fieldinfo[0]."`='".${$fieldinfo[0]}."'";
                }
            }
        }
        $query = "UPDATE `$diy->table` SET $addsql WHERE id=$id";
        if ($dsql->ExecuteNoneQuery($query)) {
            $goto = "diy_list.php?action=list&diyid={$diy->diyid}";
            showmsg(Lang('operation_successful'), $goto);
        } else {
            showmsg(Lang('operation_successful'), '-1');
        }
    }
} elseif ($action == 'check') {
    if (is_array($id) && is_all_numeric($id)) {
        $ids = implode(',', $id);
    } else {
        showmsg(Lang('diy_err_no_select'), '-1');
        exit();
    }
    $query = "UPDATE `$diy->table` SET ifcheck=1 WHERE id IN ($ids)";
    if ($dsql->ExecuteNoneQuery($query)) {
        showmsg(Lang('operation_successful'), "diy_list.php?action=list&diyid={$diy->diyid}");
    } else {
        showmsg(Lang('operation_failed'), "diy_list.php?action=list&diyid={$diy->diyid}");
    }
} elseif ($action == 'delete') {
    if (empty($do)) {
        if (is_array($id)) {
            $ids = implode(',', $id);
        } else {
            showmsg(Lang('diy_err_no_select'), '-1');
            exit();
        }
        $query = "DELETE FROM `$diy->table` WHERE id IN ($ids)";
        if ($dsql->ExecuteNoneQuery($query)) {
            showmsg(Lang('operation_successful'), "diy_list.php?action=list&diyid={$diy->diyid}");
        } else {
            showmsg(Lang('operation_failed'), "diy_list.php?action=list&diyid={$diy->diyid}");
        }
    } else if ($do = 1) {
        $row = $dsql->GetOne("SELECT * FROM `$diy->table` WHERE id='$id'");
        if (file_exists($cfg_basedir.$row[$name])) {
            unlink($cfg_basedir.$row[$name]);
            $dsql->ExecuteNoneQuery("UPDATE `$diy->table` SET $name='' WHERE id='$id'");
            showmsg(Lang('operation_successful'), "diy_list.php?action=list&diyid={$diy->diyid}");
        } else {
            showmsg(Lang('diy_err_file_notexists'), '-1');
        }
    }
} 
elseif ($action == 'excel') {
    ob_end_clean();//清除缓冲区,避免乱码
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename={$diy->name}_".date("Y-m-d").".xls");
    print(chr(0xEF).chr(0xBB).chr(0xBF));//清除bom
    $fieldlist = (array)$diy->getFieldList();
    echo "<table><tr>";
    foreach($fieldlist as $field=>$fielddata)
    {
        echo "<th>{$fielddata[0]}</th>";
    }
    echo "<th>".Lang('status')."</th>";
    echo "</tr>";
    $sql = "SELECT * FROM {$diy->table} ORDER BY id DESC";
    $dsql->SetQuery($sql);
    $dsql->Execute('t');
    while($arr = $dsql->GetArray('t'))
    {
        echo "<tr>";
        foreach($fieldlist as $key => $field)
        {
            echo "<td>".$arr[$key]."</td>";
        }
    $status = $arr['ifcheck'] == 1 ? Lang('reviewed') : Lang('not_approved');
    echo "<td>".$status."</td>";
    echo "</tr>";
    }
    echo "</table>";
} else {
    showmsg(Lang("illegal_operation"), "-1");
}
?>