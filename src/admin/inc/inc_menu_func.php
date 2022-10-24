<?php
/**
 * 管理菜单函数
 *
 * @version        $Id: inc_menu_func.php 2022-07-01 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Login\UserLogin;
use DedeBIZ\Template\DedeTagParse;
require_once(dirname(__FILE__)."/../config.php");
$headTemplet = '<li><div class="link"><i class="fa ~icon~"></i>~channelname~<i class="fa fa-angle-down"></i></div><ul class="submenu">';
$footTemplet = "</ul></li>";
$itemTemplet = "<li>~link~</li>";
function GetMenus($userrank, $topos = 'main')
{
    global $openitem, $headTemplet, $footTemplet, $itemTemplet;
    if ($topos == 'main') {
        $openitem = (empty($openitem) ? 1 : $openitem);
        $menus = $GLOBALS['menusMain'];
    } else if ($topos == 'module') {
        $openitem = 100;
        $menus = $GLOBALS['menusMoudle'];
    }
    $dtp = new DedeTagParse();
    $dtp->SetNameSpace('m', '<', '>');
    $dtp->LoadSource($menus);
    $dtp2 = new DedeTagParse();
    $dtp2->SetNameSpace('m', '<', '>');
    $m = 0;
    foreach ($dtp->CTags as $i => $ctag) {
        if ($ctag->GetName() == 'top' && ($ctag->GetAtt('rank') == '' || UserLogin::TestPurview($ctag->GetAtt('rank')))) {
            if ($openitem != 999 && !preg_match("#".$openitem.'_'."#", $ctag->GetAtt('item')) && $openitem != 100) continue;
            $htmp = str_replace("~channelname~", Lang($ctag->GetAtt("name")), $headTemplet);
            if (empty($openitem) || $openitem == 100) {
                if ($ctag->GetAtt('notshowall') == '1') continue;
                $htmp = str_replace('~display~', $ctag->GetAtt('display'), $htmp);
            } else {
                if ($openitem == $ctag->GetAtt('item') || preg_match("#".$openitem.'_'."#", $ctag->GetAtt('item')) || $openitem == '-1')
                    $htmp = str_replace('~display~', 'block', $htmp);
                else
                    $htmp = str_replace('~display~', 'none', $htmp);
            }
            $icon = 'fa-plug';
            if ($ctag->GetAtt('icon') != '') {
                $icon = $ctag->GetAtt('icon');
            }
            $htmp = str_replace('~icon~', $icon, $htmp);
            $htmp = str_replace('~cc~', $m.'_'.$openitem, $htmp);
            echo $htmp;
            $dtp2->LoadSource($ctag->InnerText);
            foreach ($dtp2->CTags as $j => $ctag2) {
                $ischannel = trim($ctag2->GetAtt('ischannel'));
                if ($ctag2->GetName() == 'item' && ($ctag2->GetAtt('rank') == '' || UserLogin::TestPurview($ctag2->GetAtt('rank')))) {
                    $link = "<a href='".$ctag2->GetAtt('link')."' target='".$ctag2->GetAtt('target')."'>".Lang($ctag2->GetAtt('name'))."</a>";
                    if ($ischannel == '1') {
                        if ($ctag2->GetAtt('addalt') != '') {
                            $addalt = Lang($ctag2->GetAtt('addalt'));
                        } else {
                            $addalt = '录入新内容';
                        }
                        if ($ctag2->GetAtt('addico') != '') {
                            $addico = $ctag2->GetAtt('addico');
                        } else {
                            $addico = 'fa-plus-circle';
                        }
                        $link = "$link<a href='".$ctag2->GetAtt('linkadd')."' class='submenu-right' target='".$ctag2->GetAtt('target')."'><span class='fa $addico' title='$addalt'></span></a>";
                    } else {
                        $link .= "";
                    }
                    $itemtmp = str_replace('~link~', $link, $itemTemplet);
                    echo $itemtmp;
                }
            }
            echo $footTemplet;
        }
    }
}//End Function
?>