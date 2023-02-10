<?php
/**
 * 后台操作记录信息
 *
 * @version        $id:inc_action_info.php 2 14:55 2010-11-11 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../config.php");
$cuserLogin = new userLogin();
//后台功能操作配置项
$actionSearch[0] = array(
    'title'  => '常规操作',
    'description' => '常规功能操作',
    'soniterm' =>  array(
        0  =>  array(
            'title' => '网站栏目管理',
            'description' => '网站所有栏目管理',
            'purview' => 't_List,t_AccList',
            'linkurl' => 'catalog_main.php'
        ),
        1  =>  array(
            'title' => '待审核的文档',
            'description' => '所有文档模型发表待审核文档列表',
            'purview' => 'a_Check,a_AccCheck',
            'linkurl' => 'content_list.php?arcrank=-1'
        ),
        2  =>  array(
            'title' => '我发布的文档',
            'description' => '当前后台登录所发表的文档',
            'purview' => 'a_List,a_AccList,a_MyList',
            'linkurl' => 'content_list.php?mid=$cuserLogin->userID'
        ),
        3  =>  array(
            'title' => '自定义文档属性',
            'description' => '网站自定义文档属性',
            'purview' => 'sys_Att',
            'linkurl' => 'content_att.php'
        ),
        4  =>  array(
            'title' => '评论管理',
            'description' => '网站所有评论管理',
            'purview' => 'sys_Feedback',
            'linkurl' => 'feedback_main.php'
        ),
        5  =>  array(
            'title' => '文档回收站',
            'description' => '系统配置变量的核心设置中开启了文档回收站是否开启功能，后台删除的文档会存放在此处',
            'purview' => 'a_List,a_AccList,a_MyList',
            'linkurl' => 'recycling.php'
        )
    )
);
$actionSearch[1] = array(
    'title' => '文档管理',
    'description' => '网站文档管理',
    'soniterm' => array(
        0  =>  array(
            'title' => '专题管理',
            'description' => '网站所有专题管理',
            'purview' => 'spec_New',
            'linkurl' => 'content_s_list.php'
        ),
        1  =>  array(
            'title' => '标签管理',
            'description' => '网站所有标签管理',
            'purview' => 'sys_Keyword',
            'linkurl' => 'tags_main.php'
        ),
    )
);
$actionSearch[2] = array(
    'title' => '附件管理',
    'description' => '网站附件管理',
    'soniterm' => array(
        0  =>  array(
            'title' => '上传新文件',
            'description' => '通过这可以上传图片、FLASH、视频音频、附件等其它附件',
            'purview' => '',
            'linkurl' => 'media_add.php'
        ),
        1  =>  array(
            'title' => '附件数据管理',
            'description' => '列出所有上传的附件',
            'purview' => 'sys_Upload,sys_MyUpload',
            'linkurl' => 'media_main.php'
        ),
        2  =>  array(
            'title' => '文件式管理器',
            'description' => '应用文件浏览的模式进行附件的管理',
            'purview' => 'plus_文件管理器',
            'linkurl' => 'media_main.php?dopost=filemanager'
        ),
    )
);
$actionSearch[3] = array(
    'title' => '文档模型',
    'description' => '所有文档模型管理',
    'soniterm' => array(
        0  =>  array(
            'title' => '文档模型管理',
            'description' => '网站文档、图片、软件、专题、分类等模型就行管理，也可以创建新模型',
            'purview' => 'c_List',
            'linkurl' => 'mychannel_main.php'
        ),
        1  =>  array(
            'title' => '文档单页管理',
            'description' => '创建和管理单页面',
            'purview' => 'temp_One',
            'linkurl' => 'templets_one.php'
        ),
        2  =>  array(
            'title' => '联动类别管理',
            'description' => '创建和管理所有的联动',
            'purview' => 'c_Stepseclect',
            'linkurl' => 'stepselect_main.php?dopost=filemanager'
        ),
        3  =>  array(
            'title' => '自由列表管理',
            'description' => '创建不同的列表形式',
            'purview' => 'c_List',
            'linkurl' => 'freelist_main.php'
        ),
        4  =>  array(
            'title' => '自定义表单',
            'description' => '创建和管理自定义表单',
            'purview' => 'c_List',
            'linkurl' => 'diy_main.php'
        ),
    )
);
$actionSearch[4] = array(
    'title' => '批量维护',
    'description' => '网站数据维护',
    'soniterm' => array(
        0  =>  array(
            'title' => '文档批量维护',
            'description' => '某个栏目或者全部栏目的文档进行批量审核文档、更新网页、移动文档、删除文档',
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'content_batch_up.php'
        ),
        1  =>  array(
            'title' => '搜索关键词维护',
            'description' => '所有搜索关键词管理',
            'purview' => 'sys_Keyword',
            'linkurl' => 'search_keywords_main.php?dopost=filemanager'
        ),
        2  =>  array(
            'title' => '文档关键词维护',
            'description' => '所有文档关键词批量维护',
            'purview' => 'sys_Keyword',
            'linkurl' => 'article_keywords_main.php'
        ),
        3  =>  array(
            'title' => '文档重复检测',
            'description' => '网站重复标题文档处理',
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'article_test_same.php'
        ),
        4  =>  array(
            'title' => '自动摘要分页',
            'description' => '更新没有填写描述的文档或更新没分页的文档的自动分页标识',
            'purview' => 'sys_Keyword',
            'linkurl' => 'article_description_main.php'
        ),
        5  =>  array(
            'title' => '数据库字段替换',
            'description' => '网站数据库字段批量替换',
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'sys_data_replace.php'
        ),
    )
);
$actionSearch[5] = array(
    'title' => '会员管理',
    'description' => '网站所有会员管理',
    'soniterm' => array(
        0  =>  array(
            'title' => '所有会员列表',
            'description' => '所有会员修改删除，查看会员文档以及提升管理员等操作',
            'purview' => 'member_List',
            'linkurl' => 'member_main.php'
        ),
        1  =>  array(
            'title' => '会员级别设置',
            'description' => '会员级别设置，通过不同会员的浏览权限来对会员级别进行一个扩展',
            'purview' => 'member_Type',
            'linkurl' => 'member_rank.php'
        ),
        2  =>  array(
            'title' => '积分头衔设置',
            'description' => '会员积分等级设置，根据活动积分对会员进行头衔划分',
            'purview' => 'member_Type',
            'linkurl' => 'member_scores.php'
        ),
        4  =>  array(
            'title' => '会员短信管理',
            'description' => '会员之间发送的短消息管理，其中包含群发短消息和对单个会员发送短消息两种',
            'purview' => 'member_Type',
            'linkurl' => 'member_pm.php'
        ),
    )
);
$actionSearch[6] = array(
    'title' => '支付工具',
    'description' => '财务相关设置，包含积分，商店订单等操作',
    'soniterm' => array(
        0  =>  array(
            'title' => '积分产品管理',
            'description' => '网站积分产品管理，可以在这里生成积分以及查看积分的当前状态',
            'purview' => 'sys_Data',
            'linkurl' => 'cards_manage.php'
        ),
        1  =>  array(
            'title' => '积分产品分类',
            'description' => '网站积分产品分类，可以添加不同点数的积分产品类型',
            'purview' => 'sys_Data',
            'linkurl' => 'cards_type.php'
        ),
        2  =>  array(
            'title' => '会员产品分类',
            'description' => '会员类产品型划分，对会员产品进行定义',
            'purview' => 'sys_Data',
            'linkurl' => 'member_type.php'
        ),
        3  =>  array(
            'title' => '会员消费记录',
            'description' => '会员消费记录，同时可以查看消费充值订单的付款情况',
            'purview' => 'sys_Data',
            'linkurl' => 'member_operations.php'
        ),
        5  =>  array(
            'title' => '支付接口设置',
            'description' => '网站支付接口设置',
            'purview' => 'sys_Data',
            'linkurl' => 'sys_payment.php'
        ),
    )
);
$actionSearch[7] = array(
    'title' => '更新任务',
    'description' => '一键生成静态管理',
    'soniterm' => array(
        0  =>  array(
            'title' => '更新网站',
            'description' => '生成所有静态页面',
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_all.php'
        ),
        1  =>  array(
            'title' => '更新首页',
            'description' => '生成网站首页面',
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_homepage.php'
        ),
        2  =>  array(
            'title' => '更新栏目',
            'description' => '生成栏目页面',
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_list.php'
        ),
        3  =>  array(
            'title' => '更新文档',
            'description' => '生成文档页面',
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_archives.php'
        ),
        4  =>  array(
            'title' => '更新专题',
            'description' => '生成专题页面',
            'purview' => 'sys_MakeHtml',
            'linkurl' => 'makehtml_spec.php'
        ),
        5  =>  array(
            'title' => '更新缓存',
            'description' => '更新栏目缓存、更新枚举缓存、清理文档调用缓存、清理过期会员浏览历史、删除过期会员短信、删除过期流量统计',
            'purview' => 'sys_ArcBatch',
            'linkurl' => 'sys_cache_up.php'
        ),
    )
);
$actionSearch[8] = DEDEBIZ_SAFE_MODE? null : array(
    'title' => '模板管理',
    'description' => '网站主题模板管理',
    'soniterm' => array(
        0  => array(
            'title' => '默认模板管理',
            'description' => '网站正在采用的模板文件管理',
            'purview' => 'temp_All',
            'linkurl' => 'templets_main.php'
        ),
        1  => array(
            'title' => '标签源码管理',
            'description' => '系统标签文件编辑修改',
            'purview' => 'temp_All',
            'linkurl' => 'templets_tagsource.php'
        ),
        2  =>  array(
            'title' => '自定义宏标记',
            'description' => '所有自定义标记管理',
            'purview' => 'temp_MyTag',
            'linkurl' => 'mytag_main.php'
        ),
        3  =>  array(
            'title' => '智能标记向导',
            'description' => '根据需要生成相应的调用标签',
            'purview' => 'temp_Other',
            'linkurl' => 'mytag_tag_guide.php'
        ),
        4  =>  array(
            'title' => '全局标记测试',
            'description' => '全局标签调用测试',
            'purview' => 'temp_Test',
            'linkurl' => 'tag_test.php'
        ),
    )
);
$actionSearch[9] = array(
    'title' => '系统设置',
    'description' => '后台系统设置',
    'soniterm' => array(
        0  =>  array(
            'title' => '系统配置变量',
            'description' => '包括站点设置、核心设置、附件设置、会员设置、互动设置、性能选项、其它选项、添加新变量等分类设置',
            'purview' => 'sys_Edit',
            'linkurl' => 'sys_info.php'
        ),
        1  =>  array(
            'title' => '系统用户管理',
            'description' => '网站管理员管理',
            'purview' => 'sys_User',
            'linkurl' => 'sys_admin_user.php'
        ),
        2  =>  array(
            'title' => '用户组设置',
            'description' => '网站管理员组别的划分',
            'purview' => 'sys_Group',
            'linkurl' => 'sys_group.php'
        ),
        3  =>  array(
            'title' => '系统日志管理',
            'description' => '登录后台的管理员操作进行记录',
            'purview' => 'sys_Log',
            'linkurl' => 'log_list.php'
        ),
        5  =>  array(
            'title' => '图片水印设置',
            'description' => '上传图片添加的水印配置',
            'purview' => 'sys_Edit',
            'linkurl' => 'sys_info_mark.php'
        ),
        // 5  =>  array(
        //     'title' => '云服务设置',
        //     'description' => '主流云服务平台支持',
        //     'purview' => 'sys_Edit',
        //     'linkurl' => 'sys_cloud.php'
        // ),
        6  =>  array(
            'title' => '软件下载设置',
            'description' => '软件下载连接显示方式，下载方式，镜像服务器等等进行配置',
            'purview' => 'sys_SoftConfig',
            'linkurl' => 'soft_config.php'
        ),
        7  =>  array(
            'title' => '防采集串混淆',
            'description' => '网站防采集串混淆',
            'purview' => 'sys_StringMix',
            'linkurl' => 'article_string_mix.php'
        ),
        8  =>  array(
            'title' => '数据库备份还原',
            'description' => '数据库备份和还原',
            'purview' => 'sys_data',
            'linkurl' => 'sys_data.php'
        ),
        9  => DEDEBIZ_SAFE_MODE? null : array(
            'title' => 'SQL命令工具',
            'description' => '数据表执行单行或者多行的SQL语句',
            'purview' => 'sys_data',
            'linkurl' => 'sys_sql_query.php'
        ),
        10  =>  array(
            'title' => '文件扫描工具',
            'description' => '以DedeBIZ开发模式为标准对现有的文件进行扫描判断',
            'purview' => 'sys_verifies',
            'linkurl' => 'sys_safetest.php'
        ),
        11  =>  array(
            'title' => '系统修复工具',
            'description' => '手动和自动升级错误处理',
            'purview' => 'sys_verifies',
            'linkurl' => 'sys_repair.php'
        ),
    )
);
?>