<?php
/**
 * 后台操作记录信息
 *
 * @version        $Id: inc_action_info.php 2022-07-01 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/../config.php");
$cUserLogin = new UserLogin();
//后台功能操作配置项
$actionSearch[0] = array(
    'toptitle' => Lang('manage'),
    'title'  => Lang('normal_actions'),
    'description' => Lang('normal_actions_desc'),
    'soniterm' =>  array(
        0  =>  array(
            'title' => Lang('site_catalog_main'),
            'description' => Lang('site_catalog_main_desc'),
            'purview' => 't_List,t_AccList',
            'linkurl' => 'catalog_main.php'
        ),
        1  =>  array(
            'title' => Lang('content_list_-1'),
            'description' => Lang('content_list_-1_desc'),
            'purview' => 'a_Check,a_AccCheck',
            'linkurl' => 'content_list.php?arcrank=-1'
        ),
        2  =>  array(
            'title' => Lang('content_list_me'),
            'description' => Lang('content_list_me_desc'),
            'purview' => 'a_List,a_AccList,a_MyList',
            'linkurl' => 'content_list.php?mid='.$cUserLogin->userID
        ),
        3  =>  array(
            'title' => Lang('feedback_main'),
            'description' => Lang('feedback_main_desc'),
            'purview' => 'sys_Feedback',
            'linkurl' => 'feedback_main.php'
        ),
        4  =>  array(
            'title' => Lang('content_recycling'),
            'description' => Lang('content_recycling_desc'),
            'purview' => 'a_List,a_AccList,a_MyList',
            'linkurl' => 'recycling.php'
        )
    )
);
$actionSearch[1] = array(
    'toptitle' => Lang('manage'),
    'title' => Lang('content_main'),
    'description' => Lang('content_main_desc'),
    'soniterm' => array(
        0  =>  array(
            'title' => Lang('content_s_list'),
            'description' => Lang('content_s_list_desc'),
            'purview' => 'spec_New',
            'linkurl' => 'content_s_list.php'
        ),
    )
);
$actionSearch[2] = array(
    'toptitle' => Lang('manage'),
    'title' => Lang('attachment_main'),
    'description' => Lang('attachment_main_desc'),
    'soniterm' => array(
        0  =>  array(
            'title' => Lang('media_add'),
            'description' => Lang('media_add_desc'),
            'purview' => '',
            'linkurl' => 'media_add.php'
        ),
        1  =>  array(
            'title' => Lang('media_main'),
            'description' => Lang('media_main_desc'),
            'purview' => 'sys_Upload,sys_MyUpload',
            'linkurl' => 'media_main.php'
        ),
        2  =>  array(
            'title' => Lang('media_main_filemanager'),
            'description' => Lang('media_main_filemanager_desc'),
            'purview' => 'plus_文件管理器',
            'linkurl' => 'media_main.php?dopost=filemanager'
        ),
    )
);
$actionSearch[3] = array(
    'toptitle' => Lang('manage'),
    'title' => Lang('mychannel'),
    'description' => Lang('mychannel_desc'),
    'soniterm' => array(
        0  =>  array(
            'title' => Lang('mychannel_main'),
            'description' => Lang('mychannel_main_desc'),
            'purview' => 'c_List',
            'linkurl' => 'mychannel_main.php'
        ),
        1  =>  array(
            'title' => Lang('templets_one'),
            'description' => Lang('templets_one_desc'),
            'purview' => 'temp_One',
            'linkurl' => 'templets_one.php'
        ),
        2  =>  array(
            'title' => Lang('stepselect_main'),
            'description' => Lang('stepselect_main_desc'),
            'purview' => 'c_Stepseclect',
            'linkurl' => 'stepselect_main.php?dopost=filemanager'
        ),
        3  =>  array(
            'title' => Lang('freelist_main'),
            'description' => Lang('freelist_main_desc'),
            'purview' => 'c_List',
            'linkurl' => 'freelist_main.php'
        ),
        4  =>  array(
            'title' => Lang('diy_main'),
            'description' => Lang('diy_main_desc'),
            'purview' => 'c_List',
            'linkurl' => 'diy_main.php'
        ),
    )
);
$actionSearch[4] = array(
    'toptitle' => Lang('manage'),
    'title' => Lang('batch_main'),
    'description' => Lang('batch_main_desc'),
    'soniterm' => array(
        0  =>  array(
            'title' => Lang('sys_cache_up'),
            'description' => Lang('sys_cache_up_desc'),
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'sys_cache_up.php'
        ),
        1  =>  array(
            'title' => Lang('content_batch_up'),
            'description' => Lang('content_batch_up_desc'),
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'content_batch_up.php'
        ),
        2  =>  array(
            'title' => Lang('search_keywords_main'),
            'description' => Lang('search_keywords_main_desc'),
            'purview' => 'sys_Keyword',
            'linkurl' => 'search_keywords_main.php?dopost=filemanager'
        ),
        3  =>  array(
            'title' => Lang('article_keywords_main'),
            'description' => Lang('article_keywords_main_desc'),
            'purview' => 'sys_Keyword',
            'linkurl' => 'article_keywords_main.php'
        ),
        4  =>  array(
            'title' => Lang('article_test_same'),
            'description' => Lang('article_test_same_desc'),
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'article_test_same.php'
        ),
        5  =>  array(
            'title' => Lang('article_description_main'),
            'description' => Lang('article_description_main_desc'),
            'purview' => 'sys_Keyword',
            'linkurl' => 'article_description_main.php'
        ),
        6  =>  array(
            'title' => Lang('tags_main'),
            'description' => Lang('tags_main_desc'),
            'purview' => 'sys_Keyword',
            'linkurl' => 'tags_main.php'
        ),
        7  =>  array(
            'title' => Lang('sys_data_replace'),
            'description' => Lang('sys_data_replace_desc'),
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'sys_data_replace.php'
        ),
    )
);
$actionSearch[5] = array(
    'toptitle' => Lang('member'),
    'title' => Lang('member_main2'),
    'description' => Lang('member_main2_desc'),
    'soniterm' => array(
        0  =>  array(
            'title' => Lang('member_main'),
            'description' => Lang('member_main_desc'),
            'purview' => 'member_List',
            'linkurl' => 'member_main.php'
        ),
        1  =>  array(
            'title' => Lang('member_rank'),
            'description' => Lang('member_rank_desc'),
            'purview' => 'member_Type',
            'linkurl' => 'member_rank.php'
        ),
        2  =>  array(
            'title' => Lang('member_scores'),
            'description' => Lang('member_scores_desc'),
            'purview' => 'member_Type',
            'linkurl' => 'member_scores.php'
        ),
        4  =>  array(
            'title' => Lang('member_pm'),
            'description' => Lang('member_pm_desc'),
            'purview' => 'member_Type',
            'linkurl' => 'member_pm.php'
        ),
    )
);
$actionSearch[6] = array(
    'toptitle' => Lang('member'),
    'title' => Lang('pay_tools'),
    'description' => Lang('pay_tools_desc'),
    'soniterm' => array(
        0  =>  array(
            'title' => Lang('cards_type'),
            'description' => Lang('cards_type_desc'),
            'purview' => 'sys_Data',
            'linkurl' => 'cards_type.php'
        ),
        1  =>  array(
            'title' => Lang('cards_manage'),
            'description' => Lang('cards_manage_desc'),
            'purview' => 'sys_Data',
            'linkurl' => 'cards_manage.php'
        ),
        2  =>  array(
            'title' => Lang('member_type'),
            'description' => Lang('member_type_desc'),
            'purview' => 'sys_Data',
            'linkurl' => 'member_type.php'
        ),
        3  =>  array(
            'title' => Lang('member_operations'),
            'description' => Lang('member_operations_desc'),
            'purview' => 'sys_Data',
            'linkurl' => 'member_operations.php'
        ),
        4  =>  array(
            'title' => Lang('shops_operations'),
            'description' => Lang('shops_operations_desc'),
            'purview' => 'sys_Data',
            'linkurl' => 'shops_operations.php'
        ),
        5  =>  array(
            'title' => Lang('sys_payment'),
            'description' => Lang('sys_payment_desc'),
            'purview' => 'sys_Data',
            'linkurl' => 'sys_payment.php'
        ),
    )
);
$actionSearch[7] = array(
    'toptitle' => Lang('makehtml'),
    'title' => Lang('makehtml_task'),
    'description' => Lang('makehtml_task_desc'),
    'soniterm' => array(
        0  =>  array(
            'title' => Lang('makehtml_all'),
            'description' => Lang('makehtml_all_desc'),
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_all.php'
        ),
        1  =>  array(
            'title' => Lang('makehtml_homepage'),
            'description' => Lang('makehtml_homepage_desc'),
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_homepage.php'
        ),
        2  =>  array(
            'title' => Lang('makehtml_list'),
            'description' => Lang('makehtml_list_desc'),
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_list.php'
        ),
        3  =>  array(
            'title' => Lang('makehtml_archives'),
            'description' => Lang('makehtml_archives_desc'),
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_archives.php'
        ),
        4  =>  array(
            'title' => Lang('makehtml_spec'),
            'description' => Lang('makehtml_spec_desc'),
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_spec.php'
        ),
        5  =>  array(
            'title' => Lang('sys_cache_up'),
            'description' => Lang('sys_cache_up_desc'),
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'sys_cache_up.php'
        ),
    )
);
$actionSearch[8] = DEDEBIZ_SAFE_MODE? null : array(
    'toptitle' => Lang('template'),
    'title' => Lang('templets_main'),
    'description' => Lang('templets_main_desc'),
    'soniterm' => array(
        0  => array(
            'title' => Lang('templets_main2'),
            'description' => Lang('templets_main2_desc'),
            'purview' => 'temp_All',
            'linkurl' => 'templets_main.php'
        ),
        1  => array(
            'title' => Lang('templets_tagsource'),
            'description' => Lang('templets_tagsource_desc'),
            'purview' => 'temp_All',
            'linkurl' => 'templets_tagsource.php'
        ),
        2  =>  array(
            'title' => Lang('mytag_main'),
            'description' => Lang('mytag_main_desc'),
            'purview' => 'temp_MyTag',
            'linkurl' => 'mytag_main.php'
        ),
        3  =>  array(
            'title' => Lang('mytag_tag_guide'),
            'description' => Lang('mytag_tag_guide_desc'),
            'purview' => 'temp_Other',
            'linkurl' => 'mytag_tag_guide.php'
        ),
        4  =>  array(
            'title' => Lang('tag_test'),
            'description' => Lang('tag_test_desc'),
            'purview' => 'temp_Test',
            'linkurl' => 'tag_test.php'
        ),
    )
);
$actionSearch[9] = array(
    'toptitle' => Lang('system'),
    'title' => Lang('setting'),
    'description' => Lang('setting_desc'),
    'soniterm' => array(
        0  =>  array(
            'title' => Lang('sys_info'),
            'description' => Lang('sys_info_desc'),
            'purview' => 'sys_Edit',
            'linkurl' => 'sys_info.php'
        ),
        1  =>  array(
            'title' => Lang('sys_admin_user'),
            'description' => Lang('sys_admin_user_desc'),
            'purview' => 'sys_User',
            'linkurl' => 'sys_admin_user.php'
        ),
        2  =>  array(
            'title' => Lang('sys_group'),
            'description' => Lang('sys_group_desc'),
            'purview' => 'sys_Group',
            'linkurl' => 'sys_group.php'
        ),
        3  =>  array(
            'title' => Lang('log_list'),
            'description' => Lang('log_list_desc'),
            'purview' => 'sys_Log',
            'linkurl' => 'log_list.php'
        ),
        5  =>  array(
            'title' => Lang('sys_info_mark'),
            'description' => Lang('sys_info_mark_desc'),
            'purview' => 'sys_Edit',
            'linkurl' => 'sys_info_mark.php'
        ),
        6  =>  array(
            'title' => Lang('content_att'),
            'description' => Lang('content_att_desc'),
            'purview' => 'sys_Att',
            'linkurl' => 'content_att.php'
        ),
        7  =>  array(
            'title' => Lang('soft_config'),
            'description' => Lang('soft_config_desc'),
            'purview' => 'sys_SoftConfig',
            'linkurl' => 'soft_config.php'
        ),
        8  =>  array(
            'title' => Lang('article_string_mix'),
            'description' => Lang('article_string_mix_desc'),
            'purview' => 'sys_StringMix',
            'linkurl' => 'article_string_mix.php'
        ),
        9  =>  array(
            'title' => Lang('article_template_rand'),
            'description' => Lang('article_template_rand_desc'),
            'purview' => 'sys_StringMix',
            'linkurl' => 'article_template_rand.php'
        ),
        11  =>  array(
            'title' => Lang('sys_data'),
            'description' => Lang('sys_data_desc'),
            'purview' => 'sys_data',
            'linkurl' => 'sys_data.php'
        ),
        12  => DEDEBIZ_SAFE_MODE? null : array(
            'title' => Lang('sys_sql_query'),
            'description' => Lang('sys_sql_query_desc'),
            'purview' => 'sys_data',
            'linkurl' => 'sys_sql_query.php'
        ),
        14  =>  array(
            'title' => Lang('sys_safetest'),
            'description' => Lang('sys_safetest_desc'),
            'purview' => 'sys_verifies',
            'linkurl' => 'sys_safetest.php'
        ),
        15  =>  array(
            'title' => Lang('sys_repair'),
            'description' => Lang('sys_repair_desc'),
            'purview' => 'sys_verifies',
            'linkurl' => 'sys_repair.php'
        ),
    )
);
?>