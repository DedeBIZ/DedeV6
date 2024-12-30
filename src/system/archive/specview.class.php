<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 专题视图
 *
 * @version        $id:specview.class.php 18:17 2010年7月7日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC."/typelink/typelink.class.php");
require_once(DEDEINC."/channelunit.class.php");
@set_time_limit(0);
class SpecView
{
    var $dsql;
    var $dtp;
    var $dtp2;
    var $TypeID;
    var $TypeLink;
    var $PageNo;
    var $TotalPage;
    var $TotalResult;
    var $pagesize;
    var $ChannelUnit;
    var $ListType;
    var $TempInfos;
    var $Fields;
    var $StartTime;
    var $remoteDir;
    /**
     *  php5构造函数
     *
     * @access    public
     * @param     int  $starttime  开始时间
     * @return    string
     */
    function __construct($starttime = 0)
    {
        $this->TypeID = 0;
        $this->dsql = $GLOBALS['dsql'];
        $this->dtp = new DedeTagParse();
        $this->dtp->SetRefObj($this);
        $this->dtp->SetNameSpace("dede", "{", "}");
        $this->dtp2 = new DedeTagParse();
        $this->dtp2->SetNameSpace("field", "[", "]");
        $this->TypeLink = new TypeLink(0);
        $this->ChannelUnit = new ChannelUnit(-1);
        $this->remoteDir = '';
        //设置一些全局参数的值
        foreach ($GLOBALS['PubFields'] as $k => $v) {
            $this->Fields[$k] = $v;
        }
        if ($starttime == 0) {
            $this->StartTime = 0;
        } else {
            $this->StartTime = GetMkTime($starttime);
        }
        $this->CountRecord();
        $tempfile = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir']."/".$GLOBALS['cfg_df_style']."/list_spec.htm";
        if (!file_exists($tempfile) || !is_file($tempfile)) {
            echo "专题主题模板文件不存在，无法更新专题";
            exit();
        }
        $this->dtp->LoadTemplate($tempfile);
        $this->TempInfos['tags'] = $this->dtp->CTags;
        $this->TempInfos['source'] = $this->dtp->SourceString;
        $ctag = $this->dtp->GetTag("page");
        if (!is_object($ctag)) {
            $this->pagesize = 20;
        } else {
            if ($ctag->GetAtt("pagesize") != "") {
                $this->pagesize = $ctag->GetAtt("pagesize");
            } else {
                $this->pagesize = 20;
            }
        }
        $this->TotalPage = ceil($this->TotalResult / $this->pagesize);
    }
    //php4构造函数
    function SpecView($starttime = 0)
    {
        $this->__construct($starttime);
    }
    //关闭相关资源
    function Close()
    {
    }
    /**
     *  统计列表里的记录
     *
     * @access    private
     * @return    void
     */
    function CountRecord()
    {
        $this->TotalResult = -1;
        if (isset($GLOBALS['TotalResult'])) {
            $this->TotalResult = $GLOBALS['TotalResult'];
        }
        if (isset($GLOBALS['PageNo'])) {
            $this->PageNo = $GLOBALS['PageNo'];
        } else {
            $this->PageNo = 1;
        }
        if ($this->TotalResult == -1) {
            if ($this->StartTime > 0) {
                $timesql = " AND #@__archives.senddate>'".$this->StartTime."'";
            } else {
                $timesql = '';
            }
            $row = $this->dsql->GetOne("SELECT count(*) AS dd FROM `#@__archives` WHERE `#@__archives`.arcrank > -1 AND channel=-1 $timesql");
            if (is_array($row)) {
                $this->TotalResult = $row['dd'];
            } else {
                $this->TotalResult = 0;
            }
        }
    }
    /**
     *  显示列表
     *
     * @access    public
     * @return    void
     */
    function Display()
    {
        if (isset($this->TypeLink->TypeInfos) && ($this->TypeLink->TypeInfos['ispart'] == 1
            || $this->TypeLink->TypeInfos['ispart'] == 2)) {
            $this->DisplayPartTemplets();
        }
        $this->ParseTempletsFirst();
        foreach ($this->dtp->CTags as $tagid => $ctag) {
            if ($ctag->GetName() == "list") {
                $limitstart = ($this->PageNo - 1) * $this->pagesize;
                $row = $this->pagesize;
                if (trim($ctag->GetInnerText()) == "") {
                    $InnerText = GetSysTemplets("list_fulllist.htm");
                } else {
                    $InnerText = trim($ctag->GetInnerText());
                }
                $this->dtp->Assign(
                    $tagid,
                    $this->GetArcList(
                        $limitstart,
                        $row,
                        $ctag->GetAtt("col"),
                        $ctag->GetAtt("titlelen"),
                        $ctag->GetAtt("infolen"),
                        $ctag->GetAtt("imgwidth"),
                        $ctag->GetAtt("imgheight"),
                        $ctag->GetAtt("listtype"),
                        $ctag->GetAtt("orderby"),
                        $InnerText,
                        $ctag->GetAtt("tablewidth")
                    )
                );
            } else if ($ctag->GetName() == "pagelist") {
                $list_len = trim($ctag->GetAtt("listsize"));
                if ($list_len == "") {
                    $list_len = 3;
                }
                $this->dtp->Assign($tagid, $this->GetPageListDM($list_len));
            }
        }
        $this->dtp->Display();
    }
    function DisplayPartTemplets(){
        
    }
    /**
     *  开始创建列表
     *
     * @access    public
     * @param     int  $isremote  是否远程
     * @return    string
     */
    function MakeHtml($isremote = 0)
    {
        //初步给固定值的标记赋值
        $this->ParseTempletsFirst();
        $totalpage = ceil($this->TotalResult / $this->pagesize);
        if ($totalpage == 0) {
            $totalpage = 1;
        }
        CreateDir($GLOBALS['cfg_special']);
        $murl = '';
        for ($this->PageNo = 1; $this->PageNo <= $totalpage; $this->PageNo++) {
            foreach ($this->dtp->CTags as $tagid => $ctag) {
                if ($ctag->GetName() == "list") {
                    $limitstart = ($this->PageNo - 1) * $this->pagesize;
                    $row = $this->pagesize;
                    if (trim($ctag->GetInnerText()) == "") {
                        $InnerText = GetSysTemplets("spec_list.htm");
                    } else {
                        $InnerText = trim($ctag->GetInnerText());
                    }
                    $this->dtp->Assign(
                        $tagid,
                        $this->GetArcList(
                            $limitstart,
                            $row,
                            $ctag->GetAtt("col"),
                            $ctag->GetAtt("titlelen"),
                            $ctag->GetAtt("infolen"),
                            $ctag->GetAtt("imgwidth"),
                            $ctag->GetAtt("imgheight"),
                            "spec",
                            $ctag->GetAtt("orderby"),
                            $InnerText,
                            $ctag->GetAtt("tablewidth")
                        )
                    );
                } else if ($ctag->GetName() == "pagelist") {
                    $list_len = trim($ctag->GetAtt("listsize"));
                    if ($list_len == "") {
                        $list_len = 3;
                    }
                    $this->dtp->Assign($tagid, $this->GetPageListST($list_len));
                }
            } //End foreach
            $makeFile = $GLOBALS['cfg_special']."/spec_".$this->PageNo.$GLOBALS['art_shortname'];
            $murl = $makeFile;
            $makeFile = $GLOBALS['cfg_basedir'].$makeFile;
            $this->dtp->SaveTo($makeFile);
        }
        copy($GLOBALS['cfg_basedir'].$GLOBALS['cfg_special']."/spec_1".$GLOBALS['art_shortname'], $GLOBALS['cfg_basedir'].$GLOBALS['cfg_special']."/index.html");
        $murl = $GLOBALS['cfg_special']."/index.html";
        return $murl;
    }
    /**
     *  解析模板，对固定的标记进行初始给值
     *
     * @access    private
     * @return    void
     */
    function ParseTempletsFirst()
    {
        MakeOneTag($this->dtp, $this);
    }
    /**
     *  获取文档列表
     *
     * @access    public
     * @param     int  $limitstart  限制开始  
     * @param     int  $row  行数 
     * @param     int  $col  列数
     * @param     int  $titlelen  标题长度
     * @param     int  $infolen  描述长度
     * @param     int  $imgwidth  图片宽度
     * @param     int  $imgheight  图片高度
     * @param     string  $listtype  列表类型
     * @param     string  $orderby  排列顺序
     * @param     string  $innertext  底层模板
     * @param     string  $tablewidth  表格宽度
     * @return    string
     */
    function GetArcList(
        $limitstart = 0,
        $row = 10,
        $col = 1,
        $titlelen = 30,
        $infolen = 250,
        $imgwidth = 120,
        $imgheight = 90,
        $listtype = "all",
        $orderby = "default",
        $innertext = "",
        $tablewidth = "100"
    ) {
        $typeid = $this->TypeID;
        if ($row == "") {
            $row = 10;
        }
        if ($limitstart == "") {
            $limitstart = 0;
        }
        if ($titlelen == "") {
            $titlelen = 30;
        }
        if ($infolen == "") {
            $infolen = 250;
        }
        if ($imgwidth == "") {
            $imgwidth = 120;
        }
        if ($imgheight == "") {
            $imgheight = 120;
        }
        if ($listtype == "") {
            $listtype = "all";
        }
        if ($orderby == "") {
            $orderby = "default";
        } else {
            $orderby = strtolower($orderby);
        }
        $tablewidth = str_replace("%", "", $tablewidth);
        if ($tablewidth == "") {
            $tablewidth = 100;
        }
        if ($col == "") {
            $col = 1;
        }
        $colWidth = ceil(100 / $col);
        $tablewidth = $tablewidth."%";
        $colWidth = $colWidth."%";
        $innertext = trim($innertext);
        if ($innertext == "") {
            $innertext = GetSysTemplets("spec_list.htm");
        }
        //按不同情况设定SQL条件
        $orwhere = " arc.arcrank > -1 AND arc.channel = -1 ";
        if ($this->StartTime > 0) {
            $orwhere .= " AND arc.senddate>'".$this->StartTime."'";
        }
        //排序方式
        $ordersql = '';
        if ($orderby == 'senddate') {
            $ordersql = " ORDER BY arc.senddate DESC";
        } else if ($orderby == 'pubdate') {
            $ordersql = " ORDER BY arc.pubdate DESC";
        } else if ($orderby == 'id') {
            $ordersql = " ORDER BY arc.id DESC";
        } else {
            $ordersql = " ORDER BY arc.sortrank DESC";
        }
        $query = "SELECT arc.*,tp.typedir,tp.typename,tp.isdefault,arc.money,tp.defaultname,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath,mb.uname,mb.face,mb.userid FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id LEFT JOIN `#@__member` mb on arc.mid = mb.mid WHERE $orwhere $ordersql LIMIT $limitstart,$row ";
        $this->dsql->SetQuery($query);
        $this->dsql->Execute('al');
        $artlist = '';
        if ($col > 1) {
            $artlist = "<table width='$tablewidth'>";
        }
        $this->dtp2->LoadSource($innertext);
        for ($i = 0; $i < $row; $i++) {
            if ($col > 1) {
                $artlist .= "<tr>";
            }
            for ($j = 0; $j < $col; $j++) {
                if ($col > 1) {
                    $artlist .= "<td width='$colWidth'>";
                }
                if ($row = $this->dsql->GetArray("al")) {
                    //处理一些特殊字段
                    $row["description"] = cn_substr($row["description"], $infolen);
                    $row["title"] = cn_substr($row["title"], $titlelen);
                    $row["id"] =  $row["id"];
                    if ($row['litpic'] == '-' || $row['litpic'] == '') {
                        $row['litpic'] = $GLOBALS['cfg_cmspath'].'/static/web/img/thumbnail.jpg';
                    }
                    if (!preg_match("/^http:\/\//", $row['litpic']) && $GLOBALS['cfg_multi_site'] == 'Y') {
                        $row['litpic'] = $GLOBALS['cfg_mainsite'].$row['litpic'];
                    }
                    $row['picname'] = $row['litpic'];
                    $row["arcurl"] = GetFileUrl(
                        $row["id"],
                        $row["typeid"],
                        $row["senddate"],
                        $row["title"],
                        $row["ismake"],
                        $row["arcrank"],
                        $row["namerule"],
                        $row["typedir"],
                        $row["money"],
                        $row['filename'],
                        $row["moresite"],
                        $row["siteurl"],
                        $row["sitepath"]
                    );
                    $row["typeurl"] = GetTypeUrl($row["typeid"], $row["typedir"], $row["isdefault"], $row["defaultname"], $row["ispart"], $row["namerule2"], $row["moresite"], $row["siteurl"], $row["sitepath"]);
                    $row["info"] = $row["description"];
                    $row["filename"] = $row["arcurl"];
                    $row["stime"] = GetDateMK($row["pubdate"]);
                    $row["textlink"] = "<a href='".$row["filename"]."'>".$row["title"]."</a>";
                    $row["typelink"] = "[<a href='".$row["typeurl"]."'>".$row["typename"]."</a>]";
                    $row["imglink"] = "<a href='".$row["filename"]."'><img src='".$row["picname"]."' width='$imgwidth' height='$imgheight'></a>";
                    $row["image"] = "<img src='".$row["picname"]."' width='$imgwidth' height='$imgheight'>";
                    $row['plusurl'] = $row['phpurl'] = $GLOBALS['cfg_phpurl'];
                    $row['memberurl'] = $GLOBALS['cfg_memberurl'];
                    $row['face'] = empty($row['face'])? $GLOBALS['cfg_mainsite'].'/static/web/img/admin.png' : $row['face'];
                    $row['userurl'] = $GLOBALS['cfg_memberurl'].'/index.php?uid='.$row['userid'];
                    $row['templeturl'] = $GLOBALS['cfg_templeturl'];
                    //编译附加表里的数据
                    foreach ($this->ChannelUnit->ChannelFields as $k => $arr) {
                        if (isset($row[$k])) {
                            $row[$k] = $this->ChannelUnit->MakeField($k, $row[$k]);
                        }
                    }
                    if (is_array($this->dtp2->CTags)) {
                        foreach ($this->dtp2->CTags as $k => $ctag) {
                            if ($ctag->GetName() == 'array') {
                                //传递整个数组，在runphp模式中有特殊作用
                                $this->dtp2->Assign($k, $row);
                            } else {
                                if (isset($row[$ctag->GetName()])) {
                                    $this->dtp2->Assign($k, $row[$ctag->GetName()]);
                                } else {
                                    $this->dtp2->Assign($k, '');
                                }
                            }
                        }
                    }
                    $artlist .= $this->dtp2->GetResult();
                }//if hasRow
                else {
                    $artlist .= '';
                }
                if ($col > 1) {
                    $artlist .= "</td>";
                }
            }//Loop Col
            if ($col > 1) {
                $artlist .= "</tr>";
            }
        }//Loop Line
        if ($col > 1) {
            $artlist .= "</table>";
        }
        $this->dsql->FreeResult("al");
        return $artlist;
    }
    /**
     *  获取静态的分页列表
     *
     * @access    public
     * @param     int  $list_len  列表宽度
     * @return    string
     */
    function GetPageListST($list_len)
    {
        $prepage = '';
        $nextpage = '';
        $prepagenum = $this->PageNo - 1;
        $nextpagenum = $this->PageNo + 1;
        if ($list_len == "" || preg_match("/[^0-9]/", $list_len)) {
            $list_len = 3;
        }
        $totalpage = ceil($this->TotalResult / $this->pagesize);
        if ($totalpage <= 1 && $this->TotalResult > 0) {
            return "<li class='page-item disabled'><span class='page-link'>1页".$this->TotalResult."条</span></li>";
        }
        if ($this->TotalResult == 0) {
            return "<li class='page-item disabled'><span class='page-link'>0页".$this->TotalResult."条</span></li>";
        }
        $purl = $this->GetCurUrl();
        $tnamerule = "spec_";
        //获得上页和下页的链接
        if ($this->PageNo != 1) {
            $prepage .= "<li class='page-item'><a href='".$tnamerule."$prepagenum".$GLOBALS['art_shortname']."' class='page-link'>上页</a></li>";
            $indexpage = "<li class='page-item'><a href='".$tnamerule."1".$GLOBALS['art_shortname']."' class='page-link'>首页</a></li>";
        } else {
            $indexpage = "<li class='page-item'><span class='page-link'>首页</span></li>";
        }
        if ($this->PageNo != $totalpage && $totalpage > 1) {
            $nextpage .= "<li class='page-item'><a href='".$tnamerule."$nextpagenum".$GLOBALS['art_shortname']."'>下页</a></li>";
            $endpage = "<li class='page-item'><a href='".$tnamerule."$totalpage".$GLOBALS['art_shortname']."'>末页</a></li>";
        } else {
            $endpage = "<li class='page-item'><span class='page-link'>末页</span></li>";
        }
        //获得数字链接
        $listdd = '';
        $total_list = $list_len * 2 + 1;
        if ($this->PageNo >= $total_list) {
            $j = $this->PageNo - $list_len;
            $total_list = $this->PageNo + $list_len;
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
            if ($j == $this->PageNo) {
                $listdd .= "<li class='page-item active'><span class='page-link'>$j</span></li>";
            } else {
                $listdd .= "<li class='page-item'><a href='".$tnamerule."$j".$GLOBALS['art_shortname']."'class='page-link'>$j</a></li>";
            }
        }
        $plist = $indexpage.$prepage.$listdd.$nextpage.$endpage;
        return $plist;
    }
    /**
     *  获取动态的分页列表
     *
     * @access    private
     * @param     int  $list_len  列表宽度
     * @return    string
     */

