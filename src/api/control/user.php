<?php
namespace DedeBIZ\API;
/**
 * 用户控制器
 *
 * @version        $Id: user.php$
 * @package        DedeBIZ.API
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\libraries\Control;
use DedeBIZ\Login\MemberLogin;
if (!defined('DEDEAPI')) exit('dedebiz');
class UserControl extends Control {
    private $cfg_ml;
    public function __construct()
    {
        parent::__construct();
        $this->cfg_ml = new MemberLogin();
    }
    private function _is_login(){
        return $this->cfg_ml->IsLogin();
    }
        
    /**
     * 上传头像
     *
     * @return void
     */
    public function upload_face()
    {
        global $cfg_max_face,$cfg_user_dir;
        if (!$this->_is_login()) {
            $this->message(-1, "", Lang("user_nologin"));
        }
        $maxlength = $cfg_max_face * 1024;
        $userdir = $cfg_user_dir.'/'.$this->cfg_ml->M_ID;
        $face  = $this->item('face', null);;
        if(is_uploaded_file($face))
        {
            if(@filesize($_FILES['face']['tmp_name']) > $maxlength)
            {
                $this->message(-1, "", Lang("err_upload_maxsize"));
                return;
            }
            $mime = get_mime_type($face);
            if (preg_match("#^unknow#", $mime)) {
                $this->message(-1, "", Lang("media_no_fileinfo"));
                return;
            }
            if (!preg_match("#^image#i", $mime)) {
                $this->message(-1, "", Lang("media_only_image"));
                return;
            }
            $face = MemberUploads('face', "", $this->cfg_ml->M_ID, 'image', 'myface', 180, 180,false, false, true);
            if (is_array($face)) {
                $this->message(0, $face['filename']);
            } else {
                $this->message(-1, "", $face);
            }
        }
    }
}
?>