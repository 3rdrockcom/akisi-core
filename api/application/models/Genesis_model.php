<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Genesis_model extends CI_Model {
    protected $url_staging = "http://genestaging.genesisempresarial.org:21505";
    protected $port = "21504";
    protected $url_production = "https://genapp.genesisempresarial.org:21504";
    protected $provider_id_stage = "3b7628d2-d80c-4c97-b20d-e2cd7e7f6118";
    protected $provider_id = "ee1edfe1-e811-42fa-b275-ccf4aff4d7b8";
    protected $provider_password = "p@s5w0rdG3c0";
    protected $response;

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_token_providers($data=array()){
        $curl = post_curl_result($this->url_production . '/api/v1/providers/get-token', $data,"");
        return $curl;
    }

    public function get_initial_data($token=""){
        $curl = post_curl_result($this->url_production . '/api/v1/providers/initial-data', array('token' => $token),"");
        return $curl;
    }

    public function account_balance($data=""){
        $curl = post_curl_result($this->url_production . '/api/v1/providers/account/balance', $data,"");
        return $curl;
    }

    public function make_transaction_payment($data=""){
        $curl = post_curl_result($this->url_production . '/api/v1/providers/transaction/register', $data,"");
        return $curl;
    }

    public function user_account_register($data=""){
        $curl = post_curl_result($this->url_production . '/api/v1/providers/user/account/register', $data,"");
        return $curl;
    }

    public function step1_user_basic($data=""){
        $curl = post_curl_result($this->url_production . '/api/v1/providers/client/register/basic/step1', $data,"");
        return $curl;
    }

    public function step2_user_location($data=""){
        $curl = post_curl_result($this->url_production . '/api/v1/providers/client/register/location/step2', $data,"");
        return $curl;
    }

    public function step3_employment_situation($data=""){
        $curl = post_curl_result($this->url_production . '/api/v1/providers/client/register/emp-situation/step3', $data,"");
        return $curl;
    }

    public function step4_client_reference($data=""){
        $curl = post_curl_result($this->url_production . '/api/v1/providers/client/register/references/step4', $data,"");
        return $curl;
    }

    public function get_result_psycho($data=""){
        $curl = post_curl_result($this->url_production . '/api/v1/providers/client/psycho/get-result', $data,"");
        return $curl;
    }

    public function detail_transactions($data=""){
        $curl = post_curl_result($this->url_production . '/api/v1/providers/account/details-transactions/', $data,"");
        return $curl;
    }




    public function get_response() {
        return $this->response;
    }

    protected function success($response_message = '', $response_code = '0000') {
        $response = array(
            'ResponseCode' => $response_code,
            'ResponseMessage' => $response_message,
        );
        $this->response = $response;
        return true;
    }

    protected function failed($response_message, $response_code = '1000') {
        $response = array(
            'ResponseCode' => $response_code,
            'ResponseMessage' => $response_message,
        );
        $this->response = $response;
        return false;
    }
}