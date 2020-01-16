<?php
//ENV: LOCAL/STAGING/DEV/PROD
$url = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
define('ENV', file_exists('c:/Windows/hh.exe')? 'LOCAL': (strstr($url,'pronet')? 'DEV': 'PROD'));
define('PRONET_NAMESPACE', '');

define('PROGRAM', 'PRONET');
settings::setEnv(ENV);
#settings::setEnv("LOCAL");
class settings {
    static $core_db;
    static $email_recipients;
    static $sms_recipients;
    public static function setEnv($environment) {
        switch ($environment) {
            case 'LOCAL':
                define('BASE_URL', 'http://localhost/pronet/');
                self::$core_db = array(
                    'host'  => 'localhost',
                    'user'  => 'root',
                    'pass'  => '',
                    'name'  => 'pronet',
                );
                define('MERCHANT_MTID', '65626');
                define('MERCHANT_USER', 'mlocmtusr01');
                define('MERCHANT_PASSWORD', '1h6W8C6H20V4');
                define('PRONET_BILLPAY_URL', 'http://52.73.24.10:8080/pronetElectronicWS/PronetWebPayWS?wsdl');
                define('PRONET_REMITTANCE_URL', 'http://52.73.24.10:8080/pronetwebpayws/PronetWebPayWS?wsdl');
                break;
            case 'STAGING':
            case 'DEV':
                define('BASE_URL', 'http://3.84.84.97/');
                self::$core_db = array(
                    'host'  => '172.16.50.27',
                    'user'  => 'webusr',
                    'pass'  => 'efHBxdcV',
                    'name'  => 'pronet',
                );
                define('PRONET_BILLPAY_URL', 'https://app.redpronet.com:8443/pronetElectronicWS/PronetWebPayWS?wsdl');
                define('PRONET_REMITTANCE_URL', 'https://app.redpronet.com:8443/pronetwebpayws/PronetWebPayWS?wsdl');
                break;
            case 'PROD':
                define('BASE_URL', 'http://3.84.84.97/');
                self::$core_db = array(
                    'host'  => '172.16.50.27',
                    'user'  => 'webusr',
                    'pass'  => 'efHBxdcV',
                    'name'  => 'pronet',
                );
                define('PRONET_BILLPAY_URL', 'https://app.redpronet.com:8443/pronetElectronicWS/PronetWebPayWS?wsdl');
                define('PRONET_REMITTANCE_URL', 'https://app.redpronet.com:8443/pronetwebpayws/PronetWebPayWS?wsdl');
                break;
        }
        self::$email_recipients = array(
            'from'      => 'noreply@akisiwallet.com',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_user' => 'noreply@akisiwallet.com',
            'smtp_pass' => '10reB2017',
            'protocol'  => 'smtp',
            'smtp_port' => '465',
        );
        self::$sms_recipients = array(
            'user'      => 'PyxPayments',
            'password'  => 'Bahamas1',
            'sender'    => 'Akisi SMS',
            'send_url'  => 'http://api2.infobip.com/api/v3/sendsms/plain?',
        );
    }

}