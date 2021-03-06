<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Carbon\Carbon;

class Kar_reservation extends MY_Karaoke {

  var $access, $billing_id;

  function __construct(){
    parent::__construct();
    if($this->session->userdata('menu') != 'kar_reservation'){
      $this->session->set_userdata(array('menu' => 'kar_reservation'));
      $this->session->unset_userdata('search_term');
    }
    $this->load->model('app_config/m_kar_config');

    $this->role_id = $this->session->userdata('role_id');
    $this->module_controller = 'kar_reservation';
    $this->access = $this->m_kar_config->get_permission($this->role_id, $this->module_controller);

    $this->load->model('kar_client/m_kar_client');
    $this->load->model('kar_charge_type/m_kar_charge_type');
    $this->load->model('kar_billing/m_kar_billing');
    $this->load->model('m_kar_reservation');
    $this->load->model('kar_member/m_kar_member');
    $this->load->model('kar_room_type/m_kar_room_type');
    $this->load->model('kar_room/m_kar_room');
    $this->load->model('kar_extra/m_kar_extra');
    $this->load->model('kar_service/m_kar_service');
    $this->load->model('kar_paket/m_kar_paket');
    $this->load->model('kar_fnb/m_kar_fnb');
    $this->load->model('kar_non_tax/m_kar_non_tax');
    $this->load->model('kar_billing_room/m_kar_billing_room');
    $this->load->model('kar_billing_extra/m_kar_billing_extra');
    $this->load->model('kar_billing_service/m_kar_billing_service');
    $this->load->model('kar_billing_paket/m_kar_billing_paket');
    $this->load->model('kar_billing_fnb/m_kar_billing_fnb');
    $this->load->model('kar_billing_custom/m_kar_billing_custom');
    $this->load->model('kar_discount/m_kar_discount');
    $this->load->model('kar_guest/m_kar_guest');
  }

	public function index()
  {
    if ($this->access->_read == 1) {
      $data['access'] = $this->access;
      $data['title'] = 'Manajemen Pemesanan';

      if($this->input->post('search_term')){
        $search_term = $this->input->post('search_term');
        $this->session->set_userdata(array('search_term' => $search_term));
      }

      $config['base_url'] = base_url().'kar_reservation/index/';
      $config['per_page'] = 10;

      $from = $this->uri->segment(3);

      if($this->session->userdata('search_term') == null){
        $num_rows = $this->m_kar_reservation->num_rows();

        $config['total_rows'] = $num_rows;
        $this->pagination->initialize($config);

        $data['billing'] = $this->m_kar_reservation->get_list($config['per_page'],$from,$search_term = null);
      }else{
        $search_term = $this->session->userdata('search_term');
        $num_rows = $this->m_kar_reservation->num_rows($search_term);
        $config['total_rows'] = $num_rows;
        $this->pagination->initialize($config);

        $data['billing'] = $this->m_kar_reservation->get_list($config['per_page'],$from,$search_term);
      }

      $this->view('kar_reservation/index',$data);
    } else {
      redirect(base_url().'app_error/error/403');
    }

  }

  public function thumbnail()
  {
    if ($this->access->_read == 1) {
      $data['access'] = $this->access;
      $data['title'] = 'Manajemen Pemesanan';
      $data['room'] = $this->m_kar_room->get_all();

      $this->view('kar_reservation/thumbnail',$data);
    } else {
      redirect(base_url().'app_error/error/403');
    }
  }

  public function reset_search()
  {
    $this->session->unset_userdata('search_term');
    redirect(base_url().'kar_reservation/index');
  }

  public function form($id = null)
  {
    $data['access'] = $this->access;
    $data['member'] = $this->m_kar_member->get_all();
    $data['room_type'] = $this->m_kar_room_type->get_all();
    $data['extra'] = $this->m_kar_extra->get_all();
    $data['service'] = $this->m_kar_service->get_all();
    $data['paket'] = $this->m_kar_paket->get_all();
    $data['fnb'] = $this->m_kar_fnb->get_all();
    $data['non_tax'] = $this->m_kar_non_tax->get_all();
    $data['charge_type'] = $this->m_kar_charge_type->get_all();
    $data['discount_room'] = $this->m_kar_reservation->discount_room();
    $data['list_member'] = $this->m_kar_guest->get_all();
    if ($id == null) {
      if ($this->access->_create == 1) {
        $data['title'] = 'Tambah Data Pemesanan';
        $data['action'] = 'insert';
        $data['billing'] = null;
        //make receipt no
        // get last billing
        $last_billing = $this->m_kar_billing->get_last();
        //declare billing variable
        if ($last_billing == null) {
          $data['billing_id'] = 1;
          $data['billing_receipt_no'] = date('ymd').'000001';
          $this->m_kar_reservation->new_billing($data['billing_receipt_no']);
        }else{
          // status billing
          // -1 cancel
          // 0 empty
          // 1 proses
          // 2 complete          
          if ($last_billing->billing_status == 0) {
            $data['billing_id'] = $last_billing->billing_id;
            $data['billing_receipt_no'] = $last_billing->billing_receipt_no;
            // empty detail billing
            $this->m_kar_reservation->empty_detail($data['billing_id']);
          } else {
            // get new last billing
            $data['billing_id'] = $last_billing->billing_id+1;
            if (date('Y-m-d', strtotime($last_billing->created)) != date('Y-m-d')) {
              $data['billing_receipt_no'] = date('ymd').'000001';
            }else{
              $number = substr($last_billing->billing_receipt_no,6,12);
              $number = intval($number)+1;
              $data['billing_receipt_no'] = date('ymd').str_pad($number, 6, '0', STR_PAD_LEFT);
            }
            
            // insert new billing
            $this->m_kar_reservation->new_billing($data['billing_receipt_no']);
          }
        }
        $data['billing_id_name'] = 'TRS-'.$data['billing_receipt_no'];    

        $this->view('kar_reservation/form', $data);
      } else {
        redirect(base_url().'app_error/error/403');
      }
    }else{
      if ($this->access->_update == 1) {
        $data['title'] = 'Ubah Data Extra';
        $data['billing'] = $this->m_kar_billing->get_by_id($id);
        $data['action'] = 'update';
        $data['billing_room'] = $this->m_kar_reservation->get_billing_room($id);
        $this->view('kar_reservation/form', $data);
      } else {
        redirect(base_url().'app_error/error/403');
      }
    }
  }

  function get_arr_checked_value($data) {
      // format result : 01#02
      $result = '';
      foreach($data as $key => $val) {
          if($val != '') {
              $result .= $val;
          }
      }
      return $result;
  }

  public function insert()
  {
    $data = $_POST;   

    $data['billing_status'] = 1;
    $data['billing_date_in'] = ind_to_date($data['billing_date_in']);
    $data['billing_down_payment'] = price_to_num($data['billing_down_payment']);

    $data['user_id'] = $this->session->userdata('user_id');
    $data['user_realname'] = $this->session->userdata('user_realname');

    $guest_type = $data['guest_type'];

    // Tamu Baru
    // $guest_name = $data['guest_name'];
    // $guest_gender = $data['guest_gender'];
    // $guest_phone = $data['guest_phone'];
    // $guest_id_type = $data['guest_id_type'];
    // $guest_id_no = $data['guest_id_no'];

    //Member (Tamu Langganan)
    // $form_guest_name = $data['form_guest_name'];
    // $form_guest_gender = $data['form_guest_gender'];
    // $form_guest_phone = $data['form_guest_phone'];
    // $form_guest_id_type = $data['form_guest_id_type'];
    // $form_guest_id_no = $data['form_guest_id_no'];

    if ($data['form_guest_gender'] == "Laki-laki") {
      $form_guest_gender = "L";
    }else if($data['form_guest_gender'] == "Perempuan"){
      $form_guest_gender = "P";
    }

    if ($guest_type == '0') {
      unset($data['form_guest_id'], $data['form_guest_name'], $data['form_guest_gender'], $data['form_guest_phone'], $data['form_guest_id_type'], $data['form_guest_id_no']);
      //
      $data['guest_id'] = $data['guest_id'];
      $data['guest_name'] = $data['guest_name'];
      // $data['guest_gender'] = $data['guest_gender'];
      $data['guest_gender'] = ($data['guest_gender'] != '') ? $this->get_arr_checked_value($data['guest_gender']) : '';
      $data['guest_phone'] = $data['guest_phone'];
      $data['guest_id_type'] = $data['guest_id_type'];
      $data['guest_id_no'] = $data['guest_id_no'];
    }else if ($guest_type == '1') {
      $data['guest_id'] = $data['form_guest_id'];
      $data['guest_name'] = $data['form_guest_name'];
      $data['guest_gender'] = $data['form_guest_gender'];
      $data['guest_phone'] = $data['form_guest_phone'];
      $data['guest_id_type'] = $data['form_guest_id_type'];
      $data['guest_id_no'] = $data['form_guest_id_no'];
      //
      unset($data['form_guest_id'], $data['form_guest_name'], $data['form_guest_gender'], $data['form_guest_phone'], $data['form_guest_id_type'], $data['form_guest_id_no']);
    }

    $action = $data['action'];
    unset($data['action']);
    
    $this->m_kar_reservation->update($data['billing_id'],$data);

    $this->update_all_billing($data['billing_id']);

    $this->session->set_flashdata('status', '<div class="alert alert-success alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="fa fa-check" aria-hidden="true"></span><span class="sr-only"> Sukses:</span> Data berhasil ditambahkan!</div>');
    if ($action == 'save_payment') {
      redirect(base_url().'kar_reservation/payment/'.$data['billing_id']);
    } else {
      redirect(base_url().'kar_reservation/index');
    }
  }

