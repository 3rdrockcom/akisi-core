<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Login extends CI_Controller {

	public function index()
	{

      if(!islogged()){
          if(isset($_POST['function_ctrl'])){
              $this->checkPosts();
          }else{
              loadloginform();
          }
      }else{
      redirect("main", "location");
      }
	}

	public function forgotpassword(){
		if(!islogged()){
			loadforgotpassword();
		}else{
			redirect("main", "location");
		}
	}

    private function checkPosts(){
        switch($this->input->post('function_ctrl')){
            case 'login': $this->validate();
        }
    }

    private function validate(){

        $this->load->model("user_model");
        $isvalidate = !$this->user_model->validate() ? 0 : 1;
        $resulta = !(bool)$isvalidate ? "Invalid username and password!" : "";


        echo "<user>
                 <result>{$isvalidate}</result>
                 <message>{$resulta}</message>
               </user>";
    }
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */