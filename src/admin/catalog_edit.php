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
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
if (empty($dopost)) $dopost = '';
$id = isset($id) ? intval($id) : 0;
//检查权限许可
UserLogin::CheckPurview('t_Edit,t_AccEdit');
//检查栏目操作许可
UserLogin::CheckCatalog($id, Lang('catalog_err_edit_noperm'));
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
    $upquery = "UPDATE `#@__arctype` SET issend='$issend',sortrank='$sortrank',typename='$typename',cnoverview='$cnoverview',enname='$enname',enoverview='$enoverview',bigpic='$bigpic',litimg='$litimg',typedir='$typedir',isdefault='$isdefault',defaultname='$defaultname',issend='$issend',ishidden='$ishidden',channeltype='$channeltype',tempindex='$tempindex',templist='$templist',temparticle='$temparticle',namerule='$namerule',namerule2='$namerule2',ispart='$ispart',corank='$corank',description='$description',keywords='$keywords',seotitle='$seotitle',moresite='$moresite',`iscross`='$cross',`content`='$content',`crossid`='$crossid',`smalltypes`='$smalltypes' $uptopsql WHERE id='$id'";
    if (!$dsql->ExecuteNoneQuery($upquery)) {
        ShowMsg(Lang("catalog_err_update"), "-1");
        exit();
    }
    //如果选择子栏目可投稿，更新顶级栏目为可投稿
    if ($topid > 0 && $issend == 1) {
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET issend='$issend' WHERE id='$topid';");
    }
    $slinks = " id IN (".GetSonIds($id).")";
    //修改顶级栏目时强制修改下级的多站点支持属性
    if ($topid == 0 && preg_match("#,#", $slinks)) {
        $upquery = "UPDATE `#@__arctype` SET moresite='$moresite', siteurl='$siteurl',sitepath='$sitepath',ishidden='$ishidden' WHERE 1=1 AND $slinks";
        $dsql->ExecuteNoneQuery($upquery);
    }
    //修改子栏目属性
    if (!empty($upnext)) {
    $upquery = "UPDATE `#@__arctype` SET issend='$issend',defaultname='$defaultname',channeltype='$channeltype',tempindex='$tempindex',templist='$templist',temparticle='$temparticle',namerule='$namerule',namerule2='$namerule2',ishidden='$ishidden' WHERE 1=1 AND $slinks";
        if (!$dsql->ExecuteNoneQuery($upquery)) {
            ShowMsg(Lang("catalog_err_update_son"), "-1");
            exit();
        }
    }
    UpDateCatCache();
    ShowMsg(Lang("catalog_success_update"), "catalog_main.php");
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
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET issend='$issend' WHERE id='$topid';");
    }
    $upquery = "UPDATE `#@__arctype` SETissend='$issend',sortrank='$sortrank',typedir='$typedir',typename='$typename',isdefault='$isdefault',defaultname='$defaultname',ispart='$ispart',corank='$corank' $uptopsql WHERE id='$id'";
    if (!$dsql->ExecuteNoneQuery($upquery)) {
        ShowMsg(Lang("catalog_err_update"), "-1");
        exit();
    }
    UpDateCatCache();
    ShowMsg(Lang("catalog_success_update"), "catalog_main.php");
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
    <input type="hidden" name="id" value="<?php echo $id;?>">
    <input type="hidden" name="topid" value="<?php echo $myrow['topid'];?>">
    <input type="hidden" name="moresite" value="<?php echo $myrow['moresite'];?>">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="160" align="center" class="bline"><?php echo Lang('support_submission');?>：</td>
            <td class="bline">
                <label><input type="radio" name="issend" value="1" <?php if ($myrow['issend'] == "1") echo "checked='1'";?>> <?php echo Lang('support');?></label>
                <label><input type="radio" name="issend" value="0" <?php if ($myrow['issend'] == "0") echo "checked='1'";?>> <?php echo Lang('unsupport');?></label>
            </td>
        </tr>
        <tr>
            <td align="center" class="bline"><?php echo Lang('mychannel');?>：</td>
            <td class="bline">
                <?php
                foreach ($channelArray as $k => $arr) {
                    if ($k == $channelid) echo "{$arr['typename']} | {$arr['nid']}";
                }
                ?>
                <a href='catalog_edit.php?id=<?php echo $id;?>' class='btn btn-success btn-sm'><?php echo Lang('more');?></a>
            </td>
        </tr>
        <tr>
            <td align="center" class="bline"><?php echo Lang('catalog_typename');?>：</td>
            <td class="bline"><input type="text" name="typename" value="<?php echo $myrow['typename'] ?>" style="width:260px"></td>
        </tr>
        <tr>
            <td align="center" class="bline"> <?php echo Lang('sortrank');?>：</td>
            <td class="bline"> <input type="text" name="sortrank" value="<?php echo $myrow['sortrank'] ?>" style="width:100px"><?php echo Lang('sortrank_msg');?></td>
        </tr>
        <tr>
            <td align="center" class="bline"><?php echo Lang('corank');?>：</td>
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
                </select><?php echo Lang('corank_msg');?>
            </td>
        </tr>
        <tr>
            <td align="center" class="bline"><?php echo Lang('catalog_typedir');?>：</td>
            <td class="bline"><input type="text" name="typedir" value="<?php echo $myrow['typedir'] ?>" style="width:260px"></td>
        </tr>
        <tr>
            <td align="center" class="bline"><?php echo Lang('catalog_list_option');?>：</td>
            <td class="bline">
                <label><input type="radio" name="isdefault" value="1" <?php if ($myrow['isdefault'] == 1) echo "checked='1'";?>> <?php echo Lang('catalog_list_option_1');?></label>
                <label><input type="radio" name="isdefault" value="0" <?php if ($myrow['isdefault'] == 0) echo "checked='1'";?>> <?php echo Lang('catalog_list_option_0');?></label>
                <label><input type="radio" name="isdefault" value="-1" <?php if ($myrow['isdefault'] == -1) echo "checked='1'";?>> <?php echo Lang('catalog_list_option_-1');?></label>
            </td>
        </tr>
        <tr>
            <td align="center" class="bline"><?php echo Lang('catalog_default_name');?>：</td>
            <td class="bline"><input type="text" name="defaultname" value="<?php echo $myrow['defaultname'] ?>" style="width:260px"></td>
        </tr>
        <tr>
            <td align="center" class="bline"><?php echo Lang('catalog_ispart');?>：</td>
            <td class="bline">
                <label><input name="ispart" type="radio" value="0" <?php if ($myrow['ispart'] == 0) echo "checked='1'";?>> <?php echo Lang('catalog_ispart_0');?></label><br>
                <label><input name="ispart" type="radio" value="1" <?php if ($myrow['ispart'] == 1) echo "checked='1'";?>> <?php echo Lang('catalog_ispart_1');?></label><br>
                <label><input name="ispart" type="radio" value="2" <?php if ($myrow['ispart'] == 2) echo "checked='1'";?>> <?php echo Lang('catalog_ispart_2');?></label>
            </td>
        </tr>
        <tr>
            <td bgcolor="#f8fcf2" colspan="2" align="center" class="py-2">
                <button onclick='getSelCat("<?php echo $targetid;?>");' class='btn btn-success btn-sm'><?php echo Lang('save');?></button>
                <button type='button' onclick='CloseMsg()' class='btn btn-success btn-sm'><?php echo Lang('close');?></button>
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