  public function update_all_billing($billing_id)
  {
    $room = $this->m_kar_reservation->get_billing_room($billing_id);
    $extra = $this->m_kar_reservation->get_billing_extra($billing_id);
    $service = $this->m_kar_reservation->get_billing_service($billing_id);
    $paket = $this->m_kar_reservation->get_billing_paket($billing_id);
    $fnb = $this->m_kar_reservation->get_billing_fnb($billing_id);
    $non_tax = $this->m_kar_reservation->get_billing_non_tax($billing_id);
    $custom = $this->m_kar_reservation->get_billing_custom($billing_id);

    $billing_subtotal = 0;
    $billing_tax = 0;
    $billing_total = 0;
    $billing_discount = 0;

    foreach ($room as $row) {
      $billing_subtotal += $row->room_type_subtotal;
      $billing_tax += $row->room_type_tax;
      $billing_total += $row->room_type_total;
      $billing_discount += $row->room_type_discount;
    }

    foreach ($extra as $row) {
      $billing_subtotal += $row->extra_subtotal;
      $billing_tax += $row->extra_tax;
      $billing_total += $row->extra_total;
    }

    foreach ($service as $row) {
      $billing_subtotal += $row->service_subtotal;
      $billing_tax += $row->service_tax;
      $billing_total += $row->service_total;
    }

    foreach ($paket as $row) {
      $billing_subtotal += $row->paket_subtotal;
      $billing_tax += $row->paket_tax;
      $billing_total += $row->paket_total;
    }

    foreach ($fnb as $row) {
      $billing_subtotal += $row->fnb_subtotal;
      $billing_tax += $row->fnb_tax;
      $billing_total += $row->fnb_total;
    }

    foreach ($non_tax as $row) {
      $billing_subtotal += $row->non_tax_total;
      $billing_total += $row->non_tax_total;
    }

    foreach ($custom as $row) {
      $billing_subtotal += $row->custom_total;
      $billing_total += $row->custom_total;
    }

    $data['billing_subtotal'] = $billing_subtotal;
    $data['billing_tax'] = $billing_tax;
    $data['billing_discount'] = $billing_discount;
    $data['billing_total'] = $billing_total;

    $this->m_kar_reservation->update($billing_id,$data);
  }

  public function detail($id)
  {
    $data['access'] = $this->access;
    $data['id'] = $id;
    $data['title'] = 'Detail Pemesanan';

    $data['billing'] = $this->m_kar_reservation->get_billing($id);
    $data['charge_type'] = $this->m_kar_charge_type->get_all();

    $this->view('kar_reservation/detail',$data);
  }

  public function payment($id)
  {
    $data['access'] = $this->access;
    $data['id'] = $id;
    //
    $data['action'] = 'payment_action';
    //
    $data['title'] = 'Pembayaran';
    $data['billing'] = $this->m_kar_reservation->get_billing($id);
    $data['charge_type'] = $this->m_kar_charge_type->get_all();

    $this->view('kar_reservation/payment',$data);
  }

  public function payment_action()
  {
    $data = $_POST;
    $data['updated_by'] = $this->session->userdata('user_realname');
    //
    $id = $data['billing_id'];
    // $save_print = $data['save_print'];
    // unset($data['save_print']);
    //
    $billing = $this->m_kar_reservation->get_billing($id);
    $billing_total = $billing->billing_total - $billing->billing_down_payment;
    //
    $data['billing_payment'] = price_to_num($data['billing_payment']);
    if ($billing->billing_down_payment_type == 1) {
      $data['billing_change'] = $data['billing_payment'] - $billing_total;
    }else {
      $dp_prosen = $billing->billing_total*($billing->billing_down_payment/100);
      //
      if ($billing->billing_down_payment > $billing->billing_total) {
        $data['billing_change'] = $billing->billing_down_payment-$billing->billing_total;
      }else{
        if ($dp_prosen > $billing->billing_total) {
          $data['billing_change'] = $dp_prosen - $billing->billing_total;
        }else {
          $data['billing_change'] = $data['billing_payment'] - ($billing->billing_total - $dp_prosen);
        }
      }
    }
    $data['billing_status'] = 2;
    //
    $this->m_kar_billing->update($id,$data);
    // if ($save_print == 'print_pdf') {
    //   $this->frame_pdf($id, '');
    // }else if($save_print == 'print_struk'){
    //   $this->reservation_print_struk($id);
    // }

    $this->m_kar_reservation->update($data['billing_id'],$data);
    $id = $data['billing_id'];

    $client = $this->m_kar_client->get_all();
    $bill = $this->m_kar_reservation->get_billing($id);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    $dashboard = array(
      'auth'=> 'prismapos.addkomputer',
      'apikey'=> '69f86eadd81650164619f585bb017316',
      'app_type_id'=> 4,
      'client_id'=> $client->client_id,
      'pos_sn'=> $client->client_serial_number,
      'npwpd'=> $client->client_npwpd,
      'customer_name'=> $bill->guest_name,
      'no_receipt'=> 'TRS-'.$bill->billing_receipt_no,
      'tx_id'=> $bill->billing_id,
      'tx_date'=> $bill->billing_date_in,
      'tx_time'=> $bill->billing_time_in,
      'tx_total_before_tax'=> $bill->billing_subtotal,
      'tax_code'=> $tax->charge_type_code,
      'tax_ratio'=> $tax->charge_type_ratio,
      'tx_total_tax'=> $bill->billing_tax,
      'tx_total_after_tax'=> $bill->billing_subtotal+$bill->billing_tax,
      'tx_total_grand'=> $bill->billing_subtotal+$bill->billing_tax,
      'user_id'=> $bill->user_id,
      'user_realname'=> $bill->user_realname,
      'created'=> $bill->created,
    );

    echo json_encode($dashboard);
  }

  public function edit($id)
  {
    $data['billing']= $this->m_kar_billing->get_specific($id);
    $data['room_id'] = $this->m_kar_billing_room->get_by_billing_id($id);
    $this->load->view('kar_billing/update', $data);
  }

  public function cancel($billing_id)
  {
    $data = array(
      'billing_status' => -1
    );
    $this->m_kar_reservation->update($billing_id,$data);
    redirect(base_url().'kar_reservation/index');
  }

