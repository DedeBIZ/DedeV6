<?php
/**
 * 后台侧边菜单
 *
 * @version        $id:inc_menu.php 10:32 2010年7月21日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../config.php");
require_once(dirname(__FILE__)."/inc_menu_module.php");
//载入可发布栏目
$addset = '';
//检测可用的文档模型
if ($cfg_admin_channel = 'array' && count($admin_catalogs) > 0) {
    $admin_catalog = join(',', $admin_catalogs);
    $dsql->SetQuery("SELECT channeltype FROM `#@__arctype` WHERE id IN({$admin_catalog}) GROUP BY channeltype ");
} else {
    $dsql->SetQuery("SELECT channeltype FROM `#@__arctype` GROUP BY channeltype ");
}
$dsql->Execute('mc');
$candoChannel = '';
while ($row = $dsql->GetObject('mc')) {
    $candoChannel .= ($candoChannel == '' ? $row->channeltype : ','.$row->channeltype);
}
if (empty($candoChannel)) $candoChannel = 1;
$dsql->SetQuery("SELECT id,typename,addcon,mancon FROM `#@__channeltype` WHERE id IN({$candoChannel}) AND id<>-1 AND isshow=1 ORDER BY id ASC");
$dsql->Execute('im');
while ($row = $dsql->GetObject('im')) {
    $addset .= "<m:item name='{$row->typename}' ischannel='1' link='{$row->mancon}?channelid={$row->id}' linkadd='{$row->addcon}?channelid={$row->id}' channelid='{$row->id}' rank='' target='main' />";
}
$helpUrl = DEDEBIZURL."/help";
$gitUrl = DEDEBIZURL."/git";
$dedebizUrl = DEDEBIZURL;
$adminMenu1 = $adminMenu2 = '';
if ($cuserLogin->getUserType() >= 10) {
    $adminMenu1 = (DEDEBIZ_SAFE_MODE ? "" : "<m:top item='11_' name='模板管理' rank='temp_One,temp_Other,temp_MyTag,temp_test,temp_All' icon='fa fa-cube'>
    <m:item name='默认模板管理' link='templets_main.php' rank='temp_All' target='main' />
    <m:item name='标签源码管理' link='templets_tagsource.php' rank='temp_All' target='main' />
    <m:item name='自定义宏标记' link='mytag_main.php' rank='temp_MyTag' target='main' />
    <m:item name='全局标记测试' link='tag_test.php' rank='temp_Test' target='main' />
</m:top>")."
<m:top item='4_' name='模型管理' rank='t_List,t_AccList,c_List,temp_One' icon='fa fa-area-chart'>
    ".(DEDEBIZ_SAFE_MODE ? "" : "<m:item name='文档模型管理' link='mychannel_main.php' rank='c_List' target='main' />")."
    <m:item name='文档单页管理' link='templets_one.php' rank='temp_One' target='main' />
    <m:item name='联动类型管理' link='stepselect_main.php' rank='c_Stepseclect' target='main' />
    <m:item name='自由列表管理' link='freelist_main.php' rank='c_List' target='main' />
    <m:item name='自定义表单管理' link='diy_main.php' rank='c_List' target='main' />
</m:top>";
  $adminMenu2 = "<m:top item='10_' name='财务管理' rank='sys_Data' icon='fa fa-credit-card'>
    <m:item name='会员消费记录' link='member_operations.php' rank='sys_Data' target='main' />
    <m:item name='积分产品管理' link='cards_manage.php' rank='sys_Data' target='main' />
    <m:item name='积分产品分类' link='cards_type.php' rank='sys_Data' target='main' />
    <m:item name='支付接口设置' link='sys_payment.php' .php' rank='sys_Data' target='main' />
</m:top>
<m:top item='12_' name='系统设置' rank='sys_User,sys_Group,sys_Edit,sys_Log,sys_Data' icon='fa fa-cog'>
    <m:item name='系统设置' link='sys_info.php' rank='sys_Edit' target='main' />
    <m:item name='日志管理' link='log_list.php' rank='sys_Log' target='main' />
    <m:item name='管理员管理' link='sys_admin_user.php' rank='sys_User' target='main' />
    <m:item name='会员组管理' link='sys_group.php' rank='sys_Group' target='main' />
    <m:item name='图片水印设置' link='sys_info_mark.php' rank='sys_Edit' target='main' />
    <m:item name='自定义文档属性' link='content_att.php' rank='sys_Att' target='main' />
    <m:item name='软件下载设置' link='soft_config.php' rank='sys_SoftConfig' target='main' />
    ".(DEDEBIZ_SAFE_MODE ? "" : "<m:item name='数据备份' link='sys_data.php' rank='sys_Data' target='main' />")."
    ".(DEDEBIZ_SAFE_MODE ? "" : "<m:item name='SQL命令工具' link='sys_sql_query.php' rank='sys_Data' target='main' />")."
    <m:item name='文件扫描工具' link='sys_safetest.php' rank='sys_verify' target='main' />
    <m:item name='系统修复工具' link='sys_repair.php' rank='sys_verify' target='main' />
</m:top>";
}
$menusMain = "<m:top item='1_' name='常用功能' icon='fa fa-desktop'>
    <m:item name='网站栏目管理' link='catalog_main.php' ischannel='1' linkadd='catalog_add.php?listtype=all' rank='t_List,t_AccList' target='main' />
    <m:item name='所有文档列表' link='content_list.php' rank='a_List,a_AccList' target='main' />
    <m:item name='待审核的文档' link='content_list.php?arcrank=-1' rank='a_Check,a_AccCheck' target='main' />
    <m:item name='我发布的文档' link='content_list.php?mid=".$cuserLogin->getUserID()."' rank='a_List,a_AccList,a_MyList' target='main' />
    <m:item name='标签管理' link='tags_main.php' rank='sys_Keyword' target='main' />
    <m:item name='评论管理' link='feedback_main.php' rank='sys_Feedback' target='main' />
    <m:item name='专题管理' link='content_s_list.php' ischannel='1' channelid='-1' linkadd='spec_add.php' rank='spec_New' target='main' />
    <m:item name='文档回收站' link='recycling.php' ischannel='1' addico='fa fa-minus-circle' linkadd='archives_do.php?dopost=clear&aid=no&recycle=1' rank='a_List,a_AccList,a_MyList' target='main' />
    <m:item name='流量统计表' link='statchart_list.php' rank='t_List,t_AccList' target='main' />
</m:top>
<m:top item='2_' name='文档管理' icon='fa fa-file-text'>
    $addset
</m:top>
<m:top item='3_' name='附件管理' rank='sys_Upload,sys_MyUpload,plus_文件管理器' icon='fa fa-folder'>
    <m:item name='附件管理' link='media_main.php' rank='sys_Upload,sys_MyUpload' target='main' />
    <m:item name='文件管理器' link='media_main.php?dopost=filemanager' rank='plus_文件管理器' target='main' />
</m:top>
$adminMenu1
<m:top item='5_' name='文档维护' icon='fa fa-circle-o-notch'>
    <m:item name='文档批量维护' link='content_batch_up.php' rank='sys_ArcBatch' target='main' />
    <m:item name='文档重复检测' link='article_test_same.php' rank='sys_ArcBatch' target='main' />
    <m:item name='文档关键词维护' link='article_keywords_main.php' rank='sys_Keyword' target='main' />
    <m:item name='搜索关键词维护' link='search_keywords_main.php' rank='sys_Keyword' target='main' />
    <m:item name='自动摘要分页' link='article_description_main.php' rank='sys_Keyword' target='main' />
    ".(DEDEBIZ_SAFE_MODE ? "" : "<m:item name='数据库字段替换' link='sys_data_replace.php' rank='sys_ArcBatch' target='main' />")."
</m:top>
{$GLOBALS['menusMoudle']}
<m:top item='8_' name='更新网站' rank='sys_MakeHtml' icon='fa fa-repeat'>
    <m:item name='更新整站' link='makehtml_all.php' rank='sys_MakeHtml' target='main' />
    <m:item name='更新首页' link='makehtml_homepage.php' rank='sys_MakeHtml' target='main' />
    <m:item name='更新栏目' link='makehtml_list.php' rank='sys_MakeHtml' target='main' />
    <m:item name='更新文档' link='makehtml_archives.php' rank='sys_MakeHtml' target='main' />
    <m:item name='更新标签' link='makehtml_taglist.php' rank='sys_MakeHtml' target='main' />
    <m:item name='更新专题' link='makehtml_spec.php' rank='sys_MakeHtml' target='main' />
    <m:item name='更新自由列表' link='makehtml_freelist.php' rank='sys_MakeHtml' target='main' />
    <m:item name='更新缓存' link='sys_cache_up.php' rank='sys_ArcBatch' target='main' />
</m:top>
<m:top item='9_' name='会员管理' rank='member_List,member_Type' icon='fa fa-user-circle'>
    <m:item name='所有会员列表' link='member_main.php' rank='member_List' target='main' />
    <m:item name='会员短信管理' link='member_pm.php' rank='member_Type' target='main' />
    <m:item name='会员级别设置' link='member_rank.php' rank='member_Type' target='main' />
    <m:item name='会员等级分类' link='member_type.php' rank='sys_Data' target='main' />
    <m:item name='积分头衔设置' link='member_scores.php' rank='member_Type' target='main' />
</m:top>
$adminMenu2
<m:top item='12_' name='系统帮助' icon='fa fa-question-circle'>
    <m:item name='系统概况' link='$cfg_biz_helpUrl' rank='' target='_blank' />
    <m:item name='代码托管' link='$cfg_biz_gitUrl' rank='' target='_blank' />
</m:top>";
?>