<script>
var BASE_URL     = '<?= base_url(); ?>';
var NAMA_BULAN   = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
var NAMA_BULAN_S = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

var ST = {
    idswk_lokal : '',
    idswk_api   : '',
    nama_swk    : '',
    tanggal     : '',
    tahun       : 0,
    bulan       : 0,
    units       : [],
    omset       : {},
    kunjungan   : {}
};

$(document).ready(function() {

    $('#pilihKecamatan').select2({ dropdownParent: $('body'), width: '100%' });

    // Tombol Buka Modal Panduan
    $('#btnPanduan').on('click', function() {
        $('#modalPanduan').modal('show');
    });

    // Tampilkan Data Grid
    $('#btnTampilkan').on('click', function() {
        var idswk   = $('#pilihKecamatan').val();
        var opt     = $('#pilihKecamatan option:selected');
        var namaSwk = opt.data('nama') || opt.text();
        var apiId   = opt.data('api-id') || idswk;
        var tanggal = $('#pilihBulan').val(); // format YYYY-MM

        if (!idswk)   return Swal.fire('Perhatian','Pilih Kecamatan terlebih dahulu.','warning');
        if (!tanggal) return Swal.fire('Perhatian','Pilih bulan input terlebih dahulu.','warning');

        loadData(idswk, apiId, namaSwk, tanggal);
    });

    // Unduh Template Excel (.xlsx per Bulan)
    $('#btnDownloadTemplate').on('click', function() {
        var idswk   = $('#pilihKecamatan').val();
        var opt     = $('#pilihKecamatan option:selected');
        var apiId   = opt.data('api-id') || idswk;
        var tanggal = $('#pilihBulan').val();

        if (!idswk)   return Swal.fire('Perhatian','Pilih Kecamatan terlebih dahulu sebelum mengunduh template.','warning');
        if (!tanggal) return Swal.fire('Perhatian','Pilih bulan terlebih dahulu.','warning');

        var url = BASE_URL + 'input_bulanan/download_template?idswk_lokal=' + encodeURIComponent(idswk) +
                  '&idswk_api=' + encodeURIComponent(apiId) +
                  '&tanggal=' + encodeURIComponent(tanggal);

        window.location.href = url;
    });

    // Tombol Buka Modal Upload
    $('#btnUploadExcel').on('click', function() {
        var idswk   = $('#pilihKecamatan').val();
        var opt     = $('#pilihKecamatan option:selected');
        var namaSwk = opt.data('nama') || opt.text();
        var tanggal = $('#pilihBulan').val();

        if (!idswk)   return Swal.fire('Perhatian','Pilih Kecamatan terlebih dahulu.','warning');
        if (!tanggal) return Swal.fire('Perhatian','Pilih bulan terlebih dahulu.','warning');

        $('#up_idswk_lokal').val(idswk);
        $('#up_tanggal').val(tanggal);
        $('#up_label_kecamatan').val(namaSwk);
        $('#up_label_bulan').val(fmtBulanHuman(tanggal));
        $('#file_excel').val('');
        $('#fileLabel').text('Pilih file…');

        $('#modalUpload').modal('show');
    });

    // Custom File Label
    $('#file_excel').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $('#fileLabel').text(fileName || 'Pilih file…');
    });

    // Submit Upload Excel Form
    $('#formUploadExcel').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        var btn = $('#btnProsesUpload');

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Mengimpor…');

        $.ajax({
            url: BASE_URL + 'input_bulanan/upload_excel',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-upload mr-1"></i> Impor Data');

                if (res.status) {
                    $('#modalUpload').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: res.message || 'Data omset & transaksi bulanan berhasil disimpan.',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        var idswk   = $('#up_idswk_lokal').val();
                        var opt     = $('#pilihKecamatan option:selected');
                        var namaSwk = opt.data('nama') || opt.text();
                        var apiId   = opt.data('api-id') || idswk;
                        var tanggal = $('#up_tanggal').val();
                        loadData(idswk, apiId, namaSwk, tanggal);
                    });
                } else {
                    Swal.fire('Gagal Impor', res.message || 'Terjadi kesalahan saat membaca file.', 'error');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-upload mr-1"></i> Impor Data');
                Swal.fire('Error Server', 'Tidak dapat memproses file: ' + xhr.statusText, 'error');
            }
        });
    });

    // Auto-load jika hanya ada 1 kecamatan
    if ($('#pilihKecamatan option').length === 2) {
        $('#pilihKecamatan').val($('#pilihKecamatan option:last').val()).trigger('change');
        $('#btnTampilkan').trigger('click');
    }
});

