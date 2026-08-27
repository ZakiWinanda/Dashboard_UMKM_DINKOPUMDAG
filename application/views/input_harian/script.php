<script>
var BASE_URL     = '<?= base_url(); ?>';
var NAMA_BULAN   = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
var NAMA_HARI    = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

var ST = {
    idswk_lokal : '',
    idswk_api   : '',
    nama_swk    : '',
    tanggal     : '',
    tahun       : 0,
    bulan       : 0,
    hari        : 0,
    units       : [],
    omset       : {},
    kunjungan   : {}
};

$(document).ready(function() {

    $('#pilihSwk').select2({ dropdownParent: $('body'), width: '100%' });

    // Tombol Buka Modal Panduan
    $('#btnPanduan').on('click', function() {
        $('#modalPanduan').modal('show');
    });

    // Tampilkan Data Grid
    $('#btnTampilkan').on('click', function() {
        var idswk   = $('#pilihSwk').val();
        var opt     = $('#pilihSwk option:selected');
        var namaSwk = opt.data('nama') || opt.text();
        var apiId   = opt.data('api-id') || idswk;
        var tanggal = $('#pilihTanggal').val();

        if (!idswk)   return Swal.fire('Perhatian','Pilih SWK terlebih dahulu.','warning');
        if (!tanggal) return Swal.fire('Perhatian','Pilih tanggal terlebih dahulu.','warning');

        loadData(idswk, apiId, namaSwk, tanggal);
    });

    // Unduh Template Excel (.xls per Hari)
    $('#btnDownloadTemplate').on('click', function() {
        var idswk   = $('#pilihSwk').val();
        var opt     = $('#pilihSwk option:selected');
        var apiId   = opt.data('api-id') || idswk;
        var tanggal = $('#pilihTanggal').val();

        if (!idswk)   return Swal.fire('Perhatian','Pilih SWK terlebih dahulu sebelum mengunduh template.','warning');
        if (!tanggal) return Swal.fire('Perhatian','Pilih tanggal terlebih dahulu.','warning');

        var url = BASE_URL + 'input_harian/download_template?idswk_lokal=' + encodeURIComponent(idswk) +
                  '&idswk_api=' + encodeURIComponent(apiId) +
                  '&tanggal=' + encodeURIComponent(tanggal);

        window.location.href = url;
    });

    // Tombol Buka Modal Upload
    $('#btnUploadExcel').on('click', function() {
        var idswk   = $('#pilihSwk').val();
        var opt     = $('#pilihSwk option:selected');
        var namaSwk = opt.data('nama') || opt.text();
        var tanggal = $('#pilihTanggal').val();

        if (!idswk)   return Swal.fire('Perhatian','Pilih SWK terlebih dahulu.','warning');
        if (!tanggal) return Swal.fire('Perhatian','Pilih tanggal terlebih dahulu.','warning');

        $('#up_idswk_lokal').val(idswk);
        $('#up_tanggal').val(tanggal);
        $('#up_label_swk').val(namaSwk);
        $('#up_label_tanggal').val(fmtTglHuman(tanggal));
        $('#file_excel').val('');
        $('#fileLabel').text('Pilih file…');

        $('#modalUpload').modal('show');
    });

    // Custom File Label
    $('#file_excel').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $('#fileLabel').text(fileName || 'Pilih file…');
    });

    // Process Upload Form
    $('#formUploadExcel').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        $('#btnProsesUpload').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Mengimpor…');

        $.ajax({
            url         : BASE_URL + 'input_harian/upload_excel',
            type        : 'POST',
            data        : formData,
            contentType : false,
            processData : false,
            dataType    : 'json',
            success: function(res) {
                $('#btnProsesUpload').prop('disabled', false).html('<i class="fa fa-upload mr-1"></i> Impor Data');

                if (res.status) {
                    $('#modalUpload').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukses Impor!',
                        text: res.message
                    });

                    // Auto reload grid data
                    var idswk   = $('#pilihSwk').val();
                    var opt     = $('#pilihSwk option:selected');
                    var namaSwk = opt.data('nama') || opt.text();
                    var apiId   = opt.data('api-id') || idswk;
                    var tanggal = $('#pilihTanggal').val();
                    loadData(idswk, apiId, namaSwk, tanggal);

                } else {
                    Swal.fire('Gagal Impor', res.message || 'Terjadi kesalahan saat membaca file.', 'error');
                }
            },
            error: function() {
                $('#btnProsesUpload').prop('disabled', false).html('<i class="fa fa-upload mr-1"></i> Impor Data');
                Swal.fire('Error', 'Gagal mengirim file ke server.', 'error');
            }
        });
    });
});

