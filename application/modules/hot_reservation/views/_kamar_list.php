<div id="modal_room_list" class="modal fade"  role="dialog" aria-labelledby="modal_room_list">
  <div style="width:800px;" class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="title_room_list">Pesanan Kamar</h4>
      </div>
      <div class="modal-body">
        <button class="btn btn-info" id="btn_room"><i class="fa fa-plus"></i> Tambah Kamar</button>
        <br><br>
        <table id="tbl_room_list" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <!-- <th class="text-center">Jenis Kamar</th> -->
              <th class="text-center">Kamar</th>
              <th class="text-center">Tarif</th>
              <th class="text-center">Durasi</th>
              <th class="text-center" width="110">Harga</th>
              <th class="text-center" width="100">Diskon</th>
              <th class="text-center" width="100">Denda</th>
              <th class="text-center" width="100">Total</th>
              <th class="text-center" width="100">Aksi</th>
            </tr>
          </thead>
          <tbody id="row_room_list">
          </tbody>
        </table>
        <em>
          <small>
            NB: 
            <?php if ($client->client_is_taxed == 0): ?>
              Harga belum termasuk 
            <?php else: ?>
              Harga sudah termasuk 
            <?php endif;?>
            <?php foreach ($charge_type as $row){
              echo $row->charge_type_name.',';
            }?>
          </small>
        </em>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-dismiss="modal"><i class="fa fa-check"></i> Selesai</button>
      </div>
    </div>
  </div>
</div>