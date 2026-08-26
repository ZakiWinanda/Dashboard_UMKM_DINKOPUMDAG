<?php
defined('BASEPATH') OR exit('No direct script access allowed');
setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'Indonesian');
date_default_timezone_set('Asia/Jakarta');

$bulan_list = array();

$start = DateTime::createFromFormat('m-Y', $bulan_awal);
$end   = DateTime::createFromFormat('m-Y', $bulan_akhir);

while($start <= $end) {
    $key = $start->format('m-Y');

    $bulan_list[$key] = array(
        'bulan' => $start->format('m'),
        'tahun' => $start->format('Y'),
        'label' => strtoupper(
            strftime(
                '%B',
                $start->getTimestamp()
            )
        )
    );
    $start->modify('+1 month');
}


function nilai_laporan($row) {
    if($row->nilai_text !== NULL && $row->nilai_text != '') {
        return $row->nilai_text;
    }

    if($row->nilai_radio !== NULL && $row->nilai_radio != '') {
        return ucfirst($row->nilai_radio);
    }
    return $row->realisasi;
}

function persen($t=0, $r=0)
{
	if ($t > 0) {
		$persentase = ($r / $t) * 100;
		$persentase = min($persentase, 100);
	} else {
		$persentase = 0;
	}
	return round($persentase);
}

function persen_radio3($realisasi)
{
	$nilai = ['Baik'  => 100, 'Cukup' => 75, 'Buruk' => 50];
	return isset($nilai[$realisasi]) ? $nilai[$realisasi] : 0;
}

function persen_radio2($realisasi)
{
	$nilai = ['Ada'   => 100, 'Tidak' => 0];
	return isset($nilai[$realisasi]) ? $nilai[$realisasi] : 0;
}

function persen_sudah_belum($realisasi)
{
    $nilai = ['Sudah'   => 100, 'Belum' => 0];
    return isset($nilai[$realisasi]) ? $nilai[$realisasi] : 0;
}

function persen_isian($realisasi)
{
	return !empty($realisasi) ? 100 : 0;
}

/*
|--------------------------------------------------------------------------
| MAPPING NAMA INDIKATOR
|--------------------------------------------------------------------------
*/

$indikator = array(

    'tingkat_keterisian_stan' =>
        'TINGKAT KETERISIAN STAN',

    'kenaikan_omset' =>
        'KENAIKAN OMSET',

    'kelengkapan_administrasi.nib' =>
        'NIB',

    'kelengkapan_administrasi.sk' =>
        'SK PENEMPATAN',

    'kelengkapan_administrasi.satu_data' =>
        'SATU DATA',

    'promosi' =>
        'PROMOSI',

    'kebersihan_ruang_makan' =>
        'KEBERSIHAN RUANG MAKAN',

    'kebersihan_tenan' =>
        'KEBERSIHAN TENAN',

    'kebersihan_toilet' =>
        'KEBERSIHAN TOILET',

    'kebersihan_area_parkir' =>
        'KEBERSIHAN AREA PARKIR',

    'kebersihan_produk_makanan' =>
        'PENATAAN PRODUK MAKANAN',

    'sampah_terpilah' =>
        'APAKAH SAMPAH TERPILAH?',

    'lahan_parkir' =>
        'ADA TIDAK LAHAN PARKIR',

    'juru_parkir' =>
        'ADA TIDAK JURU PARKIR?',

    'frekuensi_kunjungan' =>
        'FREKUENSI KUNJUNGAN',

    'pemberian_ide_praktis' =>
        'PEMBERIAN IDE PRAKTIS',

    'review_online' =>
        'REVIEW ONLINE',

    'rapat_evaluasi' =>
        'RAPAT EVALUASI'

);


/*
|--------------------------------------------------------------------------
| MENGUBAH HASIL get_laporan()
|--------------------------------------------------------------------------
|
| menjadi:
|
| $laporan_data[indikator][bulan]
|
*/

$laporan_data = array();