// ─── Load Data dari Server ───────────────────────────────────
function loadData(idswk_lokal, idswk_api, nama_swk, tanggal)
{
    $('#emptyState').hide();
    $('#kalenderWrap').hide();
    $('#summaryRow').hide();
    $('#loadingState').show();

    $.ajax({
        url  : BASE_URL + 'input_harian/load_unit_usaha',
        type : 'POST',
        dataType : 'json',
        data : { idswk_lokal: idswk_lokal, idswk_api: idswk_api, tanggal: tanggal },
        success: function(res) {
            $('#loadingState').hide();

            if (!res.status) {
                Swal.fire('Info', res.message || 'Tidak ada data.', 'info');
                $('#emptyState').show();
                return;
            }

            ST.idswk_lokal = idswk_lokal;
            ST.idswk_api   = idswk_api;
            ST.nama_swk    = nama_swk;
            ST.tanggal     = tanggal;
            ST.tahun       = res.tahun;
            ST.bulan       = res.bulan;
            ST.hari        = calDays(res.tahun, res.bulan);
            ST.units       = res.units;
            ST.omset       = res.omset    || {};
            ST.kunjungan   = res.kunjungan || {};

            renderKalender();

            var label = fmtTglHuman(tanggal);
            $('#badgeTanggal').text(label).show();
            $('#badgeSwk').text(nama_swk).show();

            $('#totalUnit').text(res.units.length);
            $('#totalOmsetSwk').text(rupiah(res.total_omset));
            $('#headerTotalOmset').text(rupiah(res.total_omset));
            $('#totalKunjunganSwk').text(fmtKj(res.total_kunjungan));
            $('#headerTotalKunjungan').text(fmtKj(res.total_kunjungan));

            $('#summaryRow').css('display','flex');
            $('#kalenderWrap').show();
        },
        error: function() {
            $('#loadingState').hide();
            Swal.fire('Error','Gagal memuat data.','error');
        }
    });
}

