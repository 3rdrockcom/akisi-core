<?php
/**
 * @Author: Robert Ram Bolista
 * @Date:   2015-07-01 15:23:11
 * @Last Modified by:   Robert Ram Bolista
 * @Last Modified time: 2016-01-29 16:39:28
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
	public $data;
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('theme_model');
        $this->data = $this->theme_model->config();
    }

    
    public function reformstring($str="",$num=2,$fill="0",$isback=0){
        $tmp = $str;
        while(strlen($tmp)<$num){
           if($isback) $tmp .= $fill;
           else $tmp = $fill.$tmp;
        }
        return $tmp;
    }

}