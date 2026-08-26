  <div class="content-wrapper pb-5">
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
        <div class="card">
          <div class="card-body table-responsive">
            <div class="form-group">
              <button class="btn btn-primary btn-flat btn-sm" onclick="tambah()"><i class="fa fa-plus"></i> Tambah</button>
            </div>
            <table id="tabel" class="table table-bordered table-sm">
              <thead class="text-center">
                <tr>
                  <th>NO</th>
                  <th>NIP / NIK</th>
                  <th>NAMA KOORDINATOR</th>
                  <th>JUMLAH PENDAMPING</th>
                  <th>PENDAMPING</th>
                  <th>AKSI</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

<div class="modal fade" id="modalPengawas" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formPengawas">
        <input type="hidden" id="mode" name="mode">
        <div class="modal-header bg-primary">
          <h5 class="modal-title" id="modalPengawasLabel">Tambah Pengawas</h5>
          <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label>Pengawas</label>
            <select class="form-control select2" name="nip_koordinator" id="nip_koordinator" required style="width: 100%">
              <option value="">Pilih Koordinator</option>
              <?php
              $koordinator_pendamping = $this->db
                ->where('role','pendamping')
                ->order_by('nama_lengkap','ASC')
                ->get('m_users')
                ->result();
              foreach($koordinator_pendamping as $p){
              ?>
                  <option value="<?=$p->nik?>"><?=$p->nama_lengkap?> (<?=$p->nik?>)</option>
              <?php } ?>
            </select>
            <input type="hidden" name="nip" id="nip_hidden">
          </div>

          <div class="form-group">
            <label>PENDAMPING</label>
            <select class="form-control" name="nip_pendamping[]" id="nip_pendamping" multiple required>
              <?php
              $pendamping = $this->db
                ->where('role','pendamping')
                ->order_by('nama_lengkap','ASC')
                ->get('m_users')
                ->result();
              foreach($pendamping as $s){
              ?>
                <option value="<?=$s->nik?>">
                  <?=$s->nama_lengkap?>
                </option>
              <?php } ?>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Tutup</button>
          <button type="submit" id="btnSimpan" class="btn btn-primary btn-flat"><i class="fa fa-save"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $this->load->view('footer');?>
<script type="text/javascript">
$('#tabel').DataTable({
    processing:true,
    serverSide:true,
    ajax:{
        url:"<?=base_url('data_master/koordinator_pendamping/ajax_list')?>",
        type:"POST"
    },
    columns:[
        {data:'no', orderable: false, class: 'text-center'},
        {data:'nip_koordinator'},
        {data:'nama_koordinator'},
        {data:'jumlah_pendamping', class: 'text-center'},
        {data:'nama_pendamping'},
        {data:'aksi', orderable: false, class: 'text-center'}
    ]
});

function tambah()
{
    $('#formPengawas')[0].reset();
    $('#mode').val('tambah');
    $('#nip_koordinator')
        .prop('disabled',false)
        .val('')
        .trigger('change');

    $('#nip_pendamping')
        .val([])
        .trigger('change');

    $('#nip_hidden').val('');

    $('#modalPengawasLabel').html('<i class="fa fa-plus"></i> Tambah Koordinator');
    $('#modalPengawas').modal('show');
}

function edit(nip)
{
  $.ajax({
    url:"<?=base_url('data_master/koordinator_pendamping_get')?>/"+nip,
    type:"GET",
    dataType:"json",
    success:function(res){
      if(!res.status){
          Swal.fire('Gagal',res.message,'warning');
          return;
      }

      $('#formPengawas')[0].reset();
      $('#mode').val('edit');
      $('#nip_koordinator')
          .val(res.nip_koordinator)
          .trigger('change')
          .prop('disabled',true);

      $('#nip_hidden').val(res.nip_koordinator);
      $('#nip_pendamping')
          .val(res.nip_pendamping)
          .trigger('change');

      $('#modalPengawasLabel').html('<i class="fa fa-edit"></i> Edit Koordinator Pendamping');
      $('#modalPengawas').modal('show');
    }
  });
}

$('#formPengawas').submit(function(e){
  e.preventDefault();
  var url;
  if($('#mode').val()=='tambah')
      url="<?=base_url('data_master/tambah_koordinator_pendamping')?>";
  else
      url="<?=base_url('data_master/edit_koordinator_pendamping')?>";

  $.ajax({
    url:url,
    type:'POST',
    data:$(this).serialize(),
    dataType:'json',
    success:function(r){
      if(r.status){
        $('#modalPengawas').modal('hide');
        $('#tabel').DataTable().ajax.reload(null,false);
        Swal.fire(
            'Berhasil',
            r.message,
            'success'
        );
      }
      else{
        Swal.fire(
            'Gagal',
            r.message,
            'warning'
        );
      }
    }
  });
});

function hapus(nip, nama)
{
    Swal.fire({
        title: 'Hapus Koordinator Pendamping?',
        html: 'Seluruh relasi Pendamping milik <b>'+nama+'</b> akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
      if(result.isConfirmed) {
        $.ajax({
          url : "<?=base_url('data_master/hapus_koordinator_pendamping')?>",
          type : "POST",
          data : {nip : nip},
          dataType : "json",
          success:function(res){
            if(res.status) {
              Swal.fire(
                'Berhasil',
                res.message,
                'success'
              );
              $('#tabel').DataTable().ajax.reload(null,false);
            }
            else {
              Swal.fire(
                'Gagal',
                res.message,
                'warning'
              );
            }
          },
          error:function(){
            Swal.fire(
              'Error',
              'Terjadi kesalahan pada server.',
              'error'
            );
          }
        });
      }
  });
}


$('#nip').select2({
  width: '100%',
  dropdownParent: $('#modalPengawas'),
  placeholder: 'Pilih Koordinator',
  allowClear: true
});

$('#nip_pendamping').select2({
  width: '100%',
  dropdownParent: $('#modalPengawas'),
  placeholder: 'Pilih Pendamping',
  allowClear: true
});


</script>
</body>
</html>