  public function update()
  {
    $data = $_POST;

    $data['billing_status'] = 1;
    $data['billing_date_in'] = ind_to_date($data['billing_date_in']);
    // $data['billing_date_out'] = ind_to_date($data['billing_date_out']);
    $data['billing_down_payment'] = price_to_num($data['billing_down_payment']);

    $data['user_id'] = $this->session->userdata('user_id');
    $data['user_realname'] = $this->session->userdata('user_realname');

    $guest_type = $data['guest_type'];

    // Tamu Baru
    // $guest_name = $data['guest_name'];
    // $guest_gender = $data['guest_gender'];
    // $guest_phone = $data['guest_phone'];
    // $guest_id_type = $data['guest_id_type'];
    // $guest_id_no = $data['guest_id_no'];

    //Member (Tamu Langganan)
    // $form_guest_name = $data['form_guest_name'];
    // $form_guest_gender = $data['form_guest_gender'];
    // $form_guest_phone = $data['form_guest_phone'];
    // $form_guest_id_type = $data['form_guest_id_type'];
    // $form_guest_id_no = $data['form_guest_id_no'];

    if ($data['form_guest_gender'] == "Laki-laki") {
      $form_guest_gender = "L";
    }else if($data['form_guest_gender'] == "Perempuan"){
      $form_guest_gender = "P";
    }

    if ($guest_type == '0') {
      unset($data['form_guest_id'], $data['form_guest_name'], $data['form_guest_gender'], $data['form_guest_phone'], $data['form_guest_id_type'], $data['form_guest_id_no']);
      //
      $data['guest_id'] = $data['guest_id'];
      $data['guest_name'] = $data['guest_name'];
      $data['guest_gender'] = ($data['guest_gender'] != '') ? $this->get_arr_checked_value($data['guest_gender']) : '';
      $data['guest_phone'] = $data['guest_phone'];
      $data['guest_id_type'] = $data['guest_id_type'];
      $data['guest_id_no'] = $data['guest_id_no'];
    }else if ($guest_type == '1') {
      $data['guest_id'] = $data['form_guest_id'];
      $data['guest_name'] = $data['form_guest_name'];
      $data['guest_gender'] = $data['form_guest_gender'];
      $data['guest_phone'] = $data['form_guest_phone'];
      $data['guest_id_type'] = $data['form_guest_id_type'];
      $data['guest_id_no'] = $data['form_guest_id_no'];
      //
      unset($data['form_guest_id'], $data['form_guest_name'], $data['form_guest_gender'], $data['form_guest_phone'], $data['form_guest_id_type'], $data['form_guest_id_no']);
    }

    $action = $data['action'];
    unset($data['action']);
    
    $this->m_kar_reservation->update($data['billing_id'],$data);

    $data_update = array(
      'billing_id' => $data['billing_id'],
      'billing_date_in' => $data['billing_date_in'],
      'billing_time_in' => $data['billing_time_in'],
      // 'billing_date_out' => $data['billing_date_out'],
      // 'billing_time_out' => $data['billing_time_out'],
    );
    
    // $this->update_billing_room($data_update);
    $this->update_all_billing($data['billing_id']);

    $this->session->set_flashdata('status', '<div class="alert alert-success alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="fa fa-check" aria-hidden="true"></span><span class="sr-only"> Sukses:</span> Data berhasil diubah!</div>');
    // redirect(base_url().'kar_reservation/index');
    if ($action == 'save_payment') {
      redirect(base_url().'kar_reservation/payment/'.$data['billing_id']);
    } else {
      redirect(base_url().'kar_reservation/index');
    }
  }

  public function reservation_print_pdf($billing_id)
  {
    $data['title'] = "Laporan Pemesanan Pembayaran";
    $data['client'] = $this->m_kar_client->get_all();
    //
    $data['billing'] = $this->m_kar_reservation->get_billing($billing_id);
    $data['charge_type'] = $this->m_kar_charge_type->get_all();
    $data['date_now'] = date("Y-m-d");
    $data['time_now'] = date("H:i:s");

    $this->load->library('pdf');
    $this->pdf->setPaper('A4', 'potrait');
    $this->pdf->filename = "laporan-Pemesanan-pembayaran-".$data['billing']->billing_receipt_no.".pdf";
    $this->pdf->load_view('print_pdf', $data);
  }

  public function frame_pdf($billing_id, $url = '')
  {
    $data['billing_id'] = $billing_id;
    $data['url'] = $url;

    $this->view('kar_reservation/frame_pdf', $data);
  }

