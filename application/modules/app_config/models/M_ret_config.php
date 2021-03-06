<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_ret_config extends CI_Model {

  public function get_permission($role_id, $module_controller)
  {
    $query = $this->db
      ->select('module_icon,_create, _read, _update, _delete')
      ->where('ret_module.module_controller', $module_controller)
      ->where('ret_permission.role_id', $role_id)
      ->join('ret_module','ret_permission.module_id = ret_module.module_id')
      ->get('ret_permission');
    return $query->row();
  }

  public function get_list($module_parent=null)
	{
		$role_id = $this->session->userdata('role_id');

		$sql_where = '';
		$sql_where .= ($module_parent != "") ? " AND a.module_parent = '$module_parent'" : " AND a.module_parent = '' ";

		$query = $this->db->query(
			"SELECT a.module_id, a.module_name, a.module_icon, a.module_folder,
			 a.module_controller, a.module_url
			 FROM ret_module a
			 JOIN ret_permission b ON a.module_id = b.module_id
			 WHERE b.role_id = '$role_id'
			 $sql_where
			 ORDER BY a.module_id ASC"
		);
		if($query->num_rows() > 0) {
			$result = $query->result_array();
			foreach($result as $key => $val) {
				$result[$key]['child'] = $this->get_list($result[$key]['module_id']);
			}
			return $result;
		} else {
			return array();
		}
	}


}
