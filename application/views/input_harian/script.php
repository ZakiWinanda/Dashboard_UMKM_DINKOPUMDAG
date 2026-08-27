<script>
var BASE_URL     = '<?= base_url(); ?>';
var NAMA_BULAN   = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
var NAMA_HARI    = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
var HARI_PANJANG = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

// ─── Global State ───────────────────────────────────────────
var ST = {
    idswk_lokal : '',
    idswk_api   : '',
    nama_swk    : '',
    tahun       : 0,
    bulan       : 0,
    hari        : 0,
    units       : [],          // array unit usaha dari API
    omset       : {},          // { id_unit: { 'YYYY-MM-DD': nominal } }
    kunjungan   : {},          // { id_unit: { 'YYYY-MM-DD': jumlah } }
};

$(document).ready(function() {

    // Init Select2
    $('#pilihSwk').select2({ dropdownParent: $('body'), width: '100%' });

    // Tombol Tampilkan
    $('#btnTampilkan').on('click', function() {
        var idswk   = $('#pilihSwk').val();
        var opt     = $('#pilihSwk option:selected');
        var namaSwk = opt.data('nama') || opt.text();
        var apiId   = opt.data('api-id') || idswk;
        var periode = $('#pilihPeriode').val();

        if (!idswk)   return Swal.fire('Perhatian','Pilih SWK terlebih dahulu.','warning');
        if (!periode) return Swal.fire('Perhatian','Pilih bulan dan tahun.','warning');

        loadData(idswk, apiId, namaSwk, periode);
    });

    // Simpan Omset
    $('#btnSimpanOmset').on('click', simpanOmset);
    $('#om_nilai').on('keydown', function(e){ if(e.key==='Enter') simpanOmset(); });
    $('#modalOmset').on('shown.bs.modal', function(){ $('#om_nilai').focus().select(); });

    // Simpan Kunjungan
    $('#btnSimpanKunjungan').on('click', simpanKunjungan);
    $('#kj_nilai').on('keydown', function(e){ if(e.key==='Enter') simpanKunjungan(); });
    $('#modalKunjungan').on('shown.bs.modal', function(){ $('#kj_nilai').focus().select(); });
});

