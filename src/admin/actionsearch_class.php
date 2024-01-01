<?php
/**
 * @version        $id:actionsearch_class.php 8:26 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
class ActionSearch
{
    var $keyword;
    var $asarray = array();
    var $result    = array();
    function __construct($keyword)
    {
        $this->asarray = $this->GetSearchstr();
        $this->keyword = $keyword;
    }
    //初始化系统
    function ActionSearch($keyword)
    {
        $this->__construct($keyword);
    }
    function GetSearchstr()
    {
        require_once(dirname(__FILE__)."/inc/inc_action_info.php");
        return is_array($actionSearch) ? $actionSearch : array();
    }
    function search()
    {
        $this->searchkeyword();
        return $this->result;
    }
    /**
     *  遍历功能配置项进行关键词匹配
     *
     * @return    void
     */
    function searchkeyword()
    {
        $i = 0; //数组序列索引
        foreach ($this->asarray as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            //对二级项目进行匹配
            if (is_array($this->asarray[$key]['soniterm'])) {
                foreach ($this->asarray[$key]['soniterm'] as $k => $val) {
                    //进行权限判断
                    if (TestPurview($val['purview'])) {
                        //如果有操作权限
                        if ($this->_strpos($val['title'], $this->keyword) !== false) {
                            //一级项目匹配
                            $this->result[$i]['toptitle'] = $this->redColorKeyword($this->asarray[$key]['toptitle']);
                            $this->result[$i]['title'] = $this->redColorKeyword($this->asarray[$key]['title']);
                            //二级项目匹配
                            $this->result[$i]['soniterm'][] = $this->redColorKeyword($val);
                        }
                    }
                }
            }
            $i++;
        }
    }
    /**
     *  加亮关键词
     *
     * @access    public
     * @param     string  $text  关键词
     * @return    string
     */
    function redColorKeyword($text)
    {
        if (is_array($text)) {
            foreach ($text as $key => $value) {
                if ($key == 'title') {
                    $text[$key] = str_replace($this->keyword, '<b class="text-danger">'.$this->keyword.'</b>', $text[$key]);
                }
            }
        } else {
            $text = str_replace($this->keyword, '<b class="text-danger">'.$this->keyword.'</b>', $text);
        }
        return $text;
    }
    function _strpos($string, $find)
    {
        if (function_exists('stripos'))  return stripos($string, $find);
        return strpos($string, $find);
    }
}
?>