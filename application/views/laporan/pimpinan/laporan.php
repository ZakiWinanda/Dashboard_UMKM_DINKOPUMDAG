  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?=$title;?></h1>
          </div>
        </div>
      </div>
    </div>
    <div class="content">
      <div class="container-fluid">
		<div class="row">
    		<div class="col-sm-7">
    			<div class="card">
    			  <div class="card-body">
      				<form id="frm_filter_bulan" enctype="multipart/form-data">
					<?php
					if($is_koordinator_pendamping) {
						echo("<input type='hidden' value='".$nip."' name='nip_koordinator' id='nip_koordinator'>");
					}
					?>
      				  <div class="form-group row mb-2">
						<div class="col-md-6">
							<label>Nama Pendamping</label>
							<select class="form-control select2" name="idpendamping" id="idpendamping" style="width:100%">
								<option value="">- Pilih Pendamping -</option>
								<?php foreach($pendamping as $p){ ?>
								<option value="<?=$p->nik;?>">
									<?=$p->nama_lengkap;?>
								</option>
								<?php } ?>
							</select>
						</div>

						<div class="col-sm-6">
						  <label>Nama SWK</label>
						  <select class="form-control select2" name="idswk" id="idswk" required="true">
							<option value="">- Pilih SWK -</option>
							<?php foreach($swk as $row){ ?>
							  <option value="<?=$row->idswk;?>">
								<?=$row->nama_swk;?>
							  </option>
							<?php } ?>
						  </select>
						</div>
      				</div>
      				<div class="form-group row">
      					<div class="col-sm-5 col-5">
      					  <input type="text" name="bulan_tahun_awal" id="bulan_tahun_awal" data-target="#bulan_tahun_awal" data-toggle="datetimepicker" placeholder="" class="form-control" required="true" value="<?=date('m-Y');?>" autocomplete="off">
      					</div>
      					<div class="col-sm-2 col-2 text-center">
      					s/d
      					</div>
      					<div class="col-sm-5 col-5">
      					  <input type="text" name="bulan_tahun_akhir" id="bulan_tahun_akhir" data-target="#bulan_tahun_akhir" data-toggle="datetimepicker" placeholder="" class="form-control" required="true" value="<?=date('m-Y');?>" autocomplete="off">
      					</div>  
      				  </div>
      				  <button type="button" onclick="cetak()" class="btn btn-primary btn-flat" title="filter"><i class="fa fa-print"></i> Cetak</button>
      				</form>
    			  </div>
    			</div>
    		</div>
		</div>
	  </div>
    </div>
  </div>

<?php $this->load->view('laporan/pimpinan/script');?>
</body>
</html>
