<script type="text/javascript">
    $(document).ready(function(){
        // 1. Inisialisasi Datepicker
        try {
            if ($.fn.datetimepicker) {
                $('#datepicker_bulan').datetimepicker({
                    format: 'MM-YYYY',
                    viewMode: 'months'
                });
            } else if ($.fn.datepicker) {
                $('#filter_bulan_tahun').datepicker({
                    format: "mm-yyyy",
                    startView: "months",
                    minViewMode: "months",
                    autoclose: true
                });
            }
        } catch(e) {
            console.warn("Datepicker init error:", e);
        }

        // 2. Event Tombol Submit Klik
        $('#btnLoad').on('click', function(e){
            e.preventDefault();
            loadData();
        });

        // 3. Event Ganti Pilihan Dropdown SWK
        $('#pilih_swk, [name=pilih_swk]').on('change', function(){
            loadData();
        });

        // 4. Auto Load data pertama kali jika SWK sudah terpilih
        if ($('#pilih_swk').val() || $('[name=pilih_swk]').val()) {
            loadData();
        }
    });

    /**
     * AJAX Load Data Rekap Harian
     */
    function loadData()
    {
        var idswk = $('#pilih_swk').val() || $('[name=pilih_swk]').val();
        var filter_bulan = $('#filter_bulan_tahun').val() || $('[name=filter_bulan_tahun]').val();

        if (!idswk) {
            Swal.fire('Peringatan', 'Silahkan pilih SWK terlebih dahulu.', 'warning');
            return;
        }

        $.ajax({
            url : "<?= site_url('capaian_harian/load_data') ?>",
            type : "POST",
            dataType : "json",
            data : {
                idswk : idswk,
                filter_bulan_tahun : filter_bulan
            },
            beforeSend: function(){
                $('#btnLoad').html('<i class="fa fa-spinner fa-spin"></i> Loading...').prop('disabled', true);
            },
            success: function(res){
                buildTable(res);
            },
            error: function(xhr){
                console.error("AJAX Error:", xhr.responseText);
                Swal.fire('Error', 'Gagal memuat data dari server.', 'error');
            },
            complete: function(){
                $('#btnLoad').html('<i class="fa fa-paper-plane mr-1"></i> Submit').prop('disabled', false);
            }
        });
    }

    /**
     * Render Tabel Kalender (Harian untuk SWK / Bulanan untuk Kecamatan)
     */
    function buildTable(res)
    {
        var filter = $('#filter_bulan_tahun').val() || $('[name=filter_bulan_tahun]').val() || '';
        var pecah  = filter.split('-');
        var bulan  = pecah[0] ? pad(parseInt(pecah[0], 10)) : '01';
        var tahun  = pecah[1] || res.tahun || '<?= date("Y") ?>';

        // Set Total Akumulasi
        var totalOmset     = res.total_omset_harian || 0;
        var totalKunjungan = res.total_kunjungan_harian || 0;

        $('#totalOmset').html('Rp ' + rupiah(totalOmset));
        $('#totalKunjungan').html(parseInt(totalKunjungan, 10).toLocaleString('id-ID'));

        if (res.is_kecamatan) {
            // ─── TAMPILAN BULANAN (UNTUK KECAMATAN) ───
            $('.card-title:contains("Omset")').text('Omset Bulanan (' + tahun + ')');
            $('.card-title:contains("Kunjungan")').text('Kunjungan / Transaksi Bulanan (' + tahun + ')');

            $('#headerOmset').html('<th style="min-width:150px;">Bulan</th>');
            $('#headerKunjungan').html('<th style="min-width:150px;">Bulan</th>');

            $('#rowOmset').html('<td class="font-weight-bold text-left">Omset (Rp)</td>');
            $('#rowKunjungan').html('<td class="font-weight-bold text-left">Jumlah Transaksi / Kunjungan</td>');

            var namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            // Map data bulanan (Key: 1..12)
            var mapOmsetBln     = {};
            var mapKunjunganBln = {};

            if (res.omset && res.omset.length > 0) {
                $.each(res.omset, function(i, e) {
                    mapOmsetBln[parseInt(e.bulan, 10)] = e.omset;
                });
            }

            if (res.kunjungan && res.kunjungan.length > 0) {
                $.each(res.kunjungan, function(i, e) {
                    mapKunjunganBln[parseInt(e.bulan, 10)] = e.jumlah;
                });
            }

            for (var m = 1; m <= 12; m++) {
                var labelBln = namaBulan[m - 1];
                var isCurBln = (parseInt(bulan, 10) === m);
                var bgClass  = isCurBln ? 'table-primary font-weight-bold' : '';

                // Header
                $('#headerOmset').append('<th class="text-center ' + bgClass + '">' + labelBln + '</th>');
                $('#headerKunjungan').append('<th class="text-center ' + bgClass + '">' + labelBln + '</th>');

                // Baris Data
                var valOmset     = (typeof mapOmsetBln[m] !== 'undefined' && parseFloat(mapOmsetBln[m]) > 0) ? rupiah(mapOmsetBln[m]) : '-';
                var valKunjungan = (typeof mapKunjunganBln[m] !== 'undefined' && parseInt(mapKunjunganBln[m], 10) > 0) ? parseInt(mapKunjunganBln[m], 10).toLocaleString('id-ID') : '-';

                $('#rowOmset').append('<td class="text-center ' + bgClass + '">' + valOmset + '</td>');
                $('#rowKunjungan').append('<td class="text-center ' + bgClass + '">' + valKunjungan + '</td>');
            }

            // Kolom Total Tahunan
            $('#headerOmset').append('<th class="text-center bg-dark text-white">Total Tahun ' + tahun + '</th>');
            $('#headerKunjungan').append('<th class="text-center bg-dark text-white">Total Tahun ' + tahun + '</th>');

            $('#rowOmset').append('<td class="text-center font-weight-bold bg-light text-primary">Rp ' + rupiah(totalOmset) + '</td>');
            $('#rowKunjungan').append('<td class="text-center font-weight-bold bg-light text-primary">' + parseInt(totalKunjungan, 10).toLocaleString('id-ID') + '</td>');
            return;
        }

        // ─── TAMPILAN HARIAN (UNTUK SWK) ───
        $('.card-title:contains("Omset")').text('Omset Harian');
        $('.card-title:contains("Kunjungan")').text('Kunjungan Harian');

        // Reset Header & Baris
        $('#headerOmset').html('<th style="min-width:150px;">Tanggal</th>');
        $('#headerKunjungan').html('<th style="min-width:150px;">Tanggal</th>');

        $('#rowOmset').html('<td class="font-weight-bold text-left">Omset (Rp)</td>');
        $('#rowKunjungan').html('<td class="font-weight-bold text-left">Jumlah Kunjungan</td>');

        if (!res.jumlah_hari || res.jumlah_hari === 0) {
            return;
        }

        // Map data harian (Key: 1..31)
        var mapOmset     = {};
        var mapKunjungan = {};

        if (res.omset && res.omset.length > 0) {
            $.each(res.omset, function(i, e) {
                var tgl = parseInt(e.tanggal.substr(8, 2), 10);
                mapOmset[tgl] = e.omset;
            });
        }

        if (res.kunjungan && res.kunjungan.length > 0) {
            $.each(res.kunjungan, function(i, e) {
                var tgl = parseInt(e.tanggal.substr(8, 2), 10);
                mapKunjungan[tgl] = e.jumlah;
            });
        }

        // Render kolom tanggal 1 s/d jumlah hari
        for (var i = 1; i <= res.jumlah_hari; i++) {
            var tglStr   = pad(i);
            var tglFull  = tahun + '-' + bulan + '-' + tglStr;
            var d        = new Date(parseInt(tahun, 10), parseInt(bulan, 10) - 1, i);
            var hariIdx  = d.getDay();
            var namaHari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][hariIdx];
            var bgClass  = (hariIdx === 0) ? 'bg-danger text-white' : '';

            // Header Kolom
            $('#headerOmset').append('<th class="text-center ' + bgClass + '">' + namaHari + '<br>' + i + '</th>');
            $('#headerKunjungan').append('<th class="text-center ' + bgClass + '">' + namaHari + '<br>' + i + '</th>');

            // Baris Data
            var valOmset     = (typeof mapOmset[i] !== 'undefined' && parseFloat(mapOmset[i]) > 0) ? rupiah(mapOmset[i]) : '-';
            var valKunjungan = (typeof mapKunjungan[i] !== 'undefined' && parseInt(mapKunjungan[i], 10) > 0) ? parseInt(mapKunjungan[i], 10).toLocaleString('id-ID') : '-';

            $('#rowOmset').append('<td class="text-center cell-omset ' + bgClass + '" data-tanggal="' + tglFull + '">' + valOmset + '</td>');
            $('#rowKunjungan').append('<td class="text-center cell-kunjungan ' + bgClass + '" data-tanggal="' + tglFull + '">' + valKunjungan + '</td>');
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
</script>