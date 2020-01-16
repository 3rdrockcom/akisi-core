<?php
/**
 * @Author: Robert Ram Bolista
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Manage_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		//Do your magic here
	}

	/**
	 * [get_role_list return all data or specific data of role (tblSystemRole)]
	 * @param  Integer $role_id - unique identity of a role
	 * @return array result
	 */
	public function get_role_list($role_id=''){
		$where="";
		if($role_id) $where=" WHERE roleid='{$role_id}'";
		$query = $this->db->query("CALL prc_get_SystemRole(\"$where\")");
		return $query->result();
	}

	/**
	 * [get_user_list return all data or specific data of user that active only (tblUserInfo/tblUserInformation)]
	 * @param  interger $userid - unique identity of a user
	 * @return array result
	 */
	public function get_user_list($userid=''){
		$where=" WHERE `status`='ACTIVE' ";
		if($userid) $where.=" AND fk_userid='{$userid}'";
		$query = $this->db->query("CALL prc_get_UserInfo_login(\"$where\")");
		return $query->result();
	}

	/**
	 * [get_user_role get the list of role under a particular user in tblUserRole]
	 * @param  Integer $userid - unique identity of a user
	 * @return array result
	 */
	public function get_user_role($userid=''){
		$query = $this->db->query("SELECT id,fk_userid,fk_roleid FROM tblUserRole WHERE fk_userid='{$userid}'");
		return $query->result();
	}

	/**
	 * [get_assigned_user - get the list of user assigned in this particular role]
	 * @param  int $roleid unique identity of a role
	 * @return array result
	 */
	public function get_assigned_user($roleid=''){
		$query = $this->db->query("SELECT GROUP_CONCAT(IFNULL(username,'')) AS assigned_user FROM tblUserRole a
		LEFT JOIN tblUserInfo b ON b.fk_userid=a.fk_userid
		WHERE a.fk_roleid='{$roleid}' AND b.status='ACTIVE'");
		return $query->result();
	}

	/**
	 * [get_user_in_role get the list of user under a particular role in tblUserRole]
	 * @param  Integer $userid - unique identity of a user
	 * @return array result
	 */
	public function get_user_in_role($roleid=''){
		$query = $this->db->query("SELECT id,fk_userid,fk_roleid FROM tblUserRole WHERE fk_roleid='{$roleid}'");
		return $query->result();
	}

	/**
	 * [get_user_info return all data or specific data of user (tblUserInformation and tblUserInfo)]
	 * @param  Integer $userid - unique identity of a user
	 * @return array result
	 */
	function get_user_info($userid=''){
		$where=" WHERE a.fk_userid='{$userid}'";
		$query = $this->db->query("CALL prc_get_UserInfo(\"$where\")");
		return $query->result();
	}

	/**
	 * [getcategoryinfo return a specific data in tblSystemMenuCategory per syscat_id]
	 * @param  Integer $catid - unique identity of a category
	 * @return array result
	 */
	public function getcategoryinfo($catid='')
	{
		$query = $this->db->query("SELECT a.syscat_id,a.description,a.arranged,a.icon,GROUP_CONCAT(c.title SEPARATOR ', ') AS menuassigned FROM tblSystemCategory a
		INNER JOIN tblSystemMenuCategory b ON b.fk_syscatid=a.syscat_id
		INNER JOIN tblSystemMenu c ON c.menuid=b.fk_menuid WHERE syscat_id='{$catid}'");
		return $query->result();
	}

	/**
	 * [getmenulist get the list of menu that is not under a particular category in tblSystemMenuCategory]
	 * @param  Integer $catid - unique identity of a category
	 * @return array result
	 */
	public function getmenulist($catid=''){
		$query = $this->db->query("SELECT menuid,title,link,`status`,arranged,icon FROM tblSystemMenu WHERE `status`='SHOW'
			AND ( menuid IN (SELECT fk_menuid FROM tblSystemMenuCategory WHERE fk_syscatid='{$catid}') OR
				  menuid NOT IN (SELECT fk_menuid FROM tblSystemMenuCategory WHERE fk_syscatid<>'{$catid}')
				)
			ORDER BY title");
		return $query->result();
	}

	/**
	 * [menuscategory get the list of menu under a particular category in tblSystemMenuCategory]
	 * @param  Integer $catid - unique identity of a category
	 * @return array result
	 */
	public function menuscategory($catid=''){
		$query = $this->db->query("SELECT menucat_id,fk_syscatid,fk_menuid FROM tblSystemMenuCategory WHERE fk_syscatid='{$catid}'");
		return $query->result();
	}

	/**
	 * [get_menu_info return a specific data in tblSystemMenu per menuid]
	 * @param  Integer $menu_id - unique identity of a menu
	 * @return array result
	 */
	public function get_menu_info($menu_id='')
	{
		$query = $this->db->query("SELECT a.menuid,a.title,a.status,a.arranged,a.comments,a.icon,c.description AS assigned_category,c.syscat_id AS category FROM tblSystemMenu a
		LEFT JOIN tblSystemMenuCategory b ON b.fk_menuid=a.menuid
		LEFT JOIN tblSystemCategory c ON c.syscat_id=b.fk_syscatid
		WHERE menuid='{$menu_id}';");
		return $query->result();
	}

	/**
	 * [category_list return all data or specific data of category (tblSystemCategory)]
	 * @param  Integer $catid - unique identity of a category
	 * @return array result
	 */
	public function category_list($catid=''){
		$where="";
		if($catid) $where=" WHERE syscat_id='{$catid}'";
		$query = $this->db->query("CALL prc_get_SystemCategory(\"$where\")");
		return $query->result();
	}

	/**
	 * [get_role_advance return all or specific menus and category access by a role]
	 * @param  integer $roleid - unique identity of a role
	 * @param  string $action - depends on what to be execute (view/edit)
	 * @param  string $get - depends on what to be execute (category or menu)
	 * @param  integer $syscatid - unique identity of a category
	 * @return array result
	 */
	public function get_role_advance($roleid='',$action='view',$get='',$syscatid=''){
		$return = "";
		if($get=="category"){
			if($action=="edit"){
				$return = $this->db->query("SELECT DISTINCT a.* FROM tblSystemCategory a INNER JOIN tblSystemMenuCategory b ON b.fk_syscatid=a.syscat_id ORDER BY a.arranged");
			}else{
				$return = $this->db->query("SELECT DISTINCT a.* FROM tblSystemCategory a
				INNER JOIN tblSystemMenuCategory b ON b.fk_syscatid=a.syscat_id
				INNER JOIN tblSystemRoleMenu c ON c.fk_menucat_id=b.menucat_id
				WHERE c.fk_roleid='{$roleid}'
				ORDER BY a.arranged");
			}
		}else{ /** if $get=="menu" */
			if($action=="edit"){
				$return = $this->db->query("SELECT a.menucat_id,a.fk_syscatid,c.fk_roleid,a.fk_menuid,b.title,c.can_add,c.can_read,c.can_edit,c.can_delete FROM tblSystemMenuCategory a
                    INNER JOIN tblSystemMenu b ON b.menuid=a.fk_menuid
                    LEFT JOIN tblSystemRoleMenu c ON c.fk_menucat_id=a.menucat_id AND c.fk_roleid='{$roleid}'
                    WHERE a.fk_syscatid='{$syscatid}' ORDER BY b.arranged,b.title");
			}else{
				$return = $this->db->query("SELECT a.menucat_id,a.fk_syscatid,c.fk_roleid,a.fk_menuid,b.title,c.can_add,c.can_read,c.can_edit,c.can_delete FROM tblSystemMenuCategory a
                    INNER JOIN tblSystemMenu b ON b.menuid=a.fk_menuid
                    INNER JOIN tblSystemRoleMenu c ON c.fk_menucat_id=a.menucat_id AND c.fk_roleid='{$roleid}'
                    WHERE a.fk_syscatid='{$syscatid}' ORDER BY b.arranged,b.title");
			}
		}
		return $return->result();
    }
    

    //start of saving here

    /**
     * [save_user_info - add or update a data in tblUserInformation/tblUserInfo]
     * @param  Integer $userid - unique identity of user
     * @param  string $username - username of a user
     * @param  string $lname - last name of a user
     * @param  string $fname - first name of a user
     * @param  string $mname - middle name of a user
     * @param  string $email - email of a user
     * @param  string $password - password of a user
     * @param  string $action - 'S' for Update or Add and 'D' for Disable
     */
    function save_user_info($userid = '', $username = '', $lname = '', $fname = '', $mname = '', $email = '', $password = '', $action = '')
    {
        $createdby = $this->session->userdata("userid");
        if($password!="")$password = md5($password);
        $this->db->query("CALL prc_set_UserInformation('$action','$userid','$username','$lname','$fname','$mname','$email','$password','$createdby',@a)");
    }

    /**
     * [save_user_role - add or delete a category in tblSystemRole]
     * @param  string $action - 'S' for Update or Add and 'D' for Delete
     * @param  Integer $userid - unique identity of user
     * @param  Integer $roleid - unique identity of role
     * @param  string $type - if action is 'D' then (wether delete by roleid or delete by userid) param('role','user')
     */
    function save_user_role($action = '', $userid = '', $roleid = '', $type = '')
    {
        $this->db->query("CALL prc_set_UserRole('$action','$userid','$roleid','$type')");
    }

    /**
     * [disable_user - temporarily disabled the user in tblUserInfo (ACTIVE/INACTIVE)]
     * @param  Integer $userid - unique identity of user
     * @param  Integer $status - ACTIVE OR INACTIVE
     */
    function disable_user($userid = '', $status = '')
    {
        $this->db->query("UPDATE tblUserInfo SET `status`='{$status}' WHERE fk_userid='{$userid}'");

    }

    /**
     * [save_role - save or delete a role in tblSystem Role]
     * @param  interger $roleid - unique identity of role
     * @param  string $code - code of a role
     * @param  string $description - description of a role
     * @param  string $icon - icon of a role
     * @param  string $action - 'S' for Update or Add and 'D' for delete
     */
    function save_role($roleid = '', $code = '', $description = '', $icon = '', $action = '')
    {
        $createdby = $this->session->userdata("userid");
        $this->db->query("CALL prc_set_SystemRole('$action','$roleid','$code','$description','$icon','$createdby',@a)");
    }

    /**
     * [save_role_advance description]
     * @param  string $action - 'S' for Insert or Add and 'D' for Delete
     * @param  integer $roleid - unique identity of role
     * @param  integer $menucatid - unique identity of menu_category
     * @param  integer $canread - 1 if true and 0 if false
     * @param  integer $canadd - 1 if true and 0 if false
     * @param  integer $canedit - 1 if true and 0 if false
     * @param  integer $candelete - 1 if true and 0 if false
     */
    function save_role_advance($action = '', $roleid = '', $menucatid = '', $canread = '', $canadd = '', $canedit = '', $candelete = '')
    {
        $this->db->query("CALL prc_set_SystemRoleMenu('$action','$roleid','$menucatid','$canread','$canadd','$canedit','$candelete')");
    }

    /**
     * [save_sys_category - add or update or delete a data in tblSystemCategory]
     * @param  Integer $syscatid - unique identity of category
     * @param  string $category_name - name of category
     * @param  string $icon - icon of category
     * @param  string $sort - arrangement of category
     * @param  string $action - 'S' for Update or Add and 'D' for Delete
     */
    function save_sys_category($syscatid = '', $category_name = '', $icon = '', $sort = '', $action = '')
    {
        $this->db->query("CALL prc_set_SystemCategory('$action','$syscatid','$category_name','$sort','$icon',@a)");
    }

    /**
     * [save_menu_category - add or delete a category in tblSystemMenuCategory]
     * @param  string $action - 'S' for Update or Add and 'D' for Delete
     * @param  Integer $syscatid - unique identity of category
     * @param  Integer $menuid - unique identity of menu
     */
    function save_menu_category($action = '', $syscatid = '', $menuid = '')
    {
        $this->db->query("CALL prc_set_SystemMenuCategory('$action','$syscatid','$menuid')");
    }

    /**
     * [save_menu - update the data of specific menu in tblSystemMenu]
     *              - update the category of specific menu in tblSystemMenuCategory
     * @param  string $menuid - unique identity of menu
     * @param  string $menu_title - title of menu
     * @param  string $menu_icon - icon of menu
     * @param  string $menu_status - status of menu (SHOW OR HIDDEN)
     * @param  string $menu_sort - arrangement of menu
     * @param  string $assign_cat - unique identity of category
     */
    function save_menu($menuid = '', $menu_title = '', $menu_icon = '', $menu_status = '', $menu_sort = '', $assign_cat = '')
    {
        $this->db->query("CALL prc_set_SystemMenu('$menuid','$menu_title','$menu_icon','$menu_status','$menu_sort','$assign_cat')");
    }

}
