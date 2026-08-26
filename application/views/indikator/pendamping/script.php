<script type="text/javascript">
$(document).on('change', '.custom-file-input', function () {
    var input = this;
    var preview = $(input)
        .closest('.form-group')
        .children('.preview-file');

    preview.empty().hide();
    if (!input.files || input.files.length === 0) return;

    var file = input.files[0];
    $(input).next('.custom-file-label').text(file.name);
    if (file.type.indexOf('image/') === 0) {
        var reader = new FileReader();
        reader.onload = function(e){
            preview.html('<img src="'+e.target.result+'" class="img-thumbnail" style="max-width:250px;">').show();
        };
        reader.readAsDataURL(file);
    }
    else {
        preview.html('<i class="fa fa-file"></i> '+file.name).show();
    }
});

$('#frm_filter_bulan').submit(function(e){
    e.preventDefault();
    $.ajax({
        url : "<?=base_url('indikator/filter')?>",
        type : "POST",
        data : {
          idswk : $('[name=pilih_swk]').val(),
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
  var form = $(this);
  var formData = new FormData(this);
  $.ajax({
    url : "<?=base_url('indikator/simpan')?>",
    type : "POST",
    data : formData,
    processData : false,
    contentType : false,
    dataType : "json",
    beforeSend:function(){
      form.find('button[type=submit]')
        .prop('disabled',true)
        .html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

    },
    success:function(res){
      if(res.status){
        form.find('button[type=submit]')
          .removeClass('btn-primary')
          .addClass('btn-success')
          .html('<i class="fa fa-check"></i> Tersimpan');

        var indikator = form.find('input[name="indikator"]').val();
        if(indikator === 'tingkat_keterisian_stan'){
            var nilai = form.find('input[name="realisasi[tingkat_keterisian_stan]"]').val();
            $('input[name="target[kelengkapan_administrasi][nib]"]').val(nilai);
            $('input[name="target[kelengkapan_administrasi][sk]"]').val(nilai);
            $('input[name="target[kelengkapan_administrasi][satu_data]"]').val(nilai);
        }
      }
      else{
        alert(res.pesan);
        form.find('button[type=submit]')
          .prop('disabled',false)
          .html('<i class="fa fa-upload"></i> Simpan');
      }
    },
    error: function(){
      Swal.fire({
          icon: 'error',
          title: 'Terjadi Kesalahan',
          text: 'Tidak dapat menghubungi server.',
          confirmButtonText: 'OK'
      });
      form.find('button[type=submit]')
        .prop('disabled',false)
        .html('<i class="fa fa-upload"></i> Simpan');
    }
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
</script>
