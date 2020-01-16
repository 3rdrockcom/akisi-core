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

class Customer extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('customer_model');
        $this->load->model('settlement_model');
        $this->load->model('pronet_model');
        $this->load->helper('url');
    }

    public function mobile_registration_post(){
        $mobile = $this->post('R1');
        $user = $this->customer_model->get_user_bymobile($mobile);
        if(!empty($user)){
            $customer = $this->customer_model->get_customer_byuserid($user['userid']);
            if(!empty($customer)){
                $customer_reference = $customer['customer_reference'];
                $is_finish = $customer['is_finish_registration'];
                $is_verified = "1";
            }else{
                $customer_reference = "";
                $is_finish = "0";
                $checkifverified = $this->customer_model->check_ifuser_is_verified($user['userid']);
                if(!empty($checkifverified)){
                    $is_verified = "1";
                }else{
                    $is_verified = "0";
                }
                
            }
            $this->response([
                'status' => FALSE,
                'customer_reference' => $customer_reference,
                'is_finish' => $is_finish,
                'is_verified' => $is_verified,
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{
            $process = $this->customer_model->process_mobile_registration($this->post('R1'));
            if($process){
                $this->response([
                    'status' => TRUE,
                    'message' => 'Mobile registration successful. Please check you SMS for verification code.',
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }

    public function verify_mobile_registration_post(){
        $mobile = $this->post('R1');
        $verification_code = $this->post('R2');
        $user = $this->customer_model->get_user_bymobile($mobile);
        if(empty($user)){
            $this->response([
                'status' => FALSE,
                'message' => 'Mobile not exist',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{
            $validate = $this->customer_model->verify_mobile_registration($verification_code,$user['userid']);

            //check if the verifcation code is valid
            if($validate){
                //set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'Your Mobile Number has been verified.',
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Invalid verification Code.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
        
    }


    public function resend_mobile_verification_post(){
        $mobile = $this->post('R1');
        $user = $this->customer_model->get_user_bymobile($mobile);
        if(empty($user)){
            $this->response([
                'status' => FALSE,
                'message' => 'Mobile not exist',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{
            $process = $this->customer_model->resend_mobile_verification($this->post('R1'));
            if($process){
                $this->response([
                    'status' => TRUE,
                    'message' => 'Your mobile verification has been sent. Please check you SMS for verification code.',
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }

    public function customer_registration_post(){
        $cData           = array();
        $cData['fname']  = trim($this->post('R1'));
        $cData['mname']  = trim($this->post('R2'));
        $cData['lname']  = trim($this->post('R3'));
        $cData['slname'] = trim($this->post('R4'));
        $cData['mobile'] = $this->post('R5');
        $cData['email']  = $this->post('R6');

        $check_mobile = $this->customer_model->check_mobile_if_validate($this->post('R5'));
        if(!$check_mobile){
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid mobile number for registration.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $check_email = $this->customer_model->check_email_if_exist($this->post('R6'));
        if(!empty($check_email)){
            $this->response([
                'status' => FALSE,
                #'message' => 'Email already exist',
                'message' => 'Correo electronico ya existe',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }


        if(!empty($cData['fname']) && !empty($cData['lname']) && !empty($cData['mobile']) && !empty($cData['email']) && !empty($cData['email']) ){
            $insert = $this->customer_model->update_customer_registration($cData);
            //check if the customer data inserted
            if($insert['status']){
                // create a folder for a customer
                $dir = "../customer_document/c".$insert['customer_id'];
                if( is_dir($dir) === false )
                {
                    mkdir($dir);
                }

                //set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'customer registration successful.',
                    'customer_reference' => $insert['customer_reference'],
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{
                //set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete customer information to create.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

    }

    public function get_customer_get() {
        //single row will be returned
        $customer_reference = $this->get('customer_reference');
        $customer = $this->customer_model->get_customer($customer_reference);

        //check if the customer data exists
        if(!empty($customer)){
            //set the response and exit
            $this->response($customer, REST_Controller::HTTP_OK);
        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No customer were found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function get_security_question_get(){
        $question_id = $this->get('question_id');
        $questions = $this->customer_model->get_question($question_id);

        //check if the question data exists
        if(!empty($questions)){
            //set the response and exit
            $this->response($questions, REST_Controller::HTTP_OK);
        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No question(s) were found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function insert_customer_security_question_post(){
        $cData                    = array();
        #$cData['customer_reference']    = $this->post('R1');
        $cData['fk_question_id']  = $this->post('R2');
        $cData['answer']          = $this->post('R3');

        $customer = $this->customer_model->get_customer($this->post('R1'));
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        
        if(!empty($this->post('R1')) && !empty($cData['fk_question_id']) && !empty($cData['answer']) ){
            $cData['fk_customer_id']    = $customer['customer_id'];
            $insert = $this->customer_model->insert_customer_security_question($cData);
            //check if the customer data inserted
            if($insert){

                //set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'customer security question successfully added.',
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{
                //set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete customer information to insert.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function get_customer_security_question_get(){
        $question_id = $this->get('question_id');
        $customer_id = $this->get('customer_id');
        $customer_questions = $this->customer_model->get_customer_question($customer_id,$question_id);

        //check if the question data exists
        if(!empty($customer_questions)){
            //set the response and exit
            $this->response($customer_questions, REST_Controller::HTTP_OK);
        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No question(s) were found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function delete_customer_security_question_post(){
        $cData                    = array();
        $cData['fk_question_id']  = $this->post('R2');

        $customer = $this->customer_model->get_customer($this->post('R1'));
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if(!empty($this->post('R1')) && !empty($cData['fk_question_id']) ){
            $cData['fk_customer_id']  = $customer['customer_id'];
            $delete = $this->customer_model->delete_customer_security_question($cData);

            //check if the customer data inserted
            if($delete){
                //set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'Customer security question successfully deleted.',
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{
                //set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete customer information to delete.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function create_password_post(){
        $cData               = array();
        $cData['customer_reference']      = $this->post('R1');
        $cData['new_password']      = $this->post('R2');
        $cData['confirm_password']      = $this->post('R3');
        
        $customer = $this->customer_model->get_customer($this->post('R1'));
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if(!empty($cData['confirm_password']) && !empty($cData['new_password'])  && !empty($cData['customer_reference'])){
            if($this->post('R2') != $this->post('R3')){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Your password did not match',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }else{
                $update = $this->customer_model->create_password($cData);
            
                //check if the customer data inserted
                if($update){
                    //set the response and exit
                    $this->response([
                        'status' => TRUE,
                        'message' => 'Password successfully created.',
                        'response_code' => REST_Controller::HTTP_OK
                    ], REST_Controller::HTTP_OK);
                }else{
                    //set the response and exit
                    $this->response([
                        'status' => FALSE,
                        'message' => 'An error occured',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
                
                }
            }
            
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete information.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function change_password_post(){
        $cData               = array();
        $cData['old_password']      = $this->post('R2');
        $cData['new_password']      = $this->post('R3');

        $customer = $this->customer_model->get_customer($this->post('R1'));
            if(empty($customer)){
                $this->response([
                    'status' => FALSE,
                    'message' => 'customer not found',
                    'response_code' => REST_Controller::HTTP_NOT_FOUND
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        
        if(!empty($cData['old_password']) && !empty($cData['new_password'])){
            $update = $this->customer_model->change_password($cData,$customer['userid']);
            
            //check if the customer data inserted
            if($update){
                //set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'Your Password has been updated.',
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{
                //set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'Your old password is incorrect',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
            
            }
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete information.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function reset_password_post(){
        $mobile    = $this->post('R1');
        $user = $this->customer_model->get_user_bymobile($mobile);
        if(empty($user)){
            $this->response([
                'status' => FALSE,
                'message' => 'Mobile not exist.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{
            $password = generate_random_key(6);
            $md5 = md5($password);

            $update = $this->db->update('tblUserInfo', array('password'=>$md5,'reset_password'=>"1"), array('fk_userid'=>$user['userid']));

            if($update){
                #$message = "Your Akisi temporary password is ".$password.". Please login to your Akisi account to change your password.";
                $message = "Tu contraseña temporal de Akisi es ".$password.". Utilizala para iniciar  sesion en tu cuenta de Akisi para cambiar tu contraseña";
                send_sms($user['mobile'],$message);
                send_mail($message,$user['email'],"AKISI RESET PASSWORD");

                $this->response([
                    'status' => TRUE,
                    'message' => 'Se ha enviado un mensaje de texto con una contraseña temporal, favor de iniciar sesion utilizando esta contraseña. Al ingresar se te solicitara que puedas ingresar una nueva contraseña.',
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
                #'message' => 'You successfully reset your password. Please check your email and sms to get your temporary password.',
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            
        }
    }

    public function customer_login_post(){
        $uname    = $this->post('R1');
        $upass  = $this->post('R2');
        $password = md5($upass);
        if(!empty($this->post('R2')) && !empty($this->post('R1'))){
            $where = " WHERE password='$password' AND username='$uname' AND status='ACTIVE' ";
            $query = $this->db->query("SELECT a.fk_userid,a.username,a.status,a.ipadd,CONCAT(b.fname,' ',b.lname) AS FULLNAME,b.userno,b.gender,a.reset_password,c.customer_reference,c.is_finish_registration FROM tblUserInfo a INNER JOIN tblUserInformation b ON b.userid=a.fk_userid INNER JOIN tbl_customer c ON c.fk_userid=a.fk_userid $where ");
            if($query->num_rows()==1){
                $row = $query->row(0);
                if(!$row->is_finish_registration){
                    $this->response([
                        'status' => FALSE,
                        'message' => 'You have to finish your registration first to login.',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }

                $customer_reference = $row->customer_reference;
                $customer = $this->customer_model->get_customer($customer_reference);
                $image_url = base_url()."images/profile.png";
                $image = $this->db->get_where('tbl_customer_file', array('fk_customer_id' => $customer['customer_id'],'file_type'=>"PROFILE",'is_primary'=>"1"))->row_array();
                if(!empty($image)){
                    $image_url = $image['filepath'];
                }
                $data = array(
                    'username'  => $uname,
                    'fullname' => $row->FULLNAME,
                    #'userid' => $row->fk_userid,
                    'customer_reference' => $customer_reference,
                    'reset_password' => $row->reset_password,
                    'image_url' => $image_url,
                    'balance' => $customer['balance'],
                    'language' => $customer['language']
                );
                //set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'Login Success.',
                    'data' => $data,
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{
                //set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'Invalid Username or Password',
                    'response_code' => REST_Controller::HTTP_NOT_FOUND
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete information.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function upload_customer_file_post(){
        header('Access-Control-Allow-Origin: *');
        $this->load->helper('url');
        // Check form submit or not

        $data = array();
        if(!empty($_FILES['R1']['name']) && !empty($this->post('R2')) && !empty($this->post('R3'))){
            $cData                  = array();
            $customer_reference  = $this->post('R2');
            $cData['file_type']    = $this->post('R3');
            $cData['dpi_type']      = $this->post('R4');
            $cData['orig_filename'] = $_FILES['R1']['name'];

            $customer = $this->customer_model->get_customer($this->post('R2'));
            if(empty($customer)){
                $this->response([
                    'status' => FALSE,
                    'message' => 'customer not found',
                    'response_code' => REST_Controller::HTTP_NOT_FOUND
                ], REST_Controller::HTTP_NOT_FOUND);
            }
            $cData['fk_customer_id']    = $customer['customer_id'];

            $cData['file_extension'] = pathinfo($_FILES['R1']['name'], PATHINFO_EXTENSION);
            $cData['filename'] = generate_random_key(9);
            $cData['filepath'] = BASE_URL."customer_document/c".$cData['fk_customer_id']."/".$cData['filename'].".".$cData['file_extension'];
            // Set preference

            $config['upload_path'] = '../customer_document/c'.$cData['fk_customer_id'];
            $config['allowed_types'] = '*';
            $config['max_size'] = '10000'; // max_size in kb
            #$config['file_name'] = $_FILES['R1']['name'];
            $config['file_name'] = $cData['filename'];
            $config['overwrite'] = true;

            // Load upload library
            $this->load->library('upload',$config);
            $this->upload->initialize($config);
            // File upload
            if($this->upload->do_upload('R1')){
                // Get data about the file
                $uploadData = $this->upload->data();
                $insert_file = $this->customer_model->insert_customer_file($cData);
                if($insert_file){
                    $this->response([
                        'status' => TRUE,
                        'message' => 'Customer file is successfully uploaded.',
                        'response_code' => REST_Controller::HTTP_OK
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => $this->upload->display_errors(),
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

    public function check_dpi_post(){
        header('Access-Control-Allow-Origin: *');
        $this->load->helper('url');
        $dpi_b64 = $this->post('R2');
        $selfie_b64 = $this->post('R3');
        $reverse_b64 = $this->post('R4');
        $dpi_number = $this->post('R5');

        $check_if_dpi_exist = $this->customer_model->check_if_dpi_exist($dpi_number);
        
        if(!empty($dpi_b64) && !empty($selfie_b64) && !empty($reverse_b64) && !empty($dpi_number)){
        #if(!empty($_FILES['R2']['name']) && !empty($_FILES['R3']['name']) && !empty($_FILES['R4']['name'])){
            $customer_reference  = $this->post('R1');
            /*
            $file_tmp_dpi= $_FILES['R2']['tmp_name'];
            $file_tmp_selfie= $_FILES['R3']['tmp_name'];
            $file_tmp_reverse= $_FILES['R4']['tmp_name'];
            */

            $customer = $this->customer_model->get_customer($customer_reference);
            if(empty($customer)){
                $this->response([
                    'status' => FALSE,
                    'message' => 'customer not found',
                    'response_code' => REST_Controller::HTTP_NOT_FOUND
                ], REST_Controller::HTTP_NOT_FOUND);
            }

            /*
            $check_if_dpi_exist = $this->customer_model->check_if_dpi_exist($dpi_number);
            if(!empty($check_if_dpi_exist)){
                $this->response([
                    'status' => FALSE,
                    'message' => 'DPI number already exist.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            */

            //this is for testing only
            /*
            if (substr($customer['mobile'], 0,3) != "520" ){
                $this->response([
                    'status' => TRUE,
                    'message' => 'DPI matches',
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }
            */
            /*
            $data_dpi = file_get_contents($file_tmp_dpi);
            $data_selfie = file_get_contents($file_tmp_selfie);
            $data_reverse = file_get_contents($file_tmp_reverse);
            */
            list($type_dpi, $data_dpi) = explode(';', $dpi_b64);
            list($d, $data_dpi)      = explode(',', $data_dpi);
            list($type_selfie, $data_selfie) = explode(';', $selfie_b64);
            list($s, $data_selfie)      = explode(',', $data_selfie);
            list($type_reverse, $data_reverse) = explode(';', $reverse_b64);
            list($r, $data_reverse)      = explode(',', $data_reverse);
            #$dpi_b64 = base64_encode($data_dpi);
            #$selfie_b64 = base64_encode($data_selfie);
            #$reverse_b64 = base64_encode($data_reverse);

            $checkdpi = check_dpi($data_dpi,$data_selfie,$data_reverse);
            #echo $data_selfie;
            if(!$checkdpi['success']){
                $this->response([
                    'status' => FALSE,
                    'message' => $checkdpi['message'],
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            $result = $checkdpi['message'];
            if($result['Source']=="dpi" && $result['Target Selfie']>50 && $result['Match']>50 && strlen($result['Reverse'])==13){
                
                if( substr(trim($result['Reverse']), 0,13)!=substr($dpi_number, 0,13) ){
                    $this->response([
                        'status' => false,
                        'message' => 'invalid dpi number',
                        'data'=>$checkdpi['message'],
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
                
                /*
                $r = trim($result['Reverse']);
                $isvaliddpi = strpos($r, substr($dpi_number, 0,10));
                if($isvaliddpi==false){
                    $this->response([
                        'status' => false,
                        'message' => 'invalid dpi number',
                        'data'=>$checkdpi['message'],
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
                */

                
                $customer_id    = $customer['customer_id'];
                $dir = "../customer_document/c".$customer_id."/dpi";
                if( is_dir($dir) === false )
                {
                    mkdir($dir);
                }
                $dpi = array();
                $selfie = array();
                $reverse = array();

                //for dpi
                $data_dpi = base64_decode($data_dpi);
                file_put_contents($dir."/dpi.jpeg", $data_dpi);
                $dpi['filepath'] = BASE_URL."customer_document/c".$customer_id."/dpi"."/dpi.jpeg";
                /*
                $dpi['file_extension'] = pathinfo($_FILES['R2']['name'], PATHINFO_EXTENSION);
                $dpi['filename'] = "dpi";
                $dpi['filepath'] = BASE_URL."customer_document/c".$customer_id."/dpi"."/".$dpi['filename'].".".$dpi['file_extension'];

                $config['upload_path'] = $dir;
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                $config['max_size'] = '10000'; // max_size in kb
                $config['file_name'] = $dpi['filename'];
                $config['overwrite'] = true;

                // Load upload library
                $this->load->library('upload',$config);
                $this->upload->initialize($config);
                if($this->upload->do_upload('R2')){
                    #$uploadData = $this->upload->data();
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => $this->upload->display_errors(),
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
                */
                //for selfie

                $data_selfie = base64_decode($data_selfie);
                file_put_contents($dir."/selfie.jpeg", $data_selfie);
                $selfie['filepath'] = BASE_URL."customer_document/c".$customer_id."/dpi"."/selfie.jpeg";
                /*
                $selfie['file_extension'] = pathinfo($_FILES['R3']['name'], PATHINFO_EXTENSION);
                $selfie['filename'] = "selfie";
                $selfie['filepath'] = BASE_URL."customer_document/c".$customer_id."/dpi"."/".$selfie['filename'].".".$selfie['file_extension'];

                $config['upload_path'] = $dir;
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                $config['max_size'] = '10000'; // max_size in kb
                $config['file_name'] = $selfie['filename'];
                $config['overwrite'] = true;

                // Load upload library
                $this->load->library('upload',$config);
                $this->upload->initialize($config);
                if($this->upload->do_upload('R3')){
                    #$uploadData = $this->upload->data();
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => $this->upload->display_errors(),
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
                */

                //for reverse
                $data_reverse = base64_decode($data_reverse);
                file_put_contents($dir."/reverse.jpeg", $data_reverse);
                $reverse['filepath'] = BASE_URL."customer_document/c".$customer_id."/dpi"."/reverse.jpeg";
                /*
                $reverse['file_extension'] = pathinfo($_FILES['R4']['name'], PATHINFO_EXTENSION);
                $reverse['filename'] = "reverse";
                $reverse['filepath'] = BASE_URL."customer_document/c".$customer_id."/dpi"."/".$reverse['filename'].".".$reverse['file_extension'];

                $config['upload_path'] = $dir;
                $config['allowed_types'] = 'jpg|jpeg|png|gif';
                $config['max_size'] = '10000'; // max_size in kb
                $config['file_name'] = $reverse['filename'];
                $config['overwrite'] = true;

                // Load upload library
                $this->load->library('upload',$config);
                $this->upload->initialize($config);
                if($this->upload->do_upload('R4')){
                    #$uploadData = $this->upload->data();
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => $this->upload->display_errors(),
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
                */
                $cdata['dpi_url'] = $dpi['filepath'];
                $cdata['selfie_url'] = $selfie['filepath'];
                $cdata['reverse_url'] = $reverse['filepath'];
                $cdata['dpi_result'] = json_encode($checkdpi['message']);
                $cdata['dpi_reverse'] = $checkdpi['message']['Reverse'];
                $cdata['dpi'] = $dpi_number;
                $update = $this->db->update('tbl_customer', $cdata, array('customer_reference'=>$customer_reference));
                //if successfully store 
                $this->response([
                    'status' => TRUE,
                    'message' => 'DPI matches',
                    'data'=>$checkdpi['message'],
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{

                if(strlen($result['Reverse'])!=13){
                    $this->response([
                        'status' => false,
                        'message' => 'Please provide a better quality of back of the card.',
                        'data'=>$checkdpi['message'],
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }

                $this->response([
                    'status' => false,
                    'message' => 'DPI dont match',
                    'data'=>$checkdpi['message'],
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

    public function get_customer_file_get(){
        $customer_reference = $this->get('customer_reference');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $customer_id    = $customer['customer_id'];
        $file_type = $this->get('file_type');
        $is_primary = $this->get('is_primary');
        $customer = $this->customer_model->get_customer_file($customer_id,$file_type,$is_primary);

        //check if the customer data exists
        if(!empty($customer)){
            //set the response and exit
            $this->response($customer, REST_Controller::HTTP_OK);
        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No customer file were found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function finish_registration_post(){
        $customer_reference    = $this->post('R1');
        $dpi  = $this->post('R2');
        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid customer reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
        if(!empty($this->post('R1'))){
            $ben = array();
            $this->db->update('tbl_customer', array('is_finish_registration'=>"1"), array('customer_reference'=>$customer_reference));#,"dpi"=>$dpi
            $ben['customer_reference'] = $customer_reference;
            $ben['is_wallet_member'] = "1";
            if(!empty($customer['file_path'])) $ben['image_url'] = $customer['file_path'];
            $this->db->update('tbl_customer_beneficiary', $ben, array('mobile'=>$customer['mobile']));
            $this->response([
                'status' => TRUE,
                'message' => 'Your Registration is finish.',
                'response_code' => REST_Controller::HTTP_OK
            ], REST_Controller::HTTP_OK);
        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function get_beneficiary_get() {
        //single row will be returned
        $customer_reference = $this->get('customer_reference');
        $beneficiary_id = $this->get('beneficiary_id');
        $customer = $this->customer_model->get_customer($customer_reference);
        if(!empty($customer)){
            $customer_beneficiary = $this->customer_model->get_beneficiary($customer['customer_id'],$beneficiary_id);
            //check if the customer beneficiary exists
            if(!empty($customer_beneficiary)){
                //set the response and exit
                $this->response($customer_beneficiary, REST_Controller::HTTP_OK);
            }else{
                //set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'No beneficiary were found.',
                    'response_code' => REST_Controller::HTTP_NOT_FOUND
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid customer reference.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function search_beneficiary_get() {
        $customer_reference = $this->get('customer_reference');
        $search = $this->get('search');
        if(!empty($search) && !empty($customer_reference)){
            $customer = $this->customer_model->get_customer($customer_reference);
            if(!empty($customer)){
                $customer_beneficiary = $this->customer_model->search_beneficiary($customer['customer_id'],$search);
                //check if the customer beneficiary exists
                if(!empty($customer_beneficiary)){
                    //set the response and exit
                    $this->response($customer_beneficiary, REST_Controller::HTTP_OK);
                }else{
                    //set the response and exit
                    $this->response([
                        'status' => FALSE,
                        'message' => 'No beneficiary were found.',
                        'response_code' => REST_Controller::HTTP_NOT_FOUND
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
            }else{
                //set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'Invalid customer reference.',
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

    public function search_customer_bymobile_get() {
        //single row will be returned
        $mobile = $this->get('mobile');
        $customer = $this->customer_model->search_customer_bymobile($mobile);
        $data = array();

        //check if the customer data exists
        if(!empty($customer)){
            $data['customer_reference'] = $customer['customer_reference'];
            $data['first_name'] = $customer['fname'];
            $data['middle_name'] = $customer['mname'];
            $data['last_name'] = $customer['lname'];
            $data['second_last_name'] = $customer['slname'];
            $data['email'] = $customer['email'];
            $data['image_url'] = $customer['filepath'];
            //set the response and exit
            $this->response($data, REST_Controller::HTTP_OK);
        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No customer were found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function delete_beneficiary_post(){
        $cData                    = array();
        $cData['beneficiary_id']  = $this->post('R2');

        $customer = $this->customer_model->get_customer($this->post('R1'));
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid customer reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if(!empty($this->post('R1')) && !empty($cData['beneficiary_id']) ){
            $cData['fk_customer_id']  = $customer['customer_id'];
            $delete = $this->customer_model->delete_beneficiary($cData);

            //check if the customer data inserted
            if($delete){
                //set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'Beneficiary successfully deleted.',
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{
                //set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete customer information to delete.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function add_beneficiary_post(){
        $cData           = array();
        $customer_reference  = $this->post('R1');
        $cData['fname']  = $this->post('R2');
        $cData['mname']  = $this->post('R3');
        $cData['lname']  = $this->post('R4');
        $cData['slname'] = $this->post('R5');
        $cData['mobile'] = $this->post('R6');
        $cData['email']  = $this->post('R7');
        $cData['customer_reference']  = $this->post('R8');
        $cData['image_url']  = $this->post('R9');
        $cData['is_wallet_member'] = $this->post('R8')?"1":"0";

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid customer reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if($customer['mobile']==$cData['mobile']){
            $this->response([
                'status' => FALSE,
                'message' => "You cannot add your own wallet account as beneficiary",
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $check_beneficiary_if_exist = $this->customer_model->check_beneficiary($customer['customer_id'],$cData['mobile']);
        if(!empty($check_beneficiary_if_exist)){
            $this->response([
                'status' => FALSE,
                'message' => 'Beneficiary mobile number is already exist.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $cData['fk_customer_id']  = $customer['customer_id'];

        if(!empty($cData['fname']) && !empty($cData['lname']) && !empty($cData['mobile']) && !empty($cData['email'])){
            $insert = $this->customer_model->add_beneficiary($cData);
            //check if the customer data inserted
            if($insert['status']){
                // create a folder for a customer
                $dir = "../customer_document/b".$insert['beneficiary_id'];
                if( is_dir($dir) === false )
                {
                    mkdir($dir);
                }
                #$message_b = "You've been added as beneficiary to ".$customer['fname']." ".$customer['lname']."'s Akisi account.";
                $message_b = "Has sido agregado como beneficiario a la cuenta Akisi de ".$customer['fname']." ".$customer['lname'];

                #$message_a = "You added ".$cData['fname']." ".$cData['lname']." as beneficiary to your Akisi account.";
                $message_a = "Agrego a: ".$cData['fname']." ".$cData['lname']." como beneficiario de su cuenta de Akisi";

                send_sms($cData['mobile'],$message_b);
                
                send_sms($customer['mobile'],$message_a);

                //set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'beneficiary successfully added.',
                    'beneficiary_id' => $insert['beneficiary_id'],
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{
                //set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete beneficiary information to create.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

    }

    public function upload_beneficiary_file_post(){
        header('Access-Control-Allow-Origin: *');
        $this->load->helper('url');
        // Check form submit or not

        $data = array();
        if(!empty($_FILES['R1']['name']) && !empty($this->post('R2'))){
            $cData                  = array();
            $beneficiary_id  = $this->post('R2');
            $cData['orig_filename'] = $_FILES['R1']['name'];

            $beneficiary = $this->customer_model->get_beneficiary_by_id($beneficiary_id);
            if(empty($beneficiary)){
                $this->response([
                    'status' => FALSE,
                    'message' => 'beneficiary not found',
                    'response_code' => REST_Controller::HTTP_NOT_FOUND
                ], REST_Controller::HTTP_NOT_FOUND);
            }
            $cData['fk_beneficiary_id']    = $beneficiary['beneficiary_id'];

            $cData['file_extension'] = pathinfo($_FILES['R1']['name'], PATHINFO_EXTENSION);
            $cData['filename'] = generate_random_key(9);
            $cData['filepath'] = BASE_URL."customer_document/b".$cData['fk_beneficiary_id']."/".$cData['filename'].".".$cData['file_extension'];
            // Set preference

            $config['upload_path'] = '../customer_document/b'.$cData['fk_beneficiary_id'];
            $config['allowed_types'] = '*';
            $config['max_size'] = '10000'; // max_size in kb
            #$config['file_name'] = $_FILES['R1']['name'];
            $config['file_name'] = $cData['filename'];
            $config['overwrite'] = true;

            // Load upload library
            $this->load->library('upload',$config);
            $this->upload->initialize($config);
            // File upload
            if($this->upload->do_upload('R1')){
                // Get data about the file
                $uploadData = $this->upload->data();
                $insert_file = $this->customer_model->insert_beneficiary_file($cData);
                if($insert_file){
                    $this->response([
                        'status' => TRUE,
                        'message' => 'Beneficiary file is successfully uploaded.',
                        'response_code' => REST_Controller::HTTP_OK
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => $this->upload->display_errors(),
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

    //$source and $destination can be (customer_reference, merchant_reference)
    //compute if the transaction has a fee or not
    public function compute_send_money_transaction_post(){
        $source = $this->post('R1');
        $destination = $this->post('R2');
        $amount = $this->post('R3');
        $transaction_type = $this->post('R4'); // 1 if wallet to wallet and 2 if wallet to store

        $source_info = $this->customer_model->get_customer($source);
        $destination_info = $this->customer_model->get_customer($destination);

        

        if(empty($source_info) || empty($destination_info)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid customer reference.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if($transaction_type=="1"){ // wallet to wallet

            $return_fee = get_akisi_fee($amount);
            $fee_array = $return_fee['message'];
            if($fee_array['responseCode']=="00"){
                $total_amount = $amount + $fee_array['feepronet'];

                $data = array("amount"=>$amount,
                          "receipient"=>$destination_info['fname']." ".$source_info['lname'],
                          "transaction_type"=>$transaction_type,
                          "fee"=>$fee_array['feepronet'],
                          "total"=>$total_amount
                         );
                if($source_info['balance']<$total_amount){
                    $this->response([
                        'status' => FALSE,
                        'message' => 'insufficient balance',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }else{
                    $this->response($data, REST_Controller::HTTP_OK);
                }

            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Cannot retrieve akisi fee',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

        }else{ // wallet to store
            // not yet done ( need to add a fee if meron)

            $return_fee = get_akisi_fee($amount);
            $fee_array = $return_fee['message'];
            if($fee_array['responseCode']=="00"){
                $total_amount = $amount + $fee_array['feepronet'];
                $data = array("amount"=>$amount,
                          "receipient"=>$destination_info['fname']." ".$source_info['lname'],
                          "transaction_type"=>$transaction_type,
                          "fee"=>$fee_array['feepronet'],
                          "total"=>$total_amount
                         );
                $this->response($data, REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Cannot retrieve akisi fee',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            
            
        }
    }

    public function send_money_post(){
        $source = $this->post('R1');
        $destination = $this->post('R2');
        $amount = $this->post('R3');
        $fee = $this->post('R4');
        $transaction_type = $this->post('R5');
        $total = $amount + $fee;

        $source_info = $this->customer_model->get_customer($source);
        $destination_info = $this->customer_model->get_customer($destination);

        if($transaction_type=="1"){ // wallet to wallet
            if($source_info['balance']<$total){
                $this->response([
                    'status' => FALSE,
                    'message' => 'insufficient balance',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }else{
                $reference_code = rand ( 1000000000000 , 9999999999999 );
                $debit = $this->settlement_model->debit_customer_account($source_info['customer_id'],$source_info['balance'],$total);
                if($debit){
                    $credit = $this->settlement_model->credit_customer_account($destination_info['customer_id'],$destination_info['balance'],$total);
                    if($credit){
                        $transaction_debit = array(
                            "transaction_date"=>date("Y-m-d H:i:s"),
                            "transaction_type"=>"MONEY_TRANSFER",
                            "transaction_number"=>$reference_code,
                            "debit_amount"=>$amount,
                            "fee_amount"=>$fee,
                            "total_amount"=>$total,
                            "fk_customer_id"=>$source_info['customer_id'],
                            "related_fk_customer_id"=>$destination_info['customer_id'],
                            "transaction_description"=>"Envio de Transferencia a ".$destination_info['fname']." ".$destination_info['lname'],
                            "running_balance"=>($source_info['balance']-$total)
                        );
                        #"transaction_description"=>"Send Fund Transfer to ".$destination_info['fname']." ".$destination_info['lname'],
                        $this->db->insert('tbl_customer_transaction', $transaction_debit);
                        $transaction_credit = array(
                            "transaction_date"=>date("Y-m-d H:i:s"),
                            "transaction_type"=>"MONEY_TRANSFER",
                            "transaction_number"=>$reference_code,
                            "credit_amount"=>$amount,
                            "total_amount"=>$total,
                            "fk_customer_id"=>$destination_info['customer_id'],
                            "related_fk_customer_id"=>$source_info['customer_id'],
                            "transaction_description"=>"Recibio Transferencia de ".$source_info['fname']." ".$source_info['lname'],
                            "running_balance"=>($destination_info['balance']+$total)
                        );
                        #"transaction_description"=>"Receive Fund Transfer from ".$source_info['fname']." ".$source_info['lname'],
                        $this->db->insert('tbl_customer_transaction', $transaction_credit);

                        $data = array(
                            "amount_sent"=>$total,
                            "transaction_id"=>$reference_code,
                            "date"=>date("F d, Y h:ia"),
                            "send_money_type"=>($transaction_type=="1"?"Send to akisi Account":"Send to Red Akisi"),
                            "receipient"=>$source_info['fname']." ".$source_info['lname'],
                            "mobile"=>$source_info['mobile']
                        );
                        #$message_d = "Your Akisi wallet have been credited ".number_format($amount,2)." from ".$source_info['fname']." ".$source_info['lname'].". Reference No.: ".$reference_code.".";

                        $message_d = "Tu billetera Akisi ha sido acreditada con Q".number_format($amount,2)." por ".$source_info['fname']." ".$source_info['lname'].". No. de Referencia: ".$reference_code.".";

                        #$message_s = "You transferred ".number_format($amount,2)." to ".$destination_info['fname']." ".$destination_info['lname'].". Reference No.: ".$reference_code.".";

                        $message_s = "Transferencia exitosa de Q".number_format($amount,2)." a ".$destination_info['fname']." ".$destination_info['lname'].". No. de Referencia: ".$reference_code.".";

                        send_sms($source_info['mobile'],$message_s);
                        send_sms($destination_info['mobile'],$message_d);
                        $this->response($data, REST_Controller::HTTP_OK);
                    }else{
                        //balik yung dinebit sa customer
                        $this->settlement_model->credit_customer_account($source_info['customer_id'],$source_info['balance'],$total);
                        $this->response([
                            'status' => FALSE,
                            'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                            'response_code' => REST_Controller::HTTP_BAD_REQUEST
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }
        }else{ // wallet to store
            if($source_info['balance']<$total){
                $this->response([
                    'status' => FALSE,
                    'message' => 'insufficient balance',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }else{
                if(empty($destination_info)){ // if non wallet member
                    $destination_info = $this->customer_model->get_beneficiary_by_id($destination);
                }else{
                    $destination_info = $this->customer_model->get_beneficiary_by_reference($destination);
                }
                $cashout_code = rand ( 1000000000 , 9999999999 );
                $n = $cashout_code;
                
                $cashout_codes = substr($n, 0, 3)."-".substr($n,3,3)."-".substr($n,6,4);
                $transaction_number = rand ( 1000000000000 , 9999999999999 );
                $debit = $this->settlement_model->debit_customer_account($source_info['customer_id'],$source_info['balance'],$total);
                if($debit){
                    $transaction_debit = array(
                        "transaction_date"=>date("Y-m-d H:i:s"),
                        "transaction_type"=>"MONEY_TRANSFER",
                        "transaction_number"=>$transaction_number,
                        "debit_amount"=>$amount,
                        "fee_amount"=>$fee,
                        "total_amount"=>$total,
                        "fk_customer_id"=>$source_info['customer_id'],
                        "fk_beneficiary_id"=>$destination_info['beneficiary_id'],
                        "transaction_description"=>"Envio de  dinero a ".$destination_info['fname']." ".$destination_info['lname'],
                        "running_balance"=>($source_info['balance']-$total)
                    );
                    #"transaction_description"=>"Send Money to ".$destination_info['fname']." ".$destination_info['lname'],
                    $this->db->insert('tbl_customer_transaction', $transaction_debit);
                    $fk_customer_transaction_id = $this->db->insert_id();
                    $transaction_credit = array(
                        "transaction_date"=>date("Y-m-d H:i:s"),
                        "cashout_code"=>$cashout_code,
                        "amount"=>$amount,
                        "total_amount"=>$amount,
                        "fk_beneficiary_id"=>$destination_info['beneficiary_id'],
                        "related_fk_customer_id"=>$source_info['customer_id'],
                        "customer_transaction_id"=>$fk_customer_transaction_id,
                        "transaction_description"=>"Receive Money from ".$source_info['fname']." ".$source_info['lname'],
                    );
                    $this->db->insert('tbl_cashout_transaction', $transaction_credit);

                    $data = array(
                        "amount_sent"=>$total,
                        "transaction_id"=>$transaction_number,
                        "date"=>date("F d, Y h:ia"),
                        "send_money_type"=>($transaction_type=="1"?"Send to akisi Account":"Send to Red Akisi"),
                        "sender"=>$source_info['fname']." ".$source_info['lname'],
                        "receiver"=>$destination_info['fname']." ".$destination_info['lname'],
                        "receiver_mobile"=>$destination_info['mobile']
                    );
                    #$message_d = $source_info['fname']." ".$source_info['lname']." sent you ".number_format($amount,2)." with Cashout Code ".$cashout_codes.". Pickup money in any Red Akisi Store.";

                    $message_d = $source_info['fname']." ".$source_info['lname']." te transfirio Q".number_format($amount,2).".Codigo de retiro: ".$cashout_codes.". Recoge tu dinero en cualquiera de las.";

                    #$message_s = "You send ".number_format($amount,2)." to ".$destination_info['fname']." ".$destination_info['lname'].". Reference No.: ".$transaction_number.".";
                    $message_s = "Enviaste Q".number_format($amount,2)." a ".$destination_info['fname']." ".$destination_info['lname'].". No. de Referencia: ".$transaction_number.".";

                    send_sms($source_info['mobile'],$message_s);
                    send_sms($destination_info['mobile'],$message_d);
                    $this->response($data, REST_Controller::HTTP_OK);
                }else{
                    $this->settlement_model->credit_customer_account($source_info['customer_id'],$source_info['balance'],$total);
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }
        }
    }

    public function request_money_post(){
        $source = $this->post('R1'); //pinagrequestan
        $destination = $this->post('R2'); // nagrequest
        $amount = $this->post('R3');
        $fee = 0;//$this->post('R4');
        $note = $this->post('R5');
        $total = $amount + $fee;

        $source_info = $this->customer_model->get_customer($source);
        $destination_info = $this->customer_model->get_customer($destination);

        if(empty($source_info) || empty($destination_info)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid customer reference.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $request_money['customer_source'] = $source;
        $request_money['customer_destination'] = $destination;
        $request_money['amount'] = $amount;
        $request_money['fee'] = $fee;
        $request_money['total_amount'] = $fee+$amount;
        $request_money['note'] = $note;
        $this->db->insert('tbl_request_money', $request_money);


        $sms_message = "El numero ".$destination_info['mobile']." ha solicitado la cantidad de Q".number_format($total ,2).", por medio del servicio Akisi, ingresa a tu aplicacion de billetera para completar su solicitud";
        send_sms($source_info['mobile'],$sms_message);
        #send_sms("639954842680",$sms_message);
        $status_message = "Tu solicitaste Q".number_format($total ,2)." al numero ".$source_info['mobile'].".";
        $this->response([
            'status' => TRUE,
            'message' => $status_message,
            'response_code' => REST_Controller::HTTP_OK
        ], REST_Controller::HTTP_OK);
        
    }

    public function get_request_money_list_get(){
        $customer_reference = $this->get('customer_reference');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $query = $this->db->query("SELECT * FROM tbl_request_money WHERE (customer_source='{$customer_reference}' OR customer_destination='{$customer_reference}') and request_status='PENDING' ")->result_array();

        if(!empty($query)){
            $request_array = array();

            foreach($query as $row){
                $requestee = $this->customer_model->get_customer($row['customer_source']);
                $requestor = $this->customer_model->get_customer($row['customer_destination']);
                $request_array[] = array(
                                    "request_id"=>$row['request_id'],
                                    "requestor_name"=>$requestor['fname']." ".$requestor['lname'],
                                    "requestee_name"=>$requestee['fname']." ".$requestee['lname'],
                                    "amount"=>$row['amount'],
                                    "fee"=>$row['fee'],
                                    "note"=>$row['note'],
                                    "total_amount"=>$row['total_amount'],
                                    "request_status"=>$row['request_status'],
                                    "date_requested"=>date("F d, Y h:ia",strtotime($row['date_request'])),
                                );
            }
            $this->response($request_array, REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'no request found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function get_request_money_history_get(){
        $customer_reference = $this->get('customer_reference');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $query = $this->db->query("SELECT * FROM tbl_request_money WHERE (customer_source='{$customer_reference}' OR customer_destination='{$customer_reference}') and request_status<>'PENDING' ")->result_array();

        if(!empty($query)){
            $request_array = array();

            foreach($query as $row){
                $requestee = $this->customer_model->get_customer($row['customer_source']);
                $requestor = $this->customer_model->get_customer($row['customer_destination']);
                $request_array[] = array(
                                    "request_id"=>$row['request_id'],
                                    "requestor_name"=>$requestor['fname']." ".$requestor['lname'],
                                    "requestee_name"=>$requestee['fname']." ".$requestee['lname'],
                                    "amount"=>$row['amount'],
                                    "fee"=>$row['fee'],
                                    "total_amount"=>$row['total_amount'],
                                    "request_status"=>$row['request_status'],
                                    "note"=>$row['note'],
                                    "date_requested"=>date("F d, Y h:ia",strtotime($row['date_request'])),
                                    "date_process"=>date("F d, Y h:ia",strtotime($row['date_process'])),
                                );
            }
            $this->response($request_array, REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'no request found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function get_request_money_details_get(){
        $request_id = $this->get('request_id');

        $row = $this->db->get_where('tbl_request_money', array('request_id' => $request_id))->row_array();

        if(!empty($row)){
            $request_array = array();

            
            $requestee = $this->customer_model->get_customer($row['customer_source']);
            $requestor = $this->customer_model->get_customer($row['customer_destination']);
            $request_array[] = array(
                                "request_id"=>$row['request_id'],
                                "requestor_id"=>$row['customer_destination'],
                                "requestor_name"=>$requestor['fname']." ".$requestor['lname'],
                                "requestee_name"=>$row['customer_source'],
                                "requestee_name"=>$requestee['fname']." ".$requestee['lname'],
                                "amount"=>$row['amount'],
                                "fee"=>$row['fee'],
                                "total_amount"=>$row['total_amount'],
                                "request_status"=>$row['request_status'],
                                "note"=>$row['note'],
                                "date_requested"=>date("F d, Y h:ia",strtotime($row['date_request'])),
                            );
            
            $this->response($request_array, REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'no request found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function process_request_money_post(){
        $request_id = $this->post('R1'); //pinagrequestan
        $status = $this->post('R2'); // 1 = APPROVED, 2 = DENIED, 3 = CANCELLED
        $request =  $this->db->get_where('tbl_request_money', array('request_id' => $request_id,'request_status'=> 'PENDING'))->row_array();

        $request_status = array(1=>"APPROVED",2=>'DENIED',3=>'CANCELLED');

        if(empty($request)){
            $this->response([
                'status' => FALSE,
                'message' => 'no request found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if(!empty($request_id) && !empty($status)){
            if($status=="1"){ // approved
                $source = $request['customer_source'];
                $destination = $request['customer_destination'];
                $amount = $request['amount'];
                $fee = $request['fee'];
                $total = $request['total_amount'];

                $source_info = $this->customer_model->get_customer($source);
                $destination_info = $this->customer_model->get_customer($destination);

                if($source_info['balance']<$total){
                    $this->response([
                        'status' => FALSE,
                        'message' => 'insufficient balance',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }else{
                    $reference_code = rand ( 1000000000000 , 9999999999999 );
                    $debit = $this->settlement_model->debit_customer_account($source_info['customer_id'],$source_info['balance'],$total);
                    if($debit){
                        $credit = $this->settlement_model->credit_customer_account($destination_info['customer_id'],$destination_info['balance'],$total);
                        if($credit){
                            $transaction_debit = array(
                                "transaction_date"=>date("Y-m-d H:i:s"),
                                "transaction_type"=>"REQUEST_MONEY",
                                "transaction_number"=>$reference_code,
                                "debit_amount"=>$amount,
                                "fee_amount"=>$fee,
                                "total_amount"=>$total,
                                "fk_customer_id"=>$source_info['customer_id'],
                                "related_fk_customer_id"=>$destination_info['customer_id'],
                                "transaction_description"=>"Envio de Transferencia a ".$destination_info['fname']." ".$destination_info['lname'],
                                "running_balance"=>($source_info['balance']-$total)
                            );
                            $this->db->insert('tbl_customer_transaction', $transaction_debit);
                            $transaction_credit = array(
                                "transaction_date"=>date("Y-m-d H:i:s"),
                                "transaction_type"=>"REQUEST_MONEY",
                                "transaction_number"=>$reference_code,
                                "credit_amount"=>$amount,
                                "total_amount"=>$total,
                                "fk_customer_id"=>$destination_info['customer_id'],
                                "related_fk_customer_id"=>$source_info['customer_id'],
                                "transaction_description"=>"Recibio Transferencia de ".$source_info['fname']." ".$source_info['lname'],
                                "running_balance"=>($destination_info['balance']+$total)
                            );
                            $this->db->insert('tbl_customer_transaction', $transaction_credit);

                            $data = array(
                                "amount_sent"=>$total,
                                "transaction_id"=>$reference_code,
                                "date"=>date("F d, Y h:ia"),
                                "requestee"=>$source_info['fname']." ".$source_info['lname'],
                                "requestee_mobile"=>$source_info['mobile'],
                                "requestor"=>$destination_info['fname']." ".$destination_info['lname'],
                                "requestor_mobile"=>$destination_info['mobile']
                            );

                            $message_d = "Tu billetera Akisi ha sido acreditada con Q".number_format($amount,2)." por ".$source_info['fname']." ".$source_info['lname'].". No. de Referencia: ".$reference_code.".";


                            $message_s = "Transferencia exitosa de Q".number_format($amount,2)." a ".$destination_info['fname']." ".$destination_info['lname'].". No. de Referencia: ".$reference_code.".";

                            send_sms($source_info['mobile'],$message_s);
                            send_sms($destination_info['mobile'],$message_d);

                            $rData = array();
                            $rData['request_status'] = $request_status[$status];
                            $rData['date_process'] = date("Y-m-d H:i:s");
                            $update = $this->db->update('tbl_request_money', $rData, array('request_id'=>$request_id));

                            $this->response($data, REST_Controller::HTTP_OK);
                        }else{
                            //balik yung dinebit sa customer
                            $this->settlement_model->credit_customer_account($source_info['customer_id'],$source_info['balance'],$total);
                            $this->response([
                                'status' => FALSE,
                                'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                                'response_code' => REST_Controller::HTTP_BAD_REQUEST
                            ], REST_Controller::HTTP_BAD_REQUEST);
                        }
                    }else{
                        $this->response([
                            'status' => FALSE,
                            'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                            'response_code' => REST_Controller::HTTP_BAD_REQUEST
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                }

                
            }else if($status=="2" || $status=="3"){
                $rData = array();
                $rData['request_status'] = $request_status[$status];
                $rData['date_process'] = date("Y-m-d H:i:s");
                $update = $this->db->update('tbl_request_money', $rData, array('request_id'=>$request_id));

                $this->response([
                    'status' => TRUE,
                    'message' => 'TRANSACTION '.$request_status[$status].'.',
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Invalid Status',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }


        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Request ID and status are required',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    

    public function get_customer_transaction_get(){
        $customer_reference = $this->get('customer_reference');
        $transaction_id = $this->get('transaction_id');
        $limit = $this->get('limit');
        $sort = $this->get('sort');
        $transaction_type = $this->get('transaction_type');
        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $transactions = $this->customer_model->get_customer_transaction($customer['customer_id'],$transaction_id,$limit,$sort,$transaction_type);
        if(!empty($transactions)){
            $this->response($transactions, REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'no transaction found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        
    }

    //temporary update of language
    public function update_customer_language_post(){
        $customer_reference = $this->post('R1');
        $language = $this->post('R2');
        $update = $this->db->update('tbl_customer', array('language'=>$language), array('customer_reference'=>$customer_reference));
        if($update){
            $this->response([
                'status' => TRUE,
                'message' => 'Language successfully updated.',
                'response_code' => REST_Controller::HTTP_OK
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }


    }

    //temporary update of firebase
    public function update_customer_firebase_post(){
        $customer_reference = $this->post('R1');
        $firebase = $this->post('R2');
        $this->db->update('tbl_customer', array('firebase_refid'=>""), array('firebase_refid'=>$firebase));
        $update = $this->db->update('tbl_customer', array('firebase_refid'=>$firebase), array('customer_reference'=>$customer_reference));
        if($update){
            $this->response([
                'status' => TRUE,
                'message' => 'Firebase reference id successfully updated.',
                'response_code' => REST_Controller::HTTP_OK
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }


    }

    public function inquire_bill_post(){
        $pronet_customer_id = $this->post('R1');
        $biller_code          = $this->post('R2');
        $account_number     = $this->post('R3');
        $account_type       = $this->post('R4')?$this->post('R4'):"0";
        $denomination_id    = $this->post('R5');
        $payment_method     = $this->post('R6')?$this->post('R6'):null;
        $biller_id     = $this->post('R7');
        $cf1     = $this->post('R8')?$this->post('R8'):"";
        $cf2     = $this->post('R9')?$this->post('R9'):"";
        $inquire = $this->pronet_model->inquire_bill($pronet_customer_id, $biller_code, $account_number, $account_type, $denomination_id, $payment_method,$biller_id,$cf1,$cf2);
        if($inquire){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function get_billers_get(){
        $biller_id = $this->get('biller_id');
        if (!$this->pronet_model->get_billers($biller_id)) {
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        } else {
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }
    }

    public function get_customer_biller_get(){
        $biller_id = $this->get('biller_id');
        $customer_reference = $this->get('customer_reference');
        $category_id = $this->get('category_id');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $billers = $this->customer_model->get_customer_biller($customer['customer_id'],$biller_id,"",$category_id);
        if ($billers) {
            $this->response($billers, REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'No customer biller(s) were found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function add_customer_biller_post(){
        $customer_reference       = $this->post('R1');
        $cData['biller_code']       = $this->post('R2');
        $cData['account_number']  = $this->post('R3');
        $cData['account_type']    = $this->post('R4')?$this->post('R4'):0;
        $cData['biller_name']     = $this->post('R5');
        $cData['biller_nickname'] = $this->post('R6');
        $cData['logo_url']        = $this->post('R7');
        $cData['logo']            = $this->post('R8');
        $cData['reminder']        = $this->post('R9');
        $cData['day_of_month']    = $this->post('R10');
        $cData['time']            = $this->post('R11');
        $cData['category_id']     = $this->post('R12');
        $cData['biller_id']       = $this->post('R13');
        $cData['cf1']             = $this->post('R14');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if($customer['pronet_customer_id']==""){
            $this->response([
                'status' => FALSE,
                'message' => 'You do not have pronet customer reference. Please complete your profile information to proceed. (eg. Date of Birth, NIT Number)',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if(!empty($cData['biller_id']) && !empty($cData['account_number']) ){
            $inquire = $this->pronet_model->inquire_bill($customer['pronet_customer_id'], $cData['biller_code'], $cData['account_number'], $cData['account_type'], "", "",$cData['biller_id'],$cData['cf1']);
            $response = $this->pronet_model->get_response();
            if($response['ResponseCode']!="0000"){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Invalid Biller Account Number',
                    'data' => $response['ResponseMessage'],
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }else{
                $cData['fk_customer_id']  = $customer['customer_id'];
                $checkexist = $this->customer_model->get_customer_biller($cData['fk_customer_id'],$cData['biller_id'],$cData['account_number']);

                if(!empty($checkexist)){
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Biller already exist',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }else{
                    $add_biller = $this->customer_model->add_customer_biller($cData);
                    if($add_biller){
                        $this->response([
                            'status' => TRUE,
                            'message' => 'Biller successfully added.',
                            'response_code' => REST_Controller::HTTP_OK
                        ], REST_Controller::HTTP_OK);
                    }else{
                        $this->response([
                            'status' => FALSE,
                            'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                            'response_code' => REST_Controller::HTTP_BAD_REQUEST
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                }
            }

        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete biller information to create.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function update_customer_biller_post(){
        $customer_reference       = $this->post('R1');
        $customer_biller_id       = $this->post('R2');
        $cData['biller_nickname'] = $this->post('R3');
        $cData['reminder']        = $this->post('R4');
        $cData['day_of_month']    = $this->post('R5');
        $cData['time']            = $this->post('R6');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if($customer['pronet_customer_id']==""){
            $this->response([
                'status' => FALSE,
                'message' => 'You do not have pronet customer reference. Please complete your profile information to proceed. (eg. Date of Birth, NIT Number)',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if(!empty($cData['biller_nickname']) && !empty($cData['day_of_month']) && !empty($cData['time']) ){
            $update = $this->db->update('tbl_customer_biller', $cData, array('customer_biller_id'=>$customer_biller_id));

            $this->response([
                'status' => TRUE,
                'message' => 'Biller successfully updated.',
                'response_code' => REST_Controller::HTTP_OK
            ], REST_Controller::HTTP_OK);
            
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete biller information to create.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }


    }

    public function delete_customer_biller_post(){
        $customer_reference       = $this->post('R1');
        $cData['customer_biller_id']       = $this->post('R2');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if($cData['customer_biller_id']==""){
            $this->response([
                'status' => FALSE,
                'message' => 'Customer biller id is required.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->db->where($cData);
        $delete = $this->db->delete('tbl_customer_biller');
        if($delete){
            $this->response([
                'status' => TRUE,
                'message' => 'customer biller successfully deleted.',
                'response_code' => REST_Controller::HTTP_OK
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function delete_customer_topup_post(){
        $customer_reference       = $this->post('R1');
        $cData['customer_biller_id']       = $this->post('R2');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if($cData['customer_biller_id']==""){
            $this->response([
                'status' => FALSE,
                'message' => 'Customer topup id is required.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $this->db->where($cData);
        $delete = $this->db->delete('tbl_customer_topup');
        if($delete){
            $this->response([
                'status' => TRUE,
                'message' => 'Contacto eliminado correctamente.',
                'response_code' => REST_Controller::HTTP_OK
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function add_pronet_customer_post(){
        $customer_reference  = $this->post('R1');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if($customer['pronet_customer_id']!=""){
            $this->response([
                'status' => FALSE,
                'message' => 'This customer has already pronet id.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $fullname = $customer['fname']." ".$customer['lname'];
        $dpi      = $customer['dpi'];
        $email    = $customer['email'];
        $telno    = $customer['telno'];
        $nit      = $customer['nit'];
        $dob      = $customer['bdate'];
        $dpi_front = $customer['dpi_url'];
        $dpi_back = $customer['reverse_url'];

        if (!($fullname != '' && $dpi != '' && $email != '' && $telno != '' && $dob != '' && $nit != '')) {
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information of customer. (eg. name, dpi, email, telno, date of birth, NIT number)',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{
            if (!$this->pronet_model->add_user($fullname, $dpi, $email, $telno, $dob,$nit,"E007",$dpi_front,$dpi_back)) {
                $response = $this->pronet_model->get_response();
                $this->response([
                    'status' => FALSE,
                    'message' => $response['ResponseMessage'],
                    'response_code' => $response['ResponseCode']
                ], REST_Controller::HTTP_BAD_REQUEST);
            } else {
                $response = $this->pronet_model->get_response();
                $data = $response['ResponseMessage'];
                $update = $this->db->update('tbl_customer', array("pronet_customer_id"=>$data['pronet_customer_id']), array('customer_reference'=>$customer_reference));
                $this->response([
                    'status' => TRUE,
                    'message' => $data['message'],
                    'pronet_customer_id' => $data['pronet_customer_id'],
                    'response_code' => $response['ResponseCode']
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function update_customer_information_post(){
        $customer_reference  = $this->post('R1');
        $uData['lname']   = $this->post('R2');
        $uData['slname']   = $this->post('R3');
        $uData['mname']   = $this->post('R4');
        $uData['fname']   = $this->post('R5');
        $cData['address'] = $this->post('R6');
        $cData['department']   = $this->post('R7');
        $cData['municipality']   = $this->post('R8');
        $uData['gender']   = $this->post('R9');
        $uData['bdate']   = $this->post('R10');
        $uData['bplace']   = $this->post('R11');
        $uData['bcountry']   = $this->post('R12');
        

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if (time() < strtotime('+18 years', strtotime($uData['bdate']))) {
            $this->response([
                'status' => FALSE,
                'message' => 'Customer is under 18 years of age',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if ($uData['lname'] == '' || $uData['fname'] == '' || $cData['address'] == '' || $uData['bdate'] == '' || $this->post('R13') == ''  || $this->post('R14') == '') {
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information of customer. (eg. last name, first name, address, date of birth, NIT, Tel No.)',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{

            /** start of NIT checking */

            $fullname = $customer['fname']." ".$customer['lname'];
            $dpi      = $customer['dpi'];
            $email    = $customer['email'];
            $telno    = $this->post('R14');
            $nit      = $this->post('R13');
            $dob      = date('Y-m-d', strtotime($uData['bdate']));
            $dpi_front = $customer['dpi_url'];
            $dpi_back = $customer['reverse_url'];

            if(empty($customer['pronet_customer_id'])){
                $checktelno_ifexist = $this->db->query("SELECT * FROM view_customer_info WHERE telno='{$this->post('R14')}' AND customer_id!='{$customer['customer_id']}' ")->row_array();
                if(!empty($checktelno_ifexist)){
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Tel No. already exist.',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }


                if (!$this->pronet_model->add_user($fullname, $dpi, $email, $telno, $dob,$nit,"E007",$dpi_front,$dpi_back)) {
                    $response = $this->pronet_model->get_response();
                    $this->response([
                        'status' => FALSE,
                        'message' => "Cannot update customer profile. Error:". $response['ResponseMessage'],
                        'response_code' => $response['ResponseCode']
                    ], REST_Controller::HTTP_BAD_REQUEST);
                } else {
                    $response = $this->pronet_model->get_response();
                    $data = $response['ResponseMessage'];
                    $pData['pronet_customer_id'] = $data['pronet_customer_id'];
                    $update = $this->db->update('tbl_customer', $pData, array('customer_id'=>$customer['customer_id']));
                    if($update){
                        $cData['nit']   = $this->post('R13');
                        $cData['telno']   = $this->post('R14');
                        $this->db->update('tblUserInformation', $uData, array('userid'=>$customer['userid']));
                        $this->db->update('tbl_customer', $cData, array('customer_id'=>$customer['customer_id']));

                        $this->response([
                            'status' => TRUE,
                            'message' => "Customer information successfully updated.",
                            'response_code' => REST_Controller::HTTP_OK
                        ], REST_Controller::HTTP_OK);

                    }else{
                        $this->response([
                            'status' => FALSE,
                            'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                            'response_code' => REST_Controller::HTTP_BAD_REQUEST
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                }
            }else{
                $checktelno_ifexist = $this->db->query("SELECT * FROM view_customer_info WHERE telno='{$this->post('R14')}' AND customer_id!='{$customer['customer_id']}' ")->row_array();
                if(!empty($checktelno_ifexist)){
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Tel No. already exist.',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }

                if (!$this->pronet_model->update_user($customer['pronet_customer_id'],$fullname, $email, $telno, $dob,$nit,$dpi_front,$dpi_back)) {
                    $response = $this->pronet_model->get_response();
                    $this->response([
                        'status' => FALSE,
                        'message' => "Cannot update customer profile. Error:". $response['ResponseMessage'],
                        'response_code' => $response['ResponseCode']
                    ], REST_Controller::HTTP_BAD_REQUEST);
                } else {
                    $response = $this->pronet_model->get_response();
                    $data = $response['ResponseMessage'];
                    
                    $cData['nit']   = $this->post('R13');
                    $cData['telno']   = $this->post('R14');
                    $this->db->update('tblUserInformation', $uData, array('userid'=>$customer['userid']));
                    $this->db->update('tbl_customer', $cData, array('customer_id'=>$customer['customer_id']));

                    $this->response([
                        'status' => TRUE,
                        'message' => "Customer information successfully updated.",
                        'response_code' => REST_Controller::HTTP_OK
                    ], REST_Controller::HTTP_OK);

                }
            }
            /** end of NIT checking */
            /*
            $cData['nit']   = $this->post('R13');
            $cData['telno']   = $this->post('R14');
            $this->db->update('tblUserInformation', $uData, array('userid'=>$customer['userid']));
            $this->db->update('tbl_customer', $cData, array('customer_id'=>$customer['customer_id']));

            $this->response([
                'status' => TRUE,
                'message' => "Customer information successfully updated.",
                'response_code' => REST_Controller::HTTP_OK
            ], REST_Controller::HTTP_OK);
            */
        }
    }

    public function update_customer_profile_post(){
        $customer_reference  = $this->post('R1');
        $cData['nit']   = $this->post('R2');
        $cData['telno'] = $this->post('R3');
        $uData['bdate']   = $this->post('R4');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        
        if (!($cData['nit'] != '' && $cData['telno'] != '' && $uData['bdate'] != '' )) {
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information of customer. (eg. telno, date of birth, NIT number)',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{
            if (!(is_numeric($cData['telno']) && strlen($cData['telno']) == 8)){
                $this->response([
                    'status' => FALSE,
                    'message' => 'telephone number must be an 8-digit number',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
                
            }

            if (time() < strtotime('+18 years', strtotime($uData['bdate']))) {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Customer is under 18 years of age',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
            if(!empty($customer['pronet_customer_id'])){
                $uData['bdate'] = date('Y-m-d', strtotime($uData['bdate']));
                $update = $this->db->update('tblUserInformation', $uData, array('userid'=>$customer['userid']));
                if($update){
                    $this->response([
                        'status' => TRUE,
                        'message' => "Customer profile successfully updated.",
                        'response_code' => REST_Controller::HTTP_OK
                    ], REST_Controller::HTTP_OK);
                }else{
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }
            }else{
                $checktelno_ifexist =  $this->db->get_where('view_customer_info', array('telno' => $cData['telno']))->row_array();
                if(!empty($checktelno_ifexist)){
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Tel No. already exist.',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }

                $fullname = $customer['fname']." ".$customer['lname'];
                $dpi      = $customer['dpi'];
                $email    = $customer['email'];
                $telno    = $this->post('R3');
                $nit      = $this->post('R2');
                $dob      = date('Y-m-d', strtotime($uData['bdate']));
                $dpi_front = $customer['dpi_url'];
                $dpi_back = $customer['reverse_url'];


                if (!$this->pronet_model->add_user($fullname, $dpi, $email, $telno, $dob,$nit,"E007",$dpi_front,$dpi_back)) {
                    $response = $this->pronet_model->get_response();
                    $this->response([
                        'status' => FALSE,
                        'message' => "Cannot update customer profile. Error:". $response['ResponseMessage'],
                        'response_code' => $response['ResponseCode']
                    ], REST_Controller::HTTP_BAD_REQUEST);
                } else {
                    $response = $this->pronet_model->get_response();
                    $data = $response['ResponseMessage'];
                    $cData['pronet_customer_id'] = $data['pronet_customer_id'];
                    $uData['bdate'] = date('Y-m-d', strtotime($uData['bdate']));
                    $this->db->update('tbl_customer', $cData, array('customer_reference'=>$customer_reference));
                    $update = $this->db->update('tblUserInformation', $uData, array('userid'=>$customer['userid']));
                    if($update){
                        $this->response([
                            'status' => TRUE,
                            'message' => "Customer profile successfully updated. ".$data['message'],
                            'pronet_customer_id' => $data['pronet_customer_id'],
                            'response_code' => $response['ResponseCode']
                        ], REST_Controller::HTTP_OK);
                    }else{
                        $this->response([
                            'status' => FALSE,
                            'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                            'response_code' => REST_Controller::HTTP_BAD_REQUEST
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                }

            }
        }

    }

    public function get_customer_topup_get(){
        $biller_id = $this->get('biller_id');
        $customer_reference = $this->get('customer_reference');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $billers = $this->customer_model->get_customer_topup($customer['customer_id'],$biller_id);
        if ($billers) {
            $this->response($billers, REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'No customer topup(s) were found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function add_customer_topup_post(){
        $customer_reference     = $this->post('R1');
        $cData['biller_code']     = $this->post('R2');
        $cData['mobile_number'] = $this->post('R3');
        $cData['denomination']  = $this->post('R4');
        $cData['name']          = $this->post('R5');
        $cData['logo_url']      = $this->post('R6');
        $cData['logo']          = $this->post('R7');
        $cData['biller_id']     = $this->post('R8');
        $cData['cf1']           = $this->post('R9');

        $customer = $this->customer_model->get_customer($customer_reference);
        #echo $this->db->last_query();
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        if($customer['pronet_customer_id']==""){
            $this->response([
                'status' => FALSE,
                'message' => 'You do not have pronet customer reference. Please complete your profile information to proceed. (eg. Date of Birth, NIT Number)',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if(!empty($cData['biller_id']) && !empty($cData['mobile_number']) && !empty($cData['denomination'])){
            $inquire = $this->pronet_model->inquire_bill($customer['pronet_customer_id'], $cData['biller_code'], $cData['mobile_number'], "", $cData['denomination'], "",$cData['biller_id'],$cData['cf1']);

            $response = $this->pronet_model->get_response();
            if($response['ResponseCode']!="0000"){
                $this->response([
                    'status' => FALSE,
                    'message' => $response['ResponseMessage'],
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }else{
                
                $cData['fk_customer_id']  = $customer['customer_id'];
                $checkexist = $this->customer_model->get_customer_topup($cData['fk_customer_id'],$cData['biller_id'],$cData['mobile_number']);

                if(!empty($checkexist)){
                    $this->response([
                        'status' => FALSE,
                        'message' => 'Mobile number already exist',
                        'response_code' => REST_Controller::HTTP_BAD_REQUEST
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }else{
                    $add_biller = $this->customer_model->add_customer_topup($cData);
                    if($add_biller){
                        $this->response([
                            'status' => TRUE,
                            'message' => 'Topup successfully added.',
                            'response_code' => REST_Controller::HTTP_OK
                        ], REST_Controller::HTTP_OK);
                    }else{
                        $this->response([
                            'status' => FALSE,
                            'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                            'response_code' => REST_Controller::HTTP_BAD_REQUEST
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                }
            }

        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete biller information to create.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function pay_customer_bill_post(){
        $customer_reference      = $this->post('R1');
        $cData['bill_reference'] = $this->post('R2');
        $cData['cardnumber']     = $this->post('R3');
        $cData['cvv']            = $this->post('R4');
        $cData['expiry_month']   = $this->post('R5');
        $cData['expiry_year']    = $this->post('R6');
        #$cData['total_amount']   = $this->post('R7');
        #$cData['bill_type']      = $this->post('R8');
        #$cData['biller_name']    = $this->post('R9');
        $cData['receipient']     = $this->post('R7');
        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $is_credit_card_payment = 0;
        if($cData['cardnumber']!="" && $cData['cvv']!="" && $cData['expiry_month']!= "" && $cData['expiry_year']!= ""){
            $is_credit_card_payment = 1;
        }

        $bill_info = $this->pronet_model->get_billpay_info($customer['pronet_customer_id'],$cData['bill_reference']);
        $bill_info_response = $this->pronet_model->get_response();

        if($bill_info_response['ResponseCode']!="0000"){
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid reference number',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $bill_message = $bill_info_response['ResponseMessage'];
        $cData['bill_type'] = $bill_message['biller_type']; //1 is billpay,2 is topup
        $cData['biller_name']    = $bill_message['biller_name'];
        $cData['total_amount']   = $bill_message['total_amount'];
        #$this->response($cData, REST_Controller::HTTP_OK);

        if($bill_message['method_of_payment']=="E007"){
            if($customer['balance']<$cData['total_amount']){
                $this->response([
                    'status' => FALSE,
                    'message' => 'insufficient balance',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
        $customer_balance = $customer['balance'];

        if (!($cData['bill_reference'] != '' && $customer['pronet_customer_id'] != '' )) {
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{
            #$debit = ;
            if($this->settlement_model->debit_customer_account($customer['customer_id'],$customer_balance,$cData['total_amount'])){
                $customer_balance = $customer_balance-$cData['total_amount'];
                $paybill = $this->pronet_model->pay_bill($customer['pronet_customer_id'], $cData['bill_reference'], $cData['cardnumber'], $cData['cvv'],$cData['expiry_month'],$cData['expiry_year']);
                $response = $this->pronet_model->get_response();
                if ($response['ResponseCode']=="1000") {
                    //balik yung dinebit sa customer
                    $this->settlement_model->credit_customer_account($customer['customer_id'],$customer_balance,$cData['total_amount']);
                    // not successfull
                    $this->response([
                        'status' => FALSE,
                        'message' => $response['ResponseMessage'],
                        'response_code' => $response['ResponseCode']
                    ], REST_Controller::HTTP_BAD_REQUEST);
                } else {
                    $data = $response['ResponseMessage'];
                    $reference_code = rand ( 1000000000000 , 9999999999999 );
                    // for billpay
                    if($cData['bill_type']=="1"){
                        $credit = $this->settlement_model->credit_settlement_account("2","BILLPAY",$data['fee_amount'],$data['balance_amount']);
                        if($credit['status']){
                            $fk_customer_transaction_id = NULL;
                            $via_cc = "";
                            // check here if credit card payment and put a condition
                            // if $data['method_of_payment'] "E004": "Tarjeta de Débito / Crédito", "E006": "Génesis Efectivo"
                            if($data['method_of_payment']=="E006" || $data['method_of_payment']=="E004"){
                            #if(!empty($cData['cardnumber']) && !empty($cData['cvv']) && !empty($cData['expiry_month']) && !empty($cData['expiry_year'])){
                                //rollback yung kinaltas sa customer
                                $this->settlement_model->credit_customer_account($customer['customer_id'],$customer_balance,$cData['total_amount']);
                                if($data['method_of_payment']=="E006"){
                                    $via_cc = "via Génesis Efectivo.";
                                }else if($data['method_of_payment']=="E004"){
                                    $via_cc = "via Credit Card.";
                                }
                                

                                //insert in tbl_customer_other_activity
                                $transaction_debit = array(
                                    "transaction_date"=>date("Y-m-d H:i:s"),
                                    "transaction_type"=>"BILLPAY",
                                    "transaction_number"=>$reference_code,
                                    "bill_reference"=>$cData['bill_reference'],
                                    "debit_amount"=>$data['balance_amount'],
                                    "fee_amount"=>$data['fee_amount'],
                                    "total_amount"=>$data['total_amount'],
                                    "fk_customer_id"=>$customer['customer_id'],
                                    "biller_code"=>$data['biller_code'],
                                    "transaction_description"=>"Pago de factura {$cData['biller_name']} ({$data['biller_code']}) ".$via_cc,
                                    "cardnumber"=>substr($cData['cardnumber'], 12,16),
                                    //"cvv"=>md5(md5($cData['cvv'])),
                                    "expiry_month"=>md5(md5($cData['expiry_month'])),
                                    "expiry_year"=>md5(md5($cData['expiry_year'])),
                                    "transaction_status"=>$data['status']
                                );
                                #"transaction_description"=>"Bill payment for biller {$cData['biller_name']} ({$data['biller_code']}) ".$via_cc,
                                $this->db->insert('tbl_customer_other_activity', $transaction_debit);
                                $fk_customer_transaction_id = $this->db->insert_id();
                            }else{
                                $transaction_debit = array(
                                    "transaction_date"=>date("Y-m-d H:i:s"),
                                    "transaction_type"=>"BILLPAY",
                                    "transaction_number"=>$reference_code,
                                    "bill_reference"=>$cData['bill_reference'],
                                    "debit_amount"=>$data['balance_amount'],
                                    "fee_amount"=>$data['fee_amount'],
                                    "total_amount"=>$data['total_amount'],
                                    "fk_customer_id"=>$customer['customer_id'],
                                    "related_biller_code"=>$data['biller_code'],
                                    "transaction_description"=>"Pago de factura {$cData['biller_name']} ({$data['biller_code']})",
                                    "running_balance"=>$customer_balance,
                                    "transaction_status"=>$data['status'],
                                );
                                #"transaction_description"=>"Bill payment for biller {$cData['biller_name']} ({$data['biller_code']})",
                                $this->db->insert('tbl_customer_transaction', $transaction_debit);
                                $fk_customer_transaction_id = $this->db->insert_id();
                            }

                            
                            $transaction_credit = array(
                                "transaction_date"=>date("Y-m-d H:i:s"),
                                "transaction_number"=>$reference_code,
                                "bill_reference"=>$cData['bill_reference'],
                                "credit_amount"=>$data['balance_amount'],
                                "fee_amount"=>$data['fee_amount'],
                                "total_amount"=>$data['total_amount'],
                                "biller_code"=>$data['biller_code'],
                                "transaction_status"=>$data['status'],
                                "related_fk_customer_id"=>$customer['customer_id'],
                                "customer_transaction_id"=>$fk_customer_transaction_id,
                                "transaction_description"=>"Payment received from ".$customer['fname']." ".$customer['lname']." for biller {$cData['biller_name']} ({$data['biller_code']}) ".$via_cc,
                                "running_balance"=>($credit['running_balance'])
                            );
                            $this->db->insert('tbl_billpay_transaction', $transaction_credit);
                            $data_return = array(
                                "amount_paid"=>$data['total_amount'],
                                "transaction_id"=>$reference_code,
                                "bill_reference"=>$cData['bill_reference'],
                                "date"=>date("F d, Y h:ia"),
                                "biller"=>$cData['biller_name'],
                                "account_number"=>$data['account_number'],
                                "account_type"=>$bill_message['account_type'],
                                "status"=>$data['status'],
                                "cf1"=>$bill_message['cf1'],
                                "cf2"=>$bill_message['cf2'],
                                "cf3"=>$bill_message['cf3'],
                                "cf4"=>$bill_message['cf4'],
                                "cf5"=>$bill_message['cf5']
                            );

                            if($data['status']=="2"){
                                #$message = "You successfully paid ".number_format($data['total_amount'],2)." to biller ".$data['biller_name']."."."Reference No.: ".$reference_code.".";

                                $message = "Pago realizado con exito de ".number_format($data['total_amount'],2)." a ".$data['biller_name']."."." No. De referencia ".$reference_code.".";
                                send_sms($customer['mobile'],$message);

                            }else{
                                $message = "Your payment of ".number_format($data['total_amount'],2)." to biller ".$data['biller_name']." has been accepted."."Reference No.: ".$reference_code.".";
                                send_sms($customer['mobile'],$message);
                            }

                            
                            $this->response([
                                'status' => TRUE,
                                'message' => $data['message'],
                                'data'=>$data_return,
                                'response_code' => REST_Controller::HTTP_OK
                            ], REST_Controller::HTTP_OK);
                        }else{
                            //balik yung dinebit sa customer
                            $this->settlement_model->credit_customer_account($customer['customer_id'],$customer_balance,$data['total_amount']);
                            $this->response([
                                'status' => FALSE,
                                'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                                'response_code' => REST_Controller::HTTP_BAD_REQUEST
                            ], REST_Controller::HTTP_BAD_REQUEST);
                        }
                    }else if($cData['bill_type']=="2"){ // for topup
                        $credit = $this->settlement_model->credit_settlement_account("3","TOPUP",$data['fee_amount'],$data['balance_amount']);
                        if($credit['status']){

                            $fk_customer_transaction_id = NULL;
                            $via_cc = "";
                            // check here if credit card payment and put a condition
                            // $data['method_of_payment'] "E004": "Tarjeta de Débito / Crédito", "E006": "Génesis Efectivo"
                            //if(!empty($cData['cardnumber']) && !empty($cData['cvv']) && !empty($cData['expiry_month']) && !empty($cData['expiry_year'])){
                            if($data['method_of_payment']=="E006" || $data['method_of_payment']=="E004"){
                                //rollback yung kinaltas sa customer
                                $this->settlement_model->credit_customer_account($customer['customer_id'],$customer_balance,$cData['total_amount']);
                                if($data['method_of_payment']=="E006"){
                                    $via_cc = "via Génesis Efectivo.";
                                }else if($data['method_of_payment']=="E004"){
                                    $via_cc = "via Credit Card.";
                                }

                                //insert in tbl_customer_other_activity
                                $transaction_debit = array(
                                    "transaction_date"=>date("Y-m-d H:i:s"),
                                    "transaction_type"=>"TOPUP",
                                    "transaction_number"=>$reference_code,
                                    "bill_reference"=>$cData['bill_reference'],
                                    "debit_amount"=>$data['balance_amount'],
                                    "fee_amount"=>$data['fee_amount'],
                                    "total_amount"=>$data['total_amount'],
                                    "fk_customer_id"=>$customer['customer_id'],
                                    "biller_code"=>$data['biller_code'],
                                    "transaction_description"=>"Bill payment for biller {$cData['biller_name']} ({$data['biller_code']}) ".$via_cc,
                                    "cardnumber"=>substr($cData['cardnumber'], 12,16),
                                    //"cvv"=>md5(md5($cData['cvv'])),
                                    "expiry_month"=>md5(md5($cData['expiry_month'])),
                                    "expiry_year"=>md5(md5($cData['expiry_year'])),
                                    "transaction_status"=>$data['status']
                                );
                                $this->db->insert('tbl_customer_other_activity', $transaction_debit);
                                $fk_customer_transaction_id = $this->db->insert_id();
                            }else{
                                $transaction_debit = array(
                                    "transaction_date"=>date("Y-m-d H:i:s"),
                                    "transaction_type"=>"TOPUP",
                                    "transaction_number"=>$reference_code,
                                    "bill_reference"=>$cData['bill_reference'],
                                    "debit_amount"=>$data['balance_amount'],
                                    "fee_amount"=>$data['fee_amount'],
                                    "total_amount"=>$data['total_amount'],
                                    "fk_customer_id"=>$customer['customer_id'],
                                    "related_biller_code"=>$data['biller_code'],
                                    "transaction_description"=>"Recarga al numero de celular: ".$data['account_number']." desde la billetera de:  ".$customer['fname']." ".$customer['lname'],
                                    "running_balance"=>$customer_balance,
                                    "transaction_status"=>$data['status']
                                );

                                #"transaction_description"=>"Topup to {$cData['receipient']}, mobile number ".$data['account_number']." process by customer ".$customer['fname']." ".$customer['lname']." for biller ({$data['biller_code']})",
                                $this->db->insert('tbl_customer_transaction', $transaction_debit);
                                $fk_customer_transaction_id = $this->db->insert_id();
                            }

                            $transaction_credit = array(
                                "transaction_date"=>date("Y-m-d H:i:s"),
                                "transaction_number"=>$reference_code,
                                "bill_reference"=>$cData['bill_reference'],
                                "credit_amount"=>$data['balance_amount'],
                                "fee_amount"=>$data['fee_amount'],
                                "total_amount"=>$data['total_amount'],
                                "transaction_status"=>$data['status'],
                                "biller_code"=>$data['biller_code'],
                                "related_fk_customer_id"=>$customer['customer_id'],
                                "customer_transaction_id"=>$fk_customer_transaction_id,
                                "transaction_description"=>"Topup received from {$cData['receipient']} , mobile number ".$data['account_number']." process by customer ".$customer['fname']." ".$customer['lname']." for biller ({$data['biller_code']}) ".$via_cc,
                                "running_balance"=>($credit['running_balance'])
                            );
                            $this->db->insert('tbl_topup_transaction', $transaction_credit);

                            $data_return = array(
                                "amount_paid"=>$data['balance_amount'],
                                "transaction_id"=>$reference_code,
                                "bill_reference"=>$cData['bill_reference'],
                                "NIT"=>$customer['nit'],
                                "date"=>date("F d, Y h:ia"),
                                "receipient"=>$cData['receipient'],
                                "biller"=>$cData['biller_name'],
                                "mobile"=>$data['account_number'],
                                "fee"=>$data['fee_amount'],
                                "total"=>$data['total_amount'],
                                "account_type"=>$bill_message['account_type'],
                                "status"=>$data['status'],
                                "cf1"=>$bill_message['cf1'],
                                "cf2"=>$bill_message['cf2'],
                                "cf3"=>$bill_message['cf3'],
                                "cf4"=>$bill_message['cf4'],
                                "cf5"=>$bill_message['cf5']
                            );
                            if($data['status']=="2"){
                                #$message = "Akisi Message Center: You successfully Topup ".number_format($data['balance_amount'],2)." to {$cData['receipient']}, mobile number ".$data['account_number']."."."Reference No.: ".$reference_code.".";
                                $message = "Akisi: Recarga exitosa de Q".number_format($data['balance_amount'],2)." al ".$cData['receipient']."."."No. de Referencia: ".$reference_code.".";
                                send_sms($customer['mobile'],$message);
                            }
                            
                            $this->response([
                                'status' => TRUE,
                                'message' => $data['message'],
                                'data'=>$data_return,
                                'response_code' => REST_Controller::HTTP_OK
                            ], REST_Controller::HTTP_OK);
                        }else{
                            //balik yung dinebit sa customer
                            $this->settlement_model->credit_customer_account($customer['customer_id'],$customer_balance,$data['total_amount']);
                            $this->response([
                                'status' => FALSE,
                                'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                                'response_code' => REST_Controller::HTTP_BAD_REQUEST
                            ], REST_Controller::HTTP_BAD_REQUEST);
                        }
                    }
                }
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }
    }

    public function send_sms_akisi_post(){
        $mobile = $this->post('R1');
        $message = $this->post('R2');
        $return = send_sms($mobile,$message);
        if($return['success']){
            $this->response($return, REST_Controller::HTTP_OK);
        }else{
            $this->response($return, REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function update_app_version_post(){
        $android = $this->post('R1');
        $ios = $this->post('R2');

        $update = $this->db->update('tbl_app_version', array('android'=>$android,'ios'=>$ios));
        //if successfully store 
        $this->response([
            'status' => TRUE,
            'message' => 'Successfully updated app version',
            'response_code' => REST_Controller::HTTP_OK
        ], REST_Controller::HTTP_OK);
    }

    public function get_app_version_get(){ 
        $version = $this->db->get('tbl_app_version')->row_array();

        $this->response($version, REST_Controller::HTTP_OK);
    }

    /** start of red chapina api's */

    public function inquire_redchapina_post(){
        $pronet_customer_id = $this->post('R1');
        $mtcn          = $this->post('R2');
        $inquire = $this->pronet_model->inquire_redchapina($pronet_customer_id, $mtcn);
        if($inquire){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function pay_redchapina_post(){
        $pronet_customer_id = $this->post('R1');
        $remittance_number = $this->post('R2');
        $signature = $this->post('R3');
        $uParam['benNumeroIdentificacion'] = $this->post('R4');
        $uParam['benPais'] = $this->post('R5');
        $uParam['benPrimerApellido'] = $this->post('R6');
        $uParam['benPrimerNombre'] = $this->post('R7');
        $uParam['benTipoIdentificacion'] = $this->post('R8');
        $uParam['comentario'] = $this->post('R9')?$this->post('R9'):"NA";
        $uParam['departamento'] = $this->post('R10');
        $uParam['municipio'] = $this->post('R11');
        $uParam['direccion'] = $this->post('R12');
        $uParam['ocupacion'] = $this->post('R13');
        $uParam['relacionRemitente'] = $this->post('R14');
        $uParam['motivoRecepcion'] = $this->post('R15');
        $uParam['rangoIngresos'] = $this->post('R16');
        $uParam['rangoEgresos'] = $this->post('R17');
        $uParam['genero'] = $this->post('R18');
        $uParam['lugarNacimiento'] = $this->post('R19');
        $uParam['fechaNacimiento'] = $this->post('R20');
        $uParam['actividadEconomica'] = $this->post('R21');
        $uParam['monedaPago'] = $this->post('R22');
        $uParam['benTelefono'] = $this->post('R23');
        $customer_reference = $this->post('R24');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $params = array(
            'benNumeroIdentificacion' => '',//DPI
            'benPais' => '',//country of birth
            'benPrimerApellido' => 'Perez',//last name
            'benPrimerNombre' => 'Gloria',//first name
            'benTipoIdentificacion' => 'DPI',//ID type
            'comentario' => 'Compra libros',//comment
            'departamento' => 'Guatemala',//Department
            'municipio' => 'San Raymundo',//Municipality
            'direccion' => 'Frente mercado SAN RAYUMUNDO', //Address
            'ocupacion' => null,//occupation
            'relacionRemitente' => '', // relation to bene
            'motivoRecepcion' => '', // rreason of remittance
            'rangoIngresos' => '',//income
            'rangoEgresos' => '',//expenses
            'genero' => '',//expenses
            'lugarNacimiento' => '',//place of birth
            'fechaNacimiento' => '',//date of birth
            'actividadEconomica' => '',//economic activity
            'monedaPago' => '',//currency
            'benTelefono' => '66666666',//tel no
        );
        #$remittance_number = '837979807921'; // sample remittance number
        //$sig = base64_encode(file_get_contents('http://localhost/pronet/1110100124956201901223.jpg'));
        $pay = $this->pronet_model->pay_redchapina($pronet_customer_id,$remittance_number,$uParam,$signature);
        $response = $this->pronet_model->get_response();
        if ($response['ResponseCode']=="1000") {
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        } else {
            $data = $response['ResponseMessage'];
            if($this->settlement_model->credit_customer_account($customer['customer_id'],$customer['balance'],$data['amount'])){
                $reference_code = rand ( 1000000000000 , 9999999999999 );
                $transaction_credit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_type"=>"REMITTANCE",
                    "transaction_number"=>$reference_code,
                    "remittance_number"=>$remittance_number,
                    "credit_amount"=>$data['amount'],
                    "total_amount"=>$data['amount'],
                    "fk_customer_id"=>$customer['customer_id'],
                    "transaction_description"=>"Remesa cargada a billetera. No. de referencia: ".$remittance_number,
                    "running_balance"=>($customer['balance']+$data['amount'])
                );  
                #"transaction_description"=>"Remittance to ".$customer['fname']." ".$customer['lname'].", Remittance number: ".$remittance_number,
                $this->db->insert('tbl_customer_transaction', $transaction_credit);
                $fk_customer_transaction_id = $this->db->insert_id();
                $debit = $this->settlement_model->debit_prefund_account("5","REMITTANCE",0.00,$data['amount']);
                $transaction_debit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_number"=>$reference_code,
                    "remittance_number"=>$remittance_number,
                    "debit_amount"=>$data['amount'],
                    "total_amount"=>$data['amount'],
                    "related_fk_customer_id"=>$customer['customer_id'],
                    "customer_transaction_id"=>$fk_customer_transaction_id,
                    "transaction_description"=>"Remittance received by ".$customer['fname']." ".$customer['lname'].", Remittance number: ".$remittance_number,
                    "running_balance"=>($debit['running_balance'])
                );
                $this->db->insert('tbl_remittance_transaction', $transaction_debit);
                $data_return = array(
                    "amount"=>$data['amount'],
                    "transaction_id"=>$reference_code,
                    "date"=>date("F d, Y h:ia"),
                    "wallet_balance"=>($customer['balance']+$data['amount']),
                );

                #$message = "You successfully remit ".number_format($data['amount'],2).". Reference No.: ".$reference_code.".";
                $message = "Tu Remesa ha sido cargada exitosamente por ".number_format($data['amount'],2)." No. de referencia: ".$reference_code.".";

                send_sms($customer['mobile'],$message);
                $this->response([
                    'status' => TRUE,
                    'message' => $data['message'],
                    'data'=>$data_return,
                    'response_code' => REST_Controller::HTTP_OK
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function get_transaction_receipt_get(){
        $customer_reference = $this->get('customer_reference');
        $bill_reference = $this->get('reference');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $pay = $this->pronet_model->get_transaction_receipt($bill_reference);
        $response = $this->pronet_model->get_response();
        if ($response['ResponseCode']=="1000") {
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }


    }

    //(use the "name" value to send to API)
    public function get_lookup_relacion_remitente_get(){
        $check = $this->pronet_model->get_lookup_relacion_remitente();
        if($check){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    //(use the "code" value for benPais and lugarNacimiento dropdown, and the "name" for display)
    public function get_lookup_countries_get(){
        $check = $this->pronet_model->get_lookup_countries();
        if($check){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    //(use the "name" value to send to API)
    public function get_lookup_actividad_economica_get(){
        $check = $this->pronet_model->get_lookup_actividad_economica();
        if($check){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    //(use the "name" value to send to API)
    public function get_lookup_departments_get(){
        $check = $this->pronet_model->get_lookup_departments();
        if($check){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    //(use the "name" value to send to API)
    public function get_lookup_motivo_reception_get(){
        $check = $this->pronet_model->get_lookup_motivo_reception();
        if($check){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    //($department_id = null): if the optional $department_id is provided, it will filter the response only for the provided department, otherwise will return all municipios
    public function get_lookup_municipios_get(){
        $department_id = $this->get('department_id');
        $check = $this->pronet_model->get_lookup_municipios($department_id);
        if($check){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    //(use the "name" value to send to API)
    public function get_lookup_ocupation_get(){
        $check = $this->pronet_model->get_lookup_ocupation();
        if($check){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    //(concatenate the "initial" and "final" values with "-" in between, both for displaying in the UI and sending to API as rangoIngresos & rangoEgresos fields)
    public function get_lookup_rangos_get(){
        $check = $this->pronet_model->get_lookup_rangos();
        if($check){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function get_all_lookups_get(){
        $check = $this->pronet_model->get_all_lookups();
        if($check){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function update_info_1_post(){
        $customer_reference = $this->post('R1');
        $uData['bcountry']   = $this->post('R2');
        $uData['bplace']     = $this->post('R3');
        $uData['bdate']      = $this->post('R4');
        $uData['gender']     = $this->post('R5');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $uData['userid']  = $customer['userid'];
        $uData['bdate'] = date('Y-m-d', strtotime($uData['bdate']));
        if(!empty($uData['bcountry']) && !empty($uData['bplace']) && !empty($uData['bdate']) && !empty($uData['gender'])){

        $update = $this->customer_model->update_info_1($uData);
        if($update){
            $this->response([
                'status' => TRUE,
                'message' => "Successfully update info.",
                'response_code' => REST_Controller::HTTP_OK
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => "Unable to update.",
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

    public function update_info_2_post(){
        $customer_reference         = $this->post('R1');
        $uData['address']           = $this->post('R2');
        $uData['department']        = $this->post('R3');
        $uData['municipality']      = $this->post('R4');
        $uData['profession']        = $this->post('R5');
        $uData['income']            = $this->post('R6');
        $uData['expenses']          = $this->post('R7');
        $uData['economic_activity'] = $this->post('R8');
        $uData['date_updated']      = date('Y-m-d');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $uData['customer_id']  = $customer['customer_id'];
        #if(!empty($uData['address']) && !empty($uData['department']) && !empty($uData['municipality']) && !empty($uData['profession']) && !empty($uData['income']) && !empty($uData['expenses']) && !empty($uData['economic_activity'])){
        if(!empty($uData['customer_id'])){

        $update = $this->customer_model->update_info_2($uData);
        if($update){
            $this->response([
                'status' => TRUE,
                'message' => "Successfully update info.",
                'response_code' => REST_Controller::HTTP_OK
            ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => "Unable to update.",
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



    /** end of red chapina api's */

    public function check_billpay_status_post(){
        $pronet_customer_id = $this->post('R1');
        $bill_reference     = $this->post('R2');
        $transaction_status = $this->post('R3');

        $check_bill_reference = $this->customer_model->check_bill_reference($bill_reference);
        if(!$check_bill_reference['status']){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid reference number',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $bill_data = $check_bill_reference['data'];
        $customer = $this->customer_model->get_customer_byid($bill_data['related_fk_customer_id']);

        $bill_info = $this->pronet_model->get_billpay_info($pronet_customer_id,$bill_reference);
        $bill_info_response = $this->pronet_model->get_response();


        $bill_message = $bill_info_response['ResponseMessage'];

        if($bill_data['transaction_status']=="1"){
            if($transaction_status=="2"){ // 2 = Your transaction has been accepted and processed successfully
                if($check_bill_reference['transaction_type']=="BILLPAY"){
                    $update = $this->db->update('tbl_billpay_transaction', array("transaction_status"=>$transaction_status), array('billpay_transaction_id'=>$bill_data['billpay_transaction_id']));

                    #$message = "You successfully paid ".number_format($bill_message['total_amount'],2)." to biller ".$bill_message['biller_name']."."."Reference No.: ".$bill_data['transaction_number'].".";
                    #send_sms($customer['mobile'],$message);

                    $message = "Pago realizado con exito de ".number_format($bill_message['total_amount'],2)." a ".$bill_message['biller_name']."."." No. De referencia:".$bill_data['transaction_number'].".";
                    send_sms($customer['mobile'],$message);

                    

                }else if($check_bill_reference['transaction_type']=="TOPUP"){
                    $update = $this->db->update('tbl_topup_transaction', array("transaction_status"=>$transaction_status), array('billpay_transaction_id'=>$bill_data['billpay_transaction_id']));

                    #$message = "Akisi Message Center: You successfully Topup ".number_format($bill_message['total_amount'],2)." to mobile number ".$bill_message['account_number']."."."Reference No.: ".$bill_data['transaction_number'].".";

                    $message = "Akisi: Recarga exitosa de Q".number_format($bill_message['total_amount'],2)." al ".$bill_message['account_number']."."."No. de Referencia: ".$bill_data['transaction_number'].".";
                    send_sms($customer['mobile'],$message);
                }

            }else if($transaction_status=="3"){ //3 = (failed/error), Your transaction has failed / need to rollback
                if($check_bill_reference['transaction_type']=="BILLPAY"){ // billpay rollback
                    
                    if($this->settlement_model->credit_customer_account($customer['customer_id'],$customer['balance'],$bill_message['total_amount'])){
                        $debit = $this->settlement_model->debit_settlement_account("2","BILLPAY",$bill_message['fee_amount'],$bill_message['balance_amount']);
                        if($debit){
                            $transaction_number = rand ( 1000000000000 , 9999999999999 );
                            $transaction_credit = array(
                                "transaction_date"=>date("Y-m-d H:i:s"),
                                "transaction_type"=>"TOPUP",
                                "transaction_number"=>$transaction_number,
                                "bill_reference"=>$bill_reference,
                                "credit_amount"=>$bill_message['balance_amount'],
                                "fee_amount"=>$bill_message['fee_amount'],
                                "total_amount"=>$bill_message['total_amount'],
                                "fk_customer_id"=>$customer['customer_id'],
                                "related_biller_code"=>$bill_message['biller_code'],
                                "transaction_description"=>"Rollback for biller {$bill_message['biller_name']} ({$bill_message['biller_code']})",
                                "running_balance"=>($customer['balance']+$bill_message['total_amount'])
                            );
                            $this->db->insert('tbl_customer_transaction', $transaction_credit);
                            $fk_customer_transaction_id = $this->db->insert_id();

                            $transaction_debit = array(
                                "transaction_date"=>date("Y-m-d H:i:s"),
                                "transaction_number"=>$transaction_number,
                                "bill_reference"=>$bill_reference,
                                "debit_amount"=>$bill_message['balance_amount'],
                                "fee_amount"=>$bill_message['fee_amount'],
                                "total_amount"=>$bill_message['total_amount'],
                                "transaction_status"=>"2",
                                "biller_code"=>$bill_message['biller_code'],
                                "related_fk_customer_id"=>$customer['customer_id'],
                                "customer_transaction_id"=>$fk_customer_transaction_id,
                                "transaction_description"=>"Rollback Topup for biller ({$bill_message['biller_code']}) ",
                                "running_balance"=>($debit['running_balance'])
                            );
                            $this->db->insert('tbl_topup_transaction', $transaction_debit);
                        }

                        $update = $this->db->update('tbl_billpay_transaction', array("transaction_status"=>$transaction_status), array('billpay_transaction_id'=>$bill_data['billpay_transaction_id']));
                    }

                }else if($check_bill_reference['transaction_type']=="TOPUP"){ // topup rollback
                    

                    if($this->settlement_model->credit_customer_account($customer['customer_id'],$customer['balance'],$bill_message['total_amount'])){
                        $debit = $this->settlement_model->debit_settlement_account("3","TOPUP",$bill_message['fee_amount'],$bill_message['balance_amount']);
                        if($debit){
                            $transaction_number = rand ( 1000000000000 , 9999999999999 );
                            $transaction_credit = array(
                                "transaction_date"=>date("Y-m-d H:i:s"),
                                "transaction_type"=>"TOPUP",
                                "transaction_number"=>$transaction_number,
                                "bill_reference"=>$bill_reference,
                                "credit_amount"=>$bill_message['balance_amount'],
                                "fee_amount"=>$bill_message['fee_amount'],
                                "total_amount"=>$bill_message['total_amount'],
                                "fk_customer_id"=>$customer['customer_id'],
                                "related_biller_code"=>$bill_message['biller_code'],
                                "transaction_description"=>"Rollback for biller {$bill_message['biller_name']} ({$bill_message['biller_code']})",
                                "running_balance"=>($customer['balance']+$bill_message['total_amount'])
                            );
                            $this->db->insert('tbl_customer_transaction', $transaction_credit);
                            $fk_customer_transaction_id = $this->db->insert_id();

                            $transaction_debit = array(
                                "transaction_date"=>date("Y-m-d H:i:s"),
                                "transaction_number"=>$transaction_number,
                                "bill_reference"=>$bill_reference,
                                "debit_amount"=>$bill_message['balance_amount'],
                                "fee_amount"=>$bill_message['fee_amount'],
                                "total_amount"=>$bill_message['total_amount'],
                                "transaction_status"=>"2",
                                "biller_code"=>$bill_message['biller_code'],
                                "related_fk_customer_id"=>$customer['customer_id'],
                                "customer_transaction_id"=>$fk_customer_transaction_id,
                                "transaction_description"=>"Rollback Topup for biller ({$bill_message['biller_code']}) ",
                                "running_balance"=>($debit['running_balance'])
                            );
                            $this->db->insert('tbl_topup_transaction', $transaction_debit);
                        }
                    }

                    $update = $this->db->update('tbl_topup_transaction', array("transaction_status"=>$transaction_status), array('billpay_transaction_id'=>$bill_data['billpay_transaction_id']));

                }
                $message = "Akisi Message Center: Failed Transaction for Reference No.: ".$bill_data['transaction_number'].". The amount has been credit back in your Wallet.";
                send_sms($customer['mobile'],$message);
            }
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'This transaction is already processed.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    // default timezone is UTC date_default_timezone_get()
    public function check_billpay_reminder_post(){
        

        $check_bill_reminder = $this->customer_model->check_bill_reminder();
        
        if(!empty($check_bill_reminder)){
            $message = "";
            foreach($check_bill_reminder as $row){
                #$message = "This is a reminder message for your biller ".$row['biller_nickname']." that you need to pay.";
                $message = "Mensaje de Recordatorio para efectuar el pago de ".$row['biller_nickname'].".";
                $notify = notify_customer("Akisi",$row['firebase_refid'],$message,"");

                #send_sms($row['mobile'],$message);
            }
        $this->response([
            'status' => TRUE,
            'message' => "reminder successfully sent.",
            'response_code' => REST_Controller::HTTP_OK
        ], REST_Controller::HTTP_OK);
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'No customer reminder has found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function get_payment_method_get(){
        $methods = $this->pronet_model->get_payment_methods();
        if($methods){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function get_customer_payment_method_get(){
        $customer_reference = $this->get('customer_reference');
        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $customer_methods = $this->pronet_model->get_customer_payment_method($customer['pronet_customer_id']);
        if($customer_methods){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function inquire_load_wallet_post(){
        $customer_reference = $this->post('R1');
        $amount = $this->post('R2');
        $credit_card = $this->post('R3');
        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $inquire = $this->pronet_model->inquire_creditcard_charge($customer['pronet_customer_id'], $amount, $credit_card);
        if($inquire){
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => TRUE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_OK);
        }else{
            $response = $this->pronet_model->get_response();
            $this->response([
                'status' => FALSE,
                'message' => $response['ResponseMessage'],
                'response_code' => $response['ResponseCode']
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function pay_load_wallet_post(){
        $customer_reference      = $this->post('R1');
        $cData['reference']      = $this->post('R2');
        $cData['cardnumber']     = $this->post('R3');
        $cData['cvv']            = $this->post('R4');
        $cData['expiry_month']   = $this->post('R5');
        $cData['expiry_year']    = $this->post('R6');

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'customer not found',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
        $customer_balance = $customer['balance'];
        $bill_info = $this->pronet_model->get_billpay_info($customer['pronet_customer_id'],$cData['reference']);
        $bill_info_response = $this->pronet_model->get_response();

        if($bill_info_response['ResponseCode']!="0000"){
            $this->response([
                'status' => FALSE,
                'message' => 'Invalid reference number',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $bill_message = $bill_info_response['ResponseMessage'];
        $cData['bill_type'] = $bill_message['biller_type']; //1 is billpay,2 is topup
        $cData['biller_name']    = $bill_message['biller_name'];
        $cData['total_amount']   = $bill_message['total_amount'];

        if(!empty($cData['cardnumber']) && !empty($cData['cvv']) && !empty($cData['expiry_month']) && !empty($cData['expiry_year'])){
            $paybill = $this->pronet_model->pay_bill($customer['pronet_customer_id'], $cData['reference'], $cData['cardnumber'], $cData['cvv'],$cData['expiry_month'],$cData['expiry_year']);
                $response = $this->pronet_model->get_response();
                if ($response['ResponseCode']=="1000") {
                    // not successfull
                    $this->response([
                        'status' => FALSE,
                        'message' => $response['ResponseMessage'],
                        'response_code' => $response['ResponseCode']
                    ], REST_Controller::HTTP_BAD_REQUEST);
                }else{
                    //successful
                    $data = $response['ResponseMessage'];
                    $reference_code = rand ( 1000000000000 , 9999999999999 );

                    $debit = $this->settlement_model->debit_prefund_account("4","CREDIT_CARD",$data['fee_amount'],$data['balance_amount']);
                    if($debit['status']){
                        $this->settlement_model->credit_customer_account($customer['customer_id'],$customer_balance,$data['balance_amount']);
                        $via_cc = "de Credito.";

                        //insert in tbl_customer_other_activity
                        $transaction_debit = array(
                            "transaction_date"=>date("Y-m-d H:i:s"),
                            "transaction_type"=>"LOAD",
                            "transaction_number"=>$reference_code,
                            "bill_reference"=>$cData['reference'],
                            "debit_amount"=>$data['balance_amount'],
                            "fee_amount"=>$data['fee_amount'],
                            "total_amount"=>$data['total_amount'],
                            "fk_customer_id"=>$customer['customer_id'],
                            "biller_code"=>$data['biller_code'],
                            "transaction_description"=>"Recarga a billetera por medio de Tarjeta ".$via_cc,
                            "cardnumber"=>substr($cData['cardnumber'], 12,16),
                            //"cvv"=>md5(md5($cData['cvv'])),
                            "expiry_month"=>md5(md5($cData['expiry_month'])),
                            "expiry_year"=>md5(md5($cData['expiry_year'])),
                            "transaction_status"=>$data['status']
                        );
                        
                        #"transaction_description"=>"Load Wallet ".$via_cc,
                        $this->db->insert('tbl_customer_other_activity', $transaction_debit);
                        #$fk_customer_transaction_id = $this->db->insert_id();

                        $transaction_credit = array(
                            "transaction_date"=>date("Y-m-d H:i:s"),
                            "transaction_type"=>"LOAD",
                            "transaction_number"=>$reference_code,
                            "bill_reference"=>$cData['reference'],
                            "credit_amount"=>$data['balance_amount'],
                            "fee_amount"=>$data['fee_amount'],
                            "total_amount"=>$data['total_amount'],
                            "fk_customer_id"=>$customer['customer_id'],
                            "related_biller_code"=>$data['biller_code'],
                            "transaction_description"=>"Recarga a billetera por medio de Tarjeta ".$via_cc,
                            "running_balance"=>($customer_balance+$data['balance_amount']),
                            "transaction_status"=>$data['status'],
                        );
                        #"transaction_description"=>"Load Wallet ".$via_cc,
                        $this->db->insert('tbl_customer_transaction', $transaction_credit);

                        $data_return = array(
                            "amount_load"=>$data['balance_amount'],
                            "fee_amount"=>$data['fee_amount'],
                            "transaction_id"=>$reference_code,
                            "bill_reference"=>$cData['reference'],
                            "date"=>date("F d, Y h:ia"),
                        );

                        if($data['status']=="2"){
                            #$message = "You successfully load ".number_format($data['balance_amount'],2)." to your Akisi Wallet. Reference No.: ".$reference_code.".";

                            $message = "YCargaste exitosamente ".number_format($data['balance_amount'],2)." en tu billetera Akisi. No de referencia: ".$reference_code.".";
                            send_sms($customer['mobile'],$message);
                        }else{
                            $message = "Your payment of ".number_format($data['total_amount'],2)." to load your Akisi wallet has been accepted."."Reference No.: ".$reference_code.".";
                            send_sms($customer['mobile'],$message);
                        }

                        $this->response([
                            'status' => TRUE,
                            'message' => $data['message'],
                            'data'=>$data_return,
                            'response_code' => REST_Controller::HTTP_OK
                        ], REST_Controller::HTTP_OK);
                    }else{
                        $this->response([
                            'status' => FALSE,
                            'message' => 'Ha ocurrido algun problema, por favor  vuelve a intentarlo',
                            'response_code' => REST_Controller::HTTP_BAD_REQUEST
                        ], REST_Controller::HTTP_BAD_REQUEST);
                    }
                }

        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'Please provide complete information.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

}

?>