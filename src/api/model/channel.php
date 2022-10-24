<?php
namespace DedeBIZ\API;
/**
 * 商城模型
 *
 * @version        $Id: channel.php$
 * @package        DedeBIZ.API
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\Model;
if (!defined('DEDEAPI')) exit('dedebiz');
class ChannelModel extends Model
{    
    /**
     * 根据cid获取主表
     *
     * @param  mixed $cid 频道ID
     * @return void
     */
    public function GetMaintable($cid = 1)
    {
        if ($cid > 0) {
            return '#@__archives';
        }
        if ($cid < 0) {
            $row = $this->dsql->GetOneParams("SELECT addtable FROM `#@__channeltype` WHERE id=:cid AND issystem='-1';", array('cid' => $cid));
            $maintable = empty($row['addtable']) ? '' : $row['addtable'];
        }
        return $maintable;
    }
}
?>