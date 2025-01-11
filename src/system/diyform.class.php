<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 自定义表单
 *
 * @version        $id:diyform.class.php 10:31 2010年7月6日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once DEDEINC.'/dedetag.class.php';
require_once DEDEINC.'/customfields.func.php';
class diyform
{
    var $diyid;
    var $db;
    var $info;
    var $name;
    var $table;
    var $public;
    var $listTemplate;
    var $viewTemplate;
    var $postTemplate;
    function diyform($diyid)
    {
        $this->__construct($diyid);
    }
    /**
     *  析构函数
     *
     * @access    public
     * @param     string  $diyid  自定义表单ID
     * @return    string
     */
    function __construct($diyid)
    {
        $this->diyid = $diyid;
        $this->db = $GLOBALS['dsql'];
        $query = "SELECT * FROM `#@__diyforms` WHERE diyid='{$diyid}'";
        $diyinfo = $this->db->GetOne($query);
        if (!is_array($diyinfo)) {
            showMsg('参数不正确，该自定义表单不存在', 'javascript:;');
            exit();
        }
        $this->info = stripslashes($diyinfo['info']);
        $this->name = $diyinfo['name'];
        $this->table = $diyinfo['table'];
        $this->public = $diyinfo['public'];
        $this->listTemplate = $diyinfo['listtemplate'] != '' && file_exists(DEDETEMPLATE.'/apps/'.$diyinfo['listtemplate']) ? $diyinfo['listtemplate'] : 'list_diyform.htm';
        $this->viewTemplate = $diyinfo['viewtemplate'] != '' && file_exists(DEDETEMPLATE.'/apps/'.$diyinfo['viewtemplate']) ? $diyinfo['viewtemplate'] : 'view_diyform.htm';;
        $this->postTemplate = $diyinfo['posttemplate'] != '' && file_exists(DEDETEMPLATE.'/apps/'.$diyinfo['posttemplate']) ? $diyinfo['posttemplate'] : 'post_diyform.htm';;
    }
    /**
     *  获取表单
     *
     * @access    public
     * @param     string  $type  类型
     * @param     string  $value  值
     * @param     string  $admintype  管理类型
     * @return    string
     */
    function getForm($type = 'post', $value = '', $admintype = 'diy')
    {
        global $cfg_cookie_encode;
        $dtp = new DedeTagParse();
        $dtp->SetNameSpace("field", "<", ">");
        $dtp->LoadSource($this->info);
        $formstring = '';
        $formfields = '';
        $func = $type == 'post' ? 'GetFormItem' : 'GetFormItemValue';
        if (is_array($dtp->CTags)) {
            foreach ($dtp->CTags as $tagid => $tag) {
                if ($tag->GetAtt('autofield')) {
                    if ($type == 'post') {
                        $formstring .= $func($tag, $admintype);
                    } else {
                        $formstring .= $func($tag, dede_htmlspecialchars($value[$tag->GetName()]), $admintype);
                    }
                    $formfields .= $formfields == '' ? $tag->GetName().','.$tag->GetAtt('type') : ';'.$tag->GetName().','.$tag->GetAtt('type');
                }
            }
        }
        $formstring .= "<input type=\"hidden\" name=\"dede_fields\" value=\"".$formfields."\" />\n";
        $formstring .= "<input type=\"hidden\" name=\"dede_fieldshash\" value=\"".md5($formfields.$cfg_cookie_encode)."\" />";
        return $formstring;
    }
    /**
     *  获取字段列表
     *
     * @access    public
     * @return    array
     */
    function getFieldList()
    {
        $dtp = new DedeTagParse();
        $dtp->SetNameSpace("field", "<", ">");
        $dtp->LoadSource($this->info);
        $fields = array();
        if (is_array($dtp->CTags)) {
            foreach ($dtp->CTags as $tagid => $tag) {
                $fields[$tag->GetName()] = array($tag->GetAtt('itemname'), $tag->GetAtt('type'));
            }
        }
        return $fields;
    }
}
?>