// $nama_swk = '';
// $alamat   = '-';
// $jumlah_stan = 0;

foreach($laporan as $row) {
    $periode = sprintf(
        '%02d-%04d',
        $row->bulan,
        $row->tahun
    );

    $id = $row->kode;
    if($row->subindikator != '') {
        $id .= '.'.$row->subindikator;
    }

    if(!isset($laporan_data[$id])) {
        $laporan_data[$id] = array();
    }

	$nilai = nilai_laporan($row);
	$target = $row->target;
	$persen = '';

	$persen_target = array('tingkat_keterisian_stan', 'kelengkapan_administrasi', 'kenaikan_omset');
	$persen = in_array($row->kode, $persen_target) ? persen($target, $nilai).'%' : $persen;

	$persen_radio3 = array('kebersihan_ruang_makan', 'kebersihan_area_parkir', 'kebersihan_tenan', 'kebersihan_toilet', 'kebersihan_produk_makanan', 'review_online');
	$persen = in_array($row->kode, $persen_radio3) ? persen_radio3($nilai).'%' : $persen;

	$persen_radio2 = array('sampah_terpilah', 'lahan_parkir', 'juru_parkir');
    $persen = in_array($row->kode, $persen_radio2) ? persen_radio2($nilai).'%' : $persen;

    $persen_sudah_belum = array('sampah_terpilah');
	$persen = in_array($row->kode, $persen_sudah_belum) ? persen_sudah_belum($nilai).'%' : $persen;

	$persen_isian = array('promosi', 'pemberian_ide_praktis', 'rapat_evaluasi', 'frekuensi_kunjungan');
	$persen = in_array($row->kode, $persen_isian) ? persen_isian($nilai).'%' : $persen;

	if($id=='kenaikan_omset') {
		$target = number_format($row->target);
		$nilai = number_format($nilai);
		$persen = $persen;
	}

    $laporan_data[$id][$periode] = array(
        'target' => $target,
        'nilai' => $nilai,
        'persen' => $persen
    );
}



/*
|--------------------------------------------------------------------------
| CSS CETAK
|--------------------------------------------------------------------------
*/
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>LAPORAN MONITORING SWK</title>
		<style>
		body {
		    font-family:Arial;
		    font-size:11px;
		    color:#000;
		}

		table {
		    border-collapse:collapse;
		}

		table td,
		table th {
		    border:1px solid #000;
		    padding:4px;
		    vertical-align:middle;
		}

		thead th {
		    background:#eaeaea;
		}

		h1, h2, h3, h4, h5 {
			margin: 0;
		}

		.center {
		    text-align:center;
		}

		.right {
		    text-align:right;
		}

		.bold {
		    font-weight:bold;
		}

		.small {
		    font-size:10px;
		}

		.header {
		    margin-bottom:15px;
		}

		@media print{
		    @page{
		        size:A4 landscape;
		        margin:8mm;
		    }
		}
		</style>
	</head>
<body>
<div class="header">
	<?php

		if($bulan_awal == $bulan_akhir) {
		    $periode = strtoupper(strftime('%B %Y',strtotime('01-'.$bulan_awal)));
		}
		else {
		    $periode = strtoupper(strftime('%B %Y',strtotime('01-'.$bulan_awal)));
		    $periode .= ' s/d ';
		    $periode .= strtoupper(strftime('%B %Y', strtotime('01-'.$bulan_akhir)));
		}
	?>
	<div class="center" style="margin-bottom: 2em;">
		<h2>MONITORING DAN EVALUASI KINERJA PENDAMPING SENTRA WISATA KULINER (SWK) DI KOTA SURABAYA</h2>
		<h2>Bulan: <?= $periode; ?></h2>
	</div>

	<table border="0" style="width:30%;">
	<tr>
	    <td><strong>NAMA SWK</strong></td>
	    <td>:</td>
	    <td><?= strtoupper($swk->nama_swk); ?></td>
	</tr>

	<tr>
	    <td><strong>ALAMAT</strong></td>
	    <td>:</td>
	    <td><?= strtoupper($swk->alamat); ?></td>
	</tr>

	<tr>
	    <td><strong>JUMLAH STAN</strong></td>
	    <td>:</td>
	    <td><?=$swk->stan;?></td>
	</tr>

	<tr>
	    <td><strong>NAMA PENDAMPING</strong></td>
	    <td>:</td>
	    <td><?=$swk->nama_pendamping;?></td>
	</tr>

	<tr>
	    <td><strong>NIP PENDAMPING</strong></td>
	    <td>:</td>
	    <td><?=$swk->nip_pendamping;?></td>
	</tr>

	</table>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2" width="20" class="center">NO</th>
            <th rowspan="2" width="250">INDIKATOR KINERJA</th>
