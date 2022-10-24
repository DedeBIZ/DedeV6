<?php

namespace DedeBIZ\API;

/**
 * 评论模型
 *
 * @version        $Id: feedback.php$
 * @package        DedeBIZ.API
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

use DedeBIZ\libraries\Model;

if (!defined('DEDEAPI')) exit('dedebiz');
class FeedbackModel extends Model
{
    /**
     * 发表评论
     *
     * @param  mixed $aid 文档id
     * @param  mixed $title 文档标题
     * @param  mixed $fid 评论id，大于0则是回复
     * @param  mixed $mid 会员id
     * @param  mixed $username 会员名称
     * @param  mixed $typeid 栏目id
     * @param  mixed $msg 评论内容
     * @return void
     */
    public function Send($aid, $title = '', $fid, $mid, $username = '', $typeid = 0, $msg)
    {
        $ip = GetIP();
        $dtime = time();
        $inquery = "INSERT INTO `#@__feedback` (`aid`,`typeid`,`fid`, `username`,`arctitle`,`ip`,`ischeck`,`dtime`, `mid`,`bad`,`good`,`ftype`,`face`,`msg`) VALUES (:aid,:typeid,:fid,:username,:title,:ip,'0',:dtime, :mid,'0','0','feedback','0',:msg);";
        return $this->dsql->ExecuteNoneQueryParams($inquery, array(
            'aid' => $aid,
            'title' => $title,
            'typeid' => $typeid,
            'fid' => $fid,
            'ip' => $ip,
            'dtime' => $dtime,
            'mid' => $mid,
            'username' => $username,
            'msg' => $msg,
        ));
    }

    /**
     * 根据会员id获取最后一条评论
     *
     * @param  mixed $mid 会员id
     * @return void
     */
    public function GetLastOneByMid($mid)
    {
        return $this->dsql->GetOneParams("SELECT dtime FROM `#@__feedback` WHERE `mid` = :mid ORDER BY `id` DESC", array('mid' => $mid));
    }
    
    /**
     * 根据文章id获取评论
     *
     * @param  mixed $aid 内容id
     * @param  mixed $page 页码
     * @param  mixed $pagesize 每页条数
     * @param  mixed $orderby 排序
     * @return void
     */
    public function GetFeedbacksByAid($aid, $page = 1, $pagesize = 10, $orderby = '')
    {
        $result = array();
        $page = $page < 1 ? 1 : (int)$page;
        $order = " ORDER BY id DESC";
        if (!empty($orderby)) {
            switch ($orderby) {
                case 'good':
                    $order = " ORDER BY good DESC";
                    break;
                case 'time':
                    $order = " ORDER BY dtime DESC";
                    break;
                default:
                    $order = " ORDER BY id DESC";
                    break;
            }
        }
        $limit_s = ($page - 1) * $pagesize;
        $wsql = " WHERE fb.ischeck=1 AND fb.fid=0 ";
        $wsql .= " AND fb.aid=:aid";
        $cquery = "SELECT COUNT(*) as dd FROM `#@__feedback` fb $wsql";
        $row = $this->dsql->GetOneParams($cquery, array("aid" => $aid));
        $result['total'] = $row['dd'];
        $equery = "SELECT fb.*,mb.userid,mb.face as mface,mb.spacesta,mb.scores,mb.sex FROM `#@__feedback` fb LEFT JOIN `#@__member` mb on mb.mid = fb.mid $wsql $order LIMIT :limit_offset,:limit_rows";
        $this->dsql->Prepare('fb', $equery);
        $this->dsql->BindParam('fb', "aid", $aid, \PDO::PARAM_INT);
        $this->dsql->BindParam('fb', "limit_offset", (int)$limit_s, \PDO::PARAM_INT);
        $this->dsql->BindParam('fb', "limit_rows", (int)$pagesize, \PDO::PARAM_INT);
        $this->dsql->ExecuteBind('fb');
        while ($arr = $this->dsql->GetArray('fb')) {
            $arr['face'] = empty($arr['mface']) ? $GLOBALS['cfg_cmspath'] . '/static/web/img/avatar.png' : $arr['mface'];
            $result['data'][] = $arr;
        }
        return $result;
    }
}
