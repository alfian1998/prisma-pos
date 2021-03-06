<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ret_stock_out extends MY_Retail {

  var $access, $role_id;

  function __construct(){
    parent::__construct();
    if($this->session->userdata('menu') != 'ret_stock_out'){
      $this->session->set_userdata(array('menu' => 'ret_stock_out'));
      $this->session->unset_userdata('search_term');
    }
    $this->load->model('app_config/m_ret_config');

    $this->role_id = $this->session->userdata('role_id');
    $this->module_controller = 'ret_stock_out';
    $this->access = $this->m_ret_config->get_permission($this->role_id, $this->module_controller);

    $this->load->model('m_ret_stock_out');
  }

	public function index()
  {
    if ($this->access->_read == 1) {
      $data['access'] = $this->access;
      $data['title'] = 'Manajemen Stok Keluar';

      if($this->input->post('search_term')){
        $search_term = $this->input->post('search_term');
        $this->session->set_userdata(array('search_term' => $search_term));
      }

      $config['base_url'] = base_url().'ret_stock_out/index/';
      $config['per_page'] = 10;

      $from = $this->uri->segment(3);

      if($this->session->userdata('search_term') == null){
        $num_rows = $this->m_ret_stock_out->num_rows();

        $config['total_rows'] = $num_rows;
        $this->pagination->initialize($config);

        $data['stock_out'] = $this->m_ret_stock_out->get_list($config['per_page'],$from,$search_term = null);
      }else{
        $search_term = $this->session->userdata('search_term');
        $num_rows = $this->m_ret_stock_out->num_rows($search_term);
        $config['total_rows'] = $num_rows;
        $this->pagination->initialize($config);

        $data['stock_out'] = $this->m_ret_stock_out->get_list($config['per_page'],$from,$search_term);
      }

      $this->view('ret_stock_out/index',$data);
    } else {
      redirect(base_url().'app_error/error/403');
    }

  }

  public function reset_search()
  {
    $this->session->unset_userdata('search_term');
    redirect(base_url().'ret_stock_out/index');
  }

  public function form($id = null)
  {
    $data['access'] = $this->access;
    $this->load->model('ret_item/m_ret_item');
    if ($id == null) {
      if ($this->access->_create == 1) {
        $last = $this->m_ret_stock_out->get_last();
        if ($last == null) {
          $data['tx_id'] = 1;
          $data['tx_receipt_no'] = date('ymd').'000001';
        }else{
          $data['tx_id'] = $last->tx_id+1;
          if ($last->tx_date != date('Y-m-d')) {
            $data['tx_receipt_no'] = date('ymd').'000001';
          }else{
            $number = substr($last->tx_receipt_no,6,12);
            $number = intval($number)+1;
            $data['tx_receipt_no'] = date('ymd').str_pad($number, 6, '0', STR_PAD_LEFT);
          }
        }
        $data['title'] = 'Tambah Stok Keluar';
        $data['action'] = 'insert';
        $data['stock_out'] = null;
        $data['item'] = $this->m_ret_item->get_all();
        $this->view('ret_stock_out/form', $data);
      } else {
        redirect(base_url().'app_error/error/403');
      }
    }
  }

  public function insert()
  {
    $data = $_POST;
    $data['created_by'] = $this->session->userdata('user_realname');
    $data_out = array(
      'tx_receipt_no' => $data['tx_receipt_no'],
      'tx_type' => $data['tx_type'],
      'tx_date' => ind_to_date($data['tx_date']),
      'tx_notes' => $data['tx_notes'],
      'created_by' => $data['created_by']
    );
    $this->m_ret_stock_out->insert($data_out);
    $this->load->model('ret_stock/m_ret_stock');
    foreach ($data['item_id'] as $key => $val) {
      $data_item = array(
        'item_id' => $val,
        'tx_id' => $data['tx_id'],
        'tx_type' => $data['tx_type'],
        'tx_date' => ind_to_date($data['tx_date']),
        'stock_out' => $data['stock_out'][$key]*-1,
        'created_by' => $data['created_by']
      );
      $this->m_ret_stock->insert($data_item);
    }
    $this->session->set_flashdata('status', '<div class="alert alert-success alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="fa fa-check" aria-hidden="true"></span><span class="sr-only"> Sukses:</span> Data berhasil ditambahkan!</div>');
    redirect(base_url().'ret_stock_out/index');
  }

  public function detail($tx_id,$tx_type)
  {
    $data['access'] = $this->access;
    $data['title'] = 'Detail Stok Keluar';
    $data['detail'] = $this->m_ret_stock_out->get_detail($tx_id,$tx_type);

    $this->view('ret_stock_out/detail',$data);
  }

}