// ─── Render Grid Kalender ────────────────────────────────────
function renderKalender()
{
    var todayStr = getToday();
    var head = '<tr><th class="th-label">Unit Usaha / Tgl</th>';
    var footOm = '<tr><td class="foot-label">Total Omset</td>';
    var footKj = '<tr><td class="foot-label">Total Kunjungan</td>';

    var dailyOmset     = {};
    var dailyKunjungan = {};

    for (var d = 1; d <= ST.hari; d++) {
        var tgl    = fmtTgl(ST.tahun, ST.bulan, d);
        var hari   = new Date(tgl).getDay();
        var isMggu = hari === 0;
        var isSelected = tgl === ST.tanggal;
        var cls = 'text-center' + (isMggu ? ' text-danger' : '') + (isSelected ? ' bg-warning text-dark font-weight-bold' : '');
        head += '<th class="' + cls + '">' + NAMA_HARI[hari] + '<br>' + d + '</th>';

        dailyOmset[tgl]     = 0;
        dailyKunjungan[tgl] = 0;
    }
    head += '</tr>';

    $.each(ST.omset, function(uid, tglMap) {
        $.each(tglMap, function(tgl, val) {
            if (dailyOmset[tgl] !== undefined) dailyOmset[tgl] += (parseFloat(val)||0);
        });
    });
    $.each(ST.kunjungan, function(uid, tglMap) {
        $.each(tglMap, function(tgl, val) {
            if (dailyKunjungan[tgl] !== undefined) dailyKunjungan[tgl] += (parseInt(val)||0);
        });
    });

    var rowsOm = '';
    var rowsKj = '';

    $.each(ST.units, function(i, unit) {
        var uid      = unit.id;
        var label    = (unit.namaStand ? '[' + unit.namaStand + '] ' : '') + unit.namaUsaha;
        var subLabel = unit.namaPedagang ? '<small class="text-muted">' + unit.namaPedagang + '</small>' : '';

        rowsOm += '<tr><td class="td-label">' + label + '<br>' + subLabel + '</td>';
        rowsKj += '<tr><td class="td-label">' + label + '<br>' + subLabel + '</td>';

        for (var d = 1; d <= ST.hari; d++) {
            var tgl    = fmtTgl(ST.tahun, ST.bulan, d);
            var hari   = new Date(tgl).getDay();
            var isMggu = hari === 0;
            var isSelected = tgl === ST.tanggal;
            var colCls = (isMggu ? ' col-minggu' : '') + (isSelected ? ' today-col' : '');

            var omVal  = (ST.omset[uid] && ST.omset[uid][tgl]) ? parseFloat(ST.omset[uid][tgl]) : 0;
            var omTxt  = omVal > 0 ? rupiah(omVal) : '<span class="text-muted">-</span>';
            var omHas  = omVal > 0 ? ' has-omset' : '';
            rowsOm += '<td><div class="cell-input' + colCls + omHas + '">' + omTxt + '</div></td>';

            var kjVal  = (ST.kunjungan[uid] && ST.kunjungan[uid][tgl]) ? parseInt(ST.kunjungan[uid][tgl]) : 0;
            var kjTxt  = kjVal > 0 ? kjVal.toLocaleString('id-ID') : '<span class="text-muted">-</span>';
            var kjHas  = kjVal > 0 ? ' has-kunjungan' : '';
            rowsKj += '<td><div class="cell-input' + colCls + kjHas + '">' + kjTxt + '</div></td>';
        }

        rowsOm += '</tr>';
        rowsKj += '</tr>';
    });

    for (var d = 1; d <= ST.hari; d++) {
        var tgl = fmtTgl(ST.tahun, ST.bulan, d);
        var sumOm = dailyOmset[tgl] || 0;
        var sumKj = dailyKunjungan[tgl] || 0;
        footOm += '<td class="foot-total">' + (sumOm > 0 ? rupiah(sumOm) : '-') + '</td>';
        footKj += '<td class="foot-total">' + (sumKj > 0 ? sumKj.toLocaleString('id-ID') : '-') + '</td>';
    }
    footOm += '</tr>';
    footKj += '</tr>';

    $('#headOmset').html(head);
    $('#bodyOmset').html(rowsOm);
    $('#footOmset').html(footOm);
    $('#headKunjungan').html(head);
    $('#bodyKunjungan').html(rowsKj);
    $('#footKunjungan').html(footKj);
}

function calDays(y, m) { return new Date(y, m, 0).getDate(); }
function fmtTgl(y, m, d) { return y + '-' + String(m).padStart(2,'0') + '-' + String(d).padStart(2,'0'); }
function fmtTglHuman(tgl) {
    if (!tgl) return '';
    var p = tgl.split('-');
    return parseInt(p[2],10) + ' ' + NAMA_BULAN[parseInt(p[1],10)-1] + ' ' + p[0];
}
function getToday() { var n = new Date(); return n.getFullYear() + '-' + String(n.getMonth()+1).padStart(2,'0') + '-' + String(n.getDate()).padStart(2,'0'); }
function rupiah(v) { v = parseFloat(v)||0; return 'Rp ' + v.toLocaleString('id-ID'); }
function fmtKj(v) { v = parseInt(v)||0; return v.toLocaleString('id-ID') + ' orang'; }
</script>
