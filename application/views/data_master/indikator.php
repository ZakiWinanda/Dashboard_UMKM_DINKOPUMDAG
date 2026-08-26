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
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <button class="btn btn-primary btn-flat btn-sm" onclick="tambah()"><i class="fa fa-plus"></i>Tambah</button>
                    </div>

                    <table id="tabel" class="table table-bordered table-striped table-sm">
                        <thead class="text-center">
                        <tr>
                            <th width="50">NO</th>
                            <th width="80">KODE</th>
                            <th>NAMA INDIKATOR</th>
                            <th width="100">TIPE</th>
                            <th width="70">URUT</th>
                            <th width="90">STATUS</th>
                            <th width="90">TARGET</th>
                            <th width="220">AKSI</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalIndikator">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formIndikator">
                <input type="hidden" name="idindikator" id="idindikator">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="judulModal">Tambah Indikator</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kode</label>
                        <input type="text" name="kode" id="kode" class="form-control" required></div>
                    <div class="form-group">
                        <label>Nama Indikator</label>
                        <input type="text" name="nama" id="nama" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Tipe</label>
                        <select class="form-control" name="tipe" id="tipe">
                            <option value="number">Number</option>
                            <option value="text">Text</option>
                            <option value="radio">Radio</option>
                            <option value="multi">Multi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Urut</label>
                        <input type="number" name="urut" id="urut" class="form-control" value="1">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="aktif" id="aktif" class="form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
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
var table;
$(function(){
    table = $('#tabel').DataTable({
        processing:true,
        serverSide:true,
        order:[],
        ajax:{
            url:"<?=base_url('data_master/ajax_indikator')?>",
            type:"POST"
        },
        columns:[
            {data:'no',orderable:false},
            {data:'kode'},
            {data:'nama'},
            {data:'tipe',className:'text-center'},
            {data:'urut',className:'text-center'},
            {data:'status',className:'text-center',orderable:false},
            {data:'target',className:'text-center',orderable:false},
            {data:'aksi',className:'text-center',orderable:false}
        ]
    });
});

function tambah(){
    $('#formIndikator')[0].reset();
    $('#idindikator').val('');
    $('#judulModal').html('<i class="fa fa-plus"></i> Tambah Indikator');
    $('#modalIndikator').modal('show');
}

function edit(id){
    $.get(
        "<?=base_url('data_master/get_indikator');?>/"+id,
        function(r){
            $('#idindikator').val(r.idindikator);
            $('#kode').val(r.kode);
            $('#nama').val(r.nama);
            $('#tipe').val(r.tipe);
            $('#urut').val(r.urut);
            $('#aktif').val(r.aktif);
            $('#judulModal').html('<i class="fa fa-edit"></i> Edit Indikator');
            $('#modalIndikator').modal('show');
        },
        'json'
    );
}

$('#formIndikator').submit(function(e){
    e.preventDefault();
    $.ajax({
        url:"<?=base_url('data_master/simpan_indikator')?>",
        type:"POST",
        data:$(this).serialize(),
        dataType:"json",
        beforeSend:function(){
            $('#btnSimpan')
                .prop('disabled',true)
                .html('<i class="fa fa-spinner fa-spin"></i> Menyimpan');
        },
        success:function(r){
            $('#btnSimpan')
                .prop('disabled',false)
                .html('<i class="fa fa-save"></i> Simpan');
            if(r.status){
                $('#modalIndikator').modal('hide');
                table.ajax.reload(null,false);
                Swal.fire(
                    'Berhasil',
                    'Data berhasil disimpan',
                    'success'
                );
            }
            else{
                Swal.fire(
                    'Gagal',
                    r.pesan,
                    'warning'
                );
            }
        }
    });
});

function target(id){
    window.location =
        "<?=base_url('data_master/target/')?>"+id;
}

function hapus(id,nama){
    Swal.fire({
        title:'Hapus indikator?',
        text:nama,
        icon:'warning',
        showCancelButton:true
    }).then((r)=>{
        if(!r.isConfirmed) return;
        $.post(
            "<?=base_url('data_master/hapus_indikator')?>",
            {
                idindikator:id
            },

            function(res){
                if(res.status){
                    table.ajax.reload(null,false);
                }
            },
            'json'
        );
    });
}
</script>

