<script type="text/javascript">
    var totalOmset = 0;
    var totalKunjungan = 0;
    $(function(){
        $('#btnLoad').click(function(){
            loadData();
        });
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
                    html += '<option value="'+row.idswk+'">'+row.nama_swk+'</option>';
                });
                $('#pilih_swk').html(html);
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
        if($('[name=pilih_swk]').val()=='')
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
                idswk : $('[name=pilih_swk]').val(),
                filter_bulan_tahun : $('[name=filter_bulan_tahun]').val()
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
        var filter = $('[name=filter_bulan_tahun]').val() || '';
        var pecah = filter.split('-');

        var bulan = pecah[0] ? pad(parseInt(pecah[0], 10)) : '01';
        var tahun = pecah[1] || res.tahun || '<?= date("Y") ?>';

        totalOmset = res.total_omset_harian;
        totalKunjungan = res.total_kunjungan_harian;

        $('#totalOmset').html(rupiah(totalOmset));
        $('#totalKunjungan').html(totalKunjungan);

        if (res.is_kecamatan) {
            $('#headerOmset').html('<th width="150">Bulan</th>');
            $('#headerKunjungan').html('<th width="150">Bulan</th>');

            $('#rowOmset').html('<th>Omset</th>');
            $('#rowKunjungan').html('<th>Kunjungan</th>');

            var namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            var omsetBln = {};
            var kunjunganBln = {};
            $.each(res.omset, function(i, e){
                omsetBln[parseInt(e.bulan, 10)] = e.omset;
            });
            $.each(res.kunjungan, function(i, e){
                kunjunganBln[parseInt(e.bulan, 10)] = e.jumlah;
            });

            for (var m = 1; m <= 12; m++) {
                var labelBln = namaBulan[m - 1];
                var isCurBln = (parseInt(bulan, 10) === m);
                var bgClass  = isCurBln ? 'bg-primary text-white' : '';

                $('#headerOmset').append('<td class="text-center ' + bgClass + '">' + labelBln + '</td>');
                $('#headerKunjungan').append('<td class="text-center ' + bgClass + '">' + labelBln + '</td>');

                var nilaiOmset = (typeof omsetBln[m] !== 'undefined' && parseFloat(omsetBln[m]) > 0) ? rupiah(omsetBln[m]) : '-';
                var nilaiKunjungan = (typeof kunjunganBln[m] !== 'undefined' && parseInt(kunjunganBln[m], 10) > 0) ? kunjunganBln[m] : '-';

                $('#rowOmset').append('<td class="text-center ' + bgClass + '">' + nilaiOmset + '</td>');
                $('#rowKunjungan').append('<td class="text-center ' + bgClass + '">' + nilaiKunjungan + '</td>');
            }

            $('#headerOmset').append('<th class="text-center bg-dark text-white">Total ' + tahun + '</th>');
            $('#headerKunjungan').append('<th class="text-center bg-dark text-white">Total ' + tahun + '</th>');

            $('#rowOmset').append('<td class="text-center font-weight-bold bg-light text-primary">Rp ' + rupiah(totalOmset) + '</td>');
            $('#rowKunjungan').append('<td class="text-center font-weight-bold bg-light text-primary">' + totalKunjungan + '</td>');
            return;
        }

        $('#headerOmset').html('<th width="180">Tanggal</th>');
        $('#headerKunjungan').html('<th width="180">Tanggal</th>');

        $('#rowOmset').html('<th>Omset</th>');
        $('#rowKunjungan').html('<th>Kunjungan</th>');

        var omset = {};
        var kunjungan = {};
        $.each(res.omset,function(i,e){
            var tgl = parseInt(e.tanggal.substr(8,2));
            omset[tgl]=e.omset;
        });

        $.each(res.kunjungan,function(i,e){
            var tgl = parseInt(e.tanggal.substr(8,2));
            kunjungan[tgl]=e.jumlah;
        });

        for(var i=1;i<=res.jumlah_hari;i++)
        {
            var tanggal = tahun+'-'+bulan+'-'+pad(i);

            var namaHari = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
            var hari = new Date(tanggal).getDay();

            var bgClass = (hari == 0) ? 'bg-fuchsia' : '';

            $('#headerOmset').append(
                '<td class="text-center '+bgClass+'">'+namaHari[hari]+'<br>'+i+'</td>'
                );

            $('#headerKunjungan').append(
                '<td class="text-center '+bgClass+'">'+namaHari[hari]+'<br>'+i+'</td>'
                );

            var nilaiOmset='-';
            if(typeof omset[i] !== 'undefined' && parseFloat(omset[i]) > 0)
                nilaiOmset = rupiah(omset[i]);

            var nilaiKunjungan='-';
            if(typeof kunjungan[i] !== 'undefined' && parseInt(kunjungan[i], 10) > 0)
                nilaiKunjungan = kunjungan[i];

            $('#rowOmset').append(
                '<td class="text-center cell-omset '+bgClass+'" '+
                'data-tanggal="'+tanggal+'" '+
                'style="cursor:pointer">'+
                nilaiOmset+
                '</td>'
                );

            $('#rowKunjungan').append(
                '<td class="text-center cell-kunjungan '+bgClass+'" '+
                'data-tanggal="'+tanggal+'" '+
                'style="cursor:pointer">'+
                nilaiKunjungan+
                '</td>'
                );
        }
    }

    function pad(n) {
        return n<10 ? '0'+n : n;
    }

    function rupiah(angka) {
        angka = parseFloat(angka);
        if(isNaN(angka))
            angka = 0;

        return angka.toLocaleString('id-ID',
        {
            minimumFractionDigits:0,
            maximumFractionDigits:0
        }
        );
    }

    $(document).on('click','.cell-omset',function(){
        var tanggal = $(this).data('tanggal');
        $('#modalOmset').modal('show');
        $('#omsetTanggal').val($(this).data('tanggal'));
        $('#tanggalOmsetText').val(formatTanggalIndonesia(tanggal));
        $('#omsetSwk').val($('[name=pilih_swk]').val());

        var nilai = $(this).text().replace(/[^\d]/g,'');
        $('#nilaiOmset').val(nilai=='' ? 0 : nilai);
    });

    $(document).on('click','.cell-kunjungan',function(){
        var tanggal = $(this).data('tanggal');
        $('#modalKunjungan').modal('show');
        $('#kunjunganTanggal').val($(this).data('tanggal'));
        $('#tanggalKunjunganText').val(formatTanggalIndonesia(tanggal));
        $('#kunjunganSwk').val($('[name=pilih_swk]').val());

        var jml = $.trim($(this).text());
        $('#jumlahKunjungan').val(jml=='' ? 0 : jml);
    });

    function formatTanggalIndonesia(tanggal) {
        var p = tanggal.split('-');
        return p[2]+'-'+p[1]+'-'+p[0];
    }

    $('#filter_bulan_tahun').datetimepicker({
        showClose: true,
        showTime: true,
        format: 'MM-YYYY',
    });
</script>