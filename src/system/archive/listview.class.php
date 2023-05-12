<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 文档列表
 *
 * @version        $id:listview.class.php 2 15:15 2010年7月7日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC.'/archive/partview.class.php');
helper('cache');
@set_time_limit(0);
class ListView
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
    var $Fields;
    var $PartView;
    var $upPageType;
    var $addSql;
    var $IsError;
    var $CrossID;
    var $IsReplace;
    var $remoteDir;
    var $mod;
    var $_parms = array('tid','TotalResult','PageNo','PageSize','mod','timestamp','sign');
    /**
     *  php5构造函数
     *
     * @access    public
     * @param     int    $typeid  栏目id
     * @param     int    $uppage  上一页
     * @param     int    $mod  渲染类型 0:HTML 1:JSON
     * @return    string
     */
    function __construct($typeid, $uppage = 1, $mod = 0)
    {
        global $dsql, $envs;
        $envs['url_type'] = 1;
        $this->TypeID = $typeid;
        $this->dsql = &$dsql;
        $this->CrossID = '';
        $this->IsReplace = false;
        $this->IsError = false;
        $this->dtp = new DedeTagParse();
        $this->dtp->SetRefObj($this);
        $this->dtp->SetNameSpace("dede", "{", "}");
        $this->dtp2 = new DedeTagParse();
        $this->dtp2->SetNameSpace("field", "[", "]");
        $this->TypeLink = new TypeLink($typeid);
        $this->upPageType = $uppage;
        $this->remoteDir = '';
        $this->mod = $mod;
        $this->TotalResult = is_numeric($this->TotalResult) ? $this->TotalResult : "";
        if (!is_array($this->TypeLink->TypeInfos)) {
            $this->IsError = true;
        }
        if (!$this->IsError) {
            $this->ChannelUnit = new ChannelUnit($this->TypeLink->TypeInfos['channeltype']);
            $this->Fields = $this->TypeLink->TypeInfos;
            $this->Fields['id'] = $typeid;
            $this->Fields['position'] = $this->TypeLink->GetPositionLink(true);
            $this->Fields['title'] = preg_replace("/[<>]/", " / ", $this->TypeLink->GetPositionLink(false));
            //添加联动单筛选
            if (isset($_REQUEST['tid'])) {
                foreach($_GET as $key => $value) {
                    if (!in_array($key,$this->_parms)) {
                        $this->Fields[string_filter($key)] = string_filter(urldecode($value));
                    }
                }
            }
            //设置一些全局参数的值
            foreach ($GLOBALS['PubFields'] as $k => $v) $this->Fields[$k] = $v;
            //api相关逻辑处理
            if ($this->mod == 1 && empty($this->Fields['apikey'])) {
                echo json_encode(array(
                    "code" => -1,
                    "msg" => "api key is empty",
                ));
                exit;
            } 
            if ($this->mod == 1) {
                if (empty($GLOBALS['sign'])) {
                    echo json_encode(array(
                        "code" => -1,
                        "msg" => "sign is empty",
                    ));
                    exit;
                }
                //验签算法md5(typeid+timestamp+apikey+PageNo+PageSize)
                $sign = md5($this->TypeID.$GLOBALS['timestamp'].$this->Fields['apikey'].$GLOBALS['PageNo'].$GLOBALS['PageSize']);
                if ($sign !== $GLOBALS['sign']) {
                    echo json_encode(array(
                        "code" => -1,
                        "msg" => "sign check failed",
                    ));
                    exit;
                }
            }
            $this->Fields['rsslink'] = $GLOBALS['cfg_cmsurl']."/static/rss/".$this->TypeID.".xml";
            //设置环境变量
            SetSysEnv($this->TypeID, $this->Fields['typename'], 0, '', 'list');
            $this->Fields['typeid'] = $this->TypeID;
            //获得交叉栏目id
            if ($this->TypeLink->TypeInfos['cross'] > 0 && $this->TypeLink->TypeInfos['ispart'] == 0) {
                $selquery = '';
                if ($this->TypeLink->TypeInfos['cross'] == 1) {
                    $selquery = "SELECT id,topid FROM `#@__arctype` WHERE typename LIKE '{$this->Fields['typename']}' AND id<>'{$this->TypeID}' AND topid<>'{$this->TypeID}' ";
                } else {
                    $this->Fields['crossid'] = preg_replace('/[^0-9,]/', '', trim($this->Fields['crossid']));
                    if ($this->Fields['crossid'] != '') {
                        $selquery = "SELECT id,topid FROM `#@__arctype` WHERE id in({$this->Fields['crossid']}) AND id<>{$this->TypeID} AND topid<>{$this->TypeID}  ";
                    }
                }
                if ($selquery != '') {
                    $this->dsql->SetQuery($selquery);
                    $this->dsql->Execute();
                    while ($arr = $this->dsql->GetArray()) {
                        $this->CrossID .= ($this->CrossID == '' ? $arr['id'] : ','.$arr['id']);
                    }
                }
            }
        }//!error
    }
    //php4构造函数
    function ListView($typeid, $uppage = 0, $mod = 0)
    {
        $this->__construct($typeid, $uppage, $mod);
    }
    //关闭相关资源
    function Close()
    {
    }
    /**
     *  统计列表里的记录
     *
     * @access    public
     * @param     string
     * @return    string
     */
    function CountRecord()
    {
        global $cfg_list_son, $cfg_need_typeid2, $cfg_cross_sectypeid;
        if (empty($cfg_need_typeid2)) $cfg_need_typeid2 = 'N';
        $filtersql = '';
        //获得附加表的相关信息，联动单筛选
        $addtable = $this->ChannelUnit->ChannelInfos['addtable'];
        if ($addtable!="") {
            $addJoin = " LEFT JOIN `$addtable` ON arc.id = ".$addtable.'.aid ';
            $addField = '';
            $fields = explode(',',$this->ChannelUnit->ChannelInfos['listfields']);
            foreach($fields as $k=>$v)
            {
                $nfields[$v] = $k;
            }
            if (is_array($this->ChannelUnit->ChannelFields) && !empty($this->ChannelUnit->ChannelFields)) {
                foreach($this->ChannelUnit->ChannelFields as $k=>$arr)
                {
                    if (isset($nfields[$k])) {
                        if (!empty($arr['rename'])) {
                            $addField .= ','.$addtable.'.'.$k.' as '.$arr['rename'];
                        } else {
                            $addField .= ','.$addtable.'.'.$k;
                        }
                    }
                }
            }
            if (isset($_REQUEST['tid'])) {
                foreach ($_GET as $key => $value) {
                    $filtersql .= (!in_array($key,$this->_parms)) ? " AND $addtable.".string_filter($key)." = '".string_filter(urldecode($value))."'" : '';
                }
            }
        } else {
            $addField = '';
            $addJoin = '';
        }
        //统计数据库记录
        $this->TotalResult = -1;
        if (isset($GLOBALS['TotalResult'])) $this->TotalResult = $GLOBALS['TotalResult'];
        if (isset($GLOBALS['PageNo'])) $this->PageNo = $GLOBALS['PageNo'];
        else $this->PageNo = 1;
        $this->addSql  = " arc.arcrank > -1 ";
        $typeid2like = " '%,{$this->TypeID},%' ";
        if ($cfg_list_son == 'N') {
            if ($cfg_need_typeid2 == 'N') {
                if ($this->CrossID == '') $this->addSql .= " AND (arc.typeid='".$this->TypeID."') ";
                else $this->addSql .= " AND (arc.typeid in({$this->CrossID},{$this->TypeID})) ";
            } else {
                if ($this->CrossID == '') {
                    $this->addSql .= " AND ( (arc.typeid='".$this->TypeID."') OR CONCAT(',', arc.typeid2, ',') LIKE $typeid2like) ";
                } else {
                    if ($cfg_cross_sectypeid == 'Y') {
                        $typeid2Clike = " '%,{$this->CrossID},%' ";
                        $this->addSql .= " AND ( arc.typeid IN({$this->CrossID},{$this->TypeID}) OR CONCAT(',', arc.typeid2, ',') LIKE $typeid2like OR CONCAT(',', arc.typeid2, ',') LIKE $typeid2Clike)";
                    } else {
                        $this->addSql .= " AND ( arc.typeid IN({$this->CrossID},{$this->TypeID}) OR CONCAT(',', arc.typeid2, ',') LIKE $typeid2like)";
                    }
                }
            }
        } else {
            $sonids = GetSonIds($this->TypeID, $this->Fields['channeltype']);
            if (!preg_match("/,/", $sonids)) {
                $sonidsCon = " arc.typeid = '$sonids' ";
            } else {
                $sonidsCon = " arc.typeid IN($sonids) ";
            }
            if ($cfg_need_typeid2 == 'N') {
                if ($this->CrossID == '') $this->addSql .= " AND ( $sonidsCon ) ";
                else $this->addSql .= " AND ( arc.typeid IN ({$sonids},{$this->CrossID}) ) ";
            } else {
                if ($this->CrossID == '') {
                    $this->addSql .= " AND ( $sonidsCon OR CONCAT(',', arc.typeid2, ',') like $typeid2like  ) ";
                } else {
                    if ($cfg_cross_sectypeid == 'Y') {
                        $typeid2Clike = " '%,{$this->CrossID},%' ";
                        $this->addSql .= " AND ( arc.typeid IN ({$sonids},{$this->CrossID}) OR CONCAT(',', arc.typeid2, ',') LIKE $typeid2like OR CONCAT(',', arc.typeid2, ',') LIKE $typeid2Clike) ";
                    } else {
                        $this->addSql .= " AND ( arc.typeid IN ({$sonids},{$this->CrossID}) OR CONCAT(',', arc.typeid2, ',') LIKE $typeid2like) ";
                    }
                }
            }
        }
        if ($this->TotalResult==-1) {
            //添加联动单筛选
            $cquery = "SELECT COUNT(*) AS dd FROM `#@__arctiny` arc $addJoin WHERE ".$this->addSql.$filtersql;
            $row = $this->dsql->GetOne($cquery);
            if (is_array($row)) {
                $this->TotalResult = $row['dd'];
            } else {
                $this->TotalResult = 0;
            }
        }
        if ($this->mod === 0) {
            //初始化列表模板，并统计页面总数
            $tempfile = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir']."/".$this->TypeLink->TypeInfos['templist'];
            $tempfile = str_replace("{tid}", $this->TypeID, $tempfile);
            $tempfile = str_replace("{cid}", $this->ChannelUnit->ChannelInfos['nid'], $tempfile);
            if (!file_exists($tempfile)) {
                $tempfile = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir']."/".$GLOBALS['cfg_df_style']."/list_default.htm";
            }
            if (!file_exists($tempfile) || !is_file($tempfile)) {
                echo "主题模板文件不存在，无法发布文档";
                exit();
            }
            $this->dtp->LoadTemplate($tempfile);
            $ctag = $this->dtp->GetTag("page");
            if (!is_object($ctag)) {
                $ctag = $this->dtp->GetTag("list");
            }
            if (!is_object($ctag)) {
                $this->pagesize = 30;
            } else {
                if ($ctag->GetAtt("pagesize") != "") {
                    $this->pagesize = $ctag->GetAtt("pagesize");
                } else {
                    $this->pagesize = 30;
                }
            }
        } else {
            $this->pagesize = isset($GLOBALS['PageSize'])? intval($GLOBALS['PageSize']) : 10;
            $this->pagesize = $this->pagesize > 20? 20 : $this->pagesize;
        }
        $this->TotalPage = ceil($this->TotalResult / $this->pagesize);
    }
    /**
     *  列表创建网页
     *
     * @access    public
     * @param     string  $startpage  开始页面
     * @param     string  $makepagesize  创建文件数目
     * @param     string  $isremote  是否为远程
     * @return    string
     */
    function MakeHtml($startpage = 1, $makepagesize = 0, $isremote = 0)
    {
        if (empty($startpage)) {
            $startpage = 1;
        }
        //创建封面模板文件
        if ($this->TypeLink->TypeInfos['isdefault'] == -1) {
            echo '这个是动态栏目';
            return '../apps/list.php?tid='.$this->TypeLink->TypeInfos['id'];
        }
        //单独页面
        else if ($this->TypeLink->TypeInfos['ispart'] > 0) {
            $reurl = $this->MakePartTemplets();
            return $reurl;
        }
        if (empty($this->TotalResult)) $this->CountRecord();
        //初步给固定值的标记赋值
        $this->ParseTempletsFirst();
        $totalpage = ceil($this->TotalResult / $this->pagesize);
        if ($totalpage == 0) {
            $totalpage = 1;
        }
        CreateDir(MfTypedir($this->Fields['typedir']));
        $murl = '';
        if ($makepagesize > 0) {
            $endpage = $startpage + $makepagesize;
        } else {
            $endpage = ($totalpage + 1);
        }
        if ($endpage >= $totalpage + 1) {
            $endpage = $totalpage + 1;
        }
        if ($endpage == 1) {
            $endpage = 2;
        }
        for ($this->PageNo = $startpage; $this->PageNo < $endpage; $this->PageNo++) {
            $this->ParseDMFields($this->PageNo, 1);
            $makeFile = $this->GetMakeFileRule($this->Fields['id'], 'list', $this->Fields['typedir'], '', $this->Fields['namerule2']);
            $makeFile = str_replace("{page}", $this->PageNo, $makeFile);
            $murl = $makeFile;
            if (!preg_match("/^\//", $makeFile)) {
                $makeFile = "/".$makeFile;
            }
            $makeFile = $this->GetTruePath().$makeFile;
            $makeFile = preg_replace("/\/{1,}/", "/", $makeFile);
            $murl = $this->GetTrueUrl($murl);
            $this->dtp->SaveTo($makeFile);
            if (PHP_SAPI === 'cli') {
                DedeCli::showProgress(ceil(($this->PageNo / ($endpage-1)) * 100), 100);
            }
        }
        if ($startpage == 1) {
            //如果列表启用封面文件，复制这个文件第一页
            if ($this->TypeLink->TypeInfos['isdefault'] == 1 && $this->TypeLink->TypeInfos['ispart'] == 0) {
                $onlyrule = $this->GetMakeFileRule($this->Fields['id'], "list", $this->Fields['typedir'], '', $this->Fields['namerule2']);
                $onlyrule = str_replace("{page}", "1", $onlyrule);
                $list_1 = $this->GetTruePath().$onlyrule;
                $murl = MfTypedir($this->Fields['typedir']).'/'.$this->Fields['defaultname'];
                $indexname = $this->GetTruePath().$murl;
                copy($list_1, $indexname);
            }
        }
        return $murl;
    }
    /**
     *  显示列表
     *
     * @access    public
     * @return    void
     */
    function Display()
    {
        if ($this->mod === 0) {
            if ($this->TypeLink->TypeInfos['ispart'] > 0) {
                $this->DisplayPartTemplets();
                return;
            }
            $this->CountRecord();
            if ((empty($this->PageNo) || $this->PageNo == 1) && $this->TypeLink->TypeInfos['ispart'] == 1) {
                $tmpdir = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir'];
                $tempfile = str_replace("{tid}", $this->TypeID, $this->Fields['tempindex']);
                $tempfile = str_replace("{cid}", $this->ChannelUnit->ChannelInfos['nid'], $tempfile);
                $tempfile = $tmpdir."/".$tempfile;
                if (!file_exists($tempfile)) {
                    $tempfile = $tmpdir."/".$GLOBALS['cfg_df_style']."/index_default.htm";
                }
                $this->dtp->LoadTemplate($tempfile);
            }
            $this->ParseTempletsFirst();
            $this->ParseDMFields($this->PageNo, 0);
            $this->dtp->Display();
        } else {
            $this->CountRecord();
            $result = $this->GetAPIList($this->PageNo,$this->pagesize);
            if (!is_array($result)) {
                echo json_encode(array(
                    "code" => -1,
                    "msg" => "none result",
                ));
            } else {
                echo json_encode(array(
                    "code" => 0,
                    "msg" => "",
                    "lists" => $result,
                    "total" => intval($this->TotalResult),
                ));
            }
        }
    }    
    
    /**
     * GetAPIList
     *
     * @param  mixed $PageNo 页码
     * @param  mixed $row 条数
     * @param  mixed $titlelen 标题字符长度
     * @param  mixed $infolen 描述字符长度
     * @param  mixed $orderby 排序
     * @param  mixed $orderWay 排序方式
     * @return array
     */
    function GetAPIList($PageNo = 1,$row = 10,$titlelen = 30,$infolen = 250,$orderby = "default",$orderWay = 'desc') {
        $limitstart = ($PageNo - 1) * $row;
        if ($row == '') $row = 10;
        if ($limitstart == '') $limitstart = 0;
        if ($titlelen == '') $titlelen = 100;
        if ($infolen == '') $infolen = 250;
        if ($orderWay == '') $orderWay = 'desc';
        if ($orderby == '') {
            $orderby = 'default';
        } else {
            $orderby = strtolower($orderby);
        }
        //排序方式
        $ordersql = '';
        if ($orderby == "senddate" || $orderby == "id") {
            $ordersql = " ORDER BY arc.id $orderWay";
        } else if ($orderby == "hot" || $orderby == "click") {
            $ordersql = " ORDER BY arc.click $orderWay";
        } else if ($orderby == "lastpost") {
            $ordersql = " ORDER BY arc.lastpost $orderWay";
        } else {
            $ordersql = " ORDER BY arc.sortrank $orderWay";
        }
        //获得附加表的相关信息
        $addtable = $this->ChannelUnit->ChannelInfos['addtable'];
        $filtersql = "";
        if ($addtable!="")
        {
            $addJoin = " LEFT JOIN `$addtable` ON arc.id = ".$addtable.'.aid ';
            $addField = '';
            $fields = explode(',',$this->ChannelUnit->ChannelInfos['listfields']);
            foreach($fields as $k=>$v)
            {
                $nfields[$v] = $k;
            }
            if (is_array($this->ChannelUnit->ChannelFields) && !empty($this->ChannelUnit->ChannelFields)) {
                foreach($this->ChannelUnit->ChannelFields as $k=>$arr)
                {
                    if (isset($nfields[$k])) {
                        if (!empty($arr['rename'])) {
                            $addField .= ','.$addtable.'.'.$k.' as '.$arr['rename'];
                        }
                        else {
                            $addField .= ','.$addtable.'.'.$k;
                        }
                    }
                }
            }
            //添加联动单筛选
            if (isset($_REQUEST['tid'])) {
                foreach($_GET as $key => $value) 
                {
                    $filtersql .= (!in_array($key,$this->_parms)) ? " AND $addtable.".string_filter($key)." = '".string_filter(urldecode($value))."'" : '';
                }
            }
        } else {
            $addField = '';
            $addJoin = '';
        }
        //如果不用默认的sortrank或id排序，使用联合查询数据量大时非常缓慢
        if (preg_match('/hot|click|lastpost/', $orderby)) {
            $query = "SELECT arc.*,tp.typedir,tp.typename,tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath,mb.uname,mb.face $addField FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id LEFT JOIN `#@__member` mb ON arc.mid=mb.mid $addJoin WHERE {$this->addSql} $filtersql $ordersql LIMIT $limitstart,$row";
        }
        //普通情况先从arctiny表查出id，然后按di查询速度非常快
        else {
            $t1 = ExecTime();
            $ids = array();
            $query = "SELECT id FROM `#@__arctiny` arc $addJoin WHERE {$this->addSql} $filtersql $ordersql LIMIT $limitstart,$row";
            $this->dsql->SetQuery($query);
            $this->dsql->Execute();
            while ($arr = $this->dsql->GetArray()) {
                $ids[] = $arr['id'];
            }
            $idstr = join(',', $ids);
            if ($idstr == '') {
                return '';
            } else {
                $query = "SELECT arc.*,tp.typedir,tp.typename,tp.corank,tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath,mb.uname,mb.face $addField FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id LEFT JOIN `#@__member` mb ON arc.mid=mb.mid $addJoin WHERE arc.id in($idstr) $ordersql ";
            }
            $t2 = ExecTime();
        }
        $this->dsql->SetQuery($query);
        $this->dsql->Execute('al');
        $t2 = ExecTime();
        $result = array();
        $GLOBALS['autoindex'] = 0;
        while ($row = $this->dsql->GetArray("al")) {
            $GLOBALS['autoindex']++;
            $ids[$row['id']] = $row['id'];
            //处理一些特殊字段
            $row['infos'] = cn_substr($row['description'], $infolen);
            $row['id'] =  $row['id'];
            if ($row['corank'] > 0 && $row['arcrank'] == 0) {
                $row['arcrank'] = $row['corank'];
            }
            $row['filename'] = $row['arcurl'] = GetFileUrl(
                $row['id'],
                $row['typeid'],
                $row['senddate'],
                $row['title'],
                $row['ismake'],
                $row['arcrank'],
                $row['namerule'],
                $row['typedir'],
                $row['money'],
                $row['filename'],
                $row['moresite'],
                $row['siteurl'],
                $row['sitepath']
            );
            $row['typeurl'] = GetTypeUrl(
                $row['typeid'],
                MfTypedir($row['typedir']),
                $row['isdefault'],
                $row['defaultname'],
                $row['ispart'],
                $row['namerule2'],
                $row['moresite'],
                $row['siteurl'],
                $row['sitepath']
            );
            if ($row['litpic'] == '-' || $row['litpic'] == '') {
                $row['litpic'] = $GLOBALS['cfg_cmspath'].'/static/web/img/thumbnail.jpg';
            }
            /*if (!preg_match("/^http:\/\//i", $row['litpic']) && $GLOBALS['cfg_multi_site'] == 'Y') {
                $row['litpic'] = $GLOBALS['cfg_mainsite'].$row['litpic'];
            }*/
            $row['picname'] = $row['litpic'];
            $row['stime'] = GetDateMK($row['pubdate']);
            $row['typelink'] = "<a href='".$row['typeurl']."'>".$row['typename']."</a>";
            $row['image'] = "<img src='".$row['picname']."' title='".preg_replace("/['><]/", "", $row['title'])."'>";
            $row['imglink'] = "<a href='".$row['filename']."'>".$row['image']."</a>";
            $row['fulltitle'] = $row['title'];
            $row['title'] = cn_substr($row['title'], $titlelen);
            if ($row['color'] != '') {
                $row['title'] = "<span style='color:".$row['color']."'>".$row['title']."</span>";
            }
            if (preg_match('/c/', $row['flag'])) {
                $row['title'] = "".$row['title']."";
            }
            $row['face'] = empty($row['face'])? $GLOBALS['cfg_mainsite'].'/static/web/img/admin.png' : $row['face'];
            $row['textlink'] = "<a href='".$row['filename']."'>".$row['title']."</a>";
            $row['plusurl'] = $row['phpurl'] = $GLOBALS['cfg_phpurl'];
            $row['memberurl'] = $GLOBALS['cfg_memberurl'];
            $row['templeturl'] = $GLOBALS['cfg_templeturl'];
            //编译附加表里的数据
            foreach ($row as $k => $v) {
                $row[strtolower($k)] = $v;
            }
            foreach ($this->ChannelUnit->ChannelFields as $k => $arr) {
                if (isset($row[$k])) {
                    $row[$k] = $this->ChannelUnit->MakeField($k, $row[$k]);
                }
            }
            $result[] = $row;
        }//if hasRow
        $t3 = ExecTime();
        $this->dsql->FreeResult('al');
        return $result;
    }
    /**
     *  创建单独模板页面
     *
     * @access    public
     * @return    string
     */
    function MakePartTemplets()
    {
        $this->PartView = new PartView($this->TypeID, false);
        $this->PartView->SetTypeLink($this->TypeLink);
        $nmfa = 0;
        $tmpdir = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir'];
        if ($this->Fields['ispart'] == 1) {
            $tempfile = str_replace("{tid}", $this->TypeID, $this->Fields['tempindex']);
            $tempfile = str_replace("{cid}", $this->ChannelUnit->ChannelInfos['nid'], $tempfile);
            $tempfile = $tmpdir."/".$tempfile;
            if (!file_exists($tempfile)) {
                $tempfile = $tmpdir."/".$GLOBALS['cfg_df_style']."/index_default.htm";
            }
            $this->PartView->SetTemplet($tempfile);
        } else if ($this->Fields['ispart'] == 2) {
            //跳转网址
            return $this->Fields['typedir'];
        }
        CreateDir(MfTypedir($this->Fields['typedir']));
        $makeUrl = $this->GetMakeFileRule($this->Fields['id'], "index", MfTypedir($this->Fields['typedir']), $this->Fields['defaultname'], $this->Fields['namerule2']);
        $makeUrl = preg_replace("/\/{1,}/", "/", $makeUrl);
        $makeFile = $this->GetTruePath().$makeUrl;
        if ($nmfa == 0) {
            $this->PartView->SaveToHtml($makeFile);
        } else {
            if (!file_exists($makeFile)) {
                $this->PartView->SaveToHtml($makeFile);
            }
        }
        return $this->GetTrueUrl($makeUrl);
    }
    /**
     *  显示单独模板页面
     *
     * @access    public
     * @param     string
     * @return    string
     */
    function DisplayPartTemplets()
    {
        $this->PartView = new PartView($this->TypeID, false);
        $this->PartView->SetTypeLink($this->TypeLink);
        $nmfa = 0;
        $tmpdir = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir'];
        if ($this->Fields['ispart'] == 1) {
            //封面模板
            $tempfile = str_replace("{tid}", $this->TypeID, $this->Fields['tempindex']);
            $tempfile = str_replace("{cid}", $this->ChannelUnit->ChannelInfos['nid'], $tempfile);
            $tempfile = $tmpdir."/".$tempfile;
            if (!file_exists($tempfile)) {
                $tempfile = $tmpdir."/".$GLOBALS['cfg_df_style']."/index_default.htm";
            }
            $this->PartView->SetTemplet($tempfile);
        } else if ($this->Fields['ispart'] == 2) {
            //跳转网址
            $gotourl = $this->Fields['typedir'];
            header("Location:$gotourl");
            exit();
        }
        CreateDir(MfTypedir($this->Fields['typedir']));
        $makeUrl = $this->GetMakeFileRule($this->Fields['id'], "index", MfTypedir($this->Fields['typedir']), $this->Fields['defaultname'], $this->Fields['namerule2']);
        $makeFile = $this->GetTruePath().$makeUrl;
        if ($nmfa == 0) {
            $this->PartView->Display();
        } else {
            if (!file_exists($makeFile)) {
                $this->PartView->Display();
            } else {
                include($makeFile);
            }
        }
    }
    /**
     *  获得站点的真实根路径
     *
     * @access    public
     * @return    string
     */
    function GetTruePath()
    {
        $truepath = $GLOBALS["cfg_basedir"];
        return $truepath;
    }
    /**
     *  获得真实连接路径
     *
     * @access    public
     * @param     string  $nurl  地址
     * @return    string
     */
    function GetTrueUrl($nurl)
    {
        if ($this->Fields['moresite'] == 1) {
            if ($this->Fields['sitepath'] != '') {
                $nurl = preg_replace("/^".$this->Fields['sitepath']."/", '', $nurl);
            }
            $nurl = $this->Fields['siteurl'].$nurl;
        }
        return $nurl;
    }
    /**
     *  解析模板，对固定的标记进行初始给值
     *
     * @access    public
     * @return    string
     */
    function ParseTempletsFirst()
    {
        if (isset($this->TypeLink->TypeInfos['reid'])) {
            $GLOBALS['envs']['reid'] = $this->TypeLink->TypeInfos['reid'];
        }
        $GLOBALS['envs']['typeid'] = $this->TypeID;
        $GLOBALS['envs']['topid'] = GetTopid($this->Fields['typeid']);
        $GLOBALS['envs']['cross'] = 1;
        MakeOneTag($this->dtp, $this);
    }
    /**
     *  解析模板，对文档里的变动进行赋值
     *
     * @access    public
     * @param     int  $PageNo  页数
     * @param     int  $ismake  是否编译
     * @return    string
     */
    function ParseDMFields($PageNo, $ismake = 1)
    {
        //替换第二页后的文档
        if (($PageNo > 1 || strlen($this->Fields['content']) < 10) && !$this->IsReplace) {
            $this->dtp->SourceString = str_replace('[cmsreplace]', 'display:none', $this->dtp->SourceString);
            $this->IsReplace = true;
        }
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
                        $ctag->GetAtt("tablewidth"),
                        $ismake,
                        $ctag->GetAtt("orderway")
                    )
                );
            } else if ($ctag->GetName() == "pagelist") {
                $list_len = trim($ctag->GetAtt("listsize"));
                $ctag->GetAtt("listitem") == "" ? $listitem = "index,pre,pageno,next,end,option" : $listitem = $ctag->GetAtt("listitem");
                if ($list_len == "") {
                    $list_len = 3;
                }
                if ($ismake == 0) {
                    $this->dtp->Assign($tagid, $this->GetPageListDM($list_len, $listitem));
                } else {
                    $this->dtp->Assign($tagid, $this->GetPageListST($list_len, $listitem));
                }
            } else if ($PageNo != 1 && $ctag->GetName() == 'field' && $ctag->GetAtt('display') != '') {
                $this->dtp->Assign($tagid, '');
            }
        }
    }
    /**
     *  获得要创建的文件名称规则
     *
     * @access    public
     * @param     int  $typeid  栏目id
     * @param     string  $wname
     * @param     string  $typedir  栏目目录
     * @param     string  $defaultname  默认名称
     * @param     string  $namerule2  栏目规则
     * @return    string
     */
    function GetMakeFileRule($typeid, $wname, $typedir, $defaultname, $namerule2)
    {
        $typedir = MfTypedir($typedir);
        if ($wname == 'index') {
            return $typedir.'/'.$defaultname;
        } else {
            $namerule2 = str_replace('{tid}', $typeid, $namerule2);
            $namerule2 = str_replace('{typedir}', $typedir, $namerule2);
            return $namerule2;
        }
    }
    /**
     *  获得一个单列的文档列表
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
     * @param     string  $ismake  是否编译
     * @param     string  $orderWay  排序方式
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
        $tablewidth = "100",
        $ismake = 1,
        $orderWay = 'desc'
    ) {
        global $cfg_list_son, $cfg_digg_update;
        $typeid = $this->TypeID;
        if ($row == '') $row = 10;
        if ($limitstart == '') $limitstart = 0;
        if ($titlelen == '') $titlelen = 100;
        if ($infolen == '') $infolen = 250;
        if ($imgwidth == '') $imgwidth = 120;
        if ($imgheight == '') $imgheight = 120;
        if ($listtype == '') $listtype = 'all';
        if ($orderWay == '') $orderWay = 'desc';
        if ($orderby == '') {
            $orderby = 'default';
        } else {
            $orderby = strtolower($orderby);
        }
        $tablewidth = str_replace('%', '', $tablewidth);
        if ($tablewidth == '') $tablewidth = 100;
        if ($col == '') $col = 1;
        $colWidth = ceil(100 / $col);
        $tablewidth = $tablewidth.'%';
        $colWidth = $colWidth.'%';
        $innertext = trim($innertext);
        if ($innertext == '') {
            $innertext = GetSysTemplets('list_fulllist.htm');
        }
        //排序方式
        $ordersql = '';
        if ($orderby == "senddate" || $orderby == "id") {
            $ordersql = " ORDER BY arc.id $orderWay";
        } else if ($orderby == "hot" || $orderby == "click") {
            $ordersql = " ORDER BY arc.click $orderWay";
        } else if ($orderby == "lastpost") {
            $ordersql = " ORDER BY arc.lastpost $orderWay";
        } else {
            $ordersql = " ORDER BY arc.sortrank $orderWay";
        }
        //获得附加表的相关信息
        $addtable = $this->ChannelUnit->ChannelInfos['addtable'];
        $filtersql = "";
        if ($addtable!="")
        {
            $addJoin = " LEFT JOIN `$addtable` ON arc.id = ".$addtable.'.aid ';
            $addField = '';
            $fields = explode(',',$this->ChannelUnit->ChannelInfos['listfields']);
            foreach($fields as $k=>$v)
            {
                $nfields[$v] = $k;
            }
            if (is_array($this->ChannelUnit->ChannelFields) && !empty($this->ChannelUnit->ChannelFields)) {
                foreach($this->ChannelUnit->ChannelFields as $k=>$arr)
                {
                    if (isset($nfields[$k])) {
                        if (!empty($arr['rename'])) {
                            $addField .= ','.$addtable.'.'.$k.' as '.$arr['rename'];
                        }
                        else {
                            $addField .= ','.$addtable.'.'.$k;
                        }
                    }
                }
            }
            //添加联动单筛选
            if (isset($_REQUEST['tid'])) {
                foreach($_GET as $key => $value) 
                {
                    $filtersql .= (!in_array($key,$this->_parms)) ? " AND $addtable.".string_filter($key)." = '".string_filter(urldecode($value))."'" : '';
                }
            }
        } else {
            $addField = '';
            $addJoin = '';
        }
        //如果不用默认的sortrank或id排序，使用联合查询数据量大时非常缓慢
        if (preg_match('/hot|click|lastpost/', $orderby)) {
            $query = "SELECT arc.*,tp.typedir,tp.typename,tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath,mb.uname,mb.face $addField FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id LEFT JOIN `#@__member` mb ON arc.mid=mb.mid $addJoin WHERE {$this->addSql} $filtersql $ordersql LIMIT $limitstart,$row";
        }
        //普通情况先从arctiny表查出id，然后按di查询速度非常快
        else {
            $t1 = ExecTime();
            $ids = array();
            $query = "SELECT id FROM `#@__arctiny` arc $addJoin WHERE {$this->addSql} $filtersql $ordersql LIMIT $limitstart,$row";
            $this->dsql->SetQuery($query);
            $this->dsql->Execute();
            while ($arr = $this->dsql->GetArray()) {
                $ids[] = $arr['id'];
            }
            $idstr = join(',', $ids);
            if ($idstr == '') {
                return '';
            } else {
                $query = "SELECT arc.*,tp.typedir,tp.typename,tp.corank,tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath,mb.uname,mb.face $addField FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id LEFT JOIN `#@__member` mb ON arc.mid=mb.mid $addJoin WHERE arc.id in($idstr) $ordersql ";
            }
            $t2 = ExecTime();
        }
        $this->dsql->SetQuery($query);
        $this->dsql->Execute('al');
        $t2 = ExecTime();
        $artlist = '';
        $this->dtp2->LoadSource($innertext);
        $GLOBALS['autoindex'] = 0;
        for ($i = 0; $i < $row; $i++) {
            if ($col > 1) {
                $artlist .= "<div>";
            }
            for ($j = 0; $j < $col; $j++) {
                if ($row = $this->dsql->GetArray("al")) {
                    $GLOBALS['autoindex']++;
                    $ids[$row['id']] = $row['id'];
                    //处理一些特殊字段
                    $row['infos'] = cn_substr($row['description'], $infolen);
                    $row['id'] =  $row['id'];
                    if ($row['corank'] > 0 && $row['arcrank'] == 0) {
                        $row['arcrank'] = $row['corank'];
                    }
                    $row['filename'] = $row['arcurl'] = GetFileUrl(
                        $row['id'],
                        $row['typeid'],
                        $row['senddate'],
                        $row['title'],
                        $row['ismake'],
                        $row['arcrank'],
                        $row['namerule'],
                        $row['typedir'],
                        $row['money'],
                        $row['filename'],
                        $row['moresite'],
                        $row['siteurl'],
                        $row['sitepath']
                    );
                    $row['typeurl'] = GetTypeUrl(
                        $row['typeid'],
                        MfTypedir($row['typedir']),
                        $row['isdefault'],
                        $row['defaultname'],
                        $row['ispart'],
                        $row['namerule2'],
                        $row['moresite'],
                        $row['siteurl'],
                        $row['sitepath']
                    );
                    if ($row['litpic'] == '-' || $row['litpic'] == '') {
                        $row['litpic'] = $GLOBALS['cfg_cmspath'].'/static/web/img/thumbnail.jpg';
                    }
                    /*if (!preg_match("/^http:\/\//i", $row['litpic']) && $GLOBALS['cfg_multi_site'] == 'Y') {
                        $row['litpic'] = $GLOBALS['cfg_mainsite'].$row['litpic'];
                    }*/
                    $row['picname'] = $row['litpic'];
                    $row['stime'] = GetDateMK($row['pubdate']);
                    $row['typelink'] = "<a href='".$row['typeurl']."'>".$row['typename']."</a>";
                    $row['image'] = "<img src='".$row['picname']."' width='$imgwidth' height='$imgheight' title='".preg_replace("/['><]/", "", $row['title'])."'>";
                    $row['imglink'] = "<a href='".$row['filename']."'>".$row['image']."</a>";
                    $row['fulltitle'] = $row['title'];
                    $row['title'] = cn_substr($row['title'], $titlelen);
                    if ($row['color'] != '') {
                        $row['title'] = "<span style='color:".$row['color']."'>".$row['title']."</span>";
                    }
                    if (preg_match('/c/', $row['flag'])) {
                        $row['title'] = "".$row['title']."";
                    }
                    $row['face'] = empty($row['face'])? $GLOBALS['cfg_mainsite'].'/static/web/img/admin.png' : $row['face'];
                    $row['textlink'] = "<a href='".$row['filename']."'>".$row['title']."</a>";
                    $row['plusurl'] = $row['phpurl'] = $GLOBALS['cfg_phpurl'];
                    $row['memberurl'] = $GLOBALS['cfg_memberurl'];
                    $row['templeturl'] = $GLOBALS['cfg_templeturl'];
                    //编译附加表里的数据
                    foreach ($row as $k => $v) {
                        $row[strtolower($k)] = $v;
                    }
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
            }//Loop Col
            if ($col > 1) {
                $i += $col - 1;
                $artlist .= "</div>";
            }
        }//Loop Line
        $t3 = ExecTime();
        $this->dsql->FreeResult('al');
        return $artlist;
    }
    /**
     *  获取静态的分页列表
     *
     * @access    public
     * @param     string  $list_len  列表宽度
     * @param     string  $list_len  列表样式
     * @return    string
     */
    function GetPageListST($list_len, $listitem = "index,end,pre,next,pageno")
    {
        global $cfg_cmspath;
        $prepage = $nextpage = '';
        $prepagenum = $this->PageNo - 1;
        $nextpagenum = $this->PageNo + 1;
        if ($list_len == '' || preg_match("/[^0-9]/", $list_len)) {
            $list_len = 3;
        }
        $totalpage = ceil($this->TotalResult / $this->pagesize);
        if ($totalpage <= 1 && $this->TotalResult > 0) {
            return "<li class='page-item disabled'><span class='page-link'>1页".$this->TotalResult."条</span></li>\r\n";
        }
        if ($this->TotalResult == 0) {
            return "<li class='page-item disabled'><span class='page-link'>0页".$this->TotalResult."条</span></li>\r\n";
        }
        $purl = $this->GetCurUrl();
        $maininfo = "<li class='page-item disabled'><span class='page-link'>{$totalpage}页".$this->TotalResult."条</span></li>\r\n";
        $tnamerule = $this->GetMakeFileRule($this->Fields['id'], "list", $this->Fields['typedir'], $this->Fields['defaultname'], $this->Fields['namerule2']);
        $tnamerule = preg_replace("/^(.*)\//", '', $tnamerule);
        //获得上一页和首页的链接
        if ($this->PageNo != 1) {
            $prepage .= "<li class='page-item'><a class='page-link' href='".str_replace("{page}", $prepagenum, $tnamerule)."'>上一页</a></li>\r\n";
            $indexpage = "<li class='page-item'><a class='page-link' href='".str_replace("{page}", 1, $tnamerule)."'>首页</a></li>\r\n";
        } else {
            $indexpage = "<li class='page-item'><span class='page-link'>首页</span></li>\r\n";
        }
        //下一页和未页的链接
        if ($this->PageNo != $totalpage && $totalpage > 1) {
            $nextpage .= "<li class='page-item'><a class='page-link' href='".str_replace("{page}", $nextpagenum, $tnamerule)."'>下一页</a></li>\r\n";
            $endpage = "<li class='page-item'><a class='page-link' href='".str_replace("{page}", $totalpage, $tnamerule)."'>末页</a></li>\r\n";
        } else {
            $endpage = "<li class='page-item'><span class='page-link'>末页</span></li>\r\n";
        }
        //option链接
        $optionlist = '';
        $optionlen = strlen($totalpage);
        $optionlen = $optionlen * 12 + 18;
        if ($optionlen < 36) $optionlen = 36;
        if ($optionlen > 100) $optionlen = 100;
        $optionlist = "<li><select name='sldd' style='width:{$optionlen}px' onchange='location.href=this.options[this.selectedIndex].value;'>\r\n";
        for ($mjj = 1; $mjj <= $totalpage; $mjj++) {
            if ($mjj == $this->PageNo) {
                $optionlist .= "<option value='".str_replace("{page}", $mjj, $tnamerule)."' selected>$mjj</option>\r\n";
            } else {
                $optionlist .= "<option value='".str_replace("{page}", $mjj, $tnamerule)."'>$mjj</option>\r\n";
            }
        }
        $optionlist .= "</select></li>\r\n";
        //获得数字链接
        $listdd = "";
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
                $listdd .= "<li class='page-item active'><span class='page-link'>$j</span></li>\r\n";
            } else {
                $listdd .= "<li class='page-item'><a class='page-link' href='".str_replace("{page}", $j, $tnamerule)."'>".$j."</a></li>\r\n";
            }
        }
        $plist = '';
        if (preg_match('/index/i', $listitem)) $plist .= $indexpage;
        if (preg_match('/pre/i', $listitem)) $plist .= $prepage;
        if (preg_match('/pageno/i', $listitem)) $plist .= $listdd;
        if (preg_match('/next/i', $listitem)) $plist .= $nextpage;
        if (preg_match('/end/i', $listitem)) $plist .= $endpage;
        if (preg_match('/option/i', $listitem)) $plist .= $optionlist;
        if (preg_match('/info/i', $listitem)) $plist .= $maininfo;
        return $plist;
    }
    /**
     *  获取动态的分页列表
     *
     * @access    public
     * @param     string  $list_len  列表宽度
     * @param     string  $list_len  列表样式
     * @return    string
     */
    function GetPageListDM($list_len, $listitem = "index,end,pre,next,pageno")
    {
        global $cfg_cmspath, $cfg_rewrite;
        $prepage = $nextpage = '';
        $prepagenum = $this->PageNo - 1;
        $nextpagenum = $this->PageNo + 1;
        if ($list_len == '' || preg_match("/[^0-9]/", $list_len)) {
            $list_len = 3;
        }
        $totalpage = ceil($this->TotalResult / $this->pagesize);
        if ($totalpage <= 1 && $this->TotalResult > 0) {
            return "<li class='page-item disabled'><span class='page-link'>1页".$this->TotalResult."条</span></li>\r\n";
        }
        if ($this->TotalResult == 0) {
            return "<li class='page-item disabled'><span class='page-link'>0页".$this->TotalResult."条</span></li>\r\n";
        }
        $maininfo = "<li class='page-item disabled'><span class='page-link'>{$totalpage}页".$this->TotalResult."条</span></li>\r\n";
        $purl = $this->GetCurUrl();
        //如果开启为静态，则对规则进行替换
        if ($cfg_rewrite == 'Y') {
            $nowurls = preg_replace("/\-/", ".php?", $purl);
            $nowurls = explode("?", $nowurls);
            $purl = $nowurls[0];
        }
        $geturl = "tid=".$this->TypeID."&TotalResult=".$this->TotalResult."&";
        $purl .= '?'.$geturl;
        $optionlist = '';
        //添加联动单筛选
        $pageaddurl = "";
        foreach($_GET as $key => $value) {
            $pageaddurl .= ($key!="tid" && $key!="TotalResult" && $key!="PageNo" && $key!="PageSize" && $key!="mod") ? "&".string_filter($key)."=".string_filter($value) : '';
        }
        //获得上一页和下一页的链接
        if ($this->PageNo != 1) {
            $prepage .= "<li class='page-item'><a href='".$purl."PageNo=$prepagenum".$pageaddurl."' class='page-link'>上一页</a></li>\r\n";
            $indexpage = "<li class='page-item'><a href='".$purl."PageNo=1".$pageaddurl."' class='page-link'>首页</a></li>\r\n";
        } else {
            $indexpage = "<li class='page-item'><span class='page-link'>首页</span></li>\r\n";
        }
        if ($this->PageNo != $totalpage && $totalpage > 1) {
            $nextpage .= "<li class='page-item'><a href='".$purl."PageNo=$nextpagenum".$pageaddurl."' class='page-link'>下一页</a></li>\r\n";
            $endpage = "<li class='page-item'><a href='".$purl."PageNo=$totalpage".$pageaddurl."' class='page-link'>末页</a></li>\r\n";
        } else {
            $endpage = "<li class='page-item'><span class='page-link'>末页</span></li>\r\n";
        }
        //获得数字链接
        $listdd = "";
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
                $listdd .= "<li class='page-item active'><span class='page-link'>$j</span></li>\r\n";
            } else {
                $listdd .= "<li class='page-item'><a href='".$purl."PageNo=$j".$pageaddurl."' class='page-link'>".$j."</a></li>\r\n";
            }
        }
        $plist = "";
        if (preg_match('/index/i', $listitem)) $plist .= $indexpage;
        if (preg_match('/pre/i', $listitem)) $plist .= $prepage;
        if (preg_match('/pageno/i', $listitem)) $plist .= $listdd;
        if (preg_match('/next/i', $listitem)) $plist .= $nextpage;
        if (preg_match('/end/i', $listitem)) $plist .= $endpage;
        if (preg_match('/option/i', $listitem)) $plist .= $optionlist;
        if (preg_match('/info/i', $listitem)) $plist .= $maininfo;
        //伪静态分页
        if ($cfg_rewrite == 'Y') {
            $plist = str_replace('.php?tid=','-',$plist);
            $plist = preg_replace("/&TotalResult=(\d+)/i","",$plist);//去掉总结果数值
            //目录版默认
            $plist = preg_replace("/&PageNo=(\d+)/i",'-\\1',$plist);
            //网页版$plist = preg_replace("/&PageNo=(\d+)/i",'-\\1.html',$plist);
        }
        return $plist;
    }
    /**
     *  获得当前的页面文件链接
     *
     * @access    public
     * @return    string
     */
    function GetCurUrl()
    {
        if (!empty($_SERVER['REQUEST_URI'])) {
            $nowurl = $_SERVER['REQUEST_URI'];
            $nowurls = explode('?', $nowurl);
            $nowurl = $nowurls[0];
        } else {
            $nowurl = $_SERVER['PHP_SELF'];
        }
        return $nowurl;
    }
}//End Class
?>