// ─── Load Data dari Server ───────────────────────────────────
function loadData(idswk_lokal, idswk_api, nama_swk, periode)
{
    $('#emptyState').hide();
    $('#kalenderWrap').hide();
    $('#summaryRow').hide();
    $('#loadingState').show();

    $.ajax({
        url  : BASE_URL + 'input_harian/load_unit_usaha',
        type : 'POST',
        dataType : 'json',
        data : { idswk_lokal: idswk_lokal, idswk_api: idswk_api, periode: periode },
        success: function(res) {
            $('#loadingState').hide();

            if (!res.status) {
                Swal.fire('Info', res.message || 'Tidak ada data.', 'info');
                $('#emptyState').show();
                return;
            }

            // Simpan state
            ST.idswk_lokal = idswk_lokal;
            ST.idswk_api   = idswk_api;
            ST.nama_swk    = nama_swk;
            ST.tahun       = res.tahun;
            ST.bulan       = res.bulan;
            ST.hari        = res.jumlah_hari;
            ST.units       = res.units;
            ST.omset       = res.omset    || {};
            ST.kunjungan   = res.kunjungan || {};

            renderKalender();

            // Update summary
            var pBln = String(res.bulan).padStart(2,'0');
            var label = NAMA_BULAN[res.bulan-1] + ' ' + res.tahun;
            $('#badgePeriode').text(label).show();
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

    // Hitung total per hari dulu
    var dailyOmset     = {};
    var dailyKunjungan = {};

    for (var d = 1; d <= ST.hari; d++) {
        var tgl    = fmtTgl(ST.tahun, ST.bulan, d);
        var hari   = new Date(tgl).getDay();
        var isMggu = hari === 0;
        var isToday = tgl === todayStr;
        var cls = 'text-center' + (isMggu ? ' text-danger' : '');
        head += '<th class="' + cls + '">' + NAMA_HARI[hari] + '<br>' + d + '</th>';

        dailyOmset[tgl]     = 0;
        dailyKunjungan[tgl] = 0;
    }

    head += '</tr>';

    // Hitung sum per hari dari semua unit
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

    // Baris per unit usaha
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
            var isToday = tgl === todayStr;
            var colCls = (isMggu ? ' col-minggu' : '') + (isToday ? ' today-col' : '');

            // OMSET
            var omVal  = (ST.omset[uid] && ST.omset[uid][tgl]) ? parseFloat(ST.omset[uid][tgl]) : 0;
            var omTxt  = omVal > 0 ? rupiah(omVal) : '<span class="text-muted">-</span>';
            var omHas  = omVal > 0 ? ' has-omset' : '';
            rowsOm += '<td><div class="cell-input' + colCls + omHas + '" id="om_' + uid + '_' + tgl +
                      '" onclick="bukaModalOmset(\'' + uid + '\',\'' + tgl + '\',' + i + ')">' + omTxt + '</div></td>';

            // KUNJUNGAN
            var kjVal  = (ST.kunjungan[uid] && ST.kunjungan[uid][tgl]) ? parseInt(ST.kunjungan[uid][tgl]) : 0;
            var kjTxt  = kjVal > 0 ? kjVal.toLocaleString('id-ID') : '<span class="text-muted">-</span>';
            var kjHas  = kjVal > 0 ? ' has-kunjungan' : '';
            rowsKj += '<td><div class="cell-input' + colCls + kjHas + '" id="kj_' + uid + '_' + tgl +
                      '" onclick="bukaModalKunjungan(\'' + uid + '\',\'' + tgl + '\',' + i + ')">' + kjTxt + '</div></td>';
        }

        rowsOm += '</tr>';
        rowsKj += '</tr>';
    });

    // Baris footer (total per hari)
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

// ─── Buka Modal ─────────────────────────────────────────────
function bukaModalOmset(uid, tgl, idx)
{
    var unit = ST.units[idx];
    var val  = (ST.omset[uid] && ST.omset[uid][tgl]) ? parseFloat(ST.omset[uid][tgl]) : '';
    $('#om_id_unit').val(uid);
    $('#om_kode_unit').val(unit.kodeUsahaSwk);
    $('#om_nama_stand').val(unit.namaStand);
    $('#om_idswk').val(ST.idswk_lokal);
    $('#om_tanggal').val(tgl);
    $('#om_nama_unit').html('[' + (unit.namaStand||'-') + '] ' + unit.namaUsaha + '<br><small class="text-muted">' + (unit.namaPedagang||'') + '</small>');
    $('#om_label_tgl').text(fmtTglHuman(tgl));
    $('#om_nilai').val(val > 0 ? val : '');
    $('#modalOmset').modal('show');
}

function bukaModalKunjungan(uid, tgl, idx)
{
    var unit = ST.units[idx];
    var val  = (ST.kunjungan[uid] && ST.kunjungan[uid][tgl]) ? parseInt(ST.kunjungan[uid][tgl]) : '';
    $('#kj_id_unit').val(uid);
    $('#kj_kode_unit').val(unit.kodeUsahaSwk);
    $('#kj_nama_stand').val(unit.namaStand);
    $('#kj_idswk').val(ST.idswk_lokal);
    $('#kj_tanggal').val(tgl);
    $('#kj_nama_unit').html('[' + (unit.namaStand||'-') + '] ' + unit.namaUsaha + '<br><small class="text-muted">' + (unit.namaPedagang||'') + '</small>');
    $('#kj_label_tgl').text(fmtTglHuman(tgl));
    $('#kj_nilai').val(val > 0 ? val : '');
    $('#modalKunjungan').modal('show');
}

// ─── Simpan ─────────────────────────────────────────────────
function simpanOmset()
{
    var uid     = $('#om_id_unit').val();
    var tgl     = $('#om_tanggal').val();
    var rawVal  = $('#om_nilai').val().replace(/[^\d]/g,'');
    var nilai   = parseInt(rawVal, 10) || 0;

    $('#btnSimpanOmset').prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url : BASE_URL + 'input_harian/save_omset_unit',
        type: 'POST', dataType: 'json',
        data: {
            id_unit_usaha  : uid,
            nama_unit_usaha: ST.units.find(function(u){return u.id===uid;})?.namaUsaha || '',
            kode_unit_usaha: $('#om_kode_unit').val(),
            nama_stand     : $('#om_nama_stand').val(),
            idswk          : ST.idswk_lokal,
            tanggal        : tgl,
            omset          : nilai
        },
        success: function(res) {
            if (res.status) {
                if (!ST.omset[uid]) ST.omset[uid] = {};
                ST.omset[uid][tgl] = nilai;
                updateCellOmset(uid, tgl, nilai);
                updateFootOmset();
                updateTotalOmset(res.total);
                $('#modalOmset').modal('hide');
                toast('Omset disimpan!');
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: function() { Swal.fire('Error','Gagal terhubung ke server.','error'); },
        complete: function() { $('#btnSimpanOmset').prop('disabled',false).html('<i class="fa fa-save mr-1"></i>Simpan'); }
    });
}

function simpanKunjungan()
{
    var uid   = $('#kj_id_unit').val();
    var tgl   = $('#kj_tanggal').val();
    var nilai = parseInt($('#kj_nilai').val(), 10) || 0;

    $('#btnSimpanKunjungan').prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.ajax({
        url : BASE_URL + 'input_harian/save_kunjungan_unit',
        type: 'POST', dataType: 'json',
        data: {
            id_unit_usaha  : uid,
            nama_unit_usaha: ST.units.find(function(u){return u.id===uid;})?.namaUsaha || '',
            kode_unit_usaha: $('#kj_kode_unit').val(),
            nama_stand     : $('#kj_nama_stand').val(),
            idswk          : ST.idswk_lokal,
            tanggal        : tgl,
            jumlah         : nilai
        },
        success: function(res) {
            if (res.status) {
                if (!ST.kunjungan[uid]) ST.kunjungan[uid] = {};
                ST.kunjungan[uid][tgl] = nilai;
                updateCellKunjungan(uid, tgl, nilai);
                updateFootKunjungan();
                updateTotalKunjungan(res.total);
                $('#modalKunjungan').modal('hide');
                toast('Kunjungan disimpan!');
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: function() { Swal.fire('Error','Gagal terhubung ke server.','error'); },
        complete: function() { $('#btnSimpanKunjungan').prop('disabled',false).html('<i class="fa fa-save mr-1"></i>Simpan'); }
    });
}

// ─── Update UI ───────────────────────────────────────────────
function updateCellOmset(uid, tgl, nilai)
{
    var cell = $('#om_' + uid + '_' + tgl);
    cell.html(nilai > 0 ? rupiah(nilai) : '<span class="text-muted">-</span>');
    cell.toggleClass('has-omset', nilai > 0);
}

function updateCellKunjungan(uid, tgl, nilai)
{
    var cell = $('#kj_' + uid + '_' + tgl);
    cell.html(nilai > 0 ? nilai.toLocaleString('id-ID') : '<span class="text-muted">-</span>');
    cell.toggleClass('has-kunjungan', nilai > 0);
}

function updateFootOmset()
{
    var cols = [];
    for (var d = 1; d <= ST.hari; d++) cols.push(0);

    $.each(ST.omset, function(uid, tglMap) {
        $.each(tglMap, function(tgl, val) {
            var day = parseInt(tgl.substr(8,2),10);
            cols[day-1] += parseFloat(val)||0;
        });
    });

    var cells = $('#footOmset tr td');
    cells.each(function(i) {
        if (i === 0) return;
        var v = cols[i-1] || 0;
        $(this).text(v > 0 ? rupiah(v) : '-');
    });
}

function updateFootKunjungan()
{
    var cols = [];
    for (var d = 1; d <= ST.hari; d++) cols.push(0);

    $.each(ST.kunjungan, function(uid, tglMap) {
        $.each(tglMap, function(tgl, val) {
            var day = parseInt(tgl.substr(8,2),10);
            cols[day-1] += parseInt(val)||0;
        });
    });

    var cells = $('#footKunjungan tr td');
    cells.each(function(i) {
        if (i === 0) return;
        var v = cols[i-1] || 0;
        $(this).text(v > 0 ? v.toLocaleString('id-ID') : '-');
    });
}

function updateTotalOmset(total)
{
    total = parseFloat(total)||0;
    $('#totalOmsetSwk').text(rupiah(total));
    $('#headerTotalOmset').text(rupiah(total));
}

function updateTotalKunjungan(total)
{
    total = parseInt(total)||0;
    $('#totalKunjunganSwk').text(fmtKj(total));
    $('#headerTotalKunjungan').text(fmtKj(total));
}

// ─── Helpers ─────────────────────────────────────────────────
function fmtTgl(y, m, d)
{
    return y + '-' + String(m).padStart(2,'0') + '-' + String(d).padStart(2,'0');
}

function fmtTglHuman(tgl)
{
    var p = tgl.split('-');
    var hari = new Date(tgl).getDay();
    return HARI_PANJANG[hari] + ', ' + parseInt(p[2],10) + ' ' + NAMA_BULAN[parseInt(p[1],10)-1] + ' ' + p[0];
}

function getToday()
{
    var n = new Date();
    return n.getFullYear() + '-' + String(n.getMonth()+1).padStart(2,'0') + '-' + String(n.getDate()).padStart(2,'0');
}

function rupiah(v)
{
    v = parseFloat(v)||0;
    return 'Rp ' + v.toLocaleString('id-ID');
}

function fmtKj(v)
{
    v = parseInt(v)||0;
    return v.toLocaleString('id-ID') + ' orang';
}

function toast(msg)
{
    Swal.fire({ icon:'success', title:msg, toast:true, position:'top-end',
                showConfirmButton:false, timer:2000, timerProgressBar:true });
}
</script>
