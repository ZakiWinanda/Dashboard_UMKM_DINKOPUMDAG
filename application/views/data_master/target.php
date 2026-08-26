<div class="content-wrapper pb-5">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><?=$title;?></h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?=base_url('data_master/indikator')?>"
                       class="btn btn-secondary btn-flat btn-sm">
                        <i class="fa fa-arrow-left"></i>Kembali</a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">Informasi Indikator</div>

                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150">Kode</td>
                            <td width="10">:</td>
                            <td><?=$indikator->kode;?></td>
                        </tr>
                        <tr>
                            <td>Nama</td>
                            <td>:</td>
                            <td><?=$indikator->nama;?></td>
                        </tr>
                        <tr>
                            <td>Tipe</td>
                            <td>:</td>
                            <td><?=$indikator->tipe;?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
               <div class="card-body">
                    <div class="form-group">
                        <button class="btn btn-primary btn-flat btn-sm" onclick="tambah()"><i class="fa fa-plus"></i>Tambah Target</button>
                    </div>

                    <table id="tabel" class="table table-bordered table-striped table-sm">
                        <thead class="text-center">
                            <tr>
                                <th width="50">NO</th>
                                <th width="100">TAHUN</th>
                                <th>SUB INDIKATOR</th>
                                <th width="120">TARGET</th>
                                <th width="80">URUT</th>
                                <th width="170">AKSI</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTarget">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formTarget">
                <input type="hidden" name="idtarget" id="idtarget">
                <input type="hidden" name="idindikator" value="<?=$indikator->idindikator;?>">

                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="judulModal">Tambah Target</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun" id="tahun" class="form-control" value="<?=date('Y');?>" required>
                    </div>

                    <div class="form-group">
                        <label>Sub Indikator</label>
                        <input type="text" name="subindikator" id="subindikator" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Target</label>
                        <input type="number" name="target" id="target" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Urut</label>
                        <input type="number" name="urut" id="urut" class="form-control" value="1">
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

<?php $this->load->view('footer'); ?>

<script type="text/javascript">
var table;
$(function(){
    table=$('#tabel').DataTable({
        processing:true,
        serverSide:false,
        ajax:{
            url:"<?=base_url('data_master/get_target/'.$indikator->idindikator);?>",
            dataSrc:'data'
        },
        columns:[
            {data:'no'},
            {data:'tahun'},
            {data:'subindikator'},
            {data:'target',className:'text-center'},
            {data:'urut',className:'text-center'},
            {data:'aksi',className:'text-center'}
        ]
    });
});

function tambah(){
    $('#formTarget')[0].reset();
    $('#idtarget').val('');
    $('#tahun').val(new Date().getFullYear());
    $('#judulModal').html('<i class="fa fa-plus"></i> Tambah Target');
    $('#modalTarget').modal('show');
}

function edit(id){
    $.get(
        "<?=base_url('data_master/get_target_by_id');?>/"+id,
        function(r){
            $('#idtarget').val(r.idtarget);
            $('#tahun').val(r.tahun);
            $('#subindikator').val(r.subindikator);
            $('#target').val(r.target);
            $('#urut').val(r.urut);
            $('#judulModal').html('<i class="fa fa-edit"></i> Edit Target');
            $('#modalTarget').modal('show');
        },
        'json'
    );
}

$('#formTarget').submit(function(e){
    e.preventDefault();
    $.ajax({
        url:"<?=base_url('data_master/simpan_target');?>",
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
                $('#modalTarget').modal('hide');
                table.ajax.reload(null,false);
                Swal.fire(
                    'Berhasil',
                    'Data berhasil disimpan',
                    'success'
                );
            }
        }
    });
});

function hapus(id){
    Swal.fire({
        title:'Hapus target?',
        icon:'warning',
        showCancelButton:true
    }).then((r)=>{
        if(!r.isConfirmed) return;
        $.post(
            "<?=base_url('data_master/hapus_target');?>/"+id,
            function(res){
                if(res.status){
                    table.ajax.reload(null,false);
                    Swal.fire(
                        'Berhasil',
                        'Data berhasil dihapus',
                        'success'
                    );
                }
            },
            'json'
        );
    });
}
</script>