<?php foreach($bulan_list as $b){ ?>
            <th colspan="3" class="center">
                <?=$b['label'];?>
            </th>
<?php } ?>
        </tr>
        <tr>
<?php foreach($bulan_list as $b){ ?>
            <th class="center" width="40" style="font-size:90%;">TARGET</th>
            <th class="center" width="40" style="font-size:90%;">REALISASI</th>
            <th class="center" width="40" style="font-size:90%;">PERSENTASE</th>
<?php } ?>
        </tr>
    </thead>
    <tbody>
<?php

$no = 1;

/*
|--------------------------------------------------------------------------
| FUNCTION CETAK BARIS
|--------------------------------------------------------------------------
|
| Dipakai untuk seluruh indikator
|
*/
function cetakBaris($no,$judul,$key,$bulan_list,$laporan_data) {
?>
<tr>
    <td class="center"><?=$no;?></td>
    <td style="white-space: nowrap;"><?=$judul;?></td>
<?php
foreach($bulan_list as $periode=>$b)
{
    $target='-';
    $nilai='-';
    $persen='-';
    if(isset($laporan_data[$key][$periode])) {
        $target = $laporan_data[$key][$periode]['target'];
        $nilai  = $laporan_data[$key][$periode]['nilai'];
		$persen = $laporan_data[$key][$periode]['persen'];
    }
?>
    <td class="center"><?=$target;?></td>
    <td class="center"><?=$nilai;?></td>
    <td class="center"><?=$persen;?></td>
<?php
}
?>
</tr>
<?php
}
?>

<?php
cetakBaris(
    $no++,
    'Tingkat Keterisian Stan',
    'tingkat_keterisian_stan',
    $bulan_list,
    $laporan_data
);

cetakBaris(
    $no++,
    'Kenaikan Omset',
    'kenaikan_omset',
    $bulan_list,
    $laporan_data
);

?>

<tr>
    <td class="center"><?=$no++;?></td>
    <td colspan="<?=count($bulan_list)*3+1;?>"><strong>Kelengkapan Administrasi</strong></td>
</tr>

<?php

cetakBaris(
    '',
    '&nbsp;&nbsp;a. NIB',
    'kelengkapan_administrasi.nib',
    $bulan_list,
    $laporan_data
);

cetakBaris(
    '',
    '&nbsp;&nbsp;b. SK Penempatan',
    'kelengkapan_administrasi.sk',
    $bulan_list,
    $laporan_data
);

cetakBaris(
    '',
    '&nbsp;&nbsp;c. Satu Data',
    'kelengkapan_administrasi.satu_data',
    $bulan_list,
    $laporan_data
);

?>

<?php

/*
|--------------------------------------------------------------------------
| 4. PROMOSI
|--------------------------------------------------------------------------
*/

cetakBaris(
    $no++,
    'Membuat kegiatan promosi (Konten / Event)',
    'promosi',
    $bulan_list,
    $laporan_data
);


/*
|--------------------------------------------------------------------------
| 5. TINGKAT KEBERSIHAN
|--------------------------------------------------------------------------
*/
?>

<tr>

    <td class="center">
        <?=$no++;?>
    </td>

    <td colspan="<?=count($bulan_list)*3+1;?>">
        <strong>Tingkat Kebersihan SWK</strong>
    </td>