  public function reservation_print_struk($billing_id, $url)
  {
    $title = "Laporan Pemesanan Pembayaran";
    $client = $this->m_kar_client->get_all();
    //
    $billing = $this->m_kar_reservation->get_billing($billing_id);
    $charge_type = $this->m_kar_charge_type->get_all();
    $date_now = date("Y-m-d");
    $time_now = date("H:i:s");
    //

    //print
    $this->load->library("EscPos.php");

    try {
      $connector = new Escpos\PrintConnectors\WindowsPrintConnector("POS-58");
         
      $printer = new Escpos\Printer($connector);

      //print image
      if ($client->client_logo !='') {
        $img = Escpos\EscposImage::load("img/".$client->client_logo);
        $printer -> setJustification(Escpos\Printer::JUSTIFY_CENTER);
        $printer -> bitImage($img);
        $printer -> feed();
      }
      //Keterangan Wajib Pajak
      $printer -> setJustification(Escpos\Printer::JUSTIFY_CENTER);

      if ($client->client_logo == '') {
        $printer -> setUnderline(Escpos\Printer::UNDERLINE_DOUBLE);
        $printer -> text($client->client_name."\n");
        $printer -> setUnderline(Escpos\Printer::UNDERLINE_NONE);
      }

      $printer -> text($client->client_street.','.$client->client_district."\n");
      $printer -> text($client->client_city."\n");
      $printer -> text("NPWPD : ".$client->client_npwpd."\n"); 
      $printer -> text('TRS-'.$billing->billing_receipt_no."\n");
      //Judul
      $printer -> text('--------------------------------');
      $printer -> feed();
      $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
      // $printer -> text(substr($billing->user_realname,0,12).' '.convert_date($billing->created));
      $printer -> text(substr($billing->user_realname,0,12).' '.date_to_ind(date("Y-m-d")).' '.date("H:i:s"));
      $printer -> feed();
      $printer -> text('--------------------------------');
      //
      $check_in_left = "IN/Masuk";
      $check_in_right = date_to_ind($billing->billing_date_in).' '.$billing->billing_time_in;
      $printer -> text(print_justify($check_in_left, $check_in_right, 10, 19, 3));
      // $check_out_left = "OUT/Keluar";
      // $check_out_right = date_to_ind($billing->billing_date_out).' '.$billing->billing_time_out;
      // $printer -> text(print_justify($check_out_left, $check_out_right, 10, 19, 3));
      $printer -> text('--------------------------------');
      //Keterangan Tamu
      $printer -> setJustification(Escpos\Printer::JUSTIFY_CENTER);
      $printer -> selectPrintMode(Escpos\Printer::MODE_EMPHASIZED);
      $printer -> text("Detail Tamu :");
      $printer -> selectPrintMode(Escpos\Printer::MODE_FONT_A);
      $printer -> feed();
      $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
      $printer -> text("Nama    : ".substr($billing->guest_name,0,22));
      $printer -> feed();
      if ($billing->guest_phone !='') {
        $phone = $billing->guest_phone;
      }else {
        $phone = "-";
      }
      $printer -> text("No Telp : ".$phone);

      if ($billing->guest_id_type == '1') {
        $kategori_id = "-";
      }elseif ($billing->guest_id_type == '2') {
        $kategori_id = "KTP";
        $id_no = "(".$billing->guest_id_no.")";
      }elseif ($billing->guest_id_type == '3') {
        $kategori_id = "SIM";
        $id_no = "(".$billing->guest_id_no.")";
      }elseif ($billing->guest_id_type == '4') {
        $kategori_id = "Lainnya";
        $id_no = "(".$billing->guest_id_no.")";
      }

      $printer -> feed();
      $printer -> text("No ID   : ".$kategori_id.$id_no);
      $printer -> feed();
      $printer -> text('--------------------------------');
      //Keterangan Pemesanan
      // Kamar
      if ($billing->room != null){
        $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
        $printer -> selectPrintMode(Escpos\Printer::MODE_EMPHASIZED);
        $printer -> text("Room :");
        $printer -> selectPrintMode(Escpos\Printer::MODE_FONT_A);
        $printer -> feed();
        foreach ($billing->room as $row){
          $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
          $printer -> text($row->room_name);
          $printer -> feed();
          $printer -> setJustification(Escpos\Printer::JUSTIFY_RIGHT);
          //
          if ($client->client_is_taxed == 0) {
            $room_type_subtotal = num_to_price($row->room_type_charge);
          }else{
            $room_type_subtotal = num_to_price($row->room_type_total/$row->room_type_duration);
          }
          //
          if ($client->client_is_taxed == 0) {
            $room_type_total = num_to_price($row->room_type_subtotal);
          }else{
            $room_type_total = num_to_price($row->room_type_total);
          }
          //
          $printer -> text(round($row->room_type_duration,0,PHP_ROUND_HALF_UP)." Jam X ".$room_type_subtotal." = ".$room_type_total);
          $printer -> feed();
        }
      }
      // Paket
      if ($billing->paket != null){
        $printer -> text('--------------------------------');
        $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
        $printer -> selectPrintMode(Escpos\Printer::MODE_EMPHASIZED);
        $printer -> text("Paket :");
        $printer -> selectPrintMode(Escpos\Printer::MODE_FONT_A);
        $printer -> feed();
        foreach ($billing->paket as $row){
          $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
          $printer -> text($row->paket_name);
          $printer -> feed();
          $printer -> setJustification(Escpos\Printer::JUSTIFY_RIGHT);
          //
          if ($client->client_is_taxed == 0) {
            $paket_charge_sub_total = num_to_price($row->paket_charge);
          }else{
            $paket_charge_sub_total = num_to_price($row->paket_total/$row->paket_amount);
          }
          //
          if ($client->client_is_taxed == 0) {
            $paket_charge_total = num_to_price($row->paket_subtotal);
          }else{
            $paket_charge_total = num_to_price($row->paket_total);
          }
          //
          $printer -> text(round($row->paket_amount,0,PHP_ROUND_HALF_UP)." X ".$paket_charge_sub_total." = ".$paket_charge_total);
          $printer -> feed();
        }
      }
      // Pelayanan
      if ($billing->service != null){
        $printer -> text('--------------------------------');
        $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
        $printer -> selectPrintMode(Escpos\Printer::MODE_EMPHASIZED);
        $printer -> text("Pelayanan :");
        $printer -> selectPrintMode(Escpos\Printer::MODE_FONT_A);
        $printer -> feed();
        foreach ($billing->service as $row){
          $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
          $printer -> text($row->service_name);
          $printer -> feed();
          $printer -> setJustification(Escpos\Printer::JUSTIFY_RIGHT);
          //
          if ($client->client_is_taxed == 0) {
            $service_charge_sub_total = num_to_price($row->service_charge);
          }else{
            $service_charge_sub_total = num_to_price($row->service_total/$row->service_amount);
          }
          //
          if ($client->client_is_taxed == 0) {
            $service_charge_total = num_to_price($row->service_subtotal);
          }else{
            $service_charge_total = num_to_price($row->service_total);
          }
          //
          $printer -> text(round($row->service_amount,0,PHP_ROUND_HALF_UP)." X ".$service_charge_sub_total." = ".$service_charge_total);
          $printer -> feed();
        }
      }
      // F&B
      if ($billing->fnb != null){
        $printer -> text('--------------------------------');
        $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
        $printer -> selectPrintMode(Escpos\Printer::MODE_EMPHASIZED);
        $printer -> text("F&B :");
        $printer -> selectPrintMode(Escpos\Printer::MODE_FONT_A);
        $printer -> feed();
        foreach ($billing->fnb as $row){
          $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
          $printer -> text($row->fnb_name);
          $printer -> feed();
          $printer -> setJustification(Escpos\Printer::JUSTIFY_RIGHT);
          //
          if ($client->client_is_taxed == 0) {
            $fnb_charge_sub_total = num_to_price($row->fnb_charge);
          }else{
            $fnb_charge_sub_total = num_to_price($row->fnb_total/$row->fnb_amount);
          }
          //
          if ($client->client_is_taxed == 0) {
            $fnb_charge_total = num_to_price($row->fnb_subtotal);
          }else{
            $fnb_charge_total = num_to_price($row->fnb_total);
          }
          //
          $printer -> text(round($row->fnb_amount,0,PHP_ROUND_HALF_UP)." X ".$fnb_charge_sub_total." = ".$fnb_charge_total);
          $printer -> feed();
        }
      }
      // Non Pajak
      if ($billing->non_tax != null){
        $printer -> text('--------------------------------');
        $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
        $printer -> selectPrintMode(Escpos\Printer::MODE_EMPHASIZED);
        $printer -> text("Non Pajak :");
        $printer -> selectPrintMode(Escpos\Printer::MODE_FONT_A);
        $printer -> feed();
        foreach ($billing->non_tax as $row){
          $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
          $printer -> text($row->non_tax_name);
          $printer -> feed();
          $printer -> setJustification(Escpos\Printer::JUSTIFY_RIGHT);
          //
          $printer -> text(round($row->non_tax_amount,0,PHP_ROUND_HALF_UP)." X ".num_to_price($row->non_tax_charge)." = ".num_to_price($row->non_tax_total));
          $printer -> feed();
        }
      }
      // Custom
      if ($billing->custom != null){
        $printer -> text('--------------------------------');
        $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
        $printer -> selectPrintMode(Escpos\Printer::MODE_EMPHASIZED);
        $printer -> text("Kustom :");
        $printer -> selectPrintMode(Escpos\Printer::MODE_FONT_A);
        $printer -> feed();
        foreach ($billing->custom as $row){
          $printer -> setJustification(Escpos\Printer::JUSTIFY_LEFT);
          $printer -> text($row->custom_name);
          $printer -> feed();
          $printer -> setJustification(Escpos\Printer::JUSTIFY_RIGHT);
          //
          $printer -> text(round($row->custom_amount,0,PHP_ROUND_HALF_UP)." X ".num_to_price($row->custom_charge)." = ".num_to_price($row->custom_total));
          $printer -> feed();
        }
      }
      $printer -> text('--------------------------------');
      //
      if ($billing->billing_down_payment_type == 1){
        $uang_muka = num_to_price($billing->billing_down_payment);
      }
      else{
        $uang_muka = round($billing->billing_down_payment,0,PHP_ROUND_HALF_UP)." %";
      }

      if ($billing->billing_down_payment > $billing->billing_total){
        $sisa_bayar = num_to_price(0);
      }
      else{
        if ($billing->billing_down_payment_type == 1){
          $sisa_bayar = num_to_price($billing->billing_total-$billing->billing_down_payment);
        }
        else{
          $dp_prosen = $billing->billing_total*($billing->billing_down_payment/100);

          if ($dp_prosen > $billing->billing_total){
            $sisa_bayar = num_to_price(0);
          }
          else{
            $sisa_bayar = num_to_price($billing->billing_total-$dp_prosen);
          }
        }
      }
      //
      $space_array = array(
        strlen(num_to_price($billing->billing_total)),
        strlen($uang_muka),
        strlen($sisa_bayar),
        strlen(num_to_price($billing->billing_payment)),
        strlen(num_to_price($billing->billing_change)),
        strlen(num_to_price($billing->billing_discount)),
      );
      $l_max = max($space_array);
      $l_1 = $l_max - strlen(num_to_price($billing->billing_total));
      $s_1 = '';
      for ($i=0; $i < $l_1; $i++) {
        $s_1 .= ' ';
      };
      $l_2 = $l_max - strlen(num_to_price($billing->billing_down_payment));
      $s_2 = '';
      for ($i=0; $i < $l_2; $i++) {
        $s_2 .= ' ';
      };
      $l_3 = $l_max - strlen($sisa_bayar);
      $s_3 = '';
      for ($i=0; $i < $l_3; $i++) {
        $s_3 .= ' ';
      };
      $l_4 = $l_max - strlen(num_to_price($billing->billing_payment));
      $s_4 = '';
      for ($i=0; $i < $l_4; $i++) {
        $s_4 .= ' ';
      };
      $l_5 = $l_max - strlen(num_to_price($billing->billing_change));
      $s_5 = '';
      for ($i=0; $i < $l_5; $i++) {
        $s_5 .= ' ';
      };
      $l_6 = $l_max - strlen(num_to_price($billing->billing_subtotal));
      $s_6 = '';
      for ($i=0; $i < $l_6; $i++) {
        $s_6 .= ' ';
      };
      $l_7 = $l_max - strlen(num_to_price($billing->billing_discount));
      $s_7 = '';
      for ($i=0; $i < $l_7; $i++) {
        $s_7 .= ' ';
      };

      foreach ($charge_type as $row){

        if ($row->charge_type_id == '1') {
          $numb = "7";
          $charge_type_money = num_to_price($billing->billing_tax);
        }else if ($row->charge_type_id == '2') {
          $numb = "8";
          $charge_type_money = num_to_price($billing->billing_service);
        }else if ($row->charge_type_id == '3') {
          $numb = "9";
          $charge_type_money = num_to_price($billing->billing_other);
        }

        $l_[$numb] = $l_max - strlen($charge_type_money);
        $s_[$numb] = '';
        for ($i=0; $i < $l_[$numb]; $i++) {
          $s_[$numb] .= ' ';
        };
      }

      if ($client->client_is_taxed == 0){
        // Sebelum pajak
        $printer -> text("Subtotal = ".$s_6.num_to_price($billing->billing_subtotal));
        $printer -> feed();

        foreach ($charge_type as $row){
          //
          if ($row->charge_type_id == '1') {
            $numb = "7";
            $charge_type_money = num_to_price($billing->billing_tax);
          }else if ($row->charge_type_id == '2') {
            $numb = "8";
            $charge_type_money = num_to_price($billing->billing_service);
          }else if ($row->charge_type_id == '3') {
            $numb = "9";
            $charge_type_money = num_to_price($billing->billing_other);
          }
          //
          $printer -> text($row->charge_type_name." = ".$s_[$numb].$charge_type_money);
          $printer -> feed();
        }
      }

      //
      $printer -> feed();
      if ($client->client_is_taxed == 0){
        $name_total = "Total";
      }else {
        $name_total = "Total Bersih";
      }
      $printer -> text('Diskon = '.$s_7.num_to_price($billing->billing_discount));
      $printer -> feed();
      $printer -> text($name_total.' = '.$s_1.num_to_price($billing->billing_total));
      $printer -> feed();
      $printer -> text('Uang Muka = '.$s_2.$uang_muka);
      $printer -> feed();
      $printer -> text('Sisa Bayar = '.$s_3.$sisa_bayar);
      $printer -> feed();
      $printer -> feed();
      $printer -> text('Dibayar = '.$s_4.num_to_price($billing->billing_payment));
      $printer -> feed();
      $printer -> text('Kembalian = '.$s_5.num_to_price($billing->billing_change));
      $printer -> feed();
      $printer -> feed();
      $printer -> text('Terimakasih atas kunjungan anda.');
      //
      $printer -> feed();
      $printer -> feed();
      $printer -> feed();
      $printer -> feed();

      /* Close printer */
      $printer -> close();
    } catch (Exception $e) {
      echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
    }
    //
    if ($url !='') {
      redirect(base_url().$url.'/detail/'.$billing_id);
    }else {
      redirect(base_url().'kar_reservation');
    }
  }

