<?php


if (!defined('BASEPATH')) exit('No direct script access allowed');

class Datatable_controller extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('datatables');
        $this->load->model('datatables/datatable_model','dt');
    }

    function index(){
        if(!islogged()){
                forcelogout();
        }else{
            switch($this->input->post('function_ctrl')){
                case "dbuserlist" : $this->dbuserlist();  break;
                case "role_list"  : $this->role_list();   break;
                case "clist"      : $this->clist();       break;
                case "menu_list"  : $this->menu_list();   break;
            }
        }
    }

    /**
    * retrieve data of user via datatable
    * @return array
    */
    private function dbuserlist(){
        $results = $this->dt->user_list();
        echo $results;
    }

    /**
    * retrieve data of user via datatable
    * @return array
    */
    private function role_list(){
        $results = $this->dt->role_list();
        echo $results;
    }

    /**
     * retrieve data of category via datatable
    * @return array
    */
    private function clist(){
        $results = $this->dt->category_ist();
        echo $results;
    }

    /**
     * retrieve data of menu via datatable
    * @return array
    */
    private function menu_list(){
        $results = $this->dt->menu_list();
        echo $results;
    }
}
