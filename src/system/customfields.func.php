<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 系统核心函数
 * @version        $id:customfields.func.php 2 20:50 2010年7月7日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
/**
 *  获得一个附加表单发布时用
 *
 * @access    public
 * @param     object  $ctag  标签
 * @param     string  $admintype  管理员类型
 * @return    string
 */
function GetFormItem($ctag, $admintype = 'admin')
{
    $fieldname = $ctag->GetName();
    $fieldType =     $ctag->GetAtt('type');
    $formitem = $formitem = GetSysTemplets("custom_fields_{$admintype}.htm");
    $innertext = trim($ctag->GetInnerText());
    if ($innertext != '') {
        $formitem = $innertext;
    }
    if ($fieldType == 'select') {
        $myformItem = '';
        $items = explode(',', $ctag->GetAtt("default"));
        $myformItem = "<select name='$fieldname' class='form-control admin-input-sm'>";
        foreach ($items as $v) {
            $v = trim($v);
            if ($v != '') {
                $myformItem .= "<option value='$v'>$v</option>";
            }
        }
        $myformItem .= "</select>";
        $innertext = $myformItem;
    } else if ($fieldType == 'stepselect') {
        global $hasSetEnumJs, $cfg_cmspath;
        $cmspath = ((empty($cfg_cmspath) || !preg_match('/[/$]/', $cfg_cmspath)) ? $cfg_cmspath.'/' : $cfg_cmspath);
        $myformItem = '';
        $myformItem .= "<input type='hidden' id='hidden_{$fieldname}' name='{$fieldname}' value='0'>";
        $myformItem .= "<span id='span_{$fieldname}'></span>";
        $myformItem .= "<span id='span_{$fieldname}_son'></span>";
        $myformItem .= "<span id='span_{$fieldname}_sec'></span>";
        if ($hasSetEnumJs != 'hasset') {
            $myformItem .= '<script src="'.$cmspath.'static/web/js/enums.js"></script>'."";
            $GLOBALS['hasSetEnumJs'] = 'hasset';
        }
        $myformItem .= "<script>
        var em_{$fieldname}s = [];
        fetch('{$cmspath}static/enums/{$fieldname}.json').then((resp)=>resp.json()).then((d)=>{
            Object.entries(d).forEach(v=>{
                em_{$fieldname}s[parseFloat(v[0])]= v[1];
            });
            MakeTopSelect('$fieldname', 0);
        })
        </script>";
        $formitem = str_replace('~name~', $ctag->GetAtt('itemname'), $formitem);
        $formitem = str_replace('~form~', $myformItem, $formitem);
        return $formitem;
    } else if ($fieldType == 'radio') {
        $myformItem = '';
        $items = explode(',', $ctag->GetAtt("default"));
        $i = 0;
        foreach ($items as $v) {
            $v = trim($v);
            if ($v != '') {
                $myformItem .= ($i == 0 ? "<div class='form-check form-check-inline'><label class='form-check-label'><input type='radio' name='$fieldname' class='form-check-input' value='$v' checked='checked'> $v</label></div>" : "<div class='form-check form-check-inline'><label class='form-check-label'><input type='radio' name='$fieldname' class='form-check-input' value='$v'> $v</label></div>");
                $i++;
            }
        }
        $innertext = $myformItem;
    } else if ($fieldType == 'checkbox') {
        $myformItem = '';
        $items = explode(',', $ctag->GetAtt("default"));
        foreach ($items as $v) {
            $v = trim($v);
            if ($v != '') {
                if ($admintype == 'membermodel') {
                    $myformItem .= "<div class='form-check form-check-inline'><label class='form-check-label'><input type='checkbox' name='{$fieldname}[]' class='form-check-input' value='$v'> $v</label></div>";
                } else {
                    $myformItem .= "<div class='form-check form-check-inline'><label class='form-check-label'><input type='checkbox' name='{$fieldname}[]' class='form-check-input' value='$v'> $v</label></div>";
                }
            }
        }
        $innertext = $myformItem;
    } else if ($fieldType == 'htmltext' || $fieldType == 'textdata') {
        $dfvalue = ($ctag->GetAtt('default') != '' ? $ctag->GetAtt('default') : '');
        $dfvalue = str_replace('{{', '<', $dfvalue);
        $dfvalue = str_replace('}}', '>', $dfvalue);
        if ($admintype == 'admin') {
            $innertext = GetEditor($fieldname, $dfvalue, 360, 'Basic', 'string');
        } else if ($admintype == 'diy') {
            $innertext = GetEditor($fieldname, $dfvalue, 360, 'Diy', 'string');
        } else {
            $innertext = GetEditor($fieldname, $dfvalue, 360, 'Member', 'string');
        }
    } else if ($fieldType == "multitext") {
        $innertext = "<textarea name='$fieldname' id='$fieldname' class='form-control admin-textarea-sm'></textarea>";
    } else if ($fieldType == "datetime") {
        $nowtime = GetDateTimeMk(time());
        $innertext = "<input type='text' name='$fieldname' value='$nowtime' id='$fieldname' class='form-control admin-input-lg datepicker'>";
    } else if ($fieldType == 'img') {
        $fname = defined('DEDEADMIN')? 'form1' : 'addcontent';
        $innertext = "<input type='text' name='$fieldname' id='$fieldname' class='form-control admin-input-lg' placeholder='请选择图片上传或填写图片地址'> <input type='button' name='".$fieldname."_bt' class='btn btn-success btn-sm' value='选择' onclick=\"SelectImage('$fname.$fieldname','big')\">";
    } else if ($fieldType == 'media') {
        $fname = defined('DEDEADMIN')? 'form1' : 'addcontent';
        $innertext = "<input type='text' name='$fieldname' id='$fieldname' class='form-control admin-input-lg' placeholder='请选择多媒体上传或填写多媒体地址'> <input type='button' name='".$fieldname."_bt' class='btn btn-success btn-sm' value='选择' onclick=\"SelectMedia('$fname.$fieldname')\">";
    } else if ($fieldType == 'addon') {
        $fname = defined('DEDEADMIN')? 'form1' : 'addcontent';
        $innertext = "<input type='text' name='$fieldname' id='$fieldname' class='form-control admin-input-lg' placeholder='请选择附件上传或填写附件地址'> <input type='button' name='".$fieldname."_bt' class='btn btn-success btn-sm' value='选择' onclick=\"SelectSoft('$fname.$fieldname')\">";
    } else if ($fieldType == 'int' || $fieldType == 'float') {
        $dfvalue = ($ctag->GetAtt('default') != '' ? $ctag->GetAtt('default') : '0');
        $innertext = "<input type='text' name='$fieldname' id='$fieldname' class='form-control admin-input-xs' value='$dfvalue'>";
    } else if ($fieldType == 'relation') {
        $dfvalue = ($ctag->GetAtt('default') != '' ? $ctag->GetAtt('default') : '');
        $channel = ($ctag->GetAtt('channel') == "") ? "1" : $ctag->GetAtt('channel');
        $innertext = "<textarea name='$fieldname' id='$fieldname' class='form-control admin-textarea-sm'>$dfvalue</textarea><br><button type='button' class='btn btn-success btn-sm' onclick='SelectArcList(\"form1.$fieldname\", $channel);'>选择关联文档</button>";
        if ($ctag->GetAtt('automake') == 1) {
            $innertext .= "<input type='hidden' name='automake[$fieldname]' value='1'>";
        }
        $innertext .= <<<EOT
<script>
if (typeof SelectArcList === "undefined") {
    function SelectArcList(fname,cid) {
    var posLeft = 10;
    var posTop = 10;
    window.open("content_select_list.php?f=" + fname+"&channelid="+cid, "selArcList", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=500,left=" + posLeft + ", top=" + posTop);
    }
}
</script>
EOT;
    } else {
        $dfvalue = ($ctag->GetAtt('default') != '' ? $ctag->GetAtt('default') : '');
        $innertext = "<input type='text' name='$fieldname' id='$fieldname' class='form-control admin-input-lg' value='$dfvalue'>
        ";
    }
    $formitem = str_replace("~name~", $ctag->GetAtt('itemname'), $formitem);
    $formitem = str_replace("~form~", $innertext, $formitem);
    return $formitem;
}
/**
 *  处理不同类型的数据
 *
 * @access    public
 * @param     string  $dvalue  默认值
 * @param     string  $dtype  默认类型
 * @param     int  $aid  文档id
 * @param     string  $job  操作类型
 * @param     string  $addvar  值
 * @param     string  $admintype  管理类型
 * @param     string  $fieldname  变量类型
 * @return    string
 */
function GetFieldValue($dvalue, $dtype, $aid = 0, $job = 'add', $addvar = '', $admintype = 'admin', $fieldname = '')
{
    global $cfg_basedir, $cfg_cmspath, $adminid, $cfg_ml, $cfg_cookie_encode;
    if (!empty($adminid)) {
        $adminid = $adminid;
    } else {
        $adminid = isset($cfg_ml) ? $cfg_ml->M_ID : 1;
    }
    if ($dtype == 'int') {
        if ($dvalue == '') {
            return 0;
        }
        return GetAlabNum($dvalue);
    } else if ($dtype == 'relation') {
        $dvalue = trim(preg_replace("#[^0-9,]#", "", $dvalue));
        return $dvalue;
    } else if ($dtype == 'stepselect') {
        $dvalue = trim(preg_replace("#[^0-9\.]#", "", $dvalue));
        return $dvalue;
    } else if ($dtype == 'float') {
        if ($dvalue == '') {
            return 0;
        }
        return GetAlabNum($dvalue);
    } else if ($dtype == 'datetime') {
        if ($dvalue == '') {
            return 0;
        }
        return GetMkTime($dvalue);
    } else if ($dtype == 'checkbox') {
        $okvalue = '';
        if (is_array($dvalue)) {
            $okvalue = join(',', $dvalue);
        }
        return $okvalue;
    } else if ($dtype == "htmltext") {
        if ($admintype == 'member' || $admintype == 'diy') {
            $dvalue = HtmlReplace($dvalue, -1);
        }
        return $dvalue;
    } else if ($dtype == "multitext") {
        if ($admintype == 'member' || $admintype == 'diy') {
            $dvalue = HtmlReplace($dvalue, 0);
        }
        return $dvalue;
    } else if ($dtype == "textdata") {
        $ipath = $cfg_cmspath."/data/textdata";
        $tpath = ceil($aid / 5000);
        if (!is_dir($cfg_basedir.$ipath)) {
            MkdirAll($cfg_basedir.$ipath, $GLOBALS['cfg_dir_purview']);
        }
        if (!is_dir($cfg_basedir.$ipath.'/'.$tpath)) {
            MkdirAll($cfg_basedir.$ipath.'/'.$tpath, $GLOBALS['cfg_dir_purview']);
        }
        $ipath = $ipath.'/'.$tpath;
        $filename = "{$ipath}/{$aid}-".cn_substr(md5($cfg_cookie_encode), 0, 16).".txt";
        //会员投稿文档安全处理
        if ($admintype == 'member' || $admintype == 'diy') {
            $dvalue = HtmlReplace($dvalue, -1);
        }
        $fp = fopen($cfg_basedir.$filename, "w");
        fwrite($fp, stripslashes($dvalue));
        fclose($fp);
        CloseFtp();
        return $filename;
    } else if ($dtype == 'img' || $dtype == 'imgfile') {
        return addslashes($dvalue);
    } else if ($dtype == 'addon' && $admintype == 'diy') {
        if ($admintype == 'diy') {
            return addslashes($dvalue);
        }
        $dvalue = MemberUploads($fieldname, '', 0, 'addon', '', -1, -1, false);
        return $dvalue;
    } else {
        if ($admintype == 'member' || $admintype == 'diy') {
            $dvalue = HtmlReplace($dvalue, 1);
        }
        return $dvalue;
    }
}
/**
 *  获得带值的表单修改时用
 *
 * @access    public
 * @param     object  $ctag  标签
 * @param     mixed  $fvalue  变量值
 * @param     string  $admintype  会员类型
 * @param     string  $fieldname  变量名称
 * @return    string
 */
function GetFormItemValue($ctag, $fvalue, $admintype = 'admin', $fieldname = '')
{
    global $cfg_basedir;
    $fieldname = $ctag->GetName();
    $formitem = $formitem = GetSysTemplets("custom_fields_{$admintype}.htm");
    $innertext = trim($ctag->GetInnerText());
    if ($innertext != '') {
        $formitem = $innertext;
    }
    $ftype = $ctag->GetAtt('type');
    $myformItem = '';
    if (preg_match("/select|radio|checkbox/i", $ftype)) {
        $items = explode(',', $ctag->GetAtt('default'));
    }
    if ($ftype == 'select') {
        $myformItem = "<select name='$fieldname' class='form-control admin-input-sm'>";
        if (is_array($items)) {
            foreach ($items as $v) {
                $v = trim($v);
                if ($v == '') {
                    continue;
                }
                $myformItem .= ($fvalue == $v ? "<option value='$v' selected>$v</option>" : "<option value='$v'>$v</option>");
            }
        }
        $myformItem .= "</select>";
        $innertext = $myformItem;
    } else if ($ctag->GetAtt("type") == 'stepselect') {
        global $hasSetEnumJs, $cfg_cmspath;
        $cmspath = ((empty($cfg_cmspath) || preg_match('/[/$]/', $cfg_cmspath)) ? $cfg_cmspath.'/' : $cfg_cmspath);
        $myformItem = '';
        $myformItem .= "<input type='hidden' id='hidden_{$fieldname}' name='{$fieldname}' value='{$fvalue}'>";
        $myformItem .= "<span id='span_{$fieldname}'></span>";
        $myformItem .= "<span id='span_{$fieldname}_son'></span>";
        $myformItem .= "<span id='span_{$fieldname}_sec'></span>";
        if ($hasSetEnumJs != 'hasset') {
            $myformItem .= '<script src="'.$cmspath.'static/web/js/enums.js"></script>'."";
            $GLOBALS['hasSetEnumJs'] = 'hasset';
        }
        $myformItem .= "<script>
        var em_{$fieldname}s = [];
        fetch('{$cmspath}static/enums/{$fieldname}.json').then((resp)=>resp.json()).then((d)=>{
            Object.entries(d).forEach(v=>{
                em_{$fieldname}s[parseFloat(v[0])]= v[1];
            });
            MakeTopSelect('$fieldname', $fvalue);
        })
        </script>";
        $formitem = str_replace('~name~', $ctag->GetAtt('itemname'), $formitem);
        $formitem = str_replace('~form~', $myformItem, $formitem);
        return $formitem;
    } else if ($ftype == 'radio') {
        if (is_array($items)) {
            foreach ($items as $v) {
                $v = trim($v);
                if ($v == '') continue;
                $myformItem .= ($fvalue == $v ? "<div class='form-check form-check-inline'><label class='form-check-label'><input type='radio' name='$fieldname' class='form-check-input' value='$v' checked='checked'> $v</label></div>" : "<div class='form-check form-check-inline'><label class='form-check-label'><input type='radio' name='$fieldname' class='form-check-input' value='$v'> $v</label></div>");
            }
        }
        $innertext = $myformItem;
    }
    //checkbox
    else if ($ftype == 'checkbox') {
        $myformItem = '';
        $fvalues = explode(',', $fvalue);
        if (is_array($items)) {
            foreach ($items as $v) {
                $v = trim($v);
                if ($v == '') {
                    continue;
                }
                if (in_array($v, $fvalues)) {
                    $myformItem .= "<div class='form-check form-check-inline'><label class='form-check-label'><input type='checkbox' name='{$fieldname}[]' class='form-check-input' value='$v' checked='checked'> $v</label></div>";
                } else {
                    $myformItem .= "<div class='form-check form-check-inline'><label class='form-check-label'><input type='checkbox' name='{$fieldname}[]' class='form-check-input' value='$v'> $v</label></div>";
                }
            }
        }
        $innertext = $myformItem;
    }
    //文本数据的特殊处理
    else if ($ftype == "textdata") {
        if (is_file($cfg_basedir.$fvalue)) {
            $fp = fopen($cfg_basedir.$fvalue, 'r');
            $okfvalue = '';
            while (!feof($fp)) {
                $okfvalue .= fgets($fp, 1024);
            }
            fclose($fp);
        } else {
            $okfvalue = '';
        }
        if ($admintype == 'admin') {
            $myformItem = GetEditor($fieldname, $okfvalue, 360, 'Basic', 'string')." <input type='hidden' name='{$fieldname}_file' value='{$fvalue}'> ";
        } else {
            $myformItem = GetEditor($fieldname, $okfvalue, 360, 'Member', 'string')." <input type='hidden' name='{$fieldname}_file' value='{$fvalue}'> ";
        }
        $innertext = $myformItem;
    } else if ($ftype == "htmltext") {
        if ($admintype == 'admin') {
            $myformItem = GetEditor($fieldname, $fvalue, 360, 'Basic', 'string')." ";
        } else {
            $myformItem = GetEditor($fieldname, $fvalue, 360, 'Member', 'string')." ";
        }
        $innertext = $myformItem;
    } else if ($ftype == "multitext") {
        $innertext = "<textarea name='$fieldname' id='$fieldname' class='form-control admin-textarea-sm'>$fvalue</textarea>";
    } else if ($ftype == "datetime") {
        $nowtime = GetDateTimeMk($fvalue);
        $innertext = "<input type='text' name='$fieldname' value='$nowtime' id='$fieldname' class='form-control admin-input-lg'>";
    } else if ($ftype == "img") {
        $tmpValue = $fvalue;
        $ndtp = new DedeTagParse();
        $ndtp->LoadSource($fvalue);
        if (!is_array($ndtp->CTags)) {
            $ndtp->Clear();
            $fvalue =  "";
        } else {
            $ntag = $ndtp->GetTag("img");
            if (!empty($ntag)) {
                $fvalue = trim($ntag->GetInnerText());
            }
        }
        $fvalue = empty($fvalue)? $tmpValue : $fvalue;
        $fname = defined('DEDEADMIN')? 'form1' : 'addcontent';
        $innertext = "<input type='text' name='$fieldname' value='$fvalue' id='$fieldname' class='form-control admin-input-lg'> <input type='button' name='".$fieldname."_bt' class='btn btn-success btn-sm' value='选择' onclick=\"SelectImage('$fname.$fieldname','big')\">";
    } else if ($ftype == "imgfile") {
        $fname = defined('DEDEADMIN')? 'form1' : 'addcontent';
        $innertext = "<input type='text' name='$fieldname' value='$fvalue' id='$fieldname' class='form-control admin-input-lg'>";
    } else if ($ftype == "media") {
        $fname = defined('DEDEADMIN')? 'form1' : 'addcontent';
        $selectStr = "<input type='button'  name='".$fieldname."_bt' class='btn btn-success btn-sm' value='选择' onclick=\"SelectMedia('$fname.$fieldname')\">";
        $innertext = "<input type='text' name='$fieldname' value='$fvalue' id='$fieldname' class='form-control admin-input-lg'> $selectStr";
    } else if ($ftype == "addon") {
        $fname = defined('DEDEADMIN')? 'form1' : 'addcontent';
        $selectStr = "<input type='button' name='".$fieldname."_bt' class='btn btn-success btn-sm' value='选择' onclick=\"SelectSoft('$fname.$fieldname')\">";
        $innertext = "<input type='text' name='$fieldname' id='$fieldname' value='$fvalue' class='form-control admin-input-lg'> $selectStr";
    } else if ($ftype == "int" || $ftype == "float") {
        $innertext = "<input type='text' name='$fieldname' id='$fieldname' class='form-control admin-input-xs' value='$fvalue'>";
    } else if ($ftype == "relation") {
        $fname = defined('DEDEADMIN')? 'form1' : 'addcontent';
        $channel = ($ctag->GetAtt('channel') == "") ? "1" : $ctag->GetAtt('channel');
        $innertext = "<textarea name='$fieldname' id='$fieldname' class='form-control admin-textarea-sm'>$fvalue</textarea><br><button type='button' class='btn btn-success btn-sm' onclick='SelectArcList(\"$fname.$fieldname\", $channel);'>选择关联文档</button>";
        if ($ctag->GetAtt('automake') == 1) {
            $innertext .= "<input type='hidden' name='automake[$fieldname]' value='1'>";
        }
        $innertext .= <<<EOT
<script>
if (typeof SelectArcList === "undefined") {
    function SelectArcList(fname,cid) {
    var posLeft = 10;
    var posTop = 10;
    window.open("content_select_list.php?f=" + fname+"&channelid="+cid, "selArcList", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=500,left=" + posLeft + ", top=" + posTop);
    }
}
</script>
EOT;
    } else {
        $innertext = "<input type='text' name='$fieldname' id='$fieldname' class='form-control admin-input-lg' value='$fvalue'>";
    }
    $formitem = str_replace('~name~', $ctag->GetAtt('itemname'), $formitem);
    $formitem = str_replace('~form~', $innertext, $formitem);
    return $formitem;
}
?>