<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pronet_model extends CI_Model {
    protected $client;
    protected $billers;
    protected $billers_sequential;
    protected $session;
    protected $response;
    protected $creds;
    protected $api_logs;
    protected $pronet_customer;
    protected $method_of_payments;
    protected $transaction_status_details;
    protected $billers_financial_services = array();
    protected $forbidden_payment_methods = array();
    protected $debug_logs = array();

    public function __construct() {
        parent::__construct();
        $this->load->database();
        require_once((dirname(__DIR__). "/third_party/nusoap/src/nusoap.php"));
        $this->client = new nusoap_client(PRONET_BILLPAY_URL, 'wsdl');
        $this->client->soap_defencoding = 'UTF-8';
        $this->client->decode_utf8 = false;
        $err = $this->client->getError();
        if ($err) { die('error 0'); }
        $this->get_billers();
    }

    protected function create_session() {
        if (!(isset($this->pronet_customer['username'], $this->pronet_customer['password']))) {
            return $this->failed('Error: Invalid customer info');
        } else {
            $params = array(
                'sessionRequest' => array(
                    'strChannel' => 8,
                    'strPassword' => base64_decode($this->pronet_customer['password']),
                    'strPosId' => '',
                    'strUserName' => $this->pronet_customer['username'],
                ),
            );
            $soap_response = $this->call_soap('GetSession', $params);
            if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
                return $this->failed('Error: unable to create session');
            } elseif (!(isset($soap_response['return']['strSessionId']) && $soap_response['return']['strSessionId'] !='')) {
                return $this->failed('Error: unable to create session');
            } else {
                $this->session = $soap_response['return'];
                return $soap_response['return']['strSessionId'];
            }
        }
    }

    protected function call_soap($function, $params) {
        $result = $this->client->call($function, $params);
        $rec = array(
            'request' => json_encode(array('function' => $function, 'parameters' => $params)),
            'response' => serialize($result),
        );
        $this->debug_logs['pronet_log_id'] = $this->api_logs['id'];
        $this->db->set($rec)->insert('pronet_api_logs');
        $this->api_logs['id'] = $this->db->insert_id();
        if ($this->client->fault) {
            return $this->failed('Error1: unable to connect to service');
        } else {
            $err = $this->client->getError();
            if ($err) {
                return $this->failed('Error2: unable to connect to service');
            } else {
                return $result;
            }
        }
    }

    protected function get_pronet_customer_info($id) {
        $q = $this->db->where('id', $id)->get('pronet_customers');
        if (!($q->num_rows() > 0)) {
            return $this->failed('Error: customer info not found');
        } else {
            $this->pronet_customer = $q->row_array();
            if (!(isset($this->pronet_customer['mobile_number']))) {
                return $this->failed('Error: customer info not found');
            } elseif (!$this->create_session()) {
                return $this->failed('Error: unable to create session');
            } else {
                return $this->success();
            }
        }
    }

    //params:
    public function inquire_redchapina($pronet_customer_id, $mtcn) {
        if (!(is_numeric($pronet_customer_id) && $pronet_customer_id > 0 && $this->get_pronet_customer_info($pronet_customer_id))) {
            return $this->failed('Error: Invalid customer ID');
        } elseif (!($mtcn !='')) {
            return $this->failed('Error: MTCN');
        } else {
            $reference = $this->get_unique_reference();
            if ($reference === null) {
                return $this->failed('Internal system error: reference');
            } else {
                $params = array(
                    'genericPaymentRequest' => array(
                        'strCommerceId' => '102', //hard-coded ID for red chapina
                        'strTransactionId' => '01', //all inquiry request have 01 value here
                        'strPan' => $mtcn, //the account number, or bill identifier
                        'strPan2' => $this->pronet_customer['dpi'], //the account number, or bill identifier
                        'strCurrency' => '320', //todo: check if this is fixed
                        'strReference' => $reference,
                        'strSessionId' => $this->session['strSessionId'],
                    ),
                );

                $this->client = new nusoap_client(PRONET_REMITTANCE_URL, 'wsdl');
                $this->client->decode_utf8 = false; //inserted to fix the issue;
                $this->client->soap_defencoding = 'UTF-8';
                $soap_response = $this->call_soap('RegisterGenericPayment', $params);
                if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
                    $message = isset($soap_response['return']['strResponseMessage'])? $soap_response['return']['strResponseMessage']: 'Request failed';
                    return $this->failed('Error: ' . $message);
                } elseif (!(isset($soap_response['return']['redInformation']['idInterno']) && $soap_response['return']['redInformation']['idInterno'] !='')) {
                    return $this->failed('Error: Unable to process request');
                } else {
//                    $soap_response['return']['redInformation'] = array_map('utf8_decode', $soap_response['return']['redInformation']);
                    $transaction = array(
                        'pronet_customer_id'        => $pronet_customer_id,
                        'idInterno'                 => $soap_response['return']['redInformation']['idInterno'],
                        'valorPagar'                => $soap_response['return']['redInformation']['valorPagar'],
                        'dblCashAmount'             => $soap_response['return']['redInformation']['valorPagar'], //todo: check
                        'dblTotalAmount'            => $soap_response['return']['redInformation']['valorPagar'], //todo: check
                        'benNumeroIdentificacion'   => $soap_response['return']['redInformation']['benNumeroIdentificacion'],
                        'benPais'                   => $soap_response['return']['redInformation']['benPais'],
                        'benPrimerApellido'         => $soap_response['return']['redInformation']['benPrimerApellido'],
                        'benPrimerNombre'           => $soap_response['return']['redInformation']['benPrimerNombre'],
                        'benSegundoApellido'        => $soap_response['return']['redInformation']['benSegundoApellido'],
                        'benSegundoNombre'          => $soap_response['return']['redInformation']['benSegundoNombre'],
                        'benTelefono'               => $soap_response['return']['redInformation']['benTelefono'],
                        'benTipoIdentificacion'     => $soap_response['return']['redInformation']['benTipoIdentificacion'],
                        'comentario'                => $soap_response['return']['redInformation']['comentario'],
                        'departamento'              => $soap_response['return']['redInformation']['departamento'],
                        'municipio'                 => $soap_response['return']['redInformation']['municipio'],
                        'direccion'                 => $soap_response['return']['redInformation']['direccion'],
                        'ocupacion'                 => $soap_response['return']['redInformation']['ocupacion'],
                        'relacionRemitente'         => $soap_response['return']['redInformation']['relacionRemitente'],
                        'motivoRecepcion'           => $soap_response['return']['redInformation']['motivoRecepcion'],
                        'rangoIngresos'             => $soap_response['return']['redInformation']['rangoIngresos'],
                        'rangoEgresos'              => $soap_response['return']['redInformation']['rangoEgresos'],
                        'genero'                    => $soap_response['return']['redInformation']['genero'],
                        'lugarNacimiento'           => $soap_response['return']['redInformation']['lugarNacimiento'],
                        'fechaNacimiento'           => $soap_response['return']['redInformation']['fechaNacimiento'],
                        'actividadEconomica'        => $soap_response['return']['redInformation']['actividadEconomica'],
                        'monedaPago'                => $soap_response['return']['redInformation']['monedaPago'],
                    );
                    foreach ($transaction as $k=>$v) {$transaction[$k] = utf8_encode($v); }
                    $insert_data = $transaction;
                    $insert_data['pronet_customer_id']        = $pronet_customer_id;
                    $insert_data['MTCN']                      = $mtcn;
                    $insert_data['strReferenceInquiry']       = $reference;
                    $insert_data['strReferenceReservation']   = null;
                    $insert_data['strReferencePayment']       = null;
                    $insert_data['log_id_inquiry']            = $this->api_logs['id'];
                    $insert_data['log_id_reservation']        = null;
                    $insert_data['log_id_payment']            = null;

                    $this->db->set($insert_data)->insert('pronet_customer_transactions_redchapina');
                    if (!($this->db->affected_rows() > 0)) {
                        return $this->failed('Internal error');
                    } else {
                        $transaction['pronet_customer_id'] = $pronet_customer_id;
                        $transaction['reference'] = $reference;
                        return $this->success($transaction);
                    }
                }
            }
        }
    }

    protected function get_base64_file($url) {
        return base64_encode(file_get_contents($url));
    }

    //params:pronet_customer_id, $remittance_reference, $params=array of parameters;
    public function pay_redchapina($pronet_customer_id, $remittance_reference, $params, $signature_base64) {
        if (!(is_numeric($pronet_customer_id) && $pronet_customer_id > 0 && $this->get_pronet_customer_info($pronet_customer_id))) {
            return $this->failed('Error: Invalid customer ID');
        } elseif (!($remittance_reference != '' && is_numeric($remittance_reference) && strlen($remittance_reference) == 12)) {
            return $this->failed('Error: Invalid remittance reference');
//        } elseif (!(trim($signature_base64) != '')) {
//            return $this->failed('Error: Missing signature');
        } else {
            $where = array(
                'strReferenceInquiry' => $remittance_reference,
                'pronet_customer_id' => $pronet_customer_id,
            );
            $q = $this->db->where($where)->get('pronet_customer_transactions_redchapina');

            if (!($q->num_rows()> 0)) {
                return $this->failed('Error: Invalid remittance reference');
            } else {
                $rec = $q->row_array();
                if (time() - strtotime($rec['timestamp']) > (3600*1)) {//1 hour
                    return $this->failed('Error: expired remittance reference');
                } elseif ($rec['status'] == 2) {
                    return $this->failed('Error: invalid remittance status');
                    //todo: initiate a "free remittance" transaction to pronet to cancel reservation
                } elseif ($rec['status'] != 1) {
                    return $this->failed('Error: invalid remittance status');
                } elseif (!(is_array($params) && count($params) > 0)) {
                    return $this->failed('Error: missing information');
                } elseif (!(trim($this->pronet_customer['dpi_front']) !='' && trim($this->pronet_customer['dpi_back']) !='' )) {
                    return $this->failed('Error: missing information');
                } else {
                    $required_field = explode(',', 'benNumeroIdentificacion,benPais,benPrimerApellido,benPrimerNombre,benTipoIdentificacion,comentario,departamento,municipio,'
                            . 'direccion,ocupacion,relacionRemitente,motivoRecepcion,rangoIngresos,rangoEgresos,genero,lugarNacimiento,fechaNacimiento,actividadEconomica,monedaPago,benTelefono');
                    $temp = $rec;
                    $do_not_replace = array('benTipoIdentificacion','benNumeroIdentificacion','benPrimerApellido','benPrimerNombre','Genero','fechaNacimiento','lugarNacimiento');
                    //replace record with customer-provided parameters, but only if the parameters are not empty/null
                    $params = array_map('urldecode', $params);
                    foreach ($params as $k=> $v) {
                        if (isset($rec[$k]) && $v !== null && trim($v) != '') {
                            if (!(in_array($k, $do_not_replace)) || ($temp[$k] == null || trim($temp[$k]) == '') ) {
                                $temp[$k] = $v;
                            }
                        }
                    }
                    //supply defaults if required field is empty
                    if (isset($temp['benPais']) && trim($temp['benPais']) == '') { $temp['benPais'] = 'GTM'; }
                    if (isset($temp['benTipoIdentificacion']) && trim($temp['benTipoIdentificacion']) == '') { $temp['benTipoIdentificacion'] = 'DPI'; }
                    if (isset($temp['lugarNacimiento']) && trim($temp['lugarNacimiento']) == '') { $temp['lugarNacimiento'] = 'GTM'; }
                    if (isset($temp['monedaPago']) && trim($temp['monedaPago']) == '') { $temp['monedaPago'] = 'GTQ'; }
                    if (isset($temp['benNumeroIdentificacion']) && trim($temp['benNumeroIdentificacion']) == '') { $temp['benNumeroIdentificacion'] = $this->pronet_customer['dpi']; }
                    if (isset($temp['benTelefono']) && trim($temp['benTelefono']) == '') { $temp['benTelefono'] = $this->pronet_customer['mobile_number']; }
                    $missing = array();
                    $for_update = array();
                    foreach ($temp as $k => $v) {
                        if ($v === null) {
                            $temp[$k] = '';
                        }
                        if (trim($v) == '' && in_array($k, $required_field)) {//empty supplied
                            $missing[] = substr($k,0,3) == 'ben'? substr($k,3): $k; //remove ben prefix
                        }
                        if (isset($rec[$k]) && $temp[$k] != $rec[$k]) {//include in for updating array
                            $for_update[$k] = $temp[$k];
                        }
                    }
                    if (count($missing) > 0) {
                        return $this->failed('Error: missing required information: ' . implode(', ', $missing));
                    } else {
                        $reference = $this->get_unique_reference();
                        if ($reference === null) {
                            return $this->failed('Internal system error: reference');
                        } else {
                            $api_params_reserve = array(
                                'genericPaymentRequest' => array(
                                    'strCommerceId' => 102,
                                    'strCurrency' => 320,
                                    'redInformation' => array(
                                        'idInterno' => $temp['idInterno'],
                                        'valorPagar' => $temp['valorPagar'],
                                    ),
                                    'strPan' => $temp['MTCN'],
                                    'strPan2' => $temp['benNumeroIdentificacion'],
                                    'strReference' => $reference,
                                    'strSessionId' => $this->session['strSessionId'],
                                    'strTransactionId' => '04', //reservation = 04
                                ),
                            );
                            $this->client = new nusoap_client(PRONET_REMITTANCE_URL, 'wsdl');
                            $this->client->soap_defencoding = 'UTF-8';
                            $this->client->decode_utf8 = false;
                            $soap_response = $this->call_soap('RegisterGenericPayment', $api_params_reserve);
                            if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
                                $message = isset($soap_response['return']['strResponseMessage'])? $soap_response['return']['strResponseMessage']: 'Request failed';
                                $update_rec = array(
                                    'log_id_reservation' => $this->api_logs['id'],
                                    'status' => 4, //error
                                    'comments' => 'Error during reservation',
                                );
                                $this->db->set($update_rec)->where('id', $rec['id'])->update('pronet_customer_transactions_redchapina');
                                return $this->failed('Error: ' . $message);
                            } else {
                                $update_rec = array(
                                    'strReferenceReservation' => $reference,
                                    'idReserva' => $soap_response['return']['redInformation']['idReserva'],
                                    'log_id_reservation' => $this->api_logs['id'],
                                    'status' => 2, //reservation ok
                                );
                                $this->db->set($update_rec)->where('id', $rec['id'])->update('pronet_customer_transactions_redchapina');
                                if (!($this->db->affected_rows() > 0)) {
                                    return $this->failed('Internal payment error'); //db issue
                                    //todo: internal error, send info to developers;
                                } else {
                                    $reference_payment = $this->get_unique_reference();
                                    if ($reference_payment === null) {
                                        return $this->failed('Internal system error: reference');
                                    } else {
                                        $front_dpi_base64 = trim($this->pronet_customer['dpi_front']) != ''? $this->get_base64_file($this->pronet_customer['dpi_front']): '';
                                        $back_dpi_base64 = trim($this->pronet_customer['dpi_back']) != ''? $this->get_base64_file($this->pronet_customer['dpi_back']): '';
//                                        $front_dpi_base64 = $this->get_base64_file('http://52.52.132.7/customer_document/sample.jpg');
//                                        $back_dpi_base64 = $this->get_base64_file('http://52.52.132.7/customer_document/sample4_l.jpg');
                                        $api_params_payment = array(
                                            'genericPaymentRequest' => array(
                                                'dblCashAmount' => $temp['valorPagar'],
                                                'dblCheckAmount' => 0.00,
                                                'dblOtherAmount' => 0.00,
                                                'dblTotalAmount' => $temp['valorPagar'],
                                                'redInformation' => array(
                                                    'benNumeroIdentificacion'   => $temp['benNumeroIdentificacion'],
                                                    'benPais'                   => $temp['benPais'],
                                                    'benPrimerApellido'         => $temp['benPrimerApellido'],
                                                    'benPrimerNombre'           => $temp['benPrimerNombre'],
                                                    'benSegundoApellido'        => $temp['benSegundoApellido'],
                                                    'benSegundoNombre'          => $temp['benSegundoNombre'],
                                                    'benTelefono'               => $temp['benTelefono'],
                                                    'benTipoIdentificacion'     => $temp['benTipoIdentificacion'],
                                                    'comentario'                => $temp['comentario'],
                                                    'departamento'              => $temp['departamento'],
                                                    'municipio'                 => $temp['municipio'],
                                                    'direccion'                 => $temp['direccion'],
                                                    'ocupacion'                 => $temp['ocupacion'],
                                                    'relacionRemitente'         => $temp['relacionRemitente'],
                                                    'motivoRecepcion'           => $temp['motivoRecepcion'],
                                                    'rangoIngresos'             => $temp['rangoIngresos'],
                                                    'rangoEgresos'              => $temp['rangoEgresos'],
                                                    'genero'                    => $temp['genero'],
                                                    'lugarNacimiento'           => $temp['lugarNacimiento'],
                                                    'fechaNacimiento'           => $temp['fechaNacimiento'],
                                                    'actividadEconomica'        => $temp['actividadEconomica'],
                                                    'monedaPago'                => $temp['monedaPago'],
                                                    'idInterno'                 => $temp['idInterno'],
                                                    'idReserva'                 => $soap_response['return']['redInformation']['idReserva'],
                                                    'campoReserva1'             => $front_dpi_base64,
                                                    'campoReserva2'             => $back_dpi_base64,
                                                    'campoReserva3'             => $signature_base64,
                                                    'valorPagar'                => $temp['valorPagar'],
                                                ),
                                                'strCommerceId' => '102', //hard-coded ID for red chapina
                                                'strTransactionId' => '18', //hard-coded for payment of remittance
                                                'strPan' => $temp['MTCN'],
                                                'strPan2' => $temp['benNumeroIdentificacion'],
                                                'strCurrency' => '320', //todo: check if this is fixed
                                                'strReference' => $reference_payment,
                                                'strSessionId' => $this->session['strSessionId'],
                                            ),
                                        );
                                        $soap_response = $this->call_soap('RegisterGenericPayment', $api_params_payment);
                                        if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
                                            $message = isset($soap_response['return']['strResponseMessage'])? $soap_response['return']['strResponseMessage']: 'Request failed';
                                            $update_rec = array(
                                                'log_id_payment' => $this->api_logs['id'],
                                                'status' => 4, //error
                                                'comments' => 'Error during payment',
                                            );
                                            $this->db->set($update_rec)->where('id', $rec['id'])->update('pronet_customer_transactions_redchapina');
                                            //====================================================================================================================================
                                            //todo: implement free payment transaction here
                                            //====================================================================================================================================
                                            return $this->failed('Error: ' . $message);
                                        } else {
                                            $update_rec = $for_update;
                                            $update_rec['payment_timestamp'] = date('Y-m-d H:i:s');
                                            $update_rec['strReferencePayment'] = $reference_payment;
                                            $update_rec['log_id_payment'] = $this->api_logs['id'];
                                            $update_rec['status'] = 3; //payment ok
                                            $this->db->set($update_rec)->where('id', $rec['id'])->update('pronet_customer_transactions_redchapina');
                                            if (!($this->db->affected_rows() > 0)) {
                                                return $this->failed('Internal payment error'); //db issue
                                                //todo: internal error, send info to developers;
                                            } else {
                                                $response = array(
                                                    'message' => 'Successfully processed remittance',
                                                    'amount' => $temp['valorPagar'],
                                                );
                                                $this->success($response);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function get_transaction_receipt($internal_reference) {
        $q = $this->db->where('input_reference', $internal_reference)->where('status', 2)->get('pronet_customer_transactions');
        $q2 = $this->db->where('strReferenceInquiry', $internal_reference)->where('status', 3)->get('pronet_customer_transactions_redchapina');
        if ($q->num_rows() > 0) {
            $r = $q->row_array();
            return $this->get_receipt($r['output_reference']);
        } elseif ($q2->num_rows() > 0) {
            $r = $q2->row_array();
            $q3 = $this->db->where('id', $r['log_id_reservation'])->get('pronet_api_logs');
            if ($q3->num_rows() > 0) {
                $r3 = $q3->row_array();
                $resp = unserialize($r3['response']);
                if (isset($resp['return']['strUniqueTransactionId']) && $resp['return']['strUniqueTransactionId'] != '') {
                    return $this->get_receipt($resp['return']['strUniqueTransactionId']);
                }
            }
        }
        return $this->get_receipt($internal_reference);
    }

    protected function get_receipt($reference) {
        $params = array(
            'GetReceipt' => array(
                'strTranxId' => $reference,
            ),
        );
        $soap_response = $this->call_soap('GetReceipt', $params);
        if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
            $message = isset($soap_response['return']['strResponseMessage'])? $soap_response['return']['strResponseMessage']: 'Request failed';
            return $this->failed('Error: ' . utf8_encode($message));
        } elseif (!(isset($soap_response['return']['strText']) && $soap_response['return']['strText'] !='')) {
            return $this->failed('Error: Unable to process request');
        } else {
            return $this->success(base64_encode(utf8_decode($soap_response['return']['strText'])));
        }
    }

    public function get_billpay_info($pronet_customer_id, $bill_reference) {
        if (!(is_numeric($pronet_customer_id) && $pronet_customer_id > 0 && $this->get_pronet_customer_info($pronet_customer_id))) {
            return $this->failed('Error: Invalid customer ID');
        } elseif (!($bill_reference != '' && is_numeric($bill_reference) && strlen($bill_reference) == 12)) {
            return $this->failed('Error: Invalid bill reference');
        } else {
            $where = array(
                'input_reference' => $bill_reference,
                'pronet_customer_id' => $pronet_customer_id,
            );
            $q = $this->db->where($where)->get('pronet_customer_transactions');

            if (!($q->num_rows()> 0)) {
                return $this->failed('Error: Invalid bill reference');
            } else {
                $rec = $q->row_array();
                $is_payable = (time() - strtotime($rec['timestamp']) > (3600*8) && $rec['status'] == 1) ? true: false;
                $receipt = ($this->get_receipt($rec['output_reference']))? $this->response['ResponseMessage']: 'Not available';
                $response = array(
                    'timestamp'         => $rec['timestamp'],
                    'payment_timestamp' => $rec['payment_timestamp'],
                    'balance_amount'    => $rec['balance_amount'],
                    'fee_amount'        => $rec['total_amount'] - $rec['balance_amount'],
                    'total_amount'      => $rec['total_amount'],
                    'biller_code'       => $rec['biller_code'],
                    'account_number'    => $rec['account_number'],
                    'status'            => $rec['status'], //1-inquiry, 2-payment, 3-inquiry error, 4-payment error, 5-other error
                    'notes'             => $rec['notes'],
                    'account_name'      => $rec['account_name'],
                    'method_of_payment' => $rec['method_of_payment'],
                    'biller_type'       => $rec['biller_type'], //1-postpaid, 2-prepaid
                    'biller_name'       => $rec['biller_name'],
                    'biller_id'         => $rec['biller_id'],
                    'is_payable'        => $is_payable,
                    'account_type'      => $rec['account_type'],
                    'receipt'           => $receipt,
                );
                for ($i = 1; $i<10; $i++) {
                    $response["cf" . $i] = $rec["cf" . $i];
                }
                return $this->success($response);
            }
        }
    }

    //params: pronet_customer_id, biller_id, account_number, denomination*
    public function inquire_bill($pronet_customer_id, $biller_code, $account_number, $account_type = 0, $denomination_id=null, $payment_method=null, $biller_id = null, $cf1=null, $cf2=null, $cf3=null, $cf4=null, $cf5=null, $cf6=null, $cf7=null, $cf8=null, $cf9=null, $cf10=null) {
        if (!(is_numeric($pronet_customer_id) && $pronet_customer_id > 0 && $this->get_pronet_customer_info($pronet_customer_id))) {
            return $this->failed('Error: Invalid customer ID');
        } elseif (!(is_numeric($biller_code) && strlen($biller_code) == 3 && isset($this->billers[$biller_code]))) {
            return $this->failed('Error: Invalid biller ID' . $biller_code);
        } elseif (!($account_number !='')) {
            return $this->failed('Error: Invalid account info');
        } else {
            $reference = $this->get_unique_reference();
            if ($reference === null) {
                return $this->failed('Internal system error: reference');
            } else {
                $trans_id = in_array($biller_code, array('014','009','060'))? '08': '01'; //all inquiry request have 01 value here except for
                if ($biller_code == '060' && ($biller_id == '31' || $account_type === '')) {
                    $trans_id = '01';
                }
                $params = array(
                    'genericPaymentRequest' => array(
                        'strCommerceId' => $biller_code,
                        'strTransactionId' => $trans_id,
                        'strPanType' => $account_type, //by default, this is 0, otherwise caller must include the type here
                        'strPan' => $account_number, //the account number, or bill identifier
                        //'strPan2' => '123213213', //the account number, or bill identifier
                        'strCurrency' => '320', //todo: check if this is fixed
                        'strReference' => $reference,
                        'strSessionId' => $this->session['strSessionId'],
                    ),
                );
                $t = array();
                for ($i = 1; $i<10; $i++) {
                    if (isset(${"cf" . $i}) && ${"cf" . $i} != '') {
                        $tmp = ${"cf" . $i};
                        $p = explode('/',$tmp.'/');
                        $params['genericPaymentRequest'][$p[0]] = $p[1];
                        $t["cf".$i] = $tmp;
                    }
                }
                if ($payment_method !== null && trim($payment_method) != '') {
                    if (!$this->change_payment_method($pronet_customer_id, $payment_method)) {
                        return false;
                    }
                } else {
                    if (!$this->get_customer_payment_method($pronet_customer_id)) {
                        return $this->failed('Internal Error: Unable to retrieve payment method');
                    } else {
                        $payment_method = $this->response['ResponseMessage']['method'];
                    }
                }
                if ($this->get_billers($biller_id)) {
                    $response = $this->get_response();
                    if (isset($response['ResponseMessage']['group_category'], $this->forbidden_payment_methods[$payment_method]) && in_array($response['ResponseMessage']['group_category'], $this->forbidden_payment_methods[$payment_method])) {
                        #return $this->failed('This payment method is not allowed to be used for this service');
                        return $this->failed('No se permite utilizar este metodo de pago para este servicio.');

                    }
                }
                //commented code below, overridden by dynamic code above
//                if (in_array($biller_code, $this->billers_financial_services)) {//financial services biller code, check payment method
//                    if ($payment_method == 'E006') {
//                        return $this->failed('Error: Cannot use selected payment method for this biller');
//                    }
//                }
                $biller_type = 1;//default =1/postpaid
                if ($denomination_id !== null && is_numeric($denomination_id) && $denomination_id > 0) { $params['genericPaymentRequest']['strSpare'] = $denomination_id; $biller_type = 2; }
                $soap_response = $this->call_soap('RegisterGenericPayment', $params);
                if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
                    $message = isset($soap_response['return']['strResponseMessage'])? $soap_response['return']['strResponseMessage']: 'Request failed';
                    return $this->failed('Error: ' . utf8_encode($message));
                } elseif (!(isset($soap_response['return']['strUniqueTransactionId']) && $soap_response['return']['strUniqueTransactionId'] !='')) {
                    return $this->failed('Error: Unable to process request');
                } else {
                    $biller_name = isset($this->billers_sequential[$biller_id]) ? $this->billers_sequential[$biller_id]: (isset($this->billers[$biller_code])? $this->billers[$biller_code]: '');
                    $rec = array(
                        'pronet_customer_id' => $pronet_customer_id,
                        'biller_code' => $biller_code,
                        'biller_id' => $biller_id,
                        'biller_name' => $biller_name,
                        'balance_amount' => $soap_response['return']['electronicPaymentResponse']['dblOriginalAmount'],
                        'total_amount' => $soap_response['return']['electronicPaymentResponse']['dblTotalAmount'],
                        'account_name' => isset($soap_response['return']['accountInformation']['strName'])? $soap_response['return']['accountInformation']['strName']: '',
                        'account_number' => $account_number,
                        'inquiry_log_id' => $this->api_logs['id'],
                        'payment_log_id' => null,
                        'input_reference' => $params['genericPaymentRequest']['strReference'],
                        'output_reference' => $soap_response['return']['strUniqueTransactionId'],
                        'status' => 1,
                        'method_of_payment' => $payment_method,
                        'biller_type' => $biller_type,
                        'notes' => '',
                        'account_type' => $account_type,
                    );
                    $transaction = $rec + $t;
                    $this->db->set($transaction)->insert('pronet_customer_transactions');
                    if (!($this->db->affected_rows() > 0)) {
                        return $this->failed('Internal error');
                    } else {
                        $other_info = array();
                        if ($this->get_billers($biller_id)) {
                            $response = $this->get_response();
                            if (isset($response['ResponseMessage']['outputs'])) {
                                 foreach ($response['ResponseMessage']['outputs'] as $fields) {
                                     if (isset($soap_response['return'][$fields['group']][$fields['field']] )) {
                                        $other_info[$fields['group'] . '.' . $fields['field']] = $soap_response['return'][$fields['group']][$fields['field']];
                                     }
                                 }
                            }
                        }
                        $response = array(
                            'pronet_customer_id' => $pronet_customer_id,
                            'biller_code' => $biller_code,
                            'biller_name' => $biller_name,
                            'biller_id' => $biller_code,
                            'balance_amount' => $transaction['balance_amount'],
                            'fee_amount' => $transaction['total_amount'] - $transaction['balance_amount'],
                            'total_amount' => $transaction['total_amount'],
                            'account_name' => $transaction['account_name'],
                            'account_number' => $account_number,
                            'total_amount' => $soap_response['return']['electronicPaymentResponse']['dblTotalAmount'],
                            'reference' => $transaction['input_reference'],
                            'other_info' => $other_info,
                        );
                        return $this->success($response);
                    }
                }
            }
        }
    }

    //params:pronet_customer_id, bill reference, strPan*,cvv*,strExpiryMonth*,strExpiryYear*,
    public function pay_bill($pronet_customer_id, $bill_reference, $cardnumber=null, $cvv=null, $expiry_month=null, $expiry_year=null) {
        if (!(is_numeric($pronet_customer_id) && $pronet_customer_id > 0 && $this->get_pronet_customer_info($pronet_customer_id))) {
            return $this->failed('Error: Invalid customer ID');
        } elseif (!($bill_reference != '' && is_numeric($bill_reference) && strlen($bill_reference) == 12)) {
            return $this->failed('Error: Invalid bill reference');
        } else {
            $where = array(
                'status' => 1,
                //'payment_timestamp' => null,
                'input_reference' => $bill_reference,
                'pronet_customer_id' => $pronet_customer_id,
            );
            $q = $this->db->where($where)->get('pronet_customer_transactions');

            if (!($q->num_rows()> 0)) {
                return $this->failed('Error: Invalid bill reference');
            } else {
                $rec = $q->row_array();
                if (time() - strtotime($rec['timestamp']) > (3600*8)) {
                    return $this->failed('Error: expired bill reference');
                } else {
                    $params = array(
                        'MassivePaymentRequest' => array(
                            'arrayUniqueTransactionId' => $rec['output_reference'],
                            'strSessionId' => $this->session['strSessionId'],
                        ),
                    );
                    if ($rec['method_of_payment'] == 'E004') {//credit card, require card info
                        if (!(isset($cardnumber) && trim($cardnumber) !='' && is_numeric($cardnumber))) {
                            return $this->failed('Error: Card number is required');
                        } elseif (!(isset($cvv) && trim($cvv) !='' && is_numeric($cvv))) {
                            return $this->failed('Error: CVV is required');
                        } elseif (!(isset($expiry_month) && trim($expiry_month) !='' && is_numeric($expiry_month))) {
                            return $this->failed('Error: Expiry date is required');
                        } elseif (!(isset($expiry_year) && trim($expiry_year) !='' && is_numeric($expiry_year))) {
                            return $this->failed('Error: Expiry date is required');
                        } else {
                            $card_info = [
                                'strCvv' => $cvv,
                                'strExpirMonth' => $expiry_month,
                                'strExpirYear' => $expiry_year,
                                'strIPClient' => $_SERVER['REMOTE_ADDR'],
                                'strPan' => $cardnumber,
                            ];
                            $params['MassivePaymentRequest']['electronicPaymentRequest'] = $card_info;
                        }
                    }
                    $soap_response = $this->call_soap('newMassivePayRequest', $params);
                    if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
                        $message = isset($soap_response['return']['strResponseMessage'])? $soap_response['return']['strResponseMessage']: 'Request failed';
                        return $this->failed('Error: ' . $message);
                        //todo: check if we need to set status as failed in db table, or let the user retry the transaction
                    } else {
                        $update_rec = array(
                            'payment_timestamp' => date('Y-m-d H:i:s'),
                            'payment_log_id' => $this->api_logs['id'],
                            'status' => 2, //payment successful
                        );
                        $this->db->set($update_rec)->where('id', $rec['id'])->update('pronet_customer_transactions');
                        if (!($this->db->affected_rows() > 0)) {
                            return $this->failed('Internal payment error'); //db issue
                            //todo: internal error, send info to developers;
                        } else {
                            $message = 'Your transaction has been accepted';
                            $status = $this->check_status($rec, true);
                            $resp_status = 1; //default = 1, accepted & pending approval
                            if ($status == '00') {//approved
                                $this->db->set('pronet_status', '00')->where('id', $rec['id'])->update('pronet_customer_transactions');
                                $resp_status = 2;
                                $message .= ' and processed successfully';
                            } elseif ($status != '01') {//not on process, something's wrong
                                $this->db->set('pronet_status', $status)->where('id', $rec['id'])->update('pronet_customer_transactions');
                                $resp_status = 3;
                                $message = 'Your transaction has failed';
                            }
                            $response = array(
                                'message' => $message,
                                'status'    => $resp_status, //status: 1-accepted & pending approval, 2-accepted and approved, 3-error
                                'account_number' => $rec['account_number'],
                                'balance_amount' => $rec['balance_amount'],
                                'fee_amount' => $rec['total_amount'] - $rec['balance_amount'],
                                'total_amount' => $rec['total_amount'],
                                'biller_code' => $rec['biller_code'],
                                'biller_name' => $rec['biller_name'],
                                'method_of_payment' => $rec['method_of_payment'],
                            );
                            if ($resp_status == 3) {//failed
                                $message =  isset($this->transaction_status_details['strEstatusDescripcion'])? $this->transaction_status_details['strEstatusDescripcion']: 'Transaction failed.';
                                return $this->failed('Error: ' . $message);
                            } else {
                                return $this->success($response);
                            }
                        }
                    }
                }
            }
        }
    }

    public function inquire_creditcard_charge($pronet_customer_id, $amount, $cardnumber) {
        if (!(is_numeric($pronet_customer_id) && $pronet_customer_id > 0 && $this->get_pronet_customer_info($pronet_customer_id))) {
            return $this->failed('Error: Invalid customer ID');
        } elseif (!(is_numeric($amount) && $amount > 0)) {
            return $this->failed('Error: Invalid amount');
        } elseif (!($cardnumber !='' && strlen($cardnumber) >=15)) {
            return $this->failed('Error: Invalid credit card number');
        } else {
            $reference = $this->get_unique_reference();
            if ($reference === null) {
                return $this->failed('Internal system error: reference');
            } else {
                $biller_code = '171';//default, fixed
                $params = array(
                    'genericPaymentRequest' => array(
                        'dblCashAmount' => $amount,
                        'dblTotalAmount' => $amount,
                        'strCommerceId' => $biller_code,
                        'strTransactionId' => 14,
                        'strCurrency' => '320', //todo: check if this is fixed
                        'strReference' => $reference,
                        'strPan' => $cardnumber,
                        'strSessionId' => $this->session['strSessionId'],
                    ),
                );
                $biller_type = 3;//default =1/postpaid, 2-prepaid, 3-load to wallet via credit card
                $soap_response = $this->call_soap('RegisterGenericPayment', $params);
                if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
                    $message = isset($soap_response['return']['strResponseMessage'])? $soap_response['return']['strResponseMessage']: 'Request failed';
                    return $this->failed('Error: ' . utf8_encode($message));
                } elseif (!(isset($soap_response['return']['strUniqueTransactionId']) && $soap_response['return']['strUniqueTransactionId'] !='')) {
                    return $this->failed('Error: Unable to process request');
                } else {
                    $biller_name = 'Billetera Akisi';
                    $rec = array(
                        'pronet_customer_id' => $pronet_customer_id,
                        'biller_code' => $biller_code,
                        'biller_id' => null,
                        'biller_name' => $biller_name,
                        'balance_amount' => $soap_response['return']['electronicPaymentResponse']['dblOriginalAmount'],
                        'total_amount' => $soap_response['return']['electronicPaymentResponse']['dblTotalAmount'],
                        'account_name' => null,
                        'account_number' => '',
                        'inquiry_log_id' => $this->api_logs['id'],
                        'payment_log_id' => null,
                        'input_reference' => $params['genericPaymentRequest']['strReference'],
                        'output_reference' => $soap_response['return']['strUniqueTransactionId'],
                        'status' => 1,
                        'method_of_payment' => 'E004',
                        'biller_type' => $biller_type, //3-reload akisi wallet via cc
                        'notes' => '',
                        'account_type' => null,
                    );
                    $transaction = $rec;
                    $this->db->set($transaction)->insert('pronet_customer_transactions');
                    if (!($this->db->affected_rows() > 0)) {
                        return $this->failed('Internal error');
                    } else {
                        $response = array(
                            'pronet_customer_id' => $pronet_customer_id,
                            'biller_code' => $biller_code,
                            'biller_name' => $biller_name,
                            'biller_id' => null,
                            'balance_amount' => $transaction['balance_amount'],
                            'fee_amount' => $transaction['total_amount'] - $transaction['balance_amount'],
                            'total_amount' => $transaction['total_amount'],
                            'account_name' => null,
                            'account_number' => null,
                            'total_amount' => $soap_response['return']['electronicPaymentResponse']['dblTotalAmount'],
                            'reference' => $transaction['input_reference'],
                            'other_info' => array(),
                        );
                        return $this->success($response);
                    }
                }
            }
        }
    }

    //params:pronet_customer_id, reference, strPan, cvv,strExpiryMonth,strExpiryYear
    public function pay_creditcard_charge($pronet_customer_id, $reference, $cardnumber, $cvv, $expiry_month, $expiry_year) {
        if (!(is_numeric($pronet_customer_id) && $pronet_customer_id > 0 && $this->get_pronet_customer_info($pronet_customer_id))) {
            return $this->failed('Error: Invalid customer ID');
        } elseif (!($reference != '' && is_numeric($reference) && strlen($reference) == 12)) {
            return $this->failed('Error: Invalid reference');
        } else {
            $where = array(
                'status' => 1,
                'input_reference' => $reference,
                'pronet_customer_id' => $pronet_customer_id,
            );
            $q = $this->db->where($where)->get('pronet_customer_transactions');

            if (!($q->num_rows()> 0)) {
                return $this->failed('Error: Invalid reference');
            } else {
                $rec = $q->row_array();
                if (time() - strtotime($rec['timestamp']) > (3600*8)) {
                    return $this->failed('Error: expired reference');
                } else {
                    $params = array(
                        'MassivePaymentRequest' => array(
                            'arrayUniqueTransactionId' => $rec['output_reference'],
                            'strSessionId' => $this->session['strSessionId'],
                        ),
                    );
                    if (!(isset($cardnumber) && trim($cardnumber) !='' && is_numeric($cardnumber))) {
                        return $this->failed('Error: Card number is required');
                    } elseif (!(isset($cvv) && trim($cvv) !='' && is_numeric($cvv))) {
                        return $this->failed('Error: CVV is required');
                    } elseif (!(isset($expiry_month) && trim($expiry_month) !='' && is_numeric($expiry_month))) {
                        return $this->failed('Error: Expiry date is required');
                    } elseif (!(isset($expiry_year) && trim($expiry_year) !='' && is_numeric($expiry_year))) {
                        return $this->failed('Error: Expiry date is required');
                    } else {
                        $card_info = [
                            'strCvv' => $cvv,
                            'strExpirMonth' => $expiry_month,
                            'strExpirYear' => $expiry_year,
                            'strIPClient' => $_SERVER['REMOTE_ADDR'],
                            'strPan' => $cardnumber,
                        ];
                        $params['MassivePaymentRequest']['electronicPaymentRequest'] = $card_info;
                    }
                    $soap_response = $this->call_soap('newMassivePayRequest', $params);
                    if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
                        $message = isset($soap_response['return']['strResponseMessage'])? $soap_response['return']['strResponseMessage']: 'Request failed';
                        return $this->failed('Error: ' . $message);
                        //todo: check if we need to set status as failed in db table, or let the user retry the transaction
                    } else {
                        $update_rec = array(
                            'payment_timestamp' => date('Y-m-d H:i:s'),
                            'payment_log_id' => $this->api_logs['id'],
                            'status' => 2, //payment successful
                        );
                        $this->db->set($update_rec)->where('id', $rec['id'])->update('pronet_customer_transactions');
                        if (!($this->db->affected_rows() > 0)) {
                            return $this->failed('Internal payment error'); //db issue
                            //todo: internal error, send info to developers;
                        } else {
                            $message = 'Your transaction has been accepted';
                            $status = $this->check_status($rec, true);
                            $resp_status = 1; //default = 1, accepted & pending approval
                            if ($status == '00') {//approved
                                $this->db->set('pronet_status', '00')->where('id', $rec['id'])->update('pronet_customer_transactions');
                                $resp_status = 2;
                                $message .= ' and processed successfully';
                            } elseif ($status != '01') {//not on process, something's wrong
                                $this->db->set('pronet_status', $status)->where('id', $rec['id'])->update('pronet_customer_transactions');
                                $resp_status = 3;
                                $message = 'Your transaction has failed';
                            }
                            $response = array(
                                'message' => $message,
                                'status'    => $resp_status, //status: 1-accepted & pending approval, 2-accepted and approved, 3-error
                                'account_number' => $rec['account_number'],
                                'balance_amount' => $rec['balance_amount'],
                                'fee_amount' => $rec['total_amount'] - $rec['balance_amount'],
                                'total_amount' => $rec['total_amount'],
                                'biller_code' => $rec['biller_code'],
                                'biller_name' => $rec['biller_name'],
                            );
                            if ($resp_status == 3) {//failed
                                $message =  isset($this->transaction_status_details['strEstatusDescripcion'])? $this->transaction_status_details['strEstatusDescripcion']: 'Transaction failed.';
                                return $this->failed('Error: ' . $message);
                            } else {
                                return $this->success($response);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function check_status($rec, $re_check = false) {
        $params = array(
            'CheckStatusRequest' => array(
                'strUniqueReference' => $rec['output_reference'],
                'sessionId' => $this->session['strSessionId'],
            ),
        );
        $soap_response = $this->call_soap('getStatusTransaction', $params);
        if (!isset($soap_response['return']['strDetalleTransaccion']['strEstatus'])) {
            return '99';//something's wrong, but don't mark it yet for rollback
        } elseif ($soap_response['return']['strDetalleTransaccion']['strEstatus'] == '01') {
            //on process
            if ($re_check) { //delay 1 second before re-checking status
                sleep(1);
                return $this->check_status($rec, false);
            }
        }
        $this->transaction_status_details = $soap_response['return']['strDetalleTransaccion'];
        return $soap_response['return']['strDetalleTransaccion']['strEstatus'];
    }

    public function get_redchapina_info($pronet_customer_id, $remittance_reference) {
        if (!(is_numeric($pronet_customer_id) && $pronet_customer_id > 0 && $this->get_pronet_customer_info($pronet_customer_id))) {
            return $this->failed('Error: Invalid customer ID');
        } elseif (!($remittance_reference != '' && is_numeric($remittance_reference) && strlen($remittance_reference) == 12)) {
            return $this->failed('Error: Invalid remittance reference');
        } else {
            $where = array(
                'strReferenceInquiry' => $remittance_reference,
                'pronet_customer_id' => $pronet_customer_id,
            );
            $q = $this->db->where($where)->get('pronet_customer_transactions_redchapina');

            if (!($q->num_rows()> 0)) {
                return $this->failed('Error: Invalid remittance reference');
            } else {
                $rec = $q->row_array();
                $is_payable = (time() - strtotime($rec['timestamp']) > (3600*1) && $rec['status'] == 1) ? true: false;
                $response = array(
                    'timestamp'         => $rec['timestamp'],
                    'payment_timestamp' => $rec['payment_timestamp'],
                    //'MTCN'              => $rec['MTCN'],
                    'amount'            => $rec['valorPagar'],
                    'total_amount'      => $rec['dblTotalAmount'],
                    'status'            => $rec['status'], //1-inquiry ok, 2-reservation ok, 3-payment ok, 4-error
                    'is_payable'        => $is_payable,
                );
                return $this->success($response);
            }
        }
    }

    public function test_update_status($R1, $R2, $R3) {
        return $this->notify_billpay_status($R1, $R2, $R3);
    }

    protected function notify_billpay_status($R1, $R2, $R3) {
        $headers = array(
            'authorization: Basic UFJPTkVUOkFEVTM4MU5VWUFIUFBMOTI4MVNE',
            'content-type: application/x-www-form-urlencoded',
            'x-api-key: 7T1S9KEIKYQBCO30SHJSW',
        );
        return post_curl_result(BASE_URL . 'api/v1/customer/check_billpay_status', array('R1' => $R1, 'R2' => $R2, 'R3' => $R3), $headers);
    }

    public function process_cron_checkstatus() {
        $q = $this->db->where('pronet_status', '01')->where('status',2)->order_by('timestamp', 'random')->get('pronet_customer_transactions', 0, 10);
        if ($q->num_rows() > 0 ) {
            $ids = array();
            $rows = $q->result_array();
            foreach ($rows as $k => $v) { $ids[] = $v['id']; }
            $this->db->where_in('id', $ids)->where('pronet_status', '01')->set('pronet_status', 'PRC')->update('pronet_customer_transactions');
            if ($this->db->affected_rows() > 0) {
                foreach ($rows as $k => $v) {
                    if (!$this->get_pronet_customer_info($v['pronet_customer_id'])) {
                        $result = '99';//internal error; don't mark for rollback
                    } else {
                        $result = $this->check_status($v);
                    }
                    $set_data = array(
                        'pronet_status' => $result,
                        'pronet_status_log_id' => $this->api_logs['id'],
                    );
                    if ($result == '00') {//approved
                        $this->db->where('id', $v['id'])->set($set_data)->update('pronet_customer_transactions');
                        $this->notify_billpay_status($v['pronet_customer_id'], $v['input_reference'], 2);
                    } elseif ($result != '01' && $result != '99') {//not on process, there's an error,
                        $this->db->where('id', $v['id'])->set($set_data)->update('pronet_customer_transactions');
                        $this->notify_billpay_status($v['pronet_customer_id'], $v['input_reference'], 3);
                    } else {//return to original status so it could be processed the next time a cron job runs
                        $this->db->where('id', $v['id'])->set('pronet_status', '01')->update('pronet_customer_transactions');
                    }
                }
            }
        }
    }

    protected function get_unique_reference() {
        $ref = null;
        do {
            $q = $this->db->where('status', 0)->get('pronet_reference_numbers');
            if ($q->num_rows() > 0) {
                $rec = $q->row_array();
                $this->db->where('id', $rec['id'])->set(array('status' => 1))->update('pronet_reference_numbers');
                if ($this->db->affected_rows() > 0) {
                    $ref = $rec['reference'];
                }
            } else {
                //todo: send info to developer, we're out of reference numbers already;
                break;
            }
        } while ($ref == null);
        return $ref;
    }

    public function generate_references() {
        $random = array(); // Get what you already have (from the DB).
        $q = $this->db->get('pronet_reference_numbers');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $rec) {
                $random[] = $rec->reference;
            }
        }
        $random = array_flip($random); //Make them into keys
        $existingCount = count($random); //The codes you already have
        $totalLabels = 100000;
        do {
                $x1 = mt_rand(100000,999999);
                $x2 = mt_rand(100000,999999);
                $x3 = $x1 . $x2;
            $random[$x3] = 1;
        } while ((count($random)-$existingCount) < $totalLabels);
        $random = array_keys($random);
        $recs = array();
        $ctr = 0;
        foreach ($random as $k=>$v) {
            $ctr++;
            if ($ctr >= $existingCount) {
                $recs[] = array(
                    'reference' => $v,
                );
                if (count($recs) > 5000) {
                    $this->db->insert_batch('pronet_reference_numbers', $recs);
                    $recs = array();
                }
            }
        }
        if (count($recs) > 0) {
            $this->db->insert_batch('pronet_reference_numbers', $recs);
        }
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
        $this->debug_logs['response'] = json_encode($response);
        $this->db->set($this->debug_logs)->insert('pronet_debug_logs');
        return false;
    }

    protected function generate_user_creds() {
        do {
            $username = base_convert(uniqid(), 16, 36);
            $q = $this->db->where('username', $username)->get('pronet_customers');
        } while ($q->num_rows() > 0);
        $password = substr(sha1(md5($username . time())),8,12);
        $this->creds = array(
            'username' => $username,
            'password' => $password,
        );
    }

    //params: $pronet_user_id, $name, $email, $telno, $dob, $nit, $dpi_front = null, $dpi_back = null
    public function update_user($pronet_customer_id, $name, $email, $telno, $dob, $nit, $dpi_front = null, $dpi_back = null) {
        if (!($name != '' && $email != '' && $telno != '' && $dob != '' && $nit != '')) {
            return $this->failed('Error: missing a required field');
        } elseif (!(is_numeric($telno) && strlen($telno) == 8)) {
            return $this->failed('Error: telephone number must be an 8-digit number');
        } elseif (!($nit !== null && trim($nit) != '')) {
            return $this->failed('Error: NIT is required');
        } else {
            $dob_check = date('Y-m-d', strtotime($dob));
            if ($dob != $dob_check) {
                return $this->failed('Error: invalid date of birth');
            } elseif (time() < strtotime('+18 years', strtotime($dob))) {
                return $this->failed('Error: Customer is under 18 years of age');
            } else {//check telno, email if already exist in local db
                $cleaned_nit = preg_replace('/[^a-zA-Z0-9]/', '', $nit);
                if (!(strlen($cleaned_nit) <=16 )) {
                    return $this->failed('Error: Invalid NIT provided');
                }
                $q1 = $this->db->where('email_address', $email)->where('id<>', $pronet_customer_id)->get('pronet_customers');
                $q2 = $this->db->where('mobile_number', $telno)->where('id<>', $pronet_customer_id)->get('pronet_customers');
                if ($q1->num_rows() > 0) {
                    return $this->failed('Error: email address already exist');
                } elseif ($q2->num_rows() > 0) {
                    return $this->failed('Error: phone number already exist');
                } else {
                    $params = array(
                        'UserRequest' => array(
                            'strCanal' => '8',
                            'strEmail' => $email,
                            'strNIT' => $cleaned_nit,
                            //'strDPI' => $dpi,
                            'strNombreCliente' => $name,
                            'strFechaNacimiento' => $dob,
                            'strNoTelefono' => $telno,
                            'strMonedaPreferencia' => '320',
                            'strPassword' => $this->creds['password'],
                            'userName' => $cleaned_nit . $telno,
                        ),
                    );
                    $soap_response = $this->call_soap('updateUserData', $params);
                    if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
                        $message = isset($soap_response['return']['strResponseMessage'])? $soap_response['return']['strResponseMessage']: 'Request failed';
                        return $this->failed('Error: ' . utf8_encode($message));
                    } else {
                        $customer_rec = array(
                            'email_address' => $email,
                            'mobile_number' => $telno,
                            'dpi_front' => $dpi_front,
                            'dpi_back' => $dpi_back,
                            'username' => $params['UserRequest']['userName'],
                            'password' => base64_encode($params['UserRequest']['strPassword']),
                        );
                        $this->db->set($customer_rec)->insert('pronet_customers');
                        $id = $this->db->insert_id();
                        $response = array(
                            'message' => 'Successfully registered customer to Pronet',
                            'pronet_customer_id' => $id,
                        );
                        return $this->success($response);
                    }
                }
            }
        }
    }

    //params: $name, $dpi, $email, $telno, $dob, $nit
    public function add_user($name, $dpi, $email, $telno, $dob, $nit, $method = 'E007', $dpi_front = null, $dpi_back = null) {
        if (!($name != '' && $dpi != '' && $email != '' && $telno != '' && $dob != '' && $nit != '')) {
            return $this->failed('Error: missing a required field');
        } elseif (!(is_numeric($telno) && strlen($telno) == 8)) {
            return $this->failed('Error: telephone number must be an 8-digit number');
        } elseif (!($dpi_front !== null && $dpi_back !== null)) {
            return $this->failed('Error: DPI back and front image is required');
        } elseif (!($nit !== null && trim($nit) != '')) {
            return $this->failed('Error: NIT is required');
        } else {
            $dob_check = date('Y-m-d', strtotime($dob));
            if ($dob != $dob_check) {
                return $this->failed('Error: invalid date of birth');
            } elseif (time() < strtotime('+18 years', strtotime($dob))) {
                return $this->failed('Error: Customer is under 18 years of age');
            } else {//check telno, email if already exist in local db
                $cleaned_nit = preg_replace('/[^a-zA-Z0-9]/', '', $nit);
                if (!(strlen($cleaned_nit) <=16 )) {
                    return $this->failed('Error: Invalid NIT provided');
                }
                $q1 = $this->db->where('email_address', $email)->get('pronet_customers');
                $q2 = $this->db->where('mobile_number', $telno)->get('pronet_customers');
                if ($q1->num_rows() > 0) {
                    return $this->failed('Error: email address already exist');
                } elseif ($q2->num_rows() > 0) {
                    return $this->failed('Error: phone number already exist');
                } else {
                    $this->get_payment_methods();
                    $methods = $this->response['ResponseMessage'];
                    if (!(isset($methods[$method]))) {
                        return $this->failed('Error: Invalid method of payment');
                    } else {
                        $this->generate_user_creds();
                        $params = array(
                            'UserRequest' => array(
                                'strCanal' => '8',
                                'strDPI' => $dpi,
                                'strEmail' => $email,
                                'strFechaNacimiento' => $dob,
                                'strMedioPago' => $method,
                                'strMonedaPreferencia' => '320',
                                'strNIT' => $cleaned_nit,
                                'strNoTelefono' => $telno,
                                'strNombreCliente' => $name,
                                'strPassword' => $this->creds['password'],
                                'userName' => $cleaned_nit . $telno,
                            ),
                        );
                        $soap_response = $this->call_soap('addUserPronet', $params);
                        if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
                            $message = isset($soap_response['return']['strResponseMessage'])? $soap_response['return']['strResponseMessage']: 'Request failed';
                            return $this->failed('Error: ' . utf8_encode($message));
                        } else {
                            $customer_rec = array(
                                'email_address' => $email,
                                'mobile_number' => $telno,
                                'dpi' => $dpi,
                                'dpi_front' => $dpi_front,
                                'dpi_back' => $dpi_back,
                                'method_of_payment' => $method,
                                'username' => $params['UserRequest']['userName'],
                                'password' => base64_encode($params['UserRequest']['strPassword']),
                            );
                            $this->db->set($customer_rec)->insert('pronet_customers');
                            $id = $this->db->insert_id();
                            $response = array(
                                'message' => 'Successfully registered customer to Pronet',
                                'pronet_customer_id' => $id,
                            );
                            return $this->success($response);
                        }
                    }
                }
            }
        }
    }

    protected function fetch_getupdate() {
        $params = array('UpdateRequest' => array('strVersion' => '0'));
        $this->call_soap('GetUpdate', $params);
        return $this->client->responseData;
    }

    //being called by cron job
    public function update_billers() {
        $q = $this->db->order_by('timestamp', 'desc')->get('pronet_billers');
        $update = true;
        $getupdate_xml = $this->fetch_getupdate();
        if ($q->num_rows() > 0) {
            $row = $q->row_array();
            if (strlen($getupdate_xml) > 5000) {
                if (md5($getupdate_xml) == $row['hashed_xml']) { $update = false; }
            } else {
                $update = false;
            }
        }
        if ($update) {
            $search = array('S:Envelope', 'S:Body', 'ns2:', 'xmlns:ns2');
            $replace = array('Envelope', 'Body', '', 'xmlns');
            $y = str_replace($search, $replace, $getupdate_xml);
            $xml = simplexml_load_string($y, "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);
            $getupdate = json_decode($json,TRUE);
            if (isset($getupdate['Body']['GetUpdateResponse'])) {
                $getupdate = $getupdate['Body']['GetUpdateResponse'];
            } else {
                return $this->failed('Error: Unable to retrieve billers');
            }
            $uri_image = 'http://app.redpronet.com/Images/';
            $uri_image2 = 'https://app.redpronet.com/Images/';
            $billers = array(
                'logoURL' => $uri_image2,
                'categories' => array(),
                'categories_logo' => array(),
                'biller_groups' => array(),
                'billers' => array(),
            );
            $prepaid = $billers;
            foreach ($getupdate['return']['groupCategories']['groupCategories'] as $group_categories) {
                if (isset($group_categories['visible']) && $group_categories['visible'] == 'false') { //exclude biller category if visible is false
                    continue;
                }
                $icon = str_replace($uri_image, '', $group_categories['icon']);
                $icon = str_replace($uri_image2, '', $icon);
                $billers['categories_logo'][$group_categories['id']] = $icon;
                $billers['categories'][$group_categories['id']] = $group_categories['name'];
                if ($group_categories['id'] == 2) {//capture mobile group category only
                    $prepaid['categories'][$group_categories['id']] = $group_categories['name'];
                }
            }
            foreach ($getupdate['return']['affiliateGroups']['affiliateGroups'] as $affiliate_groups) {
                if (isset($affiliate_groups['visible']) && $affiliate_groups['visible'] == 'false') { //exclude biller group if visible is false
                    continue;
                }
                $logo = str_replace($uri_image, '', $affiliate_groups['uriimage']);
                $logo = str_replace($uri_image2, '', $logo);
                $billers['biller_groups'][$affiliate_groups['id']] = array(
                    'name' => $affiliate_groups['name'],
                    'category' => $affiliate_groups['groupcategory'],
                    'logo' => $logo,
                );
            }
            $all_billers = array();
            $all_billers_sequential = array();
            foreach ($getupdate['return']['affiliates']['affiliates'] as $affiliates) {
                if ((isset($affiliates['visible']) && $affiliates['visible'] == 'false') || !isset($billers['biller_groups'][$affiliates['affiliategroup']]) ) { //exclude biller if visible is false
                    continue;
                }
                $affiliate_code = $affiliates['code']; //this will be the default if not found on field definition
                $billers['billers'][$affiliates['id']] = array(
                    'name' => $affiliates['name'],
                    'biller_group' => $affiliates['affiliategroup'],
                    'group_category' => $billers['biller_groups'][$affiliates['affiliategroup']]['category'],
                    'recharge' => $affiliates['isrecharge'],
                    'fields' => array(),
                    'outputs' => array(),
                );
                $all_billers_sequential[$affiliates['id']] = strtolower($billers['biller_groups'][$affiliates['affiliategroup']]['name']) != strtolower($affiliates['name'])? $billers['biller_groups'][$affiliates['affiliategroup']]['name'] . ' - '. $affiliates['name']: $affiliates['name'];
                if (isset($affiliates['fields']['fields']) && is_array($affiliates['fields']['fields'])) {
                    foreach ($affiliates['fields']['fields'] as $field) {
                        if (!in_array($field['name'], array('strTransactionId', 'strCurrency'))) {
                            if ($field['name'] == 'strCommerceId') {
                                $affiliate_code = $field['value']; //override the definition above
                            } else {
                                $option = array();
                                if (isset($field['options']['options']) && is_array($field['options']['options']) && count($field['options']['options']) > 0) {
                                    foreach ($field['options']['options'] as $options) {
                                        $option[] = array(
                                            'id' => $options['value'],
                                            'name' => $options['name'],
                                        );
                                    }
                                }
                                if (!( ($field['typable'] == 'false' && is_array($field['value']) && count($field['value']) == 0)||($field['name'] == 'strPanType') && $field['value'] == '0')) {
                                    if (is_array($field['value']) && count($field['value']) > 0 && join('', $field['value']) != '') {
                                        $field_value = $field['value'];
                                    } elseif (!(is_array($field['value'])) && trim($field['value']) != '') {
                                        $field_value = $field['value'];
                                    } else {
                                        $field_value = '';
                                    }
                                    //$field_value = ( ($field['name'] == 'strPanType' || $field) ? $field['value']: ''); //old code
                                    //$field_value = $field['value'];
                                    $billers['billers'][$affiliates['id']]['fields'][] = array(
                                        'name' => $field['name'],
                                        'value' => $field_value,
                                        'label' => $field['hint'],
                                        'options' => $option,
                                    );
                                }
                            }
                        }
                    }
                }
                $billers['billers'][$affiliates['id']]['code'] = $affiliate_code;
                $all_billers[$affiliate_code] = $billers['biller_groups'][$affiliates['affiliategroup']]['name'];
                if ($billers['biller_groups'][$affiliates['affiliategroup']]['category'] == '3') {//financial services, include in an object array so genesis method can't be used here
                    $this->billers_financial_services[] = $affiliate_code;
                }
                if (isset($affiliates['outputs']['outputs']) && is_array($affiliates['outputs']['outputs'])) {
                    if (isset($affiliates['outputs']['outputs']['value'])) { //one output field, use directly
                        $output_field = $affiliates['outputs']['outputs'];
                        $tmp = explode(':', $output_field['value']);
                        $label = $output_field['name'] == 'Nombre'? $output_field['name']: $tmp[0];
                        $tmp1 = explode('{', $output_field['value']);
                        $tmp2 = explode('.', $tmp1[1]);
                        $field_name = str_replace('}', '', $tmp2[1]);
                        $group = str_replace('{', '', $tmp2[0]);
                        $billers['billers'][$affiliates['id']]['outputs'][] = array(
                            'label' => $label,
                            'field' => $field_name,
                            'group' => $group,
                        );
                    } else { //multiple output fields, iterate in foreach
                        foreach ($affiliates['outputs']['outputs'] as $output_field) {
                            $tmp = explode(':', $output_field['value']);
                            $label = $output_field['name'] == 'Nombre'? $output_field['name']: $tmp[0];
                            $tmp1 = explode('{', $output_field['value']);
                            $tmp2 = explode('.', $tmp1[1]);
                            $field_name = str_replace('}', '', $tmp2[1]);
                            $group = str_replace('{', '', $tmp2[0]);
                            $billers['billers'][$affiliates['id']]['outputs'][] = array(
                                'label' => $label,
                                'field' => $field_name,
                                'group' => $group,
                            );
                        }
                    }
                }
                if ($affiliates['isrecharge'] == 'true') {
                    $prepaid['biller_groups'][$affiliates['affiliategroup']] = $billers['biller_groups'][$affiliates['affiliategroup']];
                    $prepaid['billers'][$affiliates['id']] = $billers['billers'][$affiliates['id']];
                    unset($billers['billers'][$affiliates['id']]);
                    unset($billers['biller_groups'][$affiliates['affiliategroup']]);
                }
            }
            $payment_methods = array();
            $forbidden_payment_methods = array();
            if (isset($getupdate['return']['payMethodList']['payMethodList']) && is_array($getupdate['return']['payMethodList']['payMethodList'])) {
                foreach ($getupdate['return']['payMethodList']['payMethodList'] as $paymethod) {
                    if (isset($paymethod['estado']) && $paymethod['estado'] == 'A') {
                        if (isset($paymethod['groupCategories']['groupCategories'])) {
                            if (isset($paymethod['groupCategories']['groupCategories']['id'])) {//1-element only
                                $forbidden_payment_methods[$paymethod['id']][] = $paymethod['groupCategories']['groupCategories']['id'];
                            } else {
                                foreach ($paymethod['groupCategories']['groupCategories'] as $cat) {
                                    $forbidden_payment_methods[$paymethod['id']][] = $cat['id'];
                                }
                            }
                        }
                        $payment_methods[$paymethod['id']] = $paymethod['nombre'];
                    }
                }
                if (count($forbidden_payment_methods) == 0) { //empty, use hard-coded one
                    $forbidden_payment_methods['E006'][] = 3; //Forbid use of Genesis Efectivo in Financial services;
                }
            } else {
                $payment_methods = array(
                    'E004' => 'Tarjeta de Crédito',
                    'E006' => 'Génesis Efectivo',
                    'E007' => 'Billetera Akisi',
                );
            }
            $output = array(
                'postpaid' => $billers,
                'prepaid' => $prepaid,
            );
            $insert_data = array(
                'xml' => base64_encode($getupdate_xml),
                'billers' => json_encode($output),
                'method_of_payments' => json_encode($payment_methods),
                'hashed_xml' => md5($getupdate_xml),
                'all_billers' => json_encode($all_billers),
                'billers_sequential' => json_encode($all_billers_sequential),
                'billers_financial_services' => json_encode($this->billers_financial_services),
                'forbidden_payment_methods' => json_encode($forbidden_payment_methods),
            );
            $this->db->set($insert_data)->insert('pronet_billers');
        }
    }

    //include categories, etc.
    public function get_billers($biller_id = null) { //if biller_id is provided, we will only return info for that biller
        $q = $this->db->order_by('timestamp', 'desc')->get('pronet_billers');
        if ($q->num_rows() > 0) {
            $row = $q->row_array();
            $billers = json_decode($row['billers'], true);
            if (isset($biller_id) && $biller_id != null && is_numeric($biller_id)) {
                if (isset($billers['postpaid']['billers'][$biller_id])) {
                    return $this->success($billers['postpaid']['billers'][$biller_id]);
                } elseif (isset($billers['prepaid']['billers'][$biller_id])) {
                    return $this->success($billers['prepaid']['billers'][$biller_id]);
                } else {
                    return $this->failed('Currently unsupported');
                }
            }
            $this->billers = json_decode($row['all_billers'], true);
            $this->billers_sequential = json_decode($row['billers_sequential'], true);
            $this->billers_financial_services = json_decode($row['billers_financial_services'], true);
            $this->method_of_payments = json_decode($row['method_of_payments'], true);
            $this->forbidden_payment_methods = json_decode($row['forbidden_payment_methods'], true);
            $response = $billers;
            $response['method_of_payments'] = $this->method_of_payments;
            $response['forbidden_payment_methods'] = $this->forbidden_payment_methods;
            return $this->success($response);
        } else {
            return $this->failed('Unable to retrieve list of billers');
        }
    }

    public function get_customer_payment_method($pronet_customer_id) {
        if (!(is_numeric($pronet_customer_id) && $pronet_customer_id > 0 && $this->get_pronet_customer_info($pronet_customer_id))) {
            return $this->failed('Error: Invalid customer ID');
        } else {
            $response = array(
                'method' => $this->pronet_customer['method_of_payment'],
            );
            return $this->success($response);
        }
    }

    public function get_payment_methods() {
        return $this->success($this->method_of_payments);
    }

    public function change_payment_method($pronet_customer_id, $method) {
        if (!(is_numeric($pronet_customer_id) && $pronet_customer_id > 0 && $this->get_pronet_customer_info($pronet_customer_id))) {
            return $this->failed('Error: Invalid customer ID');
        } else {
            $this->get_payment_methods();
            $methods = $this->response['ResponseMessage'];
            if (!(isset($methods[$method]))) {
                return $this->failed('Error: Invalid method of payment');
            } else {
                $params = array(
                    'updateChannelPay' => array(
                        'strCanal' => '8',
                        'strMedioPago' => $method,
                        'userName' => $this->pronet_customer['username'],
                    ),
                );
                $soap_response = $this->call_soap('UpdateChannelPay', $params);
                if (!(isset($soap_response['return']['strResponseCode']) && $soap_response['return']['strResponseCode'] == '00')) {
                    return $this->failed('Error: unable to change payment method');
                } else {
                    $this->db->where('id', $pronet_customer_id)->set('method_of_payment', $method)->update('pronet_customers');
                    return $this->success('Successfully updated payment method');
                }
            }
        }
    }

    public function get_all_lookups() {
        $lookups = array();
        $this->get_lookup_countries();
        $lookups['countries'] = $this->response['ResponseMessage'];
        $this->get_lookup_departments();
        $lookups['departments'] = $this->response['ResponseMessage'];
        $this->get_lookup_municipios();
        $lookups['municipios'] = $this->response['ResponseMessage'];
        $this->get_lookup_actividad_economica();
        $lookups['actividad_economica'] = $this->response['ResponseMessage'];
        $this->get_lookup_motivo_reception();
        $lookups['motivo_reception'] = $this->response['ResponseMessage'];
        $this->get_lookup_ocupation();
        $lookups['ocupation'] = $this->response['ResponseMessage'];
        $this->get_lookup_rangos();
        $lookups['rangos'] = $this->response['ResponseMessage'];
        $this->get_lookup_relacion_remitente();
        $lookups['relacion_remitente'] = $this->response['ResponseMessage'];
        return $this->success($lookups);

    }

    public function get_lookup_countries() {
        $q = $this->db->get('pronet_lookups_countries');
        return $this->success(($q->num_rows() > 0)? $q->result_array(): array());
    }

    public function get_lookup_actividad_economica() {
        $q = $this->db->get('pronet_lookups_actividad_economica');
        return $this->success(($q->num_rows() > 0)? $q->result_array(): array());
    }

    public function get_lookup_departments() {
        $q = $this->db->get('pronet_lookups_departments');
        return $this->success(($q->num_rows() > 0)? $q->result_array(): array());
    }

    public function get_lookup_motivo_reception() {
        $q = $this->db->get('pronet_lookups_motivo_reception');
        return $this->success(($q->num_rows() > 0)? $q->result_array(): array());
    }

    public function get_lookup_municipios($department_id = null) {
        if ($department_id !== null && is_numeric($department_id)) {
            $q = $this->db->where('department_id', $department_id)->get('pronet_lookups_municipios');
        } else {
            $q = $this->db->get('pronet_lookups_municipios');
        }
        return $this->success(($q->num_rows() > 0)? $q->result_array(): array());
    }

    public function get_lookup_ocupation() {
        $q = $this->db->get('pronet_lookups_ocupation');
        return $this->success(($q->num_rows() > 0)? $q->result_array(): array());
    }

    public function get_lookup_rangos() {
        $q = $this->db->get('pronet_lookups_rangos');
        return $this->success(($q->num_rows() > 0)? $q->result_array(): array());
    }

    public function get_lookup_relacion_remitente() {
        $q = $this->db->get('pronet_lookups_relacion_remitente');
        return $this->success(($q->num_rows() > 0)? $q->result_array(): array());
    }

}