<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_hot_charge_type extends CI_Model {

	public function get_list($number,$offset,$search_term = null)
  {
		if($search_term == null){
			return $this->db
				->where('is_deleted','0')
				->get('hot_charge_type',$number,$offset)
				->result();
		}else{
			return $this->db
				->like('charge_type_name',$search_term,'both')
				->where('is_deleted','0')
				->get('hot_charge_type',$number,$offset)
				->result();
		}
  }

  public function get_all()
  {
	return $this->db
		->where('is_deleted','0')
		->where('is_active','1')
		->get('hot_charge_type')->result();
  }

  public function list_data_except_tax_hotel()
  {
	 $sql = "SELECT *
            FROM hot_charge_type 
            WHERE is_active = '1'
            ORDER BY charge_type_id ASC";
    $query = $this->db->query($sql);
    $result = $query->result_array();
    //
    return $result;
  }

  function get_data_hot_charge_type($charge_type_id) {
    $sql = "SELECT *
            FROM hot_charge_type 
            WHERE is_active = '1' AND charge_type_id = ?
            ORDER BY charge_type_id ASC";
    $query = $this->db->query($sql, $charge_type_id);
    return $query->row_array();
  }

  public function get_by_id($id)
  {
    // return $this->db->where('charge_type_id',$id)->where('is_active','1')->get('hot_charge_type')->row();
    return $this->db->where('charge_type_id',$id)->get('hot_charge_type')->row();
  }

  public function get_by_id_active($id)
  {
    return $this->db->where('charge_type_id',$id)->where('is_active','1')->get('hot_charge_type')->row();
    // return $this->db->where('charge_type_id',$id)->get('hot_charge_type')->row();
  }


  public function get_last()
  {
    return $this->db->order_by('charge_type_id','desc')->get('hot_charge_type')->row();
  }

  public function insert($data)
  {
    $this->db->insert('hot_charge_type',$data);
  }

  public function update($id,$data)
  {
    $this->db->where('charge_type_id',$id)->update('hot_charge_type',$data);
  }

  public function delete($id)
  {
    $this->db->where('charge_type_id',$id)->update('hot_charge_type',array('is_deleted' => '1'));
  }

	function num_rows($search_term = null){
		if($search_term == null){
			return $this->db->get('hot_charge_type')->num_rows();
		}else{
			return $this->db->like('charge_type_name',$search_term,'both')->get('hot_charge_type')->num_rows();
		}
	}

}
