<?php
if (!defined('DEDEINC')) exit ('dedebiz');
/**
 * 提示对话框
 *
 * @version        $id:oxwindow.class.php 2 13:53 2010-11-11 tianya $
 * @package        .Libraries
 * @copyright      Copyright (c) 2022, .COM
 * @license        https://www..com/license
 * @link           https://www..com
 */
require_once(DEDEINC."/dedetag.class.php");
class OxWindow
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
        $this->myWin .= "<table>";
    }
    /**
     *  添加单列标题
     *
     * @access    public
     * @param     string  $title  标题
     * @param     string  $col  列
     * @return    string
     */
    function AddTitle($title, $col = "2")
    {
        if ($col != "" && $col != "0") {
            $colspan = "colspan='$col'";
        } else {
            $colspan = '';
        }
        $this->myWinItem .= "<tr>";
        $this->myWinItem .= "<td $colspan>$title</td>";
        $this->myWinItem .= "</tr>";
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
     *  添加两列信息
     *
     * @access    public
     * @param     string  $iname  名称
     * @param     string  $ivalue  值
     * @return    string
     */
    function AddItem($iname, $ivalue)
    {
        $this->myWinItem .= "<tr>";
        $this->myWinItem .= "<td width='260'>$iname</td>";
        $this->myWinItem .= "<td>$ivalue</td>";
        $this->myWinItem .= "</tr>";
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
            $this->myWin .= "</table>";
        } else {
            $this->myWin .= "</table></form>";
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
                $this->myWin .= "<tr>
                    <td colspan='2' align='center'>
                    <button type='submit' class='btn btn-success btn-sm'>$tt</button>
                    <button type='button' class='btn btn-outline-success btn-sm' onclick='javascript:history.go(-1);'>返回</button>
                    </td>
                </tr>";
            } else {
                if ($msg != "") {
                    $this->myWin .= "<tr>
                        <td>$msg</td>
                    </tr>
                    <tr>
                        <td colspan='2' align='center'><button type='button' class='btn btn-success btn-sm' onclick='javascript:history.go(-1);'>返回</button></td>
                    </tr>";
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
        global $cfg_templets_dir, $wecome_info, $cfg_basedir;
        if (empty($wecome_info)) {
            $wecome_info = "提示对话框";
        }
        $ctp = new DedeTagParse();
        if ($modfile == '') {
            $ctp->LoadTemplate($cfg_basedir.$cfg_templets_dir.'/apps/win_templet.htm');
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
}//End Class
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
    $win = new OxWindow();
    $win->Init();
    $win->mainTitle = "系统提示";
    $win->AddTitle($title);
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand");
    $win->Display();
}
?>