<?php
namespace DedeBIZ\API;
/**
 * 文档控制器
 *
 * @version        $Id: archive.php$
 * @package        DedeBIZ.API
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\Control;
if (!defined('DEDEAPI')) exit('dedebiz');
class ArchiveControl extends Control {
    public function __construct()
    {
        parent::__construct();
        $this->archive = $this->model('archive');
    }
    //内容统计
    public function count(){
        $aid = (int)$this->item('aid', 0);
        $cid = (int)$this->item('cid', 0);
        $mid = (int)$this->item('mid', 0);
        $click = $this->archive->UpdateCount($aid, $cid, $mid);
        $this->message(0, $click);
    }
}
?>