<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Settlement_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        //load database library
        $this->load->database();
    }

    public function debit_customer_account($customer_id="",$balance=0.00,$amount=0.00){
        $this->db->update('tbl_customer', array('balance'=>($balance-$amount)), array('customer_id'=>$customer_id));
        if (!($this->db->affected_rows() > 0)) {
            return false;
        }
        return true;
    }

    public function credit_customer_account($customer_id="",$balance=0.00,$amount=0.00){
        $this->db->update('tbl_customer', array('balance'=>($balance+$amount)), array('customer_id'=>$customer_id));
        if (!($this->db->affected_rows() > 0)) {
            return false;
        }
        return true;
    }

    public function debit_merchant_account($merchant_id="",$prefund_balance=0.00,$amount=0.00,$fee_amount=0.00){
        $this->db->update('tbl_merchant', array('prefund_balance'=>($prefund_balance-$amount)), array('merchant_id'=>$merchant_id));
        if (!($this->db->affected_rows() > 0)) {
            return false;
        }
        return true;
    }

    public function credit_merchant_prefund($merchant_id="",$prefund_balance=0.00,$amount=0.00,$fee_amount=0.00){
        $this->db->update('tbl_merchant', array('prefund_balance'=>($prefund_balance+$amount)), array('merchant_id'=>$merchant_id));
        if (!($this->db->affected_rows() > 0)) {
            return false;
        }
        return true;
    }

    public function credit_merchant_settlement($merchant_id="",$settlement_balance=0.00,$amount=0.00,$fee_amount=0.00){
        $this->db->update('tbl_merchant', array('settlement_balance'=>($settlement_balance+$amount),), array('merchant_id'=>$merchant_id));
        if (!($this->db->affected_rows() > 0)) {
            return false;
        }
        return true;
    }

    public function credit_settlement_account($settlement_id="",$transaction_type="",$fee_amount=0.00,$amount=0.00){
        $update = $this->db->query("UPDATE tbl_settlement SET total_settlement = (total_settlement+({$fee_amount}+{$amount})), settlement_amount=settlement_amount+{$amount}, settlement_fee=settlement_fee+{$fee_amount} WHERE settlement_id='{$settlement_id}' AND transaction_type='{$transaction_type}' ");
        if($update){
            $data = $this->db->get_where('tbl_settlement', array('settlement_id' => $settlement_id))->row_array();
            return array("status"=>true,"running_balance"=>$data['total_settlement']);
        }else{
            return array("status"=>false,"running_balance"=>0);
        }
    }

    public function debit_settlement_account($settlement_id="",$transaction_type="",$fee_amount=0.00,$amount=0.00){
        $update = $this->db->query("UPDATE tbl_settlement SET total_settlement = (total_settlement-({$fee_amount}+{$amount})), settlement_amount=settlement_amount-{$amount}, settlement_fee=settlement_fee-{$fee_amount} WHERE settlement_id='{$settlement_id}' AND transaction_type='{$transaction_type}' ");
        if($update){
            $data = $this->db->get_where('tbl_settlement', array('settlement_id' => $settlement_id))->row_array();
            return array("status"=>true,"running_balance"=>$data['total_settlement']);
        }else{
            return array("status"=>false,"running_balance"=>0);
        }
    }

    public function credit_prefund_account($settlement_id="",$transaction_type="",$fee_amount=0.00,$amount=0.00){
        $update = $this->db->query("UPDATE tbl_settlement SET prefund = (prefund+({$fee_amount}+{$amount})) WHERE settlement_id='{$settlement_id}' AND transaction_type='{$transaction_type}' ");
        if($update){
            $data = $this->db->get_where('tbl_settlement', array('settlement_id' => $settlement_id))->row_array();
            return array("status"=>true,"running_balance"=>$data['prefund']);
        }else{
            return array("status"=>false,"running_balance"=>0);
        }
    }

    public function debit_prefund_account($settlement_id="",$transaction_type="",$fee_amount=0.00,$amount=0.00){
        $update = $this->db->query("UPDATE tbl_settlement SET prefund = (prefund-({$fee_amount}+{$amount})) WHERE settlement_id='{$settlement_id}' AND transaction_type='{$transaction_type}' ");
        if($update){
            $data = $this->db->get_where('tbl_settlement', array('settlement_id' => $settlement_id))->row_array();
            return array("status"=>true,"running_balance"=>$data['prefund']);
        }else{
            return array("status"=>false,"running_balance"=>0);
        }
    }

    
}
?>