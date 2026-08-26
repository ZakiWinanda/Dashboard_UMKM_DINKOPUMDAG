<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body pb-0">
                <form id="frm_filter_bulan" method="post">
                    <div class="form-group row">
                        <div class="col-md-3">
							<?php
							if($is_koordinator_pendamping) {
								echo("<input type='hidden' value='".$nip."' name='nip_koordinator' id='nip_koordinator'>");
							}
							?>
                            <select class="form-control select2" name="idpendamping" id="idpendamping">
                                <option value="">- Pilih Pendamping -</option>
                                <?php foreach($pendamping as $row){ ?>
                                    <option value="<?=$row->nik;?>">
                                        <?=$row->nama_lengkap;?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select class="form-control select2" name="idswk" id="idswk">
                                <option value="">- Pilih SWK -</option>
                                <?php foreach($swk as $row){ ?>
                                    <option value="<?=$row->idswk;?>">
                                        <?=$row->nama_swk;?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-sm-3">
              <input type="text" name="filter_bulan_tahun" id="filter_bulan_tahun" data-target="#filter_bulan_tahun" data-toggle="datetimepicker" placeholder="" class="form-control" value="<?=date('m-Y');?>" autocomplete="off" required>
            </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-paper-plane"></i> Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
