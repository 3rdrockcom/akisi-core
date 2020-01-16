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

class Salesforce extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->database();
    }

    public function get_customer_profile_get() {
        //single row will be returned
        $profile_id = $this->get('profile_id');

        $profile = $this->db->get_where('tt_salesforce', array('profile_id' => $profile_id))->row_array();
        //check if the customer data exists
        if(!empty($profile)){
            //set the response and exit
            $this->response($profile, REST_Controller::HTTP_OK);
        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No profile were found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function customer_activation_post(){
        $cData           = array();
        $cData['first_name']  = $this->post('R1');
        $cData['last_name']  = $this->post('R2');
        $cData['middle_name']  = $this->post('R3');
        $cData['email'] = $this->post('R4');
        $cData['mobile'] = $this->post('R5');
        $cData['card_number']  = $this->post('R6');

        if(!empty($cData['first_name']) && !empty($cData['last_name']) && !empty($cData['email']) && !empty($cData['mobile']) && !empty($cData['card_number']) ){
            $check_card_number = $this->db->get_where('tt_salesforce_cardnumber', array('card_number' => $this->post('R6')))->row_array();
            if(!empty($check_card_number)){

                if($check_card_number['status']=="1"){
                    $this->response([
                        'status' => FALSE,
                        'message' => 'This card number is already used or activated.',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }else{
                    $insert = $this->db->insert('tt_salesforce', $cData);
                    
                    //check if the customer data inserted
                    if($insert){
                        $profile_id = $this->db->insert_id();
                        //set the response and exit
                        $this->db->update('tt_salesforce_cardnumber', array('status'=>'1'), array('card_number'=>$this->post('R6')));
                        $this->response([
                            'status' => TRUE,
                            'message' => 'profile activation successful.',
                            'profile_id' => $profile_id,
                            'response_code' => REST_Controller::HTTP_OK
                        ], REST_Controller::HTTP_OK);
                    }else{
                        //set the response and exit
                        $this->response([
                            'status' => FALSE,
                            'message' => 'Some problems occurred, please try again.',
                            'response_code' => REST_Controller::HTTP_BAD_REQUEST
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                }
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Invalid card number.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete profile information to create.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

    }

}

?>