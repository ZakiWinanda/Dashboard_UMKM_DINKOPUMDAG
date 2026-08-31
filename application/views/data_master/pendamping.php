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
                                <th>JENIS PENDAMPING</th>
                                <th>PENUGASAN (SWK / KECAMATAN)</th>
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
                        <label>Jenis Pendamping</label>
                        <div class="d-flex">
                            <div class="custom-control custom-radio mr-4">
                                <input class="custom-control-input" type="radio" id="jenis_swk" name="jenis_pendamping" value="swk" checked>
                                <label for="jenis_swk" class="custom-control-label font-weight-normal"><i class="fa fa-store text-info"></i> Pendamping SWK</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input class="custom-control-input" type="radio" id="jenis_kecamatan" name="jenis_pendamping" value="kecamatan">
                                <label for="jenis_kecamatan" class="custom-control-label font-weight-normal"><i class="fa fa-map-marker-alt text-success"></i> Pendamping Kecamatan</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="div_swk">
                        <label>Penugasan SWK</label>
                        <select class="form-control" name="idswk[]" id="idswk" multiple style="width: 100%;">
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

                    <div class="form-group" id="div_kecamatan" style="display:none;">
                        <label>Penugasan Kecamatan</label>
                        <select class="form-control" name="kecamatan[]" id="kecamatan" multiple style="width: 100%;">
                            <?php
                            $list_kec = [
                                'ASEM ROWO', 'BENOWO', 'BUBUTAN', 'BULAK', 'DUKUH PAKIS',
                                'GAYUNGAN', 'GENTENG', 'GUBENG', 'GUNUNG ANYAR', 'JAMBANGAN',
                                'KARANG PILANG', 'KENJERAN', 'KREMBANGAN', 'LAKARSANTRI', 'MULYOREJO',
                                'PABEAN CANTIAN', 'PAKAL', 'RUNGKUT', 'SAMBIKEREP', 'SAWAHAN',
                                'SEMAMPIR', 'SIMOKERTO', 'SUKOLILO', 'SUKOMANUNGGAL', 'TAMBAKSARI',
                                'TANDES', 'TEGALSARI', 'TENGGILIS MEJOYO', 'WIYUNG', 'WONOCOLO', 'WONOKROMO'
                            ];
                            foreach($list_kec as $k){
                                ?>
                                <option value="<?=$k?>"><?=$k?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Tutup</button>
                    <button type="submit" id="btnSimpan" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Simpan</button>
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
            {data:'jenis_pendamping', class: 'text-center'},
            {data:'penugasan', orderable: false},
            {data:'aksi', orderable: false, class: 'text-center'}
        ]
    });

    $('input[name="jenis_pendamping"]').change(function(){
        toggleJenisForm($(this).val());
    });

    function toggleJenisForm(jenis) {
        if(jenis === 'kecamatan') {
            $('#div_swk').hide();
            $('#div_kecamatan').show();
        } else {
            $('#div_kecamatan').hide();
            $('#div_swk').show();
        }
    }

    function tambah() {
        $('#formPendamping')[0].reset();
        $('#mode').val('tambah');
        $('#pilih_nip').val('').trigger('change').prop('disabled', false);
        $('#nip_hidden').val('');
        $('#jenis_swk').prop('checked', true);
        toggleJenisForm('swk');
        $('#idswk').val([]).trigger('change');
        $('#kecamatan').val([]).trigger('change');
        $('#modalPendampingLabel').html('<i class="fa fa-plus"></i> Tambah Pendamping');
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

                if(res.tipe === 'kecamatan') {
                    $('#jenis_kecamatan').prop('checked', true);
                    toggleJenisForm('kecamatan');
                } else {
                    $('#jenis_swk').prop('checked', true);
                    toggleJenisForm('swk');
                }

                $('#idswk')
                .val(res.idswk)
                .trigger('change');
                $('#kecamatan')
                .val(res.kecamatan)
                .trigger('change');
                $('#modalPendampingLabel').html('<i class="fa fa-edit"></i> Edit Pendamping');
                $('#modalPendamping').modal('show');
            }
        });
    }

    $('#pilih_nip').select2({
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

    $('#kecamatan').select2({
        width: '100%',
        dropdownParent: $('#modalPendamping'),
        placeholder: 'Pilih Kecamatan',
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
                } else {
                    Swal.fire(
                        'Gagal',
                        r.message || 'Gagal menyimpan data.',
                        'error'
                    );
                }
            }
        });
    });

    function hapus(nip, nama)
    {
        Swal.fire({
            title: 'Hapus Penugasan Pendamping?',
            html: 'Seluruh relasi SWK dan Kecamatan milik <b>'+nama+'</b> akan dihapus.',
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
