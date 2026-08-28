<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body pb-0">
                <form id="frm_filter_bulan" method="post">
                    <div class="form-group row">
                        <div class="col-md-3">
							<?php
							if (!empty($is_koordinator_pendamping)) {
								echo("<input type='hidden' value='".$nip."' name='nip_koordinator' id='nip_koordinator'>");
							}
							?>
                            <select class="form-control select2" name="idpendamping" id="idpendamping" style="width: 100%;">
                                <option value="">- Pilih Pendamping -</option>
                                <?php if (!empty($pendamping)): ?>
                                    <?php foreach($pendamping as $row){ 
                                        $val_nik  = is_array($row) ? ($row['nik'] ?? '') : ($row->nik ?? '');
                                        $val_nama = is_array($row) ? ($row['nama_lengkap'] ?? $row['nama'] ?? '') : ($row->nama_lengkap ?? $row->nama ?? '');
                                    ?>
                                        <option value="<?= htmlspecialchars($val_nik); ?>">
                                            <?= htmlspecialchars($val_nama); ?>
                                        </option>
                                    <?php } ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select class="form-control select2" name="pilih_swk" id="pilih_swk" style="width: 100%;">
                                <option value="">- Pilih SWK -</option>
                                <?php if (!empty($swk)): ?>
                                    <?php foreach($swk as $row){ 
                                        $val_id   = is_array($row) ? ($row['idswk'] ?? $row['id'] ?? '') : ($row->idswk ?? $row->id ?? '');
                                        $val_nama = is_array($row) ? ($row['nama_swk'] ?? $row['name'] ?? '') : ($row->nama_swk ?? $row->name ?? '');
                                    ?>
                                        <option value="<?= htmlspecialchars($val_id); ?>">
                                            <?= htmlspecialchars($val_nama); ?>
                                        </option>
                                    <?php } ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" name="filter_bulan_tahun" id="filter_bulan_tahun" data-target="#filter_bulan_tahun" data-toggle="datetimepicker" placeholder="" class="form-control" value="<?=date('m-Y');?>" autocomplete="off" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id='btnLoad' class="col btn btn-primary btn-flat" title="filter"><i class="fa fa-paper-plane"></i> Submit bro</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