  public function delete($id)
  {
    if ($this->access->_delete == 1) {
      $this->m_kar_billing->delete($id);
      $this->session->set_flashdata('status', '<div class="alert alert-success alert-dismissable fade in"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><span class="fa fa-check" aria-hidden="true"></span><span class="sr-only"> Sukses:</span> Data berhasil dihapus!</div>');
      redirect(base_url().'kar_reservation/index');
    } else {
      redirect(base_url().'app_error/error/403');
    }
  }

  public function get_member()
  {
    $member_id = $this->input->post('member_id');
    $data = $this->m_kar_member->get_by_id($member_id);

    echo json_encode($data);
  }

  public function get_room()
  {
    $client = $this->m_kar_client->get_all();
    $room_type_id = $this->input->post('room_type_id');
    $raw = $this->m_kar_room->get_by_room_type_id($room_type_id);
    $room_type = $this->m_kar_room_type->get_by_id($room_type_id);
    
    $add = 0;
    if ($client->client_is_taxed == 1) {
      $charge_type = $this->m_kar_charge_type->get_all();
      foreach ($charge_type as $row) {
        $add += $room_type->room_type_charge*$row->charge_type_ratio/100;
      }
    }

    $room_type->room_type_charge += $add;
    $room_type->room_type_charge = round($room_type->room_type_charge,0,PHP_ROUND_HALF_UP);

    $data = array(
      'room' => array(),
      'room_type' => $room_type
    );

    if ($raw == null) {
      array_push($data['room'], array('id' => '0', 'text' => '-- Pilih Room --'));
    } else {
      array_push($data['room'], array('id' => '0', 'text' => '-- Pilih Room --'));
      foreach ($raw as $row) {
        $ket = ($row->billing_room_id == null) ? '' : '(Sudah Digunakan)';
        array_push($data['room'], array('id' => $row->room_id, 'text' => $row->room_name.' '.$ket));
      }
    }
    
    echo json_encode($data);
  }

  public function get_validate_room()
  {
    $room_id = $this->input->get('room_id');
    $billing_date_in = $this->input->get('billing_date_in');
    $validate = $this->m_kar_reservation->validate_room_id($room_id, $billing_date_in);
    //
    $result = "true";
    if($validate == true) $result = "false";
    //
    echo json_encode(array(
      'result' => $result
    ));
  }

  public function get_tamu_langganan()
  {
    $client = $this->m_kar_client->get_all();
    $guest_id = $this->input->post('guest_id');
    $guest = $this->m_kar_guest->get_by_id($guest_id);

    $guest->guest_name = $guest->guest_name;
    if ($guest->guest_gender == 'L') {
      $guest_gender = "Laki-laki";
    }else{
      $guest_gender = "Perempuan";
    }
    $guest->guest_gender = $guest_gender;
    $guest->guest_phone = $guest->guest_phone;
    $guest->guest_id_type = $guest->guest_id_type;
    $guest->guest_id_no = $guest->guest_id_no;
    if ($guest->guest_id_type == '1') {
      $guest_name = "(Tidak Ada)";
    }elseif ($guest->guest_id_type == '2') {
      $guest_name = "(KTP)";
    }elseif ($guest->guest_id_type == '3') {
      $guest_name = "(SIM)";
    }elseif ($guest->guest_id_type == '4') {
      $guest_name = "(Lainnya)";
    }
    $guest->guest_id_type_name = $guest_name;

    $data = array(
      'guest' => $guest
    );
    
    echo json_encode($data);
  }

