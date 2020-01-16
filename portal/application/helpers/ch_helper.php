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

if(!function_exists('send_sms')){
     function send_sms($mobile_number, $response_msg) {
        //override, for testing
        if (substr($mobile_number, 0,4) == "1999" || substr($mobile_number, 0,4) == "1888") {
            $mobile_number = "639432788941";
        } elseif ((substr($mobile_number, 0, 4) == '1639') && strlen ($mobile_number) == 13) {
            $mobile_number = substr($mobile_number, 1, 12);
        }
        $msgs = str_split($response_msg, 160);
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


