<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 前台提示对话框
 *
 * @version        $id:WebWindow.class.php 2 13:53 2010-11-11 tianya $
 * @package        DedeBIZ.Libraries
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC."/dedetag.class.php");
class WebWindow
{
    var $myWin = '';
    var $myWinItem = '';
    var $checkCode = '';
    var $formName = '';
    var $tmpCode = "//checkcode";
    var $hasStart = false;
    /**
     *  初始化为含表单的页面
     *
     * @param     string  $formaction  表单操作action
     * @param     string  $checkScript  检测验证脚本
     * @param     string  $formmethod  表单类型
     * @param     string  $formname  表单名称
     * @return    void
     */
    function Init($formaction = "", $checkScript = "/static/web/js/admin.blank.js", $formmethod = "POST", $formname = "myform")
    {
        $this->myWin .= "<script>";
        if ($checkScript != "" && file_exists($checkScript)) {
            $fp = fopen($checkScript, "r");
            $this->myWin .= fread($fp, filesize($checkScript));
            fclose($fp);
        } else {
            $this->myWin .= "function CheckSubmit(){return true;}";
        }
        $this->myWin .= "</script>";
        $this->formName = $formname;
        $this->myWin .= "<form name='$formname' action='$formaction' method='$formmethod' onSubmit='return CheckSubmit();'>";
    }
    /**
     *  添加隐藏域
     *
     * @param     string  $iname  隐藏域名称
     * @param     string  $ivalue  隐藏域值
     * @return    void
     */
    function AddHidden($iname, $ivalue)
    {
        $this->myWin .= "<input type='hidden' name='$iname' value='$ivalue'>";
    }
    /**
     *  开始窗口
     *
     * @return    void
     */
    function StartWin()
    {
        $this->myWin .= "<div class='table-responsive'>";
    }
    /**
     *  添加单列信息
     *
     * @access    public
     * @param     string  $ivalue  信息
     * @return    void
     */
    function AddMsgItem($ivalue)
    {
        $this->myWinItem .= $ivalue;
    }
    /**
     *  结束窗口
     *
     * @param     bool   $isform
     * @return    void
     */
    function CloseWin($isform = true)
    {
        if (!$isform) {
            $this->myWin .= "</div>";
        } else {
            $this->myWin .= "</div></form>";
        }
    }
    /**
     *  添加自定义脚本
     *
     * @param     string  $scripts
     * @return    void
     */
    function SetCheckScript($scripts)
    {
        $pos = strpos($this->myWin, $this->tmpCode);
        if ($pos > 0) {
            $this->myWin = substr_replace($this->myWin, $scripts, $pos, strlen($this->tmpCode));
        }
    }
    /**
     *  获取窗口
     *
     * @param     string  $wintype  菜单类型
     * @param     string  $msg  短消息
     * @param     bool  $isform  是否是表单
     * @return    string
     */
    function GetWindow($wintype = "save", $msg = "", $isform = true)
    {
        global $cfg_static_dir;
        $this->StartWin();
        $this->myWin .= $this->myWinItem;
        $tt = '';
        switch ($wintype) {
            case 'back':
                $tt = "返回";
                break;
            case 'ok':
                $tt = "确定";
                break;
            case 'reset':
                $tt = "重置";
                break;
            case 'search':
                $tt = "搜索";
                break;
            default:
                $tt = "保存";
                break;
        }
        if ($wintype != "") {
            if ($wintype != "hand") {
                $this->myWin .= "<div class='text-center'>
                    <button type='submit' class='btn btn-success btn-sm'>$tt</button>
                    <button type='button' class='btn btn-outline-success btn-sm' onclick='javascript:history.go(-1);'>返回</button>
                </div>";
            } else {
                if ($msg != "") {
                    $this->myWin .= "<div class='mb-3'>$msg</div>
                    <div class='text-center'>
                        <button type='button' class='btn btn-success btn-sm' onclick='javascript:history.go(-1);'>返回</button></td>
                    </div>";
                } else {
                    $this->myWin .= '';
                }
            }
        }
        $this->CloseWin($isform);
        return $this->myWin;
    }
    /**
     *  显示页面
     *
     * @access    public
     * @param     string  $modfile  模型模板
     * @return    string
     */
    function Display($modfile = "")
    {
        global $cfg_member_dir, $wintitle, $cfg_basedir;
        if (empty($wintitle)) {
            $wintitle = "提示对话框";
        }
        $ctp = new DedeTagParse();
        if ($modfile == '') {
            $ctp->LoadTemplate($cfg_basedir.$cfg_member_dir.'/templets/win_templet.htm');
        } else {
            $ctp->LoadTemplate($modfile);
        }
        $emnum = $ctp->Count;
        for ($i = 0; $i <= $emnum; $i++) {
            if (isset($GLOBALS[$ctp->CTags[$i]->GetTagName()])) {
                $ctp->Assign($i, $GLOBALS[$ctp->CTags[$i]->GetTagName()]);
            }
        }
        $ctp->Display();
        $ctp->Clear();
    }
}
/**
 *  显示一个不带表单的普通提示
 *
 * @access    public
 * @param     string   $msg  提示信息
 * @param     string   $title  提示标题
 * @return    string
 */
function ShowMsgWin($msg, $title)
{
    $win = new WebWindow();
    $win->Init();
    $win->mainTitle = "系统提示";
    $win->AddTitle($title);
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand");
    $win->Display();
}
?>