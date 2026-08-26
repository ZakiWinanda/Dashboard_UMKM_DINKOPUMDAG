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
                    <button class="btn btn-primary mb-3" onclick="tambah()"><i class="fa fa-plus"></i> Tambah Omset</button>

                    <table id="table" class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>SWK</th>
                                <th>Tahun</th>
                                <th>Bulan</th>
                                <th>Omset (Rp)</th>
                                <th>Omset +1% (Rp)</th>
                                <th width="130">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form">
                <div class="modal-header">
                    <h4>Data Omset</h4>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="idomset">
                    <div class="form-group">
                        <label>SWK</label>
                        <select name="idswk" class="form-control select2" style="width: 100%;">
                            <?php foreach($swk as $s){ ?>
                                <option value="<?=$s->idswk?>"><?=$s->nama_swk?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Bulan</label>
                        <select name="bulan" class="form-control">
                            <?php
                            for($i=1;$i<=12;$i++) {
                                echo "<option value='$i'>".bulan($i)."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Omset</label>
                        <input type="text" name="omset" class="form-control uang">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->load->view('footer');?>
<script type="text/javascript">
    var table;
    table=$("#table").DataTable({
        processing:true,
        serverSide:true,
        order:[],
        ajax:{
            url:"<?=site_url('data_master/omset/ajax_list')?>",
            type:"POST"
        }
    });

    function tambah()
    {
        $("#form")[0].reset();
        $("[name=idomset]").val("");
        $("#modal").modal("show");
    }

    function edit_data(id)
    {
        $.get("<?=site_url('data_master/omset_edit')?>/"+id,function(r){
            $("[name=idomset]").val(r.idomset);
            $("[name=idswk]").val(r.idswk);
            $("[name=tahun]").val(r.tahun);
            $("[name=bulan]").val(r.bulan);
            $("[name=omset]").val(r.omset);
            $("#modal").modal("show");
        },"json");
    }

    $("#form").submit(function(e){
        e.preventDefault();
        $.ajax({
            url:"<?=site_url('data_master/omset/simpan')?>",
            type:"POST",
            data:$(this).serialize(),
            dataType:"json",
            success:function(){
                $("#modal").modal("hide");
                table.ajax.reload(null,false);
                Swal.fire(
                    'Sukses',
                    'Data berhasil disimpan',
                    'success'
                    );
            }
        });
    });

    function hapus(id)
    {
        Swal.fire({
            title:'Hapus data?',
            icon:'warning',
            showCancelButton:true
        }).then((r)=>{
            if(r.isConfirmed){
                $.get("<?=site_url('data_master/omset_hapus')?>/"+id,function(){
                    table.ajax.reload(null,false);
                    Swal.fire(
                        'Berhasil',
                        'Data berhasil dihapus',
                        'success'
                        );
                });
            }
        });
    }
</script>
</body>
</html>
