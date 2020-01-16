<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Merchant_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        //load database library
        $this->load->database();
    }

   /*
     * Fetch merchnat data
     */
    public function get_merchant($merchant_reference = ""){
        $query = $this->db->get_where('view_merchant_info', array('merchant_reference' => $merchant_reference));
        return $query->row_array();
    }

    public function add_merchant($cData=array(),$mData=array()){
        $this->db->insert('tblUserInformation', $cData);
        $mData['merchant_reference'] = md5(generate_random_key(14));
        $mData['fk_userid'] = $this->db->insert_id();
        $insert = $this->db->insert('tbl_merchant', $mData);
        if($insert){
            $return = array(
                "status"=>true,
                "merchant_reference"=>$mData['merchant_reference'],
                "merchant_id"=>$this->db->insert_id(),
            );
            
        }else{
            $return = array(
                "status"=>false,
                "merchant_reference"=>"",
                "merchant_id"=>""
            );
        }
        return $return;
    }

    public function get_merchant_transaction($merchant_id,$transaction_type,$transaction_id){
        $this->db->select('*');
        $this->db->from('tbl_merchant_transaction a');
        $this->db->where("fk_merchant_id",$merchant_id);
        if($transaction_type!="") $this->db->where("a.transaction_type",$transaction_type);
        if($transaction_id!="") $this->db->where("a.transaction_number",$transaction_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function check_merchant_email_if_exist($email = ""){
        $query = $this->db->get_where('view_merchant_info', array('email' => $email));
        return $query->row_array();
    }

    public function check_merchant_if_exist($code = ""){
        $querys = $this->db->get_where('view_merchant_info', array('merchant_code' => $code));
        return $querys->row_array();
    }


    public function check_reference($reference_number="",$transaction_type=""){
        $query = $this->db->get_where('tbl_initiate_transaction', array('reference_number' => $reference_number,'transaction_status' => 'PENDING','transaction_type' => $transaction_type));
        return $query->row_array();
    }

    public function check_cashout_code_detail($cashout_code=""){
        $query = $this->db->get_where('tbl_cashout_transaction', array('cashout_code' => $cashout_code,'transaction_status' => '1'));
        $row = $query->row_array();
        $return = array();
        if(!empty($row)){
            $sender = $this->db->get_where('view_customer_info', array('customer_id' => $row['related_fk_customer_id']))->row_array();
            $receiver = $this->db->get_where('tbl_customer_beneficiary', array('beneficiary_id' => $row['fk_beneficiary_id']))->row_array();
            $return['sender_firstname'] = $sender['fname'];
            $return['sender_lastname'] = $sender['lname'];
            $return['sender_mobile'] = $sender['mobile'];
            $return['sender_email'] = $sender['email'];
            $return['receiver_firstname'] = $receiver['fname'];
            $return['receiver_lastname'] = $receiver['lname'];
            $return['receiver_mobile'] = $receiver['mobile'];
            $return['receiver_email'] = $receiver['email'];
            $return['amount'] = $row['amount'];
        }
        return $return;
    }

    public function get_cashout_code_detail($cashout_code=""){
        $query = $this->db->get_where('tbl_cashout_transaction', array('cashout_code' => $cashout_code,'transaction_status' => 'PENDING'));
        return $query->row_array();

    }


}
?>