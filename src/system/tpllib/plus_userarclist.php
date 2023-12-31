<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 动态模板文档列表标签
 *
 * @version        $id:plus_userarclist.php tianya $
 * @package        DedeBIZ.Tpllib
 * @copyright      Copyright (c) 2023 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
function plus_userarclist(&$atts, &$refObj, &$fields)
{
    global $dsql,$_vars;
    $attlist = "channel=1,titlelen=30,infolen=200,row=8,imgwidth=120,imgheight=90";
    FillAtts($atts,$attlist);
    FillFields($atts,$fields,$refObj);
    extract($atts, EXTR_OVERWRITE);

    $sql = "SELECT arc.*,mt.mtypename,tp.typedir,tp.typename,tp.isdefault,tp.defaultname,tp.namerule,
        tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath
        FROM `#@__archives` arc
        LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id
        LEFT JOIN `#@__mtypes` mt ON mt.mtypeid=arc.mtype
        WHERE arc.mid='{$_vars['mid']}' AND arc.channel=$channel AND arc.arcrank=0
        ORDER BY id DESC LIMIT 0,$row";

    $dsql->SetQuery($sql);
    $dsql->Execute("ul");
    $rearr = array();
    while($row = $dsql->GetArray("ul"))
    {
        //处理一些特殊字段
        $row['infos'] = cn_substr($row['description'],$infolen);
        $row['arcurl'] = GetFileUrl($row['id'],$row['typeid'],$row['senddate'],$row['title'],$row['ismake'],
        $row['arcrank'],$row['namerule'],$row['typedir'],$row['money'],$row['filename'],$row['moresite'],$row['siteurl'],$row['sitepath']);
        $row['typeurl'] = GetTypeUrl($row['typeid'],$row['typedir'],$row['isdefault'],$row['defaultname'],$row['ispart'],
        $row['namerule2'],$row['moresite'],$row['siteurl'],$row['sitepath']);
        if($row['litpic']=='') $row['litpic'] = '/images/defaultpic.gif';
        if(!preg_match("#^(http|https):\/\/#i", $row['litpic']))
        {
            $row['picname'] = $row['litpic'] = $GLOBALS['cfg_cmsurl'].$row['litpic'];
        } else {
            $row['picname'] = $row['litpic'];
        }
        $row['stime'] = GetDateMK($row['pubdate']);
        $row['typelink'] = "<a href='".$row['typeurl']."'>".$row['typename']."</a>";
        $row['image'] = "<img src='".$row['picname']."' border='0' width='$imgwidth' height='$imgheight' alt='".preg_replace("#['><]#", "", $row['title'])."'>";
        $row['imglink'] = "<a href='".$row['filename']."'>".$row['image']."</a>";
        $row['fulltitle'] = $row['title'];
        $row['title'] = cn_substr($row['title'],$titlelen);
        if($row['color']!='') {
            $row['title'] = "<font color='".$row['color']."'>".$row['title']."</font>";
        }
        if(preg_match('#b#', $row['flag']))
        {
            $row['title'] = "<strong>".$row['title']."</strong>";
        }
        $row['textlink'] = "<a href='".$row['filename']."'>".$row['title']."</a>";
        $row['plusurl'] = $row['phpurl'] = $GLOBALS['cfg_phpurl'];
        $row['memberurl'] = $GLOBALS['cfg_memberurl'];
        $row['templeturl'] = $GLOBALS['cfg_templeturl'];

        $rearr[] = $row;
    }
    $dsql->FreeResult("ul");
    return $rearr;
}
?>