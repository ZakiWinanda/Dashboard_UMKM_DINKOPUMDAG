<div class="card shadow-none bg-transparent mb-3">
    <div class="card-body p-0">
        <div class="form-row align-items-center">
            <!-- Dropdown SWK -->
            <div class="col-md-5 col-sm-12 mb-2">
                <select name="pilih_swk" id="pilih_swk" class="form-control select2" style="width: 100%;">
                   
                    <?php if (!empty($list_swk)): ?>
                        <?php foreach ($list_swk as $swk): ?>
                            <option value="<?= htmlspecialchars($swk['id']); ?>">
                                <?= htmlspecialchars($swk['nama_swk']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Input Bulan Tahun -->
            <div class="col-md-4 col-sm-8 mb-2">
                <div class="input-group">
                    <input type="text" name="filter_bulan_tahun" id="filter_bulan_tahun" class="form-control bg-white" 
                           value="<?= date('m-Y'); ?>" placeholder="MM-YYYY" readonly>
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                    </div>
                </div>
            </div>

            <!-- Tombol Submit / Load AJAX -->
            <div class="col-md-3 col-sm-4 mb-2">
                <button type="button" id="btnLoad" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane mr-1"></i> Submit
                </button>
            </div>
        </div>
    </div>
</div>