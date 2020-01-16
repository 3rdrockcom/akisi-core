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

class Merchant extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('customer_model');
        $this->load->model('settlement_model');
        $this->load->model('pronet_model');
        $this->load->model('merchant_model');
        $this->load->helper('url');
    }

    public function add_merchant_post(){
        $cData                  = array();
        $mData                  = array();
        $mData['fk_program_id']    = $this->post('R1');
        $mData['merchant_name'] = $this->post('R2');
        $mData['merchant_code'] = $this->post('R3');
        $cData['email']         = $this->post('R4');
        $cData['mobile']        = $this->post('R5');
        $mData['telno']         = $this->post('R6');
        $mData['address']       = $this->post('R7');
        $cData['fname']         = $this->post('R8');
        $cData['lname']         = $this->post('R9');
        $cData['userno']        = $this->post('R3');

        /*
        $check_email = $this->merchant_model->check_merchant_email_if_exist($this->post('R4'));
        if(!empty($check_email)){
            $this->response([
                'status' => FALSE,
                'message' => 'Email already exist',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
        */
        $check_merchant = $this->merchant_model->check_merchant_if_exist($this->post('R3'));
        if(!empty($check_merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'Merchant code already exist',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }


        #!empty($cData['fname']) && !empty($cData['lname']) && !empty($cData['mobile']) && !empty($cData['email']) && 
        if(!empty($mData['fk_program_id']) && !empty($mData['merchant_name']) && !empty($mData['merchant_code'])){
            $insert = $this->merchant_model->add_merchant($cData,$mData);
            //check if the merchant data inserted
            if($insert['status']){
                // create a folder for a merchant
                $dir = "../merchant_document/m".$insert['merchant_id'];
                if( is_dir($dir) === false )
                {
                    mkdir($dir);
                }

                //set the response and exit
                $this->response([
                    'status' => TRUE,
                    'message' => 'Successfully added a merchant.',
                    'merchant_reference' => $insert['merchant_reference'],
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
        }else{
            //set the response and exit
            $this->response([
                    'status' => FALSE,
                    'message' => 'Provide complete merchant information to create.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function update_merchant_post(){
        $merchant_reference = $this->post('R1');
        $mData['address']   = $this->post('R2');
        $cData['fname']     = $this->post('R3');
        $cData['lname']     = $this->post('R4');

        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if(!empty($cData['fname']) && !empty($cData['lname']) && !empty($mData['address']) ){
            $this->db->update('tblUserInformation', $cData, array('userid'=>$merchant['userid']));
            $update = $this->db->update('tbl_merchant', $mData, array('merchant_reference'=>$merchant_reference));
            if($update){
                $this->response([
                    'status' => TRUE,
                    'message' => 'Merchant information successfully updated.',
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
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete merchant information to create.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function load_merchant_prefund_post(){
        $merchant_reference = $this->post('R1');
        $amount   = $this->post('R2');

        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if($amount<=0){
            $this->response([
                'status' => FALSE,
                'message' => 'amount is required.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $transaction_number = rand ( 1000000000000 , 9999999999999 );
        $debit = $this->settlement_model->debit_prefund_account(6,"LOAD",0.00,$amount);
        #print_r($debit);
        if($debit['status']){
            $credit = $this->settlement_model->credit_merchant_prefund($merchant['merchant_id'],$merchant['prefund_balance'],$amount);
            if($credit){
                $transaction_credit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_type"=>"LOAD",
                    "transaction_number"=>$transaction_number,
                    "credit_amount"=>$amount,
                    "total_amount"=>$amount,
                    "fk_merchant_id"=>$merchant['merchant_id'],
                    "transaction_description"=>"Load prefund balance of ".$merchant['merchant_name'],
                    "prefund_running_balance"=>($merchant['prefund_balance']+$amount)
                );
                $this->db->insert('tbl_merchant_transaction', $transaction_credit);
                $transaction_debit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_number"=>$transaction_number,
                    "debit_amount"=>$amount,
                    "total_amount"=>$amount,
                    "related_fk_merchant_id"=>$merchant['merchant_id'],
                    "transaction_description"=>"Receive Load from ".$merchant['merchant_name'],
                    "running_balance"=>($debit['running_balance']-$amount)
                );
                $this->db->insert('tbl_load_transaction', $transaction_debit);
                $data = array(
                    "amount_load"=>$amount,
                    "transaction_id"=>$transaction_number,
                    "transaction_date"=>date("F d, Y h:ia"),
                    "receipient"=>$merchant['merchant_name'],
                );
                $this->response($data, REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Some problems occurred, please try again.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }


    }

    public function get_merchant_info_get(){
        //single row will be returned
        $merchant_reference = $this->get('merchant_reference');
        $merchant = $this->merchant_model->get_merchant($merchant_reference);

        //check if the merchant data exists
        if(!empty($merchant)){
            //set the response and exit
            $this->response($merchant, REST_Controller::HTTP_OK);
        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No merchant were found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function get_merchant_transaction_get(){
        $merchant_reference = $this->get('merchant_reference');
        $transaction_type = $this->get('transaction_type');
        $transaction_id = $this->get('transaction_id');
        
        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $transactions = $this->merchant_model->get_merchant_transaction($merchant['merchant_id'],$transaction_type,$transaction_id);

        //check if the transaction data exists
        if(!empty($transactions)){
            //set the response and exit
            $this->response($transactions, REST_Controller::HTTP_OK);
        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No merchant transaction were found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function search_customer_get(){
        $merchant_reference = $this->get('merchant_reference');
        $mobile             = $this->get('mobile');
        $dpi              = $this->get('dpi');
        $merchant = $this->merchant_model->get_merchant($merchant_reference);

        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if(!empty($mobile) || !empty($dpi)){
                $customer = $this->customer_model->search_customer_by_category($mobile,$dpi);
                //check if the transaction data exists
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

        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'please provide a data for mobile or dpi number.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

    }

    public function get_customer_info_get(){
        //single row will be returned
        $merchant_reference = $this->get('merchant_reference');
        $customer_reference = $this->get('customer_reference');
        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid customer reference',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }

        $customer_array = array();
        $customer_array['customer_reference'] = $customer['customer_reference'];
        $customer_array['fname'] = $customer['fname'];
        $customer_array['lname'] = $customer['lname'];
        $customer_array['mname'] = $customer['mname'];
        $customer_array['slname'] = $customer['slname'];
        $customer_array['bdate'] = $customer['bdate'];
        $customer_array['email'] = $customer['email'];
        $customer_array['mobile'] = $customer['mobile'];
        $customer_array['address'] = $customer['address'];
        $customer_array['dpi_url'] = $customer['dpi_url'];
        $customer_array['selfie_url'] = $customer['selfie_url'];
        $customer_array['reverse_url'] = $customer['reverse_url'];

        //check if the customer data exists
        if(!empty($customer)){
            //set the response and exit
            $this->response($customer_array, REST_Controller::HTTP_OK);
        }
    }

    public function initiate_transaction_post(){
        // compute also the transaction fee
        $merchant_reference = $this->post('R1');
        $customer_reference = $this->post('R2');
        $transaction_type   = $this->post('R3');
        $amount             = $this->post('R4');
        $fee             = $this->post('R5');

        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if($transaction_type=="LOAD"){
            if($merchant['prefund_balance']<($amount+$fee)){
                $this->response([
                    'status' => FALSE,
                    'message' => 'Insufficient merchant prefund balance.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid customer reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if($transaction_type!="LOAD" && $transaction_type!="PURCHASE" && $transaction_type!="CASHOUT"){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid transaction type.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{
            $reference_number = rand ( 100000 , 999999 );
            $fee_amount = $fee;
            $cData = array();
            $cData['transaction_type'] = $transaction_type;
            $cData['reference_number'] = $reference_number;
            $cData['amount'] = $amount;
            $cData['fee_amount'] = $fee_amount;
            $cData['total_amount'] = $amount + $fee_amount;
            $cData['fk_merchant_id'] = $merchant['merchant_id'];
            $cData['related_fk_customer_id'] = $customer['customer_id'];
            $cData['customer_reference'] = $customer_reference;
            $insert = $this->db->insert('tbl_initiate_transaction', $cData);

            if($insert){
                $message = "Your reference number for your ".$transaction_type." transaction is ".$reference_number;
                $notify = notify_customer("Akisi",$customer['firebase_refid'],$message,$reference_number);
                $array_return = array();
                $array_return['transaction_type'] = $transaction_type;
                $array_return['amount'] = $amount;
                $array_return['fee_amount'] = $fee_amount;
                $array_return['total_amount'] = $amount + $fee_amount;
                $array_return['customer_name'] = $customer['fname']." ".$customer['lname'];
                $array_return['mobile'] = $customer['mobile'];
                $array_return['email'] = $customer['email'];
                $array_return['address'] = $customer['address'];
                $this->response($array_return, REST_Controller::HTTP_OK);
            }else{
                $this->response([
                    'status' => FALSE,
                    'message' => 'Some problems occurred, please try again.',
                    'response_code' => REST_Controller::HTTP_BAD_REQUEST
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

        }
    }


    public function load_customer_post(){
        // debit merchant prefund / credit customer wallet / credit merchant fee settlement
        $merchant_reference = $this->post('R1');
        $reference_number = $this->post('R2');
        $pronet_transaction_id = $this->post('R3');

        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $ref_data = $this->merchant_model->check_reference($reference_number,'LOAD');
        if(empty($ref_data)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid reference number',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
        $customer = $this->customer_model->get_customer($ref_data['customer_reference']);
        $transaction_number = rand ( 1000000000000 , 9999999999999 );
        $debit = $this->settlement_model->debit_merchant_account($merchant['merchant_id'],$merchant['prefund_balance'],$ref_data['amount'],$ref_data['fee_amount']);
        if($debit){
            $credit = $this->settlement_model->credit_customer_account($customer['customer_id'],$customer['balance'],$ref_data['amount']);
            if($credit){
                $transaction_debit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_type"=>"LOAD",
                    "transaction_number"=>$transaction_number,
                    "reference_number"=>$ref_data['reference_number'],
                    "debit_amount"=>$ref_data['amount'],
                    "fee_amount"=>$ref_data['fee_amount'],
                    "total_amount"=>$ref_data['total_amount'],
                    "fk_merchant_id"=>$merchant['merchant_id'],
                    "related_fk_customer_id"=>$customer['customer_id'],
                    "transaction_description"=>"Load wallet of ".$customer['fname']." ".$customer['lname'],
                    "prefund_running_balance"=>($merchant['prefund_balance']-$ref_data['amount']),
                    "pronet_transaction_id"=>$pronet_transaction_id
                );
                $this->db->insert('tbl_merchant_transaction', $transaction_debit);
                $transaction_credit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_type"=>"LOAD",
                    "transaction_number"=>$transaction_number,
                    "credit_amount"=>$ref_data['amount'],
                    "total_amount"=>$ref_data['amount'],
                    "fk_customer_id"=>$customer['customer_id'],
                    "related_fk_merchant_id"=>$merchant['merchant_id'],
                    "reference_number"=>$ref_data['reference_number'],
                    "transaction_description"=>"Receive Load from ".$merchant['merchant_name'],
                    "running_balance"=>($customer['balance']+$ref_data['amount']),
                    "pronet_transaction_id"=>$pronet_transaction_id,
                    "transaction_status"=>"2" // successfull
                );
                $this->db->insert('tbl_customer_transaction', $transaction_credit);
                $this->db->update('tbl_initiate_transaction', array("transaction_status"=>"SUCCESS"),array("reference_number"=>$reference_number,"transaction_type"=>"LOAD"));
                $data = array(
                    "amount_load"=>$ref_data['amount'],
                    "amount_fee"=>$ref_data['fee_amount'],
                    "amount_total"=>$ref_data['total_amount'],
                    "transaction_id"=>$transaction_number,
                    "transaction_date"=>date("F d, Y h:ia"),
                    "receipient"=>$customer['fname']." ".$customer['lname'],
                    "mobile"=>$customer['mobile']
                );
                $message_d = "Your Akisi wallet have been credited ".number_format($ref_data['amount'],2).". Reference No.: ".$transaction_number.".";


                send_sms($customer['mobile'],$message_d);
                $this->response($data, REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Some problems occurred, please try again.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }


    }

    public function load_customer_wallet_post(){
        // debit merchant prefund / credit customer wallet / credit merchant fee settlement
        $merchant_reference = $this->post('R1');
        $customer_reference = $this->post('R2');
        $amount             = $this->post('R3');
        $fee             = $this->post('R4');
        $pronet_transaction_id = $this->post('R5');

        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
        
        if($merchant['prefund_balance']<($amount+$fee)){
            $this->response([
                'status' => FALSE,
                'message' => 'Insufficient merchant prefund balance.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $customer = $this->customer_model->get_customer($customer_reference);
        if(empty($customer)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid customer reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        if(empty($amount) || $amount<1){
            $this->response([
                'status' => FALSE,
                'message' => 'load amount required',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $transaction_number = rand ( 1000000000000 , 9999999999999 );
        $debit = $this->settlement_model->debit_merchant_account($merchant['merchant_id'],$merchant['prefund_balance'],$amount,$fee);
        if($debit){
            $credit = $this->settlement_model->credit_customer_account($customer['customer_id'],$customer['balance'],$amount);
            if($credit){
                $transaction_debit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_type"=>"LOAD",
                    "transaction_number"=>$transaction_number,
                    "debit_amount"=>$amount,
                    "fee_amount"=>$fee,
                    "total_amount"=>($fee+$amount),
                    "fk_merchant_id"=>$merchant['merchant_id'],
                    "related_fk_customer_id"=>$customer['customer_id'],
                    "transaction_description"=>"Load wallet of ".$customer['fname']." ".$customer['lname'],
                    "prefund_running_balance"=>($merchant['prefund_balance']-$amount),
                    "pronet_transaction_id"=>$pronet_transaction_id
                );
                $this->db->insert('tbl_merchant_transaction', $transaction_debit);
                $transaction_credit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_type"=>"LOAD",
                    "transaction_number"=>$transaction_number,
                    "credit_amount"=>$amount,
                    "total_amount"=>$amount,
                    "fk_customer_id"=>$customer['customer_id'],
                    "related_fk_merchant_id"=>$merchant['merchant_id'],
                    "transaction_description"=>"Receive Load from ".$merchant['merchant_name'],
                    "running_balance"=>($customer['balance']+$amount),
                    "pronet_transaction_id"=>$pronet_transaction_id,
                    "transaction_status"=>"2" // successfull
                );
                $this->db->insert('tbl_customer_transaction', $transaction_credit);
                $data = array(
                    "amount_load"=>$amount,
                    "amount_fee"=>$fee,
                    "amount_total"=>($fee+$amount),
                    "transaction_id"=>$transaction_number,
                    "transaction_date"=>date("F d, Y h:ia"),
                    "receipient"=>$customer['fname']." ".$customer['lname'],
                    "mobile"=>$customer['mobile']
                );
                $message_d = "Your Akisi wallet have been credited ".number_format($amount,2).". Reference No.: ".$transaction_number.".";


                send_sms($customer['mobile'],$message_d);
                $this->response($data, REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Some problems occurred, please try again.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }


    }

    public function purchase_post(){
        //debit customer  credit merchant settlement

        $merchant_reference = $this->post('R1');
        $reference_number = $this->post('R2');
        $pronet_transaction_id = $this->post('R3');

        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $ref_data = $this->merchant_model->check_reference($reference_number,'PURCHASE');
        if(empty($ref_data)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid reference number',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $customer = $this->customer_model->get_customer($ref_data['customer_reference']);
        $transaction_number = rand ( 1000000000000 , 9999999999999 );

        if($customer['balance']<$ref_data['total_amount']){
            $this->response([
                'status' => FALSE,
                'message' => 'insufficient balance',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $debit = $this->settlement_model->debit_customer_account($customer['customer_id'],$customer['balance'],$ref_data['total_amount']);
        //$merchant['merchant_id'],$merchant['prefund_balance'],$ref_data['amount'],$ref_data['fee_amount']
        if($debit){
            $credit = $this->settlement_model->credit_merchant_settlement($merchant['merchant_id'],$merchant['settlement_balance'],$ref_data['amount']);
            if($credit){
                $transaction_debit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_type"=>"PURCHASE",
                    "transaction_number"=>$transaction_number,
                    "debit_amount"=>$ref_data['amount'],
                    "fee_amount"=>$ref_data['fee_amount'],
                    "total_amount"=>$ref_data['total_amount'],
                    "fk_customer_id"=>$customer['customer_id'],
                    "related_fk_merchant_id"=>$merchant['merchant_id'],
                    "reference_number"=>$ref_data['reference_number'],
                    "transaction_description"=>"Purchase from ".$merchant['merchant_name'],
                    "running_balance"=>($customer['balance']-$ref_data['amount']),
                    "pronet_transaction_id"=>$pronet_transaction_id,
                    "transaction_status"=>"2" // successfull
                );
                $this->db->insert('tbl_customer_transaction', $transaction_debit);
                $transaction_credit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_type"=>"PURCHASE",
                    "transaction_number"=>$transaction_number,
                    "reference_number"=>$ref_data['reference_number'],
                    "debit_amount"=>$ref_data['amount'],
                    "fee_amount"=>$ref_data['fee_amount'],
                    "total_amount"=>$ref_data['total_amount'],
                    "fk_merchant_id"=>$merchant['merchant_id'],
                    "related_fk_customer_id"=>$customer['customer_id'],
                    "transaction_description"=>"Purchase by ".$customer['fname']." ".$customer['lname'],
                    "settlement_running_balance"=>($merchant['settlement_balance']+$ref_data['amount']),
                    "pronet_transaction_id"=>$pronet_transaction_id
                );
                $this->db->insert('tbl_merchant_transaction', $transaction_credit);

                $this->db->update('tbl_initiate_transaction', array("transaction_status"=>"SUCCESS"),array("reference_number"=>$reference_number,"transaction_type"=>"PURCHASE"));
                $data = array(
                    "amount_purchase"=>$ref_data['amount'],
                    "amount_fee"=>$ref_data['fee_amount'],
                    "amount_total"=>$ref_data['total_amount'],
                    "transaction_id"=>$transaction_number,
                    "transaction_date"=>date("F d, Y h:ia"),
                    "purchase_by"=>$customer['fname']." ".$customer['lname'],
                    "mobile"=>$customer['mobile']
                );
                $message_d = "Your Purchase an amount of ".number_format($ref_data['amount'],2).". Reference No.: ".$transaction_number.".";


                send_sms($customer['mobile'],$message_d);
                $this->response($data, REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Some problems occurred, please try again.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }

    public function cashout_by_mobile_post(){
        //debit customer credit merchant settlement
        $merchant_reference = $this->post('R1');
        $reference_number = $this->post('R2');
        $pronet_transaction_id = $this->post('R3');

        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $ref_data = $this->merchant_model->check_reference($reference_number,'CASHOUT');
        if(empty($ref_data)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid reference number',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $customer = $this->customer_model->get_customer($ref_data['customer_reference']);
        $transaction_number = rand ( 1000000000000 , 9999999999999 );

        if($customer['balance']<$ref_data['total_amount']){
            $this->response([
                'status' => FALSE,
                'message' => 'insufficient balance',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $debit = $this->settlement_model->debit_customer_account($customer['customer_id'],$customer['balance'],$ref_data['total_amount']);
        //$merchant['merchant_id'],$merchant['prefund_balance'],$ref_data['amount'],$ref_data['fee_amount']
        if($debit){
            $credit = $this->settlement_model->credit_merchant_settlement($merchant['merchant_id'],$merchant['settlement_balance'],$ref_data['amount']);
            if($credit){
                $transaction_debit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_type"=>"CASHOUT",
                    "transaction_number"=>$transaction_number,
                    "debit_amount"=>$ref_data['amount'],
                    "fee_amount"=>$ref_data['fee_amount'],
                    "total_amount"=>$ref_data['total_amount'],
                    "fk_customer_id"=>$customer['customer_id'],
                    "related_fk_merchant_id"=>$merchant['merchant_id'],
                    "reference_number"=>$ref_data['reference_number'],
                    "transaction_description"=>"Cashout by mobile from ".$merchant['merchant_name'],
                    "running_balance"=>($customer['balance']-$ref_data['amount']),
                    "pronet_transaction_id"=>$pronet_transaction_id,
                    "transaction_status"=>"2" // successfull
                );
                $this->db->insert('tbl_customer_transaction', $transaction_debit);
                $transaction_credit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_type"=>"CASHOUT",
                    "transaction_number"=>$transaction_number,
                    "reference_number"=>$ref_data['reference_number'],
                    "debit_amount"=>$ref_data['amount'],
                    "fee_amount"=>$ref_data['fee_amount'],
                    "total_amount"=>$ref_data['total_amount'],
                    "fk_merchant_id"=>$merchant['merchant_id'],
                    "related_fk_customer_id"=>$customer['customer_id'],
                    "transaction_description"=>"Cashout to ".$customer['fname']." ".$customer['lname'],
                    "settlement_running_balance"=>($merchant['settlement_balance']+$ref_data['amount']),
                    "pronet_transaction_id"=>$pronet_transaction_id
                );
                $this->db->insert('tbl_merchant_transaction', $transaction_credit);

                $this->db->update('tbl_initiate_transaction', array("transaction_status"=>"SUCCESS"),array("reference_number"=>$reference_number,"transaction_type"=>"CASHOUT"));
                $data = array(
                    "amount"=>$ref_data['amount'],
                    "amount_fee"=>$ref_data['fee_amount'],
                    "amount_total"=>$ref_data['total_amount'],
                    "transaction_id"=>$transaction_number,
                    "transaction_date"=>date("F d, Y h:ia"),
                    "transaction_type"=>"Cash Out by mobile",
                    "receipient"=>$customer['fname']." ".$customer['lname'],
                    "mobile"=>$customer['mobile']
                );
                $message_d = "You received an amount of ".number_format($ref_data['amount'],2)." via CASHOUT by mobile. Reference No.: ".$transaction_number.".";


                send_sms($customer['mobile'],$message_d);
                $this->response($data, REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Some problems occurred, please try again.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function search_cashout_code_post(){
        $merchant_reference = $this->post('R1');
        $cashout_code = $this->post('R2');

        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $cData = $this->merchant_model->check_cashout_code_detail($cashout_code);
        if(empty($cData)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid cashout code.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{
            $this->response($cData, REST_Controller::HTTP_OK);
        }
    }

    public function cashout_by_code_post(){
        //credit merchant settlement
        $merchant_reference = $this->post('R1');
        $cashout_code = $this->post('R2');
        $fee = $this->post('R3');
        $id_type = $this->post('R4');
        $id_expiry_date = $this->post('R5');
        $id_card_number = $this->post('R6');
        $pronet_transaction_id = $this->post('R7');


        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $cData = $this->merchant_model->check_cashout_code_detail($cashout_code);
        if(empty($cData)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid cashout code.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $co_data = $this->merchant_model->get_cashout_code_detail($cashout_code);

        if(!empty($id_type) && !empty($id_expiry_date) && !empty($id_card_number)){
            $transaction_number = rand ( 1000000000000 , 9999999999999 );
            $credit = $this->settlement_model->credit_merchant_settlement($merchant['merchant_id'],$merchant['settlement_balance'],$cData['amount']);
            if($credit){
                $transaction_update = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_number"=>$transaction_number,
                    "cashout_fee_amount"=>$fee,
                    "fk_merchant_id"=>$merchant['merchant_id'],
                    "id_type_presented"=>$id_type,
                    "id_expiry_date"=>$id_expiry_date,
                    "id_card_number"=>$id_card_number,
                    "pronet_transaction_id"=>$pronet_transaction_id,
                    "transaction_status"=>"2" // successfull
                );
                $this->db->update('tbl_cashout_transaction', $transaction_update,array('cashout_code'=>$cashout_code,'transaction_status'=>"PENDING"));
                $transaction_credit = array(
                    "transaction_date"=>date("Y-m-d H:i:s"),
                    "transaction_type"=>"CASHOUT",
                    "transaction_number"=>$transaction_number,
                    "reference_number"=>$cashout_code,
                    "debit_amount"=>$cData['amount'],
                    "total_amount"=>$cData['amount'],
                    "fk_merchant_id"=>$merchant['merchant_id'],
                    "related_fk_customer_id"=>$co_data['related_fk_customer_id'],
                    "transaction_description"=>"Cashout to ".$cData['receiver_firstname']." ".$cData['receiver_lastname'],
                    "settlement_running_balance"=>($merchant['settlement_balance']+$cData['amount']),
                    "pronet_transaction_id"=>$pronet_transaction_id,
                    "transaction_status"=>"2" // successfull
                );
                $this->db->insert('tbl_merchant_transaction', $transaction_credit);

                $data = array(
                    "amount"=>$cData['amount'],
                    "amount_total"=>$cData['amount'],
                    "transaction_id"=>$transaction_number,
                    "transaction_date"=>date("F d, Y h:ia"),
                    "transaction_type"=>"Cash Out by Code",
                    "sender"=>$cData['sender_firstname']." ".$cData['sender_lastname'],
                    "receipient"=>$cData['receiver_firstname']." ".$cData['receiver_lastname'],
                    "receipient_mobile"=>$cData['receiver_mobile']
                );
                $message_d = "You successfuly redeemed an amount of ".number_format($cData['amount'],2)." via CASHOUT by Code. Reference No.: ".$transaction_number.".";
                $message_s = $cData['receiver_firstname']." ".$cData['receiver_lastname']." received the amount of ".number_format($cData['amount'],2)." you sent via CASHOUT by Code. Reference No.: ".$transaction_number.".";

                send_sms($cData['receiver_mobile'],$message_d);
                send_sms($cData['sender_mobile'],$message_s);
                $this->response($data, REST_Controller::HTTP_OK);
            }
        }else{
            $this->response([
                'status' => FALSE,
                'message' => 'Provide complete information to cashout.',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

    }

    public function search_transaction_id_get(){
        $merchant_reference = $this->get('merchant_reference');
        $transaction_id = $this->get('transaction_id');

        $merchant = $this->merchant_model->get_merchant($merchant_reference);
        if(empty($merchant)){
            $this->response([
                'status' => FALSE,
                'message' => 'invalid merchant reference',
                'response_code' => REST_Controller::HTTP_BAD_REQUEST
            ], REST_Controller::HTTP_BAD_REQUEST);
        }

        $transactions = $this->merchant_model->get_merchant_transaction($merchant['merchant_id'],"PURCHASE",$transaction_id);
        $data = array();
        //check if the transaction data exists
        if(!empty($transactions)){
            $data['transaction_date'] = $transactions[0]['transaction_date'];
            $data['transaction_type'] = $transactions[0]['transaction_type'];
            $data['transaction_number'] = $transactions[0]['transaction_number'];
            $data['debit_amount'] = $transactions[0]['debit_amount'];
            $data['fee_amount'] = $transactions[0]['fee_amount'];
            $data['total_amount'] = $transactions[0]['total_amount'];
            $data['transaction_description'] = $transactions[0]['transaction_description'];
            //set the response and exit
            $this->response($data, REST_Controller::HTTP_OK);
        }else{
            //set the response and exit
            $this->response([
                'status' => FALSE,
                'message' => 'No purchase transaction were found.',
                'response_code' => REST_Controller::HTTP_NOT_FOUND
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function api_logs_get(){
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

?>