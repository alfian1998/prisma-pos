<div class="content-header">
  <h4>
    <a href="<?=base_url('hot_extra')?>" class="btn btn-success"><i class="fa fa-arrow-left"></i></a> 
    <i class="fa fa-<?=$access->module_icon?>"></i> <?=$title?>
  </h4>
  <small>Kolom dengan tanda <b class="required-field">*</b> wajib diisi!</small>
</div>
<div class="content-body">
  <div class="row">
    <form id="form" class="" action="<?=base_url()?>hot_extra/<?=$action?>" method="post">
      <div class="col-md-6">
        <input class="form-control" type="hidden" name="extra_id" value="<?php if($extra != null){echo $extra->extra_id;}?>">
        <div class="form-group">
          <label>Nama Extra <small class="required-field">*</small></label>
          <input class="form-control keyboard" type="text" name="extra_name" value="<?php if($extra != null){echo $extra->extra_name;}?>">
        </div>
        <div class="row">
          <div class="col-md-5">
            <div class="form-group">
              <label>Harga <small class="required-field">*</small></label>
              <div class="input-group">
                <div class="input-group-addon"><b>Rp</b></div>
                <input class="form-control autonumeric num" type="text" name="extra_charge" value="<?php if($extra != null){echo $extra->extra_charge;}?>">
              </div>
            </div>
          </div>
        </div>
        <small>
          Harga
          <?php if ($client->client_is_taxed == 1) {
            echo 'Sudah Termasuk';
          }else{
            echo 'Belum Termasuk';
          } ?>
          Pajak Hotel
        </small>
        <div class="form-group">
          <label>Aktif?</label><br>
          <input class="" type="checkbox" name="is_active" value="1" <?php if($extra != null){if($extra->is_active == 1){echo 'checked';}}else{echo 'checked';}?>>
        </div>
        <div class="form-group pull-right">
          <a class="btn btn-default" href="<?=base_url()?>hot_extra/index"><i class="fa fa-close"></i> Batal</a>
          <button class="btn btn-info" type="submit"><i class="fa fa-save"></i> Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript">
  $(document).ready(function () {
    $("#form").validate({
      rules: {
        'extra_name': {
          required: true
        },
        'extra_charge': {
          required: true,
          number: true
        }
      },
      messages: {
        'extra_name': {
          required: '<i style="color:red">Wajib diisi!</i>'
        },
        'extra_charge': {
          required: '<i style="color:red">Wajib diisi!</i>',
          number: '<i style="color:red">Harus berupa angka!</i>'
        }
      }
    });
  })

 $('[name=status]').change(function(){
		if($(this).val()==1){
			$('[name=before_tax]').prop('readonly',true);
      $('[name=after_tax]').prop('readonly',false);
     
    }else{
			$('[name=before_tax]').prop('readonly',false);
      $('[name=after_tax]').prop('readonly',true);	
		}
	});

  function findAfter(){
    $('[name=before_tax]').prop('readonly',true);
    var pajak=0;
    var hasil=0;
    var service_hotel=0;
    var sudahx=ind_to_sys($('#sudah').val());
    var sudah=parseFloat(sudahx);
      hasil=(sudah*100)/120;
      pajak=(sudah*10)/120;
    $("#pajak").val(sys_to_ind(pajak.toFixed(0)));
    $("#service_hotel").val(sys_to_ind(pajak.toFixed(0)));
    $("#belum").val(sys_to_ind(hasil.toFixed(0)));
  }

   function findBefore(){
    $('[name=after_tax]').prop('readonly',true);
    var pajak=0;
    var hasil=0;
    var service_hotel=0;
    var belumx=ind_to_sys($('#belum').val());
    var belum=parseFloat(belumx);
      pajak=(belum*10)/100;
      hasil=belum+pajak+pajak;
    $("#pajak").val(sys_to_ind(pajak.toFixed(0)));
    $("#service_hotel").val(sys_to_ind(pajak.toFixed(0)));
    $("#sudah").val(sys_to_ind(hasil.toFixed(0)));
  }
</script>
