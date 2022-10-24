<?php
/**
 * 文档规则采集
 *
 * @version        $Id: article_coonepage_rule.php 1 14:12 2010年7月12日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\DataListCP;
require_once(dirname(__FILE__)."/config.php");
setcookie("ENV_GOBACK_URL", $dedeNowurl, time() + 3600, "/");
if (empty($action)) $action = '';
if ($action == 'add') {
    $row = $dsql->GetOne("SELECT * FROM `#@__co_onepage` WHERE url LIKE '$url'");
    if (is_array($row)) {
        echo Lang("content_coonepage_exists");
    } else {
        $query = "INSERT INTO `#@__co_onepage` (`url`,`title`,`issource`,`lang`,`rule`) VALUES ('$url','$title','$issource','$lang','$rule');";
        $dsql->ExecuteNonequery($query);
        echo $dsql->GetError();
    }
}
else if ($action == 'del') {
    if (!preg_match("#,#", $ids)) {
        $query = "DELETE FROM `#@__co_onepage` WHERE id='$ids'";
    } else {
        $query = "DELETE FROM `#@__co_onepage` WHERE id IN($ids)";
    }
    $dsql->ExecuteNonequery($query);
}
else if ($action == 'editsave') {
    $query = "UPDATE `#@__co_onepage` SET `url`='$url',`title`='$title',`issource`='$issource',`lang`='$lang',`rule`='$rule' WHERE id='$id'";
    $dsql->ExecuteNonequery($query);
    echo $dsql->GetError();
}
else if ($action == 'editload') {
    $row = $dsql->GetOne("SELECT * FROM `#@__co_onepage` WHERE id='$id'");
    AjaxHead();
?>
<form name="addform" action="article_coonepage_rule.php" method="post">
    <input type="hidden" name="id" value="<?php echo $id;?>">
    <input type="hidden" name="action" value="editsave">
    <table width="430" cellspacing="0" cellpadding="0">
        <tr>
            <td width="90"><?php echo Lang('content_coonepage_editload_title');?></td>
            <td width="270"><input type="text" name="title" id="title" style="width:260px" value="<?php echo $row['title'];?>"></td>
        </tr>
        <tr>
            <td><?php echo Lang('content_coonepage_editload_lang');?></td>
            <td colspan="2">
              <label><input type="radio" name="lang" value="utf-8" <?php echo ($row['lang'] == 'utf-8' ? 'checked="checked"' : '');?>> UTF-8</label>
              <label><input type="radio" name="lang" value="gb2312" <?php echo ($row['lang'] == 'gb2312' ? 'checked="checked"' : '');?>> GB2312/GBK</label>
            </td>
        </tr>
        <tr>
            <td><?php echo Lang('content_coonepage_editload_issource');?></td>
            <td colspan="2">
                <label><input type="radio" name="issource" value="0" <?php echo ($row['issource'] == 0 ? 'checked="checked"' : '');?>> <?php echo Lang('no');?></label>
                <label><input type="radio" name="issource" value="1" <?php echo ($row['issource'] == 1 ? 'checked="checked"' : '');?>> <?php echo Lang('yes');?></label>
            </td>
        </tr>
        <tr>
            <td><?php echo Lang('content_coonepage_editload_url');?></td>
            <td colspan="2"><input type="text" name="url" id="url" value="<?php echo $row['url'];?>" style="width:260px"></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2"><?php echo Lang('content_coonepage_tip');?></td>
        </tr>
        <tr>
            <td><?php echo Lang('content_coonepage_editload_rule');?></td>
            <td colspan="2"><?php echo Lang('content_coonepage_editload_rule_tip');?></td>
        </tr>
        <tr>
            <td height="90"></td>
            <td colspan="2"><textarea name="rule" style="width:300px;height:80px"><?php echo $row['rule'];?></textarea></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2">
                <button class="btn btn-success btn-sm" type="submit" name="Submit"><?php echo Lang('save');?></button>
                <button class="btn btn-success btn-sm" type="button" name="Submit2" onclick="javascript:CloseEditNode();"><?php echo Lang('close');?></button>
            </td>
        </tr>
    </table>
</form>
<?php
    exit();
}
$sql = "";
$sql = "SELECT id,url,title,lang,issource FROM `#@__co_onepage` ORDER BY id DESC";
$dlist = new DataListCP();
$dlist->SetTemplate(DEDEADMIN."/templets/article_coonepage_rule.htm");
$dlist->SetSource($sql);
$dlist->Display();
?>