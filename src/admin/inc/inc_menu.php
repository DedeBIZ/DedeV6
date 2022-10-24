<?php
/**
 * 后台管理菜单项
 *
 * @version        $Id: inc_menu.php 2022-07-01 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../config.php");
require_once(dirname(__FILE__)."/inc_menu_module.php");
//载入可发布频道
$addset = '';
//检测可用的内容模型
if ($cfg_admin_channel = 'array' && count($admin_catalogs) > 0) {
    $admin_catalog = join(',', $admin_catalogs);
    $dsql->SetQuery("SELECT channeltype FROM `#@__arctype` WHERE id IN({$admin_catalog}) GROUP BY channeltype");
} else {
    $dsql->SetQuery("SELECT channeltype FROM `#@__arctype` GROUP BY channeltype");
}
$dsql->Execute();
$candoChannel = '';
while ($row = $dsql->GetObject()) {
    $candoChannel .= ($candoChannel == '' ? $row->channeltype : ','.$row->channeltype);
}
if (empty($candoChannel)) $candoChannel = 1;
$dsql->SetQuery("SELECT id,nid,typename,addcon,mancon FROM `#@__channeltype` WHERE id IN({$candoChannel}) AND id<>-1 AND isshow=1 ORDER BY id ASC");
$dsql->Execute('mm');
while ($row = $dsql->GetObject('mm')) {
    $name = $row->typename;
    if ($dlang->Exists("ch_{$row->nid}")) {
        $name = "ch_{$row->nid}";
    }
    $addset .= "<m:item name='{$name}' ischannel='1' link='{$row->mancon}?channelid={$row->id}' linkadd='{$row->addcon}?channelid={$row->id}' channelid='{$row->id}' rank='' target='main' />";
}
$helpUrl = DEDEBIZURL."/help";
$gitUrl = DEDEBIZURL."/git";
$dedebizUrl = DEDEBIZURL;
$adminMenu1 = $adminMenu2 = '';
if ($cUserLogin->getUserType() >= 10) {
    $adminMenu1 = (DEDEBIZ_SAFE_MODE? "" : "<m:top item='7' name='templets_main' rank='temp_One,temp_Other,temp_MyTag,temp_test,temp_All' icon='fa-cube'>
    <m:item name='default_templets_main' link='templets_main.php' rank='temp_All' target='main' />
    <m:item name='templets_tagsource' link='templets_tagsource.php' rank='temp_All' target='main' />
    <m:item name='mytag_main' link='mytag_main.php' rank='temp_MyTag' target='main' />
    <m:item name='mytag_tag_guide' link='mytag_tag_guide.php' rank='temp_Other' target='main' />
    <m:item name='tag_test' link='tag_test.php' rank='temp_Test' target='main' />
</m:top>")."
<m:top item='1' name='mychannel' rank='t_List,t_AccList,c_List,temp_One' icon='fa-area-chart'>
    <m:item name='mychannel_main' link='mychannel_main.php' rank='c_List' target='main' />
    <m:item name='templets_one' link='templets_one.php' rank='temp_One' target='main' />
    <m:item name='stepselect_main' link='stepselect_main.php' rank='c_Stepseclect' target='main' />
    <m:item name='freelist_main' link='freelist_main.php' rank='c_List' target='main' />
    <m:item name='diy_main' link='diy_main.php' rank='c_List' target='main' />
</m:top>";
  $adminMenu2 = "<m:top item='6' name='pay_tools' rank='sys_Data' icon='fa-credit-card'>
    <m:item name='cards_type' link='cards_type.php' rank='sys_Data' target='main' />
    <m:item name='cards_manage' link='cards_manage.php' rank='sys_Data' target='main' />
    <m:item name='member_type' link='member_type.php' rank='sys_Data' target='main' />
    <m:item name='member_operations' link='member_operations.php' rank='sys_Data' target='main' />
    <m:item name='shops_operations' link='shops_operations.php' rank='sys_Data' target='main' />
    <m:item name='sys_payment' link='sys_payment.php' .php' rank='sys_Data' target='main' />
</m:top>
<m:top item='10' name='sys_setting' rank='sys_User,sys_Group,sys_Edit,sys_Log,sys_Data' icon='fa-cog'>
    <m:item name='sys_info' link='sys_info.php' rank='sys_Edit' target='main' />
    <m:item name='sys_admin_user' link='sys_admin_user.php' rank='sys_User' target='main' />
    <m:item name='sys_group' link='sys_group.php' rank='sys_Group' target='main' />
    <m:item name='log_list' link='log_list.php' rank='sys_Log' target='main' />
    <m:item name='sys_info_mark' link='sys_info_mark.php' rank='sys_Edit' target='main' />
    <m:item name='content_att' link='content_att.php' rank='sys_Att' target='main' />
    <m:item name='soft_config' link='soft_config.php' rank='sys_SoftConfig' target='main' />
    <m:item name='article_string_mix' link='article_string_mix.php' rank='sys_StringMix' target='main' />
    <m:item name='article_template_rand' link='article_template_rand.php' rank='sys_StringMix' target='main' />
    ".(DEDEBIZ_SAFE_MODE? "" : "<m:item name='sys_data' link='sys_data.php' rank='sys_Data' target='main' />")."
    ".(DEDEBIZ_SAFE_MODE? "" : "<m:item name='sys_sql_query' link='sys_sql_query.php' rank='sys_Data' target='main' />")."
    <m:item name='sys_safetest' link='sys_safetest.php' rank='sys_verify' target='main' />
    <m:item name='sys_repair' link='sys_repair.php' rank='sys_verify' target='main' />
</m:top>";
}
$menusMain = "<m:top item='1' name='common_operation' icon='fa-desktop'>
    <m:item name='site_catalog_main' link='catalog_main.php' ischannel='1' addalt='catalog_add' linkadd='catalog_add.php?listtype=all' rank='t_List,t_AccList' target='main' />
    <m:item name='content_list' link='content_list.php' rank='a_List,a_AccList' target='main' />
    <m:item name='content_list_-1' link='content_list.php?arcrank=-1' rank='a_Check,a_AccCheck' target='main' />
    <m:item name='content_list_me' link='content_list.php?mid=".$cUserLogin->getUserID()."' rank='a_List,a_AccList,a_MyList' target='main' />
    <m:item name='feedback_main' link='feedback_main.php' rank='sys_Feedback' target='main' />
    <m:item name='recycling' link='recycling.php' ischannel='1' addalt='clear_recycling' addico='fa-minus-circle' linkadd='archives_do.php?dopost=clear&aid=no&recycle=1' rank='a_List,a_AccList,a_MyList' target='main' />
</m:top>
<m:top item='1' name='content_main' icon='fa-file-text'>
    $addset
    <m:item name='content_s_list' ischannel='1' link='content_s_list.php' linkadd='spec_add.php' channelid='-1' rank='spec_New' target='main' />
</m:top>
<m:top item='2' name='attachment_main' rank='sys_Upload,sys_MyUpload,plus_文件管理器' icon='fa-folder'>
    <m:item name='media_add' link='media_add.php' rank='' target='main' />
    <m:item name='media_main' link='media_main.php' rank='sys_Upload,sys_MyUpload' target='main' />
    <m:item name='media_main_filemanager' link='media_main.php?dopost=filemanager' rank='plus_文件管理器' target='main' />
</m:top>
$adminMenu1
<m:top item='1' name='batch_main' icon='fa-refresh'>
    <m:item name='content_batch_up' link='content_batch_up.php' rank='sys_ArcBatch' target='main' />
    <m:item name='search_keywords_main' link='search_keywords_main.php' rank='sys_Keyword' target='main' />
    <m:item name='article_keywords_main' link='article_keywords_main.php' rank='sys_Keyword' target='main' />
    <m:item name='article_test_same' link='article_test_same.php' rank='sys_ArcBatch' target='main' />
    <m:item name='article_description_main' link='article_description_main.php' rank='sys_Keyword' target='main' />
    <m:item name='tags_main' link='tags_main.php' rank='sys_Keyword' target='main' />
    ".(DEDEBIZ_SAFE_MODE? "" : "<m:item name='sys_data_replace' link='sys_data_replace.php' rank='sys_ArcBatch' target='main' />")."
</m:top>
{$GLOBALS['menusMoudle']}
<m:top item='1' name='makehtml_task' rank='sys_MakeHtml' icon='fa-repeat'>
    <m:item name='makehtml_all' link='makehtml_all.php' rank='sys_MakeHtml' target='main' />
    <m:item name='makehtml_homepage' link='makehtml_homepage.php' rank='sys_MakeHtml' target='main' />
    <m:item name='makehtml_list' link='makehtml_list.php' rank='sys_MakeHtml' target='main' />
    <m:item name='makehtml_archives' link='makehtml_archives.php' rank='sys_MakeHtml' target='main' />
    <m:item name='makehtml_taglist' link='makehtml_taglist.php' rank='sys_MakeHtml' target='main' />
    <m:item name='makehtml_spec' link='makehtml_spec.php' rank='sys_MakeHtml' target='main' />
    <m:item name='sys_cache_up' link='sys_cache_up.php' rank='sys_ArcBatch' target='main' />
</m:top>
<m:top item='6' name='user_main' rank='member_List,member_Type' icon='fa-user-circle'>
    <m:item name='member_main' link='member_main.php' rank='member_List' target='main' />
    <m:item name='member_rank' link='member_rank.php' rank='member_Type' target='main' />
    <m:item name='member_scores' link='member_scores.php' rank='member_Type' target='main' />
    <m:item name='member_pm' link='member_pm.php' rank='member_Type' target='main' />
</m:top>
$adminMenu2
<m:top item='5' name='dedebiz_help' icon='fa-info-circle'>
    <m:item name='dedebiz_intro' link='$cfg_biz_helpUrl' rank='' target='_blank' />
    <m:item name='dedebiz_git' link='$cfg_biz_gitUrl' rank='' target='_blank' />
</m:top>";
?>