<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;
require APPPATH . '/libraries/Format.php';

class Pronet extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('pronet_model');
    }

    public function process_cron_checkstatus_get(){
        $this->pronet_model->process_cron_checkstatus();
    }

    public function process_cron_getupdate_get(){
        $this->pronet_model->update_billers();
    }

    public function generate_references_get(){
        $this->pronet_model->generate_references();
    }

    public function inquire_redchapina_get(){
        exit;
        if (!$this->pronet_model->inquire_redchapina(1,'PR9999999999')) {
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

    public function get_receipt_get(){
        if (!$this->pronet_model->get_transaction_receipt($this->get('R1'))) {
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

    public function get_billpay_info_get(){
        if (!$this->pronet_model->get_billpay_info(2,'237420962281')) {
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

    public function pay_redchapina_get(){
        $params = array(
            'benNumeroIdentificacion' => '',
            'benPais' => '',
            'benPrimerApellido' => 'Perez',
            'benPrimerNombre' => 'Gloria',
            'benTipoIdentificacion' => 'DPI',
            'comentario' => 'Compra libros',
            'departamento' => 'Guatemala',
            'municipio' => 'San Raymundo',
            'direccion' => 'Frente mercado SAN RAYUMUNDO',
            'ocupacion' => null,
            'relacionRemitente' => '',
            'motivoRecepcion' => '',
            'rangoIngresos' => '',
            'rangoEgresos' => '',
            'genero' => '',
            'lugarNacimiento' => '',
            'fechaNacimiento' => '',
            'actividadEconomica' => '',
            'monedaPago' => '',
            'benTelefono' => '66666666',
        );
        $sig = base64_encode(file_get_contents('http://localhost/pronet/1110100124956201901223.jpg'));
        if (!$this->pronet_model->pay_redchapina(1,'837979807921',$params,$sig)) {
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

    public function get_all_lookups_get(){
        if (!$this->pronet_model->get_all_lookups()) {
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

    public function get_billers_get(){
        if (!$this->pronet_model->get_billers()) {
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

    public function add_user_get(){
        //$name, $dpi, $email, $telno, $dob, $nit, $method_of_payment (optional: default E007)
        if (!$this->pronet_model->add_user('Fname Lname', '7711223344553', 'djdj3hdhy2hshsj2@gmail.com', '31111225', '1990-01-01','134091830-2')) {
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

    public function inquire_bill_get(){
        //$pronet_customer_id, $biller_id, $account_number, $account_type = 0, $denomination_id=null, $payment_method=null
//        if (!$this->pronet_model->inquire_bill(1, '002', '0000000',0, null, 'E004', 3)) {
        if (!$this->pronet_model->inquire_bill(1, '101', '999999999999',0, null, 'E007', 10)) {//test for financial services using genesis
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

    public function pay_bill_get(){
        //$pronet_customer_id, $bill_reference, $cardnumber=null, $cvv=null, $expiry_month=null, $expiry_year=null
        if (!$this->pronet_model->pay_bill(1, '882332411098', '4999999999999999', '4521','12','2022')) {
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

    public function inquire_loadwallet_get(){
        if (!$this->pronet_model->inquire_creditcard_charge(11, '100', '4999999999999999')) {
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

    public function notify_billpay_status_get(){
//        error_reporting(E_ALL);
//        ini_set('display_errors', true);
//        $headers = array(
//            'authorization: Basic UFJPTkVUOkFEVTM4MU5VWUFIUFBMOTI4MVNE',
//            'content-type: application/json',
//            'x-api-key: 7T1S9KEIKYQBCO30SHJSW',
//        );
//        $req = curl_init(BASE_URL . 'api/v1/customer/check_billpay_status');
//        curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($req, CURLOPT_POST, 1 );
//        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($req, CURLOPT_SSL_VERIFYHOST, false);
//        if ($headers !== null && is_array($headers)) { curl_setopt($req, CURLOPT_HTTPHEADER, $headers); } else { curl_setopt($req, CURLOPT_HEADER, 0); }
//        curl_setopt($req, CURLOPT_POSTFIELDS, http_build_query(array('R1'=>$this->get('R1'), 'R2' => $this->get('R2'), 'R3' => $this->get('R3'))));
//        curl_setopt($req, CURLOPT_CONNECTTIMEOUT ,0);
//        $resp =curl_exec($req);
//        curl_close($req);
//        var_dump($resp);
////        $resp = json_decode($resp, true);
////        return $resp;
////        var_dump(post_curl_result(BASE_URL . '/api/v1/customer/check_billpay_status', array('R1'=>$this->get('R1'), 'R2' => $this->get('R2'), 'R3' => $this->get('R3')), $headers));
        var_dump($this->pronet_model->test_update_status($this->get('R1'), $this->get('R2'), $this->get('R3')));
    }

    public function pay_loadwallet_get(){
        if (!$this->pronet_model->pay_bill(11, $this->get('R1'), '4999999999999999', '4521','12','2022')) {
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
}
