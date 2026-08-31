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
      <div class="container-fluid pb-5 row">
    		<div class="col-sm-7">
    			<div class="card">
    			  <div class="card-body">
      				<form id="frm_filter_bulan" enctype="multipart/form-data">
      				  <div class="form-group row mb-2">
      					<div class="col-sm-12">
                  <select class="form-control" name="pilih_swk" required="true">
                    <option value="">- Pilih <?= (!empty($is_pendamping_kecamatan)) ? 'Kecamatan' : 'SWK' ?> -</option>
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

<?php $this->load->view('footer');?>
<script type="text/javascript">
function cetak()
{
    var swk = $('select[name=pilih_swk]').val();
    if(swk=='') {
      Swal.fire('Gagal','Silahkan pilih SWK','warning');
      return;
    }
    $.ajax({
        url : "<?=base_url('laporan/enkripsi')?>",
        type : "POST",
        dataType : "json",
        data : {
            bulan_awal  : $('#bulan_tahun_awal').val(),
            bulan_akhir : $('#bulan_tahun_akhir').val(),
            swk: swk
        },
        success:function(r){
            if(r.status){
                window.open("<?=base_url('laporan/')?>"+r.param,"_blank");
            }else{
                Swal.fire('Gagal',r.pesan,'warning');
            }
        }
    });
}


$('#bulan_tahun_awal').datetimepicker({
  showClose: true,
  showTime: true,
  format: 'MM-YYYY',
});

$('#bulan_tahun_akhir').datetimepicker({
  showClose: true,
  showTime: true,
  format: 'MM-YYYY',
});
</script>
</body>
</html>
