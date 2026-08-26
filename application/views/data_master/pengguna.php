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
        <div class="card-body">
          <div class="form-group">
            <button class="btn btn-primary btn-flat btn-sm" onclick="tambah()"><i class="fa fa-plus"></i> Tambah</button>
          </div>

          <table id="tabel" class="table table-bordered table-striped table-sm">
            <thead class="text-center">
            <tr>
              <th width="50">NO</th>
              <th width="140">NIK</th>
              <th>NAMA LENGKAP</th>
              <th width="130">ROLE</th>
              <th width="150">NO HP</th>
              <th width="100">STATUS</th>
              <th width="180">AKSI</th>
            </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalPengguna">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formPengguna">
        <input type="hidden" name="mode" id="mode">
        <div class="modal-header bg-primary">
          <h5 class="modal-title" id="judulModal">Tambah Pengguna</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label>NIK</label>
            <input type="text" name="nik" id="nik" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required>
          </div>

          <div class="form-group">
            <label>No HP</label>
            <input type="text" name="no_tlp" id="no_tlp" class="form-control">
          </div>

          <div class="form-group">
            <label>Role</label>
            <select class="form-control" name="role" id="role">
              <option value="administrator">Administrator</option>
              <option value="pendamping">Pendamping</option>
              <option value="pimpinan">Pimpinan</option>
            </select>
          </div>

          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" id="password" class="form-control">
            <small class="text-muted">Kosongkan jika password tidak diubah.</small>
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
var table;
table = $('#tabel').DataTable({
processing:true,
serverSide:true,
order: [],
ajax:{
  url:"<?=base_url('data_master/pengguna/ajax_list')?>",
  type:"POST"
},
columns:[
  {data:'no',orderable:false},
  {data:'nik'},
  {data:'nama'},
  {data:'role'},
  {data:'telp'},
  {data:'status',orderable:false,className:'text-center'},
  {data:'aksi',orderable:false,className:'text-center'}
]
});

function tambah() {
  $('#formPengguna')[0].reset();
  $('#mode').val('tambah');
  $('#nik').prop('readonly',false);
  $('#judulModal').html('<i class="fa fa-plus"></i> Tambah Pengguna');
  $('#modalPengguna').modal('show');
}

function edit(nik) {
  $.get(
    "<?=base_url('data_master/pengguna_get')?>/"+nik,
    function(res){
      if(!res.status){
        Swal.fire(
          'Gagal',
          'Data tidak ditemukan.',
          'warning'
        );
        return;
      }
      $('#formPengguna')[0].reset();
      $('#mode').val('edit');
      $('#nik').val(res.data.nik).prop('readonly',true);
      $('#nama_lengkap').val(res.data.nama_lengkap);
      $('#role').val(res.data.role);
      $('#no_tlp').val(res.data.no_tlp);
      $('#password').val('');
      $('#judulModal').html('<i class="fa fa-edit"></i> Edit Pengguna');
      $('#modalPengguna').modal('show');
    },'json'
  );
}

$('#formPengguna').submit(function(e) {
  e.preventDefault();
  $.ajax({
    url:"<?=base_url('data_master/pengguna_simpan')?>",
    type:"POST",
    data:$(this).serialize(),
    dataType:"json",
    beforeSend:function(){
      $('#btnSimpan')
          .prop('disabled',true)
          .html('<i class="fa fa-spinner fa-spin"></i> Menyimpan');
    },
    success:function(res){
      $('#btnSimpan')
        .prop('disabled',false)
        .html('<i class="fa fa-save"></i> Simpan');
      if(res.status){
        $('#modalPengguna').modal('hide');
        table.ajax.reload(null,false);
        Swal.fire(
          'Berhasil',
          'Data berhasil disimpan.',
          'success'
        );
      }
    }
  });
});

function nonaktifkan(nik,nama) {
  Swal.fire({
    title:'Nonaktifkan Pengguna?',
    text:nama,
    icon:'warning',
    showCancelButton:true,
    confirmButtonText:'Ya',
    cancelButtonText:'Batal'
  }).then((r)=>{
    if(!r.isConfirmed) return;
    $.ajax({
      url:"<?=base_url('data_master/pengguna_hapus')?>",
      type:"POST",
      data:{
          nik:nik
      },
      dataType:"json",
      success:function(res){
        if(res.status){
          table.ajax.reload(null,false);
          Swal.fire(
            'Berhasil',
            'Pengguna berhasil dinonaktifkan.',
            'success'
          );
        }
      }
    });
  });
}
</script>
</body>
</html>