  public function add_room()
  {
    $data = $_POST;

    $client = $this->m_kar_client->get_all();
    $tax = $this->m_kar_charge_type->get_by_id(1);
    $room = $this->m_kar_reservation->room_detail($data['room_id']);
    $discount = $this->m_kar_discount->get_by_id($data['discount_id_room']);
    
    if ($client->client_is_taxed == 0) {
      // Setingan harga sebelum pajak
      $room_type_charge = price_to_num($data['room_type_charge']);
      // Hitung maju
      $room_type_subtotal = price_to_num($data['room_type_total']);
      // $room_type_tax += $room_type_subtotal * $tax->charge_type_ratio;
      $room_type_tax += $room_type_subtotal * ($tax->charge_type_ratio/100);
      $room_type_before_discount = $room_type_subtotal + $room_type_tax;
    } else {
      // Settingan harga setelah pajak
      $room_type_before_discount = price_to_num($data['room_type_total']);
      // hitung persen semua setelah pajak/ hitung mundur
      $room_type_tax = ($tax->charge_type_ratio/(100 + $tax->charge_type_ratio))*$room_type_before_discount;
      $room_type_subtotal = $room_type_before_discount - $room_type_tax;
      $room_type_charge = $room_type_subtotal / $data['room_type_duration'];
    }

    $room_type_discount = $discount->discount_amount*$room_type_before_discount/100;
    $room_type_total = $room_type_before_discount-$room_type_discount;

    $data_room = array(
      'billing_id' => $data['billing_id'],
      'room_id' => $room->room_id,
      'room_name' => $room->room_name,
      'room_type_id' => $room->room_type_id,
      'room_type_name' => $room->room_type_name,
      'room_type_charge' => $room_type_charge,
      'discount_id' => $discount->discount_id,
      'discount_type' => $discount->discount_type,
      'discount_amount' => $discount->discount_amount,
      'room_type_subtotal' => $room_type_subtotal,
      'room_type_tax' => $room_type_tax,
      'room_type_before_discount' => $room_type_before_discount,
      'room_type_discount' => $room_type_discount,
      'room_type_total' => $room_type_total,
      'room_type_duration' => $data['room_type_duration'],
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->add_room($data_room);
  }

  public function update_room()
  {
    $data = $_POST;
    $id = $data['billing_room_id'];

    $client = $this->m_kar_client->get_all();
    // Pajak
    $tax = $this->m_kar_charge_type->get_by_id(1);

    $room = $this->m_kar_reservation->room_detail($data['room_id']);
    $discount = $this->m_kar_discount->get_by_id($data['discount_id_room']);
    
    if ($client->client_is_taxed == 0) {
      // Setingan harga sebelum pajak
      $room_type_charge = price_to_num($data['room_type_charge']);
      // Hitung maju
      $room_type_subtotal = price_to_num($data['room_type_total']);
      // $room_type_tax += $room_type_subtotal * $tax->charge_type_ratio;
      $room_type_tax += $room_type_subtotal * ($tax->charge_type_ratio/100);

      $room_type_before_discount = $room_type_subtotal + $room_type_tax;
    } else {
      // Settingan harga setelah pajak
      $room_type_before_discount = price_to_num($data['room_type_total']);
      // hitung persen semua setelah pajak/ hitung mundur
      $room_type_tax = ($tax->charge_type_ratio/(100 + $tax->charge_type_ratio))*$room_type_before_discount;

      $room_type_subtotal = $room_type_before_discount - $room_type_tax;
      $room_type_charge = $room_type_subtotal / $data['room_type_duration'];
    }

    $room_type_discount = $discount->discount_amount*$room_type_before_discount/100;
    $room_type_total = $room_type_before_discount-$room_type_discount;

    $data_room = array(
      'billing_id' => $data['billing_id'],
      'room_type_charge' => $room_type_charge,
      'discount_id' => $discount->discount_id,
      'discount_type' => $discount->discount_type,
      'discount_amount' => $discount->discount_amount,
      'room_type_subtotal' => $room_type_subtotal,
      'room_type_tax' => $room_type_tax,
      'room_type_before_discount' => $room_type_before_discount,
      'room_type_discount' => $room_type_discount,
      'room_type_total' => $room_type_total,
      'room_type_duration' => $data['room_type_duration'],
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->update_room($id,$data_room);
  }

  public function room_list()
  {
    $billing_id = $this->input->post('billing_id');
    $data = $this->m_kar_reservation->room_list($billing_id);

    echo json_encode($data);  
  }

  public function get_billing_room()
  {
    $data = $_POST;
    
    $client = $this->m_kar_client->get_all();
    $billing_id = $this->input->post('billing_id');
    $data2['room'] = $this->m_kar_reservation->get_billing_room($billing_id);
    $data2['client_is_taxed'] = $client->client_is_taxed;

    echo json_encode($data2);
  }

  public function update_room_show()
  {
    $data['tax'] = $this->m_kar_charge_type->get_by_id(1);
    //
    $id = $this->input->post('billing_room_id');
    $data = $this->m_kar_billing_room->get_by_id($id);
    echo json_encode($data);
  }

  public function delete_room() 
  {
    $id = $this->input->post('billing_room_id');
    $this->m_kar_reservation->delete_room($id);
  }

  public function get_extra()
  {
    $extra_id = $this->input->post('extra_id');
    $client = $this->m_kar_client->get_all();
    $data = $this->m_kar_extra->get_by_id($extra_id);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 1) {
      $data->extra_charge += $data->extra_charge*$tax->charge_type_ratio/100;
    }

    $data->extra_charge = round($data->extra_charge,0,PHP_ROUND_HALF_UP);

    echo json_encode($data);
  }

  public function add_extra()
  {
    $data = $_POST;
    $client = $this->m_kar_client->get_all();
    $extra = $this->m_kar_extra->get_by_id($data['extra_id']);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 0) {
      $extra_charge = price_to_num($data['extra_charge']);
      $extra_subtotal = $data['extra_amount']*$extra_charge;
      $extra_tax = $extra_subtotal*$tax->charge_type_ratio/100;
      $extra_total = $extra_subtotal+$extra_tax;
    }else{
      $extra_total = $data['extra_amount']*price_to_num($data['extra_charge']);
      $tot_ratio = 100+$tax->charge_type_ratio;
      $extra_tax = ($tax->charge_type_ratio/$tot_ratio)*$extra_total;
      $extra_subtotal = $extra_total-$extra_tax;
      $extra_charge = $extra_subtotal/$data['extra_amount'];
    }

    $data_extra = array(
      'billing_id' => $data['billing_id'],
      'extra_id' => $extra->extra_id,
      'extra_name' => $extra->extra_name,
      'extra_charge' => $extra_charge,
      'extra_amount' => $data['extra_amount'],
      'extra_subtotal' => $extra_subtotal,
      'extra_tax' => $extra_tax,
      'extra_total' => $extra_total,
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->add_extra($data_extra);
  }

  public function get_billing_extra()
  {
    $billing_id = $this->input->post('billing_id');
    $client = $this->m_kar_client->get_all();
    $data['extra'] = $this->m_kar_reservation->get_billing_extra($billing_id);
    $data['client_is_taxed'] = $client->client_is_taxed;

    echo json_encode($data);
  }

  public function delete_extra()
  {
    $id = $this->input->post('billing_extra_id');
    $this->m_kar_reservation->delete_extra($id);
  }

  public function get_service()
  {
    $service_id = $this->input->post('service_id');
    $client = $this->m_kar_client->get_all();
    $data = $this->m_kar_service->get_by_id($service_id);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 1) {
      $data->service_charge += $data->service_charge*$tax->charge_type_ratio/100;
    }

    $data->service_charge = round($data->service_charge,0,PHP_ROUND_HALF_UP);

    echo json_encode($data);
  }

  public function add_service()
  {
    $data = $_POST;
    $client = $this->m_kar_client->get_all();
    $service = $this->m_kar_service->get_by_id($data['service_id']);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 0) {
      $service_charge = price_to_num($data['service_charge']);
      $service_subtotal = $data['service_amount']*$service_charge;
      $service_tax = $service_subtotal*$tax->charge_type_ratio/100;
      $service_total = $service_subtotal+$service_tax;
    }else{
      $service_total = $data['service_amount']*price_to_num($data['service_charge']);
      $tot_ratio = 100+$tax->charge_type_ratio;
      $service_tax = ($tax->charge_type_ratio/$tot_ratio)*$service_total;
      $service_subtotal = $service_total-$service_tax;
      $service_charge = $service_subtotal/$data['service_amount'];
    }

    $data_service = array(
      'billing_id' => $data['billing_id'],
      'service_id' => $service->service_id,
      'service_name' => $service->service_name,
      'service_charge' => $service_charge,
      'service_amount' => $data['service_amount'],
      'service_subtotal' => $service_subtotal,
      'service_tax' => $service_tax,
      'service_total' => $service_total,
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->add_service($data_service);
  }

  public function get_billing_service()
  {
    $billing_id = $this->input->post('billing_id');
    $client = $this->m_kar_client->get_all();
    $data['service'] = $this->m_kar_reservation->get_billing_service($billing_id);
    $data['client_is_taxed'] = $client->client_is_taxed;

    echo json_encode($data);
  }

  public function update_service_show()
  {
    $id = $this->input->post('billing_service_id');
    $data = $this->m_kar_billing_service->get_by_id($id);
    echo json_encode($data);
  }

  public function update_service()
  {
    $data = $_POST;
    $id = $data['billing_service_id'];

    $client = $this->m_kar_client->get_all();
    // $service = $this->m_kar_service->get_by_id($data['service_id']);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 0) {
      $service_charge = price_to_num($data['service_charge']);
      $service_subtotal = $data['service_amount']*$service_charge;
      $service_tax = $service_subtotal*$tax->charge_type_ratio/100;
      $service_total = $service_subtotal+$service_tax;
    }else{
      $service_total = $data['service_amount']*price_to_num($data['service_charge']);
      $tot_ratio = 100+$tax->charge_type_ratio;
      $service_tax = ($tax->charge_type_ratio/$tot_ratio)*$service_total;
      $service_subtotal = $service_total-$service_tax;
      $service_charge = $service_subtotal/$data['service_amount'];
    }

    $data_service = array(
      'billing_id' => $data['billing_id'],
      'service_charge' => $service_charge,
      'service_amount' => $data['service_amount'],
      'service_subtotal' => $service_subtotal,
      'service_tax' => $service_tax,
      'service_total' => $service_total,
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->update_service($id,$data_service);
  }

  public function delete_service()
  {
    $id = $this->input->post('billing_service_id');
    $this->m_kar_reservation->delete_service($id);
  }



  public function get_paket()
  {
    $paket_id = $this->input->post('paket_id');
    $client = $this->m_kar_client->get_all();
    $data = $this->m_kar_paket->get_by_id($paket_id);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 1) {
      $data->paket_charge += $data->paket_charge*$tax->charge_type_ratio/100;
    }

    $data->paket_charge = round($data->paket_charge,0,PHP_ROUND_HALF_UP);

