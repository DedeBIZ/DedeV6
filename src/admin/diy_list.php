<?php
/**
 * 自定义表单列表
 *
 * @version        $id:diy_list.php 18:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('c_New');
$diyid = isset($diyid) && is_numeric($diyid) ? $diyid : 0;
$action = isset($action) && in_array($action, array('post', 'list', 'edit', 'check', 'delete', 'excel')) ? $action : '';
if (empty($diyid)) {
    showMsg("操作失败", 'javascript:;');
    exit();
}
require_once DEDEINC.'/diyform.class.php';
$diy = new diyform($diyid);
if ($action == 'post') {
    if (empty($do)) {
        $postform = $diy->getForm('post', '', 'admin');
        include DEDEADMIN.'/templets/diy_post.htm';
    } else if ($do == 2) {
        $dede_fields = empty($dede_fields) ? '' : trim($dede_fields);
        $dede_fieldshash = empty($dede_fieldshash) ? '' : trim($dede_fieldshash);
        if (!empty($dede_fields)) {
            if ($dede_fieldshash != md5($dede_fields.$cfg_cookie_encode)) {
                showMsg("数据校验不对，程序返回", '-1');
                exit();
            }
        }
        $diyform = $dsql->getOne("SELECT * FROM `#@__diyforms` WHERE diyid=$diyid");
        if (!is_array($diyform)) {
            showmsg("表单不存在，程序返回", '-1');
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
        $query = "INSERT INTO `{$diy->table}` (`id`, `ifcheck` $addvar)  VALUES (NULL, 0 $addvalue)";
        if ($dsql->ExecuteNoneQuery($query)) {
            $goto = "diy_list.php?action=list&diyid={$diy->diyid}";
            showmsg('发布成功', $goto);
        } else {
            showmsg('发布失败', '-1');
        }
    }
} else if ($action == 'list') {
    include_once DEDEINC.'/datalistcp.class.php';
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
            showMsg('操作失败，未指定id', 'javascript:;');
            exit();
        }
        $query = "SELECT * FROM {$diy->table} WHERE id=$id";
        $row = $dsql->GetOne($query);
        if (!is_array($row)) {
            showmsg("您浏览的记录不存在或未审核", '-1');
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
            showmsg("表单不存在，程序返回", '-1');
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
            showmsg('修改成功', $goto);
        } else {
            showmsg('修改成功', '-1');
        }
    }
} elseif ($action == 'check') {
    if (is_array($id) && is_all_numeric($id)) {
        $ids = implode(',', $id);
    } else {
        showmsg('未选中要操作的表单', '-1');
        exit();
    }
    $query = "UPDATE `$diy->table` SET ifcheck=1 WHERE id IN ($ids)";
    if ($dsql->ExecuteNoneQuery($query)) {
        showmsg('审核成功', "diy_list.php?action=list&diyid={$diy->diyid}");
    } else {
        showmsg('审核失败', "diy_list.php?action=list&diyid={$diy->diyid}");
    }
} elseif ($action == 'delete') {
    if (empty($do)) {
        if (is_array($id)) {
            $ids = implode(',', $id);
        } else {
            showmsg('未选中要操作的表单', '-1');
            exit();
        }
        $query = "DELETE FROM `$diy->table` WHERE id IN ($ids)";
        if ($dsql->ExecuteNoneQuery($query)) {
            showmsg('删除成功', "diy_list.php?action=list&diyid={$diy->diyid}");
        } else {
            showmsg('删除失败', "diy_list.php?action=list&diyid={$diy->diyid}");
        }
    } else if ($do = 1) {
        $row = $dsql->GetOne("SELECT * FROM `$diy->table` WHERE id='$id'");
        if (file_exists($cfg_basedir.$row[$name])) {
            unlink($cfg_basedir.$row[$name]);
            $dsql->ExecuteNoneQuery("UPDATE `$diy->table` SET $name='' WHERE id='$id'");
            showmsg('删除成功', "diy_list.php?action=list&diyid={$diy->diyid}");
        } else {
            showmsg('删除失败', '-1');
        }
    }
} elseif ($action == 'excel') {
    ob_end_clean();//清除缓冲区，避免乱码
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename={$diy->name}".date("Y-m-d").".xls");
    print(chr(0xEF).chr(0xBB).chr(0xBF));//清除bom
    $fieldlist = (array)$diy->getFieldList();
    echo "<table><tr>";
    foreach($fieldlist as $field=>$fielddata)
    {
        echo "<th>{$fielddata[0]}</th>";
    }
    echo "<th>状态</th>";
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
    $status = $arr['ifcheck'] == 1 ? '已审核' : '未审核';
    echo "<td>".$status."</td>";
    echo "</tr>";
    }
    echo "</table>";
} else {
    showmsg('未定义操作', "-1");
}
?>