<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * @Author: Robert Ram Bolista
 */

class Configuration_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		
	}


	/**
	* Function that Retrieve all Menus Category in particular Role.
	* @param $fk_syscatid INTEGER - category of menu
	* @param $roleid INTEGER - the user role. Also known as the usertype.
	* @param $userid INTEGER - user unique identity
	* @return array - result data of all menus.
	*/
	function load_menus($fk_syscatid='',$roleid='',$userid=''){
	$menu_seq = array();
	/** HOME */
	$arraymenus = array(

			//'title' => "Messaging",
			'title' => "Dashboard",
			'url'   => $this->ram_encryption->encode(site_url("main/menus/dashboard/YES/YES/YES/YES/0/"),$this->session->userdata('random')),
			'icon'  => "fa-home"
	);
	array_push($menu_seq,$arraymenus);
	$q = $this->db->query("CALL prc_get_SystemRoleMenu('','$roleid','$userid')");
	foreach($q->result() as $row){
	  $arraymenus = array();
	     /** START OF (if menu has sub menu) */
	     $arraymenus = array(
				'title' => $row->title,
				'url'   => "#",
				'icon'  => $row->icon,
				'sub'   => array()
	     );
	     $arraymenus['sub'] = $this->load_submenus($row->fk_syscatid,$roleid,$userid);
	     /** END OF (if menu has sub menu) */
	  array_push($menu_seq,$arraymenus);
	}
	return $menu_seq;
	}

	/**
	* Function that Retrieve all Sub Menus in particular Role.
	* @param $fk_syscatid INTEGER - category of menu
	* @param $roleid INTEGER - the user role. Also known as the usertype.
	* @param $userid INTEGER - user unique identity
	* @return array - result data of all menus.
	*/
	function load_submenus($fk_syscatid='',$roleid='',$userid=''){
		$submenus = array();
		$q = $this->db->query("CALL prc_get_SystemRoleMenu('$fk_syscatid','$roleid','$userid')");
		$ss=0;
		foreach($q->result() as $row){
			$ss++;
			$link = $row->link;
			$submenu = array(
	           'title' => $row->title,
			   'url' => ($row->link!="#"?$this->ram_encryption->encode(site_url("main/menus/$row->link/$row->can_read/$row->can_add/$row->can_edit/$row->can_delete/$row->fk_menucat_id/$row->view_folder"),$this->session->userdata('random')):"#"),
			   #'url' => ($row->link!="#"?site_url("main/menus/$row->link/$row->can_read/$row->can_add/$row->can_edit/$row->can_delete/$row->fk_menucat_id/$row->view_folder"):"#"),
	           'icon' => $row->icon
	        );
	        array_push($submenus,$submenu);
		}
		return $submenus;
	}

	/**
	* Function that return all the Module access of specific User
	* @param $userid INTEGER - user unique identity
	* @return array - result data of all user access modules.
	*/
	function load_user_role($userid=''){
	$q = $this->db->query("CALL prc_get_UserRole('$userid')");
	return $q;
	}

  	/**
  	 * [get_sub_menus description]
  	 * @param  string $menucat_id unique identity of Menu Categry
  	 * @return query
  	 */
  	function get_sub_menus($menucat_id=''){
  		$q = $this->db->query("SELECT sub_menuid,link,title,`status`,arranged,icon,fk_template_id,view_folder,menu_label FROM `tblSystemMenuCategory` a 
INNER JOIN `tblSystemMenuSub` b ON b.`fk_menuid`=a.`fk_menuid` AND `status`='SHOW' AND menucat_id='{$menucat_id}' ORDER BY arranged");
		return $q;

  	}
}

/* End of file configuration_model.php */
/* Location: ./application/models/configuration_model.php */