    echo json_encode($data);
  }

  public function add_paket()
  {
    $data = $_POST;
    $client = $this->m_kar_client->get_all();
    $paket = $this->m_kar_paket->get_by_id($data['paket_id']);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 0) {
      $paket_charge = price_to_num($data['paket_charge']);
      $paket_subtotal = $data['paket_amount']*$paket_charge;
      $paket_tax = $paket_subtotal*$tax->charge_type_ratio/100;
      $paket_total = $paket_subtotal+$paket_tax;
    }else{
      $paket_total = $data['paket_amount']*price_to_num($data['paket_charge']);
      $tot_ratio = 100+$tax->charge_type_ratio;
      $paket_tax = ($tax->charge_type_ratio/$tot_ratio)*$paket_total;
      $paket_subtotal = $paket_total-$paket_tax;
      $paket_charge = $paket_subtotal/$data['paket_amount'];
    }

    $data_paket = array(
      'billing_id' => $data['billing_id'],
      'paket_id' => $paket->paket_id,
      'paket_name' => $paket->paket_name,
      'paket_charge' => $paket_charge,
      'paket_amount' => $data['paket_amount'],
      'paket_subtotal' => $paket_subtotal,
      'paket_tax' => $paket_tax,
      'paket_total' => $paket_total,
      'room_id' => $data['room_id'],
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->add_paket($data_paket);
  }

  public function get_billing_paket()
  {
    $billing_id = $this->input->post('billing_id');
    $client = $this->m_kar_client->get_all();
    $data['paket'] = $this->m_kar_reservation->get_billing_paket($billing_id);
    $data['client_is_taxed'] = $client->client_is_taxed;

    echo json_encode($data);
  }

  public function update_paket_show()
  {
    $id = $this->input->post('billing_paket_id');
    $data = $this->m_kar_billing_paket->get_by_id($id);
    echo json_encode($data);
  }

  public function update_paket()
  {
    $data = $_POST;
    $id = $data['billing_paket_id'];

    $client = $this->m_kar_client->get_all();
    // $paket = $this->m_kar_paket->get_by_id($data['paket_id']);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 0) {
      $paket_charge = price_to_num($data['paket_charge']);
      $paket_subtotal = $data['paket_amount']*$paket_charge;
      $paket_tax = $paket_subtotal*$tax->charge_type_ratio/100;
      $paket_total = $paket_subtotal+$paket_tax;
    }else{
      $paket_total = $data['paket_amount']*price_to_num($data['paket_charge']);
      $tot_ratio = 100+$tax->charge_type_ratio;
      $paket_tax = ($tax->charge_type_ratio/$tot_ratio)*$paket_total;
      $paket_subtotal = $paket_total-$paket_tax;
      $paket_charge = $paket_subtotal/$data['paket_amount'];
    }

    $data_paket = array(
      'billing_id' => $data['billing_id'],
      'paket_charge' => $paket_charge,
      'paket_amount' => $data['paket_amount'],
      'paket_subtotal' => $paket_subtotal,
      'paket_tax' => $paket_tax,
      'paket_total' => $paket_total,
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->update_paket($id,$data_paket);
  }

  public function delete_paket()
  {
    $id = $this->input->post('billing_paket_id');
    $this->m_kar_reservation->delete_paket($id);
  }



  public function get_fnb()
  {
    $fnb_id = $this->input->post('fnb_id');
    $data = $this->m_kar_fnb->get_by_id($fnb_id);    
    $client = $this->m_kar_client->get_all();
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 1) {
      $data->fnb_charge += $data->fnb_charge*$tax->charge_type_ratio/100;
    }

    $data->fnb_charge = round($data->fnb_charge,0,PHP_ROUND_HALF_UP);

    echo json_encode($data);
  }

  public function add_fnb()
  {
    $data = $_POST;
    $client = $this->m_kar_client->get_all();
    $fnb = $this->m_kar_fnb->get_by_id($data['fnb_id']);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 0) {
      $fnb_charge = price_to_num($data['fnb_charge']);
      $fnb_subtotal = $data['fnb_amount']*$fnb_charge;
      $fnb_tax = $fnb_subtotal*$tax->charge_type_ratio/100;
      $fnb_total = $fnb_subtotal+$fnb_tax;
    }else{
      $fnb_total = $data['fnb_amount']*price_to_num($data['fnb_charge']);
      $tot_ratio = 100+$tax->charge_type_ratio;
      $fnb_tax = ($tax->charge_type_ratio/$tot_ratio)*$fnb_total;
      $fnb_subtotal = $fnb_total-$fnb_tax;
      $fnb_charge = $fnb_subtotal/$data['fnb_amount'];
    }

    $data_fnb = array(
      'billing_id' => $data['billing_id'],
      'fnb_id' => $fnb->fnb_id,
      'fnb_name' => $fnb->fnb_name,
      'fnb_charge' => $fnb_charge,
      'fnb_amount' => $data['fnb_amount'],
      'fnb_subtotal' => $fnb_subtotal,
      'fnb_tax' => $fnb_tax,
      'fnb_total' => $fnb_total,
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->add_fnb($data_fnb);
  }

  public function get_billing_fnb()
  {
    $billing_id = $this->input->post('billing_id');
    $client = $this->m_kar_client->get_all();
    $data['fnb'] = $this->m_kar_reservation->get_billing_fnb($billing_id);
    $data['client_is_taxed'] = $client->client_is_taxed;

    echo json_encode($data);
  }

  public function update_fnb_show()
  {
    $id = $this->input->post('billing_fnb_id');
    $data = $this->m_kar_billing_fnb->get_by_id($id);
    echo json_encode($data);
  }

  public function update_fnb()
  {
    $data = $_POST;
    $id = $data['billing_fnb_id'];

    $client = $this->m_kar_client->get_all();
    // $fnb = $this->m_kar_fnb->get_by_id($data['fnb_id']);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 0) {
      $fnb_charge = price_to_num($data['fnb_charge']);
      $fnb_subtotal = $data['fnb_amount']*$fnb_charge;
      $fnb_tax = $fnb_subtotal*$tax->charge_type_ratio/100;
      $fnb_total = $fnb_subtotal+$fnb_tax;
    }else{
      $fnb_total = $data['fnb_amount']*price_to_num($data['fnb_charge']);
      $tot_ratio = 100+$tax->charge_type_ratio;
      $fnb_tax = ($tax->charge_type_ratio/$tot_ratio)*$fnb_total;
      $fnb_subtotal = $fnb_total-$fnb_tax;
      $fnb_charge = $fnb_subtotal/$data['fnb_amount'];
    }

    $data_fnb = array(
      'billing_id' => $data['billing_id'],
      'fnb_charge' => $fnb_charge,
      'fnb_amount' => $data['fnb_amount'],
      'fnb_subtotal' => $fnb_subtotal,
      'fnb_tax' => $fnb_tax,
      'fnb_total' => $fnb_total,
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->update_fnb($id,$data_fnb);
  }

  public function delete_fnb()
  {
    $id = $this->input->post('billing_fnb_id');
    $this->m_kar_reservation->delete_fnb($id);
  }

  public function get_non_tax()
  {
    $non_tax_id = $this->input->post('non_tax_id');
    $data = $this->m_kar_non_tax->get_by_id($non_tax_id);    
    $client = $this->m_kar_client->get_all();
    $tax = $this->m_kar_charge_type->get_by_id(1);

    echo json_encode($data);
  }

  public function add_non_tax()
  {
    $data = $_POST;
    $client = $this->m_kar_client->get_all();
    $non_tax = $this->m_kar_non_tax->get_by_id($data['non_tax_id']);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    $non_tax_charge = price_to_num($data['non_tax_charge']);
    $non_tax_total = $non_tax_charge*$data['non_tax_amount'];
    
    $data_non_tax = array(
      'billing_id' => $data['billing_id'],
      'non_tax_id' => $non_tax->non_tax_id,
      'non_tax_name' => $non_tax->non_tax_name,
      'non_tax_charge' => $non_tax_charge,
      'non_tax_amount' => $data['non_tax_amount'],
      'non_tax_total' => $non_tax_total,
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->add_non_tax($data_non_tax);
  }

  public function get_billing_non_tax()
  {
    $billing_id = $this->input->post('billing_id');
    $client = $this->m_kar_client->get_all();
    $data['non_tax'] = $this->m_kar_reservation->get_billing_non_tax($billing_id);
    $data['client_is_taxed'] = $client->client_is_taxed;

    echo json_encode($data);
  }

  public function update_non_tax_show()
  {
    $client = $this->m_kar_client->get_all();
    $data['client_is_taxed'] = $client->client_is_taxed;
    //
    $id = $this->input->post('billing_non_tax_id');
    $data = $this->m_kar_reservation->get_by_id($id);
    echo json_encode($data);
  }

  public function update_non_tax()
  {
    $data = $_POST;
    $id = $data['billing_non_tax_id'];

    $client = $this->m_kar_client->get_all();
    // $non_tax = $this->m_kar_non_tax->get_by_id($data['non_tax_id']);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    $non_tax_charge = price_to_num($data['non_tax_charge']);
    $non_tax_total = $non_tax_charge*$data['non_tax_amount'];

    $data_non_tax = array(
      'billing_non_tax_id' => $data['billing_non_tax_id'],
      'billing_id' => $data['billing_id'],
      'non_tax_charge' => $non_tax_charge,
      'non_tax_amount' => $data['non_tax_amount'],
      'non_tax_total' => $non_tax_total,
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->update_non_tax($id,$data_non_tax);
  }

  public function delete_non_tax()
  {
    $id = $this->input->post('billing_non_tax_id');
    $this->m_kar_reservation->delete_non_tax($id);
  }

  public function get_custom()
  {
    $custom_id = $this->input->post('custom_id');
    $client = $this->m_kar_client->get_all();
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 1) {
      $data->custom_charge += $data->custom_charge*$tax->charge_type_ratio/100;
    }

    $data->custom_charge = round($data->custom_charge,0,PHP_ROUND_HALF_UP);

    echo json_encode($data);
  }

  public function add_custom()
  {
    $data = $_POST;
    $client = $this->m_kar_client->get_all();
    $tax = $this->m_kar_charge_type->get_by_id(1);

    if ($client->client_is_taxed == 0) {
      $custom_charge = price_to_num($data['custom_charge']);
      $custom_subtotal = $data['custom_amount']*$custom_charge;
      $custom_tax = 0;//$custom_subtotal*$tax->charge_type_ratio/100;
      $custom_total = $custom_subtotal+$custom_tax;
    }else{
      $custom_total = $data['custom_amount']*price_to_num($data['custom_charge']);
      $tot_ratio = 100+$tax->charge_type_ratio;
      $custom_tax = 0;//($tax->charge_type_ratio/$tot_ratio)*$custom_total;
      $custom_subtotal = $custom_total-$custom_tax;
      $custom_charge = $custom_subtotal/$data['custom_amount'];
    }

    $data_custom = array(
      'billing_id' => $data['billing_id'],
      'custom_id' => '99',
      'custom_name' => $data['custom_name'],
      'custom_charge' => $custom_charge,
      'custom_amount' => $data['custom_amount'],
      'custom_subtotal' => $custom_subtotal,
      'custom_tax' => $custom_tax,
      'custom_total' => $custom_total,
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->add_custom($data_custom);
  }

  public function get_billing_custom()
  {
    $billing_id = $this->input->post('billing_id');
    $client = $this->m_kar_client->get_all();
    $data['custom'] = $this->m_kar_reservation->get_billing_custom($billing_id);
    $data['client_is_taxed'] = $client->client_is_taxed;

    echo json_encode($data);
  }

  public function update_custom_show()
  {
    $client = $this->m_kar_client->get_all();
    $data['client_is_taxed'] = $client->client_is_taxed;
    //
    $id = $this->input->post('billing_custom_id');
    $data = $this->m_kar_billing_custom->get_by_id($id);
    echo json_encode($data);
  }

  public function update_custom()
  {
    $data = $_POST;
    $id = $data['billing_custom_id'];

    $client = $this->m_kar_client->get_all();
    // $custom = $this->m_kar_custom->get_by_id($data['custom_id']);
    $tax = $this->m_kar_charge_type->get_by_id(1);

    // if ($client->client_is_taxed == 0) {
    //   $custom_charge = price_to_num($data['custom_charge']);
    //   $custom_subtotal = $data['custom_amount']*$custom_charge;
    //   $custom_tax = $custom_subtotal*$tax->charge_type_ratio/100;
    //   $custom_total = $custom_subtotal+$custom_tax;
    // }else{
    //   $custom_total = $data['custom_amount']*price_to_num($data['custom_charge']);
    //   $tot_ratio = 100+$tax->charge_type_ratio;
    //   $custom_tax = ($tax->charge_type_ratio/$tot_ratio)*$custom_total;
    //   $custom_subtotal = $custom_total-$custom_tax;
    //   $custom_charge = $custom_subtotal/$data['custom_amount'];
    // }

      $custom_charge = price_to_num($data['custom_charge']);
      $custom_subtotal = $data['custom_amount']*$custom_charge;
      $custom_tax = 0;
      $custom_total = $custom_subtotal+$custom_tax;

    $data_custom = array(
      'billing_id' => $data['billing_id'],
      'custom_charge' => $custom_charge,
      'custom_amount' => $data['custom_amount'],
      'custom_subtotal' => $custom_subtotal,
      'custom_tax' => $custom_tax,
      'custom_total' => $custom_total,
      'created_by' => $this->session->userdata('user_realname')
    );
    $this->m_kar_reservation->update_custom($id,$data_custom);
  }

  public function delete_custom()
  {
    $id = $this->input->post('billing_custom_id');
    $this->m_kar_reservation->delete_custom($id);
  }

  public function get_count()
  {
    $billing_id = $this->input->post('billing_id');
    
    $data['count_room'] = $this->m_kar_reservation->count_room($billing_id);
    $data['count_extra'] = $this->m_kar_reservation->count_extra($billing_id);
    $data['count_service'] = $this->m_kar_reservation->count_service($billing_id);
    $data['count_paket'] = $this->m_kar_reservation->count_paket($billing_id);
    $data['count_fnb'] = $this->m_kar_reservation->count_fnb($billing_id);
    $data['count_non_tax'] = $this->m_kar_reservation->count_non_tax($billing_id);
    $data['count_custom'] = $this->m_kar_reservation->count_custom($billing_id);

    echo json_encode($data);
  }

  public function room_all()
  {
    $data = $this->m_kar_reservation->room_all();
    echo json_encode($data);
  }

  public function form2($room_id,$id = null)
  {
    $data['access'] = $this->access;
    $data['member'] = $this->m_kar_member->get_all();
    $data['room_type'] = $this->m_kar_room_type->get_all();
    $data['extra'] = $this->m_kar_extra->get_all();
    $data['service'] = $this->m_kar_service->get_all();
    $data['paket'] = $this->m_kar_paket->get_all();
    $data['fnb'] = $this->m_kar_fnb->get_all();
    $data['non_tax'] = $this->m_kar_non_tax->get_all();
    $data['charge_type'] = $this->m_kar_charge_type->get_all();
    $data['discount_room'] = $this->m_kar_reservation->discount_room();
    $data['list_member'] = $this->m_kar_guest->get_all();
    if ($id == null) {
      $post = $_POST;
      if ($this->access->_create == 1) {
        $data['title'] = 'Tambah Data Pemesanan';
        $data['action'] = 'insert';
        $data['billing'] = null;
        //make receipt no
        // get last billing
        $last_billing = $this->m_kar_billing->get_last();
        //declare billing variable
        if ($last_billing == null) {
          $data['billing_id'] = 1;
          $data['billing_receipt_no'] = date('ymd').'000001';
          $this->m_kar_reservation->new_billing($data['billing_receipt_no']);
        }else{
          // status billing
          // -1 cancel
          // 0 empty
          // 1 proses
          // 2 complete          
          if ($last_billing->billing_status == 0) {
            $data['billing_id'] = $last_billing->billing_id;
            $data['billing_receipt_no'] = $last_billing->billing_receipt_no;
            // empty detail billing
            $this->m_kar_reservation->empty_detail($data['billing_id']);
          } else {
            // get new last billing
            $data['billing_id'] = $last_billing->billing_id+1;
            if (date('Y-m-d', strtotime($last_billing->created)) != date('Y-m-d')) {
              $data['billing_receipt_no'] = date('ymd').'000001';
            }else{
              $number = substr($last_billing->billing_receipt_no,6,12);
              $number = intval($number)+1;
              $data['billing_receipt_no'] = date('ymd').str_pad($number, 6, '0', STR_PAD_LEFT);
            }
            
            // insert new billing
            $this->m_kar_reservation->new_billing($data['billing_receipt_no']);
          }
        }
        $data['billing_id_name'] = 'TRS-'.$data['billing_receipt_no'];    
        
        $this->view('kar_reservation/form2', $data);
      } else {
        redirect(base_url().'app_error/error/403');
      }
    }else{
      if ($this->access->_update == 1) {
        $data['title'] = 'Ubah Data Extra';
        $data['billing'] = $this->m_kar_billing->get_by_id($id);
        $data['action'] = 'update';
        $data['billing_room'] = $this->m_kar_reservation->get_billing_room($id);
        $this->view('kar_reservation/form2', $data);
      } else {
        redirect(base_url().'app_error/error/403');
      }
    }
  }

}
