<?php
/**
 * 文档处理
 *
 * @version        $Id: archives_do.php 1 8:26 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEADMIN.'/inc/inc_batchup.php');
require_once(DEDEADMIN.'/inc/inc_archives_functions.php');
require_once(DEDEINC.'/typelink/typelink.class.php');
require_once(DEDEINC.'/archive/archives.class.php');
$ENV_GOBACK_URL = (empty($_COOKIE['ENV_GOBACK_URL']) ? 'content_list.php' : $_COOKIE['ENV_GOBACK_URL']);
if (empty($dopost)) {
    ShowMsg('对不起，您没指定运行参数', '-1');
    exit();
}
$aid = isset($aid) ? preg_replace("#[^0-9]#", '', $aid) : '';
/*--------------------------
//编辑文档
function editArchives(){ }
---------------------------*/
if ($dopost == 'editArchives') {
    $query = "SELECT arc.id,arc.typeid,ch.maintable,ch.editcon
        FROM `#@__arctiny` arc
        LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid
        LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel
        WHERE arc.id='$aid' ";
    $row = $dsql->GetOne($query);
    $gurl = $row['editcon'];
    if ($gurl == '') $gurl = 'article_edit.php';
    header("location:{$gurl}?aid=$aid");
    exit();
} else if($dopost == 'upload_base64_image') {
    if ($litpic_b64 != "") {
        $data = explode(',', $litpic_b64);
        $ntime = time();
        $savepath = $cfg_image_dir.'/'.MyDate($cfg_addon_savetype, $ntime);
        CreateDir($savepath);
        $fullUrl = $savepath.'/'.dd2char(MyDate('mdHis', $ntime).$cuserLogin->getUserID().mt_rand(1000, 9999));
        $fullUrl = $fullUrl.".png";
        file_put_contents($cfg_basedir.$fullUrl, base64_decode($data[1]));
        //加水印
        WaterImg($cfg_basedir.$fullUrl, 'up');
        $litpic = $fullUrl;
        $result = array(
            "code" => 200,
            "data" => array(
                'image_url' => $litpic,
            ),
        );
        echo json_encode($result);
    } else {
        $result = array(
            "code" => -1,
            "msg" => 'no image',
        );
        echo json_encode($result);
    }
    exit();
}
/*--------------------------
//浏览文档
function viewArchives(){ }
---------------------------*/
else if ($dopost == "viewArchives") {
    $aid = preg_replace("#[^0-9]#", '', $aid);
    //获取主表信息
    $query = "SELECT arc.*,ch.maintable,ch.addtable,ch.issystem,ch.editcon,tp.typedir,tp.typename,tp.corank,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.sitepath,tp.siteurl
        FROM `#@__arctiny` arc
        LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid
        LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype
        WHERE arc.id='$aid' ";
    $trow = $dsql->GetOne($query);
    $trow['maintable'] = (trim($trow['maintable']) == '' ? '#@__archives' : trim($trow['maintable']));
    if ($trow['issystem'] != -1) {
        $arcQuery = "SELECT arc.*,tp.typedir,tp.typename,tp.corank,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.sitepath,tp.siteurl
            FROM `{$trow['maintable']}` arc LEFT JOIN `#@__arctype` tp on arc.typeid=tp.id
            LEFT JOIN `#@__channeltype` ch on ch.id=arc.channel WHERE arc.id='$aid' ";
        $arcRow = $dsql->GetOne($arcQuery);
        PutCookie('DedeUserID', $arcRow['mid'], 1800);
        PutCookie('DedeLoginTime', time(), 1800);
        if ($arcRow['ismake'] == -1 || $arcRow['corank'] != 0 || $arcRow['arcrank'] != 0 || ($arcRow['typeid'] == 0 && $arcRow['channel'] != -1) || $arcRow['money'] > 0) {
            echo "<script>location.href='{$cfg_phpurl}/view.php?aid={$aid}';</script>";
            exit();
        }
    } else {
        $arcRow['id'] = $aid;
        $arcRow['typeid'] = $trow['typeid'];
        $arcRow['senddate'] = $trow['senddate'];
        $arcRow['title'] = '';
        $arcRow['ismake'] = 1;
        $arcRow['arcrank'] = $trow['corank'];
        $arcRow['namerule'] = $trow['namerule'];
        $arcRow['typedir'] = $trow['typedir'];
        $arcRow['money'] = 0;
        $arcRow['filename'] = '';
        $arcRow['moresite'] = $trow['moresite'];
        $arcRow['siteurl'] = $trow['siteurl'];
        $arcRow['sitepath'] = $trow['sitepath'];
    }
    $arcurl  = GetFileUrl(
        $arcRow['id'],
        $arcRow['typeid'],
        $arcRow['senddate'],
        $arcRow['title'],
        $arcRow['ismake'],
        $arcRow['arcrank'],
        $arcRow['namerule'],
        $arcRow['typedir'],
        $arcRow['money'],
        $arcRow['filename'],
        $arcRow['moresite'],
        $arcRow['siteurl'],
        $arcRow['sitepath']
    );
    $arcfile = GetFileUrl(
        $arcRow['id'],
        $arcRow['typeid'],
        $arcRow['senddate'],
        $arcRow['title'],
        $arcRow['ismake'],
        $arcRow['arcrank'],
        $arcRow['namerule'],
        $arcRow['typedir'],
        $arcRow['money'],
        $arcRow['filename']
    );
    if (preg_match("#^http:#", $arcfile)) {
        $arcfile = preg_replace("#^http:\/\/([^\/]*)\/#i", '/', $arcfile);
    }
    $truefile = GetTruePath().$arcfile;
    if (!file_exists($truefile)) {
        MakeArt($aid, TRUE);
    }
    echo "<script>location.href='$arcurl"."?".time()."';</script>";
    exit();
}
/*--------------------------
//异步上传缩略图
function uploadLitpic(){ }
---------------------------*/
else if ($dopost == "uploadLitpic") {
    $upfile = AdminUpload('litpic', 'imagelit', 0, false);
    if ($upfile == '-1') {
        $msg = "<script>
                parent.document.getElementById('uploadwait').style.display = 'none';
                alert('您没指定要上传文件或文件大小超过限制');
            </script>";
    } else if ($upfile == '-2') {
        $msg = "<script>
                parent.document.getElementById('uploadwait').style.display = 'none';
                alert('上传文件失败，请检查原因');
            </script>";
    } else if ($upfile == '0') {
        $msg = "<script>
                parent.document.getElementById('uploadwait').style.display = 'none';
                alert('文件类型不正确');
            </script>";
    } else {
        if (!empty($cfg_uplitpic_cut) && $cfg_uplitpic_cut == 'N') {
            $msg = "<script>
                    parent.document.getElementById('uploadwait').style.display = 'none';
                    parent.document.getElementById('picname').value = '{$upfile}';
                    if(parent.document.getElementById('divpicview'))
                    {
                        parent.document.getElementById('divpicview').style.width = '150px';
                        parent.document.getElementById('divpicview').innerHTML = \"<img src='{$upfile}?n' width='150'>\";
                    }
                </script>";
        } else {
            $msg = "<script>
                    parent.document.getElementById('uploadwait').style.display = 'none';
                    window.open('imagecut.php?f=picname&isupload=yes&file={$upfile}', 'popUpImagesWin', 'scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=150, top=50');
                </script>";
        }
    }
    echo $msg;
    exit();
}
/*--------------------------
//推荐文档
function commendArchives(){ }
---------------------------*/
else if ($dopost == "commendArchives") {
    CheckPurview('a_Commend,sys_ArcBatch');
    if (!empty($aid) && empty($qstr)) {
        $qstr = $aid;
    }
    if ($qstr == '') {
        ShowMsg("参数无效", $ENV_GOBACK_URL);
        exit();
    }
    $arcids = preg_replace("#[^0-9,]#", '', preg_replace("#`#", ',', $qstr));
    $query = "SELECT arc.id,arc.typeid,ch.issystem,ch.maintable,ch.addtable FROM `#@__arctiny` arc
        LEFT JOIN `#@__arctype` tp on tp.id=arc.typeid
        LEFT JOIN `#@__channeltype` ch on ch.id=tp.channeltype
        WHERE arc.id in($arcids) ";
    $dsql->SetQuery($query);
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $aid = $row['id'];
        if ($row['issystem'] != -1) {
            $maintable = (trim($row['maintable']) == '' ? '#@__archives' : trim($row['maintable']));
            $arr = $dsql->GetOne("SELECT flag FROM `{$maintable}` WHERE id='$aid' ");
            $flag = ($arr['flag'] == '' ? 'c' : $arr['flag'].',c');
            $dsql->ExecuteNoneQuery(" UPDATE `{$maintable}` SET `flag`='$flag' WHERE id='{$aid}' ");
        } else {
            $maintable = trim($row['addtable']);
            $arr = $dsql->GetOne("SELECT flag From `{$maintable}` where aid='$aid' ");
            $flag = ($arr['flag'] == '' ? 'c' : $arr['flag'].',c');
            $dsql->ExecuteNoneQuery(" UPDATE `{$maintable}` SET `flag`='$flag' WHERE aid='{$aid}' ");
        }
    }
    ShowMsg("成功把所选文档设为推荐", $ENV_GOBACK_URL);
    exit();
}
/*--------------------------
//生成HTML
function makeArchives();
---------------------------*/
else if ($dopost == "makeArchives") {
    CheckPurview('sys_MakeHtml,sys_ArcBatch');
    if (!empty($aid) && empty($qstr)) $qstr = $aid;
    if ($qstr == '') {
        ShowMsg('参数无效', $ENV_GOBACK_URL);
        exit();
    }
    require_once(DEDEADMIN.'/inc/inc_archives_functions.php');
    $qstrs = explode('`', $qstr);
    $i = 0;
    foreach ($qstrs as $aid) {
        $i++;
        $pageurl = MakeArt($aid, false);
    }
    ShowMsg("成功更新指定 $i 个文件", $ENV_GOBACK_URL);
    exit();
}
/*--------------------------
//审核文档
function checkArchives() {   }
---------------------------*/
else if ($dopost == "checkArchives") {
    CheckPurview('a_Check,a_AccCheck,sys_ArcBatch');
    require_once(DEDEADMIN."/inc/inc_archives_functions.php");
    if (!empty($aid) && empty($qstr)) $qstr = $aid;
    if ($qstr == '') {
        ShowMsg("参数无效", $ENV_GOBACK_URL);
        exit();
    }
    $arcids = preg_replace("#[^0-9,]#", '', preg_replace("#`#", ',', $qstr));
    $query = "SELECT arc.id,arc.typeid,ch.issystem,ch.maintable,ch.addtable FROM `#@__arctiny` arc
        LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid
        LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype
        WHERE arc.id in($arcids) ";
    $dsql->SetQuery($query);
    $dsql->Execute('ckall');
    while ($row = $dsql->GetArray('ckall')) {
        $aid = $row['id'];
        $maintable = (trim($row['maintable']) == '' ? '#@__archives' : trim($row['maintable']));
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctiny` SET arcrank='0' WHERE id='$aid' ");
        if ($row['issystem'] == -1) {
            $dsql->ExecuteNoneQuery("UPDATE `".trim($row['addtable'])."` SET arcrank='0' WHERE aid='$aid' ");
        } else {
            $dsql->ExecuteNoneQuery("UPDATE `$maintable` SET arcrank='0', dutyadmin='".$cuserLogin->getUserID()."' WHERE id='$aid' ");
        }
        $dsql->ExecuteNoneQuery("UPDATE `#@__taglist` SET arcrank='0' WHERE aid='$aid' ");
        $pageurl = MakeArt($aid, false);
    }
    ShowMsg("成功审核指定文档", $ENV_GOBACK_URL);
    exit();
}
/*-----------------------------
function moveArchives(){ }
------------------------------*/
else if ($dopost == 'moveArchives') {
    CheckPurview('sys_ArcBatch');
    if (empty($totype)) {
        require_once(DEDEINC.'/typelink/typelink.class.php');
        if (!empty($aid) && empty($qstr)) $qstr = $aid;
        AjaxHead();
        $channelid = empty($channelid) ? 0 : $channelid;
        $tl = new TypeLink($aid);
        $typeOptions = $tl->GetOptionArray(0, $admin_catalogs, $channelid);
        $typeOptions = "<select name='totype' style='width:320px'>
        <option value='0'>请选择移动位置</option>
        $typeOptions
        </select>";
        //输出AJAX可移动窗体
        $divname = 'moveArchives';
        echo "<div class='title' onmousemove=\"DropMoveHand('{$divname}', 260);\" onmousedown=\"DropStartHand();\" onmouseup=\"DropStopHand();\">移动文档</div>";
        echo "<form name='quickeditform' action='archives_do.php' method='post'>";
        echo "<input type='hidden' name='dopost' value='{$dopost}'>";
        echo "<input type='hidden' name='qstr' value='{$qstr}'>";
        echo "<table width='100%' style='display:inline-block;z-index:9000'>";
        ?>
        <tr>
            <td width="90" class="bline">&nbsp;目标栏目：</td>
            <td class="bline"><?php echo $typeOptions; ?></td>
        </tr>
        <tr>
            <td width="90" class="bline">&nbsp;文档ID：</td>
            <td class="bline">
                <input type="text" name="tmpids" value="<?php echo $qstr; ?>" style="width:320px;overflow:hidden">
                <br>移动到目标栏目必须和选定文档频道类型一致，否则程序会自动勿略不符合文档
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" class="py-3">
                <button type="submit" class="btn btn-success btn-sm">保存</button>
                <button type="button" onclick="HideObj('<?php echo $divname; ?>');ChangeFullDiv('hide');" class="btn btn-success btn-sm">关闭</button>
            </td>
        </tr>
        </table>
        </form>
        <?php
        //AJAX窗体结束
        } else {
        $totype = preg_replace("#[^0-9]#", '', $totype);
        $typeInfos = $dsql->GetOne("SELECT tp.channeltype,tp.ispart,tp.channeltype,ch.maintable,ch.addtable,ch.issystem FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch on ch.id=tp.channeltype WHERE tp.id='$totype' ");
        $idtype = "id";
        if (!is_array($typeInfos)) {
            ShowMsg('参数错误', '-1');
            exit();
        }
        if ($typeInfos['ispart'] != 0) {
            ShowMsg('文档保存栏目必须为最终列表栏目', '-1');
            exit();
        }
        if (empty($typeInfos['addtable'])) {
            $typeInfos['maintable'] = '#@__archives';
        }
        //增加单表模型判断
        if ($typeInfos['issystem'] == -1) {
            $typeInfos['maintable'] = $typeInfos['addtable'];
            $idtype = "aid";
        }
        $arcids = preg_replace("#[^0-9,]#", '', preg_replace("#`#", ',', $qstr));
        $arc = '';
        $j = 0;
        $okids = array();
        $dsql->SetQuery("SELECT {$idtype},typeid FROM `{$typeInfos['maintable']}` WHERE {$idtype} in($arcids) AND channel='{$typeInfos['channeltype']}' ");
        $dsql->Execute();
        while ($row = $dsql->GetArray()) {
            if ($row['typeid'] != $totype) {
                $dsql->ExecuteNoneQuery("UPDATE `#@__arctiny`  SET typeid='$totype' WHERE id='{$row[$idtype]}' ");
                $dsql->ExecuteNoneQuery("UPDATE `{$typeInfos['maintable']}` SET typeid='$totype' WHERE id='{$row[$idtype]}' ");
                $dsql->ExecuteNoneQuery("UPDATE `{$typeInfos['addtable']}` SET typeid='$totype' WHERE aid='{$row[$idtype]}' ");
                $okids[] = $row[$idtype];
                $j++;
            }
        }
        //更新HTML
        foreach ($okids as $aid) {
            $arc = new Archives($aid);
            $arc->MakeHtml();
        }
        ShowMsg("成功移动 $j 个文档", $ENV_GOBACK_URL);
        exit();
    }
}
/*-----------------------------
//还原文档
function RbReturnArchives(){ }
------------------------------*/
else if ($dopost == 'return') {
    CheckPurview('a_Del,a_AccDel,a_MyDel,sys_ArcBatch');
    require_once(DEDEINC."/libraries/oxwindow.class.php");

    if (!empty($aid) && empty($qstr)) $qstr = $aid;

    if ($qstr == '') {
        ShowMsg("参数无效", "recycling.php");
        exit();
    }
    $qstrs = explode('`', $qstr);
    foreach ($qstrs as $aid) {
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET arcrank='-1',ismake='0' WHERE id='$aid'");
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctiny` SET `arcrank` = '-1' WHERE id = '$aid'; ");
    }
    ShowMsg("成功还原指定文档", "recycling.php");
    exit();
}
/*--------------------------
//删除文档
function delArchives(){ }
---------------------------*/
else if ($dopost == "delArchives") {
    CheckPurview('a_Del,a_AccDel,a_MyDel,sys_ArcBatch');
    require_once(DEDEINC."/libraries/oxwindow.class.php");
    if (empty($fmdo)) $fmdo = '';
    if ($fmdo == 'yes') {
        if (!empty($aid) && empty($qstr)) {
            $qstr = $aid;
        }
        if ($qstr == '') {
            ShowMsg("参数无效", $ENV_GOBACK_URL);
            exit();
        }
        $qstrs = explode('`', $qstr);
        $okaids = array();
        foreach ($qstrs as $aid) {
            if (!isset($okaids[$aid])) {
                DelArc($aid);
            } else {
                $okaids[$aid] = 1;
            }
        }
        ShowMsg("成功删除指定文档", $ENV_GOBACK_URL);
        exit();
    } else {
        $wintitle = "文档管理-删除文档";
        $wecome_info = "<a href='".$ENV_GOBACK_URL."'>文档管理</a>::删除文档";
        $win = new OxWindow();
        $win->Init("archives_do.php", "js/blank.js", "POST");
        $win->AddHidden("fmdo", "yes");
        $win->AddHidden("dopost", $dopost);
        $win->AddHidden("qstr", $qstr);
        $win->AddHidden("aid", $aid);
        $win->AddTitle("您确定删除，序号 $qstr 文档");
        $winform = $win->GetWindow("ok");
        $win->Display();
    }
}
/*-----------------------------
//清空文档
function RbClearArchives(){ }
------------------------------*/
else if ($dopost == 'clear') {
    CheckPurview('a_Del,a_AccDel,a_MyDel,sys_ArcBatch');
    require_once(DEDEINC."/libraries/oxwindow.class.php");
    if (empty($fmdo)) $fmdo = '';
    $recycle = empty($recycle) ? "" : $recycle;
    if ($fmdo == 'yes') {
        if (!empty($aid) && empty($qstr)) $qstr = $aid;
        if ($qstr == '') {
            ShowMsg("参数无效", "recycling.php");
            exit();
        }
        $qstrs = explode('`', $qstr);
        $okaids = array();
        foreach ($qstrs as $qstr) {
            if (!isset($okaids[$qstr])) {
                DelArc($qstr, "OK", FALSE, $recycle);
                $okaids[$qstr] = $qstr;
            } else {
                $okaids[$qstr] = 1;
            }
        }
        ShowMsg("成功删除指定文档", "recycling.php");
        exit();
    } else {
        $dsql->SetQuery("SELECT id FROM `#@__archives` WHERE `arcrank` = '-2'");
        $dsql->Execute();
        $qstr = '';
        while ($row = $dsql->GetArray()) {
            $qstr .= $row['id'].'`';
            $aid = $row['id'];
        }
        $num = $dsql->GetTotalRow();
        if (empty($num)) {
            ShowMsg("对不起，未发现相关文档", "recycling.php");
            exit();
        }
        $wintitle = "文档管理-清空所有文档";
        $wecome_info = "<a href='recycling.php'>文档回收站</a>::清空所有文档";
        $win = new OxWindow();
        $win->Init("archives_do.php", "js/blank.js", "POST");
        $win->AddHidden("fmdo", "yes");
        $win->AddHidden("dopost", $dopost);
        $win->AddHidden("qstr", $qstr);
        $win->AddHidden("aid", $aid);
        $win->AddHidden("recycle", $recycle);
        $win->AddTitle("回收站将清空 $num 篇文档<br>您确定删除，序号 $qstr 文档");
        $winform = $win->GetWindow("ok");
        $win->Display();
    }
}
/*-----------------------------
//清除文档
function RbDelArchives(){ }
------------------------------*/
else if ($dopost == 'del') {
    CheckPurview('a_Del,a_AccDel,a_MyDel,sys_ArcBatch');
    require_once(DEDEINC."/libraries/oxwindow.class.php");
    if (empty($fmdo)) $fmdo = '';
    $recycle = empty($recycle) ? "" : $recycle;
    if ($fmdo == 'yes') {
        if (!empty($aid) && empty($qstr)) $qstr = $aid;
        if ($qstr == '') {
            ShowMsg("参数无效", "recycling.php");
            exit();
        }
        $qstrs = explode('`', $qstr);
        $okaids = array();
        foreach ($qstrs as $aid) {
            if (!isset($okaids[$aid])) {
                DelArc($aid, "OK", "", $recycle);
            } else {
                $okaids[$aid] = 1;
            }
        }
        ShowMsg("成功删除指定文档", "recycling.php");
        exit();
    } else {
        $wintitle = "文档管理-删除文档";
        $wecome_info = "<a href='recycling.php'>文档管理</a>::删除文档";
        $win = new OxWindow();
        $win->Init("archives_do.php", "js/blank.js", "POST");
        $win->AddHidden("fmdo", "yes");
        $win->AddHidden("dopost", $dopost);
        $win->AddHidden("qstr", $qstr);
        $win->AddHidden("aid", $aid);
        $win->AddHidden("recycle", $recycle);
        $win->AddTitle("您确要删除，序号 $qstr 文档");
        $winform = $win->GetWindow("ok");
        $win->Display();
    }
}
/*-----------------------------
//快速编辑
function quickEdit(){ }
------------------------------*/
else if ($dopost == 'quickEdit') {
    require_once(DEDEADMIN."/inc/inc_catalog_options.php");
    AjaxHead();
    $query = "SELECT ch.typename as channelname,ch.addtable,ar.membername as rankname,arc.*
    FROM `#@__archives` arc
    LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel
    LEFT JOIN `#@__arcrank` ar ON ar.`rank`=arc.arcrank WHERE arc.id='$aid' ";
    $arcRow = $dsql->GetOne($query);
    $divname = 'quickEdit';
    echo "<div class='title' onmousemove=\"DropMoveHand('{$divname}', 260);\" onmousedown=\"DropStartHand();\" onmouseup=\"DropStopHand();\">快速属性编辑</div>";
    echo "<form name='quickeditform' action='archives_do.php?dopost=quickEditSave&aid={$aid}' method='post'>";
    echo "<input type='hidden' name='addtable' value='{$arcRow['addtable']}'>";
    echo "<input type='hidden' name='oldtypeid' value='{$arcRow['typeid']}'>";
    echo "<table width='100%' style='display:inline-block;z-index:9000'>";
    ?>
    <tr>
        <td width="90" class="bline">&nbsp;所属栏目：</td>
        <td class="bline">
            <?php
            $typeOptions = GetOptionList($arcRow['typeid'], $cuserLogin->getUserChannel(), $arcRow['channel']);
            echo "<select name='typeid' style='width:320px'>";
            if ($arcRow["typeid"] == "0") echo "<option value='0' selected>请选择栏目</option>";
            echo $typeOptions;
            echo "</select>";
            ?>
        </td>
    </tr>
    <tr>
        <td width="90" class="bline">&nbsp;属性：</td>
        <td class="bline">
            <input type="hidden" name="oldflag" value="<?php echo $arcRow['flag']; ?>">
            <?php
            $dsql->SetQuery("SELECT * FROM `#@__arcatt` ORDER BY sortid ASC");
            $dsql->Execute();
            while ($trow = $dsql->GetObject()) {
                if ($trow->att == 'j' || $trow->att == 'p') continue;
                if (preg_match("#".$trow->att."#", $arcRow['flag']))
                echo "<label><input class='np' type='checkbox' name='flags[]' id='flags{$trow->att}' value='{$trow->att}' checked='checked'>&nbsp;{$trow->attname}{$trow->att}[{$trow->att}]</label>&nbsp;";
                else
                echo "<label><input class='np' type='checkbox' name='flags[]' id='flags{$trow->att}' value='{$trow->att}'>&nbsp;{$trow->attname}[{$trow->att}]</label>&nbsp;";
            }
            ?>
        </td>
    </tr>
    <tr>
        <td width="90" class="bline">&nbsp;标题：</td>
        <td class="bline"><input name="title" type="text" id="title" value="<?php echo $arcRow['title']; ?>" style="width:320px"></td>
    </tr>
    <tr>
        <td width="90" class="bline">&nbsp;简略标题：</td>
        <td class="bline"><input name="shorttitle" type="text" id="shorttitle" value="<?php echo $arcRow['shorttitle']; ?>" style="width:320px"></td>
    </tr>
    <tr>
        <td width="90" class="bline">&nbsp;阅读权限：</td>
        <td class="bline">
            <select name="arcrank" id="arcrank" style="width:100px">
                <option value='<?php echo $arcRow["arcrank"] ?>'>
                <?php echo $arcRow["rankname"] ?> </option>
                <?php
                $urank = $cuserLogin->getUserRank();
                $dsql->SetQuery("SELECT * FROM `#@__arcrank` WHERE adminrank<='$urank'");
                $dsql->Execute();
                while ($row = $dsql->GetObject()) {
                    echo "<option value='".$row->rank."'>".$row->membername."</option>";
                }
                ?>
            </select>
            需要金币：<input name="money" type="text" id="money" value="<?php echo $arcRow["money"]; ?>" style="width:100px">
        </td>
    </tr>
    <tr>
        <td width="90" class="bline">&nbsp;关键词：</td>
        <td class="bline"><input name="keywords" type="text" id="keywords" value="<?php echo $arcRow['keywords']; ?>" style="width:320px"></td>
    </tr>
    <tr>
        <td colspan="2" align="center" class="py-3">
            <button type="submit" class="btn btn-success btn-sm">保存</button>
            <button type="button" onclick="HideObj('<?php echo $divname; ?>');ChangeFullDiv('hide');" class="btn btn-success btn-sm">关闭</button>
        </td>
    </tr>
    </table>
    </form>
<?php
//AJAX窗体结束
}
/*-----------------------------
//保存快速编辑内容
function quickEditSave(){ }
------------------------------*/
else if ($dopost == 'quickEditSave') {
    require_once(DEDEADMIN.'/inc/inc_archives_functions.php');
    //权限检测
    if (!TestPurview('a_Edit')) {
        if (TestPurview('a_AccEdit')) {
            CheckCatalog($typeid, "对不起，您没有操作栏目 {$typeid} 文档权限");
        } else {
            CheckArcAdmin($aid, $cuserLogin->getUserID());
        }
    }
    $title = dede_htmlspecialchars(cn_substrR($title, $cfg_title_maxlen));
    $shorttitle = cn_substrR($shorttitle, 36);
    $keywords = trim(cn_substrR($keywords, 60));
    if (!TestPurview('a_Check,a_AccCheck,a_MyCheck')) $arcrank = -1;
    $adminid = $cuserLogin->getUserID();
    //属性处理
    $flag = isset($flags) ? join(',', $flags) : '';
    if (!empty($flag)) {
        if (preg_match("#p#", $oldflag)) $flag .= ',p';
        if (preg_match("#j#", $oldflag)) $flag .= ',j';
    }
    $query = "UPDATE `#@__archives` SET
    typeid = '$typeid',
    flag = '$flag',
    arcrank = '$arcrank',
    money = '$money',
    title = '$title', 
    shorttitle = '$shorttitle',
    keywords = '$keywords',
    dutyadmin = '$adminid'
    WHERE id = '$aid'; ";
    //更新主表
    $dsql->ExecuteNoneQuery($query);
    //更新微表
    $dsql->ExecuteNoneQuery(" UPDATE `#@__arctiny` SET typeid='$typeid',arcrank='$arcrank' WHERE id='$aid' ");
    //更新附加表
    if ($typeid != $oldtypeid) {
        $addtable = trim($addtable);
        if (empty($addtable)) $addtable = '#@__addonarticle';
        else $addtable = preg_replace("#[^a-z0-9__#@-]#i", "", $addtable);
        $dsql->ExecuteNoneQuery(" UPDATE `$addtable` SET typeid='$typeid' WHERE aid='$aid' ");
    }
    //更新HTML
    $artUrl = MakeArt($aid, TRUE, TRUE);
    $backurl = !empty($_COOKIE['ENV_GOBACK_URL']) ? $_COOKIE['ENV_GOBACK_URL'] : '-1';
    ShowMsg('成功更新一篇文档基本信息', $backurl);
    exit();
}
/*--------------------------
分析并自动获取文档关键词
function makekw(){ }
--------------------------*/
else if ($dopost == "makekw") {
    CheckPurview('a_Commend,sys_ArcBatch');
    if (!empty($aid) && empty($qstr)) $qstr = $aid;
    if ($qstr == '') {
        ShowMsg("参数无效", $ENV_GOBACK_URL);
        exit();
    }
    $arcids = preg_replace("#[^0-9,]#", '', preg_replace("#`#", ',', $qstr));
    $query = "SELECT arc.*, addt.* From `#@__archives` arc LEFT JOIN `#@__addonarticle` addt ON addt.aid=arc.id  WHERE arc.id in($arcids) AND arc.channel=1 ";
    $dsql->SetQuery($query);
    $dsql->Execute();
    if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
        $client = new DedeBizClient($cfg_bizcore_hostname, $cfg_bizcore_port);
        $client->appid = $cfg_bizcore_appid;
        $client->key = $cfg_bizcore_key;
        while ($row = $dsql->GetArray()) {
            //跳过已经有关键词内容
            if (trim($row['keywords']) != '') continue;
            $aid = $row['id'];
            $keywords = '';
            $title = $row['title'];
            $description = $row['description'];
            $body = cn_substr($row['body'], 5000);
            $data = $client->Spliteword($title.Html2Text($body));
            $keywords = $data->data;
            $description = str_replace('　', ' ', trim($description));
            $description = str_replace('［', ' ', $description);
            $description = str_replace('］', ' ', $description);
            $description = preg_replace("#[ \t]{1,}#is", ' ', $description);
            $description = str_replace('关键词', '', $description);
            $description = str_replace('关键词', '', $description);
            $description = addslashes($description);
            $dsql->ExecuteNoneQuery(" UPDATE `#@__archives` SET `keywords`='$keywords',`description`='$description'  WHERE id='{$aid}' ");
        }
        $client->Close();
    } else {
        include_once(DEDEINC.'/libraries/splitword.class.php');
        $sp = new SplitWord($cfg_soft_lang, $cfg_soft_lang);
        while ($row = $dsql->GetArray()) {
            //跳过已经有关键词内容
            if (trim($row['keywords']) != '') continue;
            $aid = $row['id'];
            $keywords = '';
            $title = $row['title'];
            $description = $row['description'];
            $body = cn_substr($row['body'], 5000);
            $sp->SetSource($title, $cfg_soft_lang, $cfg_soft_lang);
            $sp->StartAnalysis();
            $titleindexs = preg_replace("/#p#|#e#/", '', $sp->GetFinallyIndex());
            $sp->SetSource(Html2Text($body), $cfg_soft_lang, $cfg_soft_lang);
            $sp->StartAnalysis();
            $allindexs = preg_replace("/#p#|#e#/", '', $sp->GetFinallyIndex());
            if (is_array($allindexs) && is_array($titleindexs)) {
                foreach ($titleindexs as $k => $v) {
                    if (strlen($keywords.$k) >= 60) {
                        break;
                    } else {
                        if (strlen($k) <= 2) continue;
                        $keywords .= $k.',';
                    }
                }
                foreach ($allindexs as $k => $v) {
                    if (strlen($keywords.$k) >= 60) {
                        break;
                    } else if (!in_array($k, $titleindexs)) {
                        if (strlen($k) <= 2) continue;
                        $keywords .= $k.',';
                    }
                }
            }
            $description = str_replace('　', ' ', trim($description));
            $description = str_replace('［', ' ', $description);
            $description = str_replace('］', ' ', $description);
            $description = preg_replace("#[ \t]{1,}#is", ' ', $description);
            $description = str_replace('关键词', '', $description);
            $description = str_replace('关键词', '', $description);
            $description = addslashes($description);
            $dsql->ExecuteNoneQuery(" UPDATE `#@__archives` SET `keywords`='$keywords',`description`='$description'  WHERE id='{$aid}' ");
        }
        $sp = null;
    }
    ShowMsg("成功分析指定文档关键词", $ENV_GOBACK_URL);
    exit();
}
/*--------------------------
//批量增加属性
function attsAdd(){ }
---------------------------*/
else if ($dopost == 'attsAdd') {
    CheckPurview('a_Commend,sys_ArcBatch');
    if (!empty($aid) && empty($qstr)) $qstr = $aid;
    if ($qstr == '') {
        ShowMsg("参数无效", $ENV_GOBACK_URL);
        exit();
    }
    if (empty($flagname)) {
        ShowMsg("必须指定要添加属性", $ENV_GOBACK_URL);
        exit();
    }
    $arcids = preg_replace("#[^0-9,]#", '', preg_replace("#`#", ',', $qstr));
    $query = "SELECT arc.id,arc.typeid,ch.issystem,ch.maintable,ch.addtable FROM `#@__arctiny` arc
        LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid
        LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype
        WHERE arc.id in($arcids) ";
    $dsql->SetQuery($query);
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $aid = $row['id'];
        if ($row['issystem'] != -1) {
            $maintable = (trim($row['maintable']) == '' ? '#@__archives' : trim($row['maintable']));
            $arr = $dsql->GetOne("SELECT flag FROM `{$maintable}` WHERE id='$aid' ");
            $flag = ($arr['flag'] == '' ? $flagname : $arr['flag'].','.$flagname);
            $dsql->ExecuteNoneQuery(" UPDATE `{$maintable}` SET `flag`='$flag' WHERE id='{$aid}' ");
        } else {
            $maintable = trim($row['addtable']);
            $arr = $dsql->GetOne("SELECT flag FROM `{$maintable}` WHERE aid='$aid' ");
            $flag = ($arr['flag'] == '' ? $flagname : $arr['flag'].','.$flagname);
            $dsql->ExecuteNoneQuery(" UPDATE `{$maintable}` SET `flag`='$flag' WHERE aid='{$aid}' ");
        }
    }
    ShowMsg("成功对选中文档增加指定属性", $ENV_GOBACK_URL);
    exit();
}
/*--------------------------
//批量删除属性
function attsDel(){ }
---------------------------*/
else if ($dopost == 'attsDel') {
    CheckPurview('a_Commend,sys_ArcBatch');
    if (!empty($aid) && empty($qstr)) $qstr = $aid;
    if ($qstr == '') {
        ShowMsg("参数无效", $ENV_GOBACK_URL);
        exit();
    }
    if (empty($flagname)) {
        ShowMsg("必须指定要删除属性", $ENV_GOBACK_URL);
        exit();
    }
    $arcids = preg_replace("#[^0-9,]#", '', preg_replace("#`#", ',', $qstr));
    $query = "SELECT arc.id,arc.typeid,ch.issystem,ch.maintable,ch.addtable FROM `#@__arctiny` arc
        LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid
        LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype
        WHERE arc.id in($arcids) ";
    $dsql->SetQuery($query);
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $aid = $row['id'];
        if ($row['issystem'] != -1) {
            $idname = 'id';
            $maintable = (trim($row['maintable']) == '' ? '#@__archives' : trim($row['maintable']));
            $arr = $dsql->GetOne("SELECT flag FROM `{$maintable}` WHERE id='$aid' ");
        } else {
            $idname = 'aid';
            $maintable = trim($row['addtable']);
            $arr = $dsql->GetOne("SELECT flag FROM `{$maintable}` WHERE aid='$aid' ");
        }
        $flag = $arr['flag'];
        if (trim($flag) == '' || !preg_match("#".$flagname."#", $flag)) {
            continue;
        } else {
            $flags  = explode(',', $flag);
            $okflags = array();
            foreach ($flags as $f) {
                if ($f != $flagname) $okflags[] = $f;
            }
        }
        $flag = trim(join(',', $okflags));
        $dsql->ExecuteNoneQuery(" UPDATE `{$maintable}` SET `flag`='$flag' WHERE {$idname}='{$aid}' ");
    }
    ShowMsg("成功对选中文档删除指定属性", $ENV_GOBACK_URL);
    exit();
}
/*--------------------------
//获得批量属性处理AJAX窗体
function attsDlg(){ }
---------------------------*/
else if ($dopost == 'attsDlg') {
    if (!empty($aid) && empty($qstr)) $qstr = $aid;
    $dojobname = ($dojob == 'attsDel' ? '批量删除属性' : '批量增加属性');
    AjaxHead();
    //输出AJAX可移动窗体
    $divname = 'attsDlg';
    echo "<div class='title' onmousemove=\"DropMoveHand('{$divname}', 260);\" onmousedown=\"DropStartHand();\" onmouseup=\"DropStopHand();\">{$dojobname}</div>";
    echo "<form name='quickeditform' action='archives_do.php' method='post'>";
    echo "<input type='hidden' name='dopost' value='{$dojob}'>";
    echo "<input type='hidden' name='qstr' value='{$qstr}'>";
    echo "<table width='100%' style='display:inline-block;z-index:9000'>";
?>
    <tr>
        <td width="90" class="bline">&nbsp;属性：</td>
        <td class="bline">
            <input type="hidden" name="oldflag" value="<?php echo $arcRow['flag']; ?>">
            <?php
            $dsql->SetQuery("SELECT * FROM `#@__arcatt` ORDER BY sortid ASC");
            $dsql->Execute();
            while ($trow = $dsql->GetObject()) {
                if ($trow->att == 'j' || $trow->att == 'p') continue;
                echo "<label><input class='np' type='radio' name='flagname' id='flags{$trow->att}' value='{$trow->att}'>&nbsp;{$trow->attname}[{$trow->att}]</label>&nbsp;";
            }
            ?>
        </td>
    </tr>
    <tr>
        <td width="90" class="bline">&nbsp;文档ID：</td>
        <td class="bline"><input type="text" name="tmpids" value="<?php echo $qstr; ?>" style="width:320px;overflow:hidden"></td>
    </tr>
    <tr>
        <td colspan="2" align="center" class="py-3">
            <button type="submit" class="btn btn-success btn-sm">保存</button>
            <button type="button" onclick="HideObj('<?php echo $divname; ?>');ChangeFullDiv('hide');" class="btn btn-success btn-sm">关闭</button>
        </td>
    </tr>
    </table>
    </form>
<?php
//AJAX窗体结束
}
/*------------------------
function getCatMap() {  }
-------------------------*/
else if ($dopost == 'getCatMap') {
    require_once(DEDEINC.'/typelink/typeunit.class.selector.php');
    AjaxHead();
    //输出AJAX可移动窗体
    $divname = 'getCatMap';
    echo "<div class='title' style='cursor:default'>栏目快速选择器</div>";
    $tus = new TypeUnitSelector();
    ?>
    <form name="quicksel" action="javascript:;" method="get">
        <div class="quicksel"><?php $tus->ListAllType($channelid); ?></div>
        <div align="center" class="quickselfoot">
            <button onclick="getSelCat('<?php echo $targetid; ?>');" class="btn btn-success btn-sm">保存</button>
            <button type="button" onclick="HideObj('<?php echo $divname; ?>');ChangeFullDiv('hide');" class="btn btn-success btn-sm">关闭</button>
        </div>
    </form>
<?php
//AJAX窗体结束
}
?>