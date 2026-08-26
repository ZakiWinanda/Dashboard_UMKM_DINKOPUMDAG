<div class="content-wrapper pb-5">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?=$title;?></h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label>Tahun</label>

                            <select id="tahun" class="form-control">
                                <?php
                                $tahun=date('Y');
                                for($i=$tahun-2;$i<=$tahun+5;$i++) {
                                    ?>
                                    <option value="<?=$i?>" <?=$i==$tahun?'selected':'';?>><?=$i?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary btn-block" onclick="reloadTable()"><i class="fa fa-search"></i>Tampilkan</button>
                        </div>

                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-success btn-block" onclick="copyTahun()"><i class="fa fa-copy"></i>Copy Tahun</button>
                        </div>
                    </div>

                    <table id="tabel" class="table table-bordered table-striped table-sm">
                        <thead>
                        <tr>
                            <th width="50">No</th>
                            <th width="120">Kode</th>
                            <th>Indikator</th>
                            <th width="100">Tipe</th>
                            <th width="250">Target</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modalMulti">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Kelola Sub Indikator</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="multi_idindikator">
                <table class="table table-bordered table-sm" id="tableMulti">
                    <thead>
                    <tr>
                        <th>Sub Indikator</th>
                        <th width="120">Target</th>
                        <th width="70">Aksi</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <hr>

                <div class="row">
                    <div class="col-md-7">
                        <input type="text" id="subindikator" class="form-control" placeholder="Sub indikator">
                    </div>

                    <div class="col-md-3">
                        <input type="number" id="target_multi" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary btn-block" onclick="tambahSub()">Tambah</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('footer'); ?>
<script type="text/javascript">
var table;
$(document).ready(function(){
    table = $('#tabel').DataTable({
        processing:true,
        serverSide:true,
        ordering:true,
        searching:true,
        autoWidth:false,
        ajax:{
            url:"<?=base_url('data_master/master_target/ajax_list');?>",
            type:"POST",
            data:function(d){
                d.tahun=$('#tahun').val();
            }
        },
        columns:[
            {data:'no', orderable:false, className:'text-center'},
            {data:'kode'},
            {data:'nama'},
            {data:'tipe', className:'text-center'},
            {data:'target', orderable:false}
        ]
    });
});

function reloadTable()
{
    table.ajax.reload(null,false);
}

$(document).on('change','.target',function(){
    $.ajax({
        url:"<?=base_url('data_master/master_target/simpan');?>",
        type:"POST",
        dataType:"json",
        data:{
            tahun:$('#tahun').val(),
            idindikator:$(this).data('id'),
            target:$(this).val()
        },
        success:function(r){
            if(r.status) {
                toastr.success('Target berhasil disimpan');
            }
        }
    });
});

function kelolaMulti(idindikator,tahun) {
    $('#multi_idindikator').val(idindikator);
    $.ajax({
        url:"<?=base_url('data_master/master_target/multi');?>",
        type:"POST",
        dataType:"json",
        data:{
            tahun:tahun,
            idindikator:idindikator
        },
        success:function(r){
            var html='';
            $.each(r.data,function(i,v){
                html+='<tr>';
                html+='<td>'+v.subindikator+'</td>';
                html+='<td>'+v.target+'</td>';
                html+='<td class="text-center">';
                html+='<button class="btn btn-danger btn-sm"';
                html+=' onclick="hapusSub(\''+v.idtarget+'\')">';
                html+='<i class="fa fa-trash"></i>';
                html+='</button>';
                html+='</td>';
                html+='</tr>';
            });
            $('#tableMulti tbody').html(html);
            $('#modalMulti').modal('show');
        }
    });
}

function tambahSub() {
    if($('#subindikator').val()=='') {
        toastr.warning('Sub indikator belum diisi');
        return;
    }

    $.ajax({
        url:"<?=base_url('data_master/master_target/multi_simpan');?>",
        type:"POST",
        dataType:"json",
        data:{
            tahun:$('#tahun').val(),
            idindikator:$('#multi_idindikator').val(),
            subindikator:$('#subindikator').val(),
            target:$('#target_multi').val()
        },
        success:function(r){
            if(r.status) {
                $('#subindikator').val('');
                $('#target_multi').val('');
                kelolaMulti(
                    $('#multi_idindikator').val(),
                    $('#tahun').val()
                );
            }
        }
    });
}

function hapusSub(id) {
    Swal.fire({
        title:'Hapus?',
        text:'Sub indikator akan dihapus.',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'Ya'
    }).then((result)=>{
        if(result.isConfirmed) {
            $.ajax({
                url:"<?=base_url('data_master/master_target/multi_hapus');?>",
                type:"POST",
                dataType:"json",
                data:{idtarget:id},
                success:function(r){
                    if(r.status) {
                        kelolaMulti(
                            $('#multi_idindikator').val(),
                            $('#tahun').val()
                        );
                    }
                }
            });
        }
    });
}

function copyTahun() {
    Swal.fire({
        title:'Copy Target?',
        text:'Target tahun sebelumnya akan disalin.',
        icon:'question',
        showCancelButton:true,
        confirmButtonText:'Copy'
    }).then((result)=>{

        if(result.isConfirmed) {
            $.ajax({
                url:"<?=base_url('data_master/master_target/copy');?>",
                type:"POST",
                dataType:"json",
                data:{
                    tahun_asal:parseInt($('#tahun').val())-1,
                    tahun_tujuan:$('#tahun').val()
                },
                success:function(r){
                    if(r.status) {
                        reloadTable();
                        Swal.fire(
                            'Berhasil',
                            'Target berhasil disalin.',
                            'success'
                        );
                    }
                }
            });
        }
    });
}
</script>
</body>
</html>