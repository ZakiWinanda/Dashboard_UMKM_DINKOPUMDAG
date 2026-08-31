<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Input_bulanan extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_capaian_harian');
        $this->load->model('M_kecamatan');
        $this->load->model('M_unit_usaha');
    }

    public function index()
    {
        if (empty($this->is_pendamping_kecamatan) && $this->role == 'pendamping') {
            redirect('input_harian');
            return;
        }

        $raw_kec = $this->M_kecamatan->get_kecamatan_by_user($this->nip, $this->role);
        $list_swk = [];
        foreach ($raw_kec as $k) {
            $nama_k = is_object($k) ? $k->nama_kecamatan : ($k['nama_kecamatan'] ?? $k);
            $list_swk[] = [
                'id'         => $nama_k,
                'idswk'      => $nama_k,
                'nama_swk'   => $nama_k,
                'api_swk_id' => $nama_k
            ];
        }

        $data = [
            'title'                   => 'INPUT OMSET & KUNJUNGAN BULANAN (EXCEL)',
            'list_swk'                => $list_swk,
            'is_bulanan'              => true,
            'is_pendamping_kecamatan' => true,
        ];

        $this->load->view('header', $data);
        $this->load->view('input_bulanan/index', $data);
        $this->load->view('footer');
        $this->load->view('input_bulanan/script', $data);
    }

    /**
     * AJAX: Ambil daftar Industri Rumahan & data omset/kunjungan bulanan
     */
    public function load_unit_usaha()
    {
        if (!$this->input->is_ajax_request()) show_404();

        $idswk_lokal = trim($this->input->post('idswk_lokal'));
        $idswk_api   = trim($this->input->post('idswk_api'));
        $tanggal     = trim($this->input->post('tanggal')); // YYYY-MM

        if (empty($tanggal)) {
            $tanggal = date('Y-m');
        }

        $nama_kec = !empty($idswk_lokal) ? $idswk_lokal : $idswk_api;
        if (empty($nama_kec)) {
            echo json_encode(['status' => false, 'message' => 'Kecamatan tidak valid.']);
            return;
        }

        $parts = explode('-', $tanggal);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));

        // Ambil unit usaha IR dari API jika ada, atau buat list IR per kecamatan
        $units = $this->M_unit_usaha->get_from_api($idswk_api);
        if (empty($units)) {
            $units = [
                [
                    'id'           => 'IR-' . strtoupper(substr(md5($nama_kec), 0, 8)),
                    'namaUsaha'    => 'Industri Rumahan ' . $nama_kec,
                    'kodeUsahaSwk' => 'IR-' . strtoupper(substr($nama_kec, 0, 3)),
                    'namaStand'    => 'Wilayah ' . $nama_kec,
                    'namaPedagang' => 'Pelaku Usaha ' . $nama_kec,
                    'nikPedagang'  => '-'
                ]
            ];
        }

        // Ambil omset & kunjungan per bulan dalam tahun berjalan
        $omset_rows = $this->db
            ->select('id_unit_usaha, MONTH(tanggal) AS bulan, SUM(omset) AS omset')
            ->where('idswk', $nama_kec)
            ->where('YEAR(tanggal)', $tahun)
            ->group_by(['id_unit_usaha', 'MONTH(tanggal)'])
            ->get('t_omset_unit_usaha')
            ->result();

        $kunjungan_rows = $this->db
            ->select('id_unit_usaha, MONTH(tanggal) AS bulan, SUM(jumlah) AS jumlah')
            ->where('idswk', $nama_kec)
            ->where('YEAR(tanggal)', $tahun)
            ->group_by(['id_unit_usaha', 'MONTH(tanggal)'])
            ->get('t_kunjungan_unit_usaha')
            ->result();

        $omset_map     = [];
        $kunjungan_map = [];
        $total_omset_bulan     = 0;
        $total_kunjungan_bulan = 0;

        foreach ($omset_rows as $r) {
            $omset_map[$r->id_unit_usaha][(int)$r->bulan] = (float)$r->omset;
            if ((int)$r->bulan === $bulan) {
                $total_omset_bulan += (float)$r->omset;
            }
        }

        foreach ($kunjungan_rows as $r) {
            $kunjungan_map[$r->id_unit_usaha][(int)$r->bulan] = (int)$r->jumlah;
            if ((int)$r->bulan === $bulan) {
                $total_kunjungan_bulan += (int)$r->jumlah;
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'          => true,
                'tanggal'         => $tanggal,
                'tahun'           => $tahun,
                'bulan'           => $bulan,
                'units'           => $units,
                'omset'           => $omset_map,
                'kunjungan'       => $kunjungan_map,
                'total_omset'     => $total_omset_bulan,
                'total_kunjungan' => $total_kunjungan_bulan,
            ]));
    }

    /**
     * Unduh Template Excel Bulanan (.xlsx) untuk Kecamatan
     */
    public function download_template()
    {
        $idswk_lokal = trim($this->input->get('idswk_lokal'));
        $idswk_api   = trim($this->input->get('idswk_api'));
        $tanggal     = trim($this->input->get('tanggal')); // YYYY-MM

        if (empty($tanggal)) {
            $tanggal = date('Y-m');
        }

        $nama_kec = !empty($idswk_lokal) ? $idswk_lokal : ($idswk_api ?: 'Kecamatan');

        $parts = explode('-', $tanggal);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));
        $nama_bulan_arr = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $nama_bulan_str = $nama_bulan_arr[$bulan - 1] ?? 'Bulan ' . $bulan;

        $units = $this->M_unit_usaha->get_from_api($idswk_api);
        if (empty($units)) {
            $units = [
                [
                    'id'           => 'IR-' . strtoupper(substr(md5($nama_kec), 0, 8)),
                    'namaUsaha'    => 'Industri Rumahan ' . $nama_kec,
                    'kodeUsahaSwk' => 'IR-' . strtoupper(substr($nama_kec, 0, 3)),
                    'namaStand'    => 'Wilayah ' . $nama_kec,
                    'namaPedagang' => 'Pelaku Usaha ' . $nama_kec,
                    'nikPedagang'  => '-'
                ]
            ];
        }

        // Ambil data omset & kunjungan existing
        $omset_existing = [];
        $kunjungan_existing = [];
        $tgl_cek = sprintf('%04d-%02d-01', $tahun, $bulan);

        $q_om = $this->db->where('idswk', $nama_kec)->where('tanggal', $tgl_cek)->get('t_omset_unit_usaha')->result();
        foreach ($q_om as $r) {
            $omset_existing[$r->id_unit_usaha] = (float)$r->omset;
        }

        $q_kj = $this->db->where('idswk', $nama_kec)->where('tanggal', $tgl_cek)->get('t_kunjungan_unit_usaha')->result();
        foreach ($q_kj as $r) {
            $kunjungan_existing[$r->id_unit_usaha] = (int)$r->jumlah;
        }

        $tmpXlsx = tempnam(sys_get_temp_dir(), 'xlsx_') . '.xlsx';
        if (file_exists($tmpXlsx)) @unlink($tmpXlsx);

        $zip = new ZipArchive();
        if ($zip->open($tmpXlsx, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');

            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');

            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');

            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Template Bulanan" sheetId="1" r:id="rId1"/></sheets></workbook>');

            $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF2E7D32"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/></border><border><left style="thin"><color rgb="FFD9D9D9"/></left><right style="thin"><color rgb="FFD9D9D9"/></right><top style="thin"><color rgb="FFD9D9D9"/></top><bottom style="thin"><color rgb="FFD9D9D9"/></bottom></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf><xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1"/><xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1"><alignment horizontal="right"/></xf></cellXfs></styleSheet>');

            $sheetXml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
            $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
            $sheetXml .= '<cols>';
            $sheetXml .= '<col min="1" max="1" width="36" customWidth="1"/>';
            $sheetXml .= '<col min="2" max="2" width="16" customWidth="1"/>';
            $sheetXml .= '<col min="3" max="3" width="30" customWidth="1"/>';
            $sheetXml .= '<col min="4" max="4" width="28" customWidth="1"/>';
            $sheetXml .= '<col min="5" max="5" width="22" customWidth="1"/>';
            $sheetXml .= '<col min="6" max="6" width="18" customWidth="1"/>';
            $sheetXml .= '<col min="7" max="7" width="20" customWidth="1"/>';
            $sheetXml .= '<col min="8" max="8" width="24" customWidth="1"/>';
            $sheetXml .= '<col min="9" max="9" width="24" customWidth="1"/>';
            $sheetXml .= '</cols>';
            $sheetXml .= '<sheetData>';

            // Row 1: Header Titles
            $sheetXml .= '<row r="1" ht="28" customHeight="1">';
            $sheetXml .= '<c r="A1" s="1" t="inlineStr"><is><t>id_unit_usaha</t></is></c>';
            $sheetXml .= '<c r="B1" s="1" t="inlineStr"><is><t>kode_usaha</t></is></c>';
            $sheetXml .= '<c r="C1" s="1" t="inlineStr"><is><t>nama_usaha</t></is></c>';
            $sheetXml .= '<c r="D1" s="1" t="inlineStr"><is><t>nama_pelaku_usaha</t></is></c>';
            $sheetXml .= '<c r="E1" s="1" t="inlineStr"><is><t>nik</t></is></c>';
            $sheetXml .= '<c r="F1" s="1" t="inlineStr"><is><t>periode_bulan</t></is></c>';
            $sheetXml .= '<c r="G1" s="1" t="inlineStr"><is><t>omset_bulanan</t></is></c>';
            $sheetXml .= '<c r="H1" s="1" t="inlineStr"><is><t>jumlah_kunjungan</t></is></c>';
            $sheetXml .= '<c r="I1" s="1" t="inlineStr"><is><t>keterangan</t></is></c>';
            $sheetXml .= '</row>';

            // Data Rows
            $rowNum = 2;
            foreach ($units as $u) {
                $uid     = $u['id'] ?? '';
                $kode    = $u['kodeUsahaSwk'] ?? $u['kodeUsaha'] ?? '';
                $namaUs  = $u['namaUsaha'] ?? $u['namaStand'] ?? '';
                $namaPel = $u['namaPedagang'] ?? '';
                $nikPel  = $u['nikPedagang'] ?? '';
                $omsetVal = $omset_existing[$uid] ?? 0;
                $kjVal    = $kunjungan_existing[$uid] ?? 0;
                $periode_val = sprintf('%04d-%02d', $tahun, $bulan);

                $sheetXml .= '<row r="' . $rowNum . '" ht="22" customHeight="1">';
                $sheetXml .= '<c r="A' . $rowNum . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($uid, ENT_XML1) . '</t></is></c>';
                $sheetXml .= '<c r="B' . $rowNum . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($kode, ENT_XML1) . '</t></is></c>';
                $sheetXml .= '<c r="C' . $rowNum . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($namaUs, ENT_XML1) . '</t></is></c>';
                $sheetXml .= '<c r="D' . $rowNum . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($namaPel, ENT_XML1) . '</t></is></c>';
                $sheetXml .= '<c r="E' . $rowNum . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($nikPel, ENT_XML1) . '</t></is></c>';
                $sheetXml .= '<c r="F' . $rowNum . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($periode_val, ENT_XML1) . '</t></is></c>';
                $sheetXml .= '<c r="G' . $rowNum . '" s="3"><v>' . (float)$omsetVal . '</v></c>';
                $sheetXml .= '<c r="H' . $rowNum . '" s="3"><v>' . (int)$kjVal . '</v></c>';
                $sheetXml .= '<c r="I' . $rowNum . '" s="2" t="inlineStr"><is><t>Kecamatan ' . htmlspecialchars($nama_kec, ENT_XML1) . ' - ' . htmlspecialchars($nama_bulan_str, ENT_XML1) . ' ' . $tahun . '</t></is></c>';
                $sheetXml .= '</row>';
                $rowNum++;
            }

            $sheetXml .= '</sheetData></worksheet>';
            $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
            $zip->close();

            $safe_kec = preg_replace('/[^A-Za-z0-9_-]/', '_', $nama_kec);
            $filename = 'Template_Bulanan_' . $safe_kec . '_' . sprintf('%04d_%02d', $tahun, $bulan) . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($tmpXlsx));
            header('Cache-Control: max-age=0');
            readfile($tmpXlsx);
            @unlink($tmpXlsx);
            exit;
        }

        show_error('Gagal membuat file template Excel.', 500);
    }

    /**
     * Upload & Parsing File Excel Bulanan (.xlsx, .xls, .csv) untuk Kecamatan
     */
    public function upload_excel()
    {
        if (!$this->input->is_ajax_request()) show_404();

        if (empty($_FILES['file_excel']['name'])) {
            echo json_encode(['status' => false, 'message' => 'Pilih file Excel yang akan diunggah.']);
            return;
        }

        $idswk_lokal = trim($this->input->post('idswk_lokal'));
        $tanggal     = trim($this->input->post('tanggal')); // YYYY-MM

        if (empty($tanggal)) {
            $tanggal = date('Y-m');
        }

        $parts = explode('-', $tanggal);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));
        $tgl_db = sprintf('%04d-%02d-01', $tahun, $bulan);

        $ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
        $tmp = $_FILES['file_excel']['tmp_name'];

        $rows = [];
        if ($ext === 'csv') {
            $fh = fopen($tmp, 'r');
            if ($fh) {
                while (($row = fgetcsv($fh, 2000, ',')) !== FALSE) {
                    if (count($row) === 1 && strpos($row[0], ';') !== false) {
                        $row = explode(';', $row[0]);
                    }
                    $rows[] = $row;
                }
                fclose($fh);
            }
        } elseif ($ext === 'xlsx') {
            $zip = new ZipArchive();
            if ($zip->open($tmp) === TRUE) {
                $sheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
                $stringsContent = $zip->getFromName('xl/sharedStrings.xml');
                $zip->close();

                $sharedStrings = [];
                if ($stringsContent) {
                    $sXml = simplexml_load_string($stringsContent);
                    if ($sXml) {
                        foreach ($sXml->si as $si) {
                            $sharedStrings[] = (string)($si->t ?? $si->r->t ?? '');
                        }
                    }
                }

                if ($sheetContent) {
                    $xXml = simplexml_load_string($sheetContent);
                    if ($xXml && isset($xXml->sheetData->row)) {
                        foreach ($xXml->sheetData->row as $r) {
                            $rowArr = [];
                            foreach ($r->c as $c) {
                                $cellType = (string)$c['t'];
                                $val = (string)$c->v;
                                if ($cellType === 's' && isset($sharedStrings[(int)$val])) {
                                    $val = $sharedStrings[(int)$val];
                                } elseif ($cellType === 'inlineStr' && isset($c->is->t)) {
                                    $val = (string)$c->is->t;
                                }
                                $rowArr[] = $val;
                            }
                            $rows[] = $rowArr;
                        }
                    }
                }
            }
        }

        if (empty($rows)) {
            echo json_encode(['status' => false, 'message' => 'File tidak dapat dibaca atau format kosong.']);
            return;
        }

        // Cari header index
        $header = array_shift($rows);
        $colIdx = [
            'id_unit_usaha'    => 0,
            'omset'            => 6,
            'kunjungan'        => 7,
        ];

        foreach ($header as $i => $h) {
            $hClean = strtolower(trim(str_replace([' ', '_'], '', (string)$h)));
            if (strpos($hClean, 'idunit') !== false)    $colIdx['id_unit_usaha'] = $i;
            if (strpos($hClean, 'omset') !== false)     $colIdx['omset'] = $i;
            if (strpos($hClean, 'kunjungan') !== false || strpos($hClean, 'transaksi') !== false) $colIdx['kunjungan'] = $i;
        }

        $saved_count = 0;
        $total_omset_saved = 0;
        $total_kj_saved    = 0;

        foreach ($rows as $r) {
            $uid = trim((string)($r[$colIdx['id_unit_usaha']] ?? ''));
            if (empty($uid)) continue;

            $omset_val = (float)str_replace([',', 'Rp', 'rp', ' ', '.'], ['', '', '', '', ''], (string)($r[$colIdx['omset']] ?? 0));
            $kj_val    = (int)str_replace([',', ' ', '.'], ['', '', ''], (string)($r[$colIdx['kunjungan']] ?? 0));

            // Simpan Omset
            $cekOm = $this->db
                ->where('idswk', $idswk_lokal)
                ->where('id_unit_usaha', $uid)
                ->where('tanggal', $tgl_db)
                ->get('t_omset_unit_usaha')
                ->row();

            if ($cekOm) {
                $this->db->where('id', $cekOm->id)->update('t_omset_unit_usaha', [
                    'omset'      => $omset_val,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->insert('t_omset_unit_usaha', [
                    'idswk'         => $idswk_lokal,
                    'id_unit_usaha' => $uid,
                    'tanggal'       => $tgl_db,
                    'omset'         => $omset_val,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s')
                ]);
            }

            // Simpan Kunjungan
            $cekKj = $this->db
                ->where('idswk', $idswk_lokal)
                ->where('id_unit_usaha', $uid)
                ->where('tanggal', $tgl_db)
                ->get('t_kunjungan_unit_usaha')
                ->row();

            if ($cekKj) {
                $this->db->where('id', $cekKj->id)->update('t_kunjungan_unit_usaha', [
                    'jumlah'     => $kj_val,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->insert('t_kunjungan_unit_usaha', [
                    'idswk'         => $idswk_lokal,
                    'id_unit_usaha' => $uid,
                    'tanggal'       => $tgl_db,
                    'jumlah'        => $kj_val,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s')
                ]);
            }

            $saved_count++;
            $total_omset_saved += $omset_val;
            $total_kj_saved    += $kj_val;
        }

        echo json_encode([
            'status'  => true,
            'message' => 'Berhasil mengimpor <b>' . $saved_count . ' unit usaha/IR</b>.<br>Total Omset Bulanan: <b>Rp ' . number_format($total_omset_saved, 0, ',', '.') . '</b><br>Total Kunjungan/Transaksi: <b>' . number_format($total_kj_saved, 0, ',', '.') . '</b>',
        ]);
    }
}
