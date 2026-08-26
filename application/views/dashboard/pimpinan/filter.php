<div class="card card-primary">
    <div class="card-header">
        <b>Periode Monitoring</b>
    </div>
    <div class="card-body">
        <div class="row">
		
            <div class="col-md-3">
                <label>Nama Pendamping</label>
                <select class="form-control select2" id="pendamping" style="width:100%">
                    <option value="">Semua Pendamping</option>
                    <?php foreach($pendamping as $p){ ?>
                    <option value="<?=$p->nik?>">
                        <?=$p->nama_lengkap?>
                    </option>
                    <?php } ?>
                </select>
            </div>
		
            <div class="col-md-3">
                <label>Tahun</label>
                <select class="form-control" id="tahun">
                    <?php
                    for($i=date('Y');$i>=2026;$i--){
                    ?>
                    <option value="<?=$i?>"><?=$i?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Bulan</label>
                <select class="form-control" id="bulan">
                    <?php
                    $bulan=array(
                        1=>'Januari',
                        2=>'Februari',
                        3=>'Maret',
                        4=>'April',
                        5=>'Mei',
                        6=>'Juni',
                        7=>'Juli',
                        8=>'Agustus',
                        9=>'September',
                        10=>'Oktober',
                        11=>'November',
                        12=>'Desember'
                    );

                    foreach($bulan as $k=>$v){
                    ?>
                    <option value="<?=$k?>" <?=$k==date('n')?'selected':''?>><?=$v?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-2">
                <label>&nbsp;</label>
                <button id="btnCari" class="btn btn-primary btn-block"><i class="fa fa-search"></i>Tampilkan</button>
            </div>
        </div>
    </div>
</div>