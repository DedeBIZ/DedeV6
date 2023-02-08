<?php
/**
 * 自定义表单
 *
 * @version        $id:diy.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
$diyid = isset($diyid) && is_numeric($diyid) ? $diyid : 0;
$action = isset($action) && in_array($action, array('post', 'list', 'view')) ? $action : 'post';
$id = isset($id) && is_numeric($id) ? $id : 0;
if (empty($diyid)) {
    showMsg('非法操作', 'javascript:;');
    exit();
}
require_once DEDEINC.'/diyform.class.php';
$diy = new diyform($diyid);
/*----------------------------
function Post(){ }
---------------------------*/
if ($action == 'post') {
    if (empty($do)) {
        $postform = $diy->getForm(true);
        include DEDEROOT."/theme/plus/{$diy->postTemplate}";
        exit();
    } elseif ($do == 2) {
        $dede_fields = empty($dede_fields) ? '' : trim($dede_fields);
        $dede_fieldshash = empty($dede_fieldshash) ? '' : trim($dede_fieldshash);
        if (!empty($dede_fields)) {
            if ($dede_fieldshash != md5($dede_fields.$cfg_cookie_encode)) {
                showMsg('数据校验不对，程序返回', '-1');
                exit();
            }
        }
        $diyform = $dsql->getOne("SELECT * FROM `#@__diyforms` WHERE diyid='$diyid' ");
        if (!is_array($diyform)) {
            showmsg('自定义表单不存在', '-1');
            exit();
        }
        $addvar = $addvalue = '';
        if (!empty($dede_fields)) {
            $fieldarr = explode(';', $dede_fields);
            if (is_array($fieldarr)) {
                foreach ($fieldarr as $field) {
                    if ($field == '') continue;
                    $fieldinfo = explode(',', $field);
                    if ($fieldinfo[1] == 'textdata') {
                        ${$fieldinfo[0]} = FilterSearch(stripslashes(${$fieldinfo[0]}));
                        ${$fieldinfo[0]} = addslashes(${$fieldinfo[0]});
                    } 
                    //获取地址，表单添加text数据类型ip字段型后模板用<input type="hidden" name="ip" value="">
                    if ($fieldinfo[0] == 'ip')
                    {
                        ${$fieldinfo[0]}=GetIP();
                    }
                    //获取时间，表单添加text数据类型sj字段型后模板用<input type="hidden" name="sj" value="">
                    if ($fieldinfo[0] == 'sj')
                    {
                        ${$fieldinfo[0]}=date("Y-m-d H:i:s");
                    } else {
                        ${$fieldinfo[0]} = GetFieldValue(${$fieldinfo[0]}, $fieldinfo[1],0,'add','','diy', $fieldinfo[0]);
                    }
                    $addvar .= ', `'.$fieldinfo[0].'`';
                    $addvalue .= ", '".${$fieldinfo[0]}."'";
                }
            }
        }
        $query = "INSERT INTO `{$diy->table}` (`id`, `ifcheck` $addvar) VALUES (NULL, 0 $addvalue); ";
        if ($dsql->ExecuteNoneQuery($query)) {
            $id = $dsql->GetLastID();
            if ($diy->public == 2)
            {
                $goto = "diy.php?action=list&diyid={$diy->diyid}";
                $bkmsg = '发布成功，现在跳转表单列表页';
            } else {
                $goto = !empty($cfg_cmspath) ? $cfg_cmspath : '/';
                $bkmsg = '发布成功，请等待管理员处理';
                //提交后返回提交页面
                echo"<script>alert('提交成功');history.go(-1)</script>";
            }
            showmsg($bkmsg, $goto);
        }
    }
}
/*----------------------------
function list(){ }
---------------------------*/
else if ($action == 'list') {
    if (empty($diy->public)) {
        ShowMsg('后台关闭前台浏览', 'javascript:;');
        exit();
    }
    include_once DEDEINC.'/datalistcp.class.php';
    if ($diy->public == 2)
        $query = "SELECT * FROM `{$diy->table}` ORDER BY id DESC";
    else
        $query = "SELECT * FROM `{$diy->table}` WHERE ifcheck=1 ORDER BY id DESC";
    $datalist = new DataListCP();
    $datalist->pagesize = 10;
    $datalist->SetParameter('action', 'list');
    $datalist->SetParameter('diyid', $diyid);
    $datalist->SetTemplate(DEDEINC."/../theme/plus/{$diy->listTemplate}");
    $datalist->SetSource($query);
    $fieldlist = $diy->getFieldList();
    $datalist->Display();
} else if ($action == 'view') {
    if (empty($diy->public)) {
        showMsg('后台关闭前台浏览', 'javascript:;');
        exit();
    }
    if (empty($id)) {
        showMsg('非法操作未指定id', 'javascript:;');
        exit();
    }
    if ($diy->public == 2) {
        $query = "SELECT * FROM `{$diy->table}` WHERE id='$id' ";
    } else {
        $query = "SELECT * FROM `{$diy->table}` WHERE id='$id' AND ifcheck=1";
    }
    $row = $dsql->GetOne($query);

    if (!is_array($row)) {
        showmsg('您浏览的记录不存在或未经审核', '-1');
        exit();
    }
    $fieldlist = $diy->getFieldList();
    include DEDEROOT."/theme/plus/{$diy->viewTemplate}";
}
?>