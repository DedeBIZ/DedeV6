<?php
namespace DedeBIZ\API;
/**
 * 商城模型
 *
 * @version        $Id: archive.php$
 * @package        DedeBIZ.API
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\Model;
if (!defined('DEDEAPI')) exit('dedebiz');
class ArchiveModel extends Model {
    /**
     * UpdateCount 更新点击数
     *
     * @param  mixed $aid 文档id
     * @param  mixed $cid 频道id
     * @param  mixed $mid 会员id
     * @return void
     */
    public function UpdateCount($aid=0, $cid = 0, $mid=0)
    {
        $idtype = 'id';
        $channel = $this->model('channel');
        $maintable = $channel->GetMaintable($cid);
        if ($cid < 0) {
            $idtype = 'aid';
        }
        if (!empty($maintable)) {
            $this->dsql->ExecuteNoneQuery("UPDATE `{$maintable}` SET click=click+1 WHERE {$idtype}='$aid'");
        }
        if (!empty($mid)) {
            $this->dsql->ExecuteNoneQuery("UPDATE `#@__member_tj` SET pagecount=pagecount+1 WHERE mid='$mid'");
        }
        $row = $this->dsql->GetOne("SELECT click FROM `{$maintable}` WHERE {$idtype}='$aid'");
        return is_array($row)? $row['click'] : 0;
    }
    
    /**
     * 根据id获取文档信息
     *
     * @param  mixed $aid
     * @return void
     */
    public function GetOneArchive($aid=0)
    {
        return GetOneArchive($aid);
    }
}
?>