<?php $this->load->view('footer');?>
<script type="text/javascript">
function cetak()
{
    var swk = $('select[name=idswk]').val();
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

$('#idpendamping').change(function(){
  var idpendamping = $(this).val();
  <?php
  if($is_koordinator_pendamping) {
	  echo("var nip_koordinator = $('#nip_koordinator').val();");
  }
  ?>
  $.ajax({
    url: "<?=site_url('swk/get_swk');?>",
    type: "POST",
    dataType: "json",
    data:{
		<?php
		if($is_koordinator_pendamping) {
			echo("nip_koordinator:nip_koordinator,");
		}
		?>
        idpendamping:idpendamping
    },
    success:function(res){
      var html = '<option value="">- Pilih SWK -</option>';
      $.each(res,function(i,row){
          html += '<option value="'+row.idswk+'">'+row.nama_swk+'</option>';
      });
      $('#idswk').html(html);
    }
  });
});

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