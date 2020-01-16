<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

//include Rest Controller library
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
require APPPATH . '/libraries/Format.php';
/**
* create a api using post, get, put, delete
* _get supported formats ('json','array','csv','html',jsonp','php','serialized','xml'
* _post supported formats

* supported response format
* 'array' (For GET Request Only) - Array data structure
* 'csv' (text/csv) - Comma separated file
* 'json' (application/json) - Uses json_encode(). Note: If a GET query string called 'callback' is passed, then jsonp will be returned
* 'html' (text/html) - HTML using the table library in CodeIgniter
* 'php' (For GET Request Only) - Uses var_export()
* 'serialized' (For GET Request Only) - Uses serialize()
* 'xml' (application/xml) - Uses simplexml_load_string()

* # Error Codes and Status Codes
* 200 = HTTP_OK (The request has succeeded).
* 102 = HTTP_PROCESSING
* 302 = HTTP_FOUND
* 400 = HTTP_BAD_REQUEST (The request cannot be fulfilled due to multiple errors).
* 401 = HTTP_UNAUTHORIZED (The user is unauthorized to access the requested resource).
* 403 = HTTP_FORBIDDEN (The requested resource is unavailable at this present time).
* 404 = HTTP_NOT_FOUND (This is sometimes used to mask if there was an UNAUTHORIZED (401) or FORBIDDEN (403) error, for security reasons).
* 405 = HTTP_METHOD_NOT_ALLOWED (The request method is not supported by the following resource).
* 408 = HTTP_REQUEST_TIMEOUT
*/

