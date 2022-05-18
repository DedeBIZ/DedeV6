<?php
/**
 * 栏目编辑
 *
 * @version        $Id: catalog_edit.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/typelink/typelink.class.php");
if (empty($dopost)) $dopost = '';
$id = isset($id) ? intval($id) : 0;
//检查权限许可
CheckPurview('t_Edit,t_AccEdit');
//检查栏目操作许可
CheckCatalog($id, '您无权修改本栏目');
/*-----------------------
function action_save()
----------------------*/
if ($dopost == "save") {
    $description = Html2Text($description, 1);
    $keywords = Html2Text($keywords, 1);
    $uptopsql = $smalltypes = '';
    if (isset($smalltype) && is_array($smalltype)) $smalltypes = join(',', $smalltype);
    if ($topid == 0) {
        $sitepath = $typedir;
        $uptopsql = " ,siteurl='$siteurl',sitepath='$sitepath',ishidden='$ishidden' ";
    }
    if ($ispart != 0) $cross = 0;
    $upquery = "UPDATE `#@__arctype` SET
    issend='$issend',
    sortrank='$sortrank',
    typename='$typename',
    cnoverview='$cnoverview',
    enname='$enname',
    enoverview='$enoverview',
    bigpic='$bigpic',
    litimg='$litimg',
    typedir='$typedir',
    isdefault='$isdefault',
    defaultname='$defaultname',
    issend='$issend',
    ishidden='$ishidden',
    channeltype='$channeltype',
    tempindex='$tempindex',
    templist='$templist',
    temparticle='$temparticle',
    namerule='$namerule',
    namerule2='$namerule2',
    ispart='$ispart',
    corank='$corank',
    description='$description',
    keywords='$keywords',
    seotitle='$seotitle',
    moresite='$moresite',
    `cross`='$cross',
    `content`='$content',
    `crossid`='$crossid',
    `smalltypes`='$smalltypes'
    $uptopsql
    WHERE id='$id' ";
    if (!$dsql->ExecuteNoneQuery($upquery)) {
        ShowMsg("保存当前栏目修改时失败，请检查您的输入资料是否存在问题", "-1");
        exit();
    }
    //如果选择子栏目可投稿，更新顶级栏目为可投稿
    if ($topid > 0 && $issend == 1) {
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET issend='$issend' WHERE id='$topid'; ");
    }
    $slinks = " id IN (".GetSonIds($id).")";
    //修改顶级栏目时强制修改下级的多站点支持属性
    if ($topid == 0 && preg_match("#,#", $slinks)) {
        $upquery = "UPDATE `#@__arctype` SET moresite='$moresite', siteurl='$siteurl',sitepath='$sitepath',ishidden='$ishidden' WHERE 1=1 AND $slinks";
        $dsql->ExecuteNoneQuery($upquery);
    }
    //修改子栏目属性
    if (!empty($upnext)) {
    $upquery = "UPDATE `#@__arctype` SET
    issend='$issend',
    defaultname='$defaultname',
    channeltype='$channeltype',
    tempindex='$tempindex',
    templist='$templist',
    temparticle='$temparticle',
    namerule='$namerule',
    namerule2='$namerule2',
    ishidden='$ishidden'
    WHERE 1=1 AND $slinks";
        if (!$dsql->ExecuteNoneQuery($upquery)) {
            ShowMsg("修改当前栏目成功，但修改下级栏目属性时失败", "-1");
            exit();
        }
    }
    UpDateCatCache();
    ShowMsg("成功修改一个分类", "catalog_main.php");
    exit();
} //End Save Action
else if ($dopost == "savetime") {
    $uptopsql = '';
    $slinks = " id IN (".GetSonIds($id).")";
    //顶级栏目二级域名根目录处理
    if ($topid == 0 && $moresite == 1) {
        $sitepath = $typedir;
        $uptopsql = " ,sitepath='$sitepath' ";
        if (preg_match("#,#", $slinks)) {
            $upquery = "UPDATE `#@__arctype` SET sitepath='$sitepath' WHERE $slinks";
            $dsql->ExecuteNoneQuery($upquery);
        }
    }
    //如果选择子栏目可投稿，更新顶级栏目为可投稿
    if ($topid > 0 && $issend == 1) {
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET issend='$issend' WHERE id='$topid'; ");
    }
    $upquery = "UPDATE `#@__arctype` SET
    issend='$issend',
    sortrank='$sortrank',
    typedir='$typedir',
    typename='$typename',
    isdefault='$isdefault',
    defaultname='$defaultname',
    ispart='$ispart',
    corank='$corank' $uptopsql
    WHERE id='$id' ";
    if (!$dsql->ExecuteNoneQuery($upquery)) {
        ShowMsg("保存当前栏目修改时失败，请检查您的输入资料是否存在问题", "-1");
        exit();
    }
    UpDateCatCache();
    ShowMsg("成功修改一个分类", "catalog_main.php");
    exit();
}
//读取栏目信息
$dsql->SetQuery("SELECT tp.*,ch.typename as ctypename FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id=$id");
$myrow = $dsql->GetOne();
$topid = $myrow['topid'];
if ($topid > 0) {
    $toprow = $dsql->GetOne("SELECT moresite,siteurl,sitepath FROM `#@__arctype` WHERE id=$topid");
    foreach ($toprow as $k => $v) {
        if (!preg_match("#[0-9]#", $k)) {
            $myrow[$k] = $v;
        }
    }
}
$myrow['content'] = empty($myrow['content']) ? "&nbsp;" : $myrow['content'];
//读取频道模型信息
$channelid = $myrow['channeltype'];
$dsql->SetQuery("SELECT id,typename,nid FROM `#@__channeltype` WHERE id<>-1 AND isshow=1 ORDER BY id");
$dsql->Execute();
while ($row = $dsql->GetObject()) {
    $channelArray[$row->id]['typename'] = $row->typename;
    $channelArray[$row->id]['nid'] = $row->nid;
    if ($row->id == $channelid) {
        $nid = $row->nid;
    }
}
PutCookie('lastCid', GetTopid($id), 3600 * 24, "/");
if ($dopost == 'time') {
?>
<form name="form1" action="catalog_edit.php" method="post" onSubmit="return checkSubmit();">
    <input type="hidden" name="dopost" value="savetime">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="topid" value="<?php echo $myrow['topid']; ?>">
    <input type="hidden" name="moresite" value="<?php echo $myrow['moresite']; ?>">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="160" class="bline" align="center">是否支持投稿：</td>
            <td class="bline">
                <label><input type="radio" name="issend" value="0" <?php if ($myrow['issend'] == "0") echo "checked='1'"; ?>> 不支持</label>
                <label><input type="radio" name="issend" value="1" <?php if ($myrow['issend'] == "1") echo "checked='1'"; ?>> 支持</label>
            </td>
        </tr>
        <tr>
            <td class="bline" align="center">内容模型：</td>
            <td class="bline">
                <?php
                foreach ($channelArray as $k => $arr) {
                    if ($k == $channelid) echo "{$arr['typename']} | {$arr['nid']}";
                }
                ?>
                <a href='catalog_edit.php?id=<?php echo $id; ?>' class='btn btn-success btn-sm'>更多模型</a>
            </td>
        </tr>
        <tr>
            <td class="bline" align="center">栏目名称：</td>
            <td class="bline"><input name="typename" type="text" id="typename" value="<?php echo $myrow['typename'] ?>" style="width:260px" class="iptxt"></td>
        </tr>
        <tr>
            <td class="bline" align="center"> 排列顺序：</td>
            <td class="bline"> <input name="sortrank" type="text" value="<?php echo $myrow['sortrank'] ?>" style="width:100px" class="iptxt">（由低 &gt; 高）</td>
        </tr>
        <tr>
            <td class="bline" align="center">浏览权限：</td>
            <td class="bline">
                <select name="corank" id="corank" style="width:100px">
                <?php
                $dsql->SetQuery("SELECT * FROM `#@__arcrank` WHERE `rank` >= 0");
                $dsql->Execute();
                while ($row = $dsql->GetObject()) {
                    if ($myrow['corank'] == $row->rank)
                        echo "<option value='".$row->rank."' selected>".$row->membername."</option>\r\n";
                        else
                        echo "<option value='".$row->rank."'>".$row->membername."</option>\r\n";
                }
                ?>
                </select>（仅限制栏目里的文档浏览权限）
            </td>
        </tr>
        <tr>
            <td class="bline" align="center">文件保存目录：</td>
            <td class="bline"><input name="typedir" type="text" id="typedir" value="<?php echo $myrow['typedir'] ?>" style="width:260px"class="iptxt"></td>
        </tr>
        <tr>
            <td align="center" class="bline">栏目列表选项：</td>
            <td class="bline">
                <label><input type="radio" name="isdefault" value="1" <?php if ($myrow['isdefault'] == 1) echo "checked='1'"; ?>> 链接到默认页</label>
                <label><input type="radio" name="isdefault" value="0" <?php if ($myrow['isdefault'] == 0) echo "checked='1'"; ?>> 链接到列表第一页</label>
                <label><input type="radio" name="isdefault" value="-1" <?php if ($myrow['isdefault'] == -1) echo "checked='1'"; ?>> 使用动态页</label>
            </td>
        </tr>
        <tr>
            <td class="bline" align="center">默认页的名称：</td>
            <td class="bline"><input name="defaultname" type="text" value="<?php echo $myrow['defaultname'] ?>" style="width:260px" class="iptxt"></td>
        </tr>
        <tr>
            <td class="bline" align="center">栏目属性：</td>
            <td class="bline">
                <label><input name="ispart" type="radio" id="radio" value="0" <?php if ($myrow['ispart'] == 0) echo "checked='1'"; ?>> 最终列表栏目（允许在本栏目发布文档，并生成文档列表）</label><br>
                <label><input name="ispart" type="radio" id="radio2" value="1" <?php if ($myrow['ispart'] == 1) echo "checked='1'"; ?>> 频道封面（栏目本身不允许发布文档）</label><br>
                <label><input name="ispart" type="radio" id="radio3" value="2" <?php if ($myrow['ispart'] == 2) echo "checked='1'"; ?>> 外部连接（在"文件保存目录"处填写网址）</label>
            </td>
        </tr>
        <tr>
            <td bgcolor="#F8FCF1" colspan="2" align="center" class="py-3">
                <button onclick='getSelCat("<?php echo $targetid; ?>");' class='btn btn-success btn-sm'>保存</button>
                <button type='button' onclick='CloseMsg()' class='btn btn-success btn-sm'>关闭</button>
            </td>
        </tr>
    </table>
</form>
<?php
exit();
} else {
    include DedeInclude('templets/catalog_edit.htm');
}
?>