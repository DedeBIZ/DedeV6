<?php

namespace DedeBIZ\API;

/**
 * 评论控制器
 *
 * @version        $Id: feedback.php$
 * @package        DedeBIZ.API
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

use DedeBIZ\libraries\Control;
use DedeBIZ\Login\MemberLogin;

if (!defined('DEDEAPI')) exit('dedebiz');
class FeedbackControl extends Control
{

    public function __construct()
    {
        global $cfg_feedback_forbid;
        parent::__construct();
        if ($cfg_feedback_forbid == 'Y') {
            $this->message(-1, null, Lang("app_feedback_forbid"));
            exit;
        }
        $this->feedback = $this->model('feedback');
        $this->archive = $this->model('archive');
        $this->cfg_ml = new MemberLogin();
        if (!$this->cfg_ml->IsLogin()) {
            $this->message(-1, null, Lang("err_need_login"));
            exit;
        }
    }
    
    /**
     * 发表评论
     *
     * @return void
     */
    public function send()
    {
        global $cfg_feedback_time, $cfg_feedback_msglen;
        $aid = (int)$this->item('aid', 0);
        $fid = (int)$this->item('fid', 0);
        $msg = $this->item('msg', '');
        $typeid = $this->item('typeid', 0);
        $mid = $this->cfg_ml->M_ID;
        $username = $this->cfg_ml->M_UserName;
        $arcRow = $this->archive->GetOneArchive($aid);
        if ((empty($arcRow['aid']) || $arcRow['notpost'] == '1') && empty($fid)) {
            $this->message(-1, null, Lang("app_feedback_err_send"));
            exit;
        }
        if (!empty($cfg_feedback_time)) {
            //检查最后发表评论时间，如果未登录判断当前IP最后评论时间
            $row = $this->feedback->GetLastOneByMid($mid);
            if (is_array($row) && time() - $row['dtime'] < $cfg_feedback_time) {
                $this->message(-1, null, Lang("app_feedback_time"));
                exit;
            }
        }
        $msg = cn_substrR(TrimMsg($msg), $cfg_feedback_msglen);
        $title = isset($arcRow['title']) ? $arcRow['title'] : "";
        $rs = $this->feedback->Send($aid, $title, $fid, $mid, $username, $typeid, $msg);
        if (!$rs) {
            $this->message(-1, null, Lang("app_feedback_err_send"));
            exit;
        } else {
            $this->message(0, "success");
        }
    }
    
    /**
     * 评论列表
     *
     * @return void
     */
    public function feedbacks()
    {
        $aid = (int)$this->item('aid', 0);
        $rs = $this->feedback->GetFeedbacksByAid($aid);
        $this->message(0, $rs);
    }
}
