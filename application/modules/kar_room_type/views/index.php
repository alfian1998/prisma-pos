<?php
	function digit($inp = 0)
	{
	    return number_format($inp, 0, ',', '.');
	}
?>
<div class="content-header">
  <h4><i class="fa fa-<?=$access->module_icon?>"></i> <?=$title?></h4>
</div>
<div class="content-body">
  <div class="row">
    <div class="col-md-5">
      <a class="btn btn-info" href="<?=base_url()?>kar_room_type/form"><i class="fa fa-plus"></i> Tambah Tipe Room (Kategori Room)</a>
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalInformation">
        <i class="fa fa-question"></i> Informasi
      </button>
    </div>
    <div class="col-md-4 pull-right">
      <form class="" action="<?=base_url()?>kar_room_type/index" method="post">
        <div class="form-group">
          <div class="input-group">
            <input type="text" class="form-control keyboard" name="search_term" placeholder="Pencarian..." value="<?php echo $this->session->userdata('search_term');?>">
            <span class="input-group-btn">
              <button class="btn btn-info" type="submit"><i class="fa fa-search"></i></button>
              <a class="btn btn-default" href="<?=base_url()?>kar_room_type/reset_search"><i class="fa fa-refresh"></i></a>
            </span>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <?php if ($this->session->userdata('search_term')): ?>
        <i class="search_result">Hasil pencarian dengan kata kunci: <b><?=$this->session->userdata('search_term');?></b></i><br><br>
      <?php endif; ?>
      <?php echo $this->session->flashdata('status'); ?>
      <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed">
          <thead>
            <tr>
              <th class="text-center" width="50">No</th>
              <th class="text-center" width="100">Aksi</th>
              <th class="text-center">Nama Tipe Room (Kategori Room)</th>
              <th class="text-center" width="150">Jumlah Room</th>
              <th class="text-center" width="150">Harga</th>
              <th class="text-center" width="80">Aktif</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($room_type != null): ?>
              <?php 
                $tot_ratio = 100;
                if ($client->client_is_taxed == 1) {
                  foreach ($charge_type as $row) {
                    $tot_ratio += $row->charge_type_ratio;
                  }
                }
              ?>
              <?php 
              $i=1;foreach ($room_type as $row): 
              $number_of_room = $this->m_kar_room_type->get_list_room_by_type_id($row->room_type_id);
              ?>
                <tr>
                  <td class="text-center"><?=$this->uri->segment('3')+$i++?></td>
                  <td class="text-center">
                    <?php if ($row->room_type_id > 0 ): ?>
                      <a class="btn btn-xs btn-warning" href="<?=base_url()?>kar_room_type/form/<?=$row->room_type_id?>"><i class="fa fa-pencil"></i></a>
                      <button class="btn btn-xs btn-danger" onclick="del('<?=$row->room_type_id?>');"><i class="fa fa-trash"></i></button>
                    <?php endif; ?>
                  </td>
                  <td><?=$row->room_type_name?></td> 
                  <td class="text-center"><?=$number_of_room?></td> 
                  <td><?=num_to_idr(round(($tot_ratio/100)*$row->room_type_charge),0,PHP_ROUND_HALF_UP)?></td> 
                  <td class="text-center">
                    <?php if ($row->is_active == 1): ?>
                      <i class="fa fa-check cl-success"></i>
                    <?php else: ?>
                      <i class="fa fa-close cl-danger"></i>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td class="text-center" colspan="5">Tidak ada data!</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        <div class="pull-right">
          <?php echo $this->pagination->create_links(); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Delete -->
<div id="modal_delete" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Hapus Data</h4>
      </div>
      <div class="modal-body">
        <p>Anda yakin ingin menghapus data ini?</p>
        <b class="cl-danger">Peringatan!</b>
        <p>Data ini mungkin digunakan atau terhubung dengan data lain.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Batal</button>
        <button id="btn_delete_action" type="button" class="btn btn-danger"><i class="fa fa-trash"></i> Hapus</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
  function del(id) {
    $("#modal_delete").modal('show');

    $("#btn_delete_action").click(function () {
      window.location = "<?=base_url()?>kar_room_type/delete/"+id;
    })
  }
</script>

<!-- Modal -->
<div class="modal fade" id="modalInformation" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Informasi Menu Tipe Room (Kategori Room)</h4>
      </div>
      <div class="modal-body" style="font-size: 15px;">
        <ul style="margin-left: -22px;">
          <li>Menu ini digunakan untuk memanajemen Tipe Room (Kategori Room)</li>
          <li>Ketika Anda mengisi kolom Jumlah Room di Form Tambah Tipe Room maka di menu Room akan otomatis terisi sama dengan Jumlah Room yang Anda isikan</li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> <b>Tutup</b></button>
      </div>
    </div>
  </div>
</div>