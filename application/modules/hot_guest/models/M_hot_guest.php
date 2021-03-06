<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_hot_guest extends CI_Model {

	public function get_list($number,$offset,$search_term = null,$guest_type)
  {
		if($search_term == null){
			if ($guest_type !='') {
				return $this->db
					->where('is_deleted','0')
					->where('guest_type','1')
					->get('hot_guest',$number,$offset)
					->result();
			}else{
				return $this->db
					->where('is_deleted','0')
					->get('hot_guest',$number,$offset)
					->result();
			}
		}else{
			if ($guest_type !='') {
				return $this->db
					->like('guest_name',$search_term,'both')
					->where('is_deleted','0')
					->where('guest_type','1')
					->get('hot_guest',$number,$offset)
					->result();
			}else{
				return $this->db
					->like('guest_name',$search_term,'both')
					->where('is_deleted','0')
					->get('hot_guest',$number,$offset)
					->result();
			}
		}
  }

	public function get_all()
	{
		return $this->db
			->where('is_deleted','0')
			->where('is_active','1')
			->get('hot_guest')->result();
	}

  public function get_by_id($id)
  {
    return $this->db->where('guest_id',$id)->get('hot_guest')->row();
  }

  public function get_last()
  {
    return $this->db->order_by('guest_id','desc')->get('hot_guest')->row();
  }

  public function insert($data)
  {
    $this->db->insert('hot_guest',$data);
  }

  public function update($id,$data)
  {
    $this->db->where('guest_id',$id)->update('hot_guest',$data);
  }

  public function delete($id)
  {
    $this->db->where('guest_id',$id)->update('hot_guest',array('is_deleted' => '1'));
  }

	function num_rows($search_term = null){
		if($search_term == null){
			return $this->db->get('hot_guest')->num_rows();
		}else{
			return $this->db->like('guest_name',$search_term,'both')->get('hot_guest')->num_rows();
		}
	}

}