/**
 * Load Data Unit Usaha Kecamatan & Nilai Bulanan
 */
function loadData(idswk_lokal, idswk_api, namaSwk, tanggal)
{
    $('#emptyState').hide();
    $('#kalenderWrap').hide();
    $('#summaryRow').hide();
    $('#loadingState').show();

    var parts = tanggal.split('-');
    var tahun = parseInt(parts[0], 10) || new Date().getFullYear();
    var bulan = parseInt(parts[1], 10) || (new Date().getMonth() + 1);

    ST.idswk_lokal = idswk_lokal;
    ST.idswk_api   = idswk_api;
    ST.nama_swk    = namaSwk;
    ST.tanggal     = tanggal;
    ST.tahun       = tahun;
    ST.bulan       = bulan;

    $.ajax({
        url: BASE_URL + 'input_bulanan/load_unit_usaha',
        type: 'POST',
        data: {
            idswk_lokal : idswk_lokal,
            idswk_api   : idswk_api,
            tanggal     : tanggal
        },
        dataType: 'json',
        success: function(res) {
            $('#loadingState').hide();

            if (!res.status) {
                $('#emptyState').show();
                return Swal.fire('Informasi', res.message || 'Gagal memuat data.', 'info');
            }

            ST.units     = res.units || [];
            ST.omset     = res.omset || {};
            ST.kunjungan = res.kunjungan || {};

            renderGridBulanan(res);

            $('#badgeBulan').text(fmtBulanHuman(tanggal)).show();
            $('#badgeKecamatan').text(namaSwk).show();

            $('#totalUnit').text(ST.units.length + ' Usaha');
            $('#totalOmsetKec').text(rupiah(res.total_omset || 0));
            $('#totalKunjunganKec').text(fmtKj(res.total_kunjungan || 0));
            $('#headerTotalOmset').text(rupiah(res.total_omset || 0));
            $('#headerTotalKunjungan').text(fmtKj(res.total_kunjungan || 0));

            $('#summaryRow').show();
            $('#kalenderWrap').show();
        },
        error: function(xhr) {
            $('#loadingState').hide();
            $('#emptyState').show();
            Swal.fire('Error Server', 'Gagal memuat data dari server: ' + xhr.statusText, 'error');
        }
    });
}

/**
 * Render Tabel Grid 12 Bulan untuk Omset & Kunjungan Kecamatan
 */
