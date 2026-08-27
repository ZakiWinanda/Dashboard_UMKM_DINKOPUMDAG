<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Input_harian extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_capaian_harian');
        $this->load->model('M_swk');
        $this->load->model('M_unit_usaha');
    }

    public function index()
    {
        if ($this->is_pimpinan || $this->is_admin) {
            $list_swk = $this->M_swk->get_all_as_array();
        } else {
            $list_swk = $this->M_swk->get_by_pendamping_array($this->nip);
        }

        $data = [
            'title'    => 'INPUT OMSET & KUNJUNGAN HARIAN (EXCEL)',
            'list_swk' => $list_swk,
        ];

        $this->load->view('header', $data);
        $this->load->view('input_harian/index', $data);
        $this->load->view('footer');
        $this->load->view('input_harian/script', $data);
    }

    /**
     * AJAX: Ambil daftar unit usaha & data omset/kunjungan untuk 1 TANGGAL
     */
    public function load_unit_usaha()
    {
        if (!$this->input->is_ajax_request()) show_404();

        $idswk_api   = trim($this->input->post('idswk_api'));
        $idswk_lokal = trim($this->input->post('idswk_lokal'));
        $tanggal     = trim($this->input->post('tanggal')); // YYYY-MM-DD

        // Resolve ID SWK Lokal & API dari m_swk
        $swkRow = $this->db->group_start()
            ->where('idswk', $idswk_lokal)
            ->or_where('api_swk_id', $idswk_lokal)
            ->or_where('idswk', $idswk_api)
            ->or_where('api_swk_id', $idswk_api)
            ->group_end()
            ->get('m_swk')->row();

        if ($swkRow) {
            $idswk_lokal = $swkRow->idswk;
            if (!empty($swkRow->api_swk_id)) $idswk_api = $swkRow->api_swk_id;
        }

        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        if (empty($idswk_api)) {
            echo json_encode(['status' => false, 'message' => 'SWK tidak valid.']);
            return;
        }

        $parts = explode('-', $tanggal);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));

        $units = $this->M_unit_usaha->get_from_api($idswk_api);

        if (empty($units)) {
            echo json_encode(['status' => false, 'message' => 'Tidak ada unit usaha aktif untuk SWK ini.']);
            return;
        }

        $omset_map     = $this->M_unit_usaha->getOmsetBulan($idswk_lokal, $bulan, $tahun);
        $kunjungan_map = $this->M_unit_usaha->getKunjunganBulan($idswk_lokal, $bulan, $tahun);

        $total_omset     = $this->M_unit_usaha->totalOmsetSwkBulan($idswk_lokal, $bulan, $tahun);
        $total_kunjungan = $this->M_unit_usaha->totalKunjunganSwkBulan($idswk_lokal, $bulan, $tahun);

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
                'total_omset'     => $total_omset,
                'total_kunjungan' => $total_kunjungan,
            ]));
    }

    /**
     * Unduh Template Native Excel Modern (.xlsx) per Hari dengan Nama SWK & Nama User
     */
    public function download_template()
    {
        $idswk_lokal = trim($this->input->get('idswk_lokal'));
        $idswk_api   = trim($this->input->get('idswk_api'));
        $tanggal     = trim($this->input->get('tanggal'));

        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        $nama_swk = 'SWK';
        $swkRow = $this->db->group_start()
            ->where('idswk', $idswk_lokal)
            ->or_where('api_swk_id', $idswk_lokal)
            ->or_where('idswk', $idswk_api)
            ->or_where('api_swk_id', $idswk_api)
            ->group_end()
            ->get('m_swk')->row();

        if ($swkRow) {
            $idswk_lokal = $swkRow->idswk;
            if (!empty($swkRow->api_swk_id)) $idswk_api = $swkRow->api_swk_id;
            if (!empty($swkRow->nama_swk))   $nama_swk   = $swkRow->nama_swk;
        }

        $parts = explode('-', $tanggal);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));

        $units         = $this->M_unit_usaha->get_from_api($idswk_api);
        $omset_map     = $this->M_unit_usaha->getOmsetBulan($idswk_lokal, $bulan, $tahun);
        $kunjungan_map = $this->M_unit_usaha->getKunjunganBulan($idswk_lokal, $bulan, $tahun);

        $tmpXlsx = tempnam(sys_get_temp_dir(), 'xlsx_') . '.xlsx';
        if (file_exists($tmpXlsx)) @unlink($tmpXlsx);

        $zip = new ZipArchive();
        if ($zip->open($tmpXlsx, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // [Content_Types].xml
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');

            // _rels/.rels
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');

            // xl/_rels/workbook.xml.rels
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');

            // xl/workbook.xml
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Template Input" sheetId="1" r:id="rId1"/></sheets></workbook>');

            // xl/styles.xml
            $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF1F4E78"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="2"><border><left/><right/><top/><bottom/></border><border><left style="thin"><color rgb="FFD9D9D9"/></left><right style="thin"><color rgb="FFD9D9D9"/></right><top style="thin"><color rgb="FFD9D9D9"/></top><bottom style="thin"><color rgb="FFD9D9D9"/></bottom></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf><xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1"/><xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1"><alignment horizontal="right"/></xf></cellXfs></styleSheet>');

            // Build xl/worksheets/sheet1.xml
            $sheetXml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
            $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
            $sheetXml .= '<cols>';
            $sheetXml .= '<col min="1" max="1" width="38" customWidth="1"/>';
            $sheetXml .= '<col min="2" max="2" width="14" customWidth="1"/>';
            $sheetXml .= '<col min="3" max="3" width="18" customWidth="1"/>';
            $sheetXml .= '<col min="4" max="4" width="28" customWidth="1"/>';
            $sheetXml .= '<col min="5" max="5" width="26" customWidth="1"/>';
            $sheetXml .= '<col min="6" max="6" width="16" customWidth="1"/>';
            $sheetXml .= '<col min="7" max="7" width="18" customWidth="1"/>';
            $sheetXml .= '<col min="8" max="8" width="16" customWidth="1"/>';
            $sheetXml .= '</cols>';
            $sheetXml .= '<sheetData>';

            // Header Row (Row 1)
            $sheetXml .= '<row r="1" ht="26" customHeight="1">';
            $sheetXml .= '<c r="A1" t="inlineStr" s="1"><is><t>id_unit_usaha</t></is></c>';
            $sheetXml .= '<c r="B1" t="inlineStr" s="1"><is><t>kode_stand</t></is></c>';
            $sheetXml .= '<c r="C1" t="inlineStr" s="1"><is><t>kode_usaha</t></is></c>';
            $sheetXml .= '<c r="D1" t="inlineStr" s="1"><is><t>nama_usaha</t></is></c>';
            $sheetXml .= '<c r="E1" t="inlineStr" s="1"><is><t>nama_pedagang</t></is></c>';
            $sheetXml .= '<c r="F1" t="inlineStr" s="1"><is><t>tanggal</t></is></c>';
            $sheetXml .= '<c r="G1" t="inlineStr" s="1"><is><t>omset</t></is></c>';
            $sheetXml .= '<c r="H1" t="inlineStr" s="1"><is><t>kunjungan</t></is></c>';
            $sheetXml .= '</row>';

            // Data Rows (Row 2++)
            $rowNum = 2;
            foreach ($units as $u) {
                $uid = $u['id'];
                $om  = (int)($omset_map[$uid][$tanggal] ?? 0);
                $kj  = (int)($kunjungan_map[$uid][$tanggal] ?? 0);

                $sheetXml .= '<row r="' . $rowNum . '">';
                $sheetXml .= '<c r="A' . $rowNum . '" t="inlineStr" s="2"><is><t>' . htmlspecialchars($uid, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
                $sheetXml .= '<c r="B' . $rowNum . '" t="inlineStr" s="2"><is><t>' . htmlspecialchars($u['namaStand'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
                $sheetXml .= '<c r="C' . $rowNum . '" t="inlineStr" s="2"><is><t>' . htmlspecialchars($u['kodeUsahaSwk'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
                $sheetXml .= '<c r="D' . $rowNum . '" t="inlineStr" s="2"><is><t>' . htmlspecialchars($u['namaUsaha'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
                $sheetXml .= '<c r="E' . $rowNum . '" t="inlineStr" s="2"><is><t>' . htmlspecialchars($u['namaPedagang'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
                $sheetXml .= '<c r="F' . $rowNum . '" t="inlineStr" s="2"><is><t>' . htmlspecialchars($tanggal, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
                $sheetXml .= '<c r="G' . $rowNum . '" s="3"><v>' . $om . '</v></c>';
                $sheetXml .= '<c r="H' . $rowNum . '" s="3"><v>' . $kj . '</v></c>';
                $sheetXml .= '</row>';

                $rowNum++;
            }

            $sheetXml .= '</sheetData>';
            $sheetXml .= '</worksheet>';

            $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
            $zip->close();
        }

        // Susun Nama File: Template_Input_Harian_[SWK]_[User]_[Tanggal].xlsx
        $swk_clean  = preg_replace('/[^A-Za-z0-9]/', '_', $nama_swk);
        $user_clean = preg_replace('/[^A-Za-z0-9]/', '_', !empty($this->nama) ? $this->nama : ($this->nip ?? 'User'));

        $swk_clean  = preg_replace('/_+/', '_', trim($swk_clean, '_'));
        $user_clean = preg_replace('/_+/', '_', trim($user_clean, '_'));

        if (empty($swk_clean))  $swk_clean  = 'SWK';
        if (empty($user_clean)) $user_clean = 'User';

        $filename = "Template_Input_Harian_" . $swk_clean . "_" . $user_clean . "_" . $tanggal . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpXlsx));
        header('Cache-Control: max-age=0');

        readfile($tmpXlsx);
        @unlink($tmpXlsx);
        exit;
    }

    /**
     * Upload & Process File Excel / CSV (.xlsx, .xls, .csv)
     */
    public function upload_excel()
    {
        if (!$this->input->is_ajax_request()) show_404();

        $monev       = $this->session->userdata('monev_swk');
        $nip         = $monev['nip'] ?? '';
        $idswk_lokal = trim($this->input->post('idswk_lokal'));
        $tanggal     = trim($this->input->post('tanggal')); // YYYY-MM-DD

        // Resolve ke local idswk yang konsisten
        $swkRow = $this->db->group_start()
            ->where('idswk', $idswk_lokal)
            ->or_where('api_swk_id', $idswk_lokal)
            ->group_end()
            ->get('m_swk')->row();

        if ($swkRow) {
            $idswk_lokal = $swkRow->idswk;
        }

        if (empty($idswk_lokal)) {
            echo json_encode(['status' => false, 'message' => 'SWK wajib dipilih.']);
            return;
        }

        if (empty($_FILES['file_excel']['tmp_name'])) {
            echo json_encode(['status' => false, 'message' => 'File Excel/CSV belum dipilih.']);
            return;
        }

        $tmpFile = $_FILES['file_excel']['tmp_name'];
        $rows    = $this->_parseFileToRows($tmpFile);

        if (empty($rows) || count($rows) < 2) {
            echo json_encode(['status' => false, 'message' => 'File kosong atau format file tidak dapat dibaca.']);
            return;
        }

        // Tentukan indeks kolom secara presisi
        $headerRowIdx = -1;
        $idCol        = 0;
        $standCol     = 1;
        $kodeCol      = 2;
        $nameCol      = 3;
        $tglCol       = 5;
        $omCol        = 6;
        $kjCol        = 7;

        // Cari lokasi baris header yang tepat di antara 5 baris pertama
        for ($r = 0; $r < min(5, count($rows)); $r++) {
            $rowCells = $rows[$r];
            if (!is_array($rowCells)) continue;

            foreach ($rowCells as $idx => $cellVal) {
                $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', (string)$cellVal)));
                if ($clean === 'id_unit_usaha' || $clean === 'idunitusaha' || $clean === 'id_unit') {
                    $headerRowIdx = $r;
                    break 2;
                }
            }
        }

        // Jika baris header ditemukan, petakan indeks kolom secara presisi
        if ($headerRowIdx >= 0 && isset($rows[$headerRowIdx])) {
            foreach ($rows[$headerRowIdx] as $idx => $cellVal) {
                $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', (string)$cellVal)));
                if ($clean === 'id_unit_usaha' || $clean === 'idunitusaha' || $clean === 'id_unit') {
                    $idCol = $idx;
                } elseif ($clean === 'kode_stand' || $clean === 'kodestand' || $clean === 'stand') {
                    $standCol = $idx;
                } elseif ($clean === 'kode_usaha' || $clean === 'kodeusaha') {
                    $kodeCol = $idx;
                } elseif ($clean === 'nama_usaha' || $clean === 'namausaha') {
                    $nameCol = $idx;
                } elseif ($clean === 'tanggal' || $clean === 'tgl') {
                    $tglCol = $idx;
                } elseif ($clean === 'omset') {
                    $omCol = $idx;
                } elseif ($clean === 'kunjungan' || $clean === 'jumlah') {
                    $kjCol = $idx;
                }
            }
        }

        $startDataIdx = ($headerRowIdx >= 0) ? ($headerRowIdx + 1) : 1;

        $batchOmset     = [];
        $batchKunjungan = [];
        $rowCount       = 0;

        // Loop data baris per baris
        for ($i = $startDataIdx; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row)) continue;

            $uid    = trim((string)($row[$idCol] ?? ''));
            $tglRow = isset($row[$tglCol]) ? trim((string)$row[$tglCol]) : $tanggal;

            // Validasi UID harus berupa UUID (panjang minimal 20 karakter)
            if (empty($uid) || strlen($uid) < 20) continue;

            $tglFormatted = $this->_parseDate($tglRow);
            if (!$tglFormatted) $tglFormatted = $tanggal;
            if (empty($tglFormatted)) continue;

            $stand = isset($row[$standCol]) ? trim((string)$row[$standCol]) : '';
            $kode  = isset($row[$kodeCol])  ? trim((string)$row[$kodeCol])  : '';
            $nama  = isset($row[$nameCol])  ? trim((string)$row[$nameCol])  : '';

            // Omset
            if (isset($row[$omCol])) {
                $rawOm = trim((string)$row[$omCol]);
                $omVal = (int)preg_replace('/[^\d]/', '', $rawOm);
                $batchOmset[] = [
                    'id_unit_usaha'  => $uid,
                    'nama_unit_usaha'=> $nama,
                    'kode_unit_usaha'=> $kode,
                    'nama_stand'     => $stand,
                    'idswk'          => $idswk_lokal,
                    'tanggal'        => $tglFormatted,
                    'omset'          => $omVal,
                    'created_by'     => $nip,
                ];
            }

            // Kunjungan
            if (isset($row[$kjCol])) {
                $rawKj = trim((string)$row[$kjCol]);
                $kjVal = (int)preg_replace('/[^\d]/', '', $rawKj);
                $batchKunjungan[] = [
                    'id_unit_usaha'  => $uid,
                    'nama_unit_usaha'=> $nama,
                    'kode_unit_usaha'=> $kode,
                    'nama_stand'     => $stand,
                    'idswk'          => $idswk_lokal,
                    'tanggal'        => $tglFormatted,
                    'jumlah'         => $kjVal,
                    'created_by'     => $nip,
                ];
            }

            $rowCount++;
        }

        $savedOmset = $this->M_unit_usaha->saveBatchOmset($batchOmset);
        $savedKj    = $this->M_unit_usaha->saveBatchKunjungan($batchKunjungan);

        $parts = explode('-', $tanggal);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));

        $total_omset     = $this->M_unit_usaha->totalOmsetSwkBulan($idswk_lokal, $bulan, $tahun);
        $total_kunjungan = $this->M_unit_usaha->totalKunjunganSwkBulan($idswk_lokal, $bulan, $tahun);

        echo json_encode([
            'status'          => true,
            'message'         => 'Impor berhasil! Total ' . $rowCount . ' unit usaha diperbarui.',
            'total_omset'     => $total_omset,
            'total_kunjungan' => $total_kunjungan,
        ]);
    }

    /**
     * Reader Multi-Format (Native Excel XLSX Zip, XML 2003, HTML Table, CSV)
     */
    private function _parseFileToRows($filePath)
    {
        $rows = [];
        $content = file_get_contents($filePath);

        if (empty($content)) return [];

        // 1. Coba XLSX (Zip XML Excel Modern)
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                $sharedStrings = [];
                $ssXml = $zip->getFromName('xl/sharedStrings.xml');
                if ($ssXml) {
                    $ssDom = @simplexml_load_string($ssXml);
                    if ($ssDom && isset($ssDom->si)) {
                        foreach ($ssDom->si as $si) {
                            if (isset($si->t)) {
                                $sharedStrings[] = (string)$si->t;
                            } elseif (isset($si->r)) {
                                $t = '';
                                foreach ($si->r as $r) {
                                    $t .= (string)$r->t;
                                }
                                $sharedStrings[] = $t;
                            } else {
                                $sharedStrings[] = '';
                            }
                        }
                    }
                }

                $sheetXmlName = 'xl/worksheets/sheet1.xml';
                if ($zip->locateName($sheetXmlName) === false) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $stat = $zip->statIndex($i);
                        if (strpos($stat['name'], 'xl/worksheets/sheet') !== false) {
                            $sheetXmlName = $stat['name'];
                            break;
                        }
                    }
                }

                $sheetXml = $zip->getFromName($sheetXmlName);
                if ($sheetXml) {
                    $sheetDom = @simplexml_load_string($sheetXml);
                    if ($sheetDom && isset($sheetDom->sheetData->row)) {
                        foreach ($sheetDom->sheetData->row as $rowNode) {
                            $row = [];
                            foreach ($rowNode->c as $cell) {
                                $rAttr = (string)($cell->attributes()['r'] ?? '');
                                $cIdx  = $rAttr ? $this->_colLetterToIdx($rAttr) : count($row);

                                $attr = $cell->attributes();
                                $type = (string)($attr['t'] ?? '');
                                $val  = (string)($cell->v ?? '');

                                if ($type === 's' && isset($sharedStrings[(int)$val])) {
                                    $val = $sharedStrings[(int)$val];
                                } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                                    $val = (string)$cell->is->t;
                                }

                                $row[$cIdx] = trim($val);
                            }

                            if (!empty($row)) {
                                $maxIdx = max(array_keys($row));
                                $cleanRow = [];
                                for ($k = 0; $k <= $maxIdx; $k++) {
                                    $cleanRow[$k] = $row[$k] ?? '';
                                }
                                $rows[] = $cleanRow;
                            }
                        }
                    }
                }
                $zip->close();
                if (!empty($rows)) return $rows;
            }
        }

        // 2. Coba XML / HTML Table (.xls / Native XML 2003)
        if (strpos($content, '<tr') !== false || strpos($content, '<table') !== false || strpos($content, '<Row') !== false || strpos($content, '<row') !== false) {
            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8"?>' . $content);

            $trList = $dom->getElementsByTagName('row');
            if ($trList->length == 0) {
                $trList = $dom->getElementsByTagName('tr');
            }

            foreach ($trList as $tr) {
                $r = [];
                $cells = $tr->getElementsByTagName('cell');
                if ($cells->length == 0) {
                    $cells = $tr->getElementsByTagName('td');
                    if ($cells->length == 0) {
                        $cells = $tr->getElementsByTagName('th');
                    }
                }
                foreach ($cells as $cell) {
                    $r[] = trim($cell->nodeValue);
                }
                if (!empty($r)) $rows[] = $r;
            }
            if (!empty($rows)) return $rows;
        }

        // 3. Fallback: CSV / Text File
        $handle = fopen($filePath, 'r');
        if ($handle) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") rewind($handle);

            $firstLine = fgets($handle);
            rewind($handle);
            if ($bom === "\xEF\xBB\xBF") fread($handle, 3);

            $delimiter = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';
            if (substr_count($firstLine, "\t") > substr_count($firstLine, $delimiter)) {
                $delimiter = "\t";
            }

            while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
                if (!empty($data)) $rows[] = $data;
            }
            fclose($handle);
        }

        return $rows;
    }

    private function _colLetterToIdx($ref)
    {
        $col = strtoupper(preg_replace('/[^A-Z]/i', '', $ref));
        $idx = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $idx = $idx * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return max(0, $idx - 1);
    }

    private function _parseDate($dateStr)
    {
        $dateStr = trim((string)$dateStr);
        if (empty($dateStr)) return false;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $dateStr, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        if (is_numeric($dateStr) && (int)$dateStr > 30000) {
            return date('Y-m-d', ((int)$dateStr - 25569) * 86400);
        }

        $ts = strtotime($dateStr);
        return $ts ? date('Y-m-d', $ts) : false;
    }
}
