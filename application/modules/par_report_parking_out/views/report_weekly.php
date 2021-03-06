<div class="content-header">
  <a class="btn btn-success pull-right" target="_blank" href="<?=base_url()?>par_report_parking_out/report_weekly_pdf/<?=$date_start?>/<?=$date_end?>"><i class="fa fa-file-pdf-o"></i> Download PDF</a>
  <h4><i class="fa fa-<?=$access->module_icon?>"></i> <?=$title?></h4>
</div>
<div class="content-body">
  <table class="table table-bordered table-condensed table-striped">
    <thead>
      <tr>
        <th class="text-center" width="80">No. Karcis</th>
        <th class="text-center">Kategori</th>
        <th class="text-center">Merek</th>
        <th class="text-center">TNKB</th>
        <th class="text-center" width="150">Masuk</th>
        <th class="text-center">Petugas</th>
        <th class="text-center" width="150">Keluar</th>
        <th class="text-center">Petugas</th>
        <th class="text-center" width="50">Durasi</th>
        <th class="text-center" width="150">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($billing as $row): ?>
        <tr>
          <td>TXP-<?=$row->receipt_no?></td>
          <td><?=$row->category_name?></td>
          <td><?=$row->brand_name?></td>
          <td class="text-center"><?=$row->billing_tnkb?></td>
          <td class="text-center"><?=date_to_ind($row->billing_date_in).' '.$row->billing_time_in?></td>
          <td class="text-center"><?=$row->user_realname_in?></td>
          <td class="text-center"><?=date_to_ind($row->billing_date_out).' '.$row->billing_time_out?></td>
          <td class="text-center"><?=$row->user_realname_out?></td>
          <td class="text-center"><?=$row->billing_duration?> jam</td>
          <td><?=num_to_idr($row->billing_total_grand)?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
