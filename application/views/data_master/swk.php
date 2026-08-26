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
                    <table id="tabel" class="table table-bordered table-sm">
                        <thead class="text-center">
                            <tr>
                                <th>NO</th>
                                <th>NAMA SWK</th>
                                <th>ALAMAT</th>
                                <th>JUMLAH STAN</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSwk">
    <div class="modal-dialog modal-md">
        <form id="formSwk">
            <input type="hidden" name="idswk" id="idswk">
            <input type="hidden" id="mode" value="tambah">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="modalTitle">Tambah SWK</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama SWK</label>
                        <input type="text" class="form-control" name="nama_swk" id="nama_swk" required>
                    </div>

                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" class="form-control" name="alamat" id="alamat" required>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Stan</label>
                        <input type="number" class="form-control" name="stan" id="stan">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php $this->load->view('footer');?>
<script type="text/javascript">
    $('#tabel').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?=base_url('data_master/swk/ajax_list');?>",
            type: "POST"
        },
        columns: [
        { data: 'no'},
        { data: 'nama_swk'},
        { data: 'alamat'},
        { data: 'stan', class: 'text-center'},
        { data: 'aksi', class: 'text-center'}
        ]
    });

    function tambah() {
        $('#mode').val('tambah');
        $('#modalTitle').html('Tambah SWK');
        $('#formSwk')[0].reset();
        $('#idswk').val('');
        $('#modalSwk').modal('show');
    }

    function edit(id){
        $.ajax({
            url : "<?=site_url('data_master/swk_get')?>/"+id,
            type : "GET",
            dataType : "json",
            success:function(r){
                $('#mode').val('edit');
                $('#modalTitle').html('Edit SWK');
                $('#idswk').val(r.idswk);
                $('#nama_swk').val(r.nama_swk);
                $('#alamat').val(r.alamat);
                $('#stan').val(r.stan);
                $('#aktif').val(r.aktif);
                $('#modalSwk').modal('show');
            }
        });
    }

    $('#formSwk').submit(function(e){
        e.preventDefault();
        var url;
        if($('#mode').val()=="tambah")
            url="<?=site_url('data_master/swk_simpan')?>";
        else
            url="<?=site_url('data_master/swk_update')?>";
        $.ajax({
            url:url,
            type:"POST",
            data:$(this).serialize(),
            dataType:"json",
            success:function(r){
                if(r.status){
                    $('#modalSwk').modal('hide');
                    Swal.fire({
                        icon:'success',
                        title:'Berhasil',
                        text:r.message,
                        timer:1500,
                        showConfirmButton:false
                    });
                    $('#tabel').DataTable().ajax.reload(null,false);
                }
                else{
                    Swal.fire('Gagal',r.message,'error');
                }
            }
        });
    });

    function nonaktifkan(idswk, nama_swk){
        Swal.fire({
            title: 'Nonaktifkan SWK?',
            html: '<b>' + nama_swk + '</b> akan dinonaktifkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Nonaktifkan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function(result){
            if(result.isConfirmed){
                $.ajax({
                    url : "<?= site_url('data_master/swk_hapus') ?>",
                    type : "POST",
                    dataType : "json",
                    data : {idswk : idswk},
                    success:function(res){
                        if(res.status){
                            Swal.fire({
                                icon:'success',
                                title:'Berhasil',
                                text:res.message,
                                timer:1500,
                                showConfirmButton:false
                            });
                            $('#tabel').DataTable().ajax.reload(null,false);
                        }
                        else{
                            Swal.fire({
                                icon:'error',
                                title:'Gagal',
                                text:res.message
                            });
                        }
                    },
                    error:function(){
                        Swal.fire({
                            icon:'error',
                            title:'Error',
                            text:'Terjadi kesalahan pada server.'
                        });
                    }
                });
            }
        });
    }
</script>
</body>
</html>
