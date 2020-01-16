<?php

/**
 * Created by Ram Bolista
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('send_mail')){
    function send_mail($message, $email_address, $subject, $attachment = false, $attachment_filename = false) {
        $CI = & get_instance();

        $config = array(
            'mailtype'  => 'html',
            'smtp_host' => settings::$email_recipients['smtp_host'],
            'smtp_user' => settings::$email_recipients['smtp_user'],
            'smtp_pass' => settings::$email_recipients['smtp_pass'],
            'protocol'  => settings::$email_recipients['protocol'],
            'smtp_port' => settings::$email_recipients['smtp_port'],
            'charset'   => 'utf-8',
            'newline'   => "\r\n",
            'smtp_timeout' => '7',
            'smtp_crypto' => 'ssl',
        );
        $CI->load->library('email');
        $CI->email->initialize($config);
        $CI->email->from(settings::$email_recipients['from'], PROGRAM . ' Auto Mailer');
        $CI->email->to($email_address);
        $CI->email->subject($subject);
        $CI->email->message("<html><body><div>{$message}</div></body></html>");
        if ($attachment !== false) {
            if ($attachment_filename !== false) {
                $CI->email->attach($attachment, 'attachment', $attachment_filename);
            } else {
                $CI->email->attach($attachment);
            }
        }
        $CI->email->send();
    }
}

if(!function_exists('send_sms_old')){
     function send_sms_old($mobile_number, $response_msg) {
        //override, for testing
        if (substr($mobile_number, 0,3) == "995" || substr($mobile_number, 0,3) == "922") {
            $mobile_number = "63".$mobile_number;
        } elseif ((substr($mobile_number, 0, 4) == '1639') && strlen ($mobile_number) == 13) {
            $mobile_number = substr($mobile_number, 1, 12);
        }
        $msgs = str_split($response_msg, 160);
        if(strlen ($mobile_number) == 10) $mobile_number = "1".$mobile_number;
        foreach($msgs as $d) {
            $params = array(
                'user'      => settings::$sms_recipients['user'],
                'password'  => settings::$sms_recipients['password'],
                'sender'    => settings::$sms_recipients['sender'],
                'SMSText'   => $d,
                'GSM'       => $mobile_number,
            );
            $send_url = settings::$sms_recipients['send_url'] . http_build_query($params);
            $send_response = file_get_contents($send_url);
        }
        if (!($send_response != '')) {
            return array(
                'success' => FALSE,
                'message' => 'No Response',
            );
        } else {
            if (strstr($send_response, '<status>0</status>') === false) {
                return array(
                    'success' => FALSE,
                    'message' => 'Failed:' . $send_response,
                );
            } else {
                return array(
                    'success' => true,
                    'message' => 'Success:' . $send_response,
                );
            }
        }
        return $send_response;
    }
}

if(!function_exists('send_sms')){
    function send_sms($mobile_number, $response_msg) {
       //override, for testing
       if (substr($mobile_number, 0,3) != "639"  && substr($mobile_number, 0,4) != "1702" ) {
            $CI = & get_instance();
            $curl = curl_init();
            $msg = curl_escape($curl, $response_msg);
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://comunicador.tigo.com.gt/api/http/send_to_contact?msisdn=".$mobile_number."&message=".$msg."&api_key=xb6pNlPzWaeKtqJRxpCdyfVELYIVmBzQ",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => array(
                "Postman-Token: 34036fa2-1957-4e65-ab9a-61424d4ca608",
                "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $data['mobile_number'] = $mobile_number;
            $data['message'] = $response_msg;

            if ($err) {
                $data['response'] = $err;

                $CI->db->insert('tbl_sms_logs', $data);
                return array(
                    'success' => FALSE,
                    'message' => 'Failed:' . $err,
                );
            } else {
                $data['response'] = $response;

                $resp = json_decode($response, true);
                #$data['response_code'] = isset($resp['code'])?$resp['code']:isset($resp['sms_sent'])? $resp['sms_sent']: '';
                #$data['response_message'] = isset($resp['error'])?$resp['error']:isset($resp['sms_message'])? $resp['sms_message']: $response;
                $data['response_code'] = isset($resp['code'])?$resp['code']:$resp['sms_sent'];
                $data['response_message'] = isset($resp['error'])?$resp['error']:$resp['sms_message'];



                $CI->db->insert('tbl_sms_logs', $data);

                return array(
                    'success' => true,
                    'message' => $resp,
                );
            }
       }else{
            $msgs = str_split($response_msg, 160);
            if(strlen ($mobile_number) == 10) $mobile_number = "1".$mobile_number;
            foreach($msgs as $d) {
                $params = array(
                    'user'      => settings::$sms_recipients['user'],
                    'password'  => settings::$sms_recipients['password'],
                    'sender'    => settings::$sms_recipients['sender'],
                    'SMSText'   => $d,
                    'GSM'       => $mobile_number,
                );
                $send_url = settings::$sms_recipients['send_url'] . http_build_query($params);
                $send_response = file_get_contents($send_url);
            }
            if (!($send_response != '')) {
                return array(
                    'success' => FALSE,
                    'message' => 'No Response',
                );
            } else {
                if (strstr($send_response, '<status>0</status>') === false) {
                    return array(
                        'success' => FALSE,
                        'message' => 'Failed:' . $send_response,
                    );
                } else {
                    return array(
                        'success' => true,
                        'message' => 'Success:' . $send_response,
                    );
                }
            }
            return $send_response;
       }
   }
}





// get response via curl get
if(!function_exists('get_curl_result')){
    function get_curl_result($url, $header = null)
    {
        $req = curl_init($url);
        curl_setopt($req, CURLOPT_POST, 0);
        curl_setopt($req, CURLOPT_HTTPGET, 1);
        curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
        if ($header !== null && is_array($header)) { curl_setopt($req, CURLOPT_HTTPHEADER, $header); } else { curl_setopt($req, CURLOPT_HEADER, 0); }
        $resp =curl_exec($req);
        curl_close($req);

        $resp = json_decode($resp, true);
        return $resp;

    }
}

// get response via curl post
if(!function_exists('post_curl_result')){
    function post_curl_result($url, $object_array, $header = null)
    {
        $req = curl_init($url);
        curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($req, CURLOPT_POST, 1 );
        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($req, CURLOPT_SSL_VERIFYHOST, false);
        if ($header !== null && is_array($header)) { curl_setopt($req, CURLOPT_HTTPHEADER, $header); } else { curl_setopt($req, CURLOPT_HEADER, 0); }
        curl_setopt($req, CURLOPT_POSTFIELDS, http_build_query($object_array));
        curl_setopt($req, CURLOPT_CONNECTTIMEOUT ,0);
        $resp =curl_exec($req);
        $httpcode = curl_getinfo($req, CURLINFO_HTTP_CODE);
        curl_close($req);

        $resp = json_decode($resp, true);
        $resp['response_code']=$httpcode;
        return $resp;

    }
}

    /*
     * generate random key
    */