function renderGridBulanan(res)
{
    var head = '<tr><th class="th-label" style="min-width:240px;">Industri Rumahan / Pelaku Usaha</th>';
    for (var m = 1; m <= 12; m++) {
        var isCur = (m === ST.bulan);
        var cls = isCur ? ' class="bg-primary text-white text-center"' : ' class="text-center"';
        head += '<th' + cls + '>' + NAMA_BULAN_S[m - 1] + '<br><small>' + ST.tahun + '</small></th>';
    }
    head += '<th class="text-center bg-dark text-white" style="min-width:110px;">Total ' + ST.tahun + '</th></tr>';

    var rowsOm = '';
    var rowsKj = '';
    var footOm = '<tr><td class="foot-label font-weight-bold">TOTAL OMSET KECAMATAN</td>';
    var footKj = '<tr><td class="foot-label font-weight-bold">TOTAL KUNJUNGAN / TRANSAKSI</td>';

    var monthOmsetSum = {};
    var monthKunjSum  = {};

    $.each(ST.units, function(idx, u) {
        var uid  = u.id;
        var nama = u.namaUsaha || u.namaStand || 'Industri Rumahan';
        var ped  = u.namaPedagang ? '<br><small class="text-muted"><i class="fa fa-user mr-1"></i>' + u.namaPedagang + '</small>' : '';
        var lbl  = '<div class="td-label font-weight-bold">' + nama + ped + '</div>';

        rowsOm += '<tr><td>' + lbl + '</td>';
        rowsKj += '<tr><td>' + lbl + '</td>';

        var userTotalOm = 0;
        var userTotalKj = 0;

        for (var m = 1; m <= 12; m++) {
            var isCur = (m === ST.bulan);
            var colCls = isCur ? ' today-col' : '';

            // Omset Bulan m
            var omVal = (ST.omset[uid] && ST.omset[uid][m]) ? parseFloat(ST.omset[uid][m]) : 0;
            userTotalOm += omVal;
            monthOmsetSum[m] = (monthOmsetSum[m] || 0) + omVal;
            var omTxt = omVal > 0 ? rupiah(omVal) : '<span class="text-muted">-</span>';
            var omHas = omVal > 0 ? ' has-omset' : '';

            rowsOm += '<td><div class="cell-input' + colCls + omHas + '">' + omTxt + '</div></td>';

            // Kunjungan Bulan m
            var kjVal = (ST.kunjungan[uid] && ST.kunjungan[uid][m]) ? parseInt(ST.kunjungan[uid][m], 10) : 0;
            userTotalKj += kjVal;
            monthKunjSum[m] = (monthKunjSum[m] || 0) + kjVal;
            var kjTxt = kjVal > 0 ? kjVal.toLocaleString('id-ID') : '<span class="text-muted">-</span>';
            var kjHas = kjVal > 0 ? ' has-kunjungan' : '';

            rowsKj += '<td><div class="cell-input' + colCls + kjHas + '">' + kjTxt + '</div></td>';
        }

        rowsOm += '<td class="text-center font-weight-bold bg-light text-primary">' + rupiah(userTotalOm) + '</td></tr>';
        rowsKj += '<td class="text-center font-weight-bold bg-light text-primary">' + userTotalKj.toLocaleString('id-ID') + '</td></tr>';
    });

    var grandTotalOm = 0;
    var grandTotalKj = 0;
    for (var m = 1; m <= 12; m++) {
        var sOm = monthOmsetSum[m] || 0;
        var sKj = monthKunjSum[m] || 0;
        grandTotalOm += sOm;
        grandTotalKj += sKj;
        footOm += '<td class="foot-total">' + (sOm > 0 ? rupiah(sOm) : '-') + '</td>';
        footKj += '<td class="foot-total">' + (sKj > 0 ? sKj.toLocaleString('id-ID') : '-') + '</td>';
    }
    footOm += '<td class="foot-total bg-dark text-white">' + rupiah(grandTotalOm) + '</td></tr>';
    footKj += '<td class="foot-total bg-dark text-white">' + grandTotalKj.toLocaleString('id-ID') + '</td></tr>';

    $('#headOmset').html(head);
    $('#bodyOmset').html(rowsOm);
    $('#footOmset').html(footOm);
    $('#headKunjungan').html(head);
    $('#bodyKunjungan').html(rowsKj);
    $('#footKunjungan').html(footKj);
}

function fmtBulanHuman(tglStr) {
    if (!tglStr) return '';
    var p = tglStr.split('-');
    var thn = p[0];
    var bln = parseInt(p[1], 10) - 1;
    return (NAMA_BULAN[bln] || '') + ' ' + thn;
}

function rupiah(v) {
    v = parseFloat(v) || 0;
    return 'Rp ' + v.toLocaleString('id-ID');
}

function fmtKj(v) {
    v = parseInt(v) || 0;
    return v.toLocaleString('id-ID') + ' orang';
}
</script>
