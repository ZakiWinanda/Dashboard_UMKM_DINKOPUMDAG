<script type="text/javascript">
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

$('#frm_filter_bulan').submit(function(e){
    e.preventDefault();
    $.ajax({
        url : "<?=base_url('indikator/filter')?>",
        type : "POST",
        data : {
          idswk : $('[name=idswk]').val(),
          bulan : $('[name=filter_bulan_tahun]').val()
        },
        dataType : "json",

        beforeSend:function(){

          $('#frm_filter_bulan button')
              .prop('disabled',true);

          var loading = '<i class="fa fa-spinner fa-spin"></i> Memuat...';

          $('#nama_swk').html(loading);
          $('#alamat_swk').html(loading);
          $('#jumlah_stan').html(loading);
          $('#bulan_tahun').html(loading);

          $('#accordion').find('input[type=url]').val('');
          $('#accordion').find('input[type=text]').val('');
          $('#accordion').find('input[type=number]').val('');
          $('#accordion').find('textarea').val('');
          $('#accordion').find('input[type=radio]').prop('checked',false);
          $('.custom-file-label').text('Pilih file...');
          $('.preview-file').html('');

        },
        success:function(res){
          $('#frm_filter_bulan button')
              .prop('disabled',false);

          if(res.status){
            $('#nama_swk').html(res.data.nama_swk);
            $('#alamat_swk').html(res.data.alamat);
            $('#jumlah_stan').html(res.data.stan);
            $('#bulan_tahun').html(res.data.bulan_tahun);

            $('.form-perform input[name="idswk"]').val($('[name=pilih_swk]').val());
            $('.form-perform input[name="bulan"]').val($('[name=filter_bulan_tahun]').val());

            if(res.detail){
              $.each(res.detail, function(indikator, rows){
                $.each(rows, function(i,row){
                  if(row.subindikator!='') {
                    $('[name="target['+indikator+']['+row.subindikator+']"]').val(row.target);
                    $('[name="realisasi['+indikator+']['+row.subindikator+']"]').val(row.realisasi);
                  }
                  else{
                    $('[name="target['+indikator+']"]').val(row.target);
                    $('[name="realisasi['+indikator+']"]').val(row.realisasi);
                  }

                  if(row.nilai_text){
                    $('[name="keterangan['+indikator+']"]').val(row.nilai_text);
                  }

                if(row.nilai_radio){
                    if(indikator=='review_online') {
                      $('[name="status"][value="'+row.nilai_radio+'"]').prop('checked',true);

                        var form = $('input[name="indikator"][value="'+indikator+'"]').closest('form');
                        var preview = form.find('.preview-file');
                        if(row.data_dukung != '' && row.data_dukung != null) {
                            preview.html(
                                '<a href="<?=base_url("assets/uploads/data_dukung/")?>'+row.data_dukung+'" target="_blank">'+
                                    '<img src="<?=base_url("assets/uploads/data_dukung/")?>'+row.data_dukung+'" class="img-thumbnail" style="max-width:250px;">'+
                                '</a>'
                            ).show();

                            form.find('.custom-file-label').text(row.data_dukung);
                        }
                    }
                    else {
                      $('[name="'+indikator+'"][value="'+row.nilai_radio+'"]').prop('checked',true);
                    }
                }
                });
              });

              $('.status-indikator')
                .removeClass('fa-check-circle text-green')
                .addClass('fa-ban text-warning');

              $.each(res.status_indikator, function(indikator, status) {
                var form = $('.form-perform input[name="indikator"][value="' + indikator + '"]')
                    .closest('form');

                var icon = form.closest('.card').find('.status-indikator');
                if (status === true) {
                    icon.removeClass('fa-ban text-warning')
                        .addClass('fa-check-circle text-green');
                } else {
                    icon.removeClass('fa-check-circle text-green')
                        .addClass('fa-ban text-warning');
                }
              });
            }
          }
          else{
            $('#nama_swk').html('-');
            $('#alamat_swk').html('-');
            $('#jumlah_stan').html('-');
            $('#bulan_tahun').html('-');

            $('.status-indikator')
              .removeClass('fa-check-circle text-green')
              .addClass('fa-ban text-warning');

            $.each(res.status_indikator, function(indikator, status) {
              var form = $('.form-perform input[name="indikator"][value="' + indikator + '"]')
                  .closest('form');

              var icon = form.closest('.card').find('.status-indikator');
              if (status === true) {
                  icon.removeClass('fa-ban text-warning')
                      .addClass('fa-check-circle text-green');
              } else {
                  icon.removeClass('fa-check-circle text-green')
                      .addClass('fa-ban text-warning');
              }
            });

            Swal.fire({
              icon:'warning',
              title:'',
              text:res.pesan,
              confirmButtonText:'OK'
            });
          }
        },
        error:function(){
            $('#frm_filter_bulan button')
                .prop('disabled',false);

            $('#nama_swk').html('-');
            $('#alamat_swk').html('-');
            $('#jumlah_stan').html('-');
            $('#bulan_tahun').html('-');

            Swal.fire({
              icon:'error',
              title:'Terjadi Kesalahan',
              text:'Tidak dapat menghubungi server.',
              confirmButtonText:'OK'
            });
        }
    });
});

$('.form-perform').on('submit', function(e){
  e.preventDefault();
  Swal.fire({
    icon:'warning',
    title:'',
    text:"Akses terbatas",
    confirmButtonText:'OK'
  });
});

$('#accordion .collapse').each(function () {
	var icon = $(this)
		.prev('.card-header')
		.find('.toggle-caret');

	if ($(this).hasClass('show')) {
		icon.removeClass('fa-caret-down')
			.addClass('fa-caret-up');
	} else {
		icon.removeClass('fa-caret-up')
			.addClass('fa-caret-down');
	}
});

$('#accordion').on('show.bs.collapse', '.collapse', function () {
	$(this)
		.prev('.card-header')
		.find('.toggle-caret')
		.removeClass('fa-caret-down')
		.addClass('fa-caret-up');
});

$('#accordion').on('hide.bs.collapse', '.collapse', function () {
	$(this)
		.prev('.card-header')
		.find('.toggle-caret')
		.removeClass('fa-caret-up')
		.addClass('fa-caret-down');
});

$('#bulan_tahun').datetimepicker({
  showClose: true,
  showTime: true,
  format: 'MM-YYYY',
});

$('#filter_bulan_tahun').datetimepicker({
  showClose: true,
  showTime: true,
  format: 'MM-YYYY',
});

$('.uang').inputmask('decimal', {
    alias: 'numeric',
    groupSeparator: '.',
    radixPoint: ',',
    digits: 0,
    autoGroup: true,
    removeMaskOnSubmit: true,
    rightAlign: false
});
</script>
