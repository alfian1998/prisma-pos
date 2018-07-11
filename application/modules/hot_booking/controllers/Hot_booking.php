<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hot_booking extends MY_Hotel {

  var $access, $booking_id;

  function __construct(){
    parent::__construct();
    if($this->session->userdata('menu') != 'hot_booking'){
      $this->session->set_userdata(array('menu' => 'hot_booking'));
      $this->session->unset_userdata('search_term');
    }
    $this->load->model('app_config/m_hot_config');

    $this->role_id = $this->session->userdata('role_id');
    $this->module_controller = 'hot_booking';
    $this->access = $this->m_hot_config->get_permission($this->role_id, $this->module_controller);

    $this->load->model('m_hot_booking');
    $this->load->model('m_hot_room');
  }

  public function getGuest(){
      $q = $this->input->get('q');
      $s = $this->m_hot_booking->cari_tamu($q);
      $array = array();
      foreach($s as $row){
        $array[] = array(
          'id'=>$row->id,
          'text'=>$row->full_name
        );
      }
      echo json_encode($array);
  }
  
	public function index()
  {
    if ($this->access->_read == 1) {
      $data['access'] = $this->access;
      $data['title'] = 'Manajemen Pemesanan Kamar';
      $data['guest'] = $this->m_hot_booking->get_all_tamu();
      $data['room'] = $this->m_hot_booking->get_room();
      $data['tipe'] = $this->m_hot_booking->get_tipe();
      $data['payment'] = $this->m_hot_booking->get_payment();


      if($this->input->post('search_term')){
        $search_term = $this->input->post('search_term');
        $this->session->set_userdata(array('search_term' => $search_term));
      }

      $config['base_url'] = base_url().'hot_booking/index/';
      $config['per_page'] = 10;

      $from = $this->uri->segment(3);

      if($this->session->userdata('search_term') == null){
        $num_rows = $this->m_hot_booking->num_rows();

        $config['total_rows'] = $num_rows;
        $this->pagination->initialize($config);

        $data['booking'] = $this->m_hot_booking->get_list($config['per_page'],$from,$search_term = null);
      }else{
        $search_term = $this->session->userdata('search_term');
        $num_rows = $this->m_hot_booking->num_rows($search_term);
        $config['total_rows'] = $num_rows;
        $this->pagination->initialize($config);

        $data['booking'] = $this->m_hot_booking->get_list($config['per_page'],$from,$search_term);
      }

      $this->view('hot_booking/index',$data);
    } else {
      redirect(base_url().'app_error/error/403');
    }

  }

  public function reset_search()
  {
    $this->session->unset_userdata('search_term');
    redirect(base_url().'hot_booking/index');
  }

  public function form($id = null)
  {
    $data['access'] = $this->access;
    $data['guest'] = $this->m_hot_booking->get_all_tamu();
    
    $data['service'] = $this->m_hot_booking->get_all_service();
    if ($id == null) {
      if ($this->access->_create == 1) {
        
        $data['title'] = 'Tambah Pemesanan Kamar';
        $data['action'] = 'insert';
        $data['booking'] = null;
        $data['room'] = $this->m_hot_booking->get_all_room();
        $this->view('hot_booking/form', $data);
      } else {
        redirect(base_url().'app_error/error/403');
      }
    }else{
      if ($this->access->_update == 1) {
        $data['title'] = 'Ubah Pemesanan Kamar';
        $data['booking'] = $this->m_hot_booking->get_by_id($id);
        $data['bookings'] = $this->m_hot_booking->get_by_ids($id);
        $data['bookingr'] = $this->m_hot_booking->get_by_idr($id);
        $data['room'] = $this->m_hot_booking->get_all_noroom();
        $data['action'] = 'update';
        $this->view('hot_booking/form', $data);
      } else {
        redirect(base_url().'app_error/error/403');
      }
    }
  }

  public function insert()
  {
    $data = $_POST;
    $data['created_by'] = $this->session->userdata('user_realname');
    $data['date_booking'] = date('y-m-d');
    $data['date_booking_from'] = date('y-m-d');
    if(!isset($data['is_active'])){
      $data['is_active'] = 0;
    }
    $this->m_hot_booking->insert($data);
    $id = $this->db->insert_id();

    $aaa = array(
			'booking_id' => $id,
			'cashed' => 0
      );
      
		$this->m_hot_booking->insert_payment($aaa);
    
    $idx = $this->input->post('room_id');
    $datax = array(
			'is_active' => 0
      );
    $this->m_hot_room->update($idx,$datax);

    $this->session->set_flashdata('status', '<div class="alert alert-success alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="fa fa-check" aria-hidden="true"></span><span class="sr-only"> Sukses:</span> Data berhasil ditambahkan!</div>');
    redirect(base_url().'hot_booking/index');
  }

  public function edit($id)
  {
    $data['booking']= $this->m_hot_booking->get_specific($id);
    $this->load->view('hot_booking/update', $data);
  }

  public function update()
  {
    $data = $_POST;
    $id = $data['booking_id'];
    $data['updated_by'] = $this->session->userdata('user_realname');
    if(!isset($data['is_active'])){
      $data['is_active'] = 0;
    }
    $this->m_hot_booking->update($id,$data);
    $this->session->set_flashdata('status', '<div class="alert alert-success alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="fa fa-check" aria-hidden="true"></span><span class="sr-only"> Sukses:</span> Data berhasil diubah!</div>');
    redirect(base_url().'hot_booking/index');
  }

  public function delete($id)
  {
    if ($this->access->_delete == 1) {
      $this->m_hot_booking->delete($id);
      $this->m_hot_booking->deletePay($id);
      $room = $this->m_hot_booking->get_by_id($id);
      $this->m_hot_booking->deleteRoom($room->room_id);
      $this->session->set_flashdata('status', '<div class="alert alert-success alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="fa fa-check" aria-hidden="true"></span><span class="sr-only"> Sukses:</span> Data berhasil dihapus!</div>');
      redirect(base_url().'hot_booking/index');
    } else {
      redirect(base_url().'app_error/error/403');
    }

  }

}
