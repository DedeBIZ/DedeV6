<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 后台栏目管理
 *
 * @version        $id:typeunit.class.selector.php 15:21 2010年7月5日 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(DEDEDATA."/cache/inc_catalog_base.inc");
class TypeUnitSelector
{
    var $dsql;
    //php5构造函数
    function __construct()
    {
        global $cfg_Cs;
        $this->dsql = $GLOBALS['dsql'];
    }

    function TypeUnitSelector()
    {
        $this->__construct();
    }
    //清理类
    function Close()
    {
    }
    /**
     *  列出某一栏目下的所有栏目
     *
     * @access    public
     * @param     string  $channel  栏目id
     * @return    void
     */
    function ListAllType($channel = 0)
    {

        global $cfg_admin_channel, $admin_catalogs, $targetid, $oldvalue;
        $oldvalues = array();
        if (!empty($oldvalue)) $oldvalues = explode(',', $oldvalue);
        //检测会员有权限的顶级栏目
        if ($cfg_admin_channel == 'array') {
            $admin_catalog = join(',', $admin_catalogs);
            $this->dsql->SetQuery("SELECT reid FROM `#@__arctype` WHERE id IN($admin_catalog) GROUP BY reid ");
            $this->dsql->Execute();
            $topidstr = '';
            while ($row = $this->dsql->GetObject()) {
                if ($row->reid == 0) continue;
                $topidstr .= ($topidstr == '' ? $row->reid : ','.$row->reid);
            }
            $admin_catalog .= ','.$topidstr;
            $admin_catalogs = explode(',', $admin_catalog);
            $admin_catalogs = array_unique($admin_catalogs);
        }
        $this->dsql->SetQuery("SELECT id,typedir,typename,ispart,channeltype FROM `#@__arctype` WHERE reid=0 ORDER BY sortrank");
        $this->dsql->Execute(0);
        $lastid = GetCookie('lastCidMenu');
        while ($row = $this->dsql->GetObject(0)) {
            if ($cfg_admin_channel == 'array' && !in_array($row->id, $admin_catalogs)) {
                continue;
            }
            $typeDir = $row->typedir;
            $typeName = $row->typename;
            $ispart = $row->ispart;
            $id = $row->id;
            $channeltype = $row->channeltype;
            $ischeck = in_array($id, $oldvalues) ? ' checked' : '';
            $chackRadio = "<input type='radio' name='seltypeid' value='{$id}' $ischeck>";
            if ($targetid == 'typeid2') $chackRadio = "<input type='checkbox' name='seltypeid' id='seltypeid{$id}' value='{$id}' $ischeck>";
            if ((!empty($channel) && $channeltype != $channel) || $ispart != 0) {
                $chackRadio = '';
            }
            $soncat = '';
            $this->LogicListAllSunType($id, $channel, $soncat);
            if ($chackRadio == '' && $soncat == '') continue;
            echo "<div class='quickselitem'>\r\n";
            echo "<div class='topcat'><label>{$chackRadio} {$typeName}</label></div>\r\n";
            if ($soncat != '') echo "<div class='soncat'>{$soncat}</div>\r\n";
            echo "</div>\r\n";
        }
    }
    /**
     *  获得子栏目的递归调用
     *
     * @access    public
     * @param     int   $id  栏目id
     * @param     int   $channel  栏目id
     * @param     int   $soncat  子级分类
     * @return    string
     */
    function LogicListAllSunType($id, $channel = 0, &$soncat=0)
    {
        global $cfg_admin_channel, $admin_catalogs, $targetid, $oldvalue;
        $fid = $id;
        $oldvalues = array();
        if (!empty($oldvalue)) $oldvalues = explode(',', $oldvalue);
        $this->dsql->SetQuery("SELECT id,reid,typedir,typename,ispart,channeltype FROM `#@__arctype` WHERE reid='".$id."' ORDER BY sortrank");
        $this->dsql->Execute($fid);
        while ($row = $this->dsql->GetObject($fid)) {
            if ($cfg_admin_channel == 'array' && !in_array($row->id, $admin_catalogs)) {
                continue;
            }
            $typeDir = $row->typedir;
            $typeName = $row->typename;
            $reid = $row->reid;
            $id = $row->id;
            $ispart = $row->ispart;
            $channeltype = $row->channeltype;
            $ischeck = in_array($id, $oldvalues) ? ' checked' : '';
            $chackRadio = "<input type='radio' name='seltypeid' value='{$row->id}' $ischeck>";
            if ($targetid == 'typeid2') $chackRadio = "<input type='checkbox' name='seltypeid' id='seltypeid{$id}' value='{$id}' $ischeck>";
            if ($ispart != 0) {
                $chackRadio = '';
            }
            if ($channeltype != $channel && !empty($channel)) {
                continue;
            }
            if ($chackRadio != '') {
                $soncat .= "<div class='list'><label>".$chackRadio .' '. $typeName."</label></div>\r\n";
                $this->LogicListAllSunType($id, $channel, $soncat);
            } else {
                $soncat .= "<div class='list'>".$typeName."</div>\r\n";
                $this->LogicListAllSunType($id, $channel, $soncat);
            }
        }
    }
}//End Class
?>