class Genesis extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('customer_model');
        $this->load->model('settlement_model');
        $this->load->model('genesis_model');
        $this->load->helper('url');
    }

    public function check_registration_status_post(){
        $cData['dpi']  = $this->post('R2');

        $customer = $this->customer_model->get_customer($this->post('R1'));
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $data = array();
        $data['genesis_finish_registration'] = $customer['genesis_finish_registration'];
        $data['genesis_link_account'] = $customer['genesis_link_account'];
        $data['genesis_userid'] = $customer['genesis_userid'];
        $data['genesis_reg_step'] = $customer['genesis_reg_step'];
        $this->response($data, REST_Controller::HTTP_OK);
    }

    public function check_if_has_genesis_account_post(){
        $cData['token']  = $this->post('R1');
        $cData['dpi']  = $this->post('R2');
        $genesis = array();
        if(!empty($cData['token']) && !empty($cData['dpi'])){
            $data = $this->genesis_model->account_balance($cData);
            if($data['response_code']=="200" && $data['code']=="200"){
                $genesis['status']=true;
                $genesis['message']=$data['message'];
                $genesis['balance']=$data['data']['balance'];
                $genesis['credit_number']=$data['data']['credit_number'];
                $genesis['client_id']=$data['data']['client_id'];

                $this->response($genesis, $data['response_code']);
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => $data['message'],
                    'response_code' => $data['code']
                ], $data['code']);
            }
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }


    public function finish_registration_post(){
        $user_id  = $this->post('R2');
        if(empty($user_id)){
            $this->response([
                'status' => FALSE,
                'message' => 'Please provide genesis user id.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $customer = $this->customer_model->get_customer($this->post('R1'));
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $cdata['genesis_finish_registration'] = "1";
        $cdata['genesis_reg_step'] = "Finished";
        $update = $this->db->update('tbl_customer', $cdata, array('customer_reference'=>$this->post('R1'),'genesis_userid'=>$user_id));
        if($update){
            $this->response([
                'status' => TRUE,
                'message' => 'Your Genesis registration is finished.',
                'response_code' => REST_Controller::HTTP_OK
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Some problems occurred, please try again.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
        
    }

    public function link_genesis_account_to_akisi_post(){
        $cData['dpi']  = $this->post('R2');
        if(empty($cData['dpi'])){
            $this->response([
                'status' => FALSE,
                'message' => 'Please provide dpi number.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $customer = $this->customer_model->get_customer($this->post('R1'));
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $cdata['genesis_link_account'] = "1";
        $cdata['genesis_reg_step'] = "Linked";
        $this->db->update('tbl_customer', $cdata, array('customer_reference'=>$this->post('R1')));

        $this->response([
            'status' => TRUE,
            'message' => 'You successfully linked your genesis to your akisi wallet.',
            'response_code' => REST_Controller::HTTP_OK
        ], REST_Controller::HTTP_OK);
    }

    public function get_token_provider_post(){
        $cData['provider_id']  = $this->post('R1');
        $cData['provider_password']  = $this->post('R2');
        if(!empty($cData['provider_id']) && !empty($cData['provider_password'])){
            $status = $this->genesis_model->get_token_providers($cData);
            if(!empty($status)){
                $this->response($status, $status['response_code']);
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Some problems occurred, please try again.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function get_initial_data_post(){
        $token = $this->post('R1');
        if(!empty($token)){
            $data = $this->genesis_model->get_initial_data($token);
            $this->response($data, $data['response_code']);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    

    public function account_balance_post(){
        $cData['token']  = $this->post('R1');
        $cData['dpi']  = $this->post('R2');
        if(!empty($cData['token']) && !empty($cData['dpi'])){
            $data = $this->genesis_model->account_balance($cData);
            $this->response($data, $data['response_code']);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function make_transaction_payment_post(){
        $cData['token']  = $this->post('R1');
        $cData['merchant_id']  = $this->post('R2');
        $cData['client_id']  = $this->post('R3');
        $cData['amount']  = $this->post('R4');
        if(!empty($cData['token']) && !empty($cData['merchant_id']) && !empty($cData['client_id']) && !empty($cData['amount'])){
            $data = $this->genesis_model->make_transaction_payment($cData);
            $this->response($data, $data['response_code']);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function user_account_register_post(){
        $cData['token']  = $this->post('R1');
        $cData['first_name']  = $this->post('R2');
        $cData['lastname']  = $this->post('R3');
        $cData['second_lastname']  = $this->post('R4');
        $cData['second_name']  = $this->post('R5');
        $cData['birthday']  = $this->post('R6');
        $cData['place_birth_id']  = $this->post('R7');
        $cData['dpi']  = $this->post('R8');
        $cData['cellphone']  = $this->post('R9');
        $cData['email']  = $this->post('R10');
        $cData['password']  = $this->post('R11');
        $cData['username']  = $this->post('R12');
        $cData['customer_reference']  = $this->post('R13');

        $customer = $this->customer_model->get_customer($this->post('R13'));
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if(!empty($cData['token']) && !empty($cData['first_name']) && !empty($cData['lastname']) && !empty($cData['birthday']) && !empty($cData['place_birth_id']) && !empty($cData['dpi']) && !empty($cData['cellphone']) && !empty($cData['email']) && !empty($cData['username'])){
            $data = $this->genesis_model->user_account_register($cData);
            if($data['response_code']=="200"){
                $update['genesis_userid'] = $data['data']['user_id'];
                $update['genesis_reg_step'] = "user_registration";
                $this->db->update('tbl_customer', $update, array('customer_reference'=>$cData['customer_reference'],'dpi'=>$cData['dpi']));
            }
            $this->response($data, $data['response_code']);
            
            
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function step1_user_basic_post(){
        $cData['token']  = $this->post('R1');
        $cData['user_id']  = $this->post('R2');
        $cData['gender']  = $this->post('R3');
        $cData['civil_status_id']  = $this->post('R4');
        $cData['scholarship_id']  = $this->post('R5');

        if(!empty($cData['token']) && !empty($cData['user_id']) ){
            $data = $this->genesis_model->step1_user_basic($cData);
            if($data['response_code']=="200"){
                $update['genesis_reg_step'] = "step1";
                $this->db->update('tbl_customer', $update, array('genesis_userid'=>$cData['user_id']));
            }
            $this->response($data, $data['response_code']);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function step2_user_location_post(){
        $cData['token']  = $this->post('R1');
        $cData['user_id']  = $this->post('R2');
        $cData['department_id']  = $this->post('R3');
        $cData['municipality_id']  = $this->post('R4');
        $cData['address']  = $this->post('R5');
        $cData['residence_type_id']  = $this->post('R6');
        $cData['time_live_residence']  = $this->post('R7');

        if(!empty($cData['token']) && !empty($cData['user_id']) && !empty($cData['department_id']) && !empty($cData['municipality_id']) && !empty($cData['address']) && !empty($cData['residence_type_id']) && !empty($cData['time_live_residence']) ){
            $data = $this->genesis_model->step2_user_location($cData);
            if($data['response_code']=="200"){
                $update['genesis_reg_step'] = "step2";
                $this->db->update('tbl_customer', $update, array('genesis_userid'=>$cData['user_id']));
            }
            $this->response($data, $data['response_code']);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function step3_employment_situation_post(){
        $cData['token']  = $this->post('R1');
        $cData['user_id']  = $this->post('R2');
        $cData['employment_situation_id']  = $this->post('R3');
        $cData['date_admission']  = $this->post('R4');
        $cData['dependency_company_name']  = $this->post('R5');
        $cData['dependency_company_phone']  = $this->post('R6');
        $cData['monthly_expenses']  = $this->post('R7');
        $cData['business_start_date']  = $this->post('R8');
        $cData['own_economic_activity_id']  = $this->post('R9');
        $cData['own_business_address']  = $this->post('R10');
        $cData['monthly_sales']  = $this->post('R11');
        $cData['other_revenue']  = $this->post('R12');
        $cData['business_type']  = $this->post('R13');
        $cData['business_expenses']  = $this->post('R14');
        $cData['dependency_economic_activity_id']  = $this->post('R15');
        $cData['monthly_salary']  = $this->post('R16');
        $cData['own_business_phone']  = $this->post('R17');
        $cData['own_business_name']  = $this->post('R18');

        if(!empty($cData['token']) && !empty($cData['user_id']) && !empty($cData['employment_situation_id']) ){
            $data = $this->genesis_model->step3_employment_situation($cData);
            if($data['response_code']=="200"){
                $update['genesis_reg_step'] = "step3";
                $this->db->update('tbl_customer', $update, array('genesis_userid'=>$cData['user_id']));
            }
            $this->response($data, $data['response_code']);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function step4_client_reference_post(){
        $cData['token']  = $this->post('R1');
        $cData['user_id']  = $this->post('R2');
        $cData['reference_personal_one']  = $this->post('R3');
        $cData['reference_personal_phone_one']  = $this->post('R4');
        $cData['reference_personal_two']  = $this->post('R5');
        $cData['reference_personal_phone_two']  = $this->post('R6');
        $cData['amount_request']  = $this->post('R7');

        if(!empty($cData['token']) && !empty($cData['user_id']) && !empty($cData['reference_personal_one']) && !empty($cData['reference_personal_phone_one']) && !empty($cData['reference_personal_two']) && !empty($cData['reference_personal_phone_two']) && !empty($cData['amount_request']) ){
            $data = $this->genesis_model->step4_client_reference($cData);
            if($data['response_code']=="200"){
                $update['genesis_reg_step'] = "step4";
                $this->db->update('tbl_customer', $update, array('genesis_userid'=>$cData['user_id']));
            }
            $this->response($data, $data['response_code']);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function get_result_psycho_post(){
        $cData['token']  = $this->post('R1');
        $cData['dpi']  = $this->post('R2');

        if(!empty($cData['token']) && !empty($cData['dpi']) ){
            $data = $this->genesis_model->get_result_psycho($cData);
            $this->response($data, $data['response_code']);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function detail_transactions_post(){
        $cData['token']  = $this->post('R1');
        $cData['dpi']  = $this->post('R2');

        if(!empty($cData['token']) && !empty($cData['dpi']) ){
            $data = $this->genesis_model->detail_transactions($cData);
            $this->response($data, $data['response_code']);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

}

?>