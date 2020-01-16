<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model('theme_model');
        $this->load->model('configuration_model');
        
    }

    public function index(){
        $this->check_posts();
      if(!islogged()){
         forcelogout();
      }else{

      $data['template']['menuid_selected'] = '';
      $data['template']['mod_menu_id']     = '';
      $data['template']['page_title']      = '';
      $data['template']['content']         = 'welcome_message';

      /** SET UP FOR HEADER */
      $data['template']['page_nav']        = $this->configuration_model->load_menus('',$this->session->userdata('role'),$this->session->userdata('userid'));
      $data['template']['no_main_header']  = false;
      $data['template']['page_html_prop']  = array();
      $data['template']['page_body_prop']  = array();
      $data['template']['pagecss']         = "";
      $data['template']['logtype']         = "";
      $data['template']['setup']           = $this->theme_model->config();
      $data['template']['sqlrole']         = $this->configuration_model->load_user_role($this->session->userdata('userid'))->result();
      $data['template']['countrole']       = $this->configuration_model->load_user_role($this->session->userdata('userid'))->num_rows();
      $data['template']['defaultmodule']   = "All";
      $data['template']['defaulticon']     = "";

      $this->load->view('includes/template', $data);
      }

    }

    /*
    public function index(){
      $this->check_posts();
      if(!islogged()){
         forcelogout();
      }else{
        $data['template']['content']         = 'menus/loan_info/customer';
        $data['template']['page_title']      = 'Customer Info';
        $data['template']['page_nav'] = $this->theme_model->menus();
        $data['template']['setup']      = $this->theme_model->config();
        $data['template']['page_nav']["customer"]["active"] = true;
        $this->load->view('includes/template', $data);
      }

    }
    */

    /**
     * [menus - url of the system that has been decrypted]
     * @param  varchar $view      - php file located in view folder of codeigniter
     * @param  int $canread   - check if the user has read access
     * @param  int $canadd    - check if the user has add access
     * @param  int $canedit   - check if the user has edit access
     * @param  int $candelete - check if the user has delete access
     * @param  int $fkmenuid  - unique identity of a menu
     * @param  varchar $folder  - path where you find the $view
     */
    function menus($view,$canread,$canadd,$canedit,$candelete,$fkmenuid,$folder=""){

        if(!islogged()){
            forcelogout();
        }else{
            $data['setup']     = $this->theme_model->config();
            $data['canread']   = $canread;
            $data['canadd']    = $canadd;
            $data['canedit']   = $canedit;
            $data['candelete'] = $candelete;
            $data['fkmenuid']  = $fkmenuid;
            $folders = str_replace("~","/",$folder);
            if($folders) $this->load->view("menus/$folders/$view",$data);
            else $this->load->view("menus/$view",$data);
        }
    }

    

    /**
     * [check_posts - redirect to private function in main]
     */
    private function check_posts(){
        $type = $this->input->post('function_ctrl');
        switch($type){
            case 'login': $this->validate();

        }
    }

    /**
     * [sessionchecker - check if the session is still active or not]
     */
    function sessionchecker(){
        $result = 0;
        if(!islogged()) $result = 1;
        echo "<user>
                <result>{$result}</result>
              </user>";
    }

    function logout(){
        forcelogout();
    }

    

    /**
     * [encrypt_url - encrypt/decrypt the url of the system]
     * @return [json]
     */
    function encrypt_url(){
      $option = $this->input->post('enc');
      $decode = $this->ram_encryption->decode($option,$this->session->userdata('random'));
      $val = array("decode"=>$decode);
      header('Access-Control-Allow-Origin: *');
      header("Content-Type: application/json");
      echo json_encode($val);

    }

}



/* End of file main.php */
/* Location: ./application/controllers/main.php */