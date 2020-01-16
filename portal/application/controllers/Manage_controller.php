<?php
/**
 * @Author: Robert Ram Bolista
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Manage_controller extends MY_Controller {
	function __construct() {
        parent::__construct();
        $this->load->model('manage_model','tbinfo');
    }

    function index(){
        if(!islogged()){
            forcelogout();
        }else{
            switch($this->input->post('function_ctrl')){
                case "user_view":$this->user_view();break;
                case "role_view":$this->role_view();break;
                case "role_edit_advance":$this->role_edit_advance();break;
                case "role_view_advance":$this->role_view_advance();break;
                case "categoryview":$this->categoryview();break;
                case "menu_view":$this->menu_view();break;
                case "save_user":$this->save_user();break;
                case "disable_user":$this->disable_user();break;
                case "save_role":$this->save_role();break;
                case "delete_role":$this->delete_role();break;
                case "save_category":$this->save_category();break;
                case "delete_category":$this->delete_category();break;
                case "save_menu":$this->save_menu();break;
                case 'change_password':$this->change_password();break;
            }
        }
    }
     /**
     * [user_view view user properties and edit user properties]
     */
    private function user_view(){
        $data['setup'] = $this->data; /** this is global configuration in application/core/MY_Controller.php */
        $r=array();
        $ur=array();

        /** list of roles */
        $roles = $this->tbinfo->get_role_list();
        foreach ($roles as $row) {
        $r[$row->roleid] = "{$row->code} - {$row->description}";
        }

        /** list of roles under this user */
        $user_roles = $this->tbinfo->get_user_role($this->input->post('userid'));
        foreach ($user_roles as $row) {
            array_push($ur, $row->fk_roleid);
        }
        $data['canedit']        = $this->input->post('canedit');
        $data['candelete']      = $this->input->post('candelete');
        $data['job']            = $this->input->post('job');
        $data['userid']         = $this->input->post('userid');
        $data['user_info']      = $this->tbinfo->get_user_info($this->input->post('userid'))[0]; /** RETRIEVE ONLY THE FIRST ROW OF THE TABLE */
        $data['roles_list']     = $r;
        $data['user_role_list'] = $ur;
    	echo $this->load->view('admin/user_property', $data, TRUE);
    }

    /**
     * [role_view view role properties and edit role properties]
     */
    private function role_view(){
    	
        $data['setup'] = $this->data; /** this is global configuration in application/core/MY_Controller.php */
        $u=array();
        $urlist=array();

        /** list of users */
        $roles = $this->tbinfo->get_user_list();
        foreach ($roles as $row) {
        $u[$row->fk_userid] = "{$row->username} - {$row->FULLNAME}";
        }

        /** list of users under this role */
        $user_roles = $this->tbinfo->get_user_in_role($this->input->post('roleid'));
        foreach ($user_roles as $row) {
            array_push($urlist, $row->fk_userid);
        }
        $data['canedit']   = $this->input->post('canedit');
        $data['candelete'] = $this->input->post('candelete');
        $data['job']       = $this->input->post('job');
        $data['roleid']    = $this->input->post('roleid');
        $role_info = $this->tbinfo->get_role_list($this->input->post('roleid'))[0]; /** RETRIEVE ONLY THE FIRST ROW OF THE TABLE */
        if($this->input->post('job')!="add"){
            $data['code']        = $role_info->code;
            $data['description'] = $role_info->description;
            $data['icon']        = $role_info->icon;
            $data['fixed']       = $role_info->fixed;
        }else{
            $data['code']        = "";
            $data['description'] = "";
            $data['icon']        = "";
            $data['fixed']       = "";
        }
        $data['role_edit_advance'] = $this->role_edit_advance($this->input->post('roleid'));
        $data['role_view_advance'] = $this->role_view_advance($this->input->post('roleid'));
        $data['assigned_user']     = $this->tbinfo->get_assigned_user($this->input->post('roleid'))[0];
        $data['user_list']         = $u;
        $data['user_in_role_list'] = $urlist;
        echo $this->load->view('admin/role_property', $data, TRUE);
    }

    /**
     * [role_edit_advance generate a html for editing of role advance properties]
     * @param  string $roleid - unique identity of role
     * @return html
     */
    private function role_edit_advance($roleid=''){
        $this->load->model('Manage_model','tbinfo','category');
        $result = "<table class='table table-bordered table-striped' >
                                <tbody>
                                    <tr>
                                        <td><b>LEGENDS:</b> &nbsp;&nbsp;&nbsp; R: <b>READ</b> &nbsp; | &nbsp; E: <b>EDIT</b> &nbsp; | &nbsp; A: <b>ADD</b> &nbsp; | &nbsp; D: <b>DELETE</b></td>
                                    </tr>
                                </tbody>
                    </table>";

        $result .= " <div class='table-responsive'>
                        <table border='0' width='100%' cellpadding='5' cellspacing='5' >
                            <tr>";
        $sql = $this->tbinfo->get_role_advance($roleid,'edit','category','');
        foreach ($sql as $mrow):
        $description_1 = $mrow->description;
        $syscat_id_1   = $mrow->syscat_id;
                    $result .= "<td valign='top'>
                                <table border='0' width='100%' cellpadding='5' cellspacing='5' class='table table-bordered table-striped table-condensed table-hover smart-form has-tickbox'>
                                    <thead>
                                    <tr>
                                        <th style='text-align: center'>R</th>
                                        <th style='text-align: center'>E</th>
                                        <th style='text-align: center'>A</th>
                                        <th style='text-align: center'>D</th>
                                        <th>&nbsp;</th>
                                    </tr>

                                    <tr>
                                        <td width='1%' style='text-align: center'>
                                            <label class='checkbox'>
                                                <input type='checkbox' class='uniform allchecked' name='checkbox2' syscat='{$syscat_id_1}R' tag='main'>
                                                <i></i>
                                            </label>
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            <label class='checkbox'>
                                                <input type='checkbox' class='uniform allchecked' name='checkbox2' syscat='{$syscat_id_1}E' tag='main'>
                                                <i></i>
                                            </label>
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            <label class='checkbox'>
                                                <input type='checkbox' class='uniform allchecked' name='checkbox2' syscat='{$syscat_id_1}A' tag='main'>
                                                <i></i>
                                            </label>
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            <label class='checkbox'>
                                                <input type='checkbox' class='uniform allchecked' name='checkbox2' syscat='{$syscat_id_1}D' tag='main'>
                                                <i></i>
                                            </label>
                                        </td>
                                        <td><b>{$description_1}</b></td>
                                    </tr>
                                    </thead>
                                    <tbody>";
                    $sqlsub = $this->tbinfo->get_role_advance($roleid,'edit','menus',$syscat_id_1);
                    foreach ($sqlsub as $subrow):
                    $menucatid_1 = $subrow->menucat_id;
                    $menuid_1    = $subrow->fk_menuid;
                    $title_1     = $subrow->title;
                    $read_1      = $subrow->can_read=="1"?"checked":"";
                    $edit_1      = $subrow->can_edit;
                    $add_1       = $subrow->can_add;
                    $delete_1    = $subrow->can_delete;
                        $result .= "<tr class='menulist' menuid='{$menuid_1}' roleid='{$roleid}' menucatid='{$menucatid_1}'>
                                        <td width='1%' style='text-align: center'>
                                            <label class='checkbox'>
                                                <input type='checkbox' class='uniform menucheck' name='checkbox2' syscat='{$syscat_id_1}R' ".($read_1?"checked":"").">
                                            <i></i>
                                            </label>
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            <label class='checkbox'>
                                                <input type='checkbox' class='uniform menucheck' name='checkbox2' syscat='{$syscat_id_1}E' ".($edit_1?"checked":"").">
                                            <i></i>
                                            </label>
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            <label class='checkbox'>
                                                <input type='checkbox' class='uniform menucheck' name='checkbox2' syscat='{$syscat_id_1}A' ".($add_1?"checked":"").">
                                            <i></i>
                                            </label>
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            <label class='checkbox'>
                                                <input type='checkbox' class='uniform menucheck' name='checkbox2' syscat='{$syscat_id_1}D' ".($delete_1?"checked":"").">
                                            <i></i>
                                            </label>
                                        </td>
                                        <td>{$title_1}</td>
                                    </tr>";
                    endforeach;
                        $result .= "</tbody>
                                </table>
                            </td>";
        endforeach;
            $result .= "  </tr>
                        </table>
                    </div>";
        return $result;
    }

    /**
     * [role_view_advance generate a html for viewing of role advance properties]
     * @param  string $roleid - unique identity of role
     * @return html
     */
    private function role_view_advance($roleid=''){
        $this->load->model('Manage_model','tbinfo','category');
        $result = "<table class='table table-bordered table-striped'>
                                <tbody>
                                    <tr>
                                        <td><b>LEGENDS:</b> &nbsp;&nbsp;&nbsp; R: <b>READ</b> &nbsp; | &nbsp; E: <b>EDIT</b> &nbsp; | &nbsp; A: <b>ADD</b> &nbsp; | &nbsp; D: <b>DELETE</b></td>
                                    </tr>
                                </tbody>
                    </table>";
        $result .= " <div class='table-responsive'>
                        <table border='0' width='100%' cellpadding='5' cellspacing='5' >
                            <tr>";
        $sql = $this->tbinfo->get_role_advance($roleid,'view','category','');
        foreach ($sql as $mrow):
        $description_1 = $mrow->description;
        $syscat_id_1   = $mrow->syscat_id;
                    $result .= "<td valign='top'>
                                <table border='0' width='100%' cellpadding='5' cellspacing='5' class='table table-bordered table-striped table-condensed table-hover smart-form has-tickbox'>
                                    <thead>
                                    <tr>
                                        <th style='text-align: center'>R</th>
                                        <th style='text-align: center'>E</th>
                                        <th style='text-align: center'>A</th>
                                        <th style='text-align: center'>D</th>
                                        <th>&nbsp;</th>
                                    </tr>

                                    <tr>
                                        <td width='1%' style='text-align: center'>
                                            &nbsp;
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            &nbsp;
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            &nbsp;
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            &nbsp;
                                        </td>
                                        <td><b>{$description_1}</b></td>
                                    </tr>
                                    </thead>
                                    <tbody>";
                    $sqlsub = $this->tbinfo->get_role_advance($roleid,'view','menus',$syscat_id_1);
                    foreach ($sqlsub as $subrow):
                    $menucatid_1 = $subrow->menucat_id;
                    $menuid_1    = $subrow->fk_menuid;
                    $title_1     = $subrow->title;
                    $read_1      = $subrow->can_read=="1"?"checked":"";
                    $edit_1      = $subrow->can_edit;
                    $add_1       = $subrow->can_add;
                    $delete_1    = $subrow->can_delete;
                        $result .= "<tr class='menulist' menuid='{$menuid_1}' roleid='{$roleid}' menucatid='{$menucatid_1}'>
                                        <td width='1%' style='text-align: center'>
                                            ".($read_1?"<i class='fa fa-check-square fa-1x'></i>":"&nbsp;")."
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            ".($edit_1?"<i class='fa fa-check-square fa-1x'></i>":"&nbsp;")."
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            ".($add_1?"<i class='fa fa-check-square fa-1x'></i>":"&nbsp;")."
                                        </td>
                                        <td width='1%' style='text-align: center'>
                                            ".($delete_1?"<i class='fa fa-check-square fa-1x'></i>":"&nbsp;")."
                                        </td>
                                        <td>{$title_1}</td>
                                    </tr>";
                    endforeach;
                        $result .= "</tbody>
                                </table>
                            </td>";
        endforeach;
            $result .= "  </tr>
                        </table>
                    </div>";
        return $result;
    }

    /**
     * [categoryview view category properties and edit category properties]
     */
    private function categoryview(){
    	
        $data['setup'] = $this->data; /** this is global configuration in application/core/MY_Controller.php */

    	$mn=array();
    	$mc=array();
    	$menulist = $this->tbinfo->getmenulist($this->input->post('syscat_id'));
    	foreach ($menulist as $row) {
            $mn[$row->menuid] = $row->title;
    	}
    	$menuscategory = $this->tbinfo->menuscategory($this->input->post('syscat_id'));
    	foreach ($menuscategory as $row) {
    		array_push($mc, $row->fk_menuid);
    	}
        $data['canedit']       = $this->input->post('canedit');
        $data['candelete']     = $this->input->post('candelete');
        $data['job']           = $this->input->post('job');
        $data['syscatid']      = $this->input->post('syscat_id');
        $data['categoryinfo']  = $this->tbinfo->getcategoryinfo($this->input->post('syscat_id'))[0]; /** RETRIEVE ONLY THE FIRST ROW OF THE TABLE */
        $data['menuslist']     = $mn;
        $data['menuscategory'] = $mc;
    	echo $this->load->view('admin/category_properties', $data, TRUE);
    }

    /**
     * [menu_view view menu properties and edit menu properties]
     */
    private function menu_view(){
    	
        $data['setup'] = $this->data; /** this is global configuration in application/core/MY_Controller.php */
        $cl            = array(""=>"-select category-");
        $category_list = $this->tbinfo->category_list();
        foreach ($category_list as $row) {
        $cl[$row->syscat_id]   = $row->description;
        }
        
        $fl         = array(""=>"-select template-");
        /*
        $field_temp = $this->tbinfo->get_template_info();
        foreach ($field_temp as $row) {
        $fl[$row->template_id]   = $row->template_name;
        }
        */

        $data['template_list'] = $fl;
        $data['category_list'] = $cl;
        $data['canedit']       = $this->input->post('canedit');
        $data['candelete']     = $this->input->post('candelete');
        $data['job']           = $this->input->post('job');
        $data['menuid']        = $this->input->post('menuid');
        $data['menu_info']     = $this->tbinfo->get_menu_info($this->input->post('menuid'))[0]; /** RETRIEVE ONLY THE FIRST ROW OF THE TABLE */
        echo $this->load->view('admin/menu_property', $data, TRUE);

    }

    // start of saving


    /**
     * [save_user - save or edit user]
     */
    private function save_user()
    {
        $implode = $this->input->post('user_roles');
        $user_roles = explode(',', $implode);

        $username = $this->input->post('username');
        $lname = $this->input->post('lname');
        $fname = $this->input->post('fname');
        $mname = $this->input->post('mname');
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $userid = $this->input->post('userid');

        /** Save data in tblSystemCategory */
        $this->tbinfo->save_user_info($userid, $username, $lname, $fname, $mname, $email, $password, 'S');
        $q = $this->db->query("SELECT @a as userid;");
        if ($q->num_rows() > 0) {
            $row = $q->row(0);
            if ($row->userid) $userid = $row->userid;
        }
        /** Save data in tblUserRole 1 by 1 */
        foreach ($user_roles as $value) {
            if($value!="") $this->tbinfo->save_user_role('S', $userid, $value, '');
        }

        /** Delete all the role not in tblUserRole */
        $this->tbinfo->save_user_role('D', $userid, $implode, 'role');
    }

    /**
     * [disable_user - disable user (ACTIVE/INACTIVE)]
     */
    private function disable_user()
    {
        $userid = $this->input->post('userid');
        $status = $this->input->post('status');
        $this->tbinfo->disable_user($userid, $status);
    }

    /**
     * [save_role - save or edit role / also save the advance properties (access rights)]
     */
    private function save_role()
    {
        $implode = $this->input->post('user_availables');
        $user_availables = explode(',', $implode);
        $code = $this->input->post('code');
        $description = $this->input->post('description');
        $icon = $this->input->post('icon');
        $roleid = $this->input->post('roleid');

        /** Save data in tblSystemRole */
        $this->tbinfo->save_role($roleid, $code, $description, $icon, 'S');
        $q = $this->db->query("SELECT @a as roleid;");
        if ($q->num_rows() > 0) {
            $row = $q->row(0);
            if ($row->roleid) $roleid = $row->roleid;
        }

        /** Save data in tblUserRole 1 by 1 */
        foreach ($user_availables as $value) {
            if($value!="") $this->tbinfo->save_user_role('S', $value, $roleid, '');
        }

        /** Delete all the user not in tblUserRole */
        $this->tbinfo->save_user_role('D', $implode, $roleid, 'user');

        $this->tbinfo->save_role_advance('D', $roleid, '', '', '', '', '');
        $accesslist = $this->input->post('accesslist');
        $accesslist_permenu = explode(",", $accesslist);
        if (count($accesslist_permenu) > 0) {
            foreach ($accesslist_permenu as $value) {
                list($role_id_edit, $menucatid, $canread, $canedit, $canadd, $candelete) = explode(":", $value);
                if($menucatid!=""){
                    if($role_id_edit!="")$this->tbinfo->save_role_advance('S', $role_id_edit, $menucatid, $canread, $canadd, $canedit, $candelete);
                    else $this->tbinfo->save_role_advance('S', $roleid, $menucatid, $canread, $canadd, $canedit, $candelete);
                }
            }
        } else {
            list($role_id_edit, $menucatid, $canread, $canedit, $canadd, $candelete) = explode(":", $accesslist);
            if($menucatid!=""){
                if($role_id_edit!="") $this->tbinfo->save_role_advance('S', $role_id_edit, $menucatid, $canread, $canadd, $canedit, $candelete);
                else $this->tbinfo->save_role_advance('S', $roleid, $menucatid, $canread, $canadd, $canedit, $candelete);
            }
        }

    }

    /**
     * [delete_role delete role]
     */
    private function delete_role()
    {
        $roleid = $this->input->post('roleid');
        /** delete data in tblSystemRole */
        $this->tbinfo->save_role($roleid, '', '', '', 'D');
    }

    /**
     * [save_category - save or edit category and the menus under this category]
     */
    private function save_category()
    {
        $cat_implode = $this->input->post('categorymenus');
        $category_menu = explode(',', $cat_implode);
        $category_name = $this->input->post('catname');
        $icon = $this->input->post('icon');
        $sort = $this->input->post('sort');
        $syscatid = $this->input->post('syscatid');

        /** Save data in tblSystemCategory */
        $this->tbinfo->save_sys_category($syscatid, $category_name, $icon, $sort, 'S');
        $q = $this->db->query("SELECT @a as syscat;");
        if ($q->num_rows() > 0) {
            $row = $q->row(0);
            if ($row->syscat) $syscatid = $row->syscat;
        }
        /** Save data in tblSystemMenuCategory 1 by 1 */
        foreach ($category_menu as $value) {
            if($value!="") $this->tbinfo->save_menu_category('S', $syscatid, $value);
        }

        /** Delete all the menus not in category */
        $this->tbinfo->save_menu_category('D', $syscatid, $cat_implode);
    }

    /**
     * [delete_category - delete category]
     */
    private function delete_category()
    {
        $syscatid = $this->input->post('syscatid');
        /** delete data in tblSystemCategory */
        $this->tbinfo->save_sys_category($syscatid, '', '', '', 'D');
    }

    /**
     * [save_menu - save menu]
     */
    private function save_menu()
    {
        $menuid      = $this->input->post('menuid');
        $menu_title  = $this->input->post('menu_title');
        $menu_icon   = $this->input->post('menu_icon');
        $menu_status = $this->input->post('menu_status');
        $menu_sort   = $this->input->post('menu_sort');
        $assign_cat  = $this->input->post('assign_cat');
        $this->tbinfo->save_menu($menuid, $menu_title, $menu_icon, $menu_status, $menu_sort, $assign_cat);
    }

    /**
     * [change_password - save password]
     */
    private function change_password(){
        $currentPassword = $this->input->post('currentPassword');
        $newPassword     = $this->input->post('newPassword');
        $retypePassword  = $this->input->post('retypePassword');
        $return = $this->tbinfo->change_password($currentPassword, $newPassword, $retypePassword);
        echo json_encode($return);

    }


}
