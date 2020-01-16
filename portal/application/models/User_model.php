<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends CI_Model {
    function __construct(){
      parent::__construct();
    }

    function validate(){
        $uname = $this->input->post("fusername");
        $upass = $this->input->post("fpassword");

        $password = md5($upass);
        $where = " WHERE password='$password' AND username='$uname' AND status='ACTIVE' ";
        #$query = $this->db->query("SELECT * FROM tblUserInfo $where ");
        $query = $this->db->query("CALL prc_get_UserInfo_login(\"$where\");");

        if($query->num_rows()==1){
            $row = $query->row(0);
            $qrole = $this->db->query("SELECT GROUP_CONCAT(DISTINCT fk_roleid) as role FROM tblUserRole c WHERE c.fk_userid='{$row->fk_userid}';")->row(0);
            $sess = array(
                 'username'  => $uname,
                 'userno'  => $row->userno,
                 'logged_in' => TRUE,
                 'fullname' => $row->FULLNAME,
                 'userid' => $row->fk_userid,
                 'role' => $qrole->role,
                 'random' => random_string('alnum',24)
            );


            /** Load first the main info */
            $this->session->set_userdata($sess);
            /** Delete first the existing session for this user */
            if($this->session->userdata("userid")) $this->db->query("delete from tblSession WHERE fk_userid='".$this->session->userdata("userid")."' AND timestamp<>'".$this->session->userdata("__ci_last_regenerate")."'");
            /** Update session for the current user */
            $this->db->query($this->db->update_string("tblSession", array("fk_userid"=>$this->session->userdata("userid"),"fullname" => $this->session->userdata("fullname")), array("timestamp" => $this->session->userdata("__ci_last_regenerate"))));
            /** Update information of the current user */
            $this->db->query($this->db->update_string("tblUserInfo", array("ipadd"=>$this->input->ip_address()), array("username" => "$uname")));

            return true;
        }else{
          return false;
        }
    }

}

/* End of file user.php */
/* Location: ./application/models/user.php */