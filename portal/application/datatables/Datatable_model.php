<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class Datatable_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		//Do your magic here
	}

	/**
	 * retrieve data of user via datatable
	 * @return json
	 */
	function user_list(){
		$results="";
		$this->datatables
		->select('a.userno as userno,fn_user_fullname_get(userid) as name,b.status as status,a.userid')
		->from('tblUserInformation a')
		->join('tblUserInfo b','b.fk_userid=a.userid','left');
		$results = $this->datatables->generate('json','UTF-8');
		return $results;
	}

	/**
	 * retrieve data of user via datatable
	 * @return json
	 */
	function role_list(){
		$results="";
		$this->datatables
		->select('code,description,roleid')
		->from('tblSystemRole');
		$results = $this->datatables->generate('json','UTF-8');
		return $results;
	}

	/**
	 * retrieve data of category via datatable
	 * @return json
	 */
	function category_ist(){
		$results="";
		$this->datatables
		->select('description,arranged,syscat_id')
		->from('tblSystemCategory');
		$results = $this->datatables->generate('json','UTF-8');
		return $results;
	}

	/**
	 * retrieve data of menu via datatable
	 * @return json
	 */
	function menu_list(){
		$results="";
		$this->datatables
		->select('title,`status`,a.arranged,description,menuid,comments,a.icon')
		->from('tblSystemMenu a')
		->join('tblSystemMenuCategory b','b.fk_menuid=a.menuid','left')
		->join('tblSystemCategory c','c.syscat_id=b.fk_syscatid','left');
		$results = $this->datatables->generate('json','UTF-8');
		return $results;
	}

}

/* End of file datatable_model.php */
/* Location: ./application/models/datatables/datatable_model.php */