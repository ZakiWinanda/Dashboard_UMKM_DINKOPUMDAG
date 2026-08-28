<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-7">
                    <h3 class="font-weight-bold m-0"><?=$title;?></h3>
                </div>
                <div class="col-sm-5 text-right">
                    <span id="badgeTanggal" class="badge badge-primary px-3 py-2 mr-1" style="font-size:.8rem;display:none;"></span>
                    <span id="badgeSwk" class="badge badge-secondary px-3 py-2" style="font-size:.8rem;display:none;"></span>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- Filter & Action Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
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
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">TANGGAL INPUT</label>
                            <input type="date" id="pilihTanggal" class="form-control" value="<?= date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-2 text-right">
                            <button type="button" id="btnTampilkan" class="btn btn-primary mr-1">
                                <i class="fa fa-search mr-1"></i> Tampilkan Data
                            </button>
                            <button type="button" id="btnPanduan" class="btn btn-outline-info mr-1">
                                <i class="fa fa-info-circle mr-1"></i> Panduan
                            </button>
                            <button type="button" id="btnDownloadTemplate" class="btn btn-outline-success mr-1">
                                <i class="fa fa-download mr-1"></i> Unduh Template
                            </button>
                            <button type="button" id="btnUploadExcel" class="btn btn-success">
                                <i class="fa fa-file-upload mr-1"></i> Upload Excel
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
                            <span class="info-box-text">Total Omset Bulan Ini</span>
                            <span class="info-box-number" id="totalOmsetSwk">Rp 0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="info-box bg-gradient-info shadow-sm mb-0">
                        <span class="info-box-icon"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Kunjungan Bulan Ini</span>
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
                <i class="fa fa-file-excel fa-4x text-muted mb-3 d-block"></i>
                <p class="text-muted">Pilih SWK &amp; Tanggal, lalu klik <strong>Tampilkan Data</strong> atau <strong>Upload Excel</strong>.</p>
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
                                <span class="font-weight-bold small text-muted"><i class="fa fa-info-circle mr-1"></i>Data omset terimpor per unit usaha</span>
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
                                <span class="font-weight-bold small text-muted"><i class="fa fa-info-circle mr-1"></i>Data kunjungan terimpor per unit usaha</span>
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

<!-- ═══ MODAL PANDUAN PENGGUNAAN (POP UP ALERT) ═══ -->
<div class="modal fade" id="modalPanduan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white py-2">
                <h6 class="modal-title font-weight-bold"><i class="fa fa-book-reader mr-2"></i>Panduan Pengisian &amp; Upload Excel Harian</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border-left py-2 mb-4" style="border-left: 4px solid #ffc107 !important;">
                    <div class="font-weight-bold text-dark mb-1">
                        <i class="fa fa-exclamation-triangle text-warning mr-1"></i> ATURAN PENTING PENGISIAN FILE EXCEL:
                    </div>
                    <ul class="mb-0 text-dark pl-3" style="font-size:.85rem;">
                        <li>HANYA isi atau ubah angka pada kolom <strong>omset</strong> dan <strong>kunjungan</strong>.</li>
                        <li><strong class="text-danger">DILARANG SANGAT</strong> mengedit, mengganti, atau menghapus isi kolom <code>id_unit_usaha</code> dan <code>tanggal</code> agar data teridentifikasi oleh sistem.</li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border shadow-xs">
                            <div class="card-body text-center p-3">
                                <div class="badge badge-primary px-3 py-2 rounded-circle mb-2" style="font-size:1.1rem;">1</div>
                                <h6 class="font-weight-bold text-primary">Unduh Template</h6>
                                <p class="small text-muted mb-0">Pilih <strong>SWK</strong> &amp; <strong>Tanggal Input</strong> pada filter, lalu klik tombol <strong class="text-success"><i class="fa fa-download"></i> Unduh Template</strong>.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border shadow-xs">
                            <div class="card-body text-center p-3">
                                <div class="badge badge-warning px-3 py-2 rounded-circle mb-2" style="font-size:1.1rem;color:#fff;">2</div>
                                <h6 class="font-weight-bold text-warning">Isi Data di Excel</h6>
                                <p class="small text-muted mb-0">Buka file Excel, lalu masukkan nominal omset dan jumlah pengunjung. Simpan file (<code>Ctrl+S</code>).</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border shadow-xs">
                            <div class="card-body text-center p-3">
                                <div class="badge badge-success px-3 py-2 rounded-circle mb-2" style="font-size:1.1rem;">3</div>
                                <h6 class="font-weight-bold text-success">Upload File</h6>
                                <p class="small text-muted mb-0">Klik tombol <strong class="text-success"><i class="fa fa-file-upload"></i> Upload Excel</strong>, pilih file Excel Anda, lalu klik <strong>Impor Data</strong>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 text-right">
                <button type="button" class="btn btn-info btn-sm px-4 font-weight-bold" data-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL UPLOAD EXCEL ═══ -->
<div class="modal fade" id="modalUpload" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title font-weight-bold"><i class="fa fa-file-upload mr-2"></i>Upload File Excel / CSV</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formUploadExcel" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="up_idswk_lokal" name="idswk_lokal">
                    <input type="hidden" id="up_tanggal" name="tanggal">

                    <div class="alert alert-warning py-2 small mb-3 border-left" style="border-left: 4px solid #ffc107 !important;">
                        <div class="font-weight-bold text-dark mb-1">
                            <i class="fa fa-exclamation-triangle mr-1 text-warning"></i> INSTRUKSI PENTING:
                        </div>
                        <ul class="pl-3 mb-0 text-muted" style="font-size:.78rem;">
                            <li>HANYA isi atau ubah data pada kolom <strong>omset</strong> dan <strong>kunjungan</strong>.</li>
                            <li><strong>DILARANG SANGAT</strong> mengubah, mengedit, atau menghapus isi kolom <code>id_unit_usaha</code> dan <code>tanggal</code> agar data dapat teridentifikasi oleh sistem.</li>
                        </ul>
                    </div>

                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">SWK Terpilih</label>
                        <input type="text" id="up_label_swk" class="form-control form-control-sm bg-light" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label class="small font-weight-bold">Tanggal Input</label>
                        <input type="text" id="up_label_tanggal" class="form-control form-control-sm bg-light" readonly>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Pilih File (.xls / .xlsx / .csv)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_excel" name="file_excel"
                                   accept=".xls, .xlsx, .csv" required>
                            <label class="custom-file-label text-truncate" for="file_excel" id="fileLabel">Pilih file…</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnProsesUpload" class="btn btn-success btn-sm px-4">
                        <i class="fa fa-upload mr-1"></i> Impor Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
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
    min-width:60px; padding:5px 4px !important;
    text-align:center; font-size:.72rem;
    white-space:nowrap;
}
.cell-input.has-omset { background:#d4edda; color:#155724; font-weight:bold; }
.cell-input.has-kunjungan { background:#d1ecf1; color:#0c5460; font-weight:bold; }
.cell-input.col-minggu { color:#c0392b; }
.cell-input.col-minggu.has-omset { background:#f8d7da; }
.cell-input.col-minggu.has-kunjungan { background:#f8d7da; }
.cell-input.today-col { outline:2px solid #007bff; outline-offset:-2px; }
.foot-total { background:#f4f6f9; font-size:.72rem; padding:5px 6px !important; text-align:center; font-weight:bold; }
.foot-label { background:#e9ecef; font-size:.72rem; padding:5px 8px !important; font-weight:bold; text-align:left; }
</style>