    function GetPageListDM($list_len)
    {
        $prepage = '';
        $nextpage = '';
        $prepagenum = $this->PageNo - 1;
        $nextpagenum = $this->PageNo + 1;
        if ($list_len == "" || preg_match("/[^0-9]/", $list_len)) {
            $list_len = 3;
        }
        $totalpage = ceil($this->TotalResult / $this->pagesize);
        if ($totalpage <= 1 && $this->TotalResult > 0) {
            return "<li class='page-item disabled'><span class='page-link'>1页".$this->TotalResult."条</span></li>";
        }
        if ($this->TotalResult == 0) {
            return "<li class='page-item disabled'><span class='page-link'>0页".$this->TotalResult."条</span></li>";
        }
        $purl = $this->GetCurUrl();
        $geturl = "typeid=".$this->TypeID."&TotalResult=".$this->TotalResult."&";
        $hidenform = "<input type='hidden' name='typeid' value='".$this->TypeID."'>";
        $hidenform .= "<input type='hidden' name='TotalResult' value='".$this->TotalResult."'>";
        $purl .= "?".$geturl;
        //获得上页和下页的链接
        if ($this->PageNo != 1) {
            $prepage .= "<li class='page-item'><a href='".$purl."PageNo=$prepagenum' class='page-link'>上页</a></li>";
            $indexpage = "<li class='page-item'><a href='".$purl."PageNo=1' class='page-link'>首页</a></li>";
        } else {
            $indexpage = "<li class='page-item'><span class='page-link'>首页</span></li>";
        }
        if ($this->PageNo != $totalpage && $totalpage > 1) {
            $nextpage .= "<li class='page-item'><a href='".$purl."PageNo=$nextpagenum' class='page-link'>下页</a></li>";
            $endpage = "<li class='page-item'><a href='".$purl."PageNo=$totalpage' class='page-link'>末页</a></li>";
        } else {
            $endpage = "<li class='page-item'><span class='page-link'>末页</span></li>";
        }
        //获得数字链接
        $listdd = '';
        $total_list = $list_len * 2 + 1;
        if ($this->PageNo >= $total_list) {
            $j = $this->PageNo - $list_len;
            $total_list = $this->PageNo + $list_len;
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
            if ($j == $this->PageNo) {
                $listdd .= "<li class='page-item active'><span class='page-link'>$j</span></li>";
            } else {
                $listdd .= "<li class='page-item'><a href='".$purl."PageNo=$j'class='page-link'>$j</a></li>";
            }
        }
        $plist = $indexpage.$prepage.$listdd.$nextpage.$endpage;
        return $plist;
    }
    /**
     *  获得当前的页面文件链接
     *
     * @access    private
     * @return    string
     */
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
}//End Class
?>