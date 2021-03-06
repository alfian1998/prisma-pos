<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_hot_payment extends CI_Model {

	public function get_list($number,$offset,$search_term = null)
  {
		if($search_term == null){
			return $this->db
				->where('is_deleted','0')
				->get('hot_payment',$number,$offset)
				->result();
		}else{
			return $this->db
				->like('payment_name',$search_term,'both')
				->where('is_deleted','0')
				->get('hot_payment',$number,$offset)
				->result();
		}
  }

	public function get_all()
	{
		return $this->db
			->where('is_deleted','0')
			->where('is_active','1')
			->get('hot_payment')->result();
	}

	public function get_all_booking()
	{
		return $this->db
			->where('is_deleted','0')
			->where('is_active','1')
			->get('hot_booking')->result();
	}

	public function get_all_tamu()
	{
		return $this->db
			->get('hot_guest')->result();
	}

	
	public function get_payment()
	{
		return $this->db
			->get('hot_payment')->result();
	}

  public function get_by_id($id)
  {
    return $this->db->where('id',$id)->get('hot_payment')->row();
	}
	
	public function get_by_billing($id)
  {
		return $this->db->query("select
				* from hot_payment a 
				join hot_booking b on a.booking_id=b.booking_id
				join hot_guest c on b.guest_id=c.guest_id
				join hot_room d on b.room_id=d.room_id
				where a.id='$id' 
		")->row();
  }

  public function get_last()
  {
    return $this->db->order_by('id','desc')->get('hot_payment')->row();
  }

  public function insert($data)
  {
    $this->db->insert('hot_payment',$data);
  }

	public function get_tipe()
	{
		return $this->db
			->where('is_deleted','0')
			->where('is_active','1')
			->get('hot_category')->result();
	}


  public function update($id,$data)
  {
    $this->db->where('id',$id)->update('hot_payment',$data);
  }

  public function delete($id)
  {
    $this->db->where('id',$id)->update('hot_payment',array('is_deleted' => '1'));
  }

	function num_rows($search_term = null){
		if($search_term == null){
			return $this->db->get('hot_payment')->num_rows();
		}else{
			return $this->db->like('payment_name',$search_term,'both')->get('hot_payment')->num_rows();
		}
	}

	public function get_client()
	{
		return $this->db->get('hot_client')->row();
	}


}
