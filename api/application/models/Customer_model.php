<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Customer_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        //load database library
        $this->load->database();
    }

    /*
     * Fetch user by mobile
     */
    public function get_user_bymobile($mobile = ""){
        $query = $this->db->get_where('tblUserInformation', array('mobile' => $mobile));
        return $query->row_array();
    }

    /*
     * check if mobile number is validated
     */
    public function check_mobile_if_validate($mobile = ""){
        $query = $this->db->get_where('tblUserInformation', array('mobile' => $mobile));
        $row = $query->row_array();
        if(!empty($row)){
            $v = $this->db->get_where('tbl_user_verification', array('fk_userid' => $row['userid'],'verification_type' => 'SMS','verification_status' => 'VERIFIED'));
            $vrow = $v->row_array();
            if(!empty($vrow)) return true;
            else return false;
        }else{
            return false;
        }

    }

    /*
     * Fetch user by mobile
     */
    public function check_email_if_exist($email = ""){
        $query = $this->db->get_where('view_customer_info', array('email' => $email,'is_finish_registration'=>"1"));
        return $query->row_array();
    }

    public function process_mobile_registration($mobile=""){
        $uData['mobile']     = $mobile;
        $uData['userno']     = $mobile;
        $insert = $this->db->insert('tblUserInformation', $uData);
        if($insert){
            $userid = $this->db->insert_id();
            $data['fk_userid'] = $userid;
            $data['verification_type'] = "SMS";
            $data['verification_status'] = "PENDING";

            $data['verification_code'] = rand ( 10000 , 99999 );
            //generate_random_key(5);
            $data['created_date'] = date("Y-m-d H:i:s");
            $insert = $this->db->insert('tbl_user_verification', $data);

            #$sms_message = "Your verification code for Akisi is ".$data['verification_code']."."; 
            $sms_message = "Tu codigo de verificacion para Akisi es: ".$data['verification_code']."."; 
            send_sms($mobile,$sms_message); 
            return true;
        }else{
            return false;
        }
    }

    public function verify_mobile_registration($verification_code = "",$userid = ""){
        $data['fk_userid']           = $userid;
        $data['verification_type']   = 'SMS';
        $data['verification_code']   = $verification_code;
        $data['verification_status'] = 'PENDING';
        $query = $this->db->get_where('tbl_user_verification', $data);
        $check = $query->row_array();
        if(!empty($check)){
            $status['verification_status'] = "VERIFIED";
            $data['fk_userid'] = $userid;
            $data['verification_type'] = "SMS";
            $data['verification_status'] = "PENDING";
            $this->db->update('tbl_user_verification', $status, $data);
            return true;
        }else{
            return false;
        }
    }
    
    public function resend_mobile_verification($mobile=""){
        $user = $this->get_user_bymobile($mobile);

        $status['verification_status'] = "EXPIRED";
        $data['fk_userid'] = $user['userid'];
        $data['verification_type'] = "SMS";
        $data['verification_status'] = "PENDING";
        $this->db->update('tbl_user_verification', $status, $data);

        $data['verification_code'] = generate_random_key(5);
        $data['created_date'] = date("Y-m-d H:i:s");
        $insert = $this->db->insert('tbl_user_verification', $data);

        #$sms_message = "Your verification code for Akisi is ".$data['verification_code'].".";  
        $sms_message = "Tu codigo de verificacion para Akisi es: ".$data['verification_code'].".";
        send_sms($mobile,$sms_message); 
        return true;
    }


    /**
     * Update data in customer table and user table
     */
    public function update_customer_registration($data=array()){
        $return = array();
        $uData              = array();
        $uData['fname']     = $data['fname'];
        $uData['lname']     = $data['lname'];
        $uData['slname']    = $data['slname'];
        $uData['email']     = $data['email'];
        $uData['mname']     = $data['mname'];
        $uData['mobile']    = $data['mobile'];

        $user = $this->get_user_bymobile($data['mobile']);


        $cData                       = array();
        $cData['customer_number']    = generate_random_key(7);
        $cData['customer_reference'] = md5(generate_random_key(5));
        $cData['fk_userid']          = $user['userid'];

        $this->db->update('tblUserInformation', $uData, array('userid'=>$user['userid']));
        $insert = $this->db->insert('tbl_customer', $cData);
        if($insert){
            $return = array(
                "status"=>true,
                "customer_reference"=>$cData['customer_reference'],
                "customer_id"=>$this->db->insert_id(),
            );
            
        }else{
            $return = array(
                "status"=>false,
                "customer_reference"=>"",
                "customer_id"=>""
            );
        }
        return $return;
    }

    /*
     * Fetch customer data
     */
    public function get_customer($customer_reference = ""){
        $query = $this->db->get_where('view_customer_info', array('customer_reference' => $customer_reference));
        return $query->row_array();
    }

    public function change_password($data,$userid){
        $old_password = md5($data['old_password']);
        $new_password = md5($data['new_password']);
        $check_oldpass = $this->db->get_where('tblUserInfo', array('fk_userid' => $userid,'password'=>$old_password))->row_array();
        if(!empty($check_oldpass)){
            $update = $this->db->update('tblUserInfo', array('password'=>$new_password,'reset_password'=>"0"), array('fk_userid'=>$userid));
            return true;
        }else{
            return false;
        }
    }

    /*
     * Fetch customer data
     */
    public function get_customer_byid($customer_id = ""){
        $query = $this->db->get_where('view_customer_info', array('customer_id' => $customer_id));
        return $query->row_array();
    }

    public function check_if_dpi_exist($dpi_number = ""){
        $query = $this->db->get_where('view_customer_info', array('dpi' => $dpi_number));
        return $query->row_array();
    }

    

    /*
     * Fetch merchant data
     */
    public function get_merchant($merchant_reference = ""){
        $query = $this->db->get_where('tbl_merchant', array('merchant_reference' => $merchant_reference));
        return $query->row_array();
    }

    /*
     * Fetch customer data
     */
    public function get_customer_byuserid($userid = ""){
        $query = $this->db->get_where('view_customer_info', array('userid' => $userid));
        return $query->row_array();
    }

    public function check_ifuser_is_verified($userid = ""){
        $query = $this->db->get_where('tbl_user_verification', array('fk_userid' => $userid,'verification_status'=>"VERIFIED","verification_type"=>"SMS"));
        return $query->row_array();
    }

    /*
     * Fetch security question data
     */
    public function get_question($question_id = ""){
        if($question_id!="") $this->db->where("question_id",$question_id);
        $query = $this->db->get('tbl_security_question');
        return $query->result_array();
    }

    public function insert_customer_security_question($data=array()){
        $insert = $this->db->replace('tbl_customer_security_question', $data);
        if($insert){
            return true;
        }else{
            return false;
        }
    }

    public function delete_customer_security_question($data=array()){
        $this->db->where($data);
        $delete = $this->db->delete('tbl_customer_security_question');
        if($delete){
            return true;
        }else{
            return false;
        }
    }

    public function get_customer_question($customer_id="",$question_id=""){
        $this->db->select('*');
        $this->db->from('tbl_customer_security_question a');
        $this->db->join('tbl_security_question b','b.question_id=a.fk_question_id');
        $this->db->where("fk_customer_id",$customer_id);
        if($question_id!="") $this->db->where("a.fk_question_id",$question_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function create_password($data=array()){
        $customer = $this->get_customer($data['customer_reference']);
        $password = md5($data['new_password']);
        if(!empty($customer)){
            
            $this->db->update('tblUserInfo', array('password'=>$password), array('fk_userid'=>$customer['userid']));
            return true;
        }else{
            return false;
        }
    }

    public function insert_customer_file($data=array()){
        if($data['file_type']=="PROFILE"){
            $data['is_primary'] = "1";
            $this->db->update('tbl_customer_file', array('is_primary'=>'0'), array('fk_customer_id'=>$data['fk_customer_id']));
        }
        $insert = $this->db->insert('tbl_customer_file', $data);
        if($insert){
            $insert_id = $this->db->insert_id();
            return $insert_id;
        }else{
            return false;
        }
    }
    
    public function get_customer_file($customer_id="",$file_type="",$is_primary=""){
        $this->db->select('*');
        $this->db->from('tbl_customer_file');
        $this->db->where("fk_customer_id",$customer_id);
        if($file_type!="") $this->db->where("file_type",$file_type);
        if($is_primary!="") $this->db->where("is_primary",$is_primary);
        $query = $this->db->get();
        return $query->result_array();
    }

    /*
     * Fetch customer beneficiary
     */
    public function get_beneficiary($customer_id = "",$beneficiary_id=""){
        #$query = $this->db->get_where('tbl_customer_beneficiary', array('fk_customer_id' => $customer_id));
        $this->db->select('*');
        $this->db->from('tbl_customer_beneficiary');
        $this->db->where("fk_customer_id",$customer_id);
        if($beneficiary_id!="") $this->db->where("beneficiary_id",$beneficiary_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function check_beneficiary($customer_id = "",$mobile=""){
        $query = $this->db->get_where('tbl_customer_beneficiary', array('fk_customer_id' => $customer_id,"mobile"=>$mobile));
        return $query->row_array();
    }

    

    public function search_beneficiary($customer_id = "",$search=""){
        $query = $this->db->query("SELECT * FROM tbl_customer_beneficiary a
		WHERE a.fk_customer_id='{$customer_id}' AND (a.mobile LIKE '%{$search}%' OR a.slname LIKE '%{$search}%' OR a.email LIKE '%{$search}%' OR a.lname LIKE '%{$search}%' OR a.fname LIKE '%{$search}%') ");
		return $query->result_array();
    }

    /*
     * Fetch customer by mobile
     */
    public function search_customer_bymobile($mobile = ""){
        $query = $this->db->get_where('view_customer_info', array('mobile' => $mobile,'is_finish_registration' => '1'));
        return $query->row_array();
    }

    public function search_customer_by_category($mobile = "",$dpi = ""){
        #customer_reference,fname,lname,mname,slname,bdate,email,address,dpi_url,selfie_url
        $this->db->select('customer_reference,fname,lname,mname,slname,bdate,email,mobile,dpi,address,dpi_url,selfie_url');
        $this->db->from('view_customer_info a');
        $this->db->where("is_finish_registration","1");
        if($mobile!="") $this->db->where("a.mobile",$mobile);
        if($dpi!="") $this->db->where("a.dpi",$dpi);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function delete_beneficiary($data=array()){
        $this->db->where($data);
        $delete = $this->db->delete('tbl_customer_beneficiary');
        if($delete){
            return true;
        }else{
            return false;
        }
    }

    public function add_beneficiary($data=array()){
        $return = array();
        $insert = $this->db->insert('tbl_customer_beneficiary', $data);
        if($insert){
            $return = array(
                "status"=>true,
                "beneficiary_id"=>$this->db->insert_id(),
            );
            
        }else{
            $return = array(
                "status"=>false,
                "beneficiary_id"=>""
            );
        }
        return $return;
    }

    public function get_beneficiary_by_id($beneficiary_id=""){
        $query = $this->db->get_where('tbl_customer_beneficiary', array('beneficiary_id' => $beneficiary_id));
        return $query->row_array();
    }

    public function get_beneficiary_by_reference($reference=""){
        $query = $this->db->get_where('tbl_customer_beneficiary', array('customer_reference' => $reference));
        return $query->row_array();
    }


    public function insert_beneficiary_file($data=array()){
        $data['is_primary'] = "1";
        $this->db->update('tbl_customer_beneficiary_file', array('is_primary'=>'0'), array('fk_beneficiary_id'=>$data['fk_beneficiary_id']));

        $insert = $this->db->insert('tbl_customer_beneficiary_file', $data);
        if($insert){
            $insert_id = $this->db->insert_id();
            $this->db->update('tbl_customer_beneficiary', array('image_url'=>$data['filepath']), array('beneficiary_id'=>$data['fk_beneficiary_id']));
            return $insert_id;
        }else{
            return false;
        }
    }

    public function get_customer_transaction($customer_id="",$transaction_id="",$limit="5",$sort="desc",$transaction_type=""){
        if($limit=="")$limit="5";
        if($sort=="")$sort="desc";
        $this->db->select('*');
        $this->db->from('view_customer_transaction');
        $this->db->where("fk_customer_id",$customer_id);
        if($transaction_type!="")$this->db->where("transaction_type",$transaction_type);
        if($transaction_id!="") $this->db->where("reference_number",$transaction_id);
        $this->db->order_by('transaction_date', $sort);
        if($limit!="ALL") $this->db->limit($limit);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_customer_biller($customer_id="",$biller_id="",$account_number="",$category_id=""){
        $this->db->select('*');
        $this->db->from('tbl_customer_biller');
        $this->db->where("fk_customer_id",$customer_id);
        if($biller_id!="") $this->db->where("biller_id",$biller_id);
        if($account_number!="") $this->db->where("account_number",$account_number);
        if($category_id!="") $this->db->where("category_id",$category_id);
        $query = $this->db->get();
        return $query->result_array();
    }


    public function get_customer_topup($customer_id="",$biller_id="",$mobile_number=""){
        $this->db->select('*');
        $this->db->from('tbl_customer_topup');
        $this->db->where("fk_customer_id",$customer_id);
        if($biller_id!="") $this->db->where("biller_id",$biller_id);
        if($mobile_number!="") $this->db->where("mobile_number",$mobile_number);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function add_customer_biller($data=array()){
        $insert = $this->db->insert('tbl_customer_biller', $data);
        return $insert;
    }

    public function add_customer_topup($data=array()){
        $insert = $this->db->insert('tbl_customer_topup', $data);
        return $insert;
    }

    public function update_info_1($uData=array()){
        $update = $this->db->update('tblUserInformation', $uData, array('userid'=>$uData['userid']));
        if (!$update) {
            return false;
        }else{
            return true;
        }
    }

    public function update_info_2($uData=array()){
        $update = $this->db->update('tbl_customer', $uData, array('customer_id'=>$uData['customer_id']));
        if (!$update) {
            return false;
        }else{
            return true;
        }
    }

    public function check_bill_reference($bill_reference){
        $return = array(
            "status"=>false,
            "transaction_type"=>"",
            "data"=>""
        );

        $ctransaction = $this->db->get_where('tbl_customer_transaction', array('bill_reference' => $bill_reference))->row_array();
        if(!empty($ctransaction)){
            $billpay = $this->db->get_where('tbl_billpay_transaction', array('bill_reference' => $bill_reference))->row_array();
            if(!empty($billpay)){
                $return = array(
                    "status"=>true,
                    "transaction_type"=>"BILLPAY",
                    "data"=>$billpay
                );
                return $return;
            }
            $topup = $this->db->get_where('tbl_topup_transaction', array('bill_reference' => $bill_reference))->row_array();
            if(!empty($topup)){
                $return = array(
                    "status"=>true,
                    "transaction_type"=>"TOPUP",
                    "data"=>$topup
                );
                return $return;
            }
        }
        
        return $return;
    }

    public function check_bill_reminder(){
        $day_of_month = date("j");
        $time_of_day_from = date("H:i");
        $time_of_day_to = date("H:i",strtotime(date("H:i")."+5 minutes"));

        $query = $this->db->query("SELECT a.*,b.* FROM tbl_customer_biller a INNER JOIN view_customer_info b ON b.customer_id=a.fk_customer_id
		WHERE reminder='1' AND day_of_month='{$day_of_month}' AND `time` BETWEEN '{$time_of_day_from}' AND '{$time_of_day_to}' ");
		return $query->result_array();
    }


}
?>