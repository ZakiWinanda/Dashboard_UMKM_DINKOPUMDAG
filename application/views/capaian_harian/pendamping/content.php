<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <!-- Dropdown SWK -->
            <div class="col-md-5 col-sm-12 mb-2">
                <select name="pilih_swk" id="pilih_swk" class="form-control">
                    <option value="">- Pilih SWK -</option>
                    <?php if (!empty($list_swk)): ?>
                        <?php foreach ($list_swk as $swk): ?>
                            <?php 
                                // Mengambil nilai ID SWK secara konsisten
                                $val_idswk = $swk['idswk'] ?? $swk['id_swk'] ?? $swk['id']; 
                                $is_single = (count($list_swk) == 1) ? 'selected' : '';
                            ?>
                            <option value="<?= htmlspecialchars($val_idswk); ?>" <?= $is_single; ?>>
                                <?= htmlspecialchars($swk['nama_swk'] ?? $swk['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Input Bulan Tahun -->
            <div class="col-md-4 col-sm-8 mb-2">
                <div class="input-group date" id="datepicker_bulan" data-target-input="nearest">
                    <input type="text" name="filter_bulan_tahun" id="filter_bulan_tahun" 
                           class="form-control datetimepicker-input" 
                           data-target="#datepicker_bulan"
                           value="<?= date('m-Y'); ?>" />
                    <div class="input-group-append" data-target="#datepicker_bulan" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>

            <!-- Tombol Submit (Wajib type="button") -->
            <div class="col-md-3 col-sm-4 mb-2">
                <button type="button" id="btnLoad" class="btn btn-primary btn-block">
                    <i class="fa fa-paper-plane mr-1"></i> Submit
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Card Tabel Omset Harian -->
<div class="card card-success card-outline mb-4">
    <div class="card-header bg-success d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0 text-white font-weight-bold">Omset Harian</h5>
        <div class="card-tools ml-auto text-white">
            Total : <strong id="totalOmset">Rp 0</strong>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-striped text-center mb-0">
            <thead>
                <tr id="headerOmset">
                    <th style="min-width: 150px;">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <tr id="rowOmset">
                    <td class="font-weight-bold text-left">Omset (Rp)</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Card Tabel Kunjungan Harian -->
<div class="card card-info card-outline">
    <div class="card-header bg-info d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0 text-white font-weight-bold">Kunjungan Harian</h5>
        <div class="card-tools ml-auto text-white">
            Total : <strong id="totalKunjungan">0</strong>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-striped text-center mb-0">
            <thead>
                <tr id="headerKunjungan">
                    <th style="min-width: 150px;">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <tr id="rowKunjungan">
                    <td class="font-weight-bold text-left">Jumlah Kunjungan</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>