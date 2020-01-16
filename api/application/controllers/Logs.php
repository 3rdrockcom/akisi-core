<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
require APPPATH . '/libraries/Format.php';

class Logs extends REST_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
    }

    public function index_get(){
        $from = $this->get('from');
        $to = $this->get('to');
        $method = $this->get('method');

        if(!empty($this->get('from')) && !empty($this->get('to')) && !empty($this->get('method'))){
            $where = " WHERE date_run is not NULL  ";
            $where .= "AND uri LIKE '%{$method}%'  AND date_run BETWEEN '{$from}' AND '{$to}' ";
            $query = $this->db->query("SELECT uri,method,params,ip_address,rtime as runtime,authorized,date_run,response_code,response FROM tbl_api_logs $where ORDER BY date_run desc")->result_array();
            $this->response($query, REST_Controller::HTTP_OK);
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete information',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

}



/* End of file main.php */
/* Location: ./application/controllers/Logs.php */