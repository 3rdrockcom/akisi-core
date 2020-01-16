<?php
//include("includes/head.php");
$this->load->view('includes/head',$template);

$this->load->view($template['content']);

#include("foot.php");
$this->load->view('includes/foot',$template);


//include footer
$this->load->view('includes/inc/google-analytics',$template);
#include("google-analytics.php"); 
?>