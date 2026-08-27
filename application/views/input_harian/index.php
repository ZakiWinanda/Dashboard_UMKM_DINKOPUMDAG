<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-7">
                    <h1 class="font-weight-bold m-0" style="font-size:1.25rem;">
                        <i class="fa fa-pencil-alt mr-2 text-primary"></i>INPUT OMSET &amp; KUNJUNGAN HARIAN
                    </h1>
                </div>
                <div class="col-sm-5 text-right">
                    <span id="badgePeriode" class="badge badge-primary px-3 py-2 mr-1" style="font-size:.8rem;display:none;"></span>
                    <span id="badgeSwk" class="badge badge-secondary px-3 py-2" style="font-size:.8rem;display:none;"></span>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- Filter -->
            <div class="card shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">SWK</label>
                            <select id="pilihSwk" class="form-control select2">
                                <option value="">— Pilih Sentra Wisata Kuliner —</option>
                                <?php foreach ($list_swk as $s): ?>
                                    <option value="<?= htmlspecialchars($s['idswk'] ?? $s['id']); ?>"
                                            data-api-id="<?= htmlspecialchars(!empty($s['api_swk_id']) ? $s['api_swk_id'] : ($s['idswk'] ?? $s['id'])); ?>"
                                            data-nama="<?= htmlspecialchars($s['nama_swk']); ?>">
                                        <?= htmlspecialchars($s['nama_swk']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">BULAN &amp; TAHUN</label>
                            <input type="month" id="pilihPeriode" class="form-control" value="<?= date('Y-m'); ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="button" id="btnTampilkan" class="btn btn-primary btn-block">
                                <i class="fa fa-search mr-1"></i> Tampilkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <div id="summaryRow" class="row" style="display:none;">
                <div class="col-md-4 mb-3">
                    <div class="info-box bg-gradient-teal shadow-sm mb-0">
                        <span class="info-box-icon"><i class="fas fa-store"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Unit Usaha Aktif</span>
                            <span class="info-box-number" id="totalUnit">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="info-box bg-gradient-success shadow-sm mb-0">
                        <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Omset SWK</span>
                            <span class="info-box-number" id="totalOmsetSwk">Rp 0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="info-box bg-gradient-info shadow-sm mb-0">
                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Kunjungan SWK</span>
                            <span class="info-box-number" id="totalKunjunganSwk">0 orang</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div id="loadingState" class="text-center py-5" style="display:none;">
                <div class="lds-dual-ring"></div>
                <p class="mt-3 text-muted small">Memuat data unit usaha dari server…</p>
            </div>

            <!-- Empty -->
            <div id="emptyState" class="text-center py-5">
                <i class="fa fa-store fa-4x text-muted mb-3 d-block"></i>
                <p class="text-muted">Pilih SWK dan periode, lalu klik <strong>Tampilkan</strong>.</p>
            </div>

            <!-- Nav Tab Omset / Kunjungan -->
            <div id="kalenderWrap" style="display:none;">

                <ul class="nav nav-tabs mb-0" id="tabInput">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" data-toggle="tab" href="#tabOmset">
                            <i class="fa fa-money-bill-wave mr-1 text-success"></i>Omset Harian
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" data-toggle="tab" href="#tabKunjungan">
                            <i class="fa fa-users mr-1 text-info"></i>Kunjungan Harian
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- ══ TAB OMSET ══ -->
                    <div class="tab-pane fade show active" id="tabOmset">
                        <div class="card card-success card-outline shadow-sm mb-0" style="border-top:0;">
                            <div class="card-header d-flex justify-content-between align-items-center py-2">
                                <span class="font-weight-bold small text-muted">Klik sel untuk memasukkan omset per unit usaha</span>
                                <span class="badge badge-success px-3 py-1">Total: <strong id="headerTotalOmset">Rp 0</strong></span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:520px;overflow:auto;">
                                    <table class="table table-bordered table-sm mb-0" id="tblOmset">
                                        <thead id="headOmset" style="position:sticky;top:0;z-index:2;background:#fff;"></thead>
                                        <tbody id="bodyOmset"></tbody>
                                        <tfoot id="footOmset" style="position:sticky;bottom:0;z-index:2;background:#fff;font-weight:bold;"></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══ TAB KUNJUNGAN ══ -->
                    <div class="tab-pane fade" id="tabKunjungan">
                        <div class="card card-info card-outline shadow-sm mb-0" style="border-top:0;">
                            <div class="card-header d-flex justify-content-between align-items-center py-2">
                                <span class="font-weight-bold small text-muted">Klik sel untuk memasukkan kunjungan per unit usaha</span>
                                <span class="badge badge-info px-3 py-1">Total: <strong id="headerTotalKunjungan">0 orang</strong></span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height:520px;overflow:auto;">
                                    <table class="table table-bordered table-sm mb-0" id="tblKunjungan">
                                        <thead id="headKunjungan" style="position:sticky;top:0;z-index:2;background:#fff;"></thead>
                                        <tbody id="bodyKunjungan"></tbody>
                                        <tfoot id="footKunjungan" style="position:sticky;bottom:0;z-index:2;background:#fff;font-weight:bold;"></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /tab-content -->
            </div><!-- /kalenderWrap -->

        </div>
    </section>
</div>

<!-- ═══ MODAL OMSET ═══ -->
<div class="modal fade" id="modalOmset" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title font-weight-bold"><i class="fa fa-money-bill-wave mr-2"></i>Input Omset</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="om_id_unit">
                <input type="hidden" id="om_kode_unit">
                <input type="hidden" id="om_nama_stand">
                <input type="hidden" id="om_idswk">
                <input type="hidden" id="om_tanggal">

                <div class="mb-3">
                    <div class="small text-muted">Unit Usaha</div>
                    <div class="font-weight-bold" id="om_nama_unit" style="font-size:.95rem;"></div>
                    <div class="small text-muted mt-1">Tanggal: <strong id="om_label_tgl"></strong></div>
                </div>
                <div class="form-group mb-1">
                    <label class="font-weight-bold small">Omset (Rp)</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                        <input type="text" id="om_nilai" class="form-control form-control-lg text-right uang"
                               placeholder="0" autocomplete="off">
                    </div>
                    <small class="text-muted">Isi 0 atau kosongkan untuk menghapus</small>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" id="btnSimpanOmset" class="btn btn-success btn-sm px-4">
                    <i class="fa fa-save mr-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL KUNJUNGAN ═══ -->
<div class="modal fade" id="modalKunjungan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white py-2">
                <h6 class="modal-title font-weight-bold"><i class="fa fa-users mr-2"></i>Input Kunjungan</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="kj_id_unit">
                <input type="hidden" id="kj_kode_unit">
                <input type="hidden" id="kj_nama_stand">
                <input type="hidden" id="kj_idswk">
                <input type="hidden" id="kj_tanggal">

                <div class="mb-3">
                    <div class="small text-muted">Unit Usaha</div>
                    <div class="font-weight-bold" id="kj_nama_unit" style="font-size:.95rem;"></div>
                    <div class="small text-muted mt-1">Tanggal: <strong id="kj_label_tgl"></strong></div>
                </div>
                <div class="form-group mb-1">
                    <label class="font-weight-bold small">Jumlah Kunjungan</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-users"></i></span></div>
                        <input type="number" id="kj_nilai" class="form-control form-control-lg text-right"
                               placeholder="0" min="0" autocomplete="off">
                        <div class="input-group-append"><span class="input-group-text">orang</span></div>
                    </div>
                    <small class="text-muted">Isi 0 atau kosongkan untuk menghapus</small>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" id="btnSimpanKunjungan" class="btn btn-info btn-sm px-4">
                    <i class="fa fa-save mr-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Sticky header & label ── */
#tblOmset thead th, #tblKunjungan thead th {
    background: #f4f6f9; font-size:.7rem; white-space:nowrap;
    padding: 5px 6px; text-align:center; vertical-align:middle;
}
.th-label { min-width:180px; text-align:left !important; background:#e9ecef !important; font-size:.72rem !important; }
.td-label {
    min-width:180px; background:#f8f9fa;
    position:sticky; left:0; z-index:1;
    font-size:.72rem; padding:4px 8px !important;
    border-right:2px solid #dee2e6 !important;
}
.cell-input {
    cursor:pointer; min-width:60px; padding:5px 4px !important;
    text-align:center; font-size:.72rem; transition:background .1s;
    white-space:nowrap;
}
.cell-input:hover { background:#e3f2fd !important; }
.cell-input.has-omset { background:#d4edda; color:#155724; font-weight:bold; }
.cell-input.has-kunjungan { background:#d1ecf1; color:#0c5460; font-weight:bold; }
.cell-input.col-minggu { color:#c0392b; }
.cell-input.col-minggu.has-omset { background:#f8d7da; }
.cell-input.col-minggu.has-kunjungan { background:#f8d7da; }
.cell-input.today-col { outline:2px solid #007bff; outline-offset:-2px; }
.foot-total { background:#f4f6f9; font-size:.72rem; padding:5px 6px !important; text-align:center; font-weight:bold; }
.foot-label { background:#e9ecef; font-size:.72rem; padding:5px 8px !important; font-weight:bold; text-align:left; }
</style>
