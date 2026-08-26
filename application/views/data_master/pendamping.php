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
                    <table id="tabel" class="table table-bordered table-sm">
                        <thead class="text-center">
                            <tr>
                                <th>NO</th>
                                <th>NIP / NIK</th>
                                <th>NAMA</th>
                                <th>NO. TELP</th>
                                <th>JUMLAH SWK</th>
                                <th>NAMA SWK</th>
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

<div class="modal fade" id="modalPendamping" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form id="formPendamping">
                <input type="hidden" id="mode" name="mode">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modalPendampingLabel">Tambah Pendamping</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Pendamping</label>
                        <select class="form-control" name="pilih_nip" id="pilih_nip" required>
                            <option value="">Pilih Pendamping</option>
                            <?php
                            $pendamping = $this->db
                            ->where('role','pendamping')
                            ->order_by('nama_lengkap','ASC')
                            ->get('m_users')
                            ->result();
                            foreach($pendamping as $p){
                                ?>
                                <option value="<?=$p->nik?>"><?=$p->nama_lengkap?> (<?=$p->nik?>)</option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="nip" id="nip_hidden">
                    </div>

                    <div class="form-group">
                        <label>SWK</label>
                        <select class="form-control" name="idswk[]" id="idswk" multiple required>
                            <?php
                            $swk = $this->db
                            ->where('aktif',1)
                            ->order_by('nama_swk','ASC')
                            ->get('m_swk')
                            ->result();
                            foreach($swk as $s){
                                ?>
                                <option value="<?=$s->idswk?>">
                                    <?=$s->nama_swk?>
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
        order:[],
        ajax:{
            url:"<?=base_url('data_master/pendamping/ajax_list')?>",
            type:"POST"
        },
        columns:[
        {data:'no', orderable: false},
        {data:'nip'},
        {data:'nama'},
        {data:'no_tlp'},
        {data:'jumlah_swk', orderable: false, class: 'text-center'},
        {data:'swk', orderable: false},
        {data:'aksi', orderable: false, class: 'text-center'}
        ]
    });

    function tambah() {
        $('#formPendamping')[0].reset();
        $('#mode').val('tambah');
        $('#nik').prop('readonly',false);
        $('#judulModal').html('<i class="fa fa-plus"></i> Tambah Pendamping');
        $('#modalPendamping').modal('show');
    }

    function edit(nip)
    {
        $.ajax({
            url:"<?=base_url('data_master/pendamping_get')?>/"+nip,
            type:"GET",
            dataType:"json",
            success:function(res){
                if(!res.status){
                    Swal.fire(
                        'Gagal',
                        'Data tidak ditemukan.',
                        'warning'
                        );
                    return;
                }
                $('#formPendamping')[0].reset();
                $('#mode').val('edit');
                $('#pilih_nip')
                .val(res.nip)
                .trigger('change')
                .prop('disabled',true);
                $('#nip_hidden')
                .val(res.nip);
                $('#idswk')
                .val(res.idswk)
                .trigger('change');
                $('#modalPendampingLabel').html('<i class="fa fa-edit"></i> Edit Pendamping');
                $('#modalPendamping').modal('show');
            }
        });
    }

    $('#nip').select2({
        width: '100%',
        dropdownParent: $('#modalPendamping'),
        placeholder: 'Pilih Pendamping',
        allowClear: true
    });

    $('#idswk').select2({
        width: '100%',
        dropdownParent: $('#modalPendamping'),
        placeholder: 'Pilih SWK',
        allowClear: true
    });

    $('#formPendamping').submit(function(e){
        e.preventDefault();
        $.ajax({
            url:"<?=base_url('data_master/pendamping_update')?>",
            type:"POST",
            data:$(this).serialize(),
            dataType:"json",
            success:function(r){
                if(r.status){
                    $('#modalPendamping').modal('hide');
                    $('#tabel').DataTable().ajax.reload(null,false);
                    Swal.fire(
                        'Berhasil',
                        r.message,
                        'success'
                        );
                }
            }
        });
    });

    function hapus(nip, nama)
    {
        Swal.fire({
            title: 'Hapus Pendamping?',
            html: 'Seluruh relasi SWK milik <b>'+nama+'</b> akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if(result.isConfirmed) {
                $.ajax({
                    url : "<?=base_url('data_master/pendamping_hapus')?>",
                    type : "POST",
                    data : {nip : nip},
                    dataType : "json",
                    beforeSend:function(){
                        Swal.fire({
                            title:'Menghapus...',
                            text:'Mohon tunggu',
                            allowOutsideClick:false,
                            didOpen:function(){
                                Swal.showLoading();
                            }
                        });
                    },
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
</script>
</body>
</html>
