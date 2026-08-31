<div class="card card-primary collapsed-card">
	<div class="card-header">
		<b id="label_daftar_wilayah"><?= !empty($is_pendamping_kecamatan) ? 'Daftar Kecamatan' : 'Daftar SWK' ?></b>
	    <div class="card-tools">
	      <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
	      </button>
	    </div>
	</div>
	<div class="card-body p-0">
		<table class="table table-hover table-bordered table-sm small" id="tblSwk">
			<thead>
				<tr class="text-center">
					<th>No</th>
					<th id="th_nama_wilayah"><?= !empty($is_pendamping_kecamatan) ? 'Nama Kecamatan' : 'Nama SWK' ?></th>
					<th id="th_stan" <?= !empty($is_pendamping_kecamatan) ? 'style="display:none;"' : '' ?>>Stan</th>
					<th>Status</th>
					<th>Skor</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="5" align="center">Belum ada data</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>