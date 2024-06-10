<?php
/**
 * 后台操作记录信息
 *
 * @version        $id:inc_action_info.php 2 14:55 2010-11-11 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../config.php");
$cuserLogin = new userLogin();
//后台功能操作配置项
$actionSearch[0] = array(
    'title'  => '常用操作',
    'soniterm' =>  array(
        0  =>  array(
            'title' => '网站栏目管理',
            'purview' => 't_List,t_AccList',
            'linkurl' => 'catalog_main.php'
        ),
        1  =>  array(
            'title' => '待审核的文档',
            'purview' => 'a_Check,a_AccCheck',
            'linkurl' => 'content_list.php?arcrank=-1'
        ),
        2  =>  array(
            'title' => '我发布的文档',
            'purview' => 'a_List,a_AccList,a_MyList',
            'linkurl' => 'content_list.php?mid=$cuserLogin->userID'
        ),
        3  =>  array(
            'title' => '标签管理',
            'purview' => 'sys_Keyword',
            'linkurl' => 'tags_main.php'
        ),
        4  =>  array(
            'title' => '评论管理',
            'purview' => 'sys_Feedback',
            'linkurl' => 'feedback_main.php'
        ),
        5  =>  array(
            'title' => '专题管理',
            'purview' => 'spec_New',
            'linkurl' => 'content_s_list.php'
        ),
        6  =>  array(
            'title' => '文档回收站',
            'description' => '后台删除的文档会存放在此处',
            'purview' => 'a_List,a_AccList,a_MyList',
            'linkurl' => 'recycling.php'
        )
    )
);
$actionSearch[1] = array(
    'title' => '附件管理',
    'soniterm' => array(
        0  =>  array(
            'title' => '附件管理',
            'purview' => 'sys_Upload,sys_MyUpload',
            'linkurl' => 'media_main.php'
        ),
        1  =>  array(
            'title' => '文件管理器',
            'purview' => 'plus_文件管理器',
            'linkurl' => 'media_main.php?dopost=filemanager'
        ),
    )
);
$actionSearch[2] = array(
    'title' => '文档模型',
    'soniterm' => array(
        0  => DEDEBIZ_SAFE_MODE? null : array(
            'title' => '文档模型管理',
            'purview' => 'c_List',
            'linkurl' => 'mychannel_main.php'
        ),
        1  =>  array(
            'title' => '文档单页管理',
            'purview' => 'temp_One',
            'linkurl' => 'templets_one.php'
        ),
        2  =>  array(
            'title' => '联动类型管理',
            'purview' => 'c_Stepseclect',
            'linkurl' => 'stepselect_main.php?dopost=filemanager'
        ),
        3  =>  array(
            'title' => '自由列表管理',
            'purview' => 'c_List',
            'linkurl' => 'freelist_main.php'
        ),
        4  =>  array(
            'title' => '自定义文档属性',
            'purview' => 'sys_Att',
            'linkurl' => 'content_att.php'
        ),
        5  =>  array(
            'title' => '自定义表单管理',
            'purview' => 'c_List',
            'linkurl' => 'diy_main.php'
        ),
    )
);
$actionSearch[3] = array(
    'title' => '批量维护',
    'soniterm' => array(
        0  =>  array(
            'title' => '文档批量维护',
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'content_batch_up.php'
        ),
        1  =>  array(
            'title' => '文档重复检测',
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'article_test_same.php'
        ),
        2  =>  array(
            'title' => '文档关键词维护',
            'purview' => 'sys_Keyword',
            'linkurl' => 'article_keywords_main.php'
        ),
        3  =>  array(
            'title' => '搜索关键词维护',
            'purview' => 'sys_Keyword',
            'linkurl' => 'search_keywords_main.php?dopost=filemanager'
        ),
        4  =>  array(
            'title' => '自动摘要分页',
            'purview' => 'sys_Keyword',
            'linkurl' => 'article_description_main.php'
        ),
        5  => DEDEBIZ_SAFE_MODE? null : array(
            'title' => '数据库字段替换',
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'sys_data_replace.php'
        ),
    )
);
$actionSearch[4] = array(
    'title' => '会员管理',
    'soniterm' => array(
        0  =>  array(
            'title' => '所有会员列表',
            'purview' => 'member_List',
            'linkurl' => 'member_main.php'
        ),
        1  =>  array(
            'title' => '会员短信管理',
            'purview' => 'member_Type',
            'linkurl' => 'member_pm.php'
        ),
        2  =>  array(
            'title' => '会员级别设置',
            'purview' => 'member_Type',
            'linkurl' => 'member_rank.php'
        ),
        3  =>  array(
            'title' => '会员等级分类',
            'purview' => 'sys_Data',
            'linkurl' => 'member_type.php'
        ),
        4  =>  array(
            'title' => '积分头衔设置',
            'purview' => 'member_Type',
            'linkurl' => 'member_scores.php'
        ),
    )
);
$actionSearch[5] = array(
    'title' => '财务管理',
    'soniterm' => array(
        0  =>  array(
            'title' => '积分产品管理',
            'purview' => 'sys_Data',
            'linkurl' => 'cards_manage.php'
        ),
        1  =>  array(
            'title' => '积分产品分类',
            'purview' => 'sys_Data',
            'linkurl' => 'cards_type.php'
        ),
        2  =>  array(
            'title' => '会员消费记录',
            'purview' => 'sys_Data',
            'linkurl' => 'member_operations.php'
        ),
        3  =>  array(
            'title' => '支付接口设置',
            'purview' => 'sys_Data',
            'linkurl' => 'sys_payment.php'
        ),
    )
);
$actionSearch[6] = array(
    'title' => '更新任务',
    'soniterm' => array(
        0  =>  array(
            'title' => '更新网站',
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_all.php'
        ),
        1  =>  array(
            'title' => '更新首页',
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_homepage.php'
        ),
        2  =>  array(
            'title' => '更新栏目',
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_list.php'
        ),
        3  =>  array(
            'title' => '更新文档',
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_archives.php'
        ),
        4  =>  array(
            'title' => '更新专题',
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_spec.php'
        ),
        5  =>  array(
            'title' => '更新缓存',
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'sys_cache_up.php'
        ),
    )
);
$actionSearch[7] = DEDEBIZ_SAFE_MODE? null : array(
    'title' => '模板管理',
    'soniterm' => array(
        0  => array(
            'title' => '默认模板管理',
            'purview' => 'temp_All',
            'linkurl' => 'templets_main.php'
        ),
        1  => array(
            'title' => '标签源码管理',
            'purview' => 'temp_All',
            'linkurl' => 'templets_tagsource.php'
        ),
        2  =>  array(
            'title' => '自定义宏标记',
            'purview' => 'temp_MyTag',
            'linkurl' => 'mytag_main.php'
        ),
        3  =>  array(
            'title' => '全局标记测试',
            'purview' => 'temp_Test',
            'linkurl' => 'tag_test.php'
        ),
    )
);
$actionSearch[8] = array(
    'title' => '系统设置',
    'soniterm' => array(
        0  =>  array(
            'title' => '系统设置',
            'purview' => 'sys_Edit',
            'linkurl' => 'sys_info.php'
        ),
        1  =>  array(
            'title' => '管理员管理',
            'purview' => 'sys_User',
            'linkurl' => 'sys_admin_user.php'
        ),
        2  =>  array(
            'title' => '会员组管理',
            'purview' => 'sys_Group',
            'linkurl' => 'sys_group.php'
        ),
        3  =>  array(
            'title' => '日志管理',
            'purview' => 'sys_Log',
            'linkurl' => 'log_list.php'
        ),
        4  =>  array(
            'title' => '图片水印设置',
            'purview' => 'sys_Edit',
            'linkurl' => 'sys_info_mark.php'
        ),
        5  =>  array(
            'title' => '软件下载设置',
            'purview' => 'sys_SoftConfig',
            'linkurl' => 'soft_config.php'
        ),
        6  => DEDEBIZ_SAFE_MODE? null : array(
            'title' => '数据库备份还原',
            'purview' => 'sys_data',
            'linkurl' => 'sys_data.php'
        ),
        7  => DEDEBIZ_SAFE_MODE? null : array(
            'title' => 'SQL命令工具',
            'purview' => 'sys_data',
            'linkurl' => 'sys_sql_query.php'
        ),
        8  =>  array(
            'title' => '文件扫描工具',
            'purview' => 'sys_verifies',
            'linkurl' => 'sys_safetest.php'
        ),
        9  =>  array(
            'title' => '系统修复工具',
            'purview' => 'sys_verifies',
            'linkurl' => 'sys_repair.php'
        ),
    )
);
?>