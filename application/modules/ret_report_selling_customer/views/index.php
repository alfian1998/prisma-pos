<div class="content-header">
  <h4><i class="fa fa-<?=$access->module_icon?>"></i> <?=$title?></h4>
</div>
<div class="content-body">
  <div class="row">
    <div class="col-md-2">
      <div class="form-group">
        <?php $type = $this->uri->segment(3); ?>
        <?php if ($type == '') {$type = 'daily';} ?>
        <select class="form-control select2" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
          <option value="<?=base_url()?>ret_report_selling_customer/index/daily" <?php if($type == 'daily'){echo 'selected';}?>>Harian</option>
          <option value="<?=base_url()?>ret_report_selling_customer/index/weekly" <?php if($type == 'weekly'){echo 'selected';}?>>Mingguan</option>
          <option value="<?=base_url()?>ret_report_selling_customer/index/monthly" <?php if($type == 'monthly'){echo 'selected';}?>>Bulanan</option>
          <option value="<?=base_url()?>ret_report_selling_customer/index/annual" <?php if($type == 'annual'){echo 'selected';}?>>Tahunan</option>
          <option value="<?=base_url()?>ret_report_selling_customer/index/range" <?php if($type == 'range'){echo 'selected';}?>>Rentang Waktu</option>
        </select>
      </div>
    </div>
    <?php if ($type == 'daily'): ?>
      <form class="" action="<?=base_url()?>ret_report_selling_customer/action" method="post">
        <div class="col-md-2">
          <div class="form-group">
            <select class="form-control select2" name="customer_id">
              <?php foreach ($customer as $row) { ?>
                <option value="<?=$row->customer_id?>"><?=$row->customer_name?></option>
              <?php }; ?>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <input type="hidden" name="type" value="daily">
          <div class="form-group">
            <div class="input-group">
              <input type="text" class="form-control date-picker" name="date" value="<?=date('d-m-Y')?>">
              <span class="input-group-btn">
                <button class="btn btn-info" type="submit"><i class="fa fa-search"></i> Cari</button>
              </span>
            </div>
          </div>
        </div>
      </form>
    <?php endif; ?>
    <?php if ($type == 'weekly'): ?>
      <form class="" action="<?=base_url()?>ret_report_selling_customer/action" method="post">
        <div class="col-md-2">
          <div class="form-group">
            <select class="form-control select2" name="customer_id">
              <?php foreach ($customer as $row) { ?>
                <option value="<?=$row->customer_id?>"><?=$row->customer_name?></option>
              <?php }; ?>
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <input type="hidden" name="type" value="weekly">
          <div class="form-group">
            <div class="input-group">
              <input type="text" class="form-control week-picker" name="week" value="">
              <span class="input-group-btn">
                <button class="btn btn-info" type="submit"><i class="fa fa-search"></i> Cari</button>
              </span>
            </div>
          </div>
        </div>
      </form>
    <?php endif; ?>
    <?php if ($type == 'monthly'): ?>
      <form class="" action="<?=base_url()?>ret_report_selling_customer/action" method="post">
        <div class="col-md-2">
          <div class="form-group">
            <select class="form-control select2" name="customer_id">
              <?php foreach ($customer as $row) { ?>
                <option value="<?=$row->customer_id?>"><?=$row->customer_name?></option>
              <?php }; ?>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <input type="hidden" name="type" value="monthly">
          <div class="form-group">
            <div class="input-group">
              <input type="text" class="form-control month-picker" name="month" value="<?=date('m-Y')?>">
              <span class="input-group-btn">
                <button class="btn btn-info" type="submit"><i class="fa fa-search"></i> Cari</button>
              </span>
            </div>
          </div>
        </div>
      </form>
    <?php endif; ?>
    <?php if ($type == 'annual'): ?>
      <form class="" action="<?=base_url()?>ret_report_selling_customer/action" method="post">
        <div class="col-md-2">
          <div class="form-group">
            <select class="form-control select2" name="customer_id">
              <?php foreach ($customer as $row) { ?>
                <option value="<?=$row->customer_id?>"><?=$row->customer_name?></option>
              <?php }; ?>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <input type="hidden" name="type" value="annual">
          <div class="form-group">
            <div class="input-group">
              <input type="text" class="form-control year-picker" name="year" value="<?=date('Y')?>">
              <span class="input-group-btn">
                <button class="btn btn-info" type="submit"><i class="fa fa-search"></i> Cari</button>
              </span>
            </div>
          </div>
        </div>
      </form>
    <?php endif; ?>
    <?php if ($type == 'range'): ?>
      <form class="" action="<?=base_url()?>ret_report_selling_customer/action" method="post">
        <div class="col-md-2">
          <div class="form-group">
            <select class="form-control select2" name="customer_id">
              <?php foreach ($customer as $row) { ?>
                <option value="<?=$row->customer_id?>"><?=$row->customer_name?></option>
              <?php }; ?>
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <input type="hidden" name="type" value="range">
          <div class="form-group">
            <div class="input-group">
              <input type="text" class="form-control daterange-picker" name="range" value="<?=date('d-m-Y').' - '.date('d-m-Y')?>">
              <span class="input-group-btn">
                <button class="btn btn-info" type="submit"><i class="fa fa-search"></i> Cari</button>
              </span>
            </div>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>
  <div class="row">
    <div class="col-md-12">

    </div>
  </div>
</div>
