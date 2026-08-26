          <div class="col-6">
            <div class="card p-3">
              <table class="table table-sm border m-0">
                <tr>
                  <td width="150">NAMA SWK</td>
                  <td width="10">:</td>
                  <td><strong id="nama_swk">-</strong></td>
                </tr>
                <tr>
                  <td>ALAMAT SWK</td>
                  <td>:</td>
                  <td id="alamat_swk">-</td>
                </tr>
                <tr>
                  <td>JUMLAH STAN</td>
                  <td>:</td>
                  <td id="jumlah_stan">-</td>
                </tr>
                <tr>
                  <td>BULAN TAHUN</td>
                  <td>:</td>
                  <td id="bulan_tahun">-</td>
                </tr>
              </table>
            </div>
          </div>

          <div class="col-12">
			<div id="accordion">
			  <div class="card card-default">
				<div class="card-header d-flex justify-content-between align-items-center">
				  <h4 class="card-title w-100">
					<a class="d-block w-100 collapsed" data-toggle="collapse" href="#tingkat_keterisian_stan" aria-expanded="false"><i class="fa fa-caret-down toggle-caret mr-2"></i> Tingkat Keterisian Stan SWK <i class="status-indikator fa fa-ban text-warning"></i></a>
				  </h4>
				  <span class="badge badge-secondary">Bobot 15%</span>
				</div>
				<div id="tingkat_keterisian_stan" class="collapse" data-parent="#accordion" style="">
				  <div class="card-body">
							<form class="form-perform" method="post" enctype="multipart/form-data">
					  <input type="hidden" name="idswk" value="">
					  <input type="hidden" name="bulan" value="">
					  <input type="hidden" name="indikator" value="tingkat_keterisian_stan">
								<div class="form-group">
									<label>Target</label>
									<input class="form-control" name="target[tingkat_keterisian_stan]" type="text" readonly="true">
								</div>
								<div class="form-group">
									<label>Realisasi</label>
									<input type="number" name="realisasi[tingkat_keterisian_stan]" class="form-control" placeholder="Masukkan Realisasi" autocomplete="off">
								</div>
								<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
							</form>
				  </div>
				</div>
			  </div>

			  <div class="card card-default">
				<div class="card-header d-flex justify-content-between align-items-center">
				  <h4 class="card-title w-100">
					<a class="d-block w-100 collapsed" data-toggle="collapse" href="#kenaikan_omset" aria-expanded="false"><i class="fa fa-caret-down toggle-caret mr-2"></i>Kenaikan Omset 1% dari bulan kemarin adalah target bulan ini <i class="status-indikator fa fa-ban text-warning"></i></a>
				  </h4>
				  <span class="badge badge-secondary">Bobot 15%</span>
				</div>
				<div id="kenaikan_omset" class="collapse" data-parent="#accordion" style="">
				  <div class="card-body">

					<div class="card border border-danger">
						<div class="card-body">
							<div class="row">
								<span class="text-muted"><i class="fa fa-info-circle"></i> Pengisian Realisasi bisa di lakukan melalui menu <a href="capaian_harian"><b>ENTRI OMSET & KUNJUNGAN</b></a></span>
							</div>
						</div>
					</div>

					<form class="form-perform" method="post" enctype="multipart/form-data">
					  <input type="hidden" name="idswk" value="">
					  <input type="hidden" name="bulan" value="">
					  <input type="hidden" name="indikator" value="kenaikan_omset">
					  <div class="row">
						  <div class="col-sm-6">
							<div class="form-group">
								<label>Target</label>
								<input class="form-control uang" name="target[kenaikan_omset]" type="text" readonly="true">
							</div>
						  </div>
						  <div class="col-sm-6">
							<div class="form-group">
								<label>Realisasi</label>
								<input type="text" name="realisasi[kenaikan_omset]" class="form-control uang" placeholder="Masukkan Realisasi" autocomplete="off" readonly="true">
							</div>
						  </div>
					   </div>
						<button type="button" class="btn btn-primary disabled"><i class="fa fa-upload"></i> Simpan</button>
					</form>
				  </div>
				</div>
			  </div>

			  <div class="card card-default">
				<div class="card-header d-flex justify-content-between align-items-center">
				  <h4 class="card-title w-100">
					<a class="d-block w-100 collapsed" data-toggle="collapse" href="#kelengkapan_administrasi" aria-expanded="false"><i class="fa fa-caret-down toggle-caret mr-2"></i> Kelengkapan Administrasi Persyaratan sebagai Pedagang Binaan Dinkopumdag <i class="status-indikator fa fa-ban text-warning"></i></a>
				  </h4>
				  <span class="badge badge-secondary">Bobot 15%</span>
				</div>
				<div id="kelengkapan_administrasi" class="collapse" data-parent="#accordion" style="">
				  <div class="card-body">
					<form class="form-perform" method="post" enctype="multipart/form-data">
					  <input type="hidden" name="idswk" value="">
					  <input type="hidden" name="bulan" value="">
					  <input type="hidden" name="indikator" value="kelengkapan_administrasi">
						<div class="row">
							<div class="col-md-4 mb-3">
								<div class="border p-2">
									<p>a. NIB</p>
									<div class="form-group">
										<label>Target</label>
										<input class="form-control" name="target[kelengkapan_administrasi][nib]" type="number" readonly="true">
									</div>
									<div class="form-group">
										<label>Realisasi</label>
										<input type="number" name="realisasi[kelengkapan_administrasi][nib]" class="form-control" placeholder="Masukkan Realisasi">
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-3">
								<div class="border p-2">
									<p>b. SK Penempatan Pedagang</p>
									<div class="form-group">
										<label>Target</label>
										<input class="form-control" name="target[kelengkapan_administrasi][sk]" type="number" readonly="true">
									</div>
									<div class="form-group">
										<label>Realisasi</label>
										<input type="number" name="realisasi[kelengkapan_administrasi][sk]" class="form-control" placeholder="Masukkan Realisasi">
									</div>
								</div>
							</div>
							<div class="col-md-4 mb-3">
								<div class="border p-2">
									<p>c. Satu Data</p>
									<div class="form-group">
										<label>Target</label>
										<input class="form-control" name="target[kelengkapan_administrasi][satu_data]" type="number" readonly="true">
									</div>
									<div class="form-group">
										<label>Realisasi</label>
										<input type="number" name="realisasi[kelengkapan_administrasi][satu_data]" class="form-control" placeholder="Masukkan Realisasi">
									</div>
								</div>
							</div>
						</div>

						<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
					</form>
				  </div>
				</div>
			  </div>

			  <div class="card card-default">
				<div class="card-header d-flex justify-content-between align-items-center">
				  <h4 class="card-title w-100">
					<a class="d-block w-100 collapsed" data-toggle="collapse" href="#promosi" aria-expanded="false"><i class="fa fa-caret-down toggle-caret mr-2"></i> Membuat kegiatan promosi dalam bentuk Konten dan / atau Event di SWK <i class="status-indikator fa fa-ban text-warning"></i></a>
				  </h4>
				  <span class="badge badge-secondary">Bobot 15%</span>
				</div>
				<div id="promosi" class="collapse" data-parent="#accordion" style="">
				  <div class="card-body">
							<form class="form-perform" method="post" enctype="multipart/form-data">
					  <input type="hidden" name="idswk" value="">
					  <input type="hidden" name="bulan" value="">
					  <input type="hidden" name="indikator" value="promosi">
								<div class="form-group">
									<label>Realisasi</label>
									<input type="url" name="keterangan[promosi]" class="form-control" placeholder="Masukkan Realisasi" autocomplete="off">
								</div>
								<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
							</form>
				  </div>
				</div>
			  </div>

			  <div class="card card-default">
				<div class="card-header d-flex justify-content-between align-items-center">
				  <h4 class="card-title w-100">
					<a class="d-block w-100 collapsed" data-toggle="collapse" href="#tingkat_kebersihan" aria-expanded="false"><i class="fa fa-caret-down toggle-caret mr-2"></i> Tingkat Kebersihan Sentra Wisata Kuliner <i class="status-indikator fa fa-ban text-warning"></i></a>
				  </h4>
				  <span class="badge badge-secondary">Bobot 8%</span>
				</div>
				<div id="tingkat_kebersihan" class="collapse" data-parent="#accordion" style="">
				  <div class="card-body">
					<div class="row">
									<div class="col-md-4 mb-3">
										<div class="border p-2 border-primary">
						  <form class="form-perform" method="post" enctype="multipart/form-data">
							<input type="hidden" name="idswk" value="">
							<input type="hidden" name="bulan" value="">
							<input type="hidden" name="indikator" value="kebersihan_ruang_makan">
											<p>a. Kebersihan Ruang Makan</p>

							<div class="form-group">
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_ruang_makan_baik" name="kebersihan_ruang_makan" value="baik" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_ruang_makan_baik">Baik</label>
							  </div>
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_ruang_makan_cukup" name="kebersihan_ruang_makan" value="cukup" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_ruang_makan_cukup">Cukup</label>
							  </div>
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_ruang_makan_buruk" name="kebersihan_ruang_makan" value="buruk" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_ruang_makan_buruk">Buruk</label>
							  </div>
							</div>
							<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
						  </form>
										</div>
									</div>
									<div class="col-md-4 mb-3">
										<div class="border p-2 border-primary">
						  <form class="form-perform" method="post" enctype="multipart/form-data">
							<input type="hidden" name="idswk" value="">
							<input type="hidden" name="bulan" value="">
							<input type="hidden" name="indikator" value="kebersihan_tenan">
											<p>b. Kebersihan Tenan</p>
							<div class="form-group">
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_tenan_baik" name="kebersihan_tenan" value="baik" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_tenan_baik">Baik</label>
							  </div>
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_tenan_cukup" name="kebersihan_tenan" value="cukup" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_tenan_cukup">Cukup</label>
							  </div>
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_tenan_buruk" name="kebersihan_tenan" value="buruk" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_tenan_buruk">Buruk</label>
							  </div>
							</div>
							<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
						  </form>
										</div>
									</div>
									<div class="col-md-4 mb-3">
										<div class="border p-2 border-primary">
						  <form class="form-perform" method="post" enctype="multipart/form-data">
							<input type="hidden" name="idswk" value="">
							<input type="hidden" name="bulan" value="">
							<input type="hidden" name="indikator" value="kebersihan_toilet">
											<p>c. Kebersihan Toilet</p>
							<div class="form-group">
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_toilet_baik" name="kebersihan_toilet" value="baik" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_toilet_baik">Baik</label>
							  </div>
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_toilet_cukup" name="kebersihan_toilet" value="cukup" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_toilet_cukup">Cukup</label>
							  </div>
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_toilet_buruk" name="kebersihan_toilet" value="buruk" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_toilet_buruk">Buruk</label>
							  </div>
							</div>
							<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
						  </form>
										</div>
									</div>
									<div class="col-md-4 mb-3">
										<div class="border p-2 border-primary">
						  <form class="form-perform" method="post" enctype="multipart/form-data">
							<input type="hidden" name="idswk" value="">
							<input type="hidden" name="bulan" value="">
							<input type="hidden" name="indikator" value="kebersihan_area_parkir">
											<p>d. Kebersihan Area Parkir</p>
							<div class="form-group">
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_area_parkir_baik" name="kebersihan_area_parkir" value="baik" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_area_parkir_baik">Baik</label>
							  </div>
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_area_parkir_cukup" name="kebersihan_area_parkir" value="cukup" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_area_parkir_cukup">Cukup</label>
							  </div>
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_area_parkir_buruk" name="kebersihan_area_parkir" value="buruk" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_area_parkir_buruk">Buruk</label>
							  </div>
							</div>
							<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
						  </form>
										</div>
									</div>
									<div class="col-md-4 mb-3">
										<div class="border p-2 border-primary">
						  <form class="form-perform" method="post" enctype="multipart/form-data">
							<input type="hidden" name="idswk" value="">
							<input type="hidden" name="bulan" value="">
							<input type="hidden" name="indikator" value="kebersihan_produk_makanan">
											<p>e. Penataan Produk Makanan</p>

							<div class="form-group">
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_produk_makanan_baik" name="kebersihan_produk_makanan" value="baik" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_produk_makanan_baik">Baik</label>
							  </div>
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_produk_makanan_cukup" name="kebersihan_produk_makanan" value="cukup" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_produk_makanan_cukup">Cukup</label>
							  </div>
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="kebersihan_produk_makanan_buruk" name="kebersihan_produk_makanan" value="buruk" class="custom-control-input">
								<label class="custom-control-label" for="kebersihan_produk_makanan_buruk">Buruk</label>
							  </div>
							</div>
							<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
						  </form>


										</div>
									</div>
									<div class="col-md-4 mb-3">
									<div class="border p-2 border-primary">
								  <form class="form-perform" method="post" enctype="multipart/form-data">
						  <input type="hidden" name="idswk" value="">
						  <input type="hidden" name="bulan" value="">
						  <input type="hidden" name="indikator" value="sampah_terpilah">
											<p>f. Apakah Sampah Terpilah?</p>

						  <div class="form-group">
							<div class="custom-control custom-radio custom-control-inline">
							  <input type="radio" id="sampah_terpilah_belum" name="sampah_terpilah" value="belum" class="custom-control-input">
							  <label class="custom-control-label" for="sampah_terpilah_belum">BELUM</label>
							</div>
							<div class="custom-control custom-radio custom-control-inline">
							  <input type="radio" id="sampah_terpilah_sudah" name="sampah_terpilah" value="sudah" class="custom-control-input">
							  <label class="custom-control-label" for="sampah_terpilah_sudah">SUDAH</label>
							</div>
						  </div>
						  <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
						</form>
									</div>
								</div>

									<div class="col-md-4 mb-3">
									<div class="border p-2 border-primary">
								  <form class="form-perform" method="post" enctype="multipart/form-data">
						  <input type="hidden" name="idswk" value="">
						  <input type="hidden" name="bulan" value="">
						  <input type="hidden" name="indikator" value="lahan_parkir">
											<p>g. Ada Tidak Lahan Parkir?</p>

						  <div class="form-group">
							<div class="custom-control custom-radio custom-control-inline">
							  <input type="radio" id="lahan_parkir_tidak" name="lahan_parkir" value="tidak" class="custom-control-input">
							  <label class="custom-control-label" for="lahan_parkir_tidak">TIDAK</label>
							</div>
							<div class="custom-control custom-radio custom-control-inline">
							  <input type="radio" id="lahan_parkir_ada" name="lahan_parkir" value="ada" class="custom-control-input">
							  <label class="custom-control-label" for="lahan_parkir_ada">ADA</label>
							</div>
						  </div>
						  <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
						</form>
									</div>
								</div>

									<div class="col-md-4 mb-3">
										<div class="border p-2 border-primary">
								   <form class="form-perform" method="post" enctype="multipart/form-data">
							<input type="hidden" name="idswk" value="">
							<input type="hidden" name="bulan" value="">
							<input type="hidden" name="indikator" value="juru_parkir">
											<p>h. Ada Tidak Juru Parkir?</p>

							<div class="form-group">
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="juru_parkir_tidak" name="juru_parkir" value="tidak" class="custom-control-input">
								<label class="custom-control-label" for="juru_parkir_tidak">TIDAK</label>
							  </div>
							  <div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="juru_parkir_ada" name="juru_parkir" value="ada" class="custom-control-input">
								<label class="custom-control-label" for="juru_parkir_ada">ADA</label>
							  </div>
							</div>
							<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
						  </form>
										</div>
									</div>

								</div>
				  </div>
				</div>
			  </div>

			  <div class="card card-default">
				<div class="card-header d-flex justify-content-between align-items-center">
				  <h4 class="card-title w-100">
					<a class="d-block w-100 collapsed" data-toggle="collapse" href="#frekuensi_kunjungan" aria-expanded="false"><i class="fa fa-caret-down toggle-caret mr-2"></i> Jumlah Kunjungan di SWK <i class="status-indikator fa fa-ban text-warning"></i></a>
				  </h4>
				  <span class="badge badge-secondary">Bobot 8%</span>
				</div>
				<div id="frekuensi_kunjungan" class="collapse" data-parent="#accordion" style="">
				  <div class="card-body">

					<div class="card border border-danger">
						<div class="card-body">
							<div class="row">
								<span class="text-muted"><i class="fa fa-info-circle"></i> Pengisian Realisasi bisa di lakukan melalui menu <a href="capaian_harian"><b>ENTRI OMSET & KUNJUNGAN</b></a></span>
							</div>
						</div>
					</div>

							<form class="form-perform" method="post" enctype="multipart/form-data">
								<input type="hidden" name="idswk" value="">
					  <input type="hidden" name="bulan" value="">
					  <input type="hidden" name="indikator" value="frekuensi_kunjungan">
								<div class="form-group">
									<label>Realisasi</label>
									<input type="number" name="keterangan[frekuensi_kunjungan]" class="form-control" placeholder="Masukkan Realisasi" readonly="true">
								</div>
								<button type="button" class="btn btn-primary disabled"><i class="fa fa-upload"></i> Simpan</button>
							</form>
				  </div>
				</div>
			  </div>

			  <div class="card card-default">
				<div class="card-header d-flex justify-content-between align-items-center">
				  <h4 class="card-title w-100">
					<a class="d-block w-100 collapsed" data-toggle="collapse" href="#review_online" aria-expanded="false"><i class="fa fa-caret-down toggle-caret mr-2"></i> Peningkatan review online dari Konsumen/Customer/Pembeli (masukkan jumlah Pedagang yang mendapatkan review baik, cukup  dan Buruk pada Indikator Target  per Bulan dilengkapi screenshoot hasil review digital) <i class="status-indikator fa fa-ban text-warning"></i></a>
				  </h4>
				  <span class="badge badge-secondary">Bobot 8%</span>
				</div>
				<div id="review_online" class="collapse" data-parent="#accordion" style="">
				  <div class="card-body">
					<form class="form-perform" method="post" enctype="multipart/form-data">
						<input type="hidden" name="idswk" value="">
                        <input type="hidden" name="bulan" value="">
                        <input type="hidden" name="indikator" value="review_online">
                        <div class="form-group">
							<div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="baik" name="status" value="baik" class="custom-control-input">
								<label class="custom-control-label" for="baik">Baik</label>
							</div>

							<div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="cukup" name="status" value="cukup" class="custom-control-input">
								<label class="custom-control-label" for="cukup">Cukup</label>
							</div>

							<div class="custom-control custom-radio custom-control-inline">
								<input type="radio" id="buruk" name="status" value="buruk" class="custom-control-input">
								<label class="custom-control-label" for="buruk">Buruk</label>
							</div>
						</div>

                        <div class="form-group">
                            <label>Data Dukung</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="review_file" name="data_dukung" accept=".jpg,.jpeg" required>
                                <label class="custom-file-label" for="review_file">Pilih file...</label>
                            </div>
                            <small class="text-muted">Format: JPG atau JPEG. Maksimal 1 MB.</small>
                            <div class="mt-2 preview-file" style="display:none"></div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
					</form>
				  </div>
				</div>
			  </div>

			  <div class="card card-default">
				<div class="card-header d-flex justify-content-between align-items-center">
				  <h4 class="card-title w-100">
					<a class="d-block w-100 collapsed" data-toggle="collapse" href="#rapat_evaluasi" aria-expanded="false"><i class="fa fa-caret-down toggle-caret mr-2"></i> Rapat Evaluasi dengan Pedagang <i class="status-indikator fa fa-ban text-warning"></i></a>
				  </h4>
				  <span class="badge badge-secondary">Bobot 8%</span>
				</div>
				<div id="rapat_evaluasi" class="collapse" data-parent="#accordion" style="">
				  <div class="card-body">
							<form class="form-perform" method="post" enctype="multipart/form-data">
								<input type="hidden" name="idswk" value="">
					  <input type="hidden" name="bulan" value="">
					  <input type="hidden" name="indikator" value="rapat_evaluasi">
					  <div class="form-group">
						<textarea name="keterangan[rapat_evaluasi]" class="form-control" rows="3" placeholder="Masukkan keterangan"></textarea>
					  </div>
								<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
							</form>
				  </div>
				</div>
			  </div>

				<div class="card card-default">
				  <div class="card-header d-flex justify-content-between align-items-center">
					<h4 class="card-title w-100">
					  <a class="d-block w-100 collapsed" data-toggle="collapse" href="#pemberian_ide_praktis" aria-expanded="false"><i class="fa fa-caret-down toggle-caret mr-2"></i> Memberikan ide praktis kepada Pedagang, misal saran penataan produk makanan dan minuman, saran terkait kemasan produk, Bundling Menu, Harga Promo di jam tertentu <i class="status-indikator fa fa-ban text-warning"></i></a>
					</h4>
					<span class="badge badge-secondary">Bobot 8%</span>
				  </div>
				  <div id="pemberian_ide_praktis" class="collapse" data-parent="#accordion" style="">
					<div class="card-body">
					  <form class="form-perform" method="post" enctype="multipart/form-data">
						<input type="hidden" name="idswk" value="">
						<input type="hidden" name="bulan" value="">
						<input type="hidden" name="indikator" value="pemberian_ide_praktis">
						<div class="form-group">
						  <textarea name="keterangan[pemberian_ide_praktis]" class="form-control" rows="3" placeholder="Masukkan keterangan"></textarea>
						</div>
						<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Simpan</button>
					  </form>
					</div>
				  </div>
				</div>
			</div>
          </div>