</tr>

<?php

cetakBaris(
    '',
    '&nbsp;&nbsp;a. Kebersihan Ruang Makan',
    'kebersihan_ruang_makan',
    $bulan_list,
    $laporan_data
);

cetakBaris(
    '',
    '&nbsp;&nbsp;b. Kebersihan Tenan',
    'kebersihan_tenan',
    $bulan_list,
    $laporan_data
);

cetakBaris(
    '',
    '&nbsp;&nbsp;c. Kebersihan Toilet',
    'kebersihan_toilet',
    $bulan_list,
    $laporan_data
);

cetakBaris(
    '',
    '&nbsp;&nbsp;d. Kebersihan Area Parkir',
    'kebersihan_area_parkir',
    $bulan_list,
    $laporan_data
);

cetakBaris(
    '',
    '&nbsp;&nbsp;e. Penataan Produk Makanan',
    'kebersihan_produk_makanan',
    $bulan_list,
    $laporan_data
);

cetakBaris(
    '',
    '&nbsp;&nbsp;f. Apakah Sampah Terpilah?',
    'sampah_terpilah',
    $bulan_list,
    $laporan_data
);

cetakBaris(
    '',
    '&nbsp;&nbsp;g. Ada Tidak Lahan Parkir?',
    'lahan_parkir',
    $bulan_list,
    $laporan_data
);

cetakBaris(
    '',
    '&nbsp;&nbsp;h. Ada Tidak Juru Parkir?',
    'juru_parkir',
    $bulan_list,
    $laporan_data
);

/*
|--------------------------------------------------------------------------
| 6. FREKUENSI KUNJUNGAN
|--------------------------------------------------------------------------
*/

cetakBaris(
    $no++,
    'Frekuensi Kunjungan',
    'frekuensi_kunjungan',
    $bulan_list,
    $laporan_data
);


/*
|--------------------------------------------------------------------------
| 7. PEMBERIAN IDE PRAKTIS
|--------------------------------------------------------------------------
|
| nilai_text
|
*/

?>

<tr>
    <td class="center"><?=$no++;?></td>
    <td>Memberikan Ide Praktis kepada Pedagang</td>
<?php

foreach($bulan_list as $periode=>$b) {
    $isi='';
    if(isset($laporan_data['pemberian_ide_praktis'][$periode])) {
        $isi=$laporan_data['pemberian_ide_praktis'][$periode]['nilai'];
    }

	if(!empty($isi)) {
?>
	<td></td>
    <td style="text-align:left">
        <?=nl2br(htmlspecialchars($isi));?>
    </td>
	<td style="text-align:center">
	<?php
	echo persen_isian($isi).'%'
	?>
	</td>
<?php
	}
	else {
?>
	<td></td>
	<td class="center">-</td>
	<td></td>
<?php
	}
}
?>

</tr>

<?php


/*
|--------------------------------------------------------------------------
| 8. REVIEW ONLINE
|--------------------------------------------------------------------------
|
| nilai_radio
|
*/

?>

<tr>
    <td class="center"><?=$no++;?></td>
    <td>Review Online</td>
<?php

foreach($bulan_list as $periode=>$b) {

    $isi='';
    if(isset($laporan_data['review_online'][$periode])) {
        $isi=$laporan_data['review_online'][$periode]['nilai'];
    }

	if(!empty($isi)) {
?>
    <td></td>
    <td class="center"><?=$isi;?></td>
    <td class="center">
	<?php
	echo persen_radio3($isi).'%';
	?>
	</td>
<?php
	}
	else {
?>
	<td></td>
    <td class="center">-</td>
	<td></td>
<?php
	}
}
?>

</tr>

<?php


/*
|--------------------------------------------------------------------------
| 9. RAPAT EVALUASI
|--------------------------------------------------------------------------
*/

cetakBaris(
    $no++,
    'Rapat Evaluasi',
    'rapat_evaluasi',
    $bulan_list,
    $laporan_data
);

?>
