<?php
/**
 * 修改软件模型
 *
 * @version        $id:soft_edit.php 16:09 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('a_Edit,a_AccEdit,a_MyEdit');
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEADMIN."/inc/inc_archives_functions.php");
if (empty($dopost)) $dopost = '';
if ($dopost != 'save') {
    require_once(DEDEADMIN."/inc/inc_catalog_options.php");
    require_once(DEDEINC."/dedetag.class.php");
    ClearMyAddon();
    $aid = preg_replace("#[^0-9]#", '', $aid);
    $channelid = "3";
    //读取归档信息
    $arcQuery = "SELECT `#@__channeltype`.typename as channelname, `#@__arcrank`.membername as rankname, `#@__archives`.* FROM `#@__archives` LEFT JOIN `#@__channeltype` ON `#@__channeltype`.id=`#@__archives`.channel LEFT JOIN `#@__arcrank` ON `#@__arcrank`.`rank`=`#@__archives`.arcrank WHERE `#@__archives`.id='$aid'";
    $dsql->SetQuery($arcQuery);
    $arcRow = $dsql->GetOne($arcQuery);
    if (!is_array($arcRow)) {
        ShowMsg("读取文档信息出错", "-1");
        exit();
    }
    $query = "SELECT * FROM `#@__channeltype` WHERE id='".$arcRow['channel']."'";
    $cInfos = $dsql->GetOne($query);
    if (!is_array($cInfos)) {
        ShowMsg("读取栏目信息出错", "javascript:;");
        exit();
    }
    $addtable = $cInfos['addtable'];
    $addQuery = "SELECT * FROM `$addtable` WHERE aid='$aid'";
    $addRow = $dsql->GetOne($addQuery);
    $newRowStart = 1;
    $nForm = '';
    $daccess = $addRow['daccess'];
    $needmoney = $addRow['needmoney'];
    if ($addRow['softlinks'] != '') {
        $dtp = new DedeTagParse();
        $dtp->LoadSource($addRow['softlinks']);
        if (is_array($dtp->CTags)) {
            foreach ($dtp->CTags as $ctag) {
                if ($ctag->GetName() == 'link') {
                    $islocal = $ctag->GetAtt('islocal');
                    if ($islocal != 1) $needmsg = "<label><input type='checkbox' name='del{$newRowStart}' value='1'> 删除</label>";
                    else $needmsg = '<button type="button" name="sel1" id="sel1" class="btn btn-success btn-sm" onclick="SelectSoft(\'form1.softurl'.$newRowStart.'\')">选择</button>';
                    $nForm .= "<div class='my-2'><label>软件网址{$newRowStart}：<input type='text' name='softurl{$newRowStart}' value='".trim($ctag->GetInnerText())."' class='admin-input-lg'></label> <label>下载名称：<input type='text' name='servermsg{$newRowStart}' value='".$ctag->GetAtt("text")."' class='admin-input-sm'></label><input type='hidden' name='islocal{$newRowStart}' value='{$islocal}'> $needmsg</div>\r\n";
                    $newRowStart++;
                }
            }
        }
        $dtp->Clear();
    }
    $channelid = $arcRow['channel'];
    $tags = GetTags($aid);
    $arcRow = XSSClean($arcRow);
    $addRow = XSSClean($addRow);
    include DedeInclude("templets/soft_edit.htm");
    exit();
} else if ($dopost == 'save') {
    require_once(DEDEINC.'/image.func.php');
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    $flag = isset($flags) ? join(',', $flags) : '';
    $notpost = isset($notpost) && $notpost == 1 ? 1 : 0;
    if (empty($typeid2)) $typeid2 = 0;
    if (!isset($autokey)) $autokey = 0;
    if (!isset($remote)) $remote = 0;
    if (!isset($dellink)) $dellink = 0;
    if (!isset($autolitpic)) $autolitpic = 0;
    if (trim($title) == '') {
        ShowMsg("文档标题不能为空", "-1");
        exit();
    }
    if (empty($typeid)) {
        ShowMsg("请选择文档栏目", "-1");
        exit();
    }
    if (empty($channelid)) {
        ShowMsg("文档为非指定类型，请检查您发布文档是否正确", "-1");
        exit();
    }
    if (!CheckChannel($typeid, $channelid)) {
        ShowMsg("您所选择的栏目与当前模型不相符，请重新选择", "-1");
        exit();
    }
    if (!TestPurview('a_Edit')) {
        CheckCatalog($typeid, "您没有操作栏目{$typeid}文档权限");
    }
    //对保存的文档进行处理
    $pubdate = GetMkTime($pubdate);
    $senddate = GetMkTime($senddate);
    $sortrank = AddDay($pubdate, $sortup);
    if ($ishtml == 0) {
        $ismake = -1;
    } else {
        $ismake = 0;
    }
    $title = cn_substrR($title, $cfg_title_maxlen);
    $shorttitle = cn_substrR($shorttitle, 255);
    $color =  cn_substrR($color, 7);
    $writer =  cn_substrR($writer, 255);
    $source = cn_substrR($source, 255);
    $description = cn_substrR($description, $cfg_auot_description);
    $keywords = cn_substrR($keywords, 255);
    $filename = trim(cn_substrR($filename, 50));
    $isremote  = 0;
    $serviterm = empty($serviterm) ? "" : $serviterm;
    if (!TestPurview('a_Check,a_AccCheck,a_MyCheck')) {
        $arcrank = -1;
    }
    $adminid = $cuserLogin->getUserID();
    //处理上传的缩略图
    if (empty($ddisremote)) {
        $ddisremote = 0;
    }
    $litpic = GetDDImage('litpic', $picname, $ddisremote);
    //分析处理附加表数据
    $inadd_f = '';
    $inadd_v = '';
    if (!empty($dede_addonfields)) {
        $addonfields = explode(';', $dede_addonfields);
        $inadd_f = '';
        $inadd_v = '';
        if (is_array($addonfields)) {
            foreach ($addonfields as $v) {
                if ($v == '') {
                    continue;
                }
                $vs = explode(',', $v);
                //网页文本特殊处理
                if ($vs[1] == 'htmltext' || $vs[1] == 'textdata') {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]}, $description, $litpic, $keywords, $vs[1]);
                } else {
                    if (!isset(${$vs[0]})) {
                        ${$vs[0]} = '';
                    }
                    ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $id);
                }
                $inadd_f .= ",`{$vs[0]}` = '".${$vs[0]}."'";
            }
        }
    }
    //处理图片文档的自定义属性
    if ($litpic != '' && !preg_match('#p#', $flag)) {
        $flag = ($flag == '' ? 'p' : $flag.',p');
    }
    if ($redirecturl != '' && !preg_match('#j#', $flag)) {
        $flag = ($flag == '' ? 'j' : $flag.',j');
    }
    //跳转网址的文档强制为动态
    if (preg_match('#j#', $flag)) $ismake = -1;
    //修改主文档表
    $inQuery = "UPDATE `#@__archives` SET typeid='$typeid',typeid2='$typeid2',sortrank='$sortrank',flag='$flag',click='$click',ismake='$ismake',arcrank='$arcrank',`money`='$money',title='$title',color='$color',source='$source',writer='$writer',litpic='$litpic',pubdate='$pubdate',senddate='$senddate',notpost='$notpost',description='$description',keywords='$keywords',shorttitle='$shorttitle',filename='$filename',dutyadmin='$adminid',weight='$weight' WHERE id='$id'; ";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("数据保存到数据库文档主表出错，请检查数据库字段", "-1");
        exit();
    }
    //软件链接列表
    $urls = '';
    for ($i = 1; $i <= 30; $i++) {
        if (!empty(${'softurl'.$i})) {
            $islocal = empty(${'islocal'.$i}) ? '' : 1;
            $isneed = empty(${'del'.$i}) ? true : false;
            $servermsg = str_replace("'", '', stripslashes(${'servermsg'.$i}));
            $softurl = stripslashes(${'softurl'.$i});
            if ($servermsg == '') {
                $servermsg = '下载地址'.$i;
            }
            if ($softurl != '') {
                if ($islocal == 1) $urls .= "{dede:link islocal='$islocal' text='{$servermsg}'} $softurl {/dede:link}\r\n";
                else if ($isneed) $urls .= "{dede:link text='$servermsg'} $softurl {/dede:link}\r\n";
                else continue;
            }
        }
    }
    $urls = addslashes($urls);
    //更新附加表
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if ($addtable != '') {
        $useip = GetIP();
        $inQuery = "UPDATE `$addtable` SET typeid='$typeid',filetype='$filetype',language='$language',softtype='$softtype',accredit='$accredit',os='$os',softrank='$softrank',officialUrl ='$officialUrl',officialDemo ='$officialDemo',softsize='$softsize',softlinks='$urls',redirecturl='$redirecturl',userip='$useip',daccess='$daccess',needmoney='$needmoney',introduce='$body' {$inadd_f} WHERE aid='$id';";
        if (!$dsql->ExecuteNoneQuery($inQuery)) {
            ShowMsg("数据保存到数据库附加表出错，请检查数据库字段", "-1");
            exit();
        }
    }
    //生成网页
    UpIndexKey($id, $arcrank, $typeid, $sortrank, $tags);
    $arcUrl = MakeArt($id, TRUE, TRUE, $isremote);
    if ($arcUrl == "") {
        $arcUrl = $cfg_phpurl."/view.php?aid=$id";
    }
    ClearMyAddon($id, $title);
    //自动更新关联文档
    if (is_array($automake)) {
        foreach ($automake as $key => $value) {
            if (isset(${$key}) && !empty(${$key})) {
                $ids = explode(",", ${$key});
                foreach ($ids as $id) {
                    MakeArt($id, true, true, $isremote);
                }
            }
        }
    }
    //返回成功信息
    $msg = "<tr>
        <td align='center'><a href='$arcUrl' target='_blank' class='btn btn-success btn-sm'>浏览文档</a><a href='soft_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布文档</a><a href='archives_do.php?aid=".$id."&dopost=editArchives' class='btn btn-success btn-sm'>修改文档</a><a href='catalog_do.php?cid=$typeid&dopost=listArchives' class='btn btn-success btn-sm'>返回文档列表</a></td>
    </tr>";
    $wintitle = "成功修改软件文档";
    $win = new OxWindow();
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", FALSE);
    $win->Display();
}
?>