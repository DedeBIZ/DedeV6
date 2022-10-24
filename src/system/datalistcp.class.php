<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 动态分页类
 *
 * @version        $Id: datalistcp.class.php 3 17:02 2010年7月9日Z tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC.'/dedetemplate.class.php');
// 分页说明
$lang_pre_page = '上页';
$lang_next_page = '下页';
$lang_index_page = '首页';
$lang_end_page = '末页';
$lang_record_number = '条';
$lang_page = '页';
$lang_total = '共';
/**
 * DataListCP
 *
 * @package DedeBIZ.Libraries
 */
class DataListCP
{
    var $dsql;
    var $tpl;
    var $pageNO;
    var $totalPage;
    var $totalResult;
    var $pagesize;
    var $getValues;
    var $sourceSql;
    var $isQuery;
    var $queryTime;
    /**
     *  用指定的文档id进行初始化
     *
     * @access    public
     * @param     string  $tplfile  模板文件
     * @return    string
     */
    function __construct($tplfile = '')
    {
        global $dsql;
        $this->sourceSql = '';
        $this->pagesize = 30;
        $this->queryTime = 0;
        $this->getValues = array();
        $this->isQuery = false;
        $this->totalResult = 0;
        $this->totalPage = 0;
        $this->pageNO = 0;
        $this->dsql = $dsql;
        $this->SetVar('ParseEnv', 'datalist');
        $this->tpl = new DedeTemplate();
        if ($GLOBALS['cfg_tplcache'] == 'N') {
            $this->tpl->isCache = false;
        }
        if ($tplfile != '') {
            $this->tpl->LoadTemplate($tplfile);
        }
    }
    /**
     *  兼容PHP4版本
     *
     * @access    private
     * @param     string  $tplfile  模板文件
     * @return    void
     */
    function DataListCP($tplfile = '')
    {
        $this->__construct($tplfile);
    }
    //设置SQL语句
    function SetSource($sql)
    {
        $this->sourceSql = $sql;
    }
    //设置模板
    //如果想要使用模板中指定的pagesize，必须在调用模板后才调用 SetSource($sql)
    function SetTemplate($tplfile)
    {
        $this->tpl->LoadTemplate($tplfile);
    }
    function SetTemplet($tplfile)
    {
        $this->tpl->LoadTemplate($tplfile);
    }
    /**
     *  对config参数及get参数等进行预处理
     *
     * @access    public
     * @return    void
     */
    function PreLoad()
    {
        global $totalresult, $pageno;
        if (empty($pageno) || preg_match("#[^0-9]#", $pageno)) {
            $pageno = 1;
        }
        if (empty($totalresult) || preg_match("#[^0-9]#", $totalresult)) {
            $totalresult = 0;
        }
        $this->pageNO = $pageno;
        $this->totalResult = $totalresult;
        if (isset($this->tpl->tpCfgs['pagesize'])) {
            $this->pagesize = $this->tpl->tpCfgs['pagesize'];
        }
        $this->totalPage = ceil($this->totalResult / $this->pagesize);
        if ($this->totalResult == 0) {
            $countQuery = preg_replace("#SELECT[ \r\n\t](.*)[ \r\n\t]FROM#is", 'SELECT COUNT(*) AS dd FROM', $this->sourceSql);
            $countQuery = preg_replace("#ORDER[ \r\n\t]{1,}BY(.*)#is", '', $countQuery);

            $row = $this->dsql->GetOne($countQuery);
            if (!is_array($row)) $row['dd'] = 0;
            $this->totalResult = isset($row['dd']) ? $row['dd'] : 0;
            $this->sourceSql .= " LIMIT 0,".$this->pagesize;
        } else {
            $this->sourceSql .= " LIMIT ".(($this->pageNO - 1) * $this->pagesize).",".$this->pagesize;
        }
    }
    //设置网址的Get参数键值
    function SetParameter($key, $value)
    {
        $this->getValues[$key] = $value;
    }
    //设置/获取文档相关的各种变量
    function SetVar($k, $v)
    {
        global $_vars;
        if (!isset($_vars[$k])) {
            $_vars[$k] = $v;
        }
    }
    function GetVar($k)
    {
        global $_vars;
        return isset($_vars[$k]) ? $_vars[$k] : '';
    }
    function XSSClean($val)
    {
        if (is_array($val)) {
            foreach ($val as $key => $v) {
                $val[$key] = $this->XSSClean($v);
            }
            return $val;
        }
        return $this->RemoveXss($val);
    }
    function RemoveXss($val)
    {
        global $cfg_soft_lang;
        if ($cfg_soft_lang == 'gb2312') $val = gb2utf8($val);
        $val = preg_replace('/([\x00-\x08|\x0b-\x0c|\x0e-\x19])/', '', $val);
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); //with a ;
            $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); //with a ;
        }
        $val = str_replace("`", "‘", $val);
        $val = str_replace("'", "‘", $val);
        $val = str_replace("\"", "“", $val);
        $val = str_replace(",", "，", $val);
        $val = str_replace("(", "（", $val);
        $val = str_replace(")", "）", $val);
        $val = str_replace("flink", "fl*&k", $val);

        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);
        $found = true;
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
                $val = preg_replace($pattern, $replacement, $val);
                if ($val_before == $val) {
                    $found = false;
                }
            }
        }
        $val = str_replace("fl*&k","flink", $val);
        if ($cfg_soft_lang == 'gb2312') $val = utf82gb($val);
        return $val;
    }
    //获取当前页数据列表
    function GetArcList($atts, $refObj = '', $fields = array())
    {
        $rsArray = array();
        $t1 = Exectime();
        if (!$this->isQuery) $this->dsql->Execute('dlist', $this->sourceSql);
        $i = 0;
        while ($arr = $this->dsql->GetArray('dlist')) {
            $i++;
            $rsArray[$i]  =  $this->XSSClean($arr);
            if ($i >= $this->pagesize) {
                break;
            }
        }
        $this->dsql->FreeResult('dlist');
        $this->queryTime = (Exectime() - $t1);
        return $rsArray;
    }
    //获取分页导航列表
    function GetPageList($atts, $refObj = '', $fields = array())
    {
        global $lang_pre_page, $lang_next_page, $lang_index_page, $lang_end_page, $lang_record_number, $lang_page, $lang_total;
        $prepage = $nextpage = $geturl = $hidenform = '';
        $purl = $this->GetCurUrl();
        $prepagenum = $this->pageNO - 1;
        $nextpagenum = $this->pageNO + 1;
        if (!isset($atts['listsize']) || preg_match("#[^0-9]#", $atts['listsize'])) {
            $atts['listsize'] = 5;
        }
        if (!isset($atts['listitem'])) {
            $atts['listitem'] = "info,index,end,pre,next,pageno";
        }
        $totalpage = ceil($this->totalResult / $this->pagesize);
        //echo " {$totalpage}=={$this->totalResult}=={$this->pagesize}";
        //无结果或只有一页的情况
        if ($totalpage <= 1 && $this->totalResult > 0) {
            return "<ul class='pagination justify-content-center'><li class='page-item d-none d-sm-block disabled'><span class='page-link'>{$lang_total}1{$lang_page}".$this->totalResult.$lang_record_number."</span></li></ul>";
        }
        if ($this->totalResult == 0) {
            return "<ul class='pagination justify-content-center'><li class='page-item d-none d-sm-block disabled'><span class='page-link'>{$lang_total}0{$lang_page}".$this->totalResult.$lang_record_number."</span></li></ul>";
        }
        $infos = "<li class='page-item d-none d-sm-block disabled'><span class='page-link'>{$lang_total}{$totalpage}{$lang_page}/{$this->totalResult}{$lang_record_number}</span></li>";
        if ($this->totalResult != 0) {
            $this->getValues['totalresult'] = $this->totalResult;
        }
        if (count($this->getValues) > 0) {
            foreach ($this->getValues as $key => $value) {
                $value = urlencode($value);
                $geturl .= "$key=$value"."&";
                $hidenform .= "<input type='hidden' name='$key' value='$value' />\n";
            }
        }
        $purl .= "?".$geturl;
        //获得上一页和下一页的链接
        if ($this->pageNO != 1) {
            $prepage .= "<li class='page-item'><a class='page-link' href='".$purl."pageno=$prepagenum'>$lang_pre_page</a></li> \n";
            $indexpage = "<li class='page-item'><a class='page-link' href='".$purl."pageno=1'>$lang_index_page</a></li> \n";
        } else {
            $indexpage = "<li class='page-item d-none d-sm-block disabled'><span class='page-link'>"."$lang_index_page \n"."</span></li>";
        }
        if ($this->pageNO != $totalpage && $totalpage > 1) {
            $nextpage .= "<li class='page-item'><a class='page-link' href='".$purl."pageno=$nextpagenum'>$lang_next_page</a></li> \n";
            $endpage = "<li class='page-item'><a class='page-link' href='".$purl."pageno=$totalpage'>$lang_end_page</a></li> \n";
        } else {
            $endpage = " <li class='page-item d-none d-sm-block disabled'><span class='page-link'>$lang_end_page</span></li> \n";
        }
        //获得数字链接
        $listdd = "";
        $total_list = $atts['listsize'] * 2 + 1;
        if ($this->pageNO >= $total_list) {
            $j = $this->pageNO - $atts['listsize'];
            $total_list = $this->pageNO + $atts['listsize'];
            if ($total_list > $totalpage) {
                $total_list = $totalpage;
            }
        } else {
            $j = 1;
            if ($total_list > $totalpage) {
                $total_list = $totalpage;
            }
        }
        for ($j; $j <= $total_list; $j++) {
            $listdd .= $j == $this->pageNO ? "<li class='page-item'><span class='page-link'>$j</span></li>\r\n" : "<li class='page-item'><a class='page-link' href='".$purl."pageno=$j'>".$j."</a></li>\n";
        }
        $plist = "<ul class='pagination justify-content-center'>\n";
        //info,index,end,pre,next,pageno,form
        if (preg_match("#info#i", $atts['listitem'])) {
            $plist .= $infos;
        }
        if (preg_match("#index#i", $atts['listitem'])) {
            $plist .= $indexpage;
        }
        if (preg_match("#pre#i", $atts['listitem'])) {
            $plist .= $prepage;
        }
        if (preg_match("#pageno#i", $atts['listitem'])) {
            $plist .= $listdd;
        }
        if (preg_match("#next#i", $atts['listitem'])) {
            $plist .= $nextpage;
        }
        if (preg_match("#end#i", $atts['listitem'])) {
            $plist .= $endpage;
        }
        if (preg_match("#form#i", $atts['listitem'])) {
            $plist .= " <form name='pagelist' action='".$this->GetCurUrl()."' style='float:left' class='pagelistform'>$hidenform";
            if ($totalpage > $total_list) {
                $plist .= "<input type='text' name='pageno' style='padding:0;width:30px;height:18px' />\r\n";
                $plist .= "<input type='submit' name='plistgo' value='GO' style='padding:0;width:30px;height:22px' />\r\n";
            }
            $plist .= "</form>\n";
        }
        $plist .= "</ul>\n";
        return $plist;
    }
    //获得当前网址
    function GetCurUrl()
    {
        if (!empty($_SERVER["REQUEST_URI"])) {
            $nowurl = $_SERVER["REQUEST_URI"];
            $nowurls = explode("?", $nowurl);
            $nowurl = $nowurls[0];
        } else {
            $nowurl = $_SERVER["PHP_SELF"];
        }
        return $nowurl;
    }
    //关闭
    function Close()
    {
    }
    //显示数据
    function Display()
    {
        $this->PreLoad();
        //在PHP4中，对象引用必须放在display之前，放在其它位置中无效
        $this->tpl->SetObject($this);
        $this->tpl->Display();
    }
    //保存为HTML
    function SaveTo($filename)
    {
        $this->tpl->SaveTo($filename);
    }
}
?>