<?php



if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('islogged')){
    function islogged(){
        $CI =& get_instance();
        return $CI->session->userdata("logged_in");
    }
}

if (!function_exists('loadloginform')){
    function loadloginform(){
        $CI                           =& get_instance();
        $data['no_main_header']       = true;
        $data['title']                = 'Login';
        $data['content']              = 'login';
        $data['autoload']             = 'autofocusinput()';
        $data['setup']                = $CI->theme_model->config();
        
        $data['template']['page_nav'] = "";
        $data['page_html_prop']       = array("id"=>"extr-page", "class"=>"animated fadeInDown");
        $data['page_body_prop']       = array();
        $data['pagecss']              = "";
        $data['logtype']              = "LOGIN";
        $data['sqlrole']              = array();
        $data['countrole']            = 0;
        $data['defaultmodule']        = "All";
        $data['defaulticon']          = "";
        $CI->load->view('login', $data);
    }
}

if (!function_exists('loadforgotpassword')){
    function loadforgotpassword(){
        $CI                           =& get_instance();
        $data['no_main_header']       = true;
        $data['title']                = 'Forgot Password';
        $data['content']              = 'forgotpassword';
        $data['autoload']             = 'autofocusinput()';
        $data['setup']                = $CI->theme_model->config();
        
        $data['template']['page_nav'] = "";
        $data['page_html_prop']       = array("id"=>"extr-page", "class"=>"animated fadeInDown");
        $data['page_body_prop']       = array();
        $data['pagecss']              = "";
        $data['logtype']              = "FORGOT PASSWORD";
        $data['sqlrole']              = array();
        $data['countrole']            = 0;
        $data['defaultmodule']        = "All";
        $data['defaulticon']          = "";
        $CI->load->view('forgotpassword', $data);
    }
}

if (!function_exists('forcelogout')){
    function forcelogout(){
        $CI =& get_instance();
        $CI->db->query("update tblUserInfo set ipadd='' WHERE username='".$CI->session->userdata('username')."'");
        $CI->session->sess_destroy();
        redirect("login", "refresh");
    }
}
if (!function_exists('template_loader')){
    function template_loader(){
        $CI =& get_instance();
        $data['title'] = $CI->input->post("titlepage");
        $data['rootid'] = $CI->input->post("rootid");
        $data['menuid_selected'] = $CI->input->post("menuid");
        $data['mod_menu_id'] = $CI->input->post("mod_menu_id");
        $data['autoload'] = '';
        $data['template'] = $CI->setup->config();
        $CI->load->model("user");
        $CI->user->loaduserdata($data);
        return $data;
    }
}


