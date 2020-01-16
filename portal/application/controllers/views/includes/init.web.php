<?php

// if (!session_id()) session_start();
require_once 'init.php';

//CONFIGURATION for SmartAdmin UI

//ribbon breadcrumbs config
//array("Display Name" => "URL");
$breadcrumbs = array(
    //"Home" => ""
);

/*navigation array config

ex:
"dashboard" => array(
    "title" => "Display Title",
    "url" => "http://yoururl.com",
    "url_target" => "_self",
    "icon" => "fa-home",
    "label_htm" => "<span>Add your custom label/badge html here</span>",
    "sub" => array() //contains array of sub items with the same format as the parent
)

*/
/*
$page_nav = array(
    "customer" => array(
        "title" => "Customer Info",
        "icon" => "fa-user",
        "url" => site_url("main")
        
        "sub" => array(
            "analytics" => array(
                "title" => "Analytics Dashboard",
                "url" => site_url("main")
            ),
            "marketing" => array(
                "title" => "Marketing Dashboard",
                "url" => site_url("main/customers")
            ),
            "social" => array(
                "title" => "Social Wall",
                "url" => APP_URL."/dashboard-social.php"
            )
        )
        
    ),
    "loan_info" => array(
        "title" => "Loan Info",
        "icon" => "fa-money",
        "sub" => array(
            "line_credit" => array(
                "title" => "Line of Credit",
                "url" => site_url("main")
            ),
            "active_laon" => array(
                "title" => "Active Loans",
                "url" => site_url("main")
            ),
            "releases" => array(
                "title" => "Releases",
                "url" => site_url("main")
            )
        )
        
    ),
    "loan_setting" => array(
        "title" => "Loan Settings",
        "icon" => "fa-gear",
        "sub" => array(
            "fees" => array(
                'title' => 'Fees',
                'icon' => 'fa-dollar ',
                'sub' => array(
                    'interest' => array(
                        'title' => 'Interest',
                        'url' => site_url("main/interest")
                    ),
                    'Fee' => array(
                        'title' => 'Fee',
                        'url' => site_url("main/fee")
                    )
                )
            ),
            "cycle" => array(
                "title" => "Payment Cycle",
                "icon" => "fa-recycle",
                'url' => site_url("main/pay_cycle")
            ),
        )
    ),
    "transaction_history" => array(
        "title" => "Transaction History",
        "icon" => "fa-history",
        "sub" => array(
            "loan_history" => array(
                "title" => "Loan History",
                "url" => site_url("main")
            ),
            "payment_history" => array(
                "title" => "Payment History",
                "url" => site_url("main")
            )
        )
    ),
);
*/
//configuration variables
#$page_title = "";
$page_css = array();
$no_main_header = false; //set true for lock.php and login.php
$page_body_prop = array(); //optional properties for <body>
$page_html_prop = array(); //optional properties for <html>
?>