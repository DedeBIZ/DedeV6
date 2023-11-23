<?php
/**
 * 添加文档模型
 *
 * @version        $id:mychannel_add.php 14:46 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (DEDEBIZ_SAFE_MODE) {
    die(DedeAlert("系统已启用安全模式，无法使用当前功能",ALERT_DANGER));
}
CheckPurview('c_New');
require_once(DEDEINC."/dedetag.class.php");
if (empty($ismake)) $ismake = 0;
if (empty($isdel)) $isdel = 0;
if (empty($action)) $action = '';
if ($action == 'add') {
    //检查输入
    if (empty($id) || preg_match("#[^0-9-]#", $id)) {
        ShowMsg("模型id必须为数字", "-1");
        exit();
    }
    if (preg_match("#[^a-z0-9]#i", $nid) || $nid == "") {
        ShowMsg("模型名字必须为英文字母或与数字混合字符串", "-1");
        exit();
    }
    if ($addtable == "") {
        ShowMsg("附加表不能为空", "-1");
        exit();
    }
    $trueTable2 = str_replace("#@__", $cfg_dbprefix, $addtable);
    if ($issystem == -1 && $id > 0) $id = $id * -1;
    //检查id是否重复
    $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$id' OR nid LIKE '$nid' OR addtable LIKE '$addtable'");
    if (is_array($row)) {
        ShowMsg("模型id和模型名称及附加表名称数据库已存在，请重新填写", "-1");
        exit();
    }
    $mysql_version = $dsql->GetVersion();
    //创建附加表
    if ($trueTable2 != '') {
        $istb = $dsql->IsTable($trueTable2);
        if (!$istb || $isdel == 1) {
            //是否需要摘要字段
            $dsql->ExecuteNoneQuery("DROP TABLE IF EXISTS `{$trueTable2}`;");
            if ($issystem != -1) {
                $tabsql = "CREATE TABLE `$trueTable2` (`aid` int(11) NOT NULL DEFAULT '0',`typeid` int(11) NOT NULL DEFAULT '0',`redirecturl` varchar(255) NOT NULL DEFAULT '',`templet` varchar(30) NOT NULL DEFAULT '',`userip` char(48) NOT NULL DEFAULT '',";
            } else {
                $tabsql = "CREATE TABLE `$trueTable2` (`aid` int(11) NOT NULL DEFAULT '0',`typeid` int(11) NOT NULL DEFAULT '0',`channel` SMALLINT NOT NULL DEFAULT '0',`arcrank` SMALLINT NOT NULL DEFAULT '0',`mid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',`click` INT(10) UNSIGNED NOT NULL DEFAULT '0',`title` varchar(255) NOT NULL DEFAULT '',`senddate` int(11) NOT NULL DEFAULT '0',`flag` set('c','h','p','f','s','j','a','b') DEFAULT NULL,`litpic` varchar(60) NOT NULL DEFAULT '',`userip` char(48) NOT NULL DEFAULT '',`lastpost` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',`scores` MEDIUMINT(8) NOT NULL DEFAULT '0',`goodpost` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',`badpost` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
            ";
            }
            if ($mysql_version < 4.1) {
                $tabsql .= " PRIMARY KEY (`aid`), KEY `typeid` (`typeid`)\r\n) TYPE=MyISAM; ";
            } else {
                $tabsql .= " PRIMARY KEY (`aid`), KEY `typeid` (`typeid`)\r\n) ENGINE=MyISAM DEFAULT CHARSET=".$cfg_db_language."; ";
            }
            $rs = $dsql->ExecuteNoneQuery($tabsql);
            if (!$rs) {
                ShowMsg("创建附加表失败".$dsql->GetError(), "javascript:;");
                exit();
            }
        }
    }
    $listfields = $fieldset = '';
    if ($issystem == -1) {
        $fieldset = "<field:channel itemname=\"模型id\" autofield=\"0\" notsend=\"0\" type=\"int\" isnull=\"true\" islist=\"1\" default=\"0\" maxlength=\"10\" page=\"\"></field:channel>
<field:arcrank itemname=\"浏览权限\" autofield=\"0\" notsend=\"0\" type=\"int\" isnull=\"true\" islist=\"1\" default=\"0\" maxlength=\"5\" page=\"\"></field:arcrank>
<field:mid itemname=\"会员id\" autofield=\"0\" notsend=\"0\" type=\"int\" isnull=\"true\" islist=\"1\" default=\"0\" maxlength=\"8\" page=\"\"></field:mid>
<field:click itemname=\"点击\" autofield=\"0\" notsend=\"0\" type=\"int\" isnull=\"true\" islist=\"1\" default=\"0\" maxlength=\"10\" page=\"\"></field:click>
<field:title itemname=\"标题\" autofield=\"0\" notsend=\"0\" type=\"text\" isnull=\"true\" islist=\"1\" default=\"0\" maxlength=\"60\" page=\"\"></field:title>
<field:senddate itemname=\"发布时间\" autofield=\"0\" notsend=\"0\" type=\"int\" isnull=\"true\" islist=\"1\" default=\"0\" maxlength=\"10\" page=\"\"></field:senddate>
<field:flag itemname=\"推荐属性\" autofield=\"0\" notsend=\"0\" type=\"checkbox\" isnull=\"true\" islist=\"1\" default=\"0\" maxlength=\"10\" page=\"\"></field:flag>
<field:litpic itemname=\"缩略图\" autofield=\"0\" notsend=\"0\" type=\"text\" isnull=\"true\" islist=\"0\" default=\"\" maxlength=\"60\" page=\"\"></field:litpic>
<field:userip itemname=\"会员ip\" autofield=\"0\" notsend=\"0\" type=\"text\" isnull=\"true\" islist=\"0\" default=\"0\" maxlength=\"15\" page=\"\"></field:userip>
<field:lastpost itemname=\"最后评论时间\" autofield=\"0\" notsend=\"0\" type=\"int\" isnull=\"true\" islist=\"1\" default=\"0\" maxlength=\"10\" page=\"\"></field:lastpost>
<field:scores itemname=\"评论积分\" autofield=\"0\" notsend=\"0\" type=\"int\" isnull=\"true\" islist=\"1\" default=\"0\" maxlength=\"8\" page=\"\"></field:scores>
<field:goodpost itemname=\"好评数\" autofield=\"0\" notsend=\"0\" type=\"int\" isnull=\"true\" islist=\"1\" default=\"0\" maxlength=\"8\" page=\"\"></field:goodpost>
<field:badpost itemname=\"差评数\" autofield=\"0\" notsend=\"0\" type=\"int\" isnull=\"true\" islist=\"1\" default=\"0\" maxlength=\"8\" page=\"\"></field:badpost>\r\n";
        $listfields = 'channel,arcrank,mid,click,title,senddate,flag,listpic,lastpost,scores,goodpost,badpost';
    }
    $inQuery = "INSERT INTO `#@__channeltype` (id,nid,typename,addtable,addcon,mancon,editcon,useraddcon,usermancon,usereditcon,fieldset,listfields,issystem,issend,arcsta,usertype,sendrank,needdes,needpic,titlename,onlyone,dfcid) VALUES ('$id','$nid','$typename','$addtable','$addcon','$mancon','$editcon','$useraddcon','$usermancon','$usereditcon','$fieldset','$listfields','$issystem','$issend','$arcsta','$usertype','$sendrank','$needdes','$needpic','$titlename','$onlyone','$dfcid');";
    $dsql->ExecuteNoneQuery($inQuery);
    ShowMsg("成功创建一个文档模型", "mychannel_edit.php?id=".$id);
    exit();
}
$row = $dsql->GetOne("SELECT id FROM `#@__channeltype` ORDER BY id DESC LIMIT 0,1 ");
$newid = $row['id'] + 1;
if ($newid < 10) $newid = $newid + 10;
require_once(DEDEADMIN."/templets/mychannel_add.htm");
?>