        <div class="row">
          <div class="col-sm-8">
            <div class="card">
              <div class="card-body pb-0">
              <form id="frm_filter_bulan" method="post" enctype="multipart/form-data">
                <div class="form-group row">
                <div class="col-sm-5">
                  <select class="form-control" name="pilih_swk" required>
                    <option value="">- Pilih SWK -</option>
                    <?php foreach($swk as $row){ ?>
                      <option value="<?=$row->idswk;?>">
                        <?=$row->nama_swk;?>
                      </option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-sm-5">
                  <input type="text" name="filter_bulan_tahun" id="filter_bulan_tahun" data-target="#filter_bulan_tahun" data-toggle="datetimepicker" placeholder="" class="form-control" value="<?=date('m-Y');?>" autocomplete="off" required>
                </div>
                <div class="col-sm-2">
                  <button type="submit" class="col btn btn-primary btn-flat" title="filter"><i class="fa fa-paper-plane"></i> Submit</button>
                </div>
                </div>
              </form>
              </div>
            </div>
          </div>
        </div>