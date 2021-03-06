<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_res_unit extends CI_Model {

	public function get_list($number,$offset,$search_term = null)
  {
		if($search_term == null){
			return $this->db
				->where('is_deleted','0')
				->get('res_unit',$number,$offset)
				->result();
		}else{
			return $this->db
				->like('unit_name',$search_term,'both')
				->where('is_deleted','0')
				->get('res_unit',$number,$offset)
				->result();
		}
  }

	public function get_all()
	{
		return $this->db
			->where('is_deleted','0')
			->where('is_active','1')
			->get('res_unit')->result();
	}

  public function get_by_id($id)
  {
    return $this->db->where('unit_id',$id)->get('res_unit')->row();
  }

  public function get_last()
  {
    return $this->db->order_by('unit_id','desc')->get('res_unit')->row();
  }

  public function insert($data)
  {
    $this->db->insert('res_unit',$data);
  }

  public function update($id,$data)
  {
    $this->db->where('unit_id',$id)->update('res_unit',$data);
  }

  public function delete($id)
  {
    $this->db->where('unit_id',$id)->update('res_unit',array('is_deleted' => '1'));
  }

	function num_rows($search_term = null){
		if($search_term == null){
			return $this->db->get('res_unit')->num_rows();
		}else{
			return $this->db->like('unit_name',$search_term,'both')->get('res_unit')->num_rows();
		}
	}

}
