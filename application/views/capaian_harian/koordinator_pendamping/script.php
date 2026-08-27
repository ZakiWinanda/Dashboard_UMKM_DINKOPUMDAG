<script type="text/javascript">
    var totalOmset = 0;
    var totalKunjungan = 0;

    $(document).ready(function(){
        $('#btnLoad').click(function(e){
            e.preventDefault();
            loadData();
        });

        $(document).on('change', '#pilih_swk, [name=pilih_swk]', function(){
            loadData();
        });

        // Auto Load data pertama kali jika SWK sudah terpilih
        if ($('#pilih_swk').val() || $('[name=pilih_swk]').val()) {
            loadData();
        }
    });

    $('#idpendamping').change(function(){
        var idpendamping = $(this).val();
        <?php
        if($is_koordinator_pendamping) {
            echo("var nip_koordinator = $('#nip_koordinator').val();");
        }
        ?>
        $.ajax({
            url: "<?=site_url('swk/get_swk');?>",
            type: "POST",
            dataType: "json",
            data:{
                <?php
                if($is_koordinator_pendamping) {
                    echo("nip_koordinator:nip_koordinator,");
                }
                ?>
                idpendamping:idpendamping
            },
            success:function(res){
                var html = '<option value="">- Pilih SWK -</option>';
                $.each(res,function(i,row){
                    var valId = row.idswk || row.id;
                    html += '<option value="'+valId+'">'+row.nama_swk+'</option>';
                });
                $('#pilih_swk').html(html);
                if ($.fn.select2) {
                    $('#pilih_swk').trigger('change.select2');
                }
            }
        });
    });

    $('#frmKunjungan').submit(function(e){
        e.preventDefault();
        Swal.fire({
            icon:'warning',
            title:'',
            text:"Akses terbatas",
            confirmButtonText:'OK'
        });
    });

    $('#frmOmset').submit(function(e){
        e.preventDefault();
        Swal.fire({
            icon:'warning',
            title:'',
            text:"Akses terbatas",
            confirmButtonText:'OK'
        });
    });

    function loadData()
    {
        var idswk = $('#pilih_swk').val() || $('[name=pilih_swk]').val();
        var filter_bulan = $('#filter_bulan_tahun').val() || $('[name=filter_bulan_tahun]').val();

        if(!idswk)
        {
            Swal.fire(
                'Peringatan',
                'Silahkan pilih SWK.',
                'warning'
            );
            return;
        }

        $.ajax({
            url : "<?=site_url('capaian_harian/load_data')?>",
            type : "POST",
            dataType : "json",
            data : {
                idswk : idswk,
                filter_bulan_tahun : filter_bulan
            },
            beforeSend:function(){
                $('#btnLoad')
                .html('<i class="fa fa-spinner fa-spin"></i> Loading...')
                .prop('disabled',true);
            },
            success:function(res){
                buildTable(res);
            },
            complete:function(){
                $('#btnLoad')
                .html('<i class="fa fa-paper-plane"></i> Submit')
                .prop('disabled',false);
            }
        });
    }

    function buildTable(res)
    {
        var filter = $('#filter_bulan_tahun').val() || $('[name=filter_bulan_tahun]').val() || '';
        var pecah  = filter.split('-');
        var bulan  = pecah[0] ? pad(parseInt(pecah[0], 10)) : '01';
        var tahun  = pecah[1] || '<?= date("Y") ?>';

        $('#headerOmset').html('<th style="min-width:150px;">Tanggal</th>');
        $('#headerKunjungan').html('<th style="min-width:150px;">Tanggal</th>');

        $('#rowOmset').html('<td class="font-weight-bold text-left">Omset (Rp)</td>');
        $('#rowKunjungan').html('<td class="font-weight-bold text-left">Jumlah Kunjungan</td>');

        totalOmset = res.total_omset_harian || 0;
        totalKunjungan = res.total_kunjungan_harian || 0;

        $('#totalOmset').html('Rp ' + rupiah(totalOmset));
        $('#totalKunjungan').html(parseInt(totalKunjungan, 10).toLocaleString('id-ID'));

        if (!res.jumlah_hari || res.jumlah_hari === 0) {
            return;
        }

        var omset = {};
        var kunjungan = {};

        if (res.omset && res.omset.length > 0) {
            $.each(res.omset, function(i, e){
                var tgl = parseInt(e.tanggal.substr(8, 2), 10);
                omset[tgl] = e.omset;
            });
        }

        if (res.kunjungan && res.kunjungan.length > 0) {
            $.each(res.kunjungan, function(i, e){
                var tgl = parseInt(e.tanggal.substr(8, 2), 10);
                kunjungan[tgl] = e.jumlah;
            });
        }

        for (var i = 1; i <= res.jumlah_hari; i++) {
            var tglStr  = pad(i);
            var tglFull = tahun + '-' + bulan + '-' + tglStr;
            var d       = new Date(parseInt(tahun, 10), parseInt(bulan, 10) - 1, i);
            var hariIdx = d.getDay();
            var namaHari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][hariIdx];

            var bgClass = (hariIdx === 0) ? 'bg-danger text-white' : '';

            $('#headerOmset').append('<th class="text-center ' + bgClass + '">' + namaHari + '<br>' + i + '</th>');
            $('#headerKunjungan').append('<th class="text-center ' + bgClass + '">' + namaHari + '<br>' + i + '</th>');

            var valOmset     = (typeof omset[i] !== 'undefined' && parseFloat(omset[i]) > 0) ? rupiah(omset[i]) : '-';
            var valKunjungan = (typeof kunjungan[i] !== 'undefined' && parseInt(kunjungan[i], 10) > 0) ? parseInt(kunjungan[i], 10).toLocaleString('id-ID') : '-';

            $('#rowOmset').append(
                '<td class="text-center cell-omset ' + bgClass + '" data-tanggal="' + tglFull + '" style="cursor:pointer">' +
                valOmset +
                '</td>'
            );

            $('#rowKunjungan').append(
                '<td class="text-center cell-kunjungan ' + bgClass + '" data-tanggal="' + tglFull + '" style="cursor:pointer">' +
                valKunjungan +
                '</td>'
            );
        }
    }

    function pad(n) {
        return n < 10 ? '0' + n : n;
    }

    function rupiah(angka) {
        angka = parseFloat(angka);
        if (isNaN(angka)) angka = 0;
        return angka.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    $(document).on('click','.cell-omset',function(){
        var tanggal = $(this).data('tanggal');
        $('#modalOmset').modal('show');
        $('#omsetTanggal').val($(this).data('tanggal'));
        $('#tanggalOmsetText').val(formatTanggalIndonesia(tanggal));
        $('#omsetSwk').val($('#pilih_swk').val() || $('[name=pilih_swk]').val());

        var nilai = $(this).text().replace(/[^\d]/g,'');
        $('#nilaiOmset').val(nilai=='' ? 0 : nilai);
    });

    $(document).on('click','.cell-kunjungan',function(){
        var tanggal = $(this).data('tanggal');
        $('#modalKunjungan').modal('show');
        $('#kunjunganTanggal').val($(this).data('tanggal'));
        $('#tanggalKunjunganText').val(formatTanggalIndonesia(tanggal));
        $('#kunjunganSwk').val($('#pilih_swk').val() || $('[name=pilih_swk]').val());

        var jml = $.trim($(this).text());
        $('#jumlahKunjungan').val(jml=='' ? 0 : jml);
    });

    function formatTanggalIndonesia(tanggal) {
        var p = tanggal.split('-');
        return p[2]+'-'+p[1]+'-'+p[0];
    }

    try {
        if ($.fn.datetimepicker) {
            $('#filter_bulan_tahun').datetimepicker({
                showClose: true,
                showTime: true,
                format: 'MM-YYYY',
            });
        }
    } catch(e){}
</script>