if(!function_exists('generate_random_key')){
    function generate_random_key($random_string_length = 20){
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $string = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $random_string_length; $i++) {
            $string .= $characters[mt_rand(0, $max)];
        }
        return $string;
    }
}

if(!function_exists('check_dpi')){
    function check_dpi($dpi,$selfie,$reverse){
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_PORT => "5000",
        CURLOPT_URL => "http://ezfacegenesis.dyndns.tv:5000/ezface/api/v1.0/match",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n\t\"dpi\":\"$dpi\",\n\t\"selfie\":\"$selfie\",\n\t\"reverse\":\"$reverse\"\n}",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Basic Z2VuZXNpc0RFUzpnVzFIVU50WjU3eHJ2T3BJZXJlenp5cGlL",
            "Content-Type: application/json",
            "Postman-Token: c56a5ebc-0017-4b6e-9928-6108da3c2834",
            "cache-control: no-cache"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return array(
                'success' => false,
                'message' => 'cURL Error #:' . $err,
            );
        } else {
            return array(
                'success' => true,
                'message' => json_decode($response, true),
            );
        }
    }
}
// if you want to send to all customer (to:/topics/all)
if(!function_exists('notify_customer')){
    function notify_customer($title="",$firebase_refid="",$message="",$code=""){
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n      \"notification\": {\n        \"title\": \"{$title}\",\n        \"body\": \"{$message}\" ,\n        \"sound\": \"default\"\n      },\n      \"code\" : \"{$code}\", \n      \"to\": \"{$firebase_refid}\",\n      \"priority\": \"high\",\n      \"restricted_package_name\": \"\"\n}",
        CURLOPT_HTTPHEADER => array(
            "Authorization: key=AIzaSyC9QVThhHtzBFYg2jKd_dINxAUmWVgomqY",
            "Content-Type: application/json",
            "Postman-Token: 8869530e-36c8-487e-b53d-467fb91367b5",
            "cache-control: no-cache"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {

            return array(
                'success' => FALSE,
                'message' => 'Failed:' . $err,
            );
        } else {
            $resp = json_decode($response, true);

            if($resp['success']){
                return array(
                    'success' => true,
                );
            }else{
                return array(
                    'success' => false,
                    'message' => isset($resp['results']['error'])?$resp['results']['error']:"",
                );
            }
        }
    }
}

// get response via curl get
if(!function_exists('get_akisi_fee')){
    function get_akisi_fee($amount)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_PORT => "8080",
        CURLOPT_URL => "https://app.redpronet.com:8443/PronetUtilsApi/getFeeAkisi",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n    \"originalAmount\": {$amount},\n    \"codTransaction\": \"SEND_MONEY_POINT\"\n}",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Postman-Token: 12e2560c-5c58-498d-adf0-1ba64d2e51b0",
            "apikey: d5851c9fe8f3bc16bcfe54211d38076614e0f1e0",
            "cache-control: no-cache"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return array(
                'success' => false,
                'message' => 'cURL Error #:' . $err,
            );
        } else {
            return array(
                'success' => true,
                'message' => json_decode($response, true),
            );
        }

    